@extends('layout.navbar')

@section('titulo', 'Reservas')

@section('contenido')

@php
    use App\Models\Reserva;

    // Fechas ocupadas por vehículo, igualito que en index
    $reservasRaw = Reserva::select('vehiculo_id','fecha_inicio','fecha_fin','id')->get();

    // vamos a expandir los rangos para que el calendario pueda marcarlos día por día
    $ocupadasPorVehiculo = [];

    foreach ($reservasRaw as $r) {
        $vid = $r->vehiculo_id;
        if (!isset($ocupadasPorVehiculo[$vid])) {
            $ocupadasPorVehiculo[$vid] = [];
        }

        $inicio = \Carbon\Carbon::parse($r->fecha_inicio);
        $fin    = \Carbon\Carbon::parse($r->fecha_fin ?: $r->fecha_inicio);

        $d = $inicio->copy();
        while ($d->lte($fin)) {
            $ocupadasPorVehiculo[$vid][] = $d->format('Y-m-d');
            $d->addDay();
        }
    }
@endphp

<script>
    // esto lo usará el calendario de edición
    window.RESERVAS = @json($ocupadasPorVehiculo);
</script>

<div class="max-w-5xl mx-auto mt-10 bg-white shadow rounded-lg p-6">
    <h1 class="text-2xl font-bold mb-4">Reservas</h1>

    @if(session('ok'))
        <div class="mb-3 px-3 py-2 bg-green-100 text-green-700 rounded">
            {{ session('ok') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-3 px-3 py-2 bg-red-100 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="bg-gray-100 text-left">
                    <th class="px-3 py-2">ID</th>
                    <th class="px-3 py-2">Cliente</th>
                    <th class="px-3 py-2">Vehículo</th>
                    <th class="px-3 py-2">Fechas</th>
                    <th class="px-3 py-2">Estado</th>
                    <th class="px-3 py-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reservas as $r)
                    <tr class="border-b">
                        <td class="px-3 py-2">{{ $r->id }}</td>
                        <td class="px-3 py-2">
                            {{ $r->user?->username ?? $r->user?->email ?? '—' }}
                        </td>
                        <td class="px-3 py-2">
                            {{ $r->vehiculo?->marca ?? '—' }} {{ $r->vehiculo?->modelo ?? '' }}
                        </td>
                        <td class="px-3 py-2">
                            {{ \Carbon\Carbon::parse($r->fecha_inicio)->format('d/m/Y') }}
                            @if($r->fecha_fin && $r->fecha_fin != $r->fecha_inicio)
                                – {{ \Carbon\Carbon::parse($r->fecha_fin)->format('d/m/Y') }}
                            @endif
                        </td>
                        <td class="px-3 py-2">
                            <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">
                                {{ ucfirst($r->estado) }}
                            </span>
                        </td>
                        <td class="px-3 py-2 flex gap-2">
                            <!-- EDITAR con calendario -->
                            <button
                                type="button"
                                onclick="openEditReservaCalendar({
                                    id: {{ $r->id }},
                                    vehiculo_id: {{ $r->vehiculo_id ?? 'null' }},
                                    fecha_inicio: '{{ $r->fecha_inicio }}',
                                    fecha_fin: '{{ $r->fecha_fin ?? $r->fecha_inicio }}'
                                })"
                                class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">
                                Editar
                            </button>

                            <!-- ELIMINAR -->
                            <form action="{{ route('reservas.destroy', $r->id) }}" method="POST" onsubmit="return confirm('¿Eliminar esta reserva?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700">
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-3 py-2" colspan="6">No hay reservas aún.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL DE EDICIÓN CON EL MISMO CALENDARIO -->
<div id="editReservaModal"
     class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-5">
    <div class="flex items-center justify-between mb-3">
      <h3 class="text-lg font-bold" id="editReservaTitulo">Editar reserva</h3>
      <button onclick="closeEditReservaCalendar()" class="text-gray-500 hover:text-gray-700">&times;</button>
    </div>

    <p class="text-sm text-gray-500 mb-3">
      Toca los días para ajustar el rango. Los días en gris están ocupados por otras reservas de este vehículo.
    </p>

    <!-- Selector de mes -->
    <div class="flex items-center justify-between mb-2">
      <button class="px-2 py-1 text-sm border rounded" onclick="editChangeMonth(-1)">&lt;</button>
      <div id="editMonthLabel" class="font-semibold"></div>
      <button class="px-2 py-1 text-sm border rounded" onclick="editChangeMonth(1)">&gt;</button>
    </div>

    <!-- Calendario -->
    <div id="editCalendarGrid" class="grid grid-cols-7 gap-1 text-center text-sm mb-4">
      <!-- se llena por JS -->
    </div>

    <div class="flex justify-end gap-2">
      <button onclick="closeEditReservaCalendar()" class="px-4 py-2 text-sm rounded border">Cancelar</button>
      <button onclick="guardarEdicionReserva()" class="px-4 py-2 text-sm rounded bg-blue-600 text-white">Guardar</button>
    </div>
  </div>
</div>

<script>
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
    // RESERVAS ya viene del @php

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

        fetch("{{ url('/reservas') }}/" + EDIT_RESERVA_ID, {
            method: "POST", // spoof PUT
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
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
</script>

@endsection
