<?php

namespace App\Http\Controllers;

use App\Models\RolPermiso;
use Illuminate\Http\Request;

class RolPermisoController extends Controller
{
    public function __construct()
    {
        // 🔒 Requiere autenticación para acceder a cualquier acción de este controlador
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Aquí podrías listar los permisos por rol, por ejemplo
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(RolPermiso $rolPermiso)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RolPermiso $rolPermiso)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RolPermiso $rolPermiso)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RolPermiso $rolPermiso)
    {
        //
    }
}
