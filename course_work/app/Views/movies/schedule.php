<!-- БАНЕР — повна ширина -->
<style>
.schedule-banner-full {
  position: relative;
  width: 100vw;
  left: 50%;
  right: 50%;
  margin-left: -50vw;
  margin-right: -50vw;
  height: 260px;
  overflow: hidden;
  margin-bottom: 2rem;
}
.schedule-banner-full iframe {
  width: 100%;
  height: 100%;
  border: none;
  display: block;
  pointer-events: none;
}
.schedule-banner-full .banner-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(20,20,40,.75) 0%, rgba(20,20,40,.4) 100%);
  display: flex;
  align-items: center;
  padding: 0 5vw;
}
.schedule-banner-full .banner-overlay h1 {
  color: #fff;
  font-size: clamp(1.8rem, 4vw, 2.8rem);
  font-weight: 800;
  margin: 0 0 .4rem;
}
.schedule-banner-full .banner-overlay p {
  color: rgba(255,255,255,.82);
  font-size: 1.1rem;
  margin: 0;
}
</style>

<div class="schedule-banner-full">
  <iframe src="<?= APP_URL ?>/images/cinemax_cinema_banner.html" scrolling="no" frameborder="0" title="CineMax"></iframe>
  <div class="banner-overlay">
    <div>
      <h1>Розклад сеансів</h1>
      <p>Літо 2026 — кращі фільми кожного дня</p>
    </div>
  </div>
</div>

<div class="filter-bar card">
  <div class="fg"><label>Дата</label>
    <input type="date" id="fDate" value="<?= htmlspecialchars($date) ?>" min="<?= date('Y-m-d') ?>"></div>
  <div class="fg"><label>Фільм</label>
    <select id="fMovie">
      <option value="0">Всі фільми</option>
      <?php foreach ($movies as $m): ?>
      <option value="<?= $m['id'] ?>" <?= $movieId==(int)$m['id']?'selected':'' ?>><?= htmlspecialchars($m['title']) ?></option>
      <?php endforeach; ?>
    </select></div>
  <button class="btn btn-primary" onclick="loadSched()">🔍 Знайти</button>
</div>

<div id="schedResult">
<?php
$grouped = [];
foreach ($sessions as $s) { $grouped[date('d.m.Y',strtotime($s['starts_at']))][] = $s; }
?>
<?php if (empty($sessions)): ?>
  <p class="empty-msg">Сеансів не знайдено. Оберіть іншу дату.</p>
<?php else: ?>
  <?php foreach ($grouped as $day => $items): ?>
  <div class="day-group">
    <h3 class="day-label">📅 <?= $day ?></h3>
    <div class="sessions-grid">
      <?php foreach ($items as $s): ?>
      <div class="sess-card">
        <?php if ($s['poster']): ?>
        <img src="<?= APP_URL . htmlspecialchars($s['poster']) ?>" alt="<?= htmlspecialchars($s['movie_title']) ?>" class="sess-poster-sm">
        <?php endif; ?>
        <div class="sess-movie"><?= htmlspecialchars($s['movie_title']) ?></div>
        <div class="sess-time"><?= date('H:i',strtotime($s['starts_at'])) ?></div>
        <div class="sess-meta"><?= htmlspecialchars($s['hall_name']) ?> · <?= $s['format'] ?> · <?= $s['age_rating'] ?></div>
        <div class="sess-price">
          <span class="price-std">Стд: <?= (int)$s['price'] ?> грн</span>
          <?php if ($s['price_vip']>0): ?><span class="price-vip">VIP: <?= (int)$s['price_vip'] ?> грн</span><?php endif; ?>
        </div>
        <?php if (\App\Core\Session::isLoggedIn()): ?>
          <a href="<?= APP_URL ?>/booking/<?= $s['id'] ?>" class="btn btn-primary btn-sm">Купити</a>
        <?php else: ?>
          <a href="<?= APP_URL ?>/login" class="btn btn-outline btn-sm">Увійти</a>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>
<?php endif; ?>
</div>

<script>
const IS_AUTH = <?= \App\Core\Session::isLoggedIn()?'true':'false' ?>;
var APP_URL_JS = '<?= APP_URL ?>';

function loadSched() {
  const date  = document.getElementById('fDate').value;
  const movie = document.getElementById('fMovie').value;
  const res   = document.getElementById('schedResult');
  res.innerHTML = '<p class="loading">⏳ Завантаження…</p>';
  fetch(`${APP_URL_JS}/api/sessions?date=${date}&movie_id=${movie}`,
    {headers:{'X-Requested-With':'XMLHttpRequest'}})
  .then(r=>r.json()).then(d=>{
    if (!d.sessions || !d.sessions.length) {
      res.innerHTML='<p class="empty-msg">Сеансів не знайдено. Оберіть іншу дату.</p>'; return;
    }
    const grouped={};
    d.sessions.forEach(s=>{
      const day=new Date(s.starts_at.replace(' ','T')).toLocaleDateString('uk-UA');
      if (!grouped[day]) grouped[day]=[];
      grouped[day].push(s);
    });
    let html='';
    for (const [day,items] of Object.entries(grouped)) {
      html+=`<div class="day-group"><h3 class="day-label">📅 ${day}</h3><div class="sessions-grid">`;
      items.forEach(s=>{
        const t=new Date(s.starts_at.replace(' ','T')).toLocaleTimeString('uk-UA',{hour:'2-digit',minute:'2-digit'});
        const vip=s.price_vip>0?`<span class="price-vip">VIP: ${parseInt(s.price_vip)} грн</span>`:'';
        const poster=s.poster?`<img src="${APP_URL_JS}${s.poster}" alt="${s.movie_title}" class="sess-poster-sm">`:'';
        const btn=IS_AUTH
          ?`<a href="${APP_URL_JS}/booking/${s.id}" class="btn btn-primary btn-sm">Купити</a>`
          :`<a href="${APP_URL_JS}/login" class="btn btn-outline btn-sm">Увійти</a>`;
        html+=`<div class="sess-card">
          ${poster}
          <div class="sess-movie">${s.movie_title}</div>
          <div class="sess-time">${t}</div>
          <div class="sess-meta">${s.hall_name} · ${s.format}</div>
          <div class="sess-price"><span class="price-std">Стд: ${parseInt(s.price)} грн</span>${vip}</div>
          ${btn}</div>`;
      });
      html+='</div></div>';
    }
    res.innerHTML=html;
  }).catch(()=>{res.innerHTML='<p class="empty-msg">Помилка завантаження.</p>';});
}
document.getElementById('fDate').addEventListener('change',loadSched);
document.getElementById('fMovie').addEventListener('change',loadSched);
</script>
