// si por algún motivo arriba NO llegó, lo dejamos como objeto
window.RESERVAS = window.RESERVAS || {};
window.RESERVAS_PENDIENTES = window.RESERVAS_PENDIENTES || {};
const PENDIENTE_MSG = window.reservasPendientesMsg || 'Estas fechas están en proceso de confirmación por otro cliente. Podrás ver el estado actualizado en unos minutos.';

// fecha/hora actual del navegador
const now = new Date();
const TODAY_Y = now.getFullYear();
const TODAY_M = now.getMonth();      // 0-11
const TODAY_D = now.getDate();
const CURRENT_HOUR = now.getHours(); // 0-23

let currentMonth = TODAY_M;
let currentYear  = TODAY_Y;
let vehiculoActual = null;
let selectedDates = new Set();

function openReservaModal(vehiculoId, nombreVehiculo) {
  vehiculoActual = vehiculoId;
  selectedDates = new Set();

  document.getElementById('vehiculoSeleccionado').value = vehiculoId;
  document.getElementById('reservaTitulo').textContent = 'Reservar: ' + nombreVehiculo;

  currentMonth = TODAY_M;
  currentYear  = TODAY_Y;

  renderCalendar();
  const modal = document.getElementById('reservaModal');
  modal.classList.remove('hidden');
  modal.classList.add('flex');
}

function closeReservaModal() {
  const modal = document.getElementById('reservaModal');
  modal.classList.add('hidden');
  modal.classList.remove('flex');
}

function changeMonth(delta) {
  let newMonth = currentMonth + delta;
  let newYear  = currentYear;

  if (newMonth < 0) {
    newMonth = 11;
    newYear--;
  } else if (newMonth > 11) {
    newMonth = 0;
    newYear++;
  }

  // no ir al pasado
  if (newYear < TODAY_Y) return;
  if (newYear === TODAY_Y && newMonth < TODAY_M) return;

  currentMonth = newMonth;
  currentYear  = newYear;
  renderCalendar();
}

function renderCalendar() {
  const monthLabel = document.getElementById('monthLabel');
  const calendarGrid = document.getElementById('calendarGrid');
  calendarGrid.innerHTML = '';

  const monthNames = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
  monthLabel.textContent = monthNames[currentMonth] + ' ' + currentYear;

  const daysShort = ['D','L','M','M','J','V','S'];
  daysShort.forEach(d => {
    const el = document.createElement('div');
    el.textContent = d;
    el.className = 'font-semibold';
    calendarGrid.appendChild(el);
  });

  const firstDay = new Date(currentYear, currentMonth, 1).getDay();
  const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

  for (let i = 0; i < firstDay; i++) {
    calendarGrid.appendChild(document.createElement('div'));
  }

  const ocupados = new Set((window.RESERVAS[vehiculoActual] || []));
  const pendientes = new Set((window.RESERVAS_PENDIENTES[vehiculoActual] || []));
  const bloqueosTemporales = new Set((window.BLOQUEOS_TEMPORALES && window.BLOQUEOS_TEMPORALES[vehiculoActual]) || []);

  for (let day = 1; day <= daysInMonth; day++) {
    const dateStr = formatDate(currentYear, currentMonth + 1, day);
    const btn = document.createElement('button');
    btn.textContent = day;
    btn.className = 'w-full aspect-square rounded text-sm flex items-center justify-center';

    // bloquear pasado
    let esPasado = false;
    if (currentYear < TODAY_Y) {
      esPasado = true;
    } else if (currentYear === TODAY_Y) {
      if (currentMonth < TODAY_M) {
        esPasado = true;
      } else if (currentMonth === TODAY_M) {
        if (day < TODAY_D) {
          esPasado = true;
        } else if (day === TODAY_D && CURRENT_HOUR >= 12) {
          esPasado = true;
        }
      }
    }

    const estaOcupado = ocupados.has(dateStr);
    const estaPendiente = pendientes.has(dateStr);
    const estaBloqueadoTemporal = bloqueosTemporales.has(dateStr);

    if (esPasado || estaOcupado) {
      btn.classList.add('bg-gray-200','text-gray-400','line-through','cursor-not-allowed');
      btn.disabled = true;
    } else if (estaPendiente || estaBloqueadoTemporal) {
      btn.classList.add('bg-amber-100','text-amber-700','border','border-amber-300','cursor-not-allowed');
      
      // Buscar qué rango de fechas está pendiente o bloqueado temporalmente
      let rangoEncontrado = null;
      let mensaje = PENDIENTE_MSG;
      
      // Primero buscar en reservas pendientes
      const rangosPendientes = (window.RESERVAS_PENDIENTES_RANGOS && window.RESERVAS_PENDIENTES_RANGOS[vehiculoActual]) || [];
      rangoEncontrado = rangosPendientes.find(r => {
        const inicio = new Date(r.inicio);
        const fin = new Date(r.fin);
        const fechaActual = new Date(dateStr);
        return fechaActual >= inicio && fechaActual <= fin;
      });
      
      // Si no está en reservas pendientes, buscar en bloqueos temporales
      if (!rangoEncontrado) {
        const rangosBloqueos = (window.BLOQUEOS_TEMPORALES_RANGOS && window.BLOQUEOS_TEMPORALES_RANGOS[vehiculoActual]) || [];
        rangoEncontrado = rangosBloqueos.find(r => {
          const inicio = new Date(r.inicio);
          const fin = new Date(r.fin);
          const fechaActual = new Date(dateStr);
          return fechaActual >= inicio && fechaActual <= fin;
        });
        
        if (rangoEncontrado) {
          if (rangoEncontrado.inicio === rangoEncontrado.fin) {
            mensaje = `La fecha ${rangoEncontrado.inicio_formateado} está siendo procesada por otro cliente. Vuelve a intentarlo en unos minutos.`;
          } else {
            mensaje = `Las fechas del ${rangoEncontrado.inicio_formateado} al ${rangoEncontrado.fin_formateado} están siendo procesadas por otro cliente. Vuelve a intentarlo en unos minutos.`;
          }
        }
      } else {
        if (rangoEncontrado.inicio === rangoEncontrado.fin) {
          mensaje = `La fecha ${rangoEncontrado.inicio_formateado} está en proceso de confirmación por otro cliente. Revisa nuevamente en unos minutos.`;
        } else {
          mensaje = `Las fechas del ${rangoEncontrado.inicio_formateado} al ${rangoEncontrado.fin_formateado} están en proceso de confirmación por otro cliente. Revisa nuevamente en unos minutos.`;
        }
      }
      
      btn.title = mensaje;
      btn.onclick = () => alert(mensaje);
    } else {
      btn.classList.add('bg-white','hover:bg-emerald-100','border');

      if (selectedDates.has(dateStr)) {
        btn.classList.add('bg-emerald-500','text-white','border-emerald-500');
      }

      btn.onclick = () => {
        if (selectedDates.has(dateStr)) {
          selectedDates.delete(dateStr);
        } else {
          selectedDates.add(dateStr);
        }
        renderCalendar();
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

// Preparar reserva y abrir modal de pago
function guardarReserva() {
  const fechas = Array.from(selectedDates).sort();

  if (!fechas.length) {
    alert('Seleccioná al menos un día');
    return;
  }

  // Primero preparar la reserva para obtener datos y calcular monto
  fetch(window.reservasPrepararUrl, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "Accept": "application/json",
      "X-CSRF-TOKEN": window.csrfToken
    },
    body: JSON.stringify({
      vehiculo_id: vehiculoActual,
      fechas: fechas
    })
  })
  .then(async (r) => {
    const data = await r.json().catch(() => null);
    if (!r.ok) {
      alert(data?.message ?? ('Error '+r.status));
      throw new Error('error');
    }
    return data;
  })
  .then((data) => {
    if (data.ok) {
      // Guardar la clave del bloqueo temporal (global para que modal-pago.js pueda acceder)
      window.bloqueoKeyActual = data.bloqueo_key || null;
      // Cerrar modal de calendario
      closeReservaModal();
      // Abrir modal de pago con los datos
      abrirModalPago(data);
    }
  })
  .catch(err => {
    console.error(err);
  });
}




