@extends('layout.navbar')

@section('titulo', 'Configuración de Pagos')

@section('contenido')
<div class="min-h-screen bg-gray-50 py-8">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-lg shadow-md p-6">
      <h1 class="text-3xl font-bold text-gray-900 mb-6">Configuración de Pagos</h1>

      @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
          <p class="text-green-700">{{ session('success') }}</p>
        </div>
      @endif

      <form action="{{ route('pago.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Imagen QR para Vehículos --}}
        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Código QR para Pagos de Vehículos
          </label>
          @if($configuracion && $configuracion->qr_imagen_vehiculos)
            <div class="mb-4">
              <p class="text-sm text-gray-600 mb-2">QR Actual para Vehículos:</p>
              <img src="{{ asset('storage/' . $configuracion->qr_imagen_vehiculos) }}" alt="QR de Pago Vehículos" class="w-48 h-48 border-2 border-gray-200 rounded-lg object-contain bg-white p-2">
            </div>
          @endif
          <input type="file" name="qr_imagen_vehiculos" accept="image/png,image/jpeg,image/jpg" 
                 class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <p class="text-xs text-gray-500 mt-1">Formatos: PNG, JPG, JPEG (máx. 5MB). Esta imagen será la que vean los usuarios al reservar vehículos.</p>
        </div>

        {{-- Imagen QR para Accesorios --}}
        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Código QR para Pagos de Accesorios
          </label>
          @if($configuracion && $configuracion->qr_imagen_accesorios)
            <div class="mb-4">
              <p class="text-sm text-gray-600 mb-2">QR Actual para Accesorios:</p>
              <img src="{{ asset('storage/' . $configuracion->qr_imagen_accesorios) }}" alt="QR de Pago Accesorios" class="w-48 h-48 border-2 border-gray-200 rounded-lg object-contain bg-white p-2">
            </div>
          @endif
          <input type="file" name="qr_imagen_accesorios" accept="image/png,image/jpeg,image/jpg" 
                 class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <p class="text-xs text-gray-500 mt-1">Formatos: PNG, JPG, JPEG (máx. 5MB). Esta imagen será la que vean los usuarios al agregar accesorios a sus reservas.</p>
        </div>

        {{-- Número de Cuenta --}}
        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Número de Cuenta
          </label>
          <input type="text" name="numero_cuenta" value="{{ old('numero_cuenta', $configuracion->numero_cuenta ?? '') }}" 
                 placeholder="Ej: 1234567890"
                 class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <p class="text-xs text-gray-500 mt-1">Número de cuenta bancaria para transferencias (opcional).</p>
        </div>

        {{-- Instrucciones de Pago --}}
        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Instrucciones de Pago
          </label>
          <textarea name="instrucciones_pago" rows="4" 
                    placeholder="Ej: Realiza el pago escaneando el código QR o transfiriendo al número de cuenta. Luego sube el comprobante."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('instrucciones_pago', $configuracion->instrucciones_pago ?? '') }}</textarea>
          <p class="text-xs text-gray-500 mt-1">Instrucciones que verán los usuarios al realizar el pago (opcional).</p>
        </div>

        {{-- Botones --}}
        <div class="flex justify-end space-x-4 pt-4 border-t">
          <a href="{{ route('mostrar.index') }}" 
             class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
            Cancelar
          </a>
          <button type="submit" 
                  class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition shadow-sm hover:shadow-md">
            Guardar Configuración
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

