<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Rentacar SRL</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .carousel-track { transition: transform 600ms ease; }
    .dot { transition: all 0.2s ease; }
    .dot.active { width: 0.9rem; height: 0.9rem; background-color: #fff; opacity: 1; }
  </style>
</head>
<body class="antialiased bg-gray-900 text-white min-h-screen flex flex-col">

  <main class="relative isolate flex-1 overflow-hidden">
    <!-- ====== HERO CAROUSEL ====== -->
    <section id="hero" class="relative h-[88vh] lg:h-screen">
      <div id="carousel" class="relative h-full overflow-hidden" aria-roledescription="carousel" aria-label="Promos Rentacar" tabindex="0">

        <!-- Slides -->
        <div id="carouselTrack" class="carousel-track flex h-full">
          <!-- Slide 1 -->
          <div class="relative w-full shrink-0 h-full">
            <img src="{{ asset('storage/general/welcome/honda.jpg') }}" alt="Flota moderna"
                 class="block w-full h-full object-cover" />
            <div class="absolute inset-0 flex items-start bg-black/30 pt-24 sm:pt-28 lg:pt-32">
              <div class="px-6 lg:px-24 max-w-3xl">
                <h1 class="text-3xl sm:text-5xl font-extrabold leading-tight">Movilidad sin límites</h1>
                <p class="mt-3 text-gray-200">
                  Sedanes, SUVs y pickups con <span class="font-semibold">mantenimiento al día</span>, 
                  <span class="font-semibold">kilometraje flexible</span> y coberturas a tu medida. 
                  Incluye <span class="font-semibold">asistencia 24/7</span>, conductor adicional opcional y 
                  kits de seguridad. Ideal para viajes de trabajo o escapadas de fin de semana.
                </p>
              </div>
            </div>
          </div>

          <!-- Slide 2 -->
          <div class="relative w-full shrink-0 h-full">
            <img src="{{ asset('storage/general/welcome/lexus.jpg') }}" alt="Alquiler corporativo"
                 class="block w-full h-full object-cover" />
            <div class="absolute inset-0 flex items-start bg-black/30 pt-24 sm:pt-28 lg:pt-32">
              <div class="px-6 lg:px-24 max-w-3xl">
                <h2 class="text-3xl sm:text-5xl font-extrabold leading-tight">Planes para empresas</h2>
                <p class="mt-3 text-gray-200">
                  Contratos corporativos con <span class="font-semibold">facturación mensual</span>, 
                  <span class="font-semibold">reemplazo inmediato</span> ante imprevistos y 
                  <span class="font-semibold">reportes de uso</span> para tu equipo. 
                  Centraliza reservas, agrega conductores y optimiza costos con tarifas preferenciales.
                </p>
              </div>
            </div>
          </div>

          <!-- Slide 3 -->
          <div class="relative w-full shrink-0 h-full">
            <img src="{{ asset('storage/general/welcome/jimny.jpg') }}" alt="Reserva online"
                 class="block w-full h-full object-cover" />
            <div class="absolute inset-0 flex items-start bg-black/30 pt-24 sm:pt-28 lg:pt-32">
              <div class="px-6 lg:px-24 max-w-3xl">
                <h2 class="text-3xl sm:text-5xl font-extrabold leading-tight">Reserva en minutos</h2>
                <p class="mt-3 text-gray-200">
                  Disponibilidad en tiempo real, <span class="font-semibold">cotización instantánea</span> 
                  y confirmación al instante. Paga con tarjeta, transferencia o en oficina; 
                  condiciones claras, depósito transparente y bloqueo de unidad sin trámites largos.
                </p>
              </div>
            </div>
          </div>

          <!-- Slide 4 -->
          <div class="relative w-full shrink-0 h-full">
            <img src="{{ asset('storage/general/welcome/hilux.jpg') }}" alt="Atención al cliente"
                 class="block w-full h-full object-cover" />
            <div class="absolute inset-0 flex items-start bg-black/30 pt-24 sm:pt-28 lg:pt-32">
              <div class="px-6 lg:px-24 max-w-3xl">
                <h2 class="text-3xl sm:text-5xl font-extrabold leading-tight">Atención que te acompaña</h2>
                <p class="mt-3 text-gray-200">
                  <span class="font-semibold">Entrega y retiro</span> en domicilio o aeropuerto, 
                  soporte por WhatsApp y teléfono, y <span class="font-semibold">asistencia en ruta</span>. 
                  Disponibles <span class="font-semibold">sillas para niños</span>, GPS y 
                  recomendaciones de ruta para que solo te preocupes de manejar.
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Indicadores (puntitos) -->
        <div id="indicators" class="absolute bottom-6 left-6 z-20 flex gap-3 items-center justify-start">
          <button class="dot w-3 h-3 rounded-full bg-white/60 cursor-pointer" data-slide="0" aria-label="Ir al slide 1"></button>
          <button class="dot w-3 h-3 rounded-full bg-white/40 cursor-pointer" data-slide="1" aria-label="Ir al slide 2"></button>
          <button class="dot w-3 h-3 rounded-full bg-white/40 cursor-pointer" data-slide="2" aria-label="Ir al slide 3"></button>
          <button class="dot w-3 h-3 rounded-full bg-white/40 cursor-pointer" data-slide="3" aria-label="Ir al slide 4"></button>
        </div>
      </div>
    </section>

    <!-- ====== LOGIN AMPLIADO AZUL MARINO ====== -->
    <div class="absolute inset-0 flex items-center justify-end px-6 lg:px-24 pointer-events-none">
      <div class="hidden lg:block w-full max-w-sm pr-6 pointer-events-auto">
        <div class="bg-gray-800/70 backdrop-blur p-12 rounded-2xl shadow-2xl border border-white/10 scale-[1.05]">
          <h2 class="text-3xl font-bold text-white mb-8 text-center">Iniciar Sesión</h2>
          <form action="{{ route('sendLogin') }}" method="POST" class="space-y-6">
            @csrf
            @error('errorUser') <p class="text-red-400 text-sm">{{ $message }}</p> @enderror
            @error('errorCred') <p class="text-red-400 text-sm">{{ $message }}</p> @enderror

            <div>
              <label class="block text-sm font-medium text-gray-200 mb-1">Usuario</label>
              <input type="text" name="username" value="{{ old('username') }}" placeholder="Usuario"
                     class="w-full px-3 py-3 rounded bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-700" required>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-200 mb-1">Contraseña</label>
              <input type="password" name="password" placeholder="********"
                     class="w-full px-3 py-3 rounded bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-700" required>
            </div>

            <!-- Botón azul marino -->
            <button type="submit" class="w-full py-3 rounded-md bg-blue-900 hover:bg-blue-800 font-semibold text-white transition">
              Entrar
            </button>
          </form>

          <!-- Enlace azul marino -->
          <p class="mt-6 text-sm text-gray-300 text-center">
            ¿No tienes cuenta?
            <a href="{{ route('register.show') }}" class="text-blue-400 hover:text-blue-300 font-semibold transition">Regístrate Ahora</a>
          </p>

        </div>
      </div>
    </div>
  </main>

  <script src="{{ asset('js/welcome.js') }}"></script>
</body>
</html>
