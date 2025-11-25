@extends('layout.navbar')

@section('titulo', 'Detalle del Cliente')

@section('contenido')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Detalle del Cliente</h1>
            <p class="text-sm text-gray-600">Consulta y actualiza la información del usuario.</p>
        </div>
        <a href="{{ route('clientes.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold">
            ← Volver al listado
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white shadow rounded-lg p-4 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-2xl font-bold">
                    {{ strtoupper(substr($cliente->username, 0, 1)) }}
                </div>
                <div>
                    <p class="text-lg font-semibold text-gray-900">{{ $cliente->username }}</p>
                    <p class="text-sm text-gray-500">ID: {{ $cliente->id }}</p>
                </div>
            </div>
            <div class="space-y-1 text-sm text-gray-700">
                <p><span class="text-gray-500">Correo:</span> {{ $cliente->email ?? 'Sin correo' }}</p>
                <p><span class="text-gray-500">Carnet:</span> {{ $cliente->numero_carnet ?? '—' }}</p>
                <p><span class="text-gray-500">Rol:</span> {{ $cliente->rol->nombre ?? 'Sin rol' }}</p>
                <p><span class="text-gray-500">Estado:</span>
                    @if($cliente->activo)
                        <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Activo</span>
                    @else
                        <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">Inactivo</span>
                    @endif
                </p>
            </div>
            <form action="{{ route('clientes.toggle', $cliente) }}" method="POST"
                  onsubmit="return confirm('¿Seguro que deseas {{ $cliente->activo ? 'desactivar' : 'activar' }} este cliente?')">
                @csrf
                @method('PATCH')
                <button type="submit"
                        class="w-full px-4 py-2 rounded-lg text-sm font-semibold {{ $cliente->activo ? 'bg-red-600 text-white hover:bg-red-700' : 'bg-green-600 text-white hover:bg-green-700' }}">
                    {{ $cliente->activo ? 'Desactivar cliente' : 'Activar cliente' }}
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white shadow rounded-lg p-4 sm:p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Editar información</h2>
            <form action="{{ route('clientes.update', $cliente) }}" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de usuario</label>
                        <input type="text" name="username" value="{{ old('username', $cliente->username) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        @error('username') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
                        <input type="email" name="email" value="{{ old('email', $cliente->email) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Número de carnet</label>
                        <input type="text" name="numero_carnet" value="{{ old('numero_carnet', $cliente->numero_carnet) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('numero_carnet') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                        <select name="id_rol"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            @foreach($roles as $rol)
                                <option value="{{ $rol->id }}" {{ old('id_rol', $cliente->id_rol) == $rol->id ? 'selected' : '' }}>
                                    {{ $rol->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_rol') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña (opcional)</label>
                    <input type="password" name="password"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Dejar en blanco para mantener la actual">
                    @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('clientes.index') }}"
                       class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">Cancelar</a>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


