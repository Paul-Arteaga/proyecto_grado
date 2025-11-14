<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('titulo')</title>

  <link rel="stylesheet" href="styles.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/navbar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/rol.css') }}"> 
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
  <nav class="navbar">
    <div class="cart-icon">
      <img src="{{ asset('storage/general/auto.png') }}" alt="Rentacar SRL" class="logoCarrito">
    </div>

    <ul class="nav-ini">     
      <li><a href="{{ route('mostrar.permiso') }}">Permisos</a></li>
      <li><a href="{{ route('mostrar.rol') }}">Rol</a></li>
      <li><a href="#">Usuario</a></li>
      <li><a href="#">Cliente</a></li>
      <li><a href="{{ route('disp.index') }}">Disponibilidad</a></li>
      <li><a href="{{ route('reservas.index') }}">Reserva</a></li>
      <li><a href="#">Promoción</a></li>
      <li><a href="#">Contrato</a></li>
      <li><a href="#">Devolución</a></li>
      <li><a href="#">Mantenimiento</a></li>
      <li><a href="#">Cotizar</a></li>
      <li><a href="#">Tarifa</a></li>
      <li><a href="#">Documentos</a></li>
      <li><a href="#">Accesorios</a></li>
      <li><a href="{{ route('categoria.index') }}">Categoría</a></li>
    </ul>

    <ul class="nav-fin flex items-center space-x-4">
      {{-- Mostrar el usuario actual --}}
      @auth
        <li class="text-white text-sm">
          Bienvenido, <strong>{{ Auth::user()->username }}</strong>
        </li>
      @endauth

      {{-- Botón de salir --}}
      <li>
        <form action="{{ route('logout') }}" method="POST" class="inline">
          @csrf
          <button type="submit"
                  class="bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded-md transition">
            Salir
          </button>
        </form>
      </li>
    </ul>
  </nav>

  <section class="hero">
    <div class="hero-content">
      @yield('contenido')
    </div>
  </section>
</body>
</html>
