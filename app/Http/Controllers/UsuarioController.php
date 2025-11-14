<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    public function __construct()
    {
        // 🔒 Obliga a estar autenticado para cualquier acción de este controlador
        $this->middleware('auth');

        // (Opcional) Si más adelante usas políticas/roles:
        // $this->middleware('can:viewAny,App\Models\Usuario')->only('index');
        // $this->middleware('can:create,App\Models\Usuario')->only(['create','store']);
        // $this->middleware('can:update,usuario')->only(['edit','update']);
        // $this->middleware('can:delete,usuario')->only('destroy');
    }

    /**
     * GET /usuario  -> name: mostrar.usuario
     * Soporta ?search=texto y ?per_page=10
     */
    public function index(Request $request)
    {
        // ✅ Limitar per_page para evitar abusos
        $perPage = (int) $request->input('per_page', 10);
        $perPage = max(5, min($perPage, 50)); // entre 5 y 50

        $search  = trim((string) $request->input('search', ''));

        $query = Usuario::with('rol')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('username', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id');

        // Paginamos y mapeamos cada item a lo que espera la vista
        $page = $query->paginate($perPage)->through(function ($u) {
            return (object) [
                'id'    => $u->id,
                'name'  => $u->name ?? $u->username ?? '—',
                'title' => $u->title ?? null,
                'email' => $u->email,
                'role'  => optional($u->rol)->nombre ?? 'member',
            ];
        });

        return view('usuario.index', [
            'users'    => $page,    // en Blade usa $users como paginator
            'search'   => $search,
            'per_page' => $perPage,
        ]);
    }

    /**
     * GET /usuario/create -> name: crearUsuario.create
     */
    public function create()
    {
        return view('usuario.create');
    }

    /**
     * POST /usuario -> name: guardarNuevo.store
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:usuarios,username'],
            'email'    => ['required', 'email', 'max:255', 'unique:usuarios,email'],
            'password' => ['required', 'string', 'min:6'],
            'id_rol'   => ['nullable', 'integer'],
            'foto'     => ['nullable', 'string', 'max:255'],
            'title'    => ['nullable', 'string', 'max:255'],
        ]);

        $data['password'] = bcrypt($data['password']);

        Usuario::create($data);

        return redirect()->route('mostrar.usuario')->with('ok', 'Usuario creado');
    }

    /**
     * GET /usuario/{usuario}/edit -> name: editarUsuario.edit
     */
    public function edit(Usuario $usuario)
    {
        return view('usuario.edit', compact('usuario'));
    }

    /**
     * PATCH /usuario/{usuario} -> name: guardarEdicion.update
     */
    public function update(Request $request, Usuario $usuario)
    {
        $data = $request->validate([
            'username' => [
                'sometimes', 'string', 'max:255',
                Rule::unique('usuarios', 'username')->ignore($usuario->id, 'id'),
            ],
            'email' => [
                'sometimes', 'email', 'max:255',
                Rule::unique('usuarios', 'email')->ignore($usuario->id, 'id'),
            ],
            'password' => ['nullable', 'string', 'min:6'],
            'id_rol'   => ['nullable', 'integer'],
            'foto'     => ['nullable', 'string', 'max:255'],
            'title'    => ['nullable', 'string', 'max:255'],
        ]);

        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        $usuario->update($data);

        return redirect()->route('mostrar.usuario')->with('ok', 'Usuario actualizado');
    }

    /**
     * DELETE /usuario/{usuario}
     */
    public function destroy(Usuario $usuario)
    {
        $usuario->delete();
        return back()->with('ok', 'Usuario eliminado');
    }
}
