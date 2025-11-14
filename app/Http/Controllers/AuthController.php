<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function __construct()
    {
        // 🔓 Deja welcome accesible para todos (NO aplicar guest aquí)
        // 🔐 Solo invitados pueden acceder a login/registro
        $this->middleware('guest')->only(['login', 'showRegister', 'storeRegister']);

        // 🔒 Solo autenticados pueden hacer logout
        $this->middleware('auth')->only(['logout']);
    }

    /** Pantalla principal (welcome con login embebido) */
    public function welcome()
    {
        // Siempre mostrar el welcome (aunque esté logueado)
        return view('welcome');
    }

    /** Login desde el formulario del welcome */
    public function login(Request $request)
    {
        // Validación
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Buscar usuario por username
        $user = User::where('username', $credentials['username'])->first();
        if (!$user) {
            return back()->withErrors(['errorUser' => 'El usuario no existe'])->withInput();
        }

        // Verificar contraseña
        if (!password_verify($credentials['password'], $user->password)) {
            return back()->withErrors(['errorCred' => 'Credenciales inválidas'])->withInput();
        }

        // Autenticación
        Auth::login($user);
        $request->session()->regenerate();

        // Redirige al dashboard (index)
        return redirect()->route('mostrar.index');
    }

    /** Mostrar formulario de registro */
    public function showRegister()
    {
        return view('auth.register');
    }

    /** Procesar registro */
    public function storeRegister(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required','string','min:3','max:50','unique:users,username'],
            'email'    => ['nullable','email', Rule::unique('users','email')],
            'password' => ['required','string','min:8','confirmed'],
            'id_rol'   => ['nullable','integer'],
        ], [
            'username.required' => 'El usuario es obligatorio.',
            'username.unique'   => 'Ese usuario ya existe.',
            'password.confirmed'=> 'Las contraseñas no coinciden.',
        ]);

        // Rol por defecto (ej. 2 = cliente)
        $validated['id_rol'] = $validated['id_rol'] ?? 2;

        // Crea el usuario (hash automático gracias al cast en el modelo)
        $user = User::create([
            'username' => $validated['username'],
            'email'    => $validated['email'] ?? null,
            'password' => $validated['password'], // el modelo hace el hash (casts['password' => 'hashed'])
            'id_rol'   => $validated['id_rol'],
        ]);

        // Inicia sesión automáticamente
        Auth::login($user);
        $request->session()->regenerate();

        // Muestra animación de éxito y redirige al INDEX (dashboard)
        return response()->view('auth.register-success');
    }

    /** Cerrar sesión */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Regresar al welcome (pantalla principal)
        return redirect()->route('home');
    }
}

