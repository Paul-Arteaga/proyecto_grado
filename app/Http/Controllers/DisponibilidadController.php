<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehiculo;
use App\Models\Categoria;

class DisponibilidadController extends Controller
{
    public function __construct()
    {
        // 🔒 Exige autenticación para todo este controlador
        $this->middleware(function ($request, $next) {
            if (!auth()->check()) {
                // Si es AJAX/JSON, responde 401; si no, redirige al welcome
                if ($request->expectsJson()) {
                    return response()->json([
                        'ok' => false,
                        'message' => 'No autenticado.',
                    ], 401);
                }
                return redirect()->route('home');
            }
            return $next($request);
        });
    }

    // 1.1 Mostrar pantalla con filtros + resultados iniciales
    public function index(Request $request)
    {
        $categorias    = Categoria::activas()->orderBy('nombre')->get(['id','nombre']);
        $transmisiones = ['Manual','Automática'];

        $filtros = [
            'desde'        => $request->input('desde'),
            'hasta'        => $request->input('hasta'),
            'categoria_id' => $request->input('categoria_id'),
            'transmision'  => $request->input('transmision'),
        ];

        $vehiculos = $this->filtrarVehiculos($filtros)
            ->paginate(10)
            ->withQueryString();

        return view('disponibilidad.index', compact('categorias','transmisiones','vehiculos','filtros'));
    }

    // 2.1 Buscar (soporta AJAX o navegación normal)
    public function search(Request $request)
    {
        $vehiculos = $this->filtrarVehiculos($request->all())
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return view('disponibilidad.partials.tabla', compact('vehiculos'))->render();
        }

        $categorias    = Categoria::activas()->orderBy('nombre')->get(['id','nombre']);
        $transmisiones = ['Manual','Automática'];
        $filtros = [
            'desde'        => $request->input('desde'),
            'hasta'        => $request->input('hasta'),
            'categoria_id' => $request->input('categoria_id'),
            'transmision'  => $request->input('transmision'),
        ];

        return view('disponibilidad.index', compact('categorias','transmisiones','vehiculos','filtros'));
    }

    // ----------- CORE: asignar vehículo a categoría -----------
    public function asignarVehiculo(Request $request)
    {
        $data = $request->validate([
            'vehiculo_id'  => ['required','integer','exists:vehiculos,id'],
            'categoria_id' => ['required','integer','exists:categorias,id'],
        ]);

        $categoriaActiva = Categoria::whereKey($data['categoria_id'])
            ->where('activo', true)
            ->exists();

        if (!$categoriaActiva) {
            return response()->json(['ok'=>false,'message'=>'La categoría seleccionada no está activa.'], 422);
        }

        Vehiculo::whereKey($data['vehiculo_id'])
            ->update(['categoria_id'=>$data['categoria_id']]);

        return response()->json(['ok'=>true,'message'=>'Vehículo asignado a la categoría.']);
    }

    // ----------- Stubs (a futuro) -----------
    public function bloquear(Request $request)
    {
        return response()->json(['ok'=>false,'message'=>'Funcionalidad pendiente.'], 501);
    }

    public function desbloquear($id)
    {
        return response()->json(['ok'=>false,'message'=>'Funcionalidad pendiente.'], 501);
    }

    public function programarMantenimiento(Request $request)
    {
        return response()->json(['ok'=>false,'message'=>'Funcionalidad pendiente.'], 501);
    }

    public function liberarMantenimiento($id)
    {
        return response()->json(['ok'=>false,'message'=>'Funcionalidad pendiente.'], 501);
    }

    public function crearReserva(Request $request)
    {
        return response()->json(['ok'=>false,'message'=>'Redirección a Reservas pendiente.'], 501);
    }

    public function crearCotizacion(Request $request)
    {
        return response()->json(['ok'=>false,'message'=>'Redirección a Cotizaciones pendiente.'], 501);
    }

    // ----------- Helper de filtros -----------
    private function filtrarVehiculos(array $f)
    {
        $q = Vehiculo::query()
            ->with(['categoria:id,nombre'])
            ->where('estado','!=','inactivo');

        if (!empty($f['categoria_id'])) {
            $q->where('categoria_id', $f['categoria_id']);
        }

        if (!empty($f['transmision'])) {
            $q->where('transmision', $f['transmision']);
        }

        // TODO: cruzar con reservas/mantenimiento/bloqueos según fechas
        // if (!empty($f['desde']) && !empty($f['hasta'])) { ... }

        return $q->orderBy('marca')->orderBy('modelo');
    }
}

