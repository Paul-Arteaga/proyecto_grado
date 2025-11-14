@extends('layout.navbar')

@section('titulo', 'Editar categoría')

@section('contenido')
<div class="max-w-5xl mx-auto p-6">
  <h1 class="text-2xl font-bold mb-6">Editar categoría</h1>

  <form action="{{ route('categoria.update',$categoria) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
    @csrf @method('PATCH')

    <div>
      <label class="block text-sm font-medium">Nombre</label>
      <input name="nombre" value="{{ old('nombre',$categoria->nombre) }}" class="border rounded px-3 py-2 w-full">
      @error('nombre') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    <div>
      <label class="block text-sm font-medium">Descripción</label>
      <textarea name="descripcion" rows="4" class="border rounded px-3 py-2 w-full">{{ old('descripcion',$categoria->descripcion) }}</textarea>
      @error('descripcion') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    <div>
      <label class="block text-sm font-medium">Capacidad de pasajeros</label>
      <input type="number" min="0" name="capacidad_pasajeros" value="{{ old('capacidad_pasajeros', $categoria->capacidad_pasajeros ?? 0) }}" class="border rounded px-3 py-2 w-full">
      @error('capacidad_pasajeros') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    <div>
      <label class="block text-sm font-medium">Imagen</label>
      <input type="file" name="imagen" class="border rounded px-3 py-2 w-full">
      @error('imagen') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror

      @if($categoria->imagen)
        <div class="mt-2">
          <span class="text-sm text-gray-600 block mb-1">Actual:</span>
          <img
            src="{{ url('media/'.ltrim($categoria->imagen, '/')) }}"
            alt="Imagen actual"
            class="w-24 h-24 object-cover rounded border"
            onerror="this.onerror=null;this.replaceWith(document.createTextNode('Sin imagen'));"
          >
        </div>
      @endif
    </div>

    <div class="flex items-center gap-2">
      <input type="checkbox" name="activo" value="1" {{ old('activo', $categoria->activo) ? 'checked' : '' }} class="rounded">
      <span>Activo</span>
    </div>

    <div class="pt-4">
      <button class="bg-blue-600 text-white px-4 py-2 rounded">Actualizar</button>
      <a href="{{ route('categoria.index') }}" class="ml-2 text-gray-600">Volver</a>
    </div>
  </form>

  <div class="mt-10 grid grid-cols-1 lg:grid-cols-2 gap-8">
    <div class="bg-white p-4 rounded shadow">
      <h2 class="font-semibold mb-3">Vincular vehículos</h2>
      <select id="vehiculos" class="border rounded w-full px-3 py-2" multiple>
        @foreach(\App\Models\Vehiculo::orderBy('placa')->get() as $v)
          <option value="{{ $v->id }}" {{ $categoria->vehiculos->contains('id', $v->id) ? 'selected' : '' }}>
            {{ $v->placa }} — {{ $v->marca }} {{ $v->modelo }}
          </option>
        @endforeach
      </select>
      <button id="btnSyncVehiculos" class="mt-3 bg-gray-800 text-white px-3 py-2 rounded">Guardar vínculos</button>
    </div>

    <div class="bg-white p-4 rounded shadow">
      <h2 class="font-semibold mb-3">Vincular tarifas</h2>
      <select id="tarifas" class="border rounded w-full px-3 py-2" multiple>
        @foreach(\App\Models\Tarifa::orderBy('nombre')->get() as $t)
          <option value="{{ $t->id }}" {{ $categoria->tarifas->contains('id', $t->id) ? 'selected' : '' }}>
            {{ $t->nombre }} — {{ $t->monto }} {{ $t->moneda ?? 'BOB' }}
          </option>
        @endforeach
      </select>
      <button id="btnSyncTarifas" class="mt-3 bg-gray-800 text-white px-3 py-2 rounded">Guardar vínculos</button>
    </div>
  </div>
</div>

@push('scripts')
<script>
async function patch(url, body) {
  return fetch(url, {
    method: 'PATCH',
    headers: {'X-CSRF-TOKEN': `{{ csrf_token() }}`, 'Accept':'application/json','Content-Type':'application/json'},
    body: JSON.stringify(body)
  });
}

document.getElementById('btnSyncVehiculos').addEventListener('click', async ()=>{
  const sel = document.getElementById('vehiculos');
  const ids = Array.from(sel.selectedOptions).map(o=>o.value);
  const res = await patch(`{{ route('categoria.syncVehiculos',$categoria) }}`, {vehiculos: ids});
  if (res.ok) alert('Vínculos de vehículos guardados.');
});

document.getElementById('btnSyncTarifas').addEventListener('click', async ()=>{
  const sel = document.getElementById('tarifas');
  const ids = Array.from(sel.selectedOptions).map(o=>o.value);
  const res = await patch(`{{ route('categoria.syncTarifas',$categoria) }}`, {tarifas: ids});
  if (res.ok) alert('Vínculos de tarifas guardados.');
});
</script>
@endpush
@endsection
