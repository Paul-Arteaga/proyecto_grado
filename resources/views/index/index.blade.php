@extends('layout.navbar')

@section('titulo', 'index')

@section('contenido')

@php
    use App\Models\Reserva;

    // 1) Traemos solo las reservas CONFIRMADAS (las solicitadas no bloquean el calendario)
    $reservasRaw = Reserva::where('estado', 'confirmada')
        ->select('vehiculo_id','fecha_inicio','fecha_fin')
        ->get();

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


<main class="max-w-6xl mx-auto py-6 sm:py-8 lg:py-12 flex flex-col lg:flex-row items-start gap-6 sm:gap-8 lg:gap-12 px-4 sm:px-6">
  <!-- Imagen y descripción -->
  <div class="flex-1 flex flex-col items-center lg:items-start w-full lg:w-auto">
    <img id="mainImage"
         src="{{ asset('storage/general/toyo.jpg') }}"
         alt="Vehículo principal"
         class="rounded-xl shadow-lg w-full max-w-full lg:max-w-xl max-h-[300px] sm:max-h-[350px] lg:max-h-[380px] object-cover" />

    <div id="mainInfo" class="mt-4 bg-white/70 rounded-lg p-4 sm:p-6 shadow w-full max-w-xl">
      <h4 class="text-lg sm:text-xl font-bold" id="mainLabel">Selecciona una categoría</h4>
      <p class="text-gray-700 text-sm sm:text-base leading-relaxed mt-2" id="mainDesc">
        Toca una categoría del carrusel para verla aquí con su descripción.
      </p>
    </div>
  </div>

  <!-- Carrusel de categorías -->
  <div class="flex-1 flex flex-col items-center lg:items-start w-full lg:w-auto lg:ml-0 xl:ml-16">
    <h3 class="text-xl sm:text-2xl font-bold mb-3 sm:mb-4 text-center lg:text-left">CATEGORÍAS</h3>
    <p class="mb-4 sm:mb-6 text-gray-600 text-sm sm:text-base text-center lg:text-left">Selecciona una categoría de vehículo</p>

    <div class="relative w-full max-w-lg overflow-visible">
      <div class="pointer-events-none absolute inset-y-0 left-0 w-10 bg-gradient-to-r from-white via-white/70 to-transparent hidden sm:block"></div>
      <div class="pointer-events-none absolute inset-y-0 right-0 w-10 bg-gradient-to-l from-white via-white/70 to-transparent hidden sm:block"></div>

      <!-- Carrusel -->
      <div id="carouselZone" class="relative w-full cursor-grab active:cursor-grabbing select-none">
      <div id="carouselSlots" class="flex items-start justify-center gap-3 sm:gap-4 lg:gap-5 scale-100 sm:scale-110 lg:scale-125 origin-center">
        <div class="flex flex-col items-center cursor-pointer">
          <img class="w-24 h-18 sm:w-28 sm:h-20 lg:w-32 lg:h-24 object-cover rounded shadow-md" alt="Marca 1">
          <span class="text-gray-400 text-xs sm:text-sm mt-1"></span>
        </div>
        <div class="flex flex-col items-center cursor-pointer">
          <img class="w-24 h-18 sm:w-28 sm:h-20 lg:w-32 lg:h-24 object-cover rounded shadow-md" alt="Marca 2">
          <span class="text-gray-400 text-xs sm:text-sm mt-1"></span>
        </div>
        <div class="flex flex-col items-center cursor-pointer">
          <img class="w-24 h-18 sm:w-28 sm:h-20 lg:w-32 lg:h-24 object-cover rounded shadow-md" alt="Marca 3">
          <span class="text-gray-400 text-xs sm:text-sm mt-1"></span>
        </div>
      </div>
      </div>
      <p class="mt-4 text-xs sm:text-sm text-gray-500 flex items-center justify-center gap-2">
        <span class="w-8 h-px bg-gray-300"></span>
        Desliza para explorar las categorías
        <span class="w-8 h-px bg-gray-300"></span>
      </p>
    </div>

    <!-- Botón general debajo del carrusel -->
    <div class="mt-6 sm:mt-8 flex justify-center w-full">
      <a href="{{ url('/disponibilidad') }}"
         class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 sm:px-6 py-2 sm:py-3 rounded-lg shadow transition flex items-center gap-2 text-sm sm:text-base">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 7l5 5m0 0l-5 5m5-5H6"/>
        </svg>
        <span class="hidden sm:inline">Vehículos disponibles</span>
        <span class="sm:hidden">Vehículos</span>
      </a>
    </div>
  </div>
  
</main>

{{-- =================== LISTADO DE VEHÍCULOS =================== --}}
<section class="max-w-6xl mx-auto px-4 sm:px-6 mt-6 sm:mt-8 lg:mt-10">
  <h2 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6">Ofertas recientes</h2>

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

    <article class="bg-white shadow rounded-lg p-4 sm:p-6 mb-4 flex flex-col sm:flex-row items-start gap-4 sm:gap-6">
      <img src="{{ $foto }}" alt="{{ $v->marca }} {{ $v->modelo }}"
           class="w-full sm:w-40 h-48 sm:h-28 object-cover rounded-md border">

      <div class="flex-1 min-w-0 w-full sm:w-auto">
        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
          <h3 class="text-lg sm:text-xl font-semibold truncate">
            {{ $v->marca }} {{ $v->modelo }}
          </h3>
          <span class="text-xs px-2 py-1 rounded text-white {{ $estadoColor }} self-start sm:self-auto">
            {{ ucfirst($v->estado) }}
          </span>
        </div>

        <div class="mt-2 flex flex-wrap items-center gap-2 sm:gap-3 text-xs sm:text-sm text-gray-700">
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

      <div class="w-full sm:w-48 flex flex-col sm:items-end justify-between gap-4 sm:gap-0">
        @if($precio)
          <div class="text-left sm:text-right w-full sm:w-auto">
            <div class="text-xs sm:text-sm text-gray-500">Desde</div>
            <div class="text-xl sm:text-2xl font-bold">Bs {{ $precio }}</div>
            <div class="text-xs text-emerald-600 mt-1">Cancelación gratuita</div>
          </div>
        @endif

        <button
          type="button"
          onclick="openReservaModal({{ $v->id }}, '{{ $v->marca }} {{ $v->modelo }}')"
          class="w-full sm:w-auto mt-0 sm:mt-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 sm:px-6 py-2 sm:py-2.5 rounded text-sm sm:text-base">
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

  const carouselZone = document.getElementById('carouselZone');
  if (carouselZone) {
    let dragStartX = null;
    let pointerId = null;
    const threshold = 30;

    const finishDrag = (clientX) => {
      if (dragStartX === null) return;
      const delta = clientX - dragStartX;
      dragStartX = null;
      if (pointerId !== null) {
        try { carouselZone.releasePointerCapture(pointerId); } catch (_) {}
        pointerId = null;
      }
      carouselZone.classList.remove('cursor-grabbing');
      if (Math.abs(delta) > threshold) {
        const total = window.MODELS.length;
        if (!total) return;
        start = (start + (delta < 0 ? 1 : -1) + total) % total;
        renderSlots();
      }
    };

    carouselZone.addEventListener('pointerdown', (e) => {
      dragStartX = e.clientX;
      pointerId = e.pointerId;
      carouselZone.setPointerCapture(pointerId);
      carouselZone.classList.add('cursor-grabbing');
    });

    ['pointerup','pointerleave','pointercancel'].forEach(evt => {
      carouselZone.addEventListener(evt, (e) => finishDrag(e.clientX));
    });
  }

  renderSlots();
  if (window.MODELS.length) {
    imgMain.src = window.MODELS[0].src;
    labelMain.textContent = window.MODELS[0].label;
    descMain.textContent = window.MODELS[0].desc;
  }
</script>

{{-- 3) MODAL DEL CALENDARIO --}}
<div id="reservaModal"
     class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50 p-4">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md max-h-[90vh] overflow-y-auto p-4 sm:p-5">
    <div class="flex items-center justify-between mb-3">
      <h3 class="text-base sm:text-lg font-bold" id="reservaTitulo">Reservar vehículo</h3>
      <button onclick="closeReservaModal()" class="text-gray-500 hover:text-gray-700 text-xl sm:text-2xl">&times;</button>
    </div>

    <p class="text-xs sm:text-sm text-gray-500 mb-3">
      Toca los días que quieres reservar. Los días en gris están ocupados.
    </p>

    <!-- Selector de mes -->
    <div class="flex items-center justify-between mb-2">
      <button class="px-2 sm:px-3 py-1 text-xs sm:text-sm border rounded" onclick="changeMonth(-1)">&lt;</button>
      <div id="monthLabel" class="font-semibold text-sm sm:text-base"></div>
      <button class="px-2 sm:px-3 py-1 text-xs sm:text-sm border rounded" onclick="changeMonth(1)">&gt;</button>
    </div>

    <!-- Calendario -->
    <div id="calendarGrid" class="grid grid-cols-7 gap-1 text-center text-xs sm:text-sm mb-4">
      <!-- se llena por JS -->
    </div>

    <input type="hidden" id="vehiculoSeleccionado">

    <div class="flex flex-col sm:flex-row justify-end gap-2">
      <button onclick="closeReservaModal()" class="w-full sm:w-auto px-4 py-2 text-xs sm:text-sm rounded border">Cancelar</button>
      <button onclick="guardarReserva()" class="w-full sm:w-auto px-4 py-2 text-xs sm:text-sm rounded bg-emerald-600 text-white">Guardar</button>
    </div>
  </div>
</div>

<script>
  window.reservasPrepararUrl = "{{ route('reservas.preparar') }}";
  window.csrfToken = "{{ csrf_token() }}";
</script>
<script src="{{ asset('js/index.js') }}"></script>

{{-- Incluir modal de pago --}}
@include('reservas.partials.modal-pago')

@endsection

