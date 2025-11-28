const csrf = window.csrfToken;
document.querySelectorAll('.toggle-estado').forEach(btn=>{
  btn.addEventListener('click', async e=>{
    e.preventDefault();
    const res = await fetch(btn.dataset.url, {
      method: 'PATCH',
      headers: {
        'X-CSRF-TOKEN': csrf,
        'Accept': 'application/json'
      }
    });
    if(res.ok) location.reload();
  });
});






