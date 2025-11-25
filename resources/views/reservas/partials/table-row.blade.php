@php
    $montoAccesorios = $r->accesorios->where('pivot.estado', '!=', 'rechazado')->sum('pivot.precio_total');
    $montoTotal = $r->monto_total + $montoAccesorios;
    $accesoriosReserva = $r->accesorios->where('pivot.estado', '!=', 'rechazado');
    $accesoriosPendientes = $r->accesorios->where('pivot.estado', 'pendiente');
    $fechaFin = \Carbon\Carbon::parse($r->fecha_fin ?? $r->fecha_inicio);
    $reservaPasada = $fechaFin->isPast();
@endphp
<tr class="border-b">
    <td class="px-3 py-2">{{ $r->id }}</td>
    <td class="px-3 py-2">
        {{ $r->user?->username ?? $r->user?->email ?? '—' }}
    </td>
    <td class="px-3 py-2">
        {{ $r->vehiculo?->marca ?? '—' }} {{ $r->vehiculo?->modelo ?? '' }}
    </td>
    <td class="px-3 py-2">
        {{ \Carbon\Carbon::parse($r->fecha_inicio)->format('d/m/Y') }}
        @if($r->fecha_fin && $r->fecha_fin != $r->fecha_inicio)
            – {{ \Carbon\Carbon::parse($r->fecha_fin)->format('d/m/Y') }}
        @endif
    </td>
    <td class="px-3 py-2">
        <div>
            <span class="font-semibold">Bs. {{ number_format($montoTotal, 2) }}</span>
            @if($montoAccesorios > 0)
                <span class="text-xs text-gray-500 block">
                    (Vehículo: Bs. {{ number_format($r->monto_total, 2) }} + Accesorios: Bs. {{ number_format($montoAccesorios, 2) }})
                </span>
            @endif
        </div>
    </td>
    <td class="px-3 py-2">
        @if($accesoriosReserva->count() > 0)
            <div class="text-xs">
                @foreach($accesoriosReserva as $acc)
                    <div class="flex items-center justify-between mb-1">
                        <span>{{ $acc->nombre }} (x{{ $acc->pivot->cantidad }})</span>
                        <span class="font-semibold">Bs. {{ number_format($acc->pivot->precio_total, 2) }}</span>
                        @if($acc->pivot->estado == 'pendiente')
                            <span class="px-1 py-0.5 text-xs bg-yellow-100 text-yellow-700 rounded">Pendiente</span>
                        @endif
                    </div>
                @endforeach
                @if($accesoriosPendientes->count() > 0 && (Auth::user()->id_rol == 1 || Auth::user()->id_rol == 3))
                    <div class="mt-2 pt-2 border-t space-y-2">
                        @php
                            $comprobanteAccesorio = $accesoriosPendientes->first()->pivot->comprobante_pago ?? null;
                        @endphp
                        @if($comprobanteAccesorio)
                            <button onclick="mostrarModalComprobante('{{ Storage::url($comprobanteAccesorio) }}')" 
                                    class="px-2 py-1 text-xs bg-yellow-600 text-white rounded hover:bg-yellow-700 w-full block text-center" 
                                    title="Ver Comprobante de Pago de Accesorios">
                                💰 Ver Comprobante Accesorios
                            </button>
                        @endif
                        <div class="flex gap-2">
                            <form action="{{ route('reservas.aprobarAccesorios', $r) }}" method="POST" class="inline" onsubmit="return confirm('¿Aprobar estos accesorios?')">
                                @csrf
                                <button type="submit" class="px-2 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700">✓ Aprobar</button>
                            </form>
                            <button onclick="mostrarModalRechazarAccesorios({{ $r->id }})" class="px-2 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700">✗ Rechazar</button>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <span class="text-gray-400 text-xs">Sin accesorios</span>
        @endif
    </td>
    <td class="px-3 py-2">
        <span class="px-2 py-1 text-xs rounded
            @if($r->estado == 'confirmada') bg-green-100 text-green-700
            @elseif($r->estado == 'completada') bg-emerald-100 text-emerald-700
            @elseif($r->estado == 'solicitada') bg-yellow-100 text-yellow-700
            @elseif($r->estado == 'pendiente') bg-orange-100 text-orange-700
            @elseif($r->estado == 'rechazada') bg-red-100 text-red-700
            @elseif($r->estado == 'cancelada') bg-gray-100 text-gray-700
            @else bg-gray-100 text-gray-700
            @endif">
            {{ ucfirst($r->estado) }}
        </span>
    </td>
    <td class="px-3 py-2">
        @if($r->estado_pago)
            <span class="px-2 py-1 text-xs rounded
                @if($r->estado_pago == 'pagado') bg-blue-100 text-blue-700
                @elseif($r->estado_pago == 'pendiente') bg-orange-100 text-orange-700
                @else bg-red-100 text-red-700
                @endif">
                {{ ucfirst($r->estado_pago) }}
            </span>
        @else
            <span class="text-gray-400">—</span>
        @endif
    </td>
    <td class="px-3 py-2">
        <div class="flex gap-2 items-center flex-wrap">
            @if(Auth::user()->id_rol == 1 || Auth::user()->id_rol == 3)
                @if($r->estado == 'solicitada')
                    @if($r->comprobante_pago)
                        <button onclick="mostrarModalComprobante('{{ Storage::url($r->comprobante_pago) }}')" 
                                class="px-2 py-1 text-xs bg-yellow-600 text-white rounded hover:bg-yellow-700" 
                                title="Ver Comprobante de Pago">
                            💰 Ver Comprobante
                        </button>
                    @endif
                    
                    @if($r->documento_carnet || $r->documento_licencia || $r->comprobante_pago)
                        <button onclick="mostrarModalDocumentos({{ $r->id }}, 
                            '{{ $r->carnet_anverso ? Storage::url($r->carnet_anverso) : ($r->documento_carnet ? Storage::url($r->documento_carnet) : '') }}', 
                            '{{ $r->carnet_reverso ? Storage::url($r->carnet_reverso) : '' }}', 
                            '{{ $r->licencia_anverso ? Storage::url($r->licencia_anverso) : ($r->documento_licencia ? Storage::url($r->documento_licencia) : '') }}', 
                            '{{ $r->licencia_reverso ? Storage::url($r->licencia_reverso) : '' }}', 
                            '{{ $r->comprobante_pago ? Storage::url($r->comprobante_pago) : '' }}')" 
                                class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">
                            📄 Ver Documentos
                        </button>
                    @endif
                    
                    <form action="{{ route('reservas.aprobar', $r) }}" method="POST" class="inline" onsubmit="return confirm('¿Aprobar esta solicitud de reserva?')">
                        @csrf
                        <button type="submit" class="px-2 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700" title="Aprobar Solicitud">
                            ✓ Aprobar
                        </button>
                    </form>
                    <button onclick="mostrarModalRechazar({{ $r->id }})" class="px-2 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700" title="Rechazar Solicitud">
                        ✗ Rechazar
                    </button>
                @else
                    @if($r->documento_carnet || $r->documento_licencia || $r->comprobante_pago)
                        <button onclick="mostrarModalDocumentos({{ $r->id }}, 
                            '{{ $r->carnet_anverso ? Storage::url($r->carnet_anverso) : ($r->documento_carnet ? Storage::url($r->documento_carnet) : '') }}', 
                            '{{ $r->carnet_reverso ? Storage::url($r->carnet_reverso) : '' }}', 
                            '{{ $r->licencia_anverso ? Storage::url($r->licencia_anverso) : ($r->documento_licencia ? Storage::url($r->documento_licencia) : '') }}', 
                            '{{ $r->licencia_reverso ? Storage::url($r->licencia_reverso) : '' }}', 
                            '{{ $r->comprobante_pago ? Storage::url($r->comprobante_pago) : '' }}')" 
                                class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">
                            📄 Docs
                        </button>
                    @endif
                @endif
            @endif
            @if((Auth::user()->id_rol == 1 || Auth::user()->id_rol == 3) && $r->estado == 'confirmada' && !$reservaPasada)
                <button
                    type="button"
                    onclick="openEditReservaCalendar({
                        id: {{ $r->id }},
                        vehiculo_id: {{ $r->vehiculo_id ?? 'null' }},
                        fecha_inicio: '{{ $r->fecha_inicio }}',
                        fecha_fin: '{{ $r->fecha_fin ?? $r->fecha_inicio }}'
                    })"
                    class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">
                    Editar
                </button>
                <form action="{{ route('reservas.destroy', $r->id) }}" method="POST" onsubmit="return confirm('¿Cancelar esta reserva? Los días futuros quedarán disponibles.')" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-2 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700">
                        Cancelar
                    </button>
                </form>
            @endif
        </div>
    </td>
</tr>


