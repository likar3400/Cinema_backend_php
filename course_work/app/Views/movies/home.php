<!-- АНІМОВАНИЙ БАНЕР КІНОТЕАТРУ -->
<div class="cinema-banner-wrap">
  <iframe
    src="<?= APP_URL ?>/images/cinemax_cinema_banner.html"
    class="cinema-banner-iframe"
    scrolling="no"
    frameborder="0"
    title="CineMax — Кіно починається тут"
  ></iframe>
  <div class="banner-overlay">
    <div class="banner-overlay-content">
      <h1>Кіно починається тут</h1>
      <p>Обирай фільм, бронюй місце, насолоджуйся — усе онлайн</p>
      <div class="hero-btns">
        <a href="<?= APP_URL ?>/schedule" class="btn btn-primary btn-lg">📅 Розклад сеансів</a>
        <a href="<?= APP_URL ?>/movies"   class="btn btn-outline btn-lg">🎬 Афіша</a>
      </div>
    </div>
  </div>
</div>

<!-- Найближчі сеанси -->
<section class="section">
  <h2 class="section-title">🎬 Найближчі сеанси</h2>
  <div class="sessions-grid" id="today-sessions">
    <?php if (empty($sessions)): ?>
      <p class="empty-msg">Найближчих сеансів немає. <a href="<?= APP_URL ?>/schedule">Дивіться повний розклад</a></p>
    <?php else: ?>
      <?php foreach (array_slice($sessions, 0, 6) as $s): ?>
      <div class="sess-card">
        <div class="sess-time"><?= date('d.m H:i', strtotime($s['starts_at'])) ?></div>
        <div class="sess-movie"><?= htmlspecialchars($s['movie_title']) ?></div>
        <div class="sess-meta"><?= htmlspecialchars($s['hall_name']) ?> · <?= $s['format'] ?></div>
        <div class="sess-price">
          <span class="price-std">Стд: <?= (int)$s['price'] ?> грн</span>
          <?php if ($s['price_vip'] > 0): ?>
          <span class="price-vip">VIP: <?= (int)$s['price_vip'] ?> грн</span>
          <?php endif; ?>
        </div>
        <?php if (\App\Core\Session::isLoggedIn()): ?>
          <a href="<?= APP_URL ?>/booking/<?= $s['id'] ?>" class="btn btn-primary btn-sm">Купити квиток</a>
        <?php else: ?>
          <a href="<?= APP_URL ?>/login" class="btn btn-outline btn-sm">Увійти</a>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
  <div class="text-center mt-3">
    <a href="<?= APP_URL ?>/schedule" class="btn btn-outline">Переглянути весь розклад →</a>
  </div>
</section>

<!-- Зараз у прокаті -->
<section class="section">
  <h2 class="section-title">🎞 Зараз у прокаті</h2>
  <div class="movies-grid">
    <?php foreach (array_slice($movies, 0, 4) as $m): ?>
    <div class="movie-card">
      <a href="<?= APP_URL ?>/movies/<?= $m['id'] ?>">
        <?php if ($m['poster']): ?>
          <img src="<?= APP_URL . htmlspecialchars($m['poster']) ?>" alt="<?= htmlspecialchars($m['title']) ?>" class="movie-poster">
        <?php else: ?>
          <div class="poster-placeholder">🎬</div>
        <?php endif; ?>
      </a>
      <div class="movie-body">
        <span class="age-badge"><?= htmlspecialchars($m['age_rating']) ?></span>
        <h3><a href="<?= APP_URL ?>/movies/<?= $m['id'] ?>"><?= htmlspecialchars($m['title']) ?></a></h3>
        <p class="movie-genre"><?= htmlspecialchars($m['genre']) ?></p>
        <div class="movie-meta">
          <span>⏱ <?= $m['duration'] ?> хв</span>
          <?php if ($m['rating']): ?><span>⭐ <?= $m['rating'] ?></span><?php endif; ?>
        </div>
        <a href="<?= APP_URL ?>/movies/<?= $m['id'] ?>" class="btn btn-outline btn-sm mt-1">Детальніше</a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="text-center mt-3"><a href="<?= APP_URL ?>/movies" class="btn btn-outline">Всі фільми →</a></div>
</section>

<!-- Попкорн банер -->
<section class="section popcorn-banner">
  <div class="pb-content">
    <div class="pb-emoji">🍿🥤🍫</div>
    <div>
      <h2>Попкорн та напої</h2>
      <p>Замовляй улюблені снеки онлайн. VIP-зона — сервіс до місця включено!</p>
      <a href="<?= APP_URL ?>/shop" class="btn btn-primary">Відкрити магазин</a>
    </div>
  </div>
</section>

<!-- Новини -->
<?php if (!empty($news)): ?>
<section class="section">
  <h2 class="section-title">📰 Новини</h2>
  <div class="news-grid">
    <?php foreach ($news as $n): ?>
    <a href="<?= APP_URL ?>/news/<?= $n['id'] ?>" class="news-card">
      <h3><?= htmlspecialchars($n['title']) ?></h3>
      <p><?= htmlspecialchars(mb_substr(strip_tags($n['body']),0,100)) ?>…</p>
      <span class="news-date"><?= date('d.m.Y',strtotime($n['created_at'])) ?></span>
    </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
