@extends('layout.navbar')

@section('titulo', 'Mis Reservas')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('contenido')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  
  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-0 mb-4 sm:mb-6">
    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Mis Reservas</h1>
    <a href="{{ route('perfil.index') }}" class="text-blue-600 hover:text-blue-800 font-medium text-sm sm:text-base">
      ← Volver al Perfil
    </a>
  </div>

  @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
      {{ session('success') }}
    </div>
  @endif

  <div class="space-y-4">
    @forelse($reservas as $reserva)
      <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
          <div class="flex-1 w-full">
            <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 mb-3 sm:mb-4">
              <h3 class="text-lg sm:text-xl font-semibold text-gray-900">
                {{ $reserva->vehiculo->marca }} {{ $reserva->vehiculo->modelo }}
              </h3>
              <div class="flex flex-wrap gap-2">
                <span class="px-2 sm:px-3 py-1 rounded-full text-xs font-medium
                  @if($reserva->estado == 'confirmada') bg-green-100 text-green-800
                  @elseif($reserva->estado == 'completada') bg-emerald-100 text-emerald-800
                  @elseif($reserva->estado == 'solicitada') bg-yellow-100 text-yellow-800
                  @elseif($reserva->estado == 'pendiente') bg-orange-100 text-orange-800
                  @elseif($reserva->estado == 'rechazada') bg-red-100 text-red-800
                  @else bg-gray-100 text-gray-800
                  @endif">
                  {{ ucfirst($reserva->estado) }}
                </span>
                <span class="px-2 sm:px-3 py-1 rounded-full text-xs font-medium
                  @if($reserva->estado_pago == 'pagado') bg-blue-100 text-blue-800
                  @elseif($reserva->estado_pago == 'pendiente') bg-orange-100 text-orange-800
                  @else bg-red-100 text-red-800
                  @endif">
                  Pago: {{ ucfirst($reserva->estado_pago) }}
                </span>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
              <div>
                <p class="text-sm text-gray-500">Placa</p>
                <p class="font-medium text-gray-900">{{ $reserva->vehiculo->placa }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Fecha Inicio</p>
                <p class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($reserva->fecha_inicio)->format('d/m/Y') }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Fecha Fin</p>
                <p class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($reserva->fecha_fin)->format('d/m/Y') }}</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
              <div>
                <p class="text-sm text-gray-500">Días de Reserva</p>
                <p class="font-medium text-gray-900">{{ $reserva->dias_reserva }} día(s)</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Monto Total</p>
                @php
                  $montoAccesorios = $reserva->accesorios->where('pivot.estado', '!=', 'rechazado')->sum('pivot.precio_total');
                  $montoTotal = $reserva->monto_total + $montoAccesorios;
                @endphp
                <p class="font-medium text-gray-900 text-lg">Bs. {{ number_format($montoTotal, 2) }}</p>
                @if($montoAccesorios > 0)
                  <p class="text-xs text-gray-500">(Vehículo: Bs. {{ number_format($reserva->monto_total, 2) }} + Accesorios: Bs. {{ number_format($montoAccesorios, 2) }})</p>
                @endif
              </div>
            </div>

            {{-- Accesorios de la reserva --}}
            @php
              $accesoriosReserva = $reserva->accesorios->where('pivot.estado', '!=', 'rechazado');
            @endphp
            @if($accesoriosReserva->count() > 0)
              <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                <p class="text-sm font-medium text-gray-700 mb-2">Accesorios incluidos:</p>
                <div class="space-y-1">
                  @foreach($accesoriosReserva as $acc)
                    <div class="flex justify-between items-center text-sm">
                      <span>{{ $acc->nombre }} (x{{ $acc->pivot->cantidad }})</span>
                      <span class="font-semibold">Bs. {{ number_format($acc->pivot->precio_total, 2) }}</span>
                      @if($acc->pivot->estado == 'pendiente')
                        <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded">Pendiente de aprobación</span>
                      @endif
                    </div>
                  @endforeach
                </div>
              </div>
            @endif

            {{-- Botón para agregar accesorios adicionales (solo si está confirmada) --}}
            @if($reserva->estado == 'confirmada')
              <div class="mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-gray-200">
                <a href="{{ route('accesorio.catalogo', ['reserva_id' => $reserva->id]) }}" 
                   class="inline-flex items-center justify-center w-full sm:w-auto px-3 sm:px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium text-sm sm:text-base">
                  <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                  </svg>
                  Agregar Accesorio Adicional
                </a>
              </div>
            @endif

            {{-- Documentos y QR --}}
            <div class="mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-gray-200">
              <p class="text-xs sm:text-sm font-medium text-gray-700 mb-2">Documentos y Código QR:</p>
              <div class="flex flex-wrap gap-2">
                @if($reserva->documento_carnet)
                  <a href="{{ asset('storage/' . $reserva->documento_carnet) }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">
                    📄 Carnet
                  </a>
                @endif
                @if($reserva->documento_licencia)
                  <a href="{{ asset('storage/' . $reserva->documento_licencia) }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">
                    🪪 Licencia
                  </a>
                @endif
                @if($reserva->comprobante_pago)
                  <a href="{{ asset('storage/' . $reserva->comprobante_pago) }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">
                    💰 Comprobante
                  </a>
                @endif
                @if($reserva->codigo_qr)
                  <a href="{{ asset('storage/' . $reserva->codigo_qr) }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">
                    📱 Ver Código QR
                  </a>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="bg-white rounded-lg shadow-md p-12 text-center">
        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        <p class="text-gray-500 text-lg">No tienes reservas aún</p>
        <a href="{{ route('disp.index') }}" class="text-blue-600 hover:text-blue-800 font-medium mt-2 inline-block">
          Ver vehículos disponibles
        </a>
      </div>
    @endforelse
  </div>

</div>
@endsection

