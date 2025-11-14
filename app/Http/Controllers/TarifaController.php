<?php

namespace App\Http\Controllers;

use App\Models\Tarifa;
use Illuminate\Http\Request;

class TarifaController extends Controller
{
    public function __construct()
    {
        // 🔒 Aplica autenticación obligatoria a todos los métodos
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Aquí puedes listar las tarifas disponibles
        // Ejemplo:
        // $tarifas = Tarifa::all();
        // return view('tarifa.index', compact('tarifas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Muestra el formulario para crear una nueva tarifa
        // return view('tarifa.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Guarda una nueva tarifa en la base de datos
        // $validated = $request->validate([...]);
        // Tarifa::create($validated);
        // return redirect()->route('mostrar.tarifa')->with('success', 'Tarifa creada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Tarifa $tarifa)
    {
        // Muestra los detalles de una tarifa específica
        // return view('tarifa.show', compact('tarifa'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tarifa $tarifa)
    {
        // Muestra el formulario de edición
        // return view('tarifa.edit', compact('tarifa'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tarifa $tarifa)
    {
        // Actualiza una tarifa existente
        // $validated = $request->validate([...]);
        // $tarifa->update($validated);
        // return redirect()->route('mostrar.tarifa')->with('success', 'Tarifa actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tarifa $tarifa)
    {
        // Elimina una tarifa
        // $tarifa->delete();
        // return redirect()->route('mostrar.tarifa')->with('success', 'Tarifa eliminada correctamente.');
    }
}
