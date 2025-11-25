@extends('layout.navbar')

@section('titulo', 'Devoluciones')

@section('contenido')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-4 sm:mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Devoluciones</h1>
            <p class="text-sm text-gray-600">Historial de reservas confirmadas o completadas para control de devoluciones</p>
        </div>
        <form method="GET" action="{{ route('devolucion.index') }}" class="w-full lg:w-auto flex items-center gap-2">
            <div class="relative flex-1 lg:flex-none lg:w-64">
                <input type="text" name="buscar" value="{{ request('buscar') }}"
                       placeholder="Buscar por cliente, placa, estado..."
                       class="w-full border border-gray-300 rounded-lg pl-3 pr-10 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @if(request('buscar'))
                    <a href="{{ route('devolucion.index') }}" class="absolute inset-y-0 right-8 flex items-center text-gray-400 hover:text-gray-600">
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

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    {{-- Vista Desktop: Tabla --}}
    <div class="hidden lg:block bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="bg-gray-100 text-left">
                    <th class="px-4 py-3">ID Reserva</th>
                    <th class="px-4 py-3">Cliente</th>
                    <th class="px-4 py-3">Vehículo</th>
                    <th class="px-4 py-3">Fechas</th>
                    <th class="px-4 py-3">Accesorios</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reservas as $reserva)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $reserva->id }}</td>
                        <td class="px-4 py-3">
                            {{ $reserva->user?->username ?? $reserva->user?->email ?? '—' }}
                            @if($reserva->user?->numero_carnet)
                                <span class="text-xs text-gray-500 block">Carnet: {{ $reserva->user->numero_carnet }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $reserva->vehiculo?->marca ?? '—' }} {{ $reserva->vehiculo?->modelo ?? '' }}</div>
                            @if($reserva->vehiculo?->placa)
                                <span class="text-xs text-gray-500">Placa: {{ $reserva->vehiculo->placa }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-xs">
                                <div>Inicio: {{ \Carbon\Carbon::parse($reserva->fecha_inicio)->format('d/m/Y') }}</div>
                                <div>Fin: {{ \Carbon\Carbon::parse($reserva->fecha_fin)->format('d/m/Y') }}</div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @if($reserva->accesorios->count() > 0)
                                <div class="text-xs space-y-1">
                                    @foreach($reserva->accesorios->where('pivot.estado', '!=', 'rechazado') as $acc)
                                        <div>{{ $acc->nombre }} (x{{ $acc->pivot->cantidad }})</div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-400 text-xs">Sin accesorios</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($reserva->devolucion)
                                <span class="px-2 py-1 text-xs rounded
                                    @if($reserva->devolucion->estado == 'completada') bg-green-100 text-green-700
                                    @elseif($reserva->devolucion->estado == 'con_danos') bg-red-100 text-red-700
                                    @else bg-yellow-100 text-yellow-700
                                    @endif">
                                    {{ ucfirst($reserva->devolucion->estado) }}
                                </span>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ \Carbon\Carbon::parse($reserva->devolucion->fecha_hora_devolucion)->format('d/m/Y H:i') }}
                                </div>
                            @else
                                <span class="px-2 py-1 text-xs rounded bg-orange-100 text-orange-700">Pendiente</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($reserva->devolucion)
                                <a href="{{ route('devolucion.show', $reserva->devolucion->id) }}" 
                                   class="text-blue-600 hover:text-blue-800 text-xs">Ver Detalles</a>
                            @else
                                <a href="{{ route('devolucion.create', $reserva->id) }}" 
                                   class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs">
                                    Registrar Devolución
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">No hay reservas confirmadas ni completadas para mostrar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Vista Mobile: Cards --}}
    <div class="lg:hidden space-y-4">
        @forelse($reservas as $reserva)
            <div class="bg-white shadow rounded-lg p-4">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <p class="text-xs text-gray-500">ID Reserva: {{ $reserva->id }}</p>
                        <h3 class="font-semibold text-gray-900 mt-1">
                            {{ $reserva->vehiculo?->marca ?? '—' }} {{ $reserva->vehiculo?->modelo ?? '' }}
                        </h3>
                        @if($reserva->vehiculo?->placa)
                            <p class="text-xs text-gray-500">Placa: {{ $reserva->vehiculo->placa }}</p>
                        @endif
                    </div>
                    <div>
                        @if($reserva->devolucion)
                            <span class="px-2 py-1 text-xs rounded
                                @if($reserva->devolucion->estado == 'completada') bg-green-100 text-green-700
                                @elseif($reserva->devolucion->estado == 'con_danos') bg-red-100 text-red-700
                                @else bg-yellow-100 text-yellow-700
                                @endif">
                                {{ ucfirst($reserva->devolucion->estado) }}
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs rounded bg-orange-100 text-orange-700">Pendiente</span>
                        @endif
                    </div>
                </div>

                <div class="space-y-2 text-sm mb-3">
                    <div>
                        <span class="text-xs text-gray-500">Cliente:</span>
                        <span class="font-medium">{{ $reserva->user?->username ?? '—' }}</span>
                        @if($reserva->user?->numero_carnet)
                            <span class="text-xs text-gray-500">({{ $reserva->user->numero_carnet }})</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-xs text-gray-500">Fechas:</span>
                        <span class="text-xs">{{ \Carbon\Carbon::parse($reserva->fecha_inicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($reserva->fecha_fin)->format('d/m/Y') }}</span>
                    </div>
                    @if($reserva->accesorios->count() > 0)
                        <div>
                            <span class="text-xs text-gray-500">Accesorios:</span>
                            <div class="text-xs mt-1">
                                @foreach($reserva->accesorios->where('pivot.estado', '!=', 'rechazado') as $acc)
                                    <span class="inline-block mr-2">{{ $acc->nombre }} (x{{ $acc->pivot->cantidad }})</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="border-t pt-3">
                    @if($reserva->devolucion)
                        <a href="{{ route('devolucion.show', $reserva->devolucion->id) }}" 
                           class="w-full block text-center px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs">
                            Ver Detalles
                        </a>
                    @else
                        <a href="{{ route('devolucion.create', $reserva->id) }}" 
                           class="w-full block text-center px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs">
                            Registrar Devolución
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white shadow rounded-lg p-8 text-center">
                <p class="text-gray-500 text-sm">No hay reservas confirmadas ni completadas para mostrar.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection

