@extends('layout.navbar')

@section('titulo', 'Registrar Devolución')

@php
    use Illuminate\Support\Facades\Auth;
@endphp

@section('contenido')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8">
    <div class="mb-6">
        <a href="{{ route('devolucion.index') }}" class="text-blue-600 hover:text-blue-800 text-sm sm:text-base">
            ← Volver a Devoluciones
        </a>
    </div>

    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-6">Registrar Devolución</h1>

    {{-- Información de la Reserva --}}
    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded">
        <h2 class="font-semibold text-gray-900 mb-3">Información de la Reserva</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
            <div>
                <span class="text-gray-600">Cliente:</span>
                <span class="font-medium">{{ $reserva->user?->username ?? '—' }}</span>
                @if($reserva->user?->numero_carnet)
                    <span class="text-gray-500">({{ $reserva->user->numero_carnet }})</span>
                @endif
            </div>
            <div>
                <span class="text-gray-600">Vehículo:</span>
                <span class="font-medium">{{ $reserva->vehiculo?->marca ?? '—' }} {{ $reserva->vehiculo?->modelo ?? '' }}</span>
            </div>
            <div>
                <span class="text-gray-600">Placa:</span>
                <span class="font-medium">{{ $reserva->vehiculo?->placa ?? '—' }}</span>
            </div>
            <div>
                <span class="text-gray-600">Período:</span>
                <span class="font-medium">
                    {{ \Carbon\Carbon::parse($reserva->fecha_inicio)->format('d/m/Y') }} - 
                    {{ \Carbon\Carbon::parse($reserva->fecha_fin)->format('d/m/Y') }}
                </span>
            </div>
        </div>
    </div>

    <form action="{{ route('devolucion.store', $reserva->id) }}" method="POST" class="bg-white shadow rounded-lg p-4 sm:p-6">
        @csrf

        {{-- Fecha y Hora de Devolución --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Fecha de Devolución <span class="text-red-500">*</span>
                </label>
                <input type="date" name="fecha_devolucion" 
                       value="{{ old('fecha_devolucion', \Carbon\Carbon::now()->format('Y-m-d')) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       required>
                @error('fecha_devolucion')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Hora de Devolución <span class="text-red-500">*</span>
                </label>
                <input type="time" name="hora_devolucion" 
                       value="{{ old('hora_devolucion', \Carbon\Carbon::now()->format('H:i')) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       required>
                @error('hora_devolucion')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Usuario que Recibe --}}
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Usuario que Recibe el Vehículo
            </label>
            <div class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-gray-900 text-sm">
                {{ Auth::user()->username }}
            </div>
            <p class="text-xs text-gray-500 mt-1">Este dato se registra automáticamente según tu sesión.</p>
        </div>

    {{-- Kilometraje de devolución --}}
    <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">
            Kilometraje al devolver <span class="text-red-500">*</span>
        </label>
        <input type="number" name="km_retorno"
               value="{{ old('km_retorno', $reserva->vehiculo?->km_actual) }}"
               min="{{ $reserva->vehiculo?->km_actual ?? 0 }}"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
               required>
        <p class="text-xs text-gray-500 mt-1">El valor no puede ser menor al kilometraje registrado al entregar el vehículo ({{ $reserva->vehiculo?->km_actual ?? 0 }} km).</p>
        @error('km_retorno')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

        {{-- Condiciones del Vehículo --}}
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Condiciones del Vehículo</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="condiciones_vehiculo[combustible_ok]" value="1" 
                           {{ old('condiciones_vehiculo.combustible_ok') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-gray-700">Combustible OK</span>
                </label>
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="condiciones_vehiculo[limpieza_ok]" value="1"
                           {{ old('condiciones_vehiculo.limpieza_ok') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-gray-700">Limpieza OK</span>
                </label>
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="condiciones_vehiculo[llantas_ok]" value="1"
                           {{ old('condiciones_vehiculo.llantas_ok') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-gray-700">Llantas OK</span>
                </label>
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="condiciones_vehiculo[luces_ok]" value="1"
                           {{ old('condiciones_vehiculo.luces_ok') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-gray-700">Luces OK</span>
                </label>
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="condiciones_vehiculo[documentos_ok]" value="1"
                           {{ old('condiciones_vehiculo.documentos_ok') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-gray-700">Documentos OK</span>
                </label>
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="condiciones_vehiculo[tiene_danos]" value="1"
                           {{ old('condiciones_vehiculo.tiene_danos') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                    <span class="text-sm text-red-700 font-medium">Tiene Daños</span>
                </label>
            </div>
            @error('condiciones_vehiculo')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Condiciones de Accesorios --}}
        @if($reserva->accesorios->where('pivot.estado', '!=', 'rechazado')->count() > 0)
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Condiciones de Accesorios</h3>
                <div class="space-y-3">
                    @foreach($reserva->accesorios->where('pivot.estado', '!=', 'rechazado') as $accesorio)
                        <div class="border border-gray-200 rounded-lg p-3">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-medium text-gray-900">{{ $accesorio->nombre }}</span>
                                <span class="text-sm text-gray-500">Cantidad: {{ $accesorio->pivot->cantidad }}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" 
                                           name="condiciones_accesorios[{{ $accesorio->id }}][devuelto]" 
                                           value="1"
                                           {{ old("condiciones_accesorios.{$accesorio->id}.devuelto") ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">Devuelto</span>
                                </label>
                                <label class="flex items-center space-x-2 cursor-pointer ml-4">
                                    <input type="checkbox" 
                                           name="condiciones_accesorios[{{ $accesorio->id }}][tiene_danos]" 
                                           value="1"
                                           {{ old("condiciones_accesorios.{$accesorio->id}.tiene_danos") ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                    <span class="text-sm text-red-700 font-medium">Tiene Daños</span>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Observaciones --}}
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Observaciones
            </label>
            <textarea name="observaciones" rows="4" 
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="Notas adicionales sobre la devolución...">{{ old('observaciones') }}</textarea>
            @error('observaciones')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Botones --}}
        <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t">
            <a href="{{ route('devolucion.index') }}" 
               class="w-full sm:w-auto px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-center text-sm sm:text-base">
                Cancelar
            </a>
            <button type="submit" 
                    class="w-full sm:w-auto px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition text-sm sm:text-base">
                Registrar Devolución
            </button>
        </div>
    </form>
</div>
@endsection


