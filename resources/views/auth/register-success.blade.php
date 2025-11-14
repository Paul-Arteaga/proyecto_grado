<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registro exitoso — Rentacar SRL</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
</head>
<body class="min-h-screen bg-gray-900 text-white flex items-center justify-center p-6">

  <div class="flex flex-col items-center text-center">
    <lottie-player
      src="https://assets9.lottiefiles.com/packages/lf20_jbrw3hcz.json"
      background="transparent"
      speed="1"
      style="width: 264px; height: 264px;"
      autoplay>
    </lottie-player>

    <h2 class="text-2xl font-bold text-green-400 mt-4">¡Registrado exitosamente!</h2>
    <p class="text-gray-300 mt-2">Estamos preparando tu experiencia…</p>
    <p class="text-gray-400 text-sm mt-1">Te redirigiremos automáticamente.</p>
  </div>

  <script>
    // Redirige al home (welcome) en 3 segundos
    setTimeout(() => {
      window.location.href = "{{ route('home') }}";
    }, 3000);
  </script>

</body>
</html>
