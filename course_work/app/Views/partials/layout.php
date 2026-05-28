<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/css/style.css">
    <meta name="csrf" content="<?= \App\Core\Session::csrfToken() ?>">
</head>
<body>

<nav class="navbar">
    <div class="container nav-inner">
        <a href="<?= APP_URL ?>/" class="brand">🎬 <?= APP_NAME ?></a>
        <ul class="nav-links" id="navLinks">
            <li><a href="<?= APP_URL ?>/"         <?= rtrim($_SERVER['REQUEST_URI'],'/')==''?'class="active"':'' ?>>Головна</a></li>
            <li><a href="<?= APP_URL ?>/movies"   <?= str_starts_with($_SERVER['REQUEST_URI'],'/'.ltrim(parse_url(APP_URL,PHP_URL_PATH),'/').'/movies')?'class="active"':'' ?>>Афіша</a></li>
            <li><a href="<?= APP_URL ?>/schedule" <?= str_contains($_SERVER['REQUEST_URI'],'/schedule')?'class="active"':'' ?>>Розклад</a></li>
            <li><a href="<?= APP_URL ?>/news"     <?= str_contains($_SERVER['REQUEST_URI'],'/news')?'class="active"':'' ?>>Новини</a></li>
            <li><a href="<?= APP_URL ?>/shop"     <?= str_contains($_SERVER['REQUEST_URI'],'/shop')?'class="active"':'' ?>>🍿 Магазин</a></li>
            <?php if (\App\Core\Session::isLoggedIn()):
                $cartCount = 0;
                try { $cartCount = (new \App\Models\ShopModel())->cartCount(\App\Core\Session::userId()); } catch (\Throwable $e) {}
                ?>
                <li>
                    <a href="<?= APP_URL ?>/cart" class="nav-cart-link">
                        🛒 Кошик
                        <span class="cart-badge" id="cartBadge" <?= $cartCount > 0 ? '' : 'style="display:none"' ?>><?= $cartCount ?></span>
                    </a>
                </li>
                <li><a href="<?= APP_URL ?>/profile/bookings">🎟 Квитки</a></li>
                <li><a href="<?= APP_URL ?>/shop/orders">📦 Замовлення</a></li>
                <?php if (\App\Core\Session::isAdmin()): ?>
                <li><a href="<?= APP_URL ?>/admin" class="nav-admin">⚙ Адмін</a></li>
            <?php endif; ?>
                <li class="nav-user-name">👤 <?= htmlspecialchars(\App\Core\Session::userName()) ?></li>
                <li><a href="<?= APP_URL ?>/logout" class="nav-logout">Вийти</a></li>
            <?php else: ?>
                <li><a href="<?= APP_URL ?>/login">Вхід</a></li>
                <li><a href="<?= APP_URL ?>/register" class="nav-register">Реєстрація</a></li>
            <?php endif; ?>
        </ul>
        <button class="burger" id="burger" aria-label="Меню">☰</button>
    </div>
</nav>

<?php foreach (['success'=>'flash-success','error'=>'flash-error'] as $k=>$cls):
    $msg = \App\Core\Session::getFlash($k); if (!$msg) continue; ?>
    <div class="flash <?= $cls ?>"><?= htmlspecialchars($msg) ?> <button class="flash-x" onclick="this.parentElement.remove()">×</button></div>
<?php endforeach; ?>

<main class="main">
    <div class="container">
        <?= $content ?? '' ?>
    </div>
</main>

<footer class="footer">
    <div class="container footer-inner">
        <div class="footer-brand">🎬 <?= APP_NAME ?></div>
        <div class="footer-links">
            <a href="<?= APP_URL ?>/movies">Афіша</a>
            <a href="<?= APP_URL ?>/schedule">Розклад</a>
            <a href="<?= APP_URL ?>/shop">Магазин</a>
            <a href="<?= APP_URL ?>/news">Новини</a>
        </div>
        <div class="footer-copy">© <?= date('Y') ?> <?= APP_NAME ?>. Всі права захищено.</div>
    </div>
</footer>
<script>
    const CSRF_TOKEN = '<?= \App\Core\Session::csrfToken() ?>';
    const APP_URL    = '<?= APP_URL ?>';
</script>
<script src="<?= APP_URL ?>/js/app.js"></script>
</body>
</html>