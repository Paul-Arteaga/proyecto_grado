@extends('layout.navbar')

@section('titulo', 'Reservas')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('contenido')

@php
    use App\Models\Reserva;

    // Fechas ocupadas por vehículo - SOLO las confirmadas (para el calendario)
    $reservasRaw = Reserva::where('estado', 'confirmada')
        ->select('vehiculo_id','fecha_inicio','fecha_fin','id')
        ->get();

    // vamos a expandir los rangos para que el calendario pueda marcarlos día por día
    $ocupadasPorVehiculo = [];

    foreach ($reservasRaw as $r) {
        $vid = $r->vehiculo_id;
        if (!isset($ocupadasPorVehiculo[$vid])) {
            $ocupadasPorVehiculo[$vid] = [];
        }

        $inicio = \Carbon\Carbon::parse($r->fecha_inicio);
        $fin    = \Carbon\Carbon::parse($r->fecha_fin ?: $r->fecha_inicio);

        $d = $inicio->copy();
        while ($d->lte($fin)) {
            $ocupadasPorVehiculo[$vid][] = $d->format('Y-m-d');
            $d->addDay();
        }
    }
@endphp

<script>
    // esto lo usará el calendario de edición
    window.RESERVAS = @json($ocupadasPorVehiculo);
</script>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-10">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4 sm:mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold">Reservas</h1>
        <form method="GET" action="{{ route('reservas.index') }}" class="w-full sm:w-auto flex items-center gap-2">
            <div class="relative flex-1 sm:flex-none sm:w-64">
                <input type="text" name="buscar" value="{{ request('buscar') }}"
                       placeholder="Buscar por cliente, placa, estado..."
                       class="w-full border border-gray-300 rounded-lg pl-3 pr-10 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @if(request('buscar'))
                    <a href="{{ route('reservas.index') }}" class="absolute inset-y-0 right-8 flex items-center text-gray-400 hover:text-gray-600">
                        ×
                    </a>
                @endif
                <span class="absolute inset-y-0 right-3 flex items-center text-gray-400">
                    🔍
                </span>
            </div>
            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700">
                Buscar
            </button>
        </form>
    </div>

    @if(session('ok'))
        <div class="mb-3 px-3 py-2 bg-green-100 text-green-700 rounded">
            {{ session('ok') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-3 px-3 py-2 bg-red-100 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-3 px-3 py-2 bg-red-100 text-red-700 rounded">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Vista Desktop: Tabla --}}
    <div class="hidden lg:block overflow-x-auto bg-white shadow rounded-lg">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="bg-gray-100 text-left">
                    <th class="px-3 py-2">ID</th>
                    <th class="px-3 py-2">Cliente</th>
                    <th class="px-3 py-2">Vehículo</th>
                    <th class="px-3 py-2">Fechas</th>
                    <th class="px-3 py-2">Monto</th>
                    <th class="px-3 py-2">Accesorios</th>
                    <th class="px-3 py-2">Estado</th>
                    <th class="px-3 py-2">Pago</th>
                    <th class="px-3 py-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reservas as $r)
                    @include('reservas.partials.table-row', ['r' => $r])
                @empty
                    <tr>
                        <td class="px-3 py-2" colspan="9">No hay reservas aún.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Vista Mobile: Cards --}}
    <div class="lg:hidden space-y-4">
        @forelse($reservas as $r)
            @php
                $montoAccesorios = $r->accesorios->where('pivot.estado', '!=', 'rechazado')->sum('pivot.precio_total');
                $montoTotal = $r->monto_total + $montoAccesorios;
                $accesoriosReserva = $r->accesorios->where('pivot.estado', '!=', 'rechazado');
                $accesoriosPendientes = $r->accesorios->where('pivot.estado', 'pendiente');
                $fechaFin = \Carbon\Carbon::parse($r->fecha_fin ?? $r->fecha_inicio);
                $reservaPasada = $fechaFin->isPast();
            @endphp
            <div class="bg-white shadow rounded-lg p-4 space-y-3">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs text-gray-500">ID: {{ $r->id }}</p>
                        <h3 class="font-semibold text-gray-900 mt-1">
                            {{ $r->vehiculo?->marca ?? '—' }} {{ $r->vehiculo?->modelo ?? '' }}
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ $r->user?->username ?? $r->user?->email ?? '—' }}
                        </p>
                    </div>
                    <div class="flex flex-col items-end gap-2">
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
                        @if($r->estado_pago)
                            <span class="px-2 py-1 text-xs rounded
                                @if($r->estado_pago == 'pagado') bg-blue-100 text-blue-700
                                @elseif($r->estado_pago == 'pendiente') bg-orange-100 text-orange-700
                                @else bg-red-100 text-red-700
                                @endif">
                                {{ ucfirst($r->estado_pago) }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <p class="text-xs text-gray-500">Fechas</p>
                        <p class="font-medium text-gray-900">
                            {{ \Carbon\Carbon::parse($r->fecha_inicio)->format('d/m/Y') }}
                            @if($r->fecha_fin && $r->fecha_fin != $r->fecha_inicio)
                                – {{ \Carbon\Carbon::parse($r->fecha_fin)->format('d/m/Y') }}
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Monto Total</p>
                        <p class="font-semibold text-gray-900">Bs. {{ number_format($montoTotal, 2) }}</p>
                        @if($montoAccesorios > 0)
                            <p class="text-xs text-gray-500">
                                (Veh: Bs. {{ number_format($r->monto_total, 2) }} + Acc: Bs. {{ number_format($montoAccesorios, 2) }})
                            </p>
                        @endif
                    </div>
                </div>

                @if($accesoriosReserva->count() > 0)
                    <div class="border-t pt-3">
                        <p class="text-xs text-gray-500 mb-2">Accesorios:</p>
                        <div class="space-y-1">
                            @foreach($accesoriosReserva as $acc)
                                <div class="flex justify-between text-xs">
                                    <span>{{ $acc->nombre }} (x{{ $acc->pivot->cantidad }})</span>
                                    <span class="font-semibold">Bs. {{ number_format($acc->pivot->precio_total, 2) }}</span>
                                    @if($acc->pivot->estado == 'pendiente')
                                        <span class="px-1 py-0.5 text-xs bg-yellow-100 text-yellow-700 rounded ml-2">Pendiente</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        @if($accesoriosPendientes->count() > 0 && (Auth::user()->id_rol == 1 || Auth::user()->id_rol == 3))
                            <div class="mt-3 pt-3 border-t space-y-2">
                                @php
                                    $comprobanteAccesorio = $accesoriosPendientes->first()->pivot->comprobante_pago ?? null;
                                @endphp
                                @if($comprobanteAccesorio)
                                    <button onclick="mostrarModalComprobante('{{ Storage::url($comprobanteAccesorio) }}')" 
                                            class="w-full px-3 py-2 text-xs bg-yellow-600 text-white rounded hover:bg-yellow-700" 
                                            title="Ver Comprobante de Pago de Accesorios">
                                        💰 Ver Comprobante Accesorios
                                    </button>
                                @endif
                                <div class="flex gap-2">
                                    <form action="{{ route('reservas.aprobarAccesorios', $r) }}" method="POST" class="flex-1" onsubmit="return confirm('¿Aprobar estos accesorios?')">
                                        @csrf
                                        <button type="submit" class="w-full px-3 py-2 text-xs bg-green-600 text-white rounded hover:bg-green-700">✓ Aprobar</button>
                                    </form>
                                    <button onclick="mostrarModalRechazarAccesorios({{ $r->id }})" class="flex-1 px-3 py-2 text-xs bg-red-600 text-white rounded hover:bg-red-700">✗ Rechazar</button>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                <div class="border-t pt-3">
                    <div class="flex flex-wrap gap-2">
                        @if(Auth::user()->id_rol == 1 || Auth::user()->id_rol == 3)
                            @if($r->estado == 'solicitada')
                                @if($r->comprobante_pago)
                                    <button onclick="mostrarModalComprobante('{{ Storage::url($r->comprobante_pago) }}')" 
                                            class="flex-1 px-3 py-2 text-xs bg-yellow-600 text-white rounded hover:bg-yellow-700" 
                                            title="Ver Comprobante de Pago">
                                        💰 Comprobante
                                    </button>
                                @endif
                                
                                @if($r->documento_carnet || $r->documento_licencia || $r->comprobante_pago)
                                    <button onclick="mostrarModalDocumentos({{ $r->id }}, 
                                        '{{ $r->carnet_anverso ? Storage::url($r->carnet_anverso) : ($r->documento_carnet ? Storage::url($r->documento_carnet) : '') }}', 
                                        '{{ $r->carnet_reverso ? Storage::url($r->carnet_reverso) : '' }}', 
                                        '{{ $r->licencia_anverso ? Storage::url($r->licencia_anverso) : ($r->documento_licencia ? Storage::url($r->documento_licencia) : '') }}', 
                                        '{{ $r->licencia_reverso ? Storage::url($r->licencia_reverso) : '' }}', 
                                        '{{ $r->comprobante_pago ? Storage::url($r->comprobante_pago) : '' }}')" 
                                            class="flex-1 px-3 py-2 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">
                                        📄 Documentos
                                    </button>
                                @endif
                                
                                <form action="{{ route('reservas.aprobar', $r) }}" method="POST" class="flex-1" onsubmit="return confirm('¿Aprobar esta solicitud de reserva?')">
                                    @csrf
                                    <button type="submit" class="w-full px-3 py-2 text-xs bg-green-600 text-white rounded hover:bg-green-700">
                                        ✓ Aprobar
                                    </button>
                                </form>
                                <button onclick="mostrarModalRechazar({{ $r->id }})" class="flex-1 px-3 py-2 text-xs bg-red-600 text-white rounded hover:bg-red-700">
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
                                            class="w-full px-3 py-2 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">
                                        📄 Ver Documentos
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
                                class="flex-1 px-3 py-2 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">
                                Editar
                            </button>
                            <form action="{{ route('reservas.destroy', $r->id) }}" method="POST" class="flex-1" onsubmit="return confirm('¿Cancelar esta reserva? Los días futuros quedarán disponibles.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full px-3 py-2 text-xs bg-red-600 text-white rounded hover:bg-red-700">
                                    Cancelar
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white shadow rounded-lg p-6 text-center text-gray-500">
                No hay reservas aún.
            </div>
        @endforelse
    </div>
</div>

<!-- MODAL DE EDICIÓN CON EL MISMO CALENDARIO -->
<div id="editReservaModal"
     class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50 p-4">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md max-h-[90vh] overflow-y-auto p-4 sm:p-5">
    <div class="flex items-center justify-between mb-3">
      <h3 class="text-base sm:text-lg font-bold" id="editReservaTitulo">Editar reserva</h3>
      <button onclick="closeEditReservaCalendar()" class="text-gray-500 hover:text-gray-700 text-xl sm:text-2xl">&times;</button>
    </div>

    <p class="text-xs sm:text-sm text-gray-500 mb-3">
      Toca los días para ajustar el rango. Los días en gris están ocupados por otras reservas de este vehículo.
    </p>

    <!-- Selector de mes -->
    <div class="flex items-center justify-between mb-2">
      <button class="px-2 sm:px-3 py-1 text-xs sm:text-sm border rounded" onclick="editChangeMonth(-1)">&lt;</button>
      <div id="editMonthLabel" class="font-semibold text-sm sm:text-base"></div>
      <button class="px-2 sm:px-3 py-1 text-xs sm:text-sm border rounded" onclick="editChangeMonth(1)">&gt;</button>
    </div>

    <!-- Calendario -->
    <div id="editCalendarGrid" class="grid grid-cols-7 gap-1 text-center text-xs sm:text-sm mb-4">
      <!-- se llena por JS -->
    </div>

    <div class="flex flex-col sm:flex-row justify-end gap-2">
      <button onclick="closeEditReservaCalendar()" class="w-full sm:w-auto px-4 py-2 text-xs sm:text-sm rounded border">Cancelar</button>
      <button onclick="guardarEdicionReserva()" class="w-full sm:w-auto px-4 py-2 text-xs sm:text-sm rounded bg-blue-600 text-white">Guardar</button>
    </div>
  </div>
</div>

<script>
    window.reservasUrl = "{{ url('/reservas') }}";
    window.csrfToken = "{{ csrf_token() }}";
</script>
<script src="{{ asset('js/reservas/index.js') }}"></script>

{{-- Modal para rechazar solicitud --}}
<div id="modalRechazar" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
  <div class="bg-white rounded-lg shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto p-4 sm:p-6">
    <h3 class="text-xl font-bold text-gray-900 mb-4">Rechazar Solicitud</h3>
    <form id="formRechazar" method="POST">
      @csrf
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Motivo del rechazo (opcional)
        </label>
        <textarea name="motivo" rows="3" 
                  placeholder="Ej: Documentos incompletos, comprobante no válido, etc."
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
      </div>
      <div class="flex justify-end space-x-4">
        <button type="button" onclick="cerrarModalRechazar()" 
                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
          Cancelar
        </button>
        <button type="submit" 
                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold">
          Rechazar Solicitud
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Modal para ver comprobante de pago --}}
<div id="modalComprobante" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-5xl h-[90vh] m-4 flex flex-col">
        <div class="flex justify-between items-center p-6 border-b flex-shrink-0">
            <h3 class="text-xl font-bold text-gray-900">Comprobante de Pago</h3>
            <button onclick="cerrarModalComprobante()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>
        <div class="flex-1 overflow-auto p-6" id="comprobanteContent">
            <!-- Contenido se genera dinámicamente -->
        </div>
    </div>
</div>

{{-- Modal para ver documentos --}}
<div id="modalDocumentos" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
  <div class="bg-white rounded-lg shadow-xl w-full max-w-5xl h-[90vh] m-4 flex flex-col">
    <div class="flex justify-between items-center p-6 border-b flex-shrink-0">
      <h3 class="text-xl font-bold text-gray-900">Documentos de la Reserva</h3>
      <button onclick="cerrarModalDocumentos()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
    </div>
    <div class="flex-1 flex flex-col overflow-hidden">
      <div id="documentosTabs" class="flex space-x-2 px-6 pt-4 border-b pb-2 flex-shrink-0">
        <!-- Tabs se generan dinámicamente -->
      </div>
      <div id="documentosContent" class="flex-1 overflow-auto p-6" style="min-height: 0;">
        <!-- Contenido se genera dinámicamente -->
      </div>
    </div>
  </div>
</div>


{{-- Incluir modal de pago --}}
@include('reservas.partials.modal-pago')

@endsection
