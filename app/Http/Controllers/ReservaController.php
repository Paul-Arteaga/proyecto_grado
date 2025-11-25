<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Vehiculo;
use App\Models\Notificacion;
use App\Models\User;
use App\Models\Accesorio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;

class ReservaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Lista todas las reservas (admin/recepcionista) o del usuario
    public function index(Request $request)
    {
        $user = Auth::user();
        $busqueda = trim($request->query('buscar', ''));
        
        $reservasQuery = Reserva::with(['user', 'vehiculo', 'accesorios']);

        // Si no es admin ni recepcionista, limitar a sus reservas
        if (!in_array($user->id_rol, [1, 3])) {
            $reservasQuery->where('user_id', $user->id);
        }

        // Aplicar buscador
        if ($busqueda !== '') {
            $reservasQuery->where(function ($query) use ($busqueda) {
                $query->where('id', 'like', "%{$busqueda}%")
                    ->orWhere('estado', 'like', "%{$busqueda}%")
                    ->orWhere('estado_pago', 'like', "%{$busqueda}%")
                    ->orWhereHas('user', function ($sub) use ($busqueda) {
                        $sub->where('username', 'like', "%{$busqueda}%")
                            ->orWhere('email', 'like', "%{$busqueda}%")
                            ->orWhere('numero_carnet', 'like', "%{$busqueda}%");
                    })
                    ->orWhereHas('vehiculo', function ($sub) use ($busqueda) {
                        $sub->where('marca', 'like', "%{$busqueda}%")
                            ->orWhere('modelo', 'like', "%{$busqueda}%")
                            ->orWhere('placa', 'like', "%{$busqueda}%");
                    });
            });
        }

        // Si es admin/recepcionista, puede filtrar por usuario; ya cubierto arriba

        // Orden más reciente primero
        $reservas = $reservasQuery->orderBy('created_at', 'desc')->get();

        return view('reservas.index', [
            'reservas' => $reservas,
            'busqueda' => $busqueda,
        ]);
    }

    // Inicia el proceso de reserva (preparar datos para el modal de pago)
    public function prepararReserva(Request $request)
    {
        $request->validate([
            'vehiculo_id' => 'required|integer|exists:vehiculos,id',
            'fechas' => 'required|array|min:1',
            'fechas.*' => 'date',
        ]);

        $vehiculo = Vehiculo::findOrFail($request->vehiculo_id);
        $fechas = collect($request->fechas)->sort()->values();
        $fechaInicio = $fechas->first();
        $fechaFin = $fechas->last();

        // Verificar disponibilidad (solo reservas confirmadas bloquean fechas)
        $existe = Reserva::where('vehiculo_id', $request->vehiculo_id)
            ->where('estado', 'confirmada')
            ->where(function ($q) use ($fechaInicio, $fechaFin) {
                $q->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin])
                  ->orWhereBetween('fecha_fin', [$fechaInicio, $fechaFin])
                  ->orWhere(function($q2) use ($fechaInicio, $fechaFin) {
                      $q2->where('fecha_inicio', '<=', $fechaInicio)
                         ->where('fecha_fin', '>=', $fechaFin);
                  });
            })
            ->exists();

        if ($existe) {
            return response()->json([
                'ok' => false,
                'message' => 'Alguno de esos días ya está reservado'
            ], 422);
        }

        // Calcular días y monto total
        $dias = Carbon::parse($fechaInicio)->diffInDays(Carbon::parse($fechaFin)) + 1;
        $montoTotal = $vehiculo->precio_diario * $dias;

        return response()->json([
            'ok' => true,
            'vehiculo' => [
                'id' => $vehiculo->id,
                'marca' => $vehiculo->marca,
                'modelo' => $vehiculo->modelo,
                'placa' => $vehiculo->placa,
                'precio_diario' => $vehiculo->precio_diario,
            ],
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'dias' => $dias,
            'monto_total' => $montoTotal,
        ]);
    }

    // Guarda la reserva con documentos y pago
    public function store(Request $request)
    {
        $user = Auth::user();
        $vehiculo = Vehiculo::findOrFail($request->vehiculo_id);
        
        // Verificar si el usuario necesita subir documentos
        $necesitaDocumentos = $user->necesitaSubirDocumentos();

        // Validación condicional según si necesita documentos
        $rules = [
            'vehiculo_id' => 'required|integer|exists:vehiculos,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'comprobante_pago' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'acepta_contrato' => 'accepted',
        ];

        if ($necesitaDocumentos) {
            $rules['carnet_anverso'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5120';
            $rules['carnet_reverso'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5120';
            $rules['licencia_anverso'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5120';
            $rules['licencia_reverso'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5120';
            $rules['licencia_fecha_vencimiento'] = 'required|date|after:today';
        }

        $request->validate($rules);

        // Verificar disponibilidad nuevamente (solo reservas confirmadas bloquean fechas)
        $existe = Reserva::where('vehiculo_id', $request->vehiculo_id)
            ->where('estado', 'confirmada') // Solo las confirmadas bloquean
            ->where(function ($q) use ($request) {
                $q->whereBetween('fecha_inicio', [$request->fecha_inicio, $request->fecha_fin])
                  ->orWhereBetween('fecha_fin', [$request->fecha_inicio, $request->fecha_fin])
                  ->orWhere(function($q2) use ($request) {
                      $q2->where('fecha_inicio', '<=', $request->fecha_inicio)
                         ->where('fecha_fin', '>=', $request->fecha_fin);
                  });
            })
            ->exists();

        if ($existe) {
            return back()->withErrors(['error' => 'Las fechas seleccionadas ya no están disponibles.'])->withInput();
        }

        // Calcular monto total del vehículo
        $dias = Carbon::parse($request->fecha_inicio)->diffInDays(Carbon::parse($request->fecha_fin)) + 1;
        $montoTotal = $vehiculo->precio_diario * $dias;
        
        // Calcular monto de accesorios si se enviaron
        $montoAccesorios = 0;
        if ($request->has('accesorios') && is_array($request->accesorios)) {
            foreach ($request->accesorios as $accesorioId => $cantidad) {
                if ($cantidad > 0) {
                    $accesorio = \App\Models\Accesorio::find($accesorioId);
                    if ($accesorio && $accesorio->activo) {
                        $montoAccesorios += $accesorio->precio * $cantidad;
                    }
                }
            }
        }

        // Guardar documentos según si necesita subirlos o no
        $carnetAnverso = null;
        $carnetReverso = null;
        $licenciaAnverso = null;
        $licenciaReverso = null;
        $licenciaFechaVencimiento = null;

        if ($necesitaDocumentos) {
            // Guardar documentos nuevos en la reserva
            $carnetAnverso = $request->file('carnet_anverso')->store('reservas/documentos', 'public');
            $carnetReverso = $request->file('carnet_reverso')->store('reservas/documentos', 'public');
            $licenciaAnverso = $request->file('licencia_anverso')->store('reservas/documentos', 'public');
            $licenciaReverso = $request->file('licencia_reverso')->store('reservas/documentos', 'public');
            $licenciaFechaVencimiento = $request->licencia_fecha_vencimiento;
            
            // Para compatibilidad con estructura anterior
            $documentoCarnet = $carnetAnverso;
            $documentoLicencia = $licenciaAnverso;
        } else {
            // Usar documentos ya verificados del perfil del usuario
            $carnetAnverso = $user->carnet_anverso;
            $carnetReverso = $user->carnet_reverso;
            $licenciaAnverso = $user->licencia_anverso;
            $licenciaReverso = $user->licencia_reverso;
            $licenciaFechaVencimiento = $user->licencia_fecha_vencimiento;
            
            // Para compatibilidad con estructura anterior
            $documentoCarnet = $carnetAnverso;
            $documentoLicencia = $licenciaAnverso;
        }

        // El comprobante siempre se sube
        $comprobantePago = $request->file('comprobante_pago')->store('reservas/comprobantes', 'public');

        // Crear la solicitud de reserva (NO se confirma automáticamente)
        $reserva = Reserva::create([
            'user_id' => $user->id,
            'vehiculo_id' => $request->vehiculo_id,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'estado' => 'solicitada', // Estado inicial: solicitada (pendiente de revisión)
            'monto_total' => $montoTotal, // Solo monto del vehículo, accesorios se guardan por separado
            'estado_pago' => 'pendiente',
            'documento_carnet' => $documentoCarnet,
            'carnet_anverso' => $carnetAnverso,
            'carnet_reverso' => $carnetReverso,
            'documento_licencia' => $documentoLicencia,
            'licencia_anverso' => $licenciaAnverso,
            'licencia_reverso' => $licenciaReverso,
            'licencia_fecha_vencimiento' => $licenciaFechaVencimiento,
            'comprobante_pago' => $comprobantePago,
        ]);

        // Guardar accesorios si se enviaron
        $accesoriosTexto = '';
        if ($request->has('accesorios') && is_array($request->accesorios)) {
            foreach ($request->accesorios as $accesorioId => $cantidad) {
                if ($cantidad > 0) {
                    $accesorio = \App\Models\Accesorio::find($accesorioId);
                    if ($accesorio && $accesorio->activo) {
                        $precioUnitario = $accesorio->precio;
                        $precioTotal = $precioUnitario * $cantidad;
                        
                        $reserva->accesorios()->attach($accesorioId, [
                            'cantidad' => $cantidad,
                            'precio_unitario' => $precioUnitario,
                            'precio_total' => $precioTotal,
                            'estado' => 'pendiente', // Los accesorios también requieren aprobación
                        ]);
                        
                        $accesoriosTexto .= "{$accesorio->nombre} (x{$cantidad}) - Bs. {$precioTotal}. ";
                    }
                }
            }
        }

        // Crear notificaciones para admin y recepcionistas (cada uno recibe su propia notificación)
        $adminYRecepcionistas = User::whereIn('id_rol', [1, 3])->get();
        $montoTotalConAccesorios = $montoTotal + $montoAccesorios;
        $mensajeNotificacion = "El usuario {$user->username} (Carnet: {$user->numero_carnet}) ha enviado una solicitud de reserva del vehículo {$vehiculo->marca} {$vehiculo->modelo} (Placa: {$vehiculo->placa}) del {$request->fecha_inicio} al {$request->fecha_fin}. Monto vehículo: Bs. {$montoTotal}.";
        if ($montoAccesorios > 0) {
            $mensajeNotificacion .= " Accesorios: {$accesoriosTexto} Monto total: Bs. {$montoTotalConAccesorios}.";
        } else {
            $mensajeNotificacion .= " Monto total: Bs. {$montoTotalConAccesorios}.";
        }
        $mensajeNotificacion .= " Revisa los documentos y comprobante.";
        
        foreach ($adminYRecepcionistas as $admin) {
            Notificacion::create([
                'reserva_id' => $reserva->id,
                'user_id' => $admin->id, // El admin/recepcionista que recibirá la notificación
                'tipo' => 'solicitud_reserva',
                'titulo' => 'Nueva Solicitud de Reserva',
                'mensaje' => $mensajeNotificacion,
                'leida' => false,
            ]);
        }

        return redirect()->route('perfil.reservas')->with('success', 'Tu solicitud de reserva ha sido enviada. Si tu documentación y pago son correctos, la reserva se confirmará automáticamente en 10-15 minutos. Podrás ver el estado en "Mis Reservas".');
    }

    // Actualizar reserva
    public function update(Request $request, Reserva $reserva)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $existe = Reserva::where('vehiculo_id', $reserva->vehiculo_id)
            ->where('id', '!=', $reserva->id)
            ->where('estado', 'confirmada')
            ->where(function ($q) use ($request) {
                $q->whereBetween('fecha_inicio', [$request->fecha_inicio, $request->fecha_fin])
                  ->orWhereBetween('fecha_fin', [$request->fecha_inicio, $request->fecha_fin])
                  ->orWhere(function($q2) use ($request) {
                      $q2->where('fecha_inicio', '<=', $request->fecha_inicio)
                         ->where('fecha_fin', '>=', $request->fecha_fin);
                  });
            })
            ->exists();

        if ($existe) {
            return response()->json([
                'ok' => false,
                'message' => 'Las nuevas fechas chocan con otra reserva'
            ], 422);
        }

        $reserva->update([
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
        ]);

        return response()->json(['ok' => true]);
    }

    // Eliminar reserva
    /**
     * Cancelar reserva (cambia estado a "cancelada" en lugar de eliminar)
     */
    public function destroy(Reserva $reserva)
    {
        // Solo admin o recepcionista pueden cancelar
        $user = Auth::user();
        if (!in_array($user->id_rol, [1, 3])) {
            abort(403, 'No autorizado.');
        }

        // Verificar que la reserva no esté ya cancelada
        if ($reserva->estado === 'cancelada') {
            return back()->withErrors(['error' => 'Esta reserva ya está cancelada.']);
        }

        // Cargar relaciones necesarias
        $reserva->load('vehiculo', 'user', 'accesorios');

        // Devolver stock de accesorios aprobados antes de cancelar
        $reserva->accesorios()->wherePivot('estado', 'aprobado')->get()->each(function ($accesorio) {
            $cantidad = $accesorio->pivot->cantidad;
            
            // Incrementar stock si está configurado
            if ($accesorio->stock !== null) {
                $nuevoStock = $accesorio->stock + $cantidad;
                $accesorio->update(['stock' => $nuevoStock]);
            }
        });

        // Cambiar estado a cancelada (no eliminar, para mantener historial)
        $reserva->update([
            'estado' => 'cancelada',
        ]);

        // Notificar al usuario que su reserva fue cancelada
        Notificacion::create([
            'reserva_id' => $reserva->id,
            'user_id' => $reserva->user_id,
            'tipo' => 'reserva_cancelada',
            'titulo' => 'Reserva Cancelada',
            'mensaje' => "Tu reserva del vehículo {$reserva->vehiculo->marca} {$reserva->vehiculo->modelo} (Placa: {$reserva->vehiculo->placa}) del {$reserva->fecha_inicio} al {$reserva->fecha_fin} ha sido cancelada. Los días futuros quedan disponibles nuevamente.",
            'leida' => false,
        ]);

        return back()->with('success', 'Reserva cancelada. Los días futuros quedan disponibles y los accesorios aprobados han vuelto al stock.');
    }

    /**
     * Aprobar solicitud de reserva (admin/recepcionista)
     */
    public function aprobarSolicitud(Reserva $reserva)
    {
        // Verificar que solo admin o recepcionista puedan aprobar
        $user = Auth::user();
        if (!in_array($user->id_rol, [1, 3])) {
            abort(403, 'No autorizado.');
        }

        // Cargar relaciones necesarias
        $reserva->load('vehiculo', 'user');

        // Verificar que la reserva esté en estado "solicitada"
        if ($reserva->estado !== 'solicitada') {
            return back()->withErrors(['error' => 'Esta reserva ya ha sido procesada.']);
        }

        // Verificar disponibilidad: no puede haber otra reserva CONFIRMADA en las mismas fechas para el mismo vehículo
        $existeConflicto = Reserva::where('vehiculo_id', $reserva->vehiculo_id)
            ->where('id', '!=', $reserva->id)
            ->where('estado', 'confirmada') // Solo verificar reservas confirmadas
            ->where(function ($q) use ($reserva) {
                $q->whereBetween('fecha_inicio', [$reserva->fecha_inicio, $reserva->fecha_fin])
                  ->orWhereBetween('fecha_fin', [$reserva->fecha_inicio, $reserva->fecha_fin])
                  ->orWhere(function($q2) use ($reserva) {
                      $q2->where('fecha_inicio', '<=', $reserva->fecha_inicio)
                         ->where('fecha_fin', '>=', $reserva->fecha_fin);
                  });
            })
            ->exists();

        if ($existeConflicto) {
            return back()->withErrors(['error' => 'No se puede aprobar esta reserva. Las fechas seleccionadas ya están reservadas por otra reserva confirmada.']);
        }

        // Guardar documentos en el perfil del usuario cuando se aprueba la reserva
        $usuarioReserva = $reserva->user;
        
        if ($reserva->carnet_anverso && $reserva->carnet_reverso && 
            $reserva->licencia_anverso && $reserva->licencia_reverso && $reserva->licencia_fecha_vencimiento) {
            
            // Copiar archivos físicos de la reserva al perfil del usuario
            $carnetAnversoPerfil = null;
            $carnetReversoPerfil = null;
            $licenciaAnversoPerfil = null;
            $licenciaReversoPerfil = null;
            
            // Copiar carnet anverso
            if ($reserva->carnet_anverso && Storage::disk('public')->exists($reserva->carnet_anverso)) {
                $carnetAnversoPerfil = 'usuarios/documentos/' . $usuarioReserva->id . '/carnet_anverso_' . time() . '.' . pathinfo($reserva->carnet_anverso, PATHINFO_EXTENSION);
                Storage::disk('public')->copy($reserva->carnet_anverso, $carnetAnversoPerfil);
            }
            
            // Copiar carnet reverso
            if ($reserva->carnet_reverso && Storage::disk('public')->exists($reserva->carnet_reverso)) {
                $carnetReversoPerfil = 'usuarios/documentos/' . $usuarioReserva->id . '/carnet_reverso_' . time() . '.' . pathinfo($reserva->carnet_reverso, PATHINFO_EXTENSION);
                Storage::disk('public')->copy($reserva->carnet_reverso, $carnetReversoPerfil);
            }
            
            // Copiar licencia anverso
            if ($reserva->licencia_anverso && Storage::disk('public')->exists($reserva->licencia_anverso)) {
                $licenciaAnversoPerfil = 'usuarios/documentos/' . $usuarioReserva->id . '/licencia_anverso_' . time() . '.' . pathinfo($reserva->licencia_anverso, PATHINFO_EXTENSION);
                Storage::disk('public')->copy($reserva->licencia_anverso, $licenciaAnversoPerfil);
            }
            
            // Copiar licencia reverso
            if ($reserva->licencia_reverso && Storage::disk('public')->exists($reserva->licencia_reverso)) {
                $licenciaReversoPerfil = 'usuarios/documentos/' . $usuarioReserva->id . '/licencia_reverso_' . time() . '.' . pathinfo($reserva->licencia_reverso, PATHINFO_EXTENSION);
                Storage::disk('public')->copy($reserva->licencia_reverso, $licenciaReversoPerfil);
            }
            
            // Actualizar perfil del usuario con los documentos verificados
            $usuarioReserva->update([
                'carnet_anverso' => $carnetAnversoPerfil ?? $usuarioReserva->carnet_anverso,
                'carnet_reverso' => $carnetReversoPerfil ?? $usuarioReserva->carnet_reverso,
                'licencia_anverso' => $licenciaAnversoPerfil ?? $usuarioReserva->licencia_anverso,
                'licencia_reverso' => $licenciaReversoPerfil ?? $usuarioReserva->licencia_reverso,
                'licencia_fecha_vencimiento' => $reserva->licencia_fecha_vencimiento,
                'documentos_verificados' => true,
                'documentos_verificados_at' => now(),
            ]);
        }

        // Aprobar accesorios pendientes de la reserva
        $reserva->accesorios()->wherePivot('estado', 'pendiente')->get()->each(function ($accesorio) use ($reserva) {
            $reserva->accesorios()->updateExistingPivot($accesorio->id, [
                'estado' => 'aprobado',
            ]);
        });

        // Actualizar estado de la reserva
        $reserva->update([
            'estado' => 'confirmada',
            'estado_pago' => 'pagado',
        ]);

        // Calcular monto total con accesorios para el mensaje
        $montoAccesorios = $reserva->accesorios()->wherePivot('estado', '!=', 'rechazado')->sum('reserva_accesorios.precio_total');
        $montoTotalConAccesorios = $reserva->monto_total + $montoAccesorios;
        $accesoriosTexto = '';
        if ($montoAccesorios > 0) {
            $accesorios = $reserva->accesorios()->wherePivot('estado', '!=', 'rechazado')->get();
            foreach ($accesorios as $accesorio) {
                $accesoriosTexto .= "{$accesorio->nombre} (x{$accesorio->pivot->cantidad}), ";
            }
            $accesoriosTexto = rtrim($accesoriosTexto, ', ');
        }

        // Notificar al usuario que su reserva fue aprobada
        $mensajeAprobacion = "Tu reserva del vehículo {$reserva->vehiculo->marca} {$reserva->vehiculo->modelo} (Placa: {$reserva->vehiculo->placa}) del {$reserva->fecha_inicio} al {$reserva->fecha_fin} ha sido aprobada. Monto vehículo: Bs. {$reserva->monto_total}.";
        if ($montoAccesorios > 0) {
            $mensajeAprobacion .= " Accesorios: {$accesoriosTexto}. Monto total: Bs. {$montoTotalConAccesorios}.";
        } else {
            $mensajeAprobacion .= " Monto total: Bs. {$montoTotalConAccesorios}.";
        }
        
        Notificacion::create([
            'reserva_id' => $reserva->id,
            'user_id' => $reserva->user_id,
            'tipo' => 'reserva_aprobada',
            'titulo' => '¡Reserva Aprobada!',
            'mensaje' => $mensajeAprobacion . " ¡Todo está listo!",
            'leida' => false,
        ]);

        return back()->with('success', 'Solicitud aprobada y reserva confirmada.');
    }

    /**
     * Rechazar solicitud de reserva (admin/recepcionista)
     */
    public function rechazarSolicitud(Request $request, Reserva $reserva)
    {
        // Verificar que solo admin o recepcionista puedan rechazar
        $user = Auth::user();
        if (!in_array($user->id_rol, [1, 3])) {
            abort(403, 'No autorizado.');
        }

        $request->validate([
            'motivo' => 'nullable|string|max:500',
        ]);

        // Cargar relaciones necesarias
        $reserva->load('vehiculo', 'user');

        // Actualizar estado de la reserva
        $reserva->update([
            'estado' => 'rechazada',
        ]);

        // Notificar al usuario que su reserva fue rechazada
        $motivo = $request->motivo ? " Motivo: {$request->motivo}" : '';
        Notificacion::create([
            'reserva_id' => $reserva->id,
            'user_id' => $reserva->user_id,
            'tipo' => 'reserva_rechazada',
            'titulo' => 'Reserva Rechazada',
            'mensaje' => "Tu solicitud de reserva del vehículo {$reserva->vehiculo->marca} {$reserva->vehiculo->modelo} (Placa: {$reserva->vehiculo->placa}) del {$reserva->fecha_inicio} al {$reserva->fecha_fin} ha sido rechazada.{$motivo} Por favor, verifica tus documentos y vuelve a intentar.",
            'leida' => false,
        ]);

        return back()->with('success', 'Solicitud rechazada.');
    }

    /**
     * Agregar accesorios a una reserva existente (confirmada)
     */
    public function agregarAccesorios(Request $request, Reserva $reserva)
    {
        // Solo el dueño de la reserva puede agregar accesorios
        if ($reserva->user_id !== Auth::id()) {
            abort(403, 'No autorizado.');
        }

        // Solo se pueden agregar accesorios a reservas confirmadas
        if ($reserva->estado !== 'confirmada') {
            return back()->withErrors(['error' => 'Solo se pueden agregar accesorios a reservas confirmadas.']);
        }

        // Validar si viene de un solo accesorio (desde catálogo) o múltiples
        if ($request->has('accesorio_id')) {
            // Viene desde el catálogo, un solo accesorio
            $request->validate([
                'accesorio_id' => 'required|integer|exists:accesorios,id',
                'cantidad' => 'required|integer|min:1',
                'comprobante_pago' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            ]);
        } else {
            // Viene desde el modal de reserva, múltiples accesorios
            $request->validate([
                'accesorios' => 'required|array',
                'accesorios.*' => 'required|integer|min:1',
                'comprobante_pago' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            ]);
        }

        $montoAccesorios = 0;
        $accesoriosTexto = '';

        // Guardar comprobante de pago
        $comprobantePago = $request->file('comprobante_pago')->store('reservas/comprobantes', 'public');

        // Si viene desde el catálogo (un solo accesorio)
        if ($request->has('accesorio_id')) {
            $accesorioId = $request->accesorio_id;
            $cantidad = $request->cantidad;
            
            $accesorio = Accesorio::find($accesorioId);
            if ($accesorio && $accesorio->activo) {
                // Verificar stock disponible
                if ($accesorio->stock !== null) {
                    // Verificar si ya existe este accesorio en la reserva para calcular stock disponible
                    $cantidadExistente = 0;
                    $existeEnReserva = $reserva->accesorios()->where('accesorio_id', $accesorioId)->first();
                    if ($existeEnReserva) {
                        $cantidadExistente = $existeEnReserva->pivot->cantidad;
                    }
                    
                    $stockDisponible = $accesorio->stock + $cantidadExistente; // Stock actual + cantidad que se devolvería si se actualiza
                    
                    if ($cantidad > $stockDisponible) {
                        return back()->withErrors(['error' => "No hay suficiente stock disponible. Stock disponible: {$stockDisponible}, solicitado: {$cantidad}."])->withInput();
                    }
                }
                
                $precioUnitario = $accesorio->precio;
                $precioTotal = $precioUnitario * $cantidad;
                $montoAccesorios = $precioTotal;

                // Verificar si ya existe este accesorio en la reserva
                $existe = $reserva->accesorios()->where('accesorio_id', $accesorioId)->exists();
                
                if ($existe) {
                    // Actualizar cantidad y precio
                    $reserva->accesorios()->updateExistingPivot($accesorioId, [
                        'cantidad' => $cantidad,
                        'precio_unitario' => $precioUnitario,
                        'precio_total' => $precioTotal,
                        'estado' => 'pendiente',
                        'comprobante_pago' => $comprobantePago,
                    ]);
                } else {
                    // Agregar nuevo accesorio
                    $reserva->accesorios()->attach($accesorioId, [
                        'cantidad' => $cantidad,
                        'precio_unitario' => $precioUnitario,
                        'precio_total' => $precioTotal,
                        'estado' => 'pendiente',
                        'comprobante_pago' => $comprobantePago,
                    ]);
                }

                $accesoriosTexto = "{$accesorio->nombre} (x{$cantidad}) - Bs. {$precioTotal}. ";
            }
        } else {
            // Viene desde el modal de reserva (múltiples accesorios)
            foreach ($request->accesorios as $accesorioId => $cantidad) {
                if ($cantidad > 0) {
                    $accesorio = Accesorio::find($accesorioId);
                    if ($accesorio && $accesorio->activo) {
                        // Verificar stock disponible
                        if ($accesorio->stock !== null) {
                            if ($cantidad > $accesorio->stock) {
                                return back()->withErrors(['error' => "No hay suficiente stock disponible para {$accesorio->nombre}. Stock disponible: {$accesorio->stock}, solicitado: {$cantidad}."])->withInput();
                            }
                        }
                        
                        $precioUnitario = $accesorio->precio;
                        $precioTotal = $precioUnitario * $cantidad;
                        $montoAccesorios += $precioTotal;

                        // Verificar si ya existe este accesorio en la reserva
                        $existe = $reserva->accesorios()->where('accesorio_id', $accesorioId)->exists();
                        
                        if ($existe) {
                            // Actualizar cantidad y precio
                            $reserva->accesorios()->updateExistingPivot($accesorioId, [
                                'cantidad' => $cantidad,
                                'precio_unitario' => $precioUnitario,
                                'precio_total' => $precioTotal,
                                'estado' => 'pendiente',
                                'comprobante_pago' => $comprobantePago,
                            ]);
                        } else {
                            // Agregar nuevo accesorio
                            $reserva->accesorios()->attach($accesorioId, [
                                'cantidad' => $cantidad,
                                'precio_unitario' => $precioUnitario,
                                'precio_total' => $precioTotal,
                                'estado' => 'pendiente',
                                'comprobante_pago' => $comprobantePago,
                            ]);
                        }

                        $accesoriosTexto .= "{$accesorio->nombre} (x{$cantidad}) - Bs. {$precioTotal}. ";
                    }
                }
            }
        }

        // Crear notificaciones para admin y recepcionistas
        $adminYRecepcionistas = User::whereIn('id_rol', [1, 3])->get();
        $vehiculo = $reserva->vehiculo;
        
        foreach ($adminYRecepcionistas as $admin) {
            Notificacion::create([
                'reserva_id' => $reserva->id,
                'user_id' => $admin->id,
                'tipo' => 'solicitud_accesorios',
                'titulo' => 'Nueva Solicitud de Accesorios',
                'mensaje' => "El usuario {$reserva->user->username} (Carnet: {$reserva->user->numero_carnet}) ha solicitado agregar accesorios a su reserva del vehículo {$vehiculo->marca} {$vehiculo->modelo} (Placa: {$vehiculo->placa}). Accesorios: {$accesoriosTexto} Monto total: Bs. {$montoAccesorios}. Revisa el comprobante de pago.",
                'leida' => false,
            ]);
        }

        return redirect()->route('perfil.reservas')->with('success', 'Solicitud de accesorios enviada. Será revisada y aprobada en breve.');
    }

    /**
     * Aprobar accesorios agregados a una reserva
     */
    public function aprobarAccesorios(Request $request, Reserva $reserva)
    {
        // Solo admin y recepcionista
        if (!in_array(Auth::user()->id_rol, [1, 3])) {
            abort(403, 'No autorizado.');
        }

        // Actualizar estado de los accesorios a aprobado y reducir stock
        $reserva->accesorios()->wherePivot('estado', 'pendiente')->get()->each(function ($accesorio) use ($reserva) {
            $cantidad = $accesorio->pivot->cantidad;
            
            // Actualizar estado a aprobado
            $reserva->accesorios()->updateExistingPivot($accesorio->id, [
                'estado' => 'aprobado',
            ]);
            
            // Reducir stock si está configurado
            if ($accesorio->stock !== null) {
                $nuevoStock = max(0, $accesorio->stock - $cantidad);
                $accesorio->update(['stock' => $nuevoStock]);
            }
        });

        // Notificar al usuario
        $vehiculo = $reserva->vehiculo;
        Notificacion::create([
            'reserva_id' => $reserva->id,
            'user_id' => $reserva->user_id,
            'tipo' => 'accesorios_aprobados',
            'titulo' => 'Accesorios Aprobados',
            'mensaje' => "Los accesorios que agregaste a tu reserva del vehículo {$vehiculo->marca} {$vehiculo->modelo} (Placa: {$vehiculo->placa}) han sido aprobados.",
            'leida' => false,
        ]);

        return back()->with('success', 'Accesorios aprobados exitosamente.');
    }

    /**
     * Rechazar accesorios agregados a una reserva
     */
    public function rechazarAccesorios(Request $request, Reserva $reserva)
    {
        // Solo admin y recepcionista
        if (!in_array(Auth::user()->id_rol, [1, 3])) {
            abort(403, 'No autorizado.');
        }

        $request->validate([
            'motivo' => 'nullable|string|max:500',
        ]);

        // Actualizar estado de los accesorios a rechazado
        $reserva->accesorios()->wherePivot('estado', 'pendiente')->get()->each(function ($accesorio) use ($reserva) {
            $reserva->accesorios()->updateExistingPivot($accesorio->id, [
                'estado' => 'rechazado',
            ]);
        });

        // Notificar al usuario
        $vehiculo = $reserva->vehiculo;
        $motivo = $request->motivo ? " Motivo: {$request->motivo}" : '';
        Notificacion::create([
            'reserva_id' => $reserva->id,
            'user_id' => $reserva->user_id,
            'tipo' => 'accesorios_rechazados',
            'titulo' => 'Accesorios Rechazados',
            'mensaje' => "Los accesorios que agregaste a tu reserva del vehículo {$vehiculo->marca} {$vehiculo->modelo} (Placa: {$vehiculo->placa}) han sido rechazados.{$motivo}",
            'leida' => false,
        ]);

        return back()->with('success', 'Accesorios rechazados.');
    }
}
