<div class="bg-white shadow rounded overflow-x-auto">
  <table class="w-full min-w-[1000px]">
    <thead class="bg-gray-50 text-left">
  <tr>
    <th class="p-3">Foto</th>
    <th class="p-3">Placa</th>
    <th class="p-3">Marca/Modelo</th>
    <th class="p-3">Categoría</th>
    <th class="p-3">Transmisión</th>
    <th class="p-3">Precio</th>
    <th class="p-3">Estado</th>
    <th class="p-3">Acciones</th>
  </tr>
</thead>
<tbody>
@forelse($vehiculos as $v)
  <tr class="border-t">
    <td class="p-3">
      @if($v->foto)
        <img src="{{ url('media/'.ltrim($v->foto,'/')) }}" class="w-16 h-12 object-cover rounded" alt="Foto">
      @else
        <span class="text-gray-400 text-sm">—</span>
      @endif
    </td>
    <td class="p-3 font-mono">{{ $v->placa }}</td>
    <td class="p-3">{{ $v->marca }} {{ $v->modelo }}</td>
    <td class="p-3">{{ $v->categoria->nombre ?? '—' }}</td>
    <td class="p-3">{{ $v->transmision }}</td>
    <td class="p-3">Bs. {{ number_format($v->precio_diario, 2) }}</td>
    <td class="p-3">
      <span class="px-2 py-1 rounded text-white
        {{ $v->estado === 'disponible' ? 'bg-green-600' :
           ($v->estado === 'inactivo' ? 'bg-gray-500' :
           ($v->estado === 'mantenimiento' ? 'bg-red-600' :
           ($v->estado === 'bloqueado' ? 'bg-orange-500' : 'bg-blue-600'))) }}">
        {{ ucfirst($v->estado) }}
      </span>
    </td>
    <td class="p-3">
      <div class="flex items-center gap-2 flex-wrap">
        <select class="border rounded px-2 py-1 asignar-cat" data-vehiculo-id="{{ $v->id }}">
          <option value="">Asignar categoría…</option>
          @foreach(\App\Models\Categoria::activas()->orderBy('nombre')->get(['id','nombre']) as $c)
            <option value="{{ $c->id }}" {{ (optional($v->categoria)->id === $c->id) ? 'selected' : '' }}>
              {{ $c->nombre }}
            </option>
          @endforeach
        </select>

        <a href="{{ route('vehiculo.edit',$v) }}" class="px-2 py-1 bg-yellow-600 text-white rounded">Editar</a>

        <button class="px-2 py-1 bg-gray-300 text-gray-700 rounded cursor-not-allowed" disabled>Bloquear</button>
        <button class="px-2 py-1 bg-gray-300 text-gray-700 rounded cursor-not-allowed" disabled>Mantener</button>
        <button class="px-2 py-1 bg-blue-600 text-white rounded" disabled>Reservar</button>
        <button class="px-2 py-1 bg-indigo-600 text-white rounded" disabled>Cotizar</button>
      </div>
    </td>
  </tr>
@empty
  <tr><td class="p-3 text-gray-500" colspan="8">Sin resultados.</td></tr>
@endforelse
</tbody>

  </table>
</div>

<div class="mt-4">
  {{ $vehiculos->links() }}
</div>
