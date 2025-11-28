let precioUnitario = 0;
let stockDisponible = null;

function abrirModalAccesorio(id, nombre, precio, descripcion, imagen, stock) {
    console.log('Abriendo modal para accesorio:', {id, nombre, precio, stock});
    
    try {
        precioUnitario = parseFloat(precio);
        stockDisponible = stock !== null && stock !== 'null' ? parseInt(stock) : null;
        
        const accesorioIdInput = document.getElementById('accesorio_id_modal');
        const accesorioNombre = document.getElementById('accesorioNombre');
        const accesorioDescripcion = document.getElementById('accesorioDescripcion');
        const accesorioPrecio = document.getElementById('accesorioPrecio');
        
        if (!accesorioIdInput || !accesorioNombre || !accesorioDescripcion || !accesorioPrecio) {
            console.error('Elementos del modal no encontrados:', {
                accesorioIdInput: !!accesorioIdInput,
                accesorioNombre: !!accesorioNombre,
                accesorioDescripcion: !!accesorioDescripcion,
                accesorioPrecio: !!accesorioPrecio
            });
            alert('Error: No se pudo abrir el modal. Por favor, recarga la página.');
            return;
        }
        
        accesorioIdInput.value = id;
        accesorioNombre.textContent = nombre;
        accesorioDescripcion.textContent = descripcion || 'Sin descripción';
        accesorioPrecio.textContent = 'Bs. ' + precioUnitario.toFixed(2);
    
        // Mostrar información de stock
        const stockInfo = document.getElementById('stockInfo');
        const cantidadInput = document.getElementById('cantidad');
        
        if (stockInfo && cantidadInput) {
            if (stockDisponible !== null) {
                stockInfo.textContent = `(Stock disponible: ${stockDisponible})`;
                stockInfo.className = stockDisponible > 0 ? 'text-xs text-green-600 ml-2 font-semibold' : 'text-xs text-red-600 ml-2 font-semibold';
                
                // Limitar cantidad máxima al stock disponible
                cantidadInput.max = stockDisponible;
                if (parseInt(cantidadInput.value) > stockDisponible) {
                    cantidadInput.value = stockDisponible > 0 ? stockDisponible : 0;
                }
            } else {
                stockInfo.textContent = '';
            }
        }
        
        const imagenDiv = document.getElementById('accesorioImagen');
        if (imagenDiv) {
            if (imagen && imagen !== '') {
                imagenDiv.innerHTML = `<img src="${imagen}" alt="${nombre}" class="w-full h-full object-cover rounded">`;
            } else {
                imagenDiv.innerHTML = '<span class="text-gray-400 text-xs">Sin imagen</span>';
            }
        }
    
        // Actualizar formulario
        const reservaId = new URLSearchParams(window.location.search).get('reserva_id');
        const reservaSelect = document.getElementById('reserva_id');
        if (reservaId && reservaSelect) {
            reservaSelect.value = reservaId;
        }
        
        // Configurar acción del formulario cuando se seleccione una reserva
        if (reservaSelect) {
            const selectedReservaId = reservaSelect.value;
            if (selectedReservaId) {
                const formAgregarAccesorio = document.getElementById('formAgregarAccesorio');
                if (formAgregarAccesorio) {
                    const actionUrl = '/reservas/' + selectedReservaId + '/accesorios';
                    formAgregarAccesorio.action = actionUrl;
                    const hiddenField = document.getElementById('reserva_id_hidden');
                    if (hiddenField) {
                        hiddenField.value = selectedReservaId;
                    }
                }
            }
        }
        
        // Actualizar precio total y monto en QR (después de configurar el formulario)
        actualizarPrecioTotal();
        
        // Mostrar modal
        const modal = document.getElementById('modalAgregarAccesorio');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            console.log('Modal mostrado correctamente');
        } else {
            console.error('No se encontró el modal con id modalAgregarAccesorio');
            alert('Error: No se encontró el modal. Por favor, recarga la página.');
        }
    } catch (error) {
        console.error('Error al abrir modal:', error);
        alert('Error al abrir el modal: ' + error.message);
    }
}

function cerrarModalAccesorio() {
    document.getElementById('modalAgregarAccesorio').classList.add('hidden');
    document.getElementById('modalAgregarAccesorio').classList.remove('flex');
}

function incrementarCantidad() {
    const input = document.getElementById('cantidad');
    const nuevaCantidad = parseInt(input.value) + 1;
    
    // Verificar stock si está configurado
    if (stockDisponible !== null && nuevaCantidad > stockDisponible) {
        alert(`No hay suficiente stock disponible. Stock disponible: ${stockDisponible}`);
        return;
    }
    
    input.value = nuevaCantidad;
    actualizarPrecioTotal();
}

function decrementarCantidad() {
    const input = document.getElementById('cantidad');
    if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
        actualizarPrecioTotal();
    }
}

function actualizarPrecioTotal() {
    const cantidad = parseInt(document.getElementById('cantidad').value) || 1;
    const total = precioUnitario * cantidad;
    document.getElementById('precioTotal').innerHTML = 'Total: <span class="font-semibold text-blue-600">Bs. ' + total.toFixed(2) + '</span>';
    
    // Actualizar monto en el QR
    const montoQr = document.getElementById('monto_accesorio_qr');
    if (montoQr) {
        montoQr.textContent = 'Bs. ' + total.toFixed(2);
    }
    
    // Actualizar acción del formulario cuando cambia la reserva
    const reservaSelect = document.getElementById('reserva_id');
    if (reservaSelect && reservaSelect.value) {
        const actionUrl = '/reservas/' + reservaSelect.value + '/accesorios';
        document.getElementById('formAgregarAccesorio').action = actionUrl;
        const hiddenField = document.getElementById('reserva_id_hidden');
        if (hiddenField) {
            hiddenField.value = reservaSelect.value;
        }
    }
}

// Actualizar acción del formulario cuando cambia la reserva
document.addEventListener('DOMContentLoaded', function() {
    const reservaSelect = document.getElementById('reserva_id');
    if (reservaSelect) {
        reservaSelect.addEventListener('change', function() {
            const reservaId = this.value;
            if (reservaId) {
                const actionUrl = '/reservas/' + reservaId + '/accesorios';
                document.getElementById('formAgregarAccesorio').action = actionUrl;
                const hiddenField = document.getElementById('reserva_id_hidden');
                if (hiddenField) {
                    hiddenField.value = reservaId;
                }
            }
        });
    }
    
    // Actualizar precio cuando cambia la cantidad
    const cantidadInput = document.getElementById('cantidad');
    if (cantidadInput) {
        cantidadInput.addEventListener('change', actualizarPrecioTotal);
    }
});






