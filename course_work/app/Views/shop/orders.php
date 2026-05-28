<div class="page-header"><h1>📦 Мої замовлення</h1></div>
<?php if (empty($orders)): ?>
<div class="empty-state"><p>Замовлень немає.</p><a href="<?= APP_URL ?>/shop" class="btn btn-primary">До магазину</a></div>
<?php else: ?>
<div class="bookings-list">
  <?php foreach ($orders as $o): ?>
  <div class="booking-card">
    <div class="bk-header">
      <h3>Замовлення #<?= $o['id'] ?></h3>
      <span class="status-badge st-confirmed">✅ Оплачено</span>
    </div>
    <div class="bk-body">
      <p>🍿 <?= htmlspecialchars($o['item_names']) ?></p>
      <p>💰 <?= number_format($o['total'],0) ?> грн</p>
      <p>📅 <?= date('d.m.Y H:i', strtotime($o['created_at'])) ?></p>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
