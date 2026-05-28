<div class="page-header"><h1>🎟 Мої квитки</h1></div>
<?php if (empty($bookings)): ?>
<div class="empty-state">
  <p>У вас поки немає бронювань.</p>
  <a href="<?= APP_URL ?>/schedule" class="btn btn-primary">Переглянути розклад</a>
</div>
<?php else: ?>
<div class="bookings-list">
  <?php foreach ($bookings as $b): ?>
  <div class="booking-card <?= $b['status']==='cancelled'?'bk-cancelled':'' ?>" id="bk-<?= $b['id'] ?>">
    <div class="bk-header">
      <h3><?= htmlspecialchars($b['movie_title']) ?></h3>
      <span class="status-badge st-<?= $b['status'] ?>"><?= $b['status']==='confirmed'?'✅ Активний':'❌ Скасовано' ?></span>
    </div>
    <div class="bk-body">
      <p>🏛 <?= htmlspecialchars($b['hall_name']) ?> · <?= $b['format'] ?></p>
      <p>📅 <?= date('d.m.Y H:i', strtotime($b['starts_at'])) ?></p>
      <p>
        <?php if ($b['seat_type']==='vip'): ?>
          💺 <strong>VIP</strong> — Ряд <?= $b['row_num'] ?>, Місце <?= $b['col_num'] ?>
        <?php else: ?>
          🪑 Стандарт — Ряд <?= $b['row_num'] ?>, Місце <?= $b['col_num'] ?>
        <?php endif; ?>
      </p>
      <p>💰 <?= number_format($b['price_paid'],0) ?> грн</p>
      <p class="ticket-code">🎫 <strong><?= htmlspecialchars($b['ticket_code']) ?></strong></p>
    </div>
    <?php if ($b['status']==='confirmed' && strtotime($b['starts_at'])>time()): ?>
    <div class="bk-footer">
      <button class="btn btn-danger btn-sm" onclick="cancelBk(<?= $b['id'] ?>)">Скасувати бронювання</button>
    </div>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
function cancelBk(id) {
  if (!confirm('Скасувати бронювання?')) return;
  fetch('<?= APP_URL ?>/api/booking/cancel',{
    method:'POST',headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
    body:JSON.stringify({booking_id:id,_csrf:CSRF_TOKEN}),
  }).then(r=>r.json()).then(d=>{
    if (d.success) {
      const el=document.getElementById('bk-'+id);
      el.classList.add('bk-cancelled');
      el.querySelector('.status-badge').className='status-badge st-cancelled';
      el.querySelector('.status-badge').textContent='❌ Скасовано';
      el.querySelector('.bk-footer')?.remove();
    } else alert('Помилка.');
  });
}
</script>
