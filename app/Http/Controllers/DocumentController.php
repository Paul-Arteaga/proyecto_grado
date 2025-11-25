<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function authorizeDocumentos(): void
    {
        if (!in_array(Auth::user()->id_rol, [1, 3])) {
            abort(403, 'No autorizado.');
        }
    }

    public function index(Request $request)
    {
        $this->authorizeDocumentos();

        $busqueda = trim($request->query('buscar', ''));
        $estado = $request->query('estado', '');

        $usuarios = User::query()
            ->with('rol')
            ->when($busqueda !== '', function ($query) use ($busqueda) {
                $query->where(function ($sub) use ($busqueda) {
                    $sub->where('name', 'like', "%{$busqueda}%")
                        ->orWhere('username', 'like', "%{$busqueda}%")
                        ->orWhere('email', 'like', "%{$busqueda}%")
                        ->orWhere('numero_carnet', 'like', "%{$busqueda}%");
                });
            })
            ->when($estado === 'verificado', fn ($q) => $q->where('documentos_verificados', true))
            ->when($estado === 'pendiente', fn ($q) => $q->where(function ($sub) {
                $sub->whereNull('documentos_verificados')->orWhere('documentos_verificados', false);
            }))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('documento.index', [
            'usuarios' => $usuarios,
            'busqueda' => $busqueda,
            'estado' => $estado,
        ]);
    }
}


