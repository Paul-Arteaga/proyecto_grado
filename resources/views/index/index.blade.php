@extends('layout.navbar')

@section('titulo', 'index')

@section('contenido')

@php
    use App\Models\Reserva;

    // 1) Traemos todas las reservas con inicio y fin
    $reservasRaw = Reserva::select('vehiculo_id','fecha_inicio','fecha_fin')->get();

    // 2) Construimos: vehiculo_id => ['2025-11-02', '2025-11-03', ...]
    $reservasPorVehiculo = [];

    foreach ($reservasRaw as $r) {
        $vid = $r->vehiculo_id;

        if (!isset($reservasPorVehiculo[$vid])) {
            $reservasPorVehiculo[$vid] = [];
        }

        $inicio = \Carbon\Carbon::parse($r->fecha_inicio);
        $fin    = \Carbon\Carbon::parse($r->fecha_fin ?? $r->fecha_inicio);

        $d = $inicio->copy();
        while ($d->lte($fin)) {
            $reservasPorVehiculo[$vid][] = $d->format('Y-m-d');
            $d->addDay();
        }
    }
@endphp

{{-- lo mandamos al JS --}}
<script>
    window.RESERVAS = @json($reservasPorVehiculo);
</script>


<main class="max-w-6xl mx-auto py-12 flex flex-col lg:flex-row items-start gap-12 px-6">
  <!-- Imagen y descripción -->
  <div class="flex-1 flex flex-col items-center lg:items-start">
    <img id="mainImage"
         src="{{ asset('storage/general/toyo.jpg') }}"
         alt="Vehículo principal"
         class="rounded-xl shadow-lg max-h-[380px] object-cover" />

    <div id="mainInfo" class="mt-4 bg-white/70 rounded-lg p-4 shadow w-full max-w-xl">
      <h4 class="text-lg font-bold" id="mainLabel">Selecciona una categoría</h4>
      <p class="text-gray-700 text-sm leading-relaxed" id="mainDesc">
        Toca una categoría del carrusel para verla aquí con su descripción.
      </p>
    </div>
  </div>

  <!-- Carrusel de categorías -->
  <div class="flex-1 flex flex-col items-center lg:items-start lg:ml-[4cm]">
    <h3 class="text-2xl font-bold mb-4">CATEGORÍAS</h3>
    <p class="mb-6 text-gray-600">Selecciona una categoría de vehículo</p>

    <div class="relative w-full max-w-lg overflow-visible">
      <!-- Botones -->
      <button id="prevBtn"
              class="absolute left-[-2cm] top-1/2 -translate-y-1/2 bg-gray-800/80 text-white rounded-full p-[10px] hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 scale-[0.95]"
              aria-label="Anterior">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 19l-7-7 7-7"/>
        </svg>
      </button>

      <button id="nextBtn"
              class="absolute right-[-2cm] top-1/2 -translate-y-1/2 bg-gray-800/80 text-white rounded-full p-[10px] hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 scale-[0.95]"
              aria-label="Siguiente">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 5l7 7-7 7"/>
        </svg>
      </button>

      <!-- Carrusel -->
      <div id="carouselSlots" class="flex items-start justify-center gap-5 scale-[1.20] origin-center">
        <div class="flex flex-col items-center cursor-pointer">
          <img class="w-32 h-24 object-cover rounded shadow-md" alt="Marca 1">
          <span class="text-gray-400 text-sm mt-1"></span>
        </div>
        <div class="flex flex-col items-center cursor-pointer">
          <img class="w-32 h-24 object-cover rounded shadow-md" alt="Marca 2">
          <span class="text-gray-400 text-sm mt-1"></span>
        </div>
        <div class="flex flex-col items-center cursor-pointer">
          <img class="w-32 h-24 object-cover rounded shadow-md" alt="Marca 3">
          <span class="text-gray-400 text-sm mt-1"></span>
        </div>
      </div>
    </div>

    <!-- Botón general debajo del carrusel -->
    <div class="mt-8 flex justify-center w-full">
      <a href="{{ url('/disponibilidad') }}"
         class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-3 rounded-lg shadow transition flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 7l5 5m0 0l-5 5m5-5H6"/>
        </svg>
        Vehículos disponibles
      </a>
    </div>
  </div>
  
</main>

{{-- =================== LISTADO DE VEHÍCULOS =================== --}}
<section class="max-w-6xl mx-auto px-6 mt-10">
  <h2 class="text-2xl font-bold mb-4">Ofertas recientes</h2>

  @php
    $vehFallback = asset('storage/general/toyo.jpg');
  @endphp

  @forelse($vehiculos as $v)
    @php
      $foto = $v->foto ? url('media/'.ltrim($v->foto,'/')) : $vehFallback;
      $cat  = optional($v->categoria);
      $cap  = $cat->capacidad_pasajeros ? $cat->capacidad_pasajeros.' pax' : '—';
      $precio = isset($v->precio_diario) ? number_format($v->precio_diario, 2, ',', '.') : null;
      $estadoColor = match($v->estado){
        'disponible'    => 'bg-green-600',
        'reservado'     => 'bg-blue-600',
        'bloqueado'     => 'bg-orange-500',
        'mantenimiento' => 'bg-red-600',
        default         => 'bg-gray-500',
      };
    @endphp

    <article class="bg-white shadow rounded-lg p-4 mb-4 flex items-start gap-6">
      <img src="{{ $foto }}" alt="{{ $v->marca }} {{ $v->modelo }}"
           class="w-40 h-28 object-cover rounded-md border">

      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2">
          <h3 class="text-lg font-semibold truncate">
            {{ $v->marca }} {{ $v->modelo }}
          </h3>
          <span class="ml-2 text-xs px-2 py-1 rounded text-white {{ $estadoColor }}">
            {{ ucfirst($v->estado) }}
          </span>
        </div>

        <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-gray-700">
          <span class="inline-flex items-center gap-1">
            {{ $cap }}
          </span>
          <span class="inline-flex items-center gap-1">
            {{ $v->transmision ?? '—' }}
          </span>
          <span class="inline-flex items-center gap-1">
            {{ number_format($v->km_actual ?? 0, 0, ',', '.') }} km
          </span>
          <span class="inline-flex items-center gap-1">
            {{ $cat->nombre ?? 'Sin categoría' }}
          </span>
        </div>
      </div>

      <div class="w-48 flex flex-col items-end justify-between">
        @if($precio)
          <div class="text-right">
            <div class="text-sm text-gray-500">Desde</div>
            <div class="text-2xl font-bold">Bs {{ $precio }}</div>
            <div class="text-xs text-emerald-600 mt-1">Cancelación gratuita</div>
          </div>
        @endif

        <button
          type="button"
          onclick="openReservaModal({{ $v->id }}, '{{ $v->marca }} {{ $v->modelo }}')"
          class="mt-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-2 rounded">
          Reservar ahora
        </button>
      </div>
    </article>
  @empty
    <p class="text-gray-500">Aún no hay vehículos cargados.</p>
  @endforelse
</section>

{{-- ========== MODELS desde BD ========== --}}
@php
  $fallback = asset('storage/general/categoria/fallback.png');
  $models = collect($categorias ?? [])->map(function($c) use ($fallback) {
      $src = $c->imagen
        ? url('media/'.ltrim($c->imagen,'/'))
        : $fallback;

      return [
        'id'    => $c->id,
        'src'   => $src,
        'label' => $c->nombre,
        'desc'  => $c->descripcion ?: 'Sin descripción.',
      ];
  });
@endphp

<script>
  // Carga de categorías en el carrusel
  window.MODELS = @json($models);

  const slots   = document.querySelectorAll('#carouselSlots > div');
  const imgMain = document.getElementById('mainImage');
  const labelMain = document.getElementById('mainLabel');
  const descMain  = document.getElementById('mainDesc');

  let start = 0;

  function renderSlots() {
    const total = window.MODELS.length;
    if (!total) return;

    for (let i = 0; i < slots.length; i++) {
      const m = window.MODELS[(start + i) % total];

      const img  = slots[i].querySelector('img');
      const span = slots[i].querySelector('span');

      img.src = m.src;
      img.alt = m.label;
      span.textContent = m.label;

      slots[i].onclick = () => {
        imgMain.src = m.src;
        labelMain.textContent = m.label;
        descMain.textContent = m.desc;
      };
    }
  }

  document.getElementById('prevBtn').addEventListener('click', () => {
    const total = window.MODELS.length;
    if (!total) return;
    start = (start - 1 + total) % total;
    renderSlots();
  });

  document.getElementById('nextBtn').addEventListener('click', () => {
    const total = window.MODELS.length;
    if (!total) return;
    start = (start + 1) % total;
    renderSlots();
  });

  renderSlots();
  if (window.MODELS.length) {
    imgMain.src = window.MODELS[0].src;
    labelMain.textContent = window.MODELS[0].label;
    descMain.textContent = window.MODELS[0].desc;
  }
</script>

{{-- 3) MODAL DEL CALENDARIO --}}
<div id="reservaModal"
     class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-5">
    <div class="flex items-center justify-between mb-3">
      <h3 class="text-lg font-bold" id="reservaTitulo">Reservar vehículo</h3>
      <button onclick="closeReservaModal()" class="text-gray-500 hover:text-gray-700">&times;</button>
    </div>

    <p class="text-sm text-gray-500 mb-3">
      Toca los días que quieres reservar. Los días en gris están ocupados.
    </p>

    <!-- Selector de mes -->
    <div class="flex items-center justify-between mb-2">
      <button class="px-2 py-1 text-sm border rounded" onclick="changeMonth(-1)">&lt;</button>
      <div id="monthLabel" class="font-semibold"></div>
      <button class="px-2 py-1 text-sm border rounded" onclick="changeMonth(1)">&gt;</button>
    </div>

    <!-- Calendario -->
    <div id="calendarGrid" class="grid grid-cols-7 gap-1 text-center text-sm mb-4">
      <!-- se llena por JS -->
    </div>

    <input type="hidden" id="vehiculoSeleccionado">

    <div class="flex justify-end gap-2">
      <button onclick="closeReservaModal()" class="px-4 py-2 text-sm rounded border">Cancelar</button>
      <button onclick="guardarReserva()" class="px-4 py-2 text-sm rounded bg-emerald-600 text-white">Guardar</button>
    </div>
  </div>
</div>

{{-- 4) JS DEL CALENDARIO --}}
<script>
  // si por algún motivo arriba NO llegó, lo dejamos como objeto
  window.RESERVAS = window.RESERVAS || {};

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

      if (esPasado || estaOcupado) {
        btn.classList.add('bg-gray-200','text-gray-400','line-through','cursor-not-allowed');
        btn.disabled = true;
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

  //  ESTA es la que te faltaba que coincida con el backend
  function guardarReserva() {
  const fechas = Array.from(selectedDates).sort();

  if (!fechas.length) {
    alert('Seleccioná al menos un día');
    return;
  }

  fetch("{{ route('reservas.store') }}", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "Accept": "application/json",
      "X-CSRF-TOKEN": "{{ csrf_token() }}"
    },
    body: JSON.stringify({
      vehiculo_id: vehiculoActual,
      fechas: fechas
    })
  })
  .then(async (r) => {
    // si no es 2xx tratamos de leer el json para ver el error
    const data = await r.json().catch(() => null);

    if (!r.ok) {
      // acá te va a aparecer si es 419 o lo que sea
      alert(data?.message ?? ('Error '+r.status));
      throw new Error('error'); // para que no siga
    }

    return data;
  })
  .then((data) => {
    // actualizar calendario local
    if (!window.RESERVAS[vehiculoActual]) {
      window.RESERVAS[vehiculoActual] = [];
    }
    window.RESERVAS[vehiculoActual] = window.RESERVAS[vehiculoActual].concat(fechas);

    closeReservaModal();
    alert('Reserva guardada ✅');
  })
  .catch(err => {
    console.error(err);
    // ya mostramos arriba, acá no hace falta otro alert
  });
}

</script>

@endsection

