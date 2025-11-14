<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservaController extends Controller
{
    public function __construct()
    {
        // 🔒 Obliga a estar autenticado para cualquier acción
        $this->middleware('auth');
    }

    // lista
    public function index()
    {
        $reservas = Reserva::with(['user', 'vehiculo'])
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        return view('reservas.index', compact('reservas'));
    }

    // crea desde el calendario del index
    public function store(Request $request)
    {
        $request->validate([
            'vehiculo_id' => 'required|integer',
            'fechas'      => 'required|array|min:1',
            'fechas.*'    => 'date',
        ]);

        $user = Auth::user(); // seguro existe por el middleware

        // ordenamos las fechas para guardar como rango
        $fechas = collect($request->fechas)->sort()->values();
        $fechaInicio = $fechas->first();
        $fechaFin    = $fechas->last();

        // verificar que ninguna de esas fechas esté ocupada
        $existe = Reserva::where('vehiculo_id', $request->vehiculo_id)
            ->where(function ($q) use ($fechaInicio, $fechaFin) {
                // solapa si: inicio_nuevo <= fin_bd  Y  fin_nuevo >= inicio_bd
                $q->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin])
                  ->orWhereBetween('fecha_fin', [$fechaInicio, $fechaFin])
                  ->orWhere(function($q2) use ($fechaInicio, $fechaFin){
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

        // crear la reserva
        Reserva::create([
            'user_id'     => $user->id,
            'vehiculo_id' => $request->vehiculo_id,
            'fecha_inicio'=> $fechaInicio,
            'fecha_fin'   => $fechaFin,
            'estado'      => 'confirmada',
        ]);

        return response()->json(['ok' => true]);
    }

    // editar (lo usas en /reservas)
    public function update(Request $request, Reserva $reserva)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after_or_equal:fecha_inicio',
        ]);

        // (Opcional) Solo dueño o rol con permiso pueden editar
        // if (Auth::id() !== $reserva->user_id && !Auth::user()->can('reservas.editar')) {
        //     abort(403, 'No autorizado.');
        // }

        // verificar solape con otras reservas del mismo vehículo
        $existe = Reserva::where('vehiculo_id', $reserva->vehiculo_id)
            ->where('id', '!=', $reserva->id)
            ->where(function ($q) use ($request) {
                $fi = $request->fecha_inicio;
                $ff = $request->fecha_fin;

                $q->whereBetween('fecha_inicio', [$fi, $ff])
                  ->orWhereBetween('fecha_fin', [$fi, $ff])
                  ->orWhere(function($q2) use ($fi, $ff){
                      $q2->where('fecha_inicio', '<=', $fi)
                         ->where('fecha_fin', '>=', $ff);
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
            'fecha_fin'    => $request->fecha_fin,
        ]);

        return response()->json(['ok' => true]);
    }

    // eliminar
    public function destroy(Reserva $reserva)
    {
        // (Opcional) Solo dueño o rol con permiso pueden eliminar
        // if (Auth::id() !== $reserva->user_id && !Auth::user()->can('reservas.eliminar')) {
        //     abort(403, 'No autorizado.');
        // }

        $reserva->delete();

        return redirect()
            ->route('reservas.index')
            ->with('ok', 'Reserva eliminada');
    }
}
