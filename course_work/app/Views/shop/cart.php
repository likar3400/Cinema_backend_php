<div class="page-header">
  <h1>🛒 Кошик</h1>
</div>

<?php if (empty($items)): ?>
<div class="empty-state">
  <div style="font-size:4rem;margin-bottom:16px">🛒</div>
  <p>Ваш кошик порожній</p>
  <a href="<?= APP_URL ?>/shop" class="btn btn-primary">До магазину</a>
</div>
<?php else: ?>

<div class="cart-wrap">
  <div class="cart-items" id="cartItems">
    <?php foreach ($items as $it): ?>
    <div class="cart-item" id="ci-<?= $it['item_id'] ?>">
      <div class="ci-img">
        <?php if ($it['image']): ?>
          <img src="<?= APP_URL . htmlspecialchars($it['image']) ?>" alt="<?= htmlspecialchars($it['name']) ?>">
        <?php else: ?>
          <span style="font-size:2rem">🍿</span>
        <?php endif; ?>
      </div>
      <div class="ci-info">
        <h3><?= htmlspecialchars($it['name']) ?></h3>
        <p><?= htmlspecialchars($it['description'] ?? '') ?></p>
      </div>
      <div class="ci-qty">
        <button class="qty-btn" onclick="updateQty(<?= $it['item_id'] ?>, -1)">−</button>
        <span class="qty-val" id="qv-<?= $it['item_id'] ?>"><?= $it['qty'] ?></span>
        <button class="qty-btn" onclick="updateQty(<?= $it['item_id'] ?>, 1)">+</button>
      </div>
      <div class="ci-price" id="cp-<?= $it['item_id'] ?>">
        <?= number_format($it['price'] * $it['qty'], 0) ?> грн
      </div>
      <button class="ci-remove" onclick="removeItem(<?= $it['item_id'] ?>)" title="Видалити">×</button>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="cart-summary card">
    <h2>Підсумок</h2>
    <div class="cs-row"><span>Товарів:</span><strong id="csCount"><?= array_sum(array_column($items,'qty')) ?></strong></div>
    <div class="cs-row cs-total"><span>Разом:</span><strong id="csTotal"><?= number_format($total,0) ?> грн</strong></div>
    <button class="btn btn-primary btn-full mt-2" id="btnCheckout" onclick="checkout()">
      ✅ Оформити замовлення
    </button>
    <a href="<?= APP_URL ?>/shop" class="btn btn-outline btn-full mt-1">← Продовжити покупки</a>
    <div id="cartMsg"></div>
  </div>
</div>

<script>
    var CSRF = document.querySelector('meta[name="csrf"]')?.getAttribute('content') || '';
    var qtys = {};
<?php foreach ($items as $it): ?>
qtys[<?= $it['item_id'] ?>] = <?= $it['qty'] ?>;
<?php endforeach; ?>
    var prices = {};
<?php foreach ($items as $it): ?>
prices[<?= $it['item_id'] ?>] = <?= $it['price'] ?>;
<?php endforeach; ?>

function updateQty(id, delta) {
  const newQty = Math.max(0, (qtys[id] || 0) + delta);
  qtys[id] = newQty;

  if (newQty === 0) { removeItem(id); return; }

  document.getElementById('qv-' + id).textContent = newQty;
  document.getElementById('cp-' + id).textContent = (prices[id] * newQty).toLocaleString('uk-UA') + ' грн';

  fetch('<?= APP_URL ?>/api/cart/update', {
    method: 'POST',
    headers: {'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
    body: JSON.stringify({item_id: id, qty: newQty, _csrf: CSRF}),
  }).then(r => r.json()).then(d => {
    document.getElementById('csTotal').textContent = parseInt(d.total).toLocaleString('uk-UA') + ' грн';
    document.getElementById('csCount').textContent = d.cart_count;
    updateNavCart(d.cart_count);
  });
}

function removeItem(id) {
  fetch('<?= APP_URL ?>/api/cart/remove', {
    method: 'POST',
    headers: {'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
    body: JSON.stringify({item_id: id, _csrf: CSRF}),
  }).then(r => r.json()).then(d => {
    if (d.success) {
      const el = document.getElementById('ci-' + id);
      el.style.opacity = '0';
      el.style.transition = '.3s';
      setTimeout(() => {
        el.remove();
        updateNavCart(d.cart_count);
        if (!document.querySelector('.cart-item')) {
          document.getElementById('cartItems').innerHTML =
            '<p class="empty-msg">Кошик порожній. <a href="<?= APP_URL ?>/shop">До магазину</a></p>';
          document.querySelector('.cart-summary').style.display = 'none';
        }
      }, 300);
    }
  });
}

function checkout() {
  const btn = document.getElementById('btnCheckout');
  btn.disabled = true; btn.textContent = '⏳ Обробка...';
  fetch('<?= APP_URL ?>/api/cart/checkout', {
    method: 'POST',
    headers: {'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
    body: JSON.stringify({_csrf: CSRF}),
  }).then(r => r.json()).then(d => {
    const m = document.getElementById('cartMsg');
    m.style.display = 'block';
    if (d.success) {
      m.className = 'ajax-ok';
      m.innerHTML = `✅ Замовлення #${d.order_id} оформлено! <a href="<?= APP_URL ?>/shop/orders">Мої замовлення</a>`;
      document.getElementById('cartItems').innerHTML = '';
      document.getElementById('csTotal').textContent = '0 грн';
      document.getElementById('csCount').textContent = '0';
      updateNavCart(0);
    } else {
      m.className = 'ajax-err';
      m.textContent = '❌ ' + (d.message || 'Помилка.');
      btn.disabled = false; btn.textContent = '✅ Оформити замовлення';
    }
  });
}

function updateNavCart(count) {
  const badge = document.getElementById('cartBadge');
  if (badge) badge.textContent = count > 0 ? count : '';
}
</script>
<?php endif; ?>
