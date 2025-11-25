@extends('layout.navbar')

@section('titulo', 'Editar Rol')

@section('contenido')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  
  <div class="bg-white rounded-lg shadow-md p-6">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Editar Rol</h1>

    @if($errors->any())
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <ul class="list-disc list-inside">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('actualizarRol.update', $rol) }}" method="POST">
      @csrf
      @method('PATCH')
      
      <div class="mb-4">
        <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
          Nombre del Rol
        </label>
        <input 
          type="text" 
          id="nombre" 
          name="nombre" 
          value="{{ old('nombre', $rol->nombre) }}"
          required
          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        >
        <p class="mt-1 text-sm text-gray-500">ID del Rol: {{ $rol->id }}</p>
      </div>

      <div class="flex items-center space-x-4">
        <button 
          type="submit" 
          class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg transition shadow-sm hover:shadow-md"
        >
          Actualizar Rol
        </button>
        <a 
          href="{{ route('mostrar.rol') }}" 
          class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold px-6 py-2 rounded-lg transition"
        >
          Cancelar
        </a>
      </div>
    </form>
  </div>

</div>
@endsection

