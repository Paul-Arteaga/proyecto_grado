<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Maneja la solicitud entrante.
     * Si el usuario ya está autenticado, redirige al dashboard.
     */
    public function handle(Request $request, Closure $next)
    {
        // Si el usuario está autenticado
        if (Auth::check()) {
            // Redirige al dashboard
            return redirect()->route('mostrar.index');
        }

        // Si no está autenticado, deja continuar la petición
        return $next($request);
    }
}

