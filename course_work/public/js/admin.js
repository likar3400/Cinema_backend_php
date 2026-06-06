
(function(){
  const path = window.location.pathname;
  document.querySelectorAll('.sb-link').forEach(a=>{
    const href = a.getAttribute('href')||'';
    if (href.length > 1 && path.startsWith(href)) a.classList.add('active');
    else if (href.endsWith('/admin') && path === '/admin') a.classList.add('active');
  });
})();

window.ajaxDel = function(path, id) {
  if (!confirm('Видалити запис? Цю дію не можна скасувати.')) return;
  fetch(window.location.origin + path, {
    method:'POST',
    headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
    body: JSON.stringify({_csrf: CSRF_TOKEN}),
  }).then(r=>r.json()).then(d=>{
    if (d.success) {
      const row = document.getElementById('row-'+id);
      if (row) {
        row.style.transition='opacity .4s';
        row.style.opacity='0';
        setTimeout(()=>row.remove(), 400);
      }
    } else {
      alert('Помилка видалення: '+(d.message||'невідома'));
    }
  }).catch(()=>alert('Помилка з\'єднання.'));
};
