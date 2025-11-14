<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    /**
     * Maneja la solicitud entrante.
     */
    public function handle(Request $request, Closure $next)
    {
        // Si el usuario no está autenticado
        if (!Auth::check()) {
            // Redirige al login principal (welcome)
            return redirect()->route('home');
        }

        // Si está autenticado, deja continuar la petición
        return $next($request);
    }
}
