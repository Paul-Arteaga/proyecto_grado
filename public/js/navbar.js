// Menú móvil
document.getElementById('mobile-menu-button')?.addEventListener('click', function() {
  const menu = document.getElementById('mobile-menu');
  menu.classList.toggle('hidden');
});

document.addEventListener('click', function(event) {
  const menu = document.getElementById('mobile-menu');
  const button = document.getElementById('mobile-menu-button');
  if (menu && !menu.contains(event.target) && !button?.contains(event.target)) {
    menu.classList.add('hidden');
  }

  // Cerrar notificaciones si se hace clic fuera
  const notifContainer = document.getElementById('notificaciones-container');
  const notifDropdown = document.getElementById('notificaciones-dropdown');
  if (notifContainer && notifDropdown && !notifContainer.contains(event.target)) {
    notifDropdown.classList.add('hidden');
  }
});

// Notificaciones (para todos los usuarios autenticados)
if (window.authUser) {
  function cargarNotificaciones() {
    fetch(window.notificacionesUrl)
      .then(r => r.json())
      .then(data => {
        const badge = document.getElementById('notificaciones-badge');
        const list = document.getElementById('notificaciones-list');
        
        if (data.length > 0) {
          badge.textContent = data.length;
          badge.classList.remove('hidden');
          
          list.innerHTML = data.map(n => `
            <div class="p-4 hover:bg-gray-50 cursor-pointer" onclick="marcarLeida(${n.id})">
              <p class="font-medium text-gray-900 text-sm">${n.titulo}</p>
              <p class="text-xs text-gray-600 mt-1">${n.mensaje}</p>
              <p class="text-xs text-gray-400 mt-2">${new Date(n.created_at).toLocaleString()}</p>
            </div>
          `).join('');
        } else {
          badge.classList.add('hidden');
          list.innerHTML = '<div class="p-4 text-center text-gray-500 text-sm">No hay notificaciones</div>';
        }
      })
      .catch(err => console.error('Error cargando notificaciones:', err));
  }

  function toggleNotificaciones() {
    const dropdown = document.getElementById('notificaciones-dropdown');
    dropdown.classList.toggle('hidden');
    if (!dropdown.classList.contains('hidden')) {
      cargarNotificaciones();
    }
  }

  window.toggleNotificaciones = toggleNotificaciones;

  function marcarLeida(id) {
    fetch(`${window.notificacionesUrl}/${id}/leida`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': window.csrfToken,
        'Content-Type': 'application/json'
      }
    })
    .then(() => {
      cargarNotificaciones();
      actualizarContador();
    });
  }

  window.marcarLeida = marcarLeida;

  function marcarTodasLeidas() {
    fetch(window.notificacionesMarcarTodasUrl, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': window.csrfToken,
        'Content-Type': 'application/json'
      }
    })
    .then(() => {
      cargarNotificaciones();
      actualizarContador();
    });
  }

  window.marcarTodasLeidas = marcarTodasLeidas;

  function actualizarContador() {
    fetch(window.notificacionesContarUrl)
      .then(r => r.json())
      .then(data => {
        const badge = document.getElementById('notificaciones-badge');
        if (data.count > 0) {
          badge.textContent = data.count;
          badge.classList.remove('hidden');
        } else {
          badge.classList.add('hidden');
        }
      });
  }

  // Cargar contador al cargar la página
  actualizarContador();
  // Actualizar cada 30 segundos
  setInterval(actualizarContador, 30000);
}

// Badge de mantenimiento
(function() {
  const badgeElems = document.querySelectorAll('[data-maintenance-badge]');
  const triggerElems = document.querySelectorAll('[data-maintenance-trigger]');
  const version = window.maintenanceBadgeVersion || '';
  const count = window.maintenanceBadgeCount || 0;
  const key = 'maintenanceBadgeSeen';

  if (!badgeElems.length) return;

  const hideBadges = () => badgeElems.forEach(el => el.classList.add('hidden'));
  const showBadges = () => badgeElems.forEach(el => el.classList.remove('hidden'));

  if (!count || !version) {
    hideBadges();
    return;
  }

  let storedVersion = null;
  try {
    storedVersion = localStorage.getItem(key);
  } catch (e) {
    console.warn('LocalStorage no disponible para el badge de mantenimiento.');
  }
  if (storedVersion === version) {
    hideBadges();
  } else {
    showBadges();
  }

  function markSeen() {
    try {
      localStorage.setItem(key, version);
    } catch (e) {
      console.warn('No se pudo guardar el estado del badge de mantenimiento.', e);
    }
    hideBadges();
  }

  if (window.isMaintenancePage) {
    markSeen();
  } else {
    triggerElems.forEach(el => {
      el.addEventListener('click', markSeen, { once: true });
    });
  }
})();


