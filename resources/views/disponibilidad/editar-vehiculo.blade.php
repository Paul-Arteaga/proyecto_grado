@extends('layout.navbar')

@section('titulo', 'Editar vehículo')

@section('contenido')
<div class="max-w-3xl mx-auto p-6">
  <h1 class="text-2xl font-bold mb-6">Editar vehículo</h1>

  <form action="{{ route('vehiculo.update', $vehiculo) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
    @csrf @method('PATCH')

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium">Placa *</label>
        <input name="placa" value="{{ old('placa',$vehiculo->placa) }}" class="border rounded px-3 py-2 w-full">
        @error('placa') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium">VIN (opcional)</label>
        <input name="vin" value="{{ old('vin',$vehiculo->vin) }}" class="border rounded px-3 py-2 w-full">
        @error('vin') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium">Marca *</label>
        <input name="marca" value="{{ old('marca',$vehiculo->marca) }}" class="border rounded px-3 py-2 w-full">
        @error('marca') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium">Modelo *</label>
        <input name="modelo" value="{{ old('modelo',$vehiculo->modelo) }}" class="border rounded px-3 py-2 w-full">
        @error('modelo') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium">Año</label>
        <input type="number" name="anio" value="{{ old('anio',$vehiculo->anio) }}" class="border rounded px-3 py-2 w-full">
        @error('anio') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium">Color</label>
        <input name="color" value="{{ old('color',$vehiculo->color) }}" class="border rounded px-3 py-2 w-full">
        @error('color') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium">Transmisión *</label>
        @php $t = old('transmision',$vehiculo->transmision); @endphp
        <select name="transmision" class="border rounded px-3 py-2 w-full">
          <option value="Manual"     {{ $t==='Manual'?'selected':'' }}>Manual</option>
          <option value="Automática" {{ $t==='Automática'?'selected':'' }}>Automática</option>
        </select>
        @error('transmision') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium">KM actual *</label>
        <input type="number" min="0" name="km_actual" value="{{ old('km_actual',$vehiculo->km_actual) }}" class="border rounded px-3 py-2 w-full">
        @error('km_actual') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium">Precio diario (Bs.) *</label>
        <input type="number" step="0.01" min="0" name="precio_diario"
               value="{{ old('precio_diario',$vehiculo->precio_diario) }}"
               class="border rounded px-3 py-2 w-full">
        @error('precio_diario') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium">Combustible</label>
        <input name="combustible" value="{{ old('combustible',$vehiculo->combustible) }}" class="border rounded px-3 py-2 w-full">
        @error('combustible') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium">Categoría</label>
        <select name="categoria_id" class="border rounded px-3 py-2 w-full">
          <option value="">Sin asignar</option>
          @foreach($categorias as $c)
            <option value="{{ $c->id }}" {{ old('categoria_id',$vehiculo->categoria_id)==$c->id?'selected':'' }}>
              {{ $c->nombre }}
            </option>
          @endforeach
        </select>
        @error('categoria_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium">Estado *</label>
        @php $e = old('estado',$vehiculo->estado); @endphp
        <select name="estado" class="border rounded px-3 py-2 w-full">
          <option value="disponible"    {{ $e==='disponible'?'selected':'' }}>Disponible</option>
          <option value="reservado"     {{ $e==='reservado'?'selected':'' }}>Reservado</option>
          <option value="bloqueado"     {{ $e==='bloqueado'?'selected':'' }}>Bloqueado</option>
          <option value="mantenimiento" {{ $e==='mantenimiento'?'selected':'' }}>Mantenimiento</option>
          <option value="inactivo"      {{ $e==='inactivo'?'selected':'' }}>Inactivo</option>
        </select>
        @error('estado') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium">Observaciones</label>
      <textarea name="observaciones" rows="3" class="border rounded px-3 py-2 w-full">{{ old('observaciones',$vehiculo->observaciones) }}</textarea>
      @error('observaciones') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    <div>
      <label class="block text-sm font-medium">Foto (jpg, png, webp)</label>
      <input type="file" name="foto" class="border rounded px-3 py-2 w-full">
      @error('foto') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror

      @if($vehiculo->foto)
        <div class="mt-2">
          <span class="text-sm text-gray-600 block mb-1">Actual:</span>
          <img src="{{ url('media/'.ltrim($vehiculo->foto,'/')) }}" class="w-24 h-24 object-cover rounded border" alt="Foto vehículo">
        </div>
      @endif
    </div>

    <div class="pt-4 flex items-center gap-2">
      <button class="bg-blue-600 text-white px-4 py-2 rounded">Actualizar</button>
      <a href="{{ route('disp.index') }}" class="text-gray-600">Volver</a>
    </div>
  </form>
</div>
@endsection
