@extends('layout.navbar')

@section('titulo', 'Detalles de Devolución')

@section('contenido')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8">
    <div class="mb-6">
        <a href="{{ route('devolucion.index') }}" class="text-blue-600 hover:text-blue-800 text-sm sm:text-base">
            ← Volver a Devoluciones
        </a>
    </div>

    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-6">Detalles de Devolución</h1>

    {{-- Información de la Reserva --}}
    <div class="bg-white shadow rounded-lg p-4 sm:p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Información de la Reserva</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-600">ID Reserva:</span>
                <span class="font-medium">{{ $devolucion->reserva->id }}</span>
            </div>
            <div>
                <span class="text-gray-600">Cliente:</span>
                <span class="font-medium">{{ $devolucion->reserva->user?->username ?? '—' }}</span>
                @if($devolucion->reserva->user?->numero_carnet)
                    <span class="text-gray-500">({{ $devolucion->reserva->user->numero_carnet }})</span>
                @endif
            </div>
            <div>
                <span class="text-gray-600">Vehículo:</span>
                <span class="font-medium">{{ $devolucion->reserva->vehiculo?->marca ?? '—' }} {{ $devolucion->reserva->vehiculo?->modelo ?? '' }}</span>
            </div>
            <div>
                <span class="text-gray-600">Placa:</span>
                <span class="font-medium">{{ $devolucion->reserva->vehiculo?->placa ?? '—' }}</span>
            </div>
            <div>
                <span class="text-gray-600">Período:</span>
                <span class="font-medium">
                    {{ \Carbon\Carbon::parse($devolucion->reserva->fecha_inicio)->format('d/m/Y') }} - 
                    {{ \Carbon\Carbon::parse($devolucion->reserva->fecha_fin)->format('d/m/Y') }}
                </span>
            </div>
        </div>
    </div>

    {{-- Información de la Devolución --}}
    <div class="bg-white shadow rounded-lg p-4 sm:p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Información de la Devolución</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm mb-4">
            <div>
                <span class="text-gray-600">Fecha y Hora:</span>
                <span class="font-medium">{{ \Carbon\Carbon::parse($devolucion->fecha_hora_devolucion)->format('d/m/Y H:i') }}</span>
            </div>
            <div>
                <span class="text-gray-600">Estado:</span>
                <span class="px-2 py-1 text-xs rounded
                    @if($devolucion->estado == 'completada') bg-green-100 text-green-700
                    @elseif($devolucion->estado == 'con_danos') bg-red-100 text-red-700
                    @else bg-yellow-100 text-yellow-700
                    @endif">
                    {{ ucfirst($devolucion->estado) }}
                </span>
            </div>
            <div>
                <span class="text-gray-600">Kilometraje registrado:</span>
                <span class="font-medium">{{ number_format($devolucion->km_retorno ?? 0) }} km</span>
            </div>
            <div>
                <span class="text-gray-600">Recibido por:</span>
                <span class="font-medium">{{ $devolucion->usuarioRecibe?->username ?? '—' }}</span>
            </div>
        </div>

        {{-- Condiciones del Vehículo --}}
        @if($devolucion->condiciones_vehiculo)
            <div class="mt-4 pt-4 border-t">
                <h3 class="font-semibold text-gray-900 mb-3">Condiciones del Vehículo</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                    @foreach($devolucion->condiciones_vehiculo as $key => $value)
                        @if($value)
                            <div class="flex items-center space-x-2">
                                @if($key === 'tiene_danos')
                                    <span class="text-red-600">✗</span>
                                    <span class="text-red-700 font-medium">{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                                @else
                                    <span class="text-green-600">✓</span>
                                    <span class="text-gray-700">{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Condiciones de Accesorios --}}
        @if($devolucion->condiciones_accesorios && count($devolucion->condiciones_accesorios) > 0)
            <div class="mt-4 pt-4 border-t">
                <h3 class="font-semibold text-gray-900 mb-3">Condiciones de Accesorios</h3>
                <div class="space-y-2">
                    @foreach($devolucion->condiciones_accesorios as $accesorioId => $condiciones)
                        @php
                            $accesorio = $devolucion->reserva->accesorios->find($accesorioId);
                        @endphp
                        @if($accesorio)
                            <div class="border border-gray-200 rounded-lg p-3">
                                <div class="font-medium text-gray-900 mb-2">{{ $accesorio->nombre }}</div>
                                <div class="flex items-center space-x-4 text-sm">
                                    @if(isset($condiciones['devuelto']) && $condiciones['devuelto'])
                                        <span class="text-green-600">✓ Devuelto</span>
                                    @else
                                        <span class="text-red-600">✗ No devuelto</span>
                                    @endif
                                    @if(isset($condiciones['tiene_danos']) && $condiciones['tiene_danos'])
                                        <span class="text-red-600 font-medium">⚠ Tiene Daños</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Observaciones --}}
        @if($devolucion->observaciones)
            <div class="mt-4 pt-4 border-t">
                <h3 class="font-semibold text-gray-900 mb-2">Observaciones</h3>
                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $devolucion->observaciones }}</p>
            </div>
        @endif
    </div>
</div>
@endsection


