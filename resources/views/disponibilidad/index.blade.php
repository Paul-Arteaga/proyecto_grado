@extends('layout.navbar')

@section('titulo', 'Disponibilidad')

@section('contenido')
<div class="max-w-6xl mx-auto p-6">

  <h1 class="text-2xl font-bold mb-4">Gestionar disponibilidad vehículo</h1>

  {{-- Filtros --}}
  <form id="formFiltros" action="{{ route('disp.search') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
    <div>
      <label class="block text-sm font-medium">Desde</label>
      <input type="date" name="desde" value="{{ $filtros['desde'] }}" class="border rounded px-3 py-2 w-full">
    </div>
    <div>
      <label class="block text-sm font-medium">Hasta</label>
      <input type="date" name="hasta" value="{{ $filtros['hasta'] }}" class="border rounded px-3 py-2 w-full">
    </div>
    <div>
      <label class="block text-sm font-medium">Categoría</label>
      <select name="categoria_id" class="border rounded px-3 py-2 w-full">
        <option value="">-- Todas --</option>
        @foreach($categorias as $c)
          <option value="{{ $c->id }}" {{ (string)$filtros['categoria_id'] === (string)$c->id ? 'selected' : '' }}>
            {{ $c->nombre }}
          </option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium">Transmisión</label>
      <select name="transmision" class="border rounded px-3 py-2 w-full">
        <option value="">-- Todas --</option>
        @foreach($transmisiones as $t)
          <option value="{{ $t }}" {{ $filtros['transmision']===$t ? 'selected' : '' }}>
            {{ $t }}
          </option>
        @endforeach
      </select>
    </div>
    <div class="flex items-end">
      <button class="bg-blue-600 text-white px-4 py-2 rounded w-full">Buscar</button>
    </div>
    <a href="{{ route('disp.vehiculo.create') }}"
   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
  Registrar vehículo
</a>

  </form>

  {{-- Resultados --}}
  <div id="tablaResultados">
    @include('disponibilidad.partials.tabla', ['vehiculos'=>$vehiculos])
  </div>
</div>

{{-- JS: AJAX search + asignar categoría --}}
<script>
const csrf = `{{ csrf_token() }}`;

// Enviar filtros por AJAX (opcional; si quitas preventDefault, funciona full-page)
document.getElementById('formFiltros').addEventListener('submit', async (e)=>{
  e.preventDefault();
  const url = e.target.action + '?' + new URLSearchParams(new FormData(e.target)).toString();
  const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
  const html = await res.text();
  document.getElementById('tablaResultados').innerHTML = html;
  engancharAsignar(); // reatacha eventos
});

function engancharAsignar() {
  document.querySelectorAll('.asignar-cat').forEach(sel=>{
    sel.addEventListener('change', async ()=>{
      const categoriaId = sel.value;
      if (!categoriaId) return;
      const vehiculoId = sel.dataset.vehiculoId;

      const res = await fetch(`{{ route('disp.asignar') }}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ vehiculo_id: vehiculoId, categoria_id: categoriaId })
      });

      const json = await res.json();
      if (json.ok) {
        alert('Vehículo asignado a la categoría.');
      } else {
        alert(json.message || 'No se pudo asignar.');
        sel.value = '';
      }
    });
  });
}
engancharAsignar();
</script>
@endsection
