<!DOCTYPE html>
<html lang="uk">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= htmlspecialchars($title ?? 'Адмін') ?></title>
<link rel="stylesheet" href="<?= APP_URL ?>/css/style.css">
<link rel="stylesheet" href="<?= APP_URL ?>/css/admin.css">
<meta name="csrf" content="<?= \App\Core\Session::csrfToken() ?>">
</head>
<body class="admin-body">
<div class="admin-wrap">

<aside class="sidebar">
  <div class="sb-brand"><a href="<?= APP_URL ?>/admin">⚙ CineMax Admin</a></div>
  <nav class="sb-nav">
    <a href="<?= APP_URL ?>/admin"           class="sb-link">📊 Дашборд</a>
    <a href="<?= APP_URL ?>/admin/stats"     class="sb-link">📈 Статистика</a>
    <div class="sb-sep"></div>
    <a href="<?= APP_URL ?>/admin/movies"    class="sb-link">🎬 Фільми</a>
    <a href="<?= APP_URL ?>/admin/categories" class="sb-link">🏷 Категорії</a>
    <a href="<?= APP_URL ?>/admin/sessions"  class="sb-link">🕐 Сеанси</a>
    <a href="<?= APP_URL ?>/admin/bookings"  class="sb-link">🎟 Бронювання</a>
    <a href="<?= APP_URL ?>/admin/halls"     class="sb-link">🏛 Зали</a>
    <div class="sb-sep"></div>
    <a href="<?= APP_URL ?>/admin/users"     class="sb-link">👥 Користувачі</a>
    <a href="<?= APP_URL ?>/admin/news"      class="sb-link">📰 Новини</a>
    <a href="<?= APP_URL ?>/admin/shop"      class="sb-link">🍿 Магазин</a>
    <a href="<?= APP_URL ?>/admin/reviews"   class="sb-link">💬 Відгуки</a>
    <div class="sb-sep"></div>
    <a href="<?= APP_URL ?>/"               class="sb-link">← На сайт</a>
    <a href="<?= APP_URL ?>/logout"         class="sb-link sb-logout">🚪 Вийти</a>
  </nav>
</aside>

<div class="admin-main">
  <header class="admin-bar">
    <h1 class="admin-title"><?= htmlspecialchars($title ?? '') ?></h1>
    <span class="admin-user">👤 <?= htmlspecialchars(\App\Core\Session::userName()) ?></span>
  </header>

  <?php $f=\App\Core\Session::getFlash('success'); if($f): ?>
  <div class="flash flash-success"><?= htmlspecialchars($f) ?> <button class="flash-x" onclick="this.parentElement.remove()">×</button></div>
  <?php endif; ?>
  <?php $fe=\App\Core\Session::getFlash('error'); if($fe): ?>
  <div class="flash flash-error"><?= htmlspecialchars($fe) ?> <button class="flash-x" onclick="this.parentElement.remove()">×</button></div>
  <?php endif; ?>

  <div class="admin-content">
    <?= $content ?? '' ?>
  </div>
</div>
</div>

<script>const CSRF_TOKEN='<?= \App\Core\Session::csrfToken() ?>';</script>
<script src="<?= APP_URL ?>/js/app.js"></script>
<script src="<?= APP_URL ?>/js/admin.js"></script>
</body>
</html>
