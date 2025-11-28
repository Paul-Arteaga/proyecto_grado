@extends('layout.navbar')

@section('titulo', 'Configuración de Contrato')

@section('contenido')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Contrato de Servicio</h1>
        <p class="text-sm text-gray-600">Sube el archivo PDF o Word del contrato que los usuarios deberán aceptar antes de enviar una reserva.</p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow rounded-lg p-4 sm:p-6 space-y-4">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-2xl">
                📄
            </div>
            <div>
                <p class="text-lg font-semibold text-gray-900">Contrato actual</p>
                @if($config)
                    <p class="text-sm text-gray-500">Última actualización: {{ $config->updated_at->format('d/m/Y H:i') }}</p>
                    <p class="text-sm text-gray-500">Archivo: {{ $config->nombre_original ?? basename($config->archivo) }}</p>
                @else
                    <p class="text-sm text-gray-500">No se ha configurado un contrato todavía.</p>
                @endif
            </div>
        </div>

        @if($config)
            <a href="{{ asset('storage/' . $config->archivo) }}" target="_blank"
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
                Descargar contrato actual
            </a>
        @endif

        <form action="{{ route('contrato.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Nuevo archivo de contrato (PDF o Word) <span class="text-red-500">*</span>
                </label>
                <input type="file" name="contrato" accept=".pdf,.doc,.docx" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-500 mt-1">Peso máximo 20 MB. Al guardar se reemplaza el contrato anterior.</p>
                @error('contrato')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                    Guardar contrato
                </button>
            </div>
        </form>
    </div>
</div>
@endsection






