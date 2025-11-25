// Detectar mensaje de éxito desde el servidor
if (window.registerSuccess) {
  // Ocultar formulario y mostrar animación
  document.getElementById('registerCard').classList.add('hidden');
  const successAnim = document.getElementById('successAnimation');
  successAnim.classList.remove('hidden');
  successAnim.classList.add('flex');

  // Redirigir tras 3 segundos
  setTimeout(() => {
    window.location.href = window.homeUrl;
  }, 3000);
}


