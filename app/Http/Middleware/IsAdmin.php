<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    /**
     * Verifica que el usuario autenticado tenga el rol de admin.
     * El rol admin debe tener id_rol = 1
     */
    public function handle(Request $request, Closure $next)
    {
        // Verificar que el usuario esté autenticado
        if (!Auth::check()) {
            return redirect()->route('home')->with('error', 'Debes iniciar sesión para acceder a esta sección.');
        }

        // Verificar que el usuario tenga rol de admin (id_rol = 1)
        $user = Auth::user();
        if ($user->id_rol !== 1) {
            return redirect()->route('mostrar.index')->with('error', 'No tienes permisos para acceder a esta sección. Solo los administradores pueden gestionar roles.');
        }

        return $next($request);
    }
}

