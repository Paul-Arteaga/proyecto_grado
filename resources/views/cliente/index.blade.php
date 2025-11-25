@extends('layout.navbar')

@section('titulo', 'Clientes')

@section('contenido')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Clientes</h1>
            <p class="text-sm text-gray-600">Gestiona la información y estado de todos los usuarios.</p>
        </div>
        <form method="GET" action="{{ route('clientes.index') }}" class="w-full lg:w-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <div class="relative flex-1 sm:flex-none sm:w-64">
                <input type="text" name="buscar" value="{{ request('buscar') }}"
                       placeholder="Buscar por nombre, email o carnet..."
                       class="w-full border border-gray-300 rounded-lg pl-3 pr-10 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @if(request('buscar'))
                    <a href="{{ route('clientes.index') }}" class="absolute inset-y-0 right-8 flex items-center text-gray-400 hover:text-gray-600">
                        ×
                    </a>
                @endif
                <span class="absolute inset-y-0 right-3 flex items-center text-gray-400">
                    🔍
                </span>
            </div>
            <div class="relative flex-1 sm:flex-none sm:w-48">
                <select name="rol"
                        class="w-full border border-gray-300 rounded-lg pl-3 pr-8 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos los roles</option>
                    @foreach($roles as $rol)
                        <option value="{{ $rol->id }}" {{ request('rol') == $rol->id ? 'selected' : '' }}>
                            {{ ucfirst($rol->nombre) }}
                        </option>
                    @endforeach
                </select>
                @if(request('rol'))
                    <a href="{{ route('clientes.index', ['buscar' => request('buscar')]) }}" class="absolute inset-y-0 right-7 flex items-center text-gray-400 hover:text-gray-600 text-lg leading-none">
                        ×
                    </a>
                @endif
                <span class="absolute inset-y-0 right-2 flex items-center text-gray-400">
                    🎯
                </span>
            </div>
            <div class="flex gap-2">
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700">
                    Aplicar
                </button>
                @if(request()->has('buscar') || request()->has('rol'))
                    <a href="{{ route('clientes.index') }}"
                       class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                        Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="hidden md:block">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Contacto</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($clientes as $cliente)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-900">{{ $cliente->username }}</div>
                                <div class="text-xs text-gray-500">ID: {{ $cliente->id }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-700">{{ $cliente->email ?? 'Sin correo' }}</div>
                                <div class="text-xs text-gray-500">Carnet: {{ $cliente->numero_carnet ?? '—' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700">
                                    {{ $cliente->rol->nombre ?? 'Sin rol' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($cliente->activo)
                                    <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Activo</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">Inactivo</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <a href="{{ route('clientes.show', $cliente) }}"
                                   class="text-blue-600 hover:text-blue-800 text-sm font-medium">Ver / Editar</a>
                                <form action="{{ route('clientes.toggle', $cliente) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="text-sm font-medium {{ $cliente->activo ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800' }}"
                                            onclick="return confirm('¿Seguro que deseas {{ $cliente->activo ? 'desactivar' : 'activar' }} este cliente?')">
                                        {{ $cliente->activo ? 'Desactivar' : 'Activar' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">No se encontraron clientes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="md:hidden divide-y divide-gray-200">
            @forelse($clientes as $cliente)
                <div class="p-4 space-y-2">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $cliente->username }}</p>
                            <p class="text-xs text-gray-500">ID: {{ $cliente->id }}</p>
                        </div>
                        @if($cliente->activo)
                            <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Activo</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">Inactivo</span>
                        @endif
                    </div>
                    <div class="text-sm text-gray-700">
                        <p>Email: {{ $cliente->email ?? 'Sin correo' }}</p>
                        <p>Carnet: {{ $cliente->numero_carnet ?? '—' }}</p>
                        <p>Rol: {{ $cliente->rol->nombre ?? 'Sin rol' }}</p>
                    </div>
                    <div class="flex gap-3 pt-2 border-t">
                        <a href="{{ route('clientes.show', $cliente) }}"
                           class="flex-1 text-center px-3 py-2 bg-blue-600 text-white rounded text-sm">Ver / Editar</a>
                        <form action="{{ route('clientes.toggle', $cliente) }}" method="POST" class="flex-1"
                              onsubmit="return confirm('¿Seguro que deseas {{ $cliente->activo ? 'desactivar' : 'activar' }} este cliente?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="w-full px-3 py-2 text-sm rounded {{ $cliente->activo ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                {{ $cliente->activo ? 'Desactivar' : 'Activar' }}
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-gray-500">No se encontraron clientes.</div>
            @endforelse
        </div>
    </div>

    <div class="mt-4">
        {{ $clientes->links() }}
    </div>
</div>
@endsection


