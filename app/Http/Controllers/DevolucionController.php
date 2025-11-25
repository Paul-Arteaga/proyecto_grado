<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Devolucion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DevolucionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar lista de reservas confirmadas listas para devolución
     */
    public function index(Request $request)
    {
        $busqueda = trim($request->query('buscar', ''));

        $reservas = Reserva::whereIn('estado', ['confirmada', 'completada'])
            ->with(['user', 'vehiculo', 'accesorios', 'devolucion'])
            ->when($busqueda !== '', function ($query) use ($busqueda) {
                $query->where(function ($sub) use ($busqueda) {
                    $sub->where('id', 'like', "%{$busqueda}%")
                        ->orWhereHas('user', function ($userQuery) use ($busqueda) {
                            $userQuery->where('username', 'like', "%{$busqueda}%")
                                ->orWhere('email', 'like', "%{$busqueda}%")
                                ->orWhere('numero_carnet', 'like', "%{$busqueda}%");
                        })
                        ->orWhereHas('vehiculo', function ($vehiculoQuery) use ($busqueda) {
                            $vehiculoQuery->where('marca', 'like', "%{$busqueda}%")
                                ->orWhere('modelo', 'like', "%{$busqueda}%")
                                ->orWhere('placa', 'like', "%{$busqueda}%");
                        })
                        ->orWhereHas('devolucion', function ($devolucionQuery) use ($busqueda) {
                            $devolucionQuery->where('estado', 'like', "%{$busqueda}%")
                                ->orWhereDate('fecha_hora_devolucion', $busqueda);
                        });
                });
            })
            ->orderBy('fecha_fin', 'desc')
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        return view('devolucion.devolucion', [
            'reservas' => $reservas,
            'busqueda' => $busqueda,
        ]);
    }

    /**
     * Mostrar formulario para registrar devolución
     */
    public function create($reservaId)
    {
        $reserva = Reserva::with(['user', 'vehiculo', 'accesorios'])->findOrFail($reservaId);
        
        // Verificar que la reserva esté confirmada
        if ($reserva->estado !== 'confirmada') {
            return redirect()->route('devolucion.index')
                ->with('error', 'Solo se pueden procesar devoluciones de reservas confirmadas.');
        }

        return view('devolucion.create', compact('reserva'));
    }

    /**
     * Guardar devolución
     */
    public function store(Request $request, $reservaId)
    {
        $reserva = Reserva::with(['vehiculo', 'accesorios'])->findOrFail($reservaId);

        $validated = $request->validate([
            'fecha_devolucion' => 'required|date',
            'hora_devolucion' => 'required|date_format:H:i',
            'condiciones_vehiculo' => 'required|array',
            'condiciones_accesorios' => 'nullable|array',
            'observaciones' => 'nullable|string|max:1000',
            'km_retorno' => 'required|integer|min:0',
        ]);

        $vehiculo = $reserva->vehiculo;
        $kmBase = $vehiculo?->km_actual ?? 0;

        if ($validated['km_retorno'] < $kmBase) {
            return back()->withErrors([
                'km_retorno' => 'El kilometraje no puede ser menor al registrado cuando se entregó el vehículo (' . $kmBase . ' km).',
            ])->withInput();
        }

        // Combinar fecha y hora
        $fechaHora = Carbon::parse($validated['fecha_devolucion'] . ' ' . $validated['hora_devolucion']);

        // Determinar estado según condiciones
        $estado = 'completada';
        $condicionesVehiculo = $validated['condiciones_vehiculo'];
        
        // Si hay algún daño reportado, cambiar estado
        if (isset($condicionesVehiculo['tiene_danos']) && $condicionesVehiculo['tiene_danos']) {
            $estado = 'con_danos';
        }

        $usuarioRecibe = Auth::user();

        // Crear devolución
        $devolucion = Devolucion::create([
            'reserva_id' => $reserva->id,
            'fecha_hora_devolucion' => $fechaHora,
            'usuario_recibe_id' => $usuarioRecibe->id,
            'condiciones_vehiculo' => $validated['condiciones_vehiculo'],
            'condiciones_accesorios' => $validated['condiciones_accesorios'] ?? [],
            'observaciones' => $validated['observaciones'] ?? null,
            'estado' => $estado,
            'km_retorno' => $validated['km_retorno'],
        ]);

        // Marcar la reserva como completada para reflejar que el servicio finalizó
        $reserva->update(['estado' => 'completada']);

        // Actualizar estado del vehículo a disponible
        if ($vehiculo) {
            $vehiculo->update([
                'estado' => 'disponible',
                'km_actual' => $validated['km_retorno'],
            ]);
        }

        // Devolver accesorios al stock si fueron entregados sin daños
        if (!empty($validated['condiciones_accesorios'])) {
            foreach ($reserva->accesorios as $accesorio) {
                $condicionesAccesorio = $validated['condiciones_accesorios'][$accesorio->id] ?? null;

                if (!$condicionesAccesorio) {
                    continue;
                }

                $devuelto = isset($condicionesAccesorio['devuelto']) && $condicionesAccesorio['devuelto'];
                $tieneDanios = isset($condicionesAccesorio['tiene_danos']) && $condicionesAccesorio['tiene_danos'];

                if ($devuelto && !$tieneDanios && $accesorio->stock !== null) {
                    $cantidad = $accesorio->pivot->cantidad ?? 0;
                    if ($cantidad > 0) {
                        $accesorio->increment('stock', $cantidad);
                    }
                }
            }
        }

        return redirect()->route('devolucion.index')
            ->with('success', 'Devolución registrada exitosamente.');
    }

    /**
     * Ver detalles de una devolución
     */
    public function show($id)
    {
        $devolucion = Devolucion::with(['reserva.user', 'reserva.vehiculo', 'reserva.accesorios', 'usuarioRecibe'])
            ->findOrFail($id);

        return view('devolucion.show', compact('devolucion'));
    }
}

