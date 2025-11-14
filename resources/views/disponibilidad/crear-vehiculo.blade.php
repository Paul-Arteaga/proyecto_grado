@extends('layout.navbar')

@section('titulo', 'Registrar vehículo')

@section('contenido')
<div class="max-w-3xl mx-auto p-6">
  <h1 class="text-2xl font-bold mb-6">Registrar vehículo</h1>

  <form action="{{ route('disp.vehiculo.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
    @csrf

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium">Precio diario (Bs.) *</label>
        <input type="number" step="0.01" min="0" name="precio_diario"
         value="{{ old('precio_diario') }}"
         class="border rounded px-3 py-2 w-full">
        @error('precio_diario') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

      <div>
        <label class="block text-sm font-medium">Placa *</label>
        <input name="placa" value="{{ old('placa') }}" class="border rounded px-3 py-2 w-full">
        @error('placa') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium">VIN (opcional)</label>
        <input name="vin" value="{{ old('vin') }}" class="border rounded px-3 py-2 w-full">
        @error('vin') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium">Marca *</label>
        <input name="marca" value="{{ old('marca') }}" class="border rounded px-3 py-2 w-full">
        @error('marca') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium">Modelo *</label>
        <input name="modelo" value="{{ old('modelo') }}" class="border rounded px-3 py-2 w-full">
        @error('modelo') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium">Año</label>
        <input type="number" name="anio" value="{{ old('anio') }}" class="border rounded px-3 py-2 w-full">
        @error('anio') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium">Color</label>
        <input name="color" value="{{ old('color') }}" class="border rounded px-3 py-2 w-full">
        @error('color') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium">Transmisión *</label>
        <select name="transmision" class="border rounded px-3 py-2 w-full">
          <option value="">Seleccione…</option>
          <option value="Manual"     {{ old('transmision')==='Manual'?'selected':'' }}>Manual</option>
          <option value="Automática" {{ old('transmision')==='Automática'?'selected':'' }}>Automática</option>
        </select>
        @error('transmision') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium">KM actual *</label>
        <input type="number" min="0" name="km_actual" value="{{ old('km_actual',0) }}" class="border rounded px-3 py-2 w-full">
        @error('km_actual') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium">Combustible</label>
        <input name="combustible" value="{{ old('combustible') }}" class="border rounded px-3 py-2 w-full">
        @error('combustible') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium">Categoría</label>
        <select name="categoria_id" class="border rounded px-3 py-2 w-full">
          <option value="">Sin asignar</option>
          @foreach($categorias as $c)
            <option value="{{ $c->id }}" {{ old('categoria_id')==$c->id?'selected':'' }}>
              {{ $c->nombre }}
            </option>
          @endforeach
        </select>
        @error('categoria_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium">Estado *</label>
        <select name="estado" class="border rounded px-3 py-2 w-full">
          @php $est = old('estado','disponible'); @endphp
          <option value="disponible"    {{ $est==='disponible'?'selected':'' }}>Disponible</option>
          <option value="reservado"     {{ $est==='reservado'?'selected':'' }}>Reservado</option>
          <option value="bloqueado"     {{ $est==='bloqueado'?'selected':'' }}>Bloqueado</option>
          <option value="mantenimiento" {{ $est==='mantenimiento'?'selected':'' }}>Mantenimiento</option>
          <option value="inactivo"      {{ $est==='inactivo'?'selected':'' }}>Inactivo</option>
        </select>
        @error('estado') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium">Observaciones</label>
      <textarea name="observaciones" rows="3" class="border rounded px-3 py-2 w-full">{{ old('observaciones') }}</textarea>
      @error('observaciones') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    <div>
      <label class="block text-sm font-medium">Foto (jpg, png, webp)</label>
      <input type="file" name="foto" class="border rounded px-3 py-2 w-full">
      @error('foto') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    <div class="pt-4 flex items-center gap-2">
      <button class="bg-blue-600 text-white px-4 py-2 rounded">Guardar</button>
      <a href="{{ route('disp.index') }}" class="text-gray-600">Volver</a>
    </div>
  </form>
</div>
@endsection
