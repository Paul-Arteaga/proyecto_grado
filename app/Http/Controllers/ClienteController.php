<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function authorizeClientes(): void
    {
        if (!in_array(Auth::user()->id_rol, [1, 3])) {
            abort(403, 'No autorizado.');
        }
    }

    public function index(Request $request)
    {
        $this->authorizeClientes();

        $busqueda = trim($request->query('buscar', ''));
        $rolFiltro = $request->query('rol');

        $clientes = User::with('rol')
            ->when($busqueda !== '', function ($query) use ($busqueda) {
                $query->where(function ($sub) use ($busqueda) {
                    $sub->where('username', 'like', "%{$busqueda}%")
                        ->orWhere('email', 'like', "%{$busqueda}%")
                        ->orWhere('numero_carnet', 'like', "%{$busqueda}%")
                        ->orWhere('id', 'like', "%{$busqueda}%");
                });
            })
            ->when($rolFiltro, function ($query) use ($rolFiltro) {
                $query->where('id_rol', $rolFiltro);
            })
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $roles = Rol::orderBy('nombre')->get();

        return view('cliente.index', [
            'clientes' => $clientes,
            'busqueda' => $busqueda,
            'roles' => $roles,
            'rolFiltro' => $rolFiltro,
        ]);
    }

    public function show(User $cliente)
    {
        $this->authorizeClientes();

        $roles = Rol::orderBy('nombre')->get();

        return view('cliente.show', compact('cliente', 'roles'));
    }

    public function update(Request $request, User $cliente)
    {
        $this->authorizeClientes();

        $data = $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($cliente->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($cliente->id)],
            'numero_carnet' => ['nullable', 'string', 'max:255'],
            'id_rol' => ['required', 'integer', 'exists:rols,id'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        $cliente->update($data);

        return redirect()->route('clientes.show', $cliente)->with('success', 'Datos del cliente actualizados.');
    }

    public function toggleEstado(User $cliente)
    {
        $this->authorizeClientes();

        $cliente->update(['activo' => !$cliente->activo]);

        $mensaje = $cliente->activo ? 'Cliente activado nuevamente.' : 'Cliente desactivado.';

        return back()->with('success', $mensaje);
    }
}


