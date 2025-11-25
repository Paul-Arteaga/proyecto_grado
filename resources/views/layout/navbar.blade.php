@php
  use App\Models\Vehiculo;
  $maintenanceThreshold = 10000;
  $maintenanceQuery = Vehiculo::query()->where(function($q) use ($maintenanceThreshold) {
      $q->whereRaw('(km_actual - km_ultimo_mantenimiento) >= ?', [$maintenanceThreshold])
        ->orWhere('estado', 'mantenimiento');
  });
  $maintenancePendingCount = $maintenanceQuery->count();
  $maintenanceLatest = $maintenancePendingCount ? (clone $maintenanceQuery)->orderByDesc('updated_at')->first(['updated_at']) : null;
  $maintenanceBadgeVersion = $maintenancePendingCount ? ($maintenanceLatest?->updated_at?->timestamp ?? now()->timestamp) : null;
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('titulo', 'Rentacar SRL')</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/rol.css') }}">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { font-family: 'Poppins', sans-serif; }
  </style>
</head>

<body class="bg-gray-50">
  {{-- Navbar Principal --}}
  <nav class="bg-white shadow-md sticky top-0 z-50 border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      {{-- Contenedor Principal --}}
      <div class="flex justify-between items-center h-16">
        
        {{-- Logo y Marca --}}
        <div class="flex items-center space-x-3">
          <a href="{{ route('mostrar.index') }}" class="flex items-center space-x-2 hover:opacity-80 transition-opacity">
            <img src="{{ asset('storage/general/auto.png') }}" alt="Rentacar SRL" class="h-10 w-auto">
            <span class="text-xl font-bold text-gray-800 hidden sm:block">Rentacar SRL</span>
          </a>
        </div>

        {{-- Menú Desktop (oculto en móvil) --}}
        <div class="hidden lg:flex items-center space-x-1 flex-1 justify-center">
          
          {{-- Dropdown: Sistema (Solo Admin) --}}
          @auth
            @if(Auth::user()->id_rol == 1)
              <div class="relative group">
                <button class="flex items-center space-x-1 px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors font-medium">
                  <span>⚙️ Sistema</span>
                  <svg class="w-4 h-4 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                  </svg>
                </button>
                <div class="absolute top-full left-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                  <a href="{{ route('mostrar.permiso') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors {{ request()->routeIs('mostrar.permiso') ? 'bg-blue-50 text-blue-700 font-semibold border-l-4 border-blue-500' : '' }}">
                    🔐 Permisos
                  </a>
                  <a href="{{ route('mostrar.rol') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors {{ request()->routeIs('mostrar.rol') ? 'bg-blue-50 text-blue-700 font-semibold border-l-4 border-blue-500' : '' }}">
                    👥 Roles
                  </a>
                  <a href="{{ route('mostrar.usuario') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors {{ request()->routeIs('mostrar.usuario') ? 'bg-blue-50 text-blue-700 font-semibold border-l-4 border-blue-500' : '' }}">
                    👤 Usuarios
                  </a>
                </div>
              </div>
            @endif
          @endauth

          {{-- Vehículos (Admin, Recepcionista, Usuario) --}}
          @auth
            @if(in_array(Auth::user()->id_rol, [1, 2, 3]))
              <div class="relative group">
                <button class="flex items-center space-x-1 px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors font-medium">
                  <span>🚗 Vehículos</span>
                  <svg class="w-4 h-4 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                  </svg>
                </button>
                <div class="absolute top-full left-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                  <a href="{{ route('categoria.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors {{ request()->routeIs('categoria.*') ? 'bg-blue-50 text-blue-700 font-semibold border-l-4 border-blue-500' : '' }}">
                    📁 Categorías
                  </a>
                  <a href="{{ route('disp.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors {{ request()->routeIs('disp.*') ? 'bg-blue-50 text-blue-700 font-semibold border-l-4 border-blue-500' : '' }}">
                    📊 Disponibilidad
                  </a>
                  <a href="{{ route('mostrar.tarifa') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors {{ request()->routeIs('mostrar.tarifa') ? 'bg-blue-50 text-blue-700 font-semibold border-l-4 border-blue-500' : '' }}">
                    💰 Tarifas
                  </a>
                </div>
              </div>
            @endif
          @endauth

          {{-- Reservas (Admin, Recepcionista) --}}
          @auth
            @if(in_array(Auth::user()->id_rol, [1, 3]))
              <a href="{{ route('reservas.index') }}" class="px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors font-medium {{ request()->routeIs('reservas.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                📅 Reservas
              </a>
            @endif
          @endauth

          {{-- Mis Reservas (Solo Usuario) --}}
          @auth
            @if(Auth::user()->id_rol == 2)
              <a href="{{ route('perfil.reservas') }}" class="px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors font-medium {{ request()->routeIs('perfil.reservas') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                📅 Mis Reservas
              </a>
            @endif
          @endauth

          {{-- Clientes (Admin, Recepcionista) --}}
          @auth
            @if(in_array(Auth::user()->id_rol, [1, 3]))
              <a href="{{ route('clientes.index') }}" class="px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors font-medium {{ request()->routeIs('clientes.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                👥 Clientes
              </a>
            @endif
          @endauth

          {{-- Mantenimiento directo (Admin / Área mantenimiento) --}}
          @auth
            @if(in_array(Auth::user()->id_rol, [1, 4]))
              <div class="relative inline-flex items-center" data-maintenance-trigger>
                <a href="{{ route('mantenimiento.index') }}" class="px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors font-medium {{ request()->routeIs('mantenimiento.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                  🔧 Mantenimiento
                </a>
                <span data-maintenance-badge class="absolute -top-1 -right-1 w-2.5 h-2.5 rounded-full bg-red-500 shadow ring-2 ring-white animate-pulse {{ $maintenancePendingCount > 0 ? '' : 'hidden' }}"></span>
              </div>
            @endif
          @endauth

          {{-- Dropdown: Más (Según rol) --}}
          @auth
            <div class="relative group">
              <button class="flex items-center space-x-1 px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors font-medium">
                <span>📋 Más</span>
                <svg class="w-4 h-4 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </button>
              <div class="absolute top-full left-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                {{-- Admin: Ve todo --}}
                @if(Auth::user()->id_rol == 1)
                  <a href="{{ route('pago.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors {{ request()->routeIs('pago.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">💳 Pagos</a>
                  <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">🎁 Promociones</a>
                  <a href="{{ route('contrato.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors {{ request()->routeIs('contrato.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">📄 Contrato</a>
                  <a href="{{ route('documento.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors {{ request()->routeIs('documento.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">📁 Documentos</a>
                  <a href="{{ route('devolucion.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors {{ request()->routeIs('devolucion.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">↩️ Devoluciones</a>
                  <div class="relative" data-maintenance-trigger>
                  <div class="relative" data-maintenance-trigger>
                    <a href="{{ route('mantenimiento.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors {{ request()->routeIs('mantenimiento.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">🔧 Mantenimiento</a>
                    <span data-maintenance-badge class="absolute top-1 right-3 w-2.5 h-2.5 rounded-full bg-red-500 shadow ring-2 ring-white animate-pulse {{ $maintenancePendingCount > 0 ? '' : 'hidden' }}"></span>
                  </div>
                    <span data-maintenance-badge class="absolute top-1 right-3 w-2.5 h-2.5 rounded-full bg-red-500 shadow ring-2 ring-white animate-pulse {{ $maintenancePendingCount > 0 ? '' : 'hidden' }}"></span>
                  </div>
                  <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">💵 Cotizaciones</a>
                  <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">📎 Documentos</a>
                  <a href="{{ route('accesorio.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors {{ request()->routeIs('accesorio.index') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">🎨 Accesorios</a>
                @endif

                {{-- Recepcionista: Ve todo EXCEPTO Pagos --}}
                @if(Auth::user()->id_rol == 3)
                  <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">🎁 Promociones</a>
                  <a href="{{ route('devolucion.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors {{ request()->routeIs('devolucion.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">↩️ Devoluciones</a>
                  <div class="relative" data-maintenance-trigger>
                    <a href="{{ route('mantenimiento.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors {{ request()->routeIs('mantenimiento.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">🔧 Mantenimiento</a>
                    <span data-maintenance-badge class="absolute top-1 right-3 w-2.5 h-2.5 rounded-full bg-red-500 shadow ring-2 ring-white animate-pulse {{ $maintenancePendingCount > 0 ? '' : 'hidden' }}"></span>
                  </div>
                  <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">💵 Cotizaciones</a>
                  <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">📎 Documentos</a>
                  <a href="{{ route('accesorio.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors {{ request()->routeIs('accesorio.index') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">🎨 Accesorios</a>
                @endif

                {{-- Usuario: Solo Promociones y Accesorios --}}
                @if(Auth::user()->id_rol == 2)
                  <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">🎁 Promociones</a>
                  <a href="{{ route('accesorio.catalogo') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors {{ request()->routeIs('accesorio.catalogo') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">🎨 Accesorios</a>
                @endif

                {{-- Mantenimiento: Devoluciones, Mantenimiento, Accesorios --}}
                @if(Auth::user()->id_rol == 4)
                  <a href="{{ route('devolucion.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors {{ request()->routeIs('devolucion.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">↩️ Devoluciones</a>
                  <div class="relative" data-maintenance-trigger>
                    <a href="{{ route('mantenimiento.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors {{ request()->routeIs('mantenimiento.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">🔧 Mantenimiento</a>
                    <span data-maintenance-badge class="absolute top-1 right-3 w-2.5 h-2.5 rounded-full bg-red-500 shadow ring-2 ring-white animate-pulse {{ $maintenancePendingCount > 0 ? '' : 'hidden' }}"></span>
                  </div>
                  <a href="{{ route('accesorio.catalogo') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors {{ request()->routeIs('accesorio.catalogo') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">🎨 Accesorios</a>
                @endif
              </div>
            </div>
          @endauth

        </div>

        {{-- Sección Usuario y Acciones (Derecha) --}}
        <div class="flex items-center space-x-4">
          
          {{-- Información del Usuario (oculto en móvil pequeño) --}}
          @auth
            <div class="hidden md:flex items-center space-x-2 bg-gray-100 px-3 py-2 rounded-lg">
              <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
              <a href="{{ route('perfil.index') }}" class="text-sm text-gray-700 hover:text-gray-900">
                <span class="hidden lg:inline">Bienvenido, </span>
                <strong class="text-gray-900">{{ Auth::user()->name ?? Auth::user()->username }}</strong>
              </a>
            </div>

            {{-- Notificaciones (Todos los usuarios autenticados) --}}
            <div class="relative" id="notificaciones-container">
                <button onclick="toggleNotificaciones()" class="relative p-2 rounded-lg hover:bg-gray-100 transition-colors text-gray-700">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                  </svg>
                  <span id="notificaciones-badge" class="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
                </button>
                
                {{-- Dropdown de Notificaciones --}}
                <div id="notificaciones-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-50 max-h-96 overflow-y-auto">
                  <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-900">Notificaciones</h3>
                    <button onclick="marcarTodasLeidas()" class="text-xs text-blue-600 hover:text-blue-800">Marcar todas como leídas</button>
                  </div>
                  <div id="notificaciones-list" class="divide-y divide-gray-200">
                    <div class="p-4 text-center text-gray-500 text-sm">Cargando...</div>
                  </div>
                </div>
              </div>
          @endauth

          {{-- Botón Salir --}}
          <form action="{{ route('logout') }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="flex items-center space-x-2 bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded-lg transition-all shadow-sm hover:shadow-md">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
              </svg>
              <span class="hidden sm:inline">Salir</span>
            </button>
          </form>

          {{-- Botón Menú Móvil --}}
          <button id="mobile-menu-button" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
          </button>

        </div>
      </div>

      {{-- Menú Móvil (Colapsable) --}}
      <div id="mobile-menu" class="lg:hidden hidden border-t border-gray-200 py-4">
        <div class="space-y-4">
          @auth
            {{-- Sección: Sistema (Solo Admin) --}}
            @if(Auth::user()->id_rol == 1)
              <div>
                <div class="font-semibold text-gray-500 text-xs uppercase px-4 py-2">Sistema</div>
                <a href="{{ route('mostrar.permiso') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors {{ request()->routeIs('mostrar.permiso') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                  🔐 Permisos
                </a>
                <a href="{{ route('mostrar.rol') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors {{ request()->routeIs('mostrar.rol') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                  👥 Roles
                </a>
                <a href="{{ route('mostrar.usuario') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors {{ request()->routeIs('mostrar.usuario') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                  👤 Usuarios
                </a>
              </div>
            @endif
          
            {{-- Sección: Vehículos (Admin, Recepcionista, Usuario) --}}
            @if(in_array(Auth::user()->id_rol, [1, 2, 3]))
              <div>
                <div class="font-semibold text-gray-500 text-xs uppercase px-4 py-2">Vehículos</div>
                <a href="{{ route('categoria.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors {{ request()->routeIs('categoria.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                  📁 Categorías
                </a>
                <a href="{{ route('disp.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors {{ request()->routeIs('disp.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                  📊 Disponibilidad
                </a>
                <a href="{{ route('mostrar.tarifa') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors {{ request()->routeIs('mostrar.tarifa') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                  💰 Tarifas
                </a>
              </div>
            @endif
          @endauth

          @auth
            {{-- Reservas (Admin, Recepcionista) --}}
            @if(in_array(Auth::user()->id_rol, [1, 3]))
              <a href="{{ route('reservas.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors {{ request()->routeIs('reservas.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                📅 Reservas
              </a>
            @endif

            {{-- Mis Reservas (Solo Usuario) --}}
            @if(Auth::user()->id_rol == 2)
              <a href="{{ route('perfil.reservas') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors {{ request()->routeIs('perfil.reservas') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                📅 Mis Reservas
              </a>
            @endif

            {{-- Clientes (Admin, Recepcionista) --}}
            @if(in_array(Auth::user()->id_rol, [1, 3]))
              <a href="{{ route('clientes.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors {{ request()->routeIs('clientes.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                👥 Clientes
              </a>
            @endif

            {{-- Sección: Más (Según rol) --}}
            <div>
              <div class="font-semibold text-gray-500 text-xs uppercase px-4 py-2">Más</div>
              
              {{-- Admin: Ve todo --}}
              @if(Auth::user()->id_rol == 1)
                <a href="{{ route('pago.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors {{ request()->routeIs('pago.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">💳 Pagos</a>
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors">🎁 Promociones</a>
                <a href="{{ route('contrato.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors {{ request()->routeIs('contrato.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">📄 Contrato</a>
                <a href="{{ route('documento.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors {{ request()->routeIs('documento.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">📁 Documentos</a>
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors">↩️ Devoluciones</a>
                <div class="relative inline-flex items-center w-full" data-maintenance-trigger>
                  <a href="{{ route('mantenimiento.index') }}" class="block flex-1 px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors {{ request()->routeIs('mantenimiento.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">🔧 Mantenimiento</a>
                  <span data-maintenance-badge class="absolute top-2 right-3 w-2.5 h-2.5 rounded-full bg-red-500 shadow ring-2 ring-white animate-pulse {{ $maintenancePendingCount > 0 ? '' : 'hidden' }}"></span>
                </div>
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors">💵 Cotizaciones</a>
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors">📎 Documentos</a>
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors">🎨 Accesorios</a>
              @endif

              {{-- Recepcionista: Ve todo EXCEPTO Pagos --}}
              @if(Auth::user()->id_rol == 3)
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors">🎁 Promociones</a>
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors">↩️ Devoluciones</a>
                <div class="relative inline-flex items-center w-full" data-maintenance-trigger>
                  <a href="{{ route('mantenimiento.index') }}" class="block flex-1 px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors {{ request()->routeIs('mantenimiento.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">🔧 Mantenimiento</a>
                  <span data-maintenance-badge class="absolute top-2 right-3 w-2.5 h-2.5 rounded-full bg-red-500 shadow ring-2 ring-white animate-pulse {{ $maintenancePendingCount > 0 ? '' : 'hidden' }}"></span>
                </div>
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors">💵 Cotizaciones</a>
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors">📎 Documentos</a>
                <a href="{{ route('accesorio.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors {{ request()->routeIs('accesorio.index') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">🎨 Accesorios</a>
              @endif

              {{-- Usuario: Solo Promociones y Accesorios --}}
              @if(Auth::user()->id_rol == 2)
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors">🎁 Promociones</a>
                <a href="{{ route('accesorio.catalogo') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors {{ request()->routeIs('accesorio.catalogo') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">🎨 Accesorios</a>
              @endif

              {{-- Mantenimiento: Devoluciones, Mantenimiento, Accesorios --}}
              @if(Auth::user()->id_rol == 4)
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors">↩️ Devoluciones</a>
                <div class="relative inline-flex items-center w-full" data-maintenance-trigger>
                  <a href="{{ route('mantenimiento.index') }}" class="block flex-1 px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors {{ request()->routeIs('mantenimiento.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">🔧 Mantenimiento</a>
                  <span data-maintenance-badge class="absolute top-2 right-3 w-2.5 h-2.5 rounded-full bg-red-500 shadow ring-2 ring-white animate-pulse {{ $maintenancePendingCount > 0 ? '' : 'hidden' }}"></span>
                </div>
                <a href="{{ route('accesorio.catalogo') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors {{ request()->routeIs('accesorio.catalogo') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">🎨 Accesorios</a>
              @endif
            </div>
          @endauth

        </div>
      </div>

    </div>
  </nav>

  {{-- Contenido Principal --}}
  <section class="min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      @yield('contenido')
    </div>
  </section>

  {{-- Script para Menú Móvil y Notificaciones --}}
  <script>
    @auth
      window.authUser = true;
      window.notificacionesUrl = '{{ route("notificaciones.index") }}';
      window.notificacionesMarcarTodasUrl = '{{ route("notificaciones.marcarTodas") }}';
      window.notificacionesContarUrl = '{{ route("notificaciones.contar") }}';
      window.csrfToken = '{{ csrf_token() }}';
    @endauth
    window.maintenanceBadgeCount = {{ $maintenancePendingCount }};
    window.maintenanceBadgeVersion = "{{ $maintenanceBadgeVersion ?? '' }}";
    window.isMaintenancePage = {{ request()->routeIs('mantenimiento.*') ? 'true' : 'false' }};
  </script>
  <script src="{{ asset('js/navbar.js') }}"></script>
</body>
</html>
