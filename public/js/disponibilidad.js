document.addEventListener('DOMContentLoaded', () => {
  const models = (window.MODELS || []).map(m => ({
    src: m.src,
    label: m.label,
    desc: m.desc || `${m.label} — Estos modelos son adecuados para la ciudad.`
  }));

  const visibleCount = 3;
  let startIndex = 0;
  let autoplayId = null;
  let autoplayStopped = false;

  const slotsWrap = document.getElementById('carouselSlots');
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');

  // 👉 elementos del panel principal
  const mainImage = document.getElementById('mainImage');
  const mainLabel = document.getElementById('mainLabel');
  const mainDesc  = document.getElementById('mainDesc');

  if (!slotsWrap || !prevBtn || !nextBtn || models.length === 0 || !mainImage || !mainLabel || !mainDesc) {
    console.warn('Carousel: faltan elementos del DOM o modelos.');
    return;
  }

  const slotElems = Array.from(slotsWrap.children);

  // Coloca un índice real en cada slot y pinta
  function render() {
    for (let i = 0; i < visibleCount; i++) {
      const realIndex = (startIndex + i) % models.length;
      const model = models[realIndex];

      const slot = slotElems[i];
      slot.dataset.modelIndex = String(realIndex);

      const img = slot.querySelector('img');
      const caption = slot.querySelector('span');
      if (img) { img.src = model.src; img.alt = model.label; }
      if (caption) { caption.textContent = model.label; }
    }
  }

  function move(delta) {
    startIndex = (startIndex + delta + models.length) % models.length;
    render();
  }

  function startAutoplay() {
    if (autoplayId || autoplayStopped) return;
    autoplayId = setInterval(() => move(1), 3000);
  }

  function stopAutoplay() {
    autoplayStopped = true;
    if (autoplayId) {
      clearInterval(autoplayId);
      autoplayId = null;
    }
  }

  // 👉 función para “promocionar” un modelo al panel principal
  function setMain(index) {
    const m = models[index];
    mainImage.src = m.src;
    mainImage.alt = m.label;
    mainLabel.textContent = m.label;
    mainDesc.textContent  = m.desc;
  }

  // Click en cada slot: promociona al panel y detiene autoplay
  slotElems.forEach(slot => {
    slot.addEventListener('click', () => {
      const idx = Number(slot.dataset.modelIndex);
      if (!Number.isNaN(idx)) {
        setMain(idx);
        stopAutoplay();
      }
    });
  });

  // Botones
  prevBtn.addEventListener('click', () => { move(-1); stopAutoplay(); });
  nextBtn.addEventListener('click', () => { move(1);  stopAutoplay(); });

  // Teclado
  document.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft')  { move(-1); stopAutoplay(); }
    if (e.key === 'ArrowRight') { move(1);  stopAutoplay(); }
  });

  // Init
  render();
  // Muestra inicialmente el primero
  setMain(0);
  startAutoplay();
});
