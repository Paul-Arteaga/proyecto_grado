@extends('layout.navbar')

@section('titulo', 'Accesorios')

@section('contenido')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-0 mb-4 sm:mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Gestión de Accesorios</h1>
        <button onclick="abrirModalCrear()" class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm sm:text-base">
            + Nuevo Accesorio
        </button>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- Vista Desktop: Tabla --}}
    <div class="hidden lg:block bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Imagen</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($accesorios as $accesorio)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($accesorio->imagen)
                                <img src="{{ Storage::url($accesorio->imagen) }}" alt="{{ $accesorio->nombre }}" class="w-16 h-16 object-cover rounded">
                            @else
                                <div class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center text-gray-400 text-xs">Sin imagen</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $accesorio->nombre }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-500">{{ Str::limit($accesorio->descripcion, 50) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900">Bs. {{ number_format($accesorio->precio, 2) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $accesorio->stock ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($accesorio->activo)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Activo</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Inactivo</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button onclick="abrirModalEditar({{ $accesorio->id }}, '{{ addslashes($accesorio->nombre) }}', '{{ addslashes($accesorio->descripcion) }}', {{ $accesorio->precio }}, {{ $accesorio->stock ?? 'null' }}, {{ $accesorio->activo ? 'true' : 'false' }}, '{{ $accesorio->imagen ? Storage::url($accesorio->imagen) : '' }}')" 
                                    class="text-blue-600 hover:text-blue-900 mr-3">Editar</button>
                            <form action="{{ route('accesorio.destroy', $accesorio) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este accesorio?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500 text-sm">No hay accesorios registrados</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Vista Mobile: Cards --}}
    <div class="lg:hidden space-y-4">
        @forelse($accesorios as $accesorio)
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-start gap-4 mb-3">
                    @if($accesorio->imagen)
                        <img src="{{ Storage::url($accesorio->imagen) }}" alt="{{ $accesorio->nombre }}" class="w-20 h-20 object-cover rounded flex-shrink-0">
                    @else
                        <div class="w-20 h-20 bg-gray-200 rounded flex items-center justify-center text-gray-400 text-xs flex-shrink-0">Sin imagen</div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-gray-900 text-sm sm:text-base mb-1">{{ $accesorio->nombre }}</h3>
                        @if($accesorio->descripcion)
                            <p class="text-xs text-gray-500 mb-2 line-clamp-2">{{ $accesorio->descripcion }}</p>
                        @endif
                        <div class="flex items-center gap-3 mb-2">
                            <span class="text-sm font-semibold text-gray-900">Bs. {{ number_format($accesorio->precio, 2) }}</span>
                            <span class="text-xs text-gray-500">Stock: {{ $accesorio->stock ?? 'N/A' }}</span>
                            @if($accesorio->activo)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Activo</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Inactivo</span>
                            @endif
                        </div>
                        <div class="flex gap-2 mt-3">
                            <button onclick="abrirModalEditar({{ $accesorio->id }}, '{{ addslashes($accesorio->nombre) }}', '{{ addslashes($accesorio->descripcion) }}', {{ $accesorio->precio }}, {{ $accesorio->stock ?? 'null' }}, {{ $accesorio->activo ? 'true' : 'false' }}, '{{ $accesorio->imagen ? Storage::url($accesorio->imagen) : '' }}')" 
                                    class="flex-1 px-3 py-2 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 text-center">Editar</button>
                            <form action="{{ route('accesorio.destroy', $accesorio) }}" method="POST" class="flex-1" onsubmit="return confirm('¿Eliminar este accesorio?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full px-3 py-2 text-xs bg-red-600 text-white rounded hover:bg-red-700">Eliminar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <p class="text-gray-500 text-sm">No hay accesorios registrados</p>
            </div>
        @endforelse
    </div>
</div>

{{-- Modal Crear/Editar --}}
<div id="modalAccesorio" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-2 sm:p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[95vh] sm:max-h-[90vh] overflow-y-auto m-2 sm:m-4">
        <div class="sticky top-0 bg-white border-b px-4 sm:px-6 py-3 sm:py-4 flex justify-between items-center z-10">
            <h3 id="modalTitulo" class="text-lg sm:text-2xl font-bold text-gray-900">Nuevo Accesorio</h3>
            <button onclick="cerrarModal()" class="text-gray-500 hover:text-gray-700 text-xl sm:text-2xl">&times;</button>
        </div>

        <form id="formAccesorio" method="POST" enctype="multipart/form-data" class="p-4 sm:p-6">
            @csrf
            <input type="hidden" id="accesorio_id" name="accesorio_id">
            <input type="hidden" id="method_field" name="_method">

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre <span class="text-red-500">*</span></label>
                <input type="text" name="nombre" id="nombre" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                <textarea name="descripcion" id="descripcion" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Precio (Bs.) <span class="text-red-500">*</span></label>
                    <input type="number" name="precio" id="precio" step="0.01" min="0" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Stock</label>
                    <input type="number" name="stock" id="stock" min="0"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Imagen</label>
                <input type="file" name="imagen" id="imagen" accept="image/*"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <div id="imagenPreview" class="mt-2"></div>
            </div>

            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="activo" id="activo" value="1" checked
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">Activo</span>
                </label>
            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-4 pt-4 border-t">
                <button type="button" onclick="cerrarModal()" 
                        class="w-full sm:w-auto px-4 sm:px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-sm sm:text-base">
                    Cancelar
                </button>
                <button type="submit" 
                        class="w-full sm:w-auto px-4 sm:px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition text-sm sm:text-base">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
  window.accesorioStoreUrl = "{{ route('accesorio.store') }}";
  window.accesorioUpdateUrl = "{{ route('accesorio.update', ':id') }}";
</script>
<script src="{{ asset('js/accesorio/index.js') }}"></script>
@endsection

