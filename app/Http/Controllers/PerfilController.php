<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PerfilController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Página principal del perfil
     */
    public function index()
    {
        $user = Auth::user();
        return view('perfil.index', compact('user'));
    }

    /**
     * Mis Reservas
     */
    public function misReservas()
    {
        $user = Auth::user();
        $reservas = Reserva::where('user_id', $user->id)
            ->with(['vehiculo', 'accesorios'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('perfil.mis-reservas', compact('reservas'));
    }

    /**
     * Mis Datos
     */
    public function misDatos()
    {
        $user = Auth::user();
        return view('perfil.mis-datos', compact('user'));
    }
}
