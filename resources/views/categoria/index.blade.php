@extends('layout.navbar')

@section('titulo', 'Categorías')

@section('contenido')

<div class="max-w-6xl mx-auto p-6">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Categorías</h1>
    <a href="{{ route('categoria.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">Nueva categoría</a>
  </div>

  <form method="GET" class="mb-4">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar…"
           class="border rounded px-3 py-2 w-full max-w-sm">
  </form>

  <div class="bg-white shadow rounded overflow-hidden">
    <table class="w-full">
      <thead class="bg-gray-50 text-left">
        <tr>
          <th class="p-3">Imagen</th>
          <th class="p-3">Nombre</th>
          <th class="p-3">Estado</th>
          <th class="p-3"></th>
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

            <td class="p-3">{{ $cat->nombre }}</td>

            <td class="p-3">
              <button
                data-url="{{ route('categoria.toggle', $cat) }}"
                class="toggle-estado px-3 py-1 rounded text-white {{ $cat->activo ? 'bg-green-600' : 'bg-gray-500' }}">
                {{ $cat->activo ? 'Activo' : 'Inactivo' }}
              </button>
            </td>

            <td class="p-3 text-right">
              <a href="{{ route('categoria.edit', $cat) }}" class="text-blue-600 hover:underline">Editar</a>
            </td>
          </tr>
        @empty
          <tr><td class="p-3 text-gray-500" colspan="4">Sin resultados.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $categorias->links() }}</div>
</div>

<script>
  const csrf = `{{ csrf_token() }}`;
  document.querySelectorAll('.toggle-estado').forEach(btn=>{
    btn.addEventListener('click', async e=>{
      e.preventDefault();
      const res = await fetch(btn.dataset.url, {
        method: 'PATCH',
        headers: {
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json'
        }
      });
      if(res.ok) location.reload();
    });
  });
</script>
@endsection
