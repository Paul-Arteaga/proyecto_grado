@extends('layout.navbar')

@section('titulo', 'Accesorios Disponibles')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('contenido')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Accesorios Disponibles</h1>

    @if($reservaSeleccionada)
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
            <p class="text-sm text-blue-700">
                <strong>Reserva seleccionada:</strong> {{ $reservaSeleccionada->vehiculo->marca }} {{ $reservaSeleccionada->vehiculo->modelo }} 
                (Placa: {{ $reservaSeleccionada->vehiculo->placa }}) - 
                {{ \Carbon\Carbon::parse($reservaSeleccionada->fecha_inicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($reservaSeleccionada->fecha_fin)->format('d/m/Y') }}
            </p>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($accesorios as $accesorio)
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                <div class="h-48 bg-gray-200 flex items-center justify-center">
                    @if($accesorio->imagen)
                        <img src="{{ Storage::url($accesorio->imagen) }}" alt="{{ $accesorio->nombre }}" class="w-full h-full object-cover">
                    @else
                        <span class="text-gray-400">Sin imagen</span>
                    @endif
                </div>
                <div class="p-4">
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $accesorio->nombre }}</h3>
                    @if($accesorio->descripcion)
                        <p class="text-sm text-gray-600 mb-3">{{ $accesorio->descripcion }}</p>
                    @endif
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-2xl font-bold text-blue-600">Bs. {{ number_format($accesorio->precio, 2) }}</span>
                        @if($accesorio->stock !== null)
                            @if($accesorio->stock > 0)
                                <span class="text-sm text-green-600 font-semibold">Stock: {{ $accesorio->stock }}</span>
                            @else
                                <span class="text-sm text-red-600 font-semibold">Agotado</span>
                            @endif
                        @endif
                    </div>
                    @php
                        $hayStock = $accesorio->stock === null || $accesorio->stock > 0;
                    @endphp
                    @if($hayStock)
                        <button type="button" onclick="console.log('Botón clickeado'); abrirModalAccesorio({{ $accesorio->id }}, {{ json_encode($accesorio->nombre) }}, {{ $accesorio->precio }}, {{ json_encode($accesorio->descripcion ?? '') }}, {{ json_encode($accesorio->imagen ? Storage::url($accesorio->imagen) : '') }}, {{ $accesorio->stock ?? 'null' }})" 
                                class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Agregar al Vehículo
                        </button>
                    @else
                        <button disabled
                                class="w-full px-4 py-2 bg-gray-400 text-white rounded-lg cursor-not-allowed font-medium flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Accesorio Agotado
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <p class="text-gray-500 text-lg">No hay accesorios disponibles en este momento.</p>
            </div>
        @endforelse
    </div>
</div>

{{-- Modal para agregar accesorio a reserva --}}
<div id="modalAgregarAccesorio" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-2 sm:p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[95vh] sm:max-h-[90vh] overflow-y-auto m-2 sm:m-4">
        <div class="sticky top-0 bg-white border-b px-4 sm:px-6 py-3 sm:py-4 flex justify-between items-center z-10">
            <h3 id="modalAccesorioTitulo" class="text-lg sm:text-2xl font-bold text-gray-900">Agregar Accesorio</h3>
            <button onclick="cerrarModalAccesorio()" class="text-gray-500 hover:text-gray-700 text-xl sm:text-2xl">&times;</button>
        </div>

        <form id="formAgregarAccesorio" method="POST" enctype="multipart/form-data" class="p-4 sm:p-6">
            @csrf
            <input type="hidden" id="accesorio_id_modal" name="accesorio_id">
            <input type="hidden" id="reserva_id_hidden" name="reserva_id">

            {{-- Información del accesorio --}}
            <div class="mb-4 sm:mb-6">
                <div id="accesorioInfo" class="flex flex-col sm:flex-row items-start sm:items-center space-y-3 sm:space-y-0 sm:space-x-4 p-3 sm:p-4 bg-gray-50 rounded-lg">
                    <div id="accesorioImagen" class="w-16 h-16 sm:w-20 sm:h-20 bg-gray-200 rounded flex items-center justify-center flex-shrink-0 mx-auto sm:mx-0"></div>
                    <div class="flex-1 text-center sm:text-left w-full sm:w-auto">
                        <h4 id="accesorioNombre" class="text-base sm:text-lg font-semibold text-gray-900"></h4>
                        <p id="accesorioDescripcion" class="text-xs sm:text-sm text-gray-600"></p>
                        <p id="accesorioPrecio" class="text-lg sm:text-xl font-bold text-blue-600 mt-1"></p>
                    </div>
                </div>
            </div>

            {{-- Seleccionar reserva --}}
            <div class="mb-4 sm:mb-6">
                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                    Selecciona la reserva a la que deseas agregar este accesorio <span class="text-red-500">*</span>
                </label>
                @if($reservasConfirmadas->count() > 0)
                    <select name="reserva_id" id="reserva_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Selecciona una reserva --</option>
                        @foreach($reservasConfirmadas as $reserva)
                            <option value="{{ $reserva->id }}" {{ $reservaSeleccionada && $reservaSeleccionada->id == $reserva->id ? 'selected' : '' }}>
                                {{ $reserva->vehiculo->marca }} {{ $reserva->vehiculo->modelo }} (Placa: {{ $reserva->vehiculo->placa }}) - 
                                {{ \Carbon\Carbon::parse($reserva->fecha_inicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($reserva->fecha_fin)->format('d/m/Y') }}
                            </option>
                        @endforeach
                    </select>
                @else
                    <p class="text-sm text-gray-500">No tienes reservas confirmadas. Primero debes tener una reserva confirmada para agregar accesorios.</p>
                @endif
            </div>

            {{-- Cantidad --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Cantidad <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center space-x-4">
                    <button type="button" onclick="decrementarCantidad()" class="w-12 h-12 flex items-center justify-center bg-gray-200 rounded hover:bg-gray-300">-</button>
                    <input type="number" name="cantidad" id="cantidad" value="1" min="1" required
                           class="w-20 text-center border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="button" onclick="incrementarCantidad()" class="w-12 h-12 flex items-center justify-center bg-gray-200 rounded hover:bg-gray-300">+</button>
                </div>
                <p id="precioTotal" class="text-sm text-gray-600 mt-2">Total: <span class="font-semibold text-blue-600">Bs. 0.00</span></p>
            </div>

            {{-- Código QR para Pago de Accesorios --}}
            <div class="mb-4 sm:mb-6 text-center">
                <h4 class="font-semibold text-gray-900 mb-2 sm:mb-3 text-sm sm:text-base">Escanea el código QR para realizar el pago</h4>
                <div class="bg-white p-3 sm:p-4 rounded-lg border-2 border-gray-200 inline-block">
                    <div id="qr_accesorios_container" class="w-40 h-40 sm:w-48 sm:h-48 mx-auto bg-white flex items-center justify-center">
                        @php
                            $configPago = \App\Models\ConfiguracionPago::activa();
                        @endphp
                        @if($configPago && $configPago->qr_imagen_accesorios)
                            <img src="{{ asset('storage/' . $configPago->qr_imagen_accesorios) }}" alt="QR de Pago Accesorios" class="w-full h-full object-contain">
                        @else
                            <p class="text-gray-400 text-sm">QR no configurado. Contacta al administrador.</p>
                        @endif
                    </div>
                </div>
                @if($configPago && $configPago->numero_cuenta)
                    <p class="text-sm text-gray-600 mt-2">Número de cuenta: <span class="font-semibold">{{ $configPago->numero_cuenta }}</span></p>
                @endif
                @if($configPago && $configPago->instrucciones_pago)
                    <p class="text-xs text-gray-500 mt-2">{{ $configPago->instrucciones_pago }}</p>
                @endif
                <p class="text-sm text-gray-600 mt-2">Realiza el pago y sube el comprobante antes de confirmar</p>
                <p class="text-xs text-gray-500 mt-1">Monto a pagar: <span id="monto_accesorio_qr" class="font-semibold">Bs. 0.00</span></p>
            </div>

            {{-- Comprobante de pago --}}
            <div class="mb-4 sm:mb-6">
                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                    Comprobante de Pago <span class="text-red-500">*</span>
                </label>
                <input type="file" name="comprobante_pago" accept=".pdf,.jpg,.jpeg,.png" required
                       class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm">
                <p class="text-xs text-gray-500 mt-1">Formatos: PDF, JPG, PNG (máx. 5MB)</p>
            </div>

            {{-- Botones --}}
            <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-4 pt-4 border-t">
                <button type="button" onclick="cerrarModalAccesorio()" 
                        class="w-full sm:w-auto px-4 sm:px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-sm sm:text-base">
                    Cancelar
                </button>
                <button type="submit" 
                        class="w-full sm:w-auto px-4 sm:px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition text-sm sm:text-base"
                        {{ $reservasConfirmadas->count() == 0 ? 'disabled' : '' }}>
                    Agregar Accesorio
                </button>
            </div>
        </form>
    </div>
</div>

<script src="{{ asset('js/accesorio/catalogo.js') }}"></script>
@endsection

