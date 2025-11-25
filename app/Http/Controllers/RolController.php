<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RolController extends Controller
{
    public function __construct()
    {
        // 🔒 Solo usuarios autenticados pueden acceder
        $this->middleware('auth');
        
        // 🔐 Solo administradores pueden gestionar roles
        $this->middleware('admin');
    }

    /**
     * Muestra la lista de roles y usuarios con sus roles asignados
     */
    public function index(Request $request)
    {
        $roles = Rol::orderBy('id')->get();
        
        // Búsqueda por número de carnet
        $search = $request->input('search', '');
        $mostrarTodos = $request->has('mostrar_todos');
        
        // Query base
        $query = User::with('rol');
        
        // Aplicar búsqueda si existe
        if ($search) {
            $query->where('numero_carnet', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
        }
        
        // Ordenar por número de carnet
        $query->orderBy('numero_carnet');
        
        // Si no se solicita mostrar todos, limitar a 10 usuarios
        if (!$mostrarTodos) {
            $usuarios = $query->limit(10)->get();
            $totalUsuarios = User::count();
        } else {
            $usuarios = $query->get();
            $totalUsuarios = $usuarios->count();
        }
        
        return view('rol.index', compact('roles', 'usuarios', 'search', 'mostrarTodos', 'totalUsuarios'));
    }

    /**
     * Asigna un rol a un usuario
     * POST /rol/asignar
     */
    public function asignarRol(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'rol_id' => 'required|exists:rols,id',
        ]);

        $usuario = User::findOrFail($request->user_id);
        $rol = Rol::findOrFail($request->rol_id);

        // Actualizar el rol del usuario
        $usuario->update(['id_rol' => $rol->id]);

        return back()->with('success', "Rol '{$rol->nombre}' asignado correctamente a {$usuario->username}.");
    }

    /**
     * Actualiza el rol de un usuario
     * PATCH /rol/usuario/{usuario}
     */
    public function actualizarRolUsuario(Request $request, User $usuario)
    {
        $request->validate([
            'id_rol' => 'required|exists:rols,id',
        ]);

        $rol = Rol::findOrFail($request->id_rol);
        $usuario->update(['id_rol' => $rol->id]);

        return back()->with('success', "Rol actualizado correctamente a '{$rol->nombre}' para {$usuario->username}.");
    }

    /**
     * Muestra el formulario para crear un nuevo rol (opcional)
     */
    public function create()
    {
        return view('rol.create');
    }

    /**
     * Almacena un nuevo rol
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:rols,nombre',
        ]);

        Rol::create(['nombre' => $request->nombre]);

        return redirect()->route('mostrar.rol')->with('success', 'Rol creado correctamente.');
    }

    /**
     * Muestra el formulario para editar un rol
     */
    public function edit(Rol $rol)
    {
        return view('rol.edit', compact('rol'));
    }

    /**
     * Actualiza un rol existente
     */
    public function update(Request $request, Rol $rol)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:rols,nombre,' . $rol->id,
        ]);

        $rol->update(['nombre' => $request->nombre]);

        return redirect()->route('mostrar.rol')->with('success', 'Rol actualizado correctamente.');
    }

    /**
     * Elimina un rol (solo si no tiene usuarios asignados)
     */
    public function destroy(Rol $rol)
    {
        // Verificar que no sea el rol admin (id = 1)
        if ($rol->id === 1) {
            return back()->with('error', 'No se puede eliminar el rol de administrador.');
        }

        // Verificar que no tenga usuarios asignados
        $usuariosConRol = User::where('id_rol', $rol->id)->count();
        if ($usuariosConRol > 0) {
            return back()->with('error', "No se puede eliminar el rol porque tiene {$usuariosConRol} usuario(s) asignado(s).");
        }

        $rol->delete();

        return back()->with('success', 'Rol eliminado correctamente.');
    }
}
