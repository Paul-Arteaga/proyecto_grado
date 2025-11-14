(function () {
  const track = document.getElementById('carouselTrack');
  const carousel = document.getElementById('carousel');
  const dotsWrap = document.getElementById('indicators');

  if (!track || !carousel || !dotsWrap) return;

  const slides = Array.from(track.children);
  const dots = Array.from(dotsWrap.querySelectorAll('button'));
  const total = slides.length;

  // Ancho dinámico del track y cada slide
  track.style.width = `${total * 100}%`;
  slides.forEach(s => (s.style.width = `${100 / total}%`));

  let index = 0;
  const intervalMs = 3000;
  let intervalId = null;
  let isPaused = false;

  function updateDots() {
    dots.forEach((d, n) => {
      const isActive = n === index;
      d.classList.toggle('active', isActive);
      d.style.opacity = isActive ? '1' : '0.5';
      d.setAttribute('aria-current', isActive ? 'true' : 'false');
    });
  }

  function goTo(i) {
    index = (i + total) % total;
    // translateX usa % relativo al ancho del track
    track.style.transform = `translateX(-${index * (100 / total)}%)`;
    updateDots();
  }

  function next() { goTo(index + 1); }
  function start() { stop(); intervalId = setInterval(() => { if (!isPaused) next(); }, intervalMs); }
  function stop() { if (intervalId) clearInterval(intervalId); intervalId = null; }

  // Clic en dots -> navega al slide
  dots.forEach((dot, i) => {
    dot.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      goTo(i);
      start(); // reinicia temporizador
    });
    // Accesible por teclado
    dot.setAttribute('tabindex', '0');
    dot.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        goTo(i);
        start();
      }
    });
  });

  // Pausa en hover sobre el área del carrusel
  carousel.addEventListener('mouseenter', () => { isPaused = true; });
  carousel.addEventListener('mouseleave', () => { isPaused = false; });

  // Swipe táctil
  let startX = 0, deltaX = 0, swiping = false;
  carousel.addEventListener('touchstart', (e) => {
    swiping = true;
    startX = e.touches[0].clientX;
    deltaX = 0;
    isPaused = true;
  }, { passive: true });

  carousel.addEventListener('touchmove', (e) => {
    if (!swiping) return;
    deltaX = e.touches[0].clientX - startX;
  }, { passive: true });

  carousel.addEventListener('touchend', () => {
    if (!swiping) return;
    swiping = false;
    isPaused = false;
    if (deltaX > 60) goTo(index - 1);
    else if (deltaX < -60) goTo(index + 1);
    start();
  });

  goTo(0);
  start();
})();
