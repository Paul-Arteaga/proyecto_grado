@extends('layout.navbar')

@section('titulo', 'Mantenimiento de Vehículos')

@section('contenido')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Mantenimiento de Vehículos</h1>
            <p class="text-sm text-gray-600">Se listan los vehículos que superan {{ number_format($kmThreshold / 1000, 0) }}k km desde su último servicio o que ya están en mantenimiento.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            <ul class="list-disc ml-4 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white shadow rounded-lg p-4 sm:p-6 space-y-6">
        @php
            $pendientes = $vehiculos->where('estado', '!=', 'mantenimiento')->values();
            $enProceso = $vehiculos->where('estado', 'mantenimiento')->values();
        @endphp

        <div>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">Vehículos próximos a mantenimiento</h2>
            @if($pendientes->isEmpty())
                <p class="text-sm text-gray-500">No hay vehículos que superen el umbral aún.</p>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($pendientes as $vehiculo)
                        <div class="border border-gray-200 rounded-lg p-4 space-y-2">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-lg font-semibold text-gray-900">{{ $vehiculo->marca }} {{ $vehiculo->modelo }}</p>
                                    <p class="text-xs text-gray-500">Placa: {{ $vehiculo->placa }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-700">Disponible</span>
                            </div>
                            <div class="grid grid-cols-2 gap-3 text-sm text-gray-700">
                                <p><span class="text-gray-500">Km actual:</span> {{ number_format($vehiculo->km_actual) }}</p>
                                <p><span class="text-gray-500">Último mant.:</span> {{ number_format($vehiculo->km_ultimo_mantenimiento) }}</p>
                                <p><span class="text-gray-500">Diferencia:</span> {{ number_format($vehiculo->km_diff) }} km</p>
                                <p><span class="text-gray-500">Categoría:</span> {{ $vehiculo->categoria->nombre ?? '—' }}</p>
                            </div>
                            <form method="POST" action="{{ route('mantenimiento.derivar', $vehiculo) }}" class="pt-2 border-t border-gray-100 space-y-2">
                                @csrf
                                <label class="block text-sm text-gray-600">Observaciones (opcional)</label>
                                <textarea name="observaciones" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Notas para el taller..."></textarea>
                                <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-orange-600 text-white rounded-lg text-sm font-semibold hover:bg-orange-700 transition">
                                    Derivar a mantenimiento
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">Vehículos en mantenimiento</h2>
            @if($enProceso->isEmpty())
                <p class="text-sm text-gray-500">No hay vehículos en mantenimiento actualmente.</p>
            @else
                <div class="space-y-4">
                    @foreach($enProceso as $vehiculo)
                        @php
                            $mantenimiento = $vehiculo->mantenimiento_activo;
                        @endphp
                        <div class="border border-gray-200 rounded-lg p-4 space-y-3 bg-blue-50">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-lg font-semibold text-gray-900">{{ $vehiculo->marca }} {{ $vehiculo->modelo }}</p>
                                    <p class="text-xs text-gray-500">Placa: {{ $vehiculo->placa }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded bg-blue-200 text-blue-800">En mantenimiento</span>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm text-gray-700">
                                <p><span class="text-gray-500">Km actual:</span> {{ number_format($vehiculo->km_actual) }}</p>
                                <p><span class="text-gray-500">Km inicio mant.:</span> {{ number_format($mantenimiento->km_inicio ?? $vehiculo->km_actual) }}</p>
                                <p><span class="text-gray-500">Categoría:</span> {{ $vehiculo->categoria->nombre ?? '—' }}</p>
                                <p><span class="text-gray-500">Creado:</span> {{ optional($mantenimiento)->created_at?->format('d/m/Y') ?? '—' }}</p>
                            </div>

                            @if($mantenimiento)
                                <form method="POST" action="{{ route('mantenimiento.completar', $mantenimiento) }}" class="space-y-3 pt-3 border-t border-gray-200">
                                    @csrf
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Kilometraje final <span class="text-red-500">*</span></label>
                                            <input type="number" name="km_fin" min="{{ $mantenimiento->km_inicio }}" required
                                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                   placeholder="Ej. {{ $vehiculo->km_actual + 50 }}">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Checklist</label>
                                            <div class="grid grid-cols-2 gap-2 text-sm text-gray-600">
                                                <label class="flex items-center gap-2">
                                                    <input type="checkbox" name="checks[motor]" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                    Motor
                                                </label>
                                                <label class="flex items-center gap-2">
                                                    <input type="checkbox" name="checks[frenos]" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                    Frenos
                                                </label>
                                                <label class="flex items-center gap-2">
                                                    <input type="checkbox" name="checks[llantas]" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                    Llantas
                                                </label>
                                                <label class="flex items-center gap-2">
                                                    <input type="checkbox" name="checks[aceite]" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                    Aceite
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                                        <textarea name="observaciones" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Notas del servicio..."></textarea>
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition">
                                            Finalizar mantenimiento
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection


