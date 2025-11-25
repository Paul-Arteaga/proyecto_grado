// ====== datos globales para edición ======
let EDIT_RESERVA_ID = null;
let EDIT_VEHICULO_ID = null;
let editSelectedDates = new Set();

// fecha actual del navegador
const now = new Date();
const TODAY_Y = now.getFullYear();
const TODAY_M = now.getMonth();     // 0-11
const TODAY_D = now.getDate();
const CURRENT_HOUR = now.getHours();// 0-23

let editCurrentMonth = TODAY_M;
let editCurrentYear  = TODAY_Y;

// 👉 nuevo helper para evitar el desfase de 1 día
function parseLocalDate(yyyy_mm_dd) {
    const [y, m, d] = yyyy_mm_dd.split('-').map(Number);
    return new Date(y, m - 1, d); // mes 0-based
}

function openEditReservaCalendar(reserva) {
    EDIT_RESERVA_ID  = reserva.id;
    EDIT_VEHICULO_ID = reserva.vehiculo_id;

    // preseleccionar el rango actual de la reserva (sin que se corra 1 día)
    editSelectedDates = new Set();
    const inicio = parseLocalDate(reserva.fecha_inicio);
    const fin    = parseLocalDate(reserva.fecha_fin);
    let d = new Date(inicio);
    while (d <= fin) {
        editSelectedDates.add(formatDate(d.getFullYear(), d.getMonth() + 1, d.getDate()));
        d.setDate(d.getDate() + 1);
    }

    // mostrar el mes del inicio, pero no permitir ir al pasado
    editCurrentMonth = Math.max(inicio.getMonth(), TODAY_M);
    editCurrentYear  = inicio.getFullYear();
    if (editCurrentYear < TODAY_Y) {
        editCurrentYear = TODAY_Y;
        editCurrentMonth = TODAY_M;
    }

    renderEditCalendar();

    const modal = document.getElementById('editReservaModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeEditReservaCalendar() {
    const modal = document.getElementById('editReservaModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function editChangeMonth(delta) {
    // calculamos el nuevo mes/año primero
    let newMonth = editCurrentMonth + delta;
    let newYear  = editCurrentYear;

    if (newMonth < 0) {
        newMonth = 11;
        newYear--;
    } else if (newMonth > 11) {
        newMonth = 0;
        newYear++;
    }

    // NO permitir ir a meses anteriores al actual
    if (newYear < TODAY_Y) return;
    if (newYear === TODAY_Y && newMonth < TODAY_M) return;

    editCurrentMonth = newMonth;
    editCurrentYear  = newYear;
    renderEditCalendar();
}

function renderEditCalendar() {
    const monthLabel = document.getElementById('editMonthLabel');
    const calendarGrid = document.getElementById('editCalendarGrid');
    calendarGrid.innerHTML = '';

    const monthNames = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    monthLabel.textContent = monthNames[editCurrentMonth] + ' ' + editCurrentYear;

    // encabezado días
    const daysShort = ['D','L','M','M','J','V','S'];
    daysShort.forEach(d => {
        const el = document.createElement('div');
        el.textContent = d;
        el.className = 'font-semibold';
        calendarGrid.appendChild(el);
    });

    const firstDay = new Date(editCurrentYear, editCurrentMonth, 1).getDay();
    const daysInMonth = new Date(editCurrentYear, editCurrentMonth + 1, 0).getDate();

    // huecos
    for (let i = 0; i < firstDay; i++) {
        calendarGrid.appendChild(document.createElement('div'));
    }

    // fechas ocupadas de ese vehículo
    const ocupados = new Set((window.RESERVAS[EDIT_VEHICULO_ID] || []));

    for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = formatDate(editCurrentYear, editCurrentMonth + 1, day);
        const btn = document.createElement('button');
        btn.textContent = day;
        btn.className = 'w-full aspect-square rounded text-sm flex items-center justify-center';

        const esDiaSeleccionado = editSelectedDates.has(dateStr);

        // bloqueamos días pasados
        let esPasado = false;
        if (editCurrentYear < TODAY_Y) {
            esPasado = true;
        } else if (editCurrentYear === TODAY_Y) {
            if (editCurrentMonth < TODAY_M) {
                esPasado = true;
            } else if (editCurrentMonth === TODAY_M) {
                if (day < TODAY_D) {
                    esPasado = true;
                } else if (day === TODAY_D && CURRENT_HOUR >= 12) {
                    // si hoy ya pasó el mediodía, hoy también bloqueado
                    esPasado = true;
                }
            }
        }

        // si el día está ocupado por otra reserva, pero NO es de esta reserva, o es pasado -> bloquear
        if ((ocupados.has(dateStr) && !esDiaSeleccionado) || esPasado) {
            btn.classList.add('bg-gray-200','text-gray-400','line-through','cursor-not-allowed');
            btn.disabled = true;
        } else {
            btn.classList.add('bg-white','hover:bg-emerald-100','border');

            if (esDiaSeleccionado) {
                btn.classList.add('bg-emerald-500','text-white','border-emerald-500');
            }

            btn.onclick = () => {
                if (editSelectedDates.has(dateStr)) {
                    editSelectedDates.delete(dateStr);
                } else {
                    editSelectedDates.add(dateStr);
                }
                renderEditCalendar();
            };
        }

        calendarGrid.appendChild(btn);
    }
}

function formatDate(y, m, d) {
    const mm = m < 10 ? '0'+m : m;
    const dd = d < 10 ? '0'+d : d;
    return `${y}-${mm}-${dd}`;
}

function guardarEdicionReserva() {
    const arr = Array.from(editSelectedDates).sort();
    if (!arr.length) {
        alert('Seleccioná al menos un día');
        return;
    }

    const fecha_inicio = arr[0];
    const fecha_fin    = arr[arr.length - 1];

    fetch(window.reservasUrl + "/" + EDIT_RESERVA_ID, {
        method: "POST", // spoof PUT
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": window.csrfToken
        },
        body: JSON.stringify({
            _method: "PUT",
            fecha_inicio: fecha_inicio,
            fecha_fin: fecha_fin
        })
    })
    .then(r => {
        if (!r.ok) throw r;
        return r.text();
    })
    .then(() => {
        window.location.reload();
    })
    .catch(async (err) => {
        let msg = 'Error al guardar';
        try {
            const data = await err.json();
            msg = data.message || data.msg || msg;
        } catch(e){}
        alert(msg);
    });
}

function mostrarModalRechazar(reservaId) {
    const form = document.getElementById('formRechazar');
    form.action = window.reservasUrl + "/" + reservaId + "/rechazar";
    document.getElementById('modalRechazar').classList.remove('hidden');
    document.getElementById('modalRechazar').classList.add('flex');
}

function cerrarModalRechazar() {
    document.getElementById('modalRechazar').classList.add('hidden');
    document.getElementById('modalRechazar').classList.remove('flex');
}

// Modal para ver documentos
function mostrarModalDocumentos(reservaId, carnetAnversoUrl, carnetReversoUrl, licenciaAnversoUrl, licenciaReversoUrl, comprobanteUrl) {
    const modal = document.getElementById('modalDocumentos');
    const tabs = document.getElementById('documentosTabs');
    const content = document.getElementById('documentosContent');
    
    // Limpiar contenido anterior
    tabs.innerHTML = '';
    content.innerHTML = '';
    
    // Crear array de documentos disponibles
    const documentos = [];
    if (carnetAnversoUrl && carnetAnversoUrl !== '') {
        documentos.push({ tipo: 'Carnet Anverso', icono: '📄', url: carnetAnversoUrl });
    }
    if (carnetReversoUrl && carnetReversoUrl !== '') {
        documentos.push({ tipo: 'Carnet Reverso', icono: '📄', url: carnetReversoUrl });
    }
    if (licenciaAnversoUrl && licenciaAnversoUrl !== '') {
        documentos.push({ tipo: 'Licencia Anverso', icono: '🪪', url: licenciaAnversoUrl });
    }
    if (licenciaReversoUrl && licenciaReversoUrl !== '') {
        documentos.push({ tipo: 'Licencia Reverso', icono: '🪪', url: licenciaReversoUrl });
    }
    if (comprobanteUrl && comprobanteUrl !== '') {
        documentos.push({ tipo: 'Comprobante', icono: '💰', url: comprobanteUrl });
    }
    
    if (documentos.length === 0) {
        alert('No hay documentos disponibles');
        return;
    }
    
    // Crear tabs y contenido
    documentos.forEach((doc, index) => {
        // Crear tab
        const tab = document.createElement('button');
        tab.className = 'px-4 py-2 text-sm font-medium rounded-t-lg transition-colors ' + 
                       (index === 0 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300');
        tab.textContent = doc.icono + ' ' + doc.tipo;
        tab.onclick = () => cambiarTabDocumento(index);
        tabs.appendChild(tab);
        
        // Crear panel de contenido
        const panel = document.createElement('div');
        panel.id = 'doc-panel-' + index;
        panel.className = index === 0 ? 'block' : 'hidden';
        
        // Detectar si es imagen o PDF
        const esImagen = doc.url.toLowerCase().match(/\.(jpg|jpeg|png|gif|webp)$/);
        if (esImagen) {
            panel.innerHTML = `
                <div class="w-full bg-gray-50 rounded-lg p-4 flex items-center justify-center" style="min-height: 300px;">
                    <img src="${doc.url}" alt="${doc.tipo}" 
                         onload="this.style.display='block';"
                         onerror="this.style.display='none'; this.parentElement.innerHTML='<p class=\'text-red-500 text-center py-8\'>Error al cargar la imagen</p>'"
                         class="border rounded-lg shadow-lg"
                         style="max-width: 80%; max-height: 70vh; width: auto; height: auto; display: block; object-fit: contain;">
                </div>`;
        } else {
            panel.innerHTML = `<div class="w-full rounded-lg overflow-hidden" style="height: calc(90vh - 250px); min-height: 400px;">
                <iframe src="${doc.url}" class="w-full h-full border-0" frameborder="0"></iframe>
            </div>`;
        }
        content.appendChild(panel);
    });
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function cambiarTabDocumento(index) {
    // Ocultar todos los paneles
    document.querySelectorAll('[id^="doc-panel-"]').forEach(panel => {
        panel.classList.add('hidden');
        panel.classList.remove('block');
    });
    
    // Mostrar el panel seleccionado
    const panel = document.getElementById('doc-panel-' + index);
    if (panel) {
        panel.classList.remove('hidden');
        panel.classList.add('block');
    }
    
    // Actualizar estilos de tabs
    const tabs = document.getElementById('documentosTabs').querySelectorAll('button');
    tabs.forEach((tab, i) => {
        if (i === index) {
            tab.classList.remove('bg-gray-200', 'text-gray-700', 'hover:bg-gray-300');
            tab.classList.add('bg-blue-600', 'text-white');
        } else {
            tab.classList.remove('bg-blue-600', 'text-white');
            tab.classList.add('bg-gray-200', 'text-gray-700', 'hover:bg-gray-300');
        }
    });
}

function cerrarModalDocumentos() {
    document.getElementById('modalDocumentos').classList.add('hidden');
    document.getElementById('modalDocumentos').classList.remove('flex');
}

// Modal para ver solo el comprobante de pago
function mostrarModalComprobante(comprobanteUrl) {
    const modal = document.getElementById('modalComprobante');
    const content = document.getElementById('comprobanteContent');
    
    if (!comprobanteUrl || comprobanteUrl === '') {
        alert('No hay comprobante disponible');
        return;
    }
    
    // Detectar si es imagen o PDF
    const esImagen = comprobanteUrl.toLowerCase().match(/\.(jpg|jpeg|png|gif|webp)$/);
    
    if (esImagen) {
        content.innerHTML = `
            <div class="w-full bg-gray-50 rounded-lg p-4 flex items-center justify-center" style="min-height: 300px;">
                <img src="${comprobanteUrl}" alt="Comprobante de Pago" 
                     onload="this.style.display='block';"
                     onerror="this.style.display='none'; this.parentElement.innerHTML='<p class=\'text-red-500 text-center py-8\'>Error al cargar la imagen</p>'"
                     class="border rounded-lg shadow-lg"
                     style="max-width: 80%; max-height: 70vh; width: auto; height: auto; display: block; object-fit: contain;">
            </div>`;
    } else {
        content.innerHTML = `<div class="w-full rounded-lg overflow-hidden" style="height: calc(90vh - 250px); min-height: 400px;">
            <iframe src="${comprobanteUrl}" class="w-full h-full border-0" frameborder="0"></iframe>
        </div>`;
    }
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function cerrarModalComprobante() {
    document.getElementById('modalComprobante').classList.add('hidden');
    document.getElementById('modalComprobante').classList.remove('flex');
}


