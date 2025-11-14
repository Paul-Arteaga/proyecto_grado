<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * Los middlewares de la aplicación.
     */
    protected $middleware = [
        // Puedes dejarlo vacío por ahora
    ];

    /**
     * Middlewares asignables a rutas específicas.
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
    ];
}
