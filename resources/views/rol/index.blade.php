@extends('layout.navbar')

@section('titulo', 'Gestión de Roles')

@section('contenido')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  
  {{-- Título y Botón --}}
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Gestión de Roles</h1>
    <a href="{{ route('crearRol.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg transition shadow-sm hover:shadow-md">
      + Agregar Rol
    </a>
  </div>

  {{-- Mensajes de éxito/error --}}
  @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
      {{ session('success') }}
    </div>
  @endif

  @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
      {{ session('error') }}
    </div>
  @endif

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    
    {{-- Panel: Lista de Roles --}}
    <div class="bg-white rounded-lg shadow-md p-6">
      <h2 class="text-xl font-semibold text-gray-800 mb-4">Roles Disponibles</h2>
      
      <div class="space-y-3">
        @foreach($roles as $rol)
          <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
            <div class="flex items-center space-x-3">
              <div class="w-3 h-3 rounded-full 
                @if($rol->id == 1) bg-red-500
                @elseif($rol->id == 2) bg-blue-500
                @elseif($rol->id == 3) bg-green-500
                @else bg-yellow-500
                @endif">
              </div>
              <span class="font-medium text-gray-800">{{ ucfirst($rol->nombre) }}</span>
              <span class="text-sm text-gray-500">(ID: {{ $rol->id }})</span>
            </div>
            <div class="flex items-center space-x-2">
              <a href="{{ route('editarRol.edit', $rol) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                Editar
              </a>
              @if($rol->id != 1)
                <form action="{{ route('rol.destroy', $rol) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este rol?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                    Eliminar
                  </button>
                </form>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    </div>

    {{-- Panel: Asignar Roles a Usuarios --}}
    <div class="bg-white rounded-lg shadow-md p-6">
      <h2 class="text-xl font-semibold text-gray-800 mb-4">Asignar Roles a Usuarios</h2>
      
      {{-- Buscador --}}
      <form method="GET" action="{{ route('mostrar.rol') }}" class="mb-4">
        <div class="flex items-center space-x-2">
          <div class="flex-1 relative">
            <input 
              type="text" 
              name="search" 
              value="{{ $search }}"
              placeholder="Buscar por número de carnet o usuario..."
              class="w-full border border-gray-300 rounded-lg px-4 py-2 pl-10 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
            Buscar
          </button>
          @if($search)
            <a href="{{ route('mostrar.rol') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg transition">
              Limpiar
            </a>
          @endif
        </div>
      </form>

      {{-- Mostrar todos / Ocultar --}}
      @if(!$mostrarTodos && isset($totalUsuarios) && $totalUsuarios > 10)
        <div class="mb-4 text-center">
          <p class="text-sm text-gray-600 mb-2">Mostrando 10 de {{ $totalUsuarios }} usuarios</p>
          <a href="{{ route('mostrar.rol', array_merge(request()->query(), ['mostrar_todos' => true])) }}" 
             class="text-blue-600 hover:text-blue-800 font-medium text-sm">
            Mostrar todos los usuarios
          </a>
        </div>
      @elseif($mostrarTodos)
        <div class="mb-4 text-center">
          <a href="{{ route('mostrar.rol', request()->except('mostrar_todos')) }}" 
             class="text-blue-600 hover:text-blue-800 font-medium text-sm">
            Ocultar usuarios (mostrar solo 10)
          </a>
        </div>
      @endif
      
      <div class="space-y-4 max-h-96 overflow-y-auto">
        @forelse($usuarios as $usuario)
          <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition">
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                  <span class="text-gray-600 font-semibold">{{ strtoupper(substr($usuario->username, 0, 1)) }}</span>
                </div>
                <div>
                  <p class="font-medium text-gray-900">{{ $usuario->username }}</p>
                  <p class="text-sm text-gray-600">Carnet: <span class="font-semibold">{{ $usuario->numero_carnet ?? 'N/A' }}</span></p>
                  @if($usuario->email)
                    <p class="text-xs text-gray-500">{{ $usuario->email }}</p>
                  @endif
                </div>
              </div>
            </div>
            
            <form action="{{ route('rol.actualizarUsuario', $usuario) }}" method="POST" class="mt-3">
              @csrf
              @method('PATCH')
              <div class="flex items-center space-x-3">
                <select name="id_rol" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                  <option value="">Sin rol</option>
                  @foreach($roles as $rol)
                    <option value="{{ $rol->id }}" {{ $usuario->id_rol == $rol->id ? 'selected' : '' }}>
                      {{ ucfirst($rol->nombre) }}
                    </option>
                  @endforeach
                </select>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                  Asignar
                </button>
              </div>
            </form>
            
            @if($usuario->rol)
              <p class="mt-2 text-xs text-gray-500">
                Rol actual: <span class="font-semibold text-gray-700">{{ ucfirst($usuario->rol->nombre) }}</span>
              </p>
            @else
              <p class="mt-2 text-xs text-gray-400">Sin rol asignado</p>
            @endif
          </div>
        @empty
          <div class="text-center py-8 text-gray-500">
            <p>No se encontraron usuarios</p>
            @if($search)
              <p class="text-sm mt-2">Intenta con otro número de carnet o usuario</p>
            @endif
          </div>
        @endforelse
      </div>
    </div>

  </div>

  {{-- Tabla Resumen (Opcional) --}}
  <div class="mt-8 bg-white rounded-lg shadow-md p-6">
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Resumen de Usuarios por Rol</h2>
    
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad de Usuarios</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuarios</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          @foreach($roles as $rol)
            @php
              $usuariosDelRol = $usuarios->where('id_rol', $rol->id);
            @endphp
            <tr>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="w-3 h-3 rounded-full mr-2
                    @if($rol->id == 1) bg-red-500
                    @elseif($rol->id == 2) bg-blue-500
                    @elseif($rol->id == 3) bg-green-500
                    @else bg-yellow-500
                    @endif">
                  </div>
                  <span class="text-sm font-medium text-gray-900">{{ ucfirst($rol->nombre) }}</span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="text-sm text-gray-900">{{ $usuariosDelRol->count() }}</span>
              </td>
              <td class="px-6 py-4">
                <div class="flex flex-wrap gap-2">
                  @forelse($usuariosDelRol as $usuario)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                      {{ $usuario->numero_carnet ?? $usuario->username }}
                    </span>
                  @empty
                    <span class="text-sm text-gray-400">Sin usuarios</span>
                  @endforelse
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

</div>
@endsection
