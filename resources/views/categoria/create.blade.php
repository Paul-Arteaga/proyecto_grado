@extends('layout.navbar')

@section('titulo', 'Crear categoría')

@section('contenido')
<div class="max-w-3xl mx-auto p-6">
  <h1 class="text-2xl font-bold mb-6">Crear categoría</h1>

  <form action="{{ route('categoria.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
    @csrf

    <div>
      <label class="block text-sm font-medium">Nombre</label>
      <input name="nombre" value="{{ old('nombre') }}" class="border rounded px-3 py-2 w-full">
      @error('nombre') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    <div>
      <label class="block text-sm font-medium">Descripción</label>
      <textarea name="descripcion" rows="4" class="border rounded px-3 py-2 w-full">{{ old('descripcion') }}</textarea>
      @error('descripcion') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    <div>
      <label class="block text-sm font-medium">Capacidad de pasajeros</label>
      <input type="number" min="0" name="capacidad_pasajeros" value="{{ old('capacidad_pasajeros', 0) }}" class="border rounded px-3 py-2 w-full">
      @error('capacidad_pasajeros') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    <div>
      <label class="block text-sm font-medium">Imagen</label>
      <input type="file" name="imagen" class="border rounded px-3 py-2 w-full">
      @error('imagen') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    <div class="flex items-center gap-2">
      <input type="checkbox" name="activo" value="1" {{ old('activo', true) ? 'checked' : '' }} class="rounded">
      <span>Activo</span>
    </div>

    <div class="pt-4">
      <button class="bg-blue-600 text-white px-4 py-2 rounded">Guardar</button>
      <a href="{{ route('categoria.index') }}" class="ml-2 text-gray-600">Cancelar</a>
    </div>
  </form>
</div>
@endsection
