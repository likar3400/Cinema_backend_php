<div class="stats-grid">
  <div class="stat-card accent-gold"><div class="stat-icon">🎬</div><div class="stat-num"><?= $moviesCount ?></div><div class="stat-label">Фільмів</div><a href="<?= APP_URL ?>/admin/movies" class="stat-link">Керувати →</a></div>
  <div class="stat-card accent-blue"><div class="stat-icon">👥</div><div class="stat-num"><?= $usersCount ?></div><div class="stat-label">Користувачів</div><a href="<?= APP_URL ?>/admin/users" class="stat-link">Керувати →</a></div>
  <div class="stat-card accent-green"><div class="stat-icon">🎟</div><div class="stat-num"><?= $stats['confirmed'] ?></div><div class="stat-label">Активних бронювань</div><a href="<?= APP_URL ?>/admin/bookings" class="stat-link">Переглянути →</a></div>
  <div class="stat-card accent-vip"><div class="stat-icon">💰</div><div class="stat-num"><?= number_format($stats['revenue'],0,'.',' ') ?> грн</div><div class="stat-label">Загальний дохід</div></div>
</div>

<div class="stats-row mt-3">
  <div class="card stats-half">
    <h3>📈 Статистика місяця (<?= date('m.Y') ?>)</h3>
    <div class="mini-stats">
      <div class="ms-row"><span>Квитків продано:</span><strong><?= $monthly['total_seats'] ?></strong></div>
      <div class="ms-row"><span>VIP місць:</span><strong><?= $monthly['vip_seats'] ?></strong></div>
      <div class="ms-row"><span>Стандарт місць:</span><strong><?= $monthly['standard_seats'] ?></strong></div>
      <div class="ms-row"><span>Дохід від квитків:</span><strong><?= number_format($monthly['revenue'],0,'.',' ') ?> грн</strong></div>
      <div class="ms-row"><span>Дохід від магазину:</span><strong><?= number_format($monthly['shop_revenue'],0,'.',' ') ?> грн</strong></div>
    </div>
    <a href="<?= APP_URL ?>/admin/stats" class="btn btn-outline btn-sm mt-2">Детальна статистика →</a>
  </div>
  <div class="card stats-half">
    <h3>⚡ Швидкий доступ</h3>
    <div class="quick-links">
      <a href="<?= APP_URL ?>/admin/movies/create"   class="qlink">➕ Додати фільм</a>
      <a href="<?= APP_URL ?>/admin/sessions/create" class="qlink">➕ Додати сеанс</a>
      <a href="<?= APP_URL ?>/admin/users/create"    class="qlink">➕ Новий користувач</a>
      <a href="<?= APP_URL ?>/admin/news/create"     class="qlink">➕ Нова новина</a>
      <a href="<?= APP_URL ?>/admin/halls/create"    class="qlink">➕ Додати зал</a>
      <a href="<?= APP_URL ?>/" target="_blank"      class="qlink">🌐 Переглянути сайт</a>
    </div>
  </div>
</div>
