<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registro — Rentacar SRL</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
</head>
<body class="min-h-screen bg-gray-900 text-white flex items-center justify-center p-6">

  <div id="registerCard" class="w-full max-w-md bg-gray-800/70 backdrop-blur p-8 rounded-2xl border border-white/10 shadow-2xl transition-all duration-500">
    <h1 class="text-3xl font-bold text-white text-center mb-6">Crear cuenta</h1>

    @if ($errors->any())
      <div class="mb-4 text-red-300 text-sm">
        <ul class="list-disc list-inside">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    {{-- FORMULARIO --}}
    <form id="registerForm" action="{{ route('register.store') }}" method="POST" class="space-y-5">
      @csrf

      <div>
        <label class="block text-sm text-gray-200 mb-1">Usuario</label>
        <input type="text" name="username" value="{{ old('username') }}" required
               class="w-full px-3 py-3 rounded bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-700"
               placeholder="tuusuario">
      </div>

      <div>
        <label class="block text-sm text-gray-200 mb-1">Email (opcional)</label>
        <input type="email" name="email" value="{{ old('email') }}"
               class="w-full px-3 py-3 rounded bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-700"
               placeholder="tucorreo@ejemplo.com">
      </div>

      <div>
        <label class="block text-sm text-gray-200 mb-1">Contraseña</label>
        <input type="password" name="password" required
               class="w-full px-3 py-3 rounded bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-700"
               placeholder="********">
      </div>

      <div>
        <label class="block text-sm text-gray-200 mb-1">Confirmar contraseña</label>
        <input type="password" name="password_confirmation" required
               class="w-full px-3 py-3 rounded bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-700"
               placeholder="********">
      </div>

      <button type="submit"
              class="w-full py-3 rounded-md bg-blue-900 hover:bg-blue-800 font-semibold text-white transition">
        Registrarme
      </button>
    </form>

    <p class="mt-6 text-sm text-gray-300 text-center">
      ¿Ya tienes cuenta?
      <a href="{{ route('home') }}" class="text-blue-400 hover:text-blue-300 font-semibold transition">Inicia sesión</a>
    </p>
  </div>

  {{-- ANIMACIÓN DE ÉXITO --}}
  <div id="successAnimation" class="hidden flex-col items-center justify-center text-center">
    <lottie-player
      src="https://assets9.lottiefiles.com/packages/lf20_jbrw3hcz.json"
      background="transparent"
      speed="1"
      style="width: 200px; height: 200px;"
      autoplay>
    </lottie-player>
    <h2 class="text-2xl font-bold text-green-400 mt-4">¡Registrado exitosamente!</h2>
    <p class="text-gray-300 mt-2">Serás redirigido en unos segundos...</p>
  </div>

  <script>
    // Detectar mensaje de éxito desde el servidor
    @if (session('success'))
      // Ocultar formulario y mostrar animación
      document.getElementById('registerCard').classList.add('hidden');
      const successAnim = document.getElementById('successAnimation');
      successAnim.classList.remove('hidden');
      successAnim.classList.add('flex');

      // Redirigir tras 3 segundos
      setTimeout(() => {
        window.location.href = "{{ route('home') }}";
      }, 3000);
    @endif
  </script>

</body>
</html>
