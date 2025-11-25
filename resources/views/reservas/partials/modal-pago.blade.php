{{-- Modal de Pago para Reserva --}}
<div id="modalPago" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-2 sm:p-4">
  <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[95vh] sm:max-h-[90vh] overflow-y-auto m-2 sm:m-4">
    <div class="sticky top-0 bg-white border-b px-4 sm:px-6 py-3 sm:py-4 flex justify-between items-center z-10">
      <h3 class="text-lg sm:text-2xl font-bold text-gray-900">Confirmar Reserva y Pago</h3>
      <button onclick="cerrarModalPago()" class="text-gray-500 hover:text-gray-700 text-xl sm:text-2xl">&times;</button>
    </div>

    <form id="formPago" action="{{ route('reservas.store') }}" method="POST" enctype="multipart/form-data" class="p-4 sm:p-6">
      @csrf
      
      <input type="hidden" id="vehiculo_id_pago" name="vehiculo_id">
      <input type="hidden" id="fecha_inicio_pago" name="fecha_inicio">
      <input type="hidden" id="fecha_fin_pago" name="fecha_fin">

      {{-- Información del Vehículo --}}
      <div class="bg-gray-50 rounded-lg p-3 sm:p-4 mb-4 sm:mb-6">
        <h4 class="font-semibold text-gray-900 mb-2 text-sm sm:text-base">Vehículo Seleccionado</h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 text-xs sm:text-sm">
          <div>
            <p class="text-gray-600">Marca/Modelo</p>
            <p class="font-medium" id="vehiculo_info"></p>
          </div>
          <div>
            <p class="text-gray-600">Placa</p>
            <p class="font-medium" id="vehiculo_placa"></p>
          </div>
          <div>
            <p class="text-gray-600">Precio Diario</p>
            <p class="font-medium" id="precio_diario"></p>
          </div>
          <div>
            <p class="text-gray-600">Días</p>
            <p class="font-medium" id="dias_reserva"></p>
          </div>
        </div>
      </div>

      {{-- Fechas --}}
      <div class="mb-4 sm:mb-6">
        <h4 class="font-semibold text-gray-900 mb-2 text-sm sm:text-base">Período de Reserva</h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
          <div>
            <label class="block text-xs sm:text-sm text-gray-700 mb-1">Fecha Inicio</label>
            <input type="date" id="fecha_inicio_display" disabled class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 bg-gray-50 text-xs sm:text-sm">
          </div>
          <div>
            <label class="block text-xs sm:text-sm text-gray-700 mb-1">Fecha Fin</label>
            <input type="date" id="fecha_fin_display" disabled class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 bg-gray-50 text-xs sm:text-sm">
          </div>
        </div>
      </div>

      {{-- Accesorios Opcionales --}}
      <div class="mb-4 sm:mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 sm:gap-0 mb-3">
          <h4 class="font-semibold text-gray-900 text-sm sm:text-base">🎨 Accesorios Opcionales</h4>
          <button type="button" onclick="toggleAccesorios()" class="text-xs sm:text-sm text-blue-600 hover:text-blue-800">
            <span id="toggleAccesoriosText">Agregar accesorios</span>
          </button>
        </div>
        <div id="accesoriosSection" class="hidden border border-gray-200 rounded-lg p-3 sm:p-4 bg-gray-50">
          @php
            $accesorios = \App\Models\Accesorio::where('activo', true)->get();
          @endphp
          @if($accesorios->count() > 0)
            <div class="space-y-3 max-h-60 overflow-y-auto">
              @foreach($accesorios as $accesorio)
                @php
                  $hayStock = $accesorio->stock === null || $accesorio->stock > 0;
                @endphp
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-2 sm:p-3 bg-white rounded-lg border border-gray-200 {{ !$hayStock ? 'opacity-60' : '' }} gap-3">
                  <div class="flex items-center space-x-2 sm:space-x-3 flex-1 w-full sm:w-auto">
                    @if($accesorio->imagen)
                      <img src="{{ Storage::url($accesorio->imagen) }}" alt="{{ $accesorio->nombre }}" class="w-10 h-10 sm:w-12 sm:h-12 object-cover rounded flex-shrink-0">
                    @else
                      <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gray-200 rounded flex items-center justify-center text-gray-400 text-xs flex-shrink-0">Sin img</div>
                    @endif
                    <div class="flex-1 min-w-0">
                      <p class="font-medium text-gray-900 text-sm sm:text-base truncate">{{ $accesorio->nombre }}</p>
                      @if($accesorio->descripcion)
                        <p class="text-xs text-gray-500 hidden sm:block">{{ Str::limit($accesorio->descripcion, 40) }}</p>
                      @endif
                      <p class="text-xs sm:text-sm font-semibold text-blue-600">Bs. {{ number_format($accesorio->precio, 2) }}</p>
                      @if($accesorio->stock !== null)
                        <p class="text-xs {{ $accesorio->stock > 0 ? 'text-green-600' : 'text-red-600' }} font-semibold">
                          Stock: {{ $accesorio->stock }}
                        </p>
                      @endif
                    </div>
                  </div>
                  <div class="flex items-center space-x-2 w-full sm:w-auto justify-end sm:justify-start">
                    @if($hayStock)
                      <button type="button" onclick="decrementarAccesorio({{ $accesorio->id }}, {{ $accesorio->precio }}, {{ $accesorio->stock ?? 'null' }})" 
                              class="w-8 h-8 sm:w-8 sm:h-8 flex items-center justify-center bg-gray-200 rounded hover:bg-gray-300 text-sm">-</button>
                      <input type="number" name="accesorios[{{ $accesorio->id }}]" 
                             id="cantidad_accesorio_{{ $accesorio->id }}" 
                             value="0" min="0" 
                             max="{{ $accesorio->stock ?? '' }}"
                             onchange="actualizarTotalAccesorios()"
                             class="w-12 sm:w-14 text-center border border-gray-300 rounded text-xs sm:text-sm">
                      <button type="button" onclick="incrementarAccesorio({{ $accesorio->id }}, {{ $accesorio->precio }}, {{ $accesorio->stock ?? 'null' }})" 
                              class="w-8 h-8 sm:w-8 sm:h-8 flex items-center justify-center bg-gray-200 rounded hover:bg-gray-300 text-sm">+</button>
                    @else
                      <span class="text-xs text-red-600 font-semibold">Agotado</span>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>
            <div class="mt-3 pt-3 border-t">
              <p class="text-sm text-gray-600">Total accesorios: <span id="total_accesorios" class="font-semibold">Bs. 0.00</span></p>
            </div>
          @else
            <p class="text-sm text-gray-500 text-center py-4">No hay accesorios disponibles</p>
          @endif
        </div>
      </div>

      {{-- Monto Total --}}
      <div class="bg-blue-50 rounded-lg p-3 sm:p-4 mb-4 sm:mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
          <span class="text-base sm:text-lg font-semibold text-gray-900">Monto Total a Pagar:</span>
          <span class="text-xl sm:text-2xl font-bold text-blue-600" id="monto_total_display">Bs. 0.00</span>
        </div>
        <div id="desglose_monto" class="text-xs sm:text-sm text-gray-600 mt-2"></div>
      </div>

      {{-- Código QR para Pago (Estático del Admin) --}}
      <div class="mb-4 sm:mb-6 text-center">
        <h4 class="font-semibold text-gray-900 mb-2 sm:mb-3 text-sm sm:text-base">Escanea el código QR para realizar el pago</h4>
        <div class="bg-white p-3 sm:p-4 rounded-lg border-2 border-gray-200 inline-block">
          <div id="qr_code_container" class="w-40 h-40 sm:w-48 sm:h-48 mx-auto bg-white flex items-center justify-center">
            @php
              $configPago = \App\Models\ConfiguracionPago::activa();
            @endphp
            @if($configPago && $configPago->qr_imagen_vehiculos)
              <img src="{{ asset('storage/' . $configPago->qr_imagen_vehiculos) }}" alt="QR de Pago Vehículos" class="w-full h-full object-contain">
            @else
              <p class="text-gray-400 text-sm">QR no configurado. Contacta al administrador.</p>
            @endif
          </div>
        </div>
        @if($configPago && $configPago->numero_cuenta)
          <p class="text-xs sm:text-sm text-gray-600 mt-2">Número de cuenta: <span class="font-semibold">{{ $configPago->numero_cuenta }}</span></p>
        @endif
        @if($configPago && $configPago->instrucciones_pago)
          <p class="text-xs text-gray-500 mt-2 px-2">{{ $configPago->instrucciones_pago }}</p>
        @endif
        <p class="text-xs sm:text-sm text-gray-600 mt-2">Realiza el pago y sube el comprobante antes de confirmar</p>
        <p class="text-xs text-gray-500 mt-1">Monto a pagar: <span id="monto_qr" class="font-semibold">Bs. 0.00</span></p>
      </div>

      {{-- Verificar si el usuario ya tiene documentos verificados --}}
      @php
        $usuario = Auth::user();
        $necesitaDocumentos = $usuario->necesitaSubirDocumentos();
      @endphp

      @if(!$necesitaDocumentos)
        {{-- Usuario ya tiene documentos verificados y vigentes --}}
        <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-green-700">
                <strong>Documentos verificados:</strong> Tus documentos ya están verificados y vigentes. No necesitas subirlos nuevamente.
                @if($usuario->licencia_fecha_vencimiento)
                  <br>Tu licencia vence el: <strong>{{ \Carbon\Carbon::parse($usuario->licencia_fecha_vencimiento)->format('d/m/Y') }}</strong>
                @endif
              </p>
            </div>
          </div>
        </div>
      @endif

      {{-- Documentos a Subir (solo si necesita) --}}
      <div id="documentos-section" class="space-y-3 sm:space-y-4 mb-4 sm:mb-6" style="{{ $necesitaDocumentos ? '' : 'display: none;' }}">
        <h4 class="font-semibold text-gray-900 text-sm sm:text-base">Documentos Requeridos</h4>
        
        {{-- Carnet de Identidad --}}
        <div class="border border-gray-200 rounded-lg p-3 sm:p-4">
          <h5 class="font-medium text-gray-900 mb-2 sm:mb-3 text-sm sm:text-base">📄 Carnet de Identidad <span class="text-red-500">*</span></h5>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Anverso <span class="text-red-500">*</span>
              </label>
              <input type="file" name="carnet_anverso" accept=".pdf,.jpg,.jpeg,.png" {{ $necesitaDocumentos ? 'required' : '' }}
                     class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Reverso <span class="text-red-500">*</span>
              </label>
              <input type="file" name="carnet_reverso" accept=".pdf,.jpg,.jpeg,.png" {{ $necesitaDocumentos ? 'required' : '' }}
                     class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
          </div>
          <p class="text-xs text-gray-500 mt-2">Formatos: PDF, JPG, PNG (máx. 5MB cada uno)</p>
        </div>

        {{-- Licencia de Conducir --}}
        <div class="border border-gray-200 rounded-lg p-4">
          <h5 class="font-medium text-gray-900 mb-3">🪪 Licencia de Conducir <span class="text-red-500">*</span></h5>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Anverso <span class="text-red-500">*</span>
              </label>
              <input type="file" name="licencia_anverso" accept=".pdf,.jpg,.jpeg,.png" {{ $necesitaDocumentos ? 'required' : '' }}
                     class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Reverso <span class="text-red-500">*</span>
              </label>
              <input type="file" name="licencia_reverso" accept=".pdf,.jpg,.jpeg,.png" {{ $necesitaDocumentos ? 'required' : '' }}
                     class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Fecha de Vencimiento de la Licencia <span class="text-red-500">*</span>
            </label>
            <input type="date" name="licencia_fecha_vencimiento" {{ $necesitaDocumentos ? 'required' : '' }}
                   value="{{ $usuario && $usuario->licencia_fecha_vencimiento ? \Carbon\Carbon::parse($usuario->licencia_fecha_vencimiento)->format('Y-m-d') : '' }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <p class="text-xs text-gray-500 mt-1">Ingresa la fecha de vencimiento de tu licencia de conducir</p>
          </div>
          <p class="text-xs text-gray-500 mt-2">Formatos: PDF, JPG, PNG (máx. 5MB cada uno)</p>
        </div>
      </div>

      {{-- Comprobante de Pago (SIEMPRE requerido, fuera de la sección de documentos) --}}
      <div class="mb-6">
        <h4 class="font-semibold text-gray-900 mb-3">💰 Comprobante de Pago <span class="text-red-500">*</span></h4>
        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Sube tu comprobante de pago <span class="text-red-500">*</span>
          </label>
          <input type="file" name="comprobante_pago" accept=".pdf,.jpg,.jpeg,.png" required
                 class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <p class="text-xs text-gray-500 mt-2">Formatos: PDF, JPG, PNG (máx. 5MB)</p>
          <p class="text-xs text-yellow-600 mt-1 font-medium">⚠️ El comprobante de pago es obligatorio para todas las reservas</p>
        </div>
      </div>

      {{-- Mensaje Informativo --}}
      <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm text-yellow-700">
              <strong>Importante:</strong> Tu solicitud de reserva será enviada para revisión. Si tu documentación y comprobante de pago son correctos, la reserva se confirmará automáticamente en 10-15 minutos. Podrás ver el estado en "Mis Reservas".
            </p>
          </div>
        </div>
      </div>

      @php
        $configContrato = \App\Models\ConfiguracionContrato::activa();
      @endphp

      <div class="bg-white border border-gray-200 rounded-lg p-4 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
          <div>
            <p class="text-sm font-semibold text-gray-900">Contrato de servicio</p>
            <p class="text-xs text-gray-500">Debes aceptar los términos del contrato antes de enviar la solicitud.</p>
          </div>
          @if($configContrato)
            <a href="{{ asset('storage/' . $configContrato->archivo) }}" target="_blank"
               class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800 font-semibold gap-1">
              📄 Descargar contrato
            </a>
          @else
            <span class="text-xs text-red-500">Contrato no configurado por el administrador.</span>
          @endif
        </div>
        <label class="flex items-start gap-2 text-sm text-gray-700">
          <input type="checkbox" name="acepta_contrato" value="1" required class="mt-1 h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
          <span>
            He leído y acepto los términos del contrato de alquiler de vehículos.
            @if($configContrato)
              <br><span class="text-xs text-gray-500">Puedes descargarlo para revisarlo cuando quieras.</span>
            @endif
          </span>
        </label>
        @error('acepta_contrato')
          <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
      </div>

      {{-- Botones --}}
      <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-4 pt-4 border-t">
        <button type="button" onclick="cerrarModalPago()" 
                class="w-full sm:w-auto px-4 sm:px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-sm sm:text-base">
          Cancelar
        </button>
        <button type="submit" 
                class="w-full sm:w-auto px-4 sm:px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition shadow-sm hover:shadow-md text-sm sm:text-base">
          Enviar Solicitud de Reserva
        </button>
      </div>
    </form>
  </div>
</div>

<script>
    window.necesitaDocumentos = @json($necesitaDocumentos ?? false);
</script>
<script src="{{ asset('js/reservas/modal-pago.js') }}"></script>

