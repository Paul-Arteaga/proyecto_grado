let montoBaseReserva = 0;

function abrirModalPago(datos) {
  // Guardar bloqueo_key y vehiculo_id para liberar después
  if (datos && datos.bloqueo_key) {
    window.bloqueoKeyActual = datos.bloqueo_key;
  } else {
    window.bloqueoKeyActual = null;
  }
  
  if (datos && datos.vehiculo && datos.vehiculo.id) {
    window.vehiculoActualModal = datos.vehiculo.id;
  } else {
    window.vehiculoActualModal = null;
  }
  
  // Llenar datos del formulario
  if (datos && datos.vehiculo) {
    document.getElementById('vehiculo_id_pago').value = datos.vehiculo.id;
  }
  if (datos && datos.fecha_inicio) {
    document.getElementById('fecha_inicio_pago').value = datos.fecha_inicio;
    document.getElementById('fecha_inicio_display').value = datos.fecha_inicio;
  }
  if (datos && datos.fecha_fin) {
    document.getElementById('fecha_fin_pago').value = datos.fecha_fin;
    document.getElementById('fecha_fin_display').value = datos.fecha_fin;
  }
  
  // Agregar bloqueo_key al formulario si existe
  let bloqueoInput = document.getElementById('bloqueo_key_pago');
  if (!bloqueoInput) {
    bloqueoInput = document.createElement('input');
    bloqueoInput.type = 'hidden';
    bloqueoInput.id = 'bloqueo_key_pago';
    bloqueoInput.name = 'bloqueo_key';
    document.getElementById('formPago').appendChild(bloqueoInput);
  }
  bloqueoInput.value = (datos && datos.bloqueo_key) ? datos.bloqueo_key : '';
  
  if (datos && datos.vehiculo) {
    document.getElementById('vehiculo_info').textContent = datos.vehiculo.marca + ' ' + datos.vehiculo.modelo;
    document.getElementById('vehiculo_placa').textContent = datos.vehiculo.placa || '';
    document.getElementById('precio_diario').textContent = 'Bs. ' + parseFloat(datos.vehiculo.precio_diario || 0).toFixed(2);
  }
  if (datos && datos.dias) {
    document.getElementById('dias_reserva').textContent = datos.dias + ' día(s)';
  }
  
  // Guardar monto base de la reserva
  montoBaseReserva = parseFloat(datos.monto_total || 0);
  
  // Actualizar total inicial
  actualizarTotalConAccesorios();

  // Mostrar/ocultar sección de documentos según si el usuario necesita subirlos
  const documentosSection = document.getElementById('documentos-section');
  if (window.necesitaDocumentos) {
    if (documentosSection) documentosSection.style.display = 'block';
  } else {
    if (documentosSection) documentosSection.style.display = 'none';
  }
  
  // Resetear accesorios
  document.querySelectorAll('[id^="cantidad_accesorio_"]').forEach(input => {
    input.value = 0;
  });
  document.getElementById('accesoriosSection').classList.add('hidden');
  document.getElementById('toggleAccesoriosText').textContent = 'Agregar accesorios';

  // Mostrar modal
  document.getElementById('modalPago').classList.remove('hidden');
  document.getElementById('modalPago').classList.add('flex');
}

function toggleAccesorios() {
  const section = document.getElementById('accesoriosSection');
  const toggleText = document.getElementById('toggleAccesoriosText');
  
  if (section.classList.contains('hidden')) {
    section.classList.remove('hidden');
    toggleText.textContent = 'Ocultar accesorios';
  } else {
    section.classList.add('hidden');
    toggleText.textContent = 'Agregar accesorios';
  }
}

function incrementarAccesorio(id, precio, stock) {
  const input = document.getElementById('cantidad_accesorio_' + id);
  const nuevaCantidad = parseInt(input.value) + 1;
  
  // Verificar stock si está configurado
  if (stock !== null && stock !== 'null' && nuevaCantidad > parseInt(stock)) {
    alert(`No hay suficiente stock disponible. Stock disponible: ${stock}`);
    return;
  }
  
  input.value = nuevaCantidad;
  actualizarTotalAccesorios();
}

function decrementarAccesorio(id, precio, stock) {
  const input = document.getElementById('cantidad_accesorio_' + id);
  if (parseInt(input.value) > 0) {
    input.value = parseInt(input.value) - 1;
    actualizarTotalAccesorios();
  }
}

function actualizarTotalAccesorios() {
  let totalAccesorios = 0;
  
  document.querySelectorAll('[id^="cantidad_accesorio_"]').forEach(input => {
    const cantidad = parseInt(input.value) || 0;
    if (cantidad > 0) {
      const accesorioId = input.id.replace('cantidad_accesorio_', '');
      // Buscar el precio del accesorio en el DOM
      const accesorioDiv = input.closest('.flex.items-center.justify-between');
      const precioText = accesorioDiv.querySelector('.text-blue-600');
      if (precioText) {
        const precio = parseFloat(precioText.textContent.replace('Bs. ', '').replace(',', ''));
        totalAccesorios += precio * cantidad;
      }
    }
  });
  
  document.getElementById('total_accesorios').textContent = 'Bs. ' + totalAccesorios.toFixed(2);
  actualizarTotalConAccesorios();
}

function actualizarTotalConAccesorios() {
  const totalAccesorios = parseFloat(document.getElementById('total_accesorios').textContent.replace('Bs. ', '').replace(',', '')) || 0;
  const totalFinal = montoBaseReserva + totalAccesorios;
  
  document.getElementById('monto_total_display').textContent = 'Bs. ' + totalFinal.toFixed(2);
  document.getElementById('monto_qr').textContent = 'Bs. ' + totalFinal.toFixed(2);
  
  // Actualizar desglose
  const desglose = document.getElementById('desglose_monto');
  if (totalAccesorios > 0) {
    desglose.innerHTML = `
      <div class="flex justify-between">
        <span>Vehículo:</span>
        <span>Bs. ${montoBaseReserva.toFixed(2)}</span>
      </div>
      <div class="flex justify-between">
        <span>Accesorios:</span>
        <span>Bs. ${totalAccesorios.toFixed(2)}</span>
      </div>
    `;
  } else {
    desglose.innerHTML = '';
  }
}

function cerrarModalPago() {
  // Liberar bloqueo temporal si existe
  if (window.bloqueoKeyActual && window.vehiculoActualModal) {
    fetch(window.liberarBloqueoUrl, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json",
        "X-CSRF-TOKEN": window.csrfToken
      },
      body: JSON.stringify({
        bloqueo_key: window.bloqueoKeyActual,
        vehiculo_id: window.vehiculoActualModal
      })
    }).catch(err => console.error('Error al liberar bloqueo:', err));
    
    // Limpiar variables
    window.bloqueoKeyActual = null;
    window.vehiculoActualModal = null;
  }
  
  document.getElementById('modalPago').classList.add('hidden');
  document.getElementById('modalPago').classList.remove('flex');
}




