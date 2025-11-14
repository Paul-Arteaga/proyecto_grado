<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VehiculoController extends Controller
{
    public function __construct()
    {
        // 🔒 Obliga a estar autenticado para cualquier acción de vehículos
        $this->middleware('auth'); // o 'auth:web' si prefieres ser explícito
    }

    // ---------- FORM CREAR ----------
    public function create()
    {
        $categorias = Categoria::orderBy('nombre')->get(['id','nombre']);
        return view('disponibilidad.crear-vehiculo', compact('categorias'));
    }

    // Alias para tu ruta: disp.vehiculo.create
    public function createFromDisponibilidad(Request $request)
    {
        return $this->create();
    }

    // ---------- GUARDAR ----------
    public function store(Request $request)
    {
        $data = $request->validate([
            'placa'         => ['required','string','max:20','unique:vehiculos,placa'],
            'vin'           => ['nullable','string','max:30','unique:vehiculos,vin'],
            'marca'         => ['required','string','max:60'],
            'modelo'        => ['required','string','max:60'],
            'anio'          => ['nullable','integer','min:1900','max:2100'],
            'color'         => ['nullable','string','max:30'],
            'transmision'   => ['required','in:Manual,Automática'],
            'km_actual'     => ['required','integer','min:0'],
            'combustible'   => ['nullable','string','max:20'],
            'categoria_id'  => ['nullable','integer','exists:categorias,id'],
            'estado'        => ['required','in:disponible,reservado,bloqueado,mantenimiento,inactivo'],
            'observaciones' => ['nullable','string'],
            'precio_diario' => ['required','numeric','min:0'],
            'foto'          => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ]);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('vehiculos', 'public'); // ej: vehiculos/abc.jpg
        }

        Vehiculo::create($data);

        return redirect()->route('disp.index')->with('ok','Vehículo creado.');
    }

    // Alias para tu ruta: disp.vehiculo.store
    public function storeFromDisponibilidad(Request $request)
    {
        return $this->store($request);
    }

    // ---------- FORM EDITAR ----------
    public function edit(Vehiculo $vehiculo)
    {
        $categorias = Categoria::orderBy('nombre')->get(['id','nombre']);
        return view('disponibilidad.editar-vehiculo', compact('vehiculo','categorias'));
    }

    // ---------- ACTUALIZAR ----------
    public function update(Request $request, Vehiculo $vehiculo)
    {
        $data = $request->validate([
            'placa'         => ['required','string','max:20',"unique:vehiculos,placa,{$vehiculo->id}"],
            'vin'           => ['nullable','string','max:30',"unique:vehiculos,vin,{$vehiculo->id}"],
            'marca'         => ['required','string','max:60'],
            'modelo'        => ['required','string','max:60'],
            'anio'          => ['nullable','integer','min:1900','max:2100'],
            'color'         => ['nullable','string','max:30'],
            'transmision'   => ['required','in:Manual,Automática'],
            'km_actual'     => ['required','integer','min:0'],
            'combustible'   => ['nullable','string','max:20'],
            'categoria_id'  => ['nullable','integer','exists:categorias,id'],
            'estado'        => ['required','in:disponible,reservado,bloqueado,mantenimiento,inactivo'],
            'observaciones' => ['nullable','string'],
            'precio_diario' => ['required','numeric','min:0'],
            'foto'          => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ]);

        if ($request->hasFile('foto')) {
            if ($vehiculo->foto && Storage::disk('public')->exists($vehiculo->foto)) {
                Storage::disk('public')->delete($vehiculo->foto);
            }
            $data['foto'] = $request->file('foto')->store('vehiculos', 'public');
        }

        $vehiculo->update($data);

        return back()->with('ok','Vehículo actualizado.');
    }
}
