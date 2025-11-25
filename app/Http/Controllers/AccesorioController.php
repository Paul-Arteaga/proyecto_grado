<?php

namespace App\Http\Controllers;

use App\Models\Accesorio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AccesorioController extends Controller
{
    /**
     * Vista para admin/recepcionista: Lista de accesorios (CRUD)
     */
    public function index()
    {
        // Solo admin y recepcionista pueden ver esta vista
        if (!in_array(Auth::user()->id_rol, [1, 3])) {
            abort(403, 'No autorizado.');
        }

        $accesorios = Accesorio::orderBy('nombre')->get();
        return view('accesorio.index', compact('accesorios'));
    }

    /**
     * Vista para usuarios: Solo lectura de accesorios disponibles
     */
    public function catalogo(Request $request)
    {
        $accesorios = Accesorio::where('activo', true)
            ->orderBy('nombre')
            ->get();
        
        // Obtener reservas confirmadas del usuario para agregar accesorios
        $reservasConfirmadas = \App\Models\Reserva::where('user_id', Auth::id())
            ->where('estado', 'confirmada')
            ->with('vehiculo')
            ->orderBy('fecha_inicio', 'desc')
            ->get();
        
        // Si viene un reserva_id en la URL, es porque el usuario hizo clic en "Agregar accesorio" desde una reserva específica
        $reservaSeleccionada = null;
        if ($request->has('reserva_id')) {
            $reservaSeleccionada = $reservasConfirmadas->firstWhere('id', $request->reserva_id);
        }
        
        return view('accesorio.catalogo', compact('accesorios', 'reservasConfirmadas', 'reservaSeleccionada'));
    }

    /**
     * Crear nuevo accesorio
     */
    public function store(Request $request)
    {
        // Solo admin y recepcionista
        if (!in_array(Auth::user()->id_rol, [1, 3])) {
            abort(403, 'No autorizado.');
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'imagen' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'stock' => 'nullable|integer|min:0',
            'activo' => 'boolean',
        ]);

        $data = $request->only(['nombre', 'descripcion', 'precio', 'stock', 'activo']);
        $data['activo'] = $request->has('activo') ? true : false;

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('accesorios', 'public');
        }

        Accesorio::create($data);

        return redirect()->route('accesorio.index')->with('success', 'Accesorio creado exitosamente.');
    }

    /**
     * Actualizar accesorio
     */
    public function update(Request $request, Accesorio $accesorio)
    {
        // Solo admin y recepcionista
        if (!in_array(Auth::user()->id_rol, [1, 3])) {
            abort(403, 'No autorizado.');
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'imagen' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'stock' => 'nullable|integer|min:0',
            'activo' => 'boolean',
        ]);

        $data = $request->only(['nombre', 'descripcion', 'precio', 'stock', 'activo']);
        $data['activo'] = $request->has('activo') ? true : false;

        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si existe
            if ($accesorio->imagen && Storage::disk('public')->exists($accesorio->imagen)) {
                Storage::disk('public')->delete($accesorio->imagen);
            }
            $data['imagen'] = $request->file('imagen')->store('accesorios', 'public');
        }

        $accesorio->update($data);

        return redirect()->route('accesorio.index')->with('success', 'Accesorio actualizado exitosamente.');
    }

    /**
     * Eliminar accesorio
     */
    public function destroy(Accesorio $accesorio)
    {
        // Solo admin y recepcionista
        if (!in_array(Auth::user()->id_rol, [1, 3])) {
            abort(403, 'No autorizado.');
        }

        // Eliminar imagen si existe
        if ($accesorio->imagen && Storage::disk('public')->exists($accesorio->imagen)) {
            Storage::disk('public')->delete($accesorio->imagen);
        }

        $accesorio->delete();

        return redirect()->route('accesorio.index')->with('success', 'Accesorio eliminado exitosamente.');
    }
}
