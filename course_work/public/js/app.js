(function(){
  const b=document.getElementById('burger');
  const l=document.getElementById('navLinks');
  if(!b||!l) return;
  b.addEventListener('click',()=>{
    l.classList.toggle('open');
    b.setAttribute('aria-expanded',l.classList.contains('open'));
  });
  document.addEventListener('click',e=>{
    if(!b.contains(e.target)&&!l.contains(e.target)) l.classList.remove('open');
  });
})();

document.querySelectorAll('.flash').forEach(el=>{
  setTimeout(()=>{ el.style.transition='.5s'; el.style.opacity='0'; setTimeout(()=>el.remove(),500); },5000);
});


window.apiPost = async function(url, data={}) {
  const res = await fetch(APP_URL_JS + url, {
    method:'POST',
    headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
    body: JSON.stringify({...data, _csrf: (typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : '')}),
  });
  return res.json();
};