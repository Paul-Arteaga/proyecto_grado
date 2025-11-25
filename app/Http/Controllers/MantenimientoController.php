<?php

namespace App\Http\Controllers;

use App\Models\Mantenimiento;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MantenimientoController extends Controller
{
    private int $kmThreshold = 10000;

    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function authorizeAccess(): void
    {
        if (!in_array(Auth::user()->id_rol, [1, 4])) {
            abort(403, 'No autorizado.');
        }
    }

    public function index()
    {
        $this->authorizeAccess();

        $vehiculos = Vehiculo::with(['categoria', 'mantenimientos' => function ($query) {
            $query->orderByDesc('created_at');
        }])->get()->filter(function ($vehiculo) {
            $diff = ($vehiculo->km_actual - $vehiculo->km_ultimo_mantenimiento);
            return $vehiculo->estado === 'mantenimiento' || $diff >= $this->kmThreshold;
        })->map(function ($vehiculo) {
            $vehiculo->km_diff = max(0, $vehiculo->km_actual - $vehiculo->km_ultimo_mantenimiento);
            $vehiculo->mantenimiento_activo = $vehiculo->mantenimientos->firstWhere('estado', 'en_progreso');
            return $vehiculo;
        });

        return view('mantenimiento.index', [
            'vehiculos' => $vehiculos,
            'kmThreshold' => $this->kmThreshold,
        ]);
    }

    public function derivar(Request $request, Vehiculo $vehiculo)
    {
        $this->authorizeAccess();

        if ($vehiculo->estado === 'mantenimiento') {
            return back()->withErrors(['error' => 'El vehículo ya está en mantenimiento.']);
        }

        $diff = $vehiculo->km_actual - $vehiculo->km_ultimo_mantenimiento;
        if ($diff < $this->kmThreshold) {
            return back()->withErrors(['error' => 'El vehículo aún no alcanzó los ' . $this->kmThreshold . ' km para mantenimiento.']);
        }

        $request->validate([
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $mantenimiento = Mantenimiento::create([
            'vehiculo_id' => $vehiculo->id,
            'km_inicio' => $vehiculo->km_actual,
            'estado' => 'en_progreso',
            'observaciones' => $request->observaciones,
        ]);

        $vehiculo->update(['estado' => 'mantenimiento']);

        return back()->with('success', 'Vehículo derivado a mantenimiento.');
    }

    public function completar(Request $request, Mantenimiento $mantenimiento)
    {
        $this->authorizeAccess();

        if ($mantenimiento->estado === 'completado') {
            return back()->withErrors(['error' => 'Este mantenimiento ya fue completado.']);
        }

        $request->validate([
            'km_fin' => 'required|integer|min:' . $mantenimiento->km_inicio,
            'checks' => 'nullable|array',
            'observaciones' => 'nullable|string|max:1000',
        ], [
            'km_fin.min' => 'El kilometraje final no puede ser menor al de inicio.',
        ]);

        $vehiculo = $mantenimiento->vehiculo;

        $mantenimiento->update([
            'km_fin' => $request->km_fin,
            'estado' => 'completado',
            'checks' => $request->checks ?? [],
            'observaciones' => $request->observaciones,
            'realizado_por' => Auth::id(),
        ]);

        if ($vehiculo) {
            $vehiculo->update([
                'estado' => 'disponible',
                'km_actual' => max($vehiculo->km_actual, $request->km_fin),
                'km_ultimo_mantenimiento' => $request->km_fin,
            ]);
        }

        return back()->with('success', 'Mantenimiento completado correctamente.');
    }
}


