<?php

namespace App\Http\Controllers;

use App\Models\Permiso;
use Illuminate\Http\Request;

class PermisoController extends Controller
{
    public function __construct()
    {
        // 🔒 Middleware para requerir autenticación
        $this->middleware('auth');
    }

    public function index()
    {
        // Listado de permisos
        $permisos = Permiso::all();
        return view('permiso.index', compact('permisos'));
    }

    public function store(Request $request)
    {
        // Crear un nuevo permiso (si lo implementas luego)
    }

    public function show(Permiso $permiso)
    {
        // Mostrar detalles de un permiso específico
    }

    public function update(Request $request, Permiso $permiso)
    {
        // Actualizar permiso existente
    }

    public function destroy(Permiso $permiso)
    {
        // Eliminar permiso (si lo implementas luego)
    }
}
