async function patch(url, body) {
  return fetch(url, {
    method: 'PATCH',
    headers: {
      'X-CSRF-TOKEN': window.csrfToken, 
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(body)
  });
}

document.getElementById('btnSyncVehiculos').addEventListener('click', async ()=>{
  const sel = document.getElementById('vehiculos');
  const ids = Array.from(sel.selectedOptions).map(o=>o.value);
  const res = await patch(window.syncVehiculosUrl, {vehiculos: ids});
  if (res.ok) alert('Vínculos de vehículos guardados.');
});

document.getElementById('btnSyncTarifas').addEventListener('click', async ()=>{
  const sel = document.getElementById('tarifas');
  const ids = Array.from(sel.selectedOptions).map(o=>o.value);
  const res = await patch(window.syncTarifasUrl, {tarifas: ids});
  if (res.ok) alert('Vínculos de tarifas guardados.');
});


