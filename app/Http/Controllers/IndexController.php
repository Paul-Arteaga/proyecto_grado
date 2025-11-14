<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Vehiculo;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function __construct()
    {
        // 🔒 Obliga a autenticarse antes de acceder a cualquier método
        $this->middleware('auth');
    }

    public function index()
    {
        // 🔹 Categorías activas para el carrusel
        $categorias = Categoria::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'descripcion', 'imagen']);

        // 🔹 Vehículos disponibles para mostrar en el index
        $vehiculos = Vehiculo::with('categoria:id,nombre')
            ->where('estado', '!=', 'inactivo')
            ->latest('id')
            ->take(10)
            ->get([
                'id',
                'marca',
                'modelo',
                'transmision',
                'km_actual',
                'foto',
                'estado',
                'categoria_id',
                'precio_diario'
            ]);

        // 🔹 Enviamos ambos conjuntos a la vista
        return view('index.index', compact('categorias', 'vehiculos'));
    }
}
