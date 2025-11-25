@extends('layout.navbar')

@section('titulo', 'Categorías')

@section('contenido')

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-4 sm:py-6">
  <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-0 mb-4 sm:mb-6">
    <h1 class="text-xl sm:text-2xl font-bold">Categorías</h1>
    <a href="{{ route('categoria.create') }}" class="bg-blue-600 text-white px-3 sm:px-4 py-2 rounded text-sm sm:text-base w-full sm:w-auto text-center">Nueva categoría</a>
  </div>

  <form method="GET" class="mb-4">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar…"
           class="border rounded px-3 py-2 w-full sm:max-w-sm text-sm sm:text-base">
  </form>

  {{-- Vista Desktop: Tabla --}}
  <div class="hidden md:block bg-white shadow rounded overflow-hidden">
    <table class="w-full">
      <thead class="bg-gray-50 text-left">
        <tr>
          <th class="p-3 text-sm">Imagen</th>
          <th class="p-3 text-sm">Nombre</th>
          <th class="p-3 text-sm">Estado</th>
          <th class="p-3 text-sm"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($categorias as $cat)
          <tr class="border-t">
            <td class="p-3">
              @if($cat->imagen)
                <img
                  src="{{ url('media/'.ltrim($cat->imagen, '/')) }}"
                  class="w-16 h-16 object-cover rounded"
                  alt="Imagen categoría"
                  onerror="this.onerror=null;this.replaceWith(document.createTextNode('Sin imagen'));"
                >
              @else
                <span class="text-gray-400 text-sm">Sin imagen</span>
              @endif
            </td>
            <td class="p-3 text-sm">{{ $cat->nombre }}</td>
            <td class="p-3">
              <button
                data-url="{{ route('categoria.toggle', $cat) }}"
                class="toggle-estado px-3 py-1 rounded text-white text-xs sm:text-sm {{ $cat->activo ? 'bg-green-600' : 'bg-gray-500' }}">
                {{ $cat->activo ? 'Activo' : 'Inactivo' }}
              </button>
            </td>
            <td class="p-3 text-right">
              <a href="{{ route('categoria.edit', $cat) }}" class="text-blue-600 hover:underline text-sm">Editar</a>
            </td>
          </tr>
        @empty
          <tr><td class="p-3 text-gray-500 text-sm" colspan="4">Sin resultados.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Vista Mobile: Cards --}}
  <div class="md:hidden space-y-4">
    @forelse($categorias as $cat)
      <div class="bg-white shadow rounded-lg p-4">
        <div class="flex items-center gap-4 mb-3">
          @if($cat->imagen)
            <img
              src="{{ url('media/'.ltrim($cat->imagen, '/')) }}"
              class="w-20 h-20 object-cover rounded flex-shrink-0"
              alt="Imagen categoría"
              onerror="this.onerror=null;this.replaceWith(document.createTextNode('Sin imagen'));"
            >
          @else
            <div class="w-20 h-20 bg-gray-200 rounded flex items-center justify-center flex-shrink-0">
              <span class="text-gray-400 text-xs">Sin imagen</span>
            </div>
          @endif
          <div class="flex-1 min-w-0">
            <h3 class="font-semibold text-gray-900 text-sm sm:text-base truncate">{{ $cat->nombre }}</h3>
            <div class="mt-2 flex items-center gap-2">
              <button
                data-url="{{ route('categoria.toggle', $cat) }}"
                class="toggle-estado px-3 py-1 rounded text-white text-xs {{ $cat->activo ? 'bg-green-600' : 'bg-gray-500' }}">
                {{ $cat->activo ? 'Activo' : 'Inactivo' }}
              </button>
              <a href="{{ route('categoria.edit', $cat) }}" class="text-blue-600 hover:underline text-xs sm:text-sm">Editar</a>
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="bg-white shadow rounded-lg p-6 text-center">
        <p class="text-gray-500 text-sm">Sin resultados.</p>
      </div>
    @endforelse
  </div>

  <div class="mt-4">{{ $categorias->links() }}</div>
</div>

<script>
  window.csrfToken = "{{ csrf_token() }}";
</script>
<script src="{{ asset('js/categoria/index.js') }}"></script>
@endsection
