<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Http\Request;

class RolController extends Controller
{
    public function __construct()
    {
        // 🔒 Protege todas las rutas de este controlador
        // Solo usuarios autenticados pueden acceder
        $this->middleware('auth');
    }

    public function index()
    {
        return view("rol.index");
    }

    public function store(Request $request)
    {
        // Aquí podrías manejar la creación de nuevos roles
    }

    public function update(Request $request, Rol $rol)
    {
        // Aquí podrías manejar la actualización de un rol existente
    }

    public function destroy(Rol $rol)
    {
        // Aquí podrías manejar la eliminación de un rol
    }
}
