@extends('layout.navbar')

@section('titulo', 'Permisos')

@section('contenido')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10 text-gray-900">

  <!-- Header limpio -->
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-semibold tracking-tight">Gestionar Permisos</h1>
      <p class="mt-2 text-sm text-gray-600">
        Lista de permisos del sistema: nombre, módulo y descripción.
      </p>
    </div>

    <!-- Botón simple (sin SVG para evitar estilos heredados) -->
    <a href="#"
       class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2">
      Add permiso
    </a>
  </div>

  <!-- Tabla blanca con bordes suaves -->
  <div class="mt-8 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">
    <div class="overflow-x-auto">
      <table class="min-w-full whitespace-nowrap text-left">
        <thead class="bg-gray-50">
          <tr class="text-sm font-semibold text-gray-600">
            <th class="px-6 py-4">Nombre</th>
            <th class="px-6 py-4">Módulo</th>
            <th class="px-6 py-4">Descripción</th>
            <th class="px-6 py-4"><span class="sr-only">Acciones</span></th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-200 text-sm text-gray-700">
          @forelse($permisos as $permiso)
            <tr class="hover:bg-gray-50">
              <td class="px-6 py-4 font-medium">{{ $permiso->nombre }}</td>
              <td class="px-6 py-4">{{ $permiso->modulo ?? '—' }}</td>
              <td class="px-6 py-4 text-gray-600">{{ $permiso->descripcion ?? '—' }}</td>
              <td class="px-6 py-4 text-right">
                <div class="inline-flex items-center gap-4">
                  <a href="#" class="text-indigo-600 hover:text-indigo-500 font-medium">Edit</a>
                  <form action="{{ route('eliminar.permiso', $permiso->id) }}" method="POST"
                        onsubmit="return confirm('¿Eliminar este permiso?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-500 font-medium">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                No permissions found.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>
@endsection
