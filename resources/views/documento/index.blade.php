@extends('layout.navbar')

@section('titulo', 'Documentos de Clientes')

@section('contenido')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Documentos de Clientes</h1>
            <p class="text-sm text-gray-600">Consulta la documentación subida por cada usuario.</p>
        </div>
        <form method="GET" action="{{ route('documento.index') }}" class="w-full lg:w-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <div class="relative flex-1 sm:flex-none sm:w-64">
                <input type="text" name="buscar" value="{{ request('buscar') }}"
                       placeholder="Buscar por nombre, carnet o correo..."
                       class="w-full border border-gray-300 rounded-lg pl-3 pr-10 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @if(request('buscar'))
                    <a href="{{ route('documento.index', ['estado' => request('estado')]) }}"
                       class="absolute inset-y-0 right-8 flex items-center text-gray-400 hover:text-gray-600">
                        ×
                    </a>
                @endif
                <span class="absolute inset-y-0 right-3 flex items-center text-gray-400">
                    🔍
                </span>
            </div>
            <div class="relative flex-1 sm:flex-none sm:w-48">
                <select name="estado"
                        class="w-full border border-gray-300 rounded-lg pl-3 pr-8 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos los estados</option>
                    <option value="verificado" {{ request('estado') === 'verificado' ? 'selected' : '' }}>Verificados</option>
                    <option value="pendiente" {{ request('estado') === 'pendiente' ? 'selected' : '' }}>Pendientes</option>
                </select>
                @if(request('estado'))
                    <a href="{{ route('documento.index', ['buscar' => request('buscar')]) }}"
                       class="absolute inset-y-0 right-7 flex items-center text-gray-400 hover:text-gray-600 text-lg leading-none">
                        ×
                    </a>
                @endif
                <span class="absolute inset-y-0 right-2 flex items-center text-gray-400">
                    📁
                </span>
            </div>
            <div class="flex gap-2">
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700">
                    Aplicar
                </button>
                @if(request()->has('buscar') || request()->has('estado'))
                    <a href="{{ route('documento.index') }}"
                       class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                        Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="hidden md:block">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Carnet</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Licencia</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($usuarios as $usuario)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-900">{{ $usuario->name ?? $usuario->username }}</div>
                                <div class="text-xs text-gray-500">Usuario: {{ $usuario->username }}</div>
                                <div class="text-xs text-gray-500">Correo: {{ $usuario->email ?? '—' }}</div>
                            </td>
                            <td class="px-4 py-3 space-y-1">
                                <p class="text-sm text-gray-700">Número: {{ $usuario->numero_carnet ?? '—' }}</p>
                                <div class="flex flex-wrap gap-2 text-xs">
                                    @if($usuario->carnet_anverso)
                                        <a href="{{ asset('storage/' . $usuario->carnet_anverso) }}" target="_blank" class="text-blue-600 hover:underline">Anverso</a>
                                    @endif
                                    @if($usuario->carnet_reverso)
                                        <a href="{{ asset('storage/' . $usuario->carnet_reverso) }}" target="_blank" class="text-blue-600 hover:underline">Reverso</a>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 space-y-1">
                                <p class="text-sm text-gray-700">
                                    Vence: {{ $usuario->licencia_fecha_vencimiento ? \Carbon\Carbon::parse($usuario->licencia_fecha_vencimiento)->format('d/m/Y') : '—' }}
                                </p>
                                <div class="flex flex-wrap gap-2 text-xs">
                                    @if($usuario->licencia_anverso)
                                        <a href="{{ asset('storage/' . $usuario->licencia_anverso) }}" target="_blank" class="text-blue-600 hover:underline">Anverso</a>
                                    @endif
                                    @if($usuario->licencia_reverso)
                                        <a href="{{ asset('storage/' . $usuario->licencia_reverso) }}" target="_blank" class="text-blue-600 hover:underline">Reverso</a>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if($usuario->documentos_verificados)
                                    <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Verificado</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-700">Pendiente</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($usuario->documentos_verificados_at)
                                    <p class="text-xs text-gray-500">Verificado el {{ $usuario->documentos_verificados_at->format('d/m/Y H:i') }}</p>
                                @else
                                    <span class="text-xs text-gray-400">Sin verificación registrada</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">No se encontraron usuarios.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="md:hidden divide-y divide-gray-200">
            @forelse($usuarios as $usuario)
                <div class="p-4 space-y-2">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $usuario->name ?? $usuario->username }}</p>
                            <p class="text-xs text-gray-500">Usuario: {{ $usuario->username }}</p>
                        </div>
                        @if($usuario->documentos_verificados)
                            <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Verificado</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-700">Pendiente</span>
                        @endif
                    </div>
                    <div class="text-sm text-gray-700 space-y-1">
                        <p>Carnet: {{ $usuario->numero_carnet ?? '—' }}</p>
                        <div class="text-xs flex flex-wrap gap-2">
                            @if($usuario->carnet_anverso)
                                <a href="{{ asset('storage/' . $usuario->carnet_anverso) }}" target="_blank" class="text-blue-600 hover:underline">Carnet Anverso</a>
                            @endif
                            @if($usuario->carnet_reverso)
                                <a href="{{ asset('storage/' . $usuario->carnet_reverso) }}" target="_blank" class="text-blue-600 hover:underline">Carnet Reverso</a>
                            @endif
                        </div>
                        <p>Licencia vence: {{ $usuario->licencia_fecha_vencimiento ? \Carbon\Carbon::parse($usuario->licencia_fecha_vencimiento)->format('d/m/Y') : '—' }}</p>
                        <div class="text-xs flex flex-wrap gap-2">
                            @if($usuario->licencia_anverso)
                                <a href="{{ asset('storage/' . $usuario->licencia_anverso) }}" target="_blank" class="text-blue-600 hover:underline">Licencia Anverso</a>
                            @endif
                            @if($usuario->licencia_reverso)
                                <a href="{{ asset('storage/' . $usuario->licencia_reverso) }}" target="_blank" class="text-blue-600 hover:underline">Licencia Reverso</a>
                            @endif
                        </div>
                    </div>
                    @if($usuario->documentos_verificados_at)
                        <p class="text-xs text-gray-500">Verificado el {{ $usuario->documentos_verificados_at->format('d/m/Y H:i') }}</p>
                    @endif
                </div>
            @empty
                <div class="p-6 text-center text-gray-500">No se encontraron usuarios.</div>
            @endforelse
        </div>
    </div>

    <div class="mt-4">
        {{ $usuarios->links() }}
    </div>
</div>
@endsection






