<div class="page-header">
  <h1>🍿 Магазин попкорну та напоїв</h1>
  <p class="page-sub">Замовляй улюблені снеки прямо до свого місця у залі!</p>
</div>

<?php
$grouped = [];
foreach ($items as $it) $grouped[$it['category']][] = $it;
$catLabels = ['popcorn'=>'🍿 Попкорн','drink'=>'🥤 Напої','snack'=>'🍫 Снеки','combo'=>'🎁 Комбо-набори'];
?>

<?php foreach ($catLabels as $cat => $label): ?>
<?php if (empty($grouped[$cat])) continue; ?>
<section class="section">
  <h2 class="section-title"><?= $label ?></h2>
  <div class="shop-grid">
    <?php foreach ($grouped[$cat] as $it): ?>
    <div class="shop-card" data-id="<?= $it['id'] ?>" data-price="<?= $it['price'] ?>" data-name="<?= htmlspecialchars($it['name']) ?>">
      <?php if ($it['image']): ?>
        <img src="<?= APP_URL . htmlspecialchars($it['image']) ?>" alt="<?= htmlspecialchars($it['name']) ?>" class="shop-img">
      <?php else: ?>
        <div class="shop-icon"><?= $cat==='popcorn'?'🍿':($cat==='drink'?'🥤':($cat==='snack'?'🍫':'🎁')) ?></div>
      <?php endif; ?>
      <div class="shop-info">
        <h3><?= htmlspecialchars($it['name']) ?></h3>
        <p class="shop-desc"><?= htmlspecialchars($it['description'] ?? '') ?></p>
        <div class="shop-price"><strong><?= number_format($it['price'],0) ?> грн</strong></div>
        <?php if (\App\Core\Session::isLoggedIn()): ?>
        <button class="btn btn-primary btn-sm btn-full mt-1" onclick="addToCart(<?= $it['id'] ?>, this)">
          🛒 До кошика
        </button>
        <?php else: ?>
        <a href="<?= APP_URL ?>/login" class="btn btn-outline btn-sm btn-full mt-1">Увійти для замовлення</a>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endforeach; ?>

<div id="shopNotif" class="shop-notif" style="display:none"></div>

<script>
function addToCart(itemId, btn) {
  const orig = btn.textContent;
  btn.disabled = true; btn.textContent = '⏳...';

  fetch('<?= APP_URL ?>/api/cart/add', {
    method: 'POST',
    headers: {'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
    body: JSON.stringify({item_id: itemId, qty: 1, _csrf: CSRF_TOKEN}),
  }).then(r => r.json()).then(d => {
    if (d.success) {
      btn.textContent = '✅ Додано!';
      btn.style.background = '#22c55e';
      // Оновлюємо лічильник кошика у навбарі
      const badge = document.getElementById('cartBadge');
      if (badge) badge.textContent = d.cart_count;

      showNotif('✅ ' + d.message);
      setTimeout(() => { btn.disabled = false; btn.textContent = orig; btn.style.background = ''; }, 2000);
    } else {
      btn.disabled = false; btn.textContent = orig;
      showNotif('❌ ' + (d.message || 'Помилка'), true);
    }
  }).catch(() => { btn.disabled = false; btn.textContent = orig; });
}

function showNotif(msg, isError = false) {
  const el = document.getElementById('shopNotif');
  el.textContent = msg;
  el.className = 'shop-notif ' + (isError ? 'shop-notif-err' : 'shop-notif-ok');
  el.style.display = 'block';
  clearTimeout(window._notifTimer);
  window._notifTimer = setTimeout(() => el.style.display = 'none', 3000);
}
</script>
