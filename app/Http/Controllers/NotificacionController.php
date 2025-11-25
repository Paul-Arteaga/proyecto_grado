<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificacionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Obtiene las notificaciones no leídas (para el dropdown del navbar)
     */
    public function index()
    {
        $user = Auth::user();
        
        // Todos los usuarios ven sus propias notificaciones (admin/recepcionistas reciben notificaciones de solicitudes)
        $notificaciones = Notificacion::where('user_id', $user->id)
            ->where('leida', false)
            ->with(['reserva.vehiculo', 'reserva.user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json($notificaciones);
    }

    /**
     * Marca una notificación como leída
     */
    public function marcarLeida(Notificacion $notificacion)
    {
        $notificacion->update([
            'leida' => true,
            'leida_por' => Auth::id(),
            'leida_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    /**
     * Marca todas las notificaciones como leídas
     */
    public function marcarTodasLeidas()
    {
        $user = Auth::user();
        
        // Marcar todas las notificaciones del usuario como leídas
        Notificacion::where('user_id', $user->id)
            ->where('leida', false)
            ->update([
                'leida' => true,
                'leida_por' => $user->id,
                'leida_at' => now(),
            ]);

        return response()->json(['ok' => true]);
    }

    /**
     * Cuenta de notificaciones no leídas
     */
    public function contar()
    {
        $user = Auth::user();
        
        // Contar notificaciones no leídas del usuario
        $count = Notificacion::where('user_id', $user->id)
            ->where('leida', false)
            ->count();

        return response()->json(['count' => $count]);
    }
}
