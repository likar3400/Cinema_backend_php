<?php
$avgFromReviews = !empty($reviews)
        ? round(array_sum(array_column($reviews, 'rating')) / count($reviews), 1)
        : null;
$displayRating = $avgFromReviews ?? $movie['rating'];
?>

<div class="movie-detail">
    <div class="detail-top">
        <?php if ($movie['poster']): ?>
            <img src="<?= APP_URL . htmlspecialchars($movie['poster']) ?>" alt="<?= htmlspecialchars($movie['title']) ?>" class="detail-poster">
        <?php else: ?>
            <div class="detail-poster-ph">🎬</div>
        <?php endif; ?>
        <div class="detail-info">
            <span class="age-badge age-badge-lg"><?= htmlspecialchars($movie['age_rating']) ?></span>
            <h1><?= htmlspecialchars($movie['title']) ?></h1>
            <p class="detail-genre">🎭 <?= htmlspecialchars($movie['genre']) ?></p>
            <p>
                ⏱ <?= $movie['duration'] ?> хвилин
                <?php if ($displayRating): ?>
                    &nbsp;⭐ <span id="movieRating"><?= $displayRating ?></span>/10
                    <?php if ($avgFromReviews && count($reviews) > 0): ?>
                        <small style="color:var(--muted)">
                            (<?= count($reviews) ?> відгук<?= count($reviews) > 1 ? 'ів' : '' ?>)
                        </small>
                    <?php endif; ?>
                <?php endif; ?>
            </p>
            <?php if ($movie['release_date']): ?>
                <p>📅 <?= date('d.m.Y', strtotime($movie['release_date'])) ?></p>
            <?php endif; ?>
            <p class="detail-desc"><?= nl2br(htmlspecialchars($movie['description'] ?? '')) ?></p>
            <?php if ($movie['trailer_url']): ?>
                <a href="<?= htmlspecialchars($movie['trailer_url']) ?>" class="btn btn-outline" target="_blank">▶ Трейлер</a>
            <?php endif; ?>
        </div>
    </div>

    <h2 class="section-title mt-3">Найближчі сеанси</h2>
    <div class="filter-bar">
        <label>Дата:</label>
        <input type="date" id="fDate" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>">
    </div>

    <div class="sessions-grid" id="sessContainer">
        <?php foreach ($sessions as $s): ?>
            <div class="sess-card">
                <div class="sess-time"><?= date('d.m H:i', strtotime($s['starts_at'])) ?></div>
                <div class="sess-meta"><?= htmlspecialchars($s['hall_name']) ?> · <?= $s['format'] ?> · <?= $s['language'] ?></div>
                <div class="sess-price">
                    <span class="price-std">Стд: <?= (int)$s['price'] ?> грн</span>
                    <?php if ($s['price_vip'] > 0): ?>
                        <span class="price-vip">VIP: <?= (int)$s['price_vip'] ?> грн</span>
                    <?php endif; ?>
                </div>
                <?php if (\App\Core\Session::isLoggedIn()): ?>
                    <a href="<?= APP_URL ?>/booking/<?= $s['id'] ?>" class="btn btn-primary btn-sm">Обрати місце</a>
                <?php else: ?>
                    <a href="<?= APP_URL ?>/login" class="btn btn-outline btn-sm">Увійти</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <?php if (empty($sessions)): ?>
            <p class="empty-msg">Найближчих сеансів немає.</p>
        <?php endif; ?>
    </div>
</div>
<section class="section reviews-section" id="reviews">
    <h2 class="section-title">💬 Відгуки</h2>

    <div id="reviewList">
        <?php if (empty($reviews)): ?>
            <p class="empty-msg" id="noReviews">Відгуків ще немає. Будьте першим!</p>
        <?php else: ?>
            <?php foreach ($reviews as $r): ?>
                <div class="review-card" id="rc-<?= $r['id'] ?>">
                    <div class="review-header">
                        <strong><?= htmlspecialchars($r['user_name']) ?></strong>
                        <span class="review-stars"><?= str_repeat('⭐', min((int)$r['rating'], 10)) ?></span>
                        <span class="review-rating"><?= $r['rating'] ?>/10</span>
                        <span class="review-date"><?= date('d.m.Y', strtotime($r['created_at'])) ?></span>
                        <?php if (\App\Core\Session::isAdmin()): ?>
                            <button class="btn-del-review" onclick="delReview(<?= $r['id'] ?>)" title="Видалити">🗑</button>
                        <?php endif; ?>
                    </div>
                    <p class="review-body"><?= nl2br(htmlspecialchars($r['body'] ?? '')) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if (\App\Core\Session::isLoggedIn() && !$alreadyReviewed): ?>
        <div class="review-form card mt-3" id="reviewFormWrap">
            <h3>Залишити відгук</h3>
            <div class="review-rating-picker">
                <label>Рейтинг (1–10):</label>
                <input type="number" id="rRating" min="1" max="10" value="8" style="width:70px">
            </div>
            <textarea id="rBody" rows="4" placeholder="Ваш відгук (мін. 10 символів)…"
                      style="width:100%;margin-top:.8rem;border-radius:8px;padding:.7rem;
             background:var(--card);color:var(--text);border:1px solid var(--border)"></textarea>
            <button class="btn btn-primary mt-2" onclick="submitReview()">✉ Надіслати відгук</button>
            <div id="reviewMsg" class="mt-1"></div>
        </div>
    <?php elseif (!\App\Core\Session::isLoggedIn()): ?>
        <p class="mt-2"><a href="<?= APP_URL ?>/login">Увійдіть</a>, щоб залишити відгук.</p>
    <?php elseif ($alreadyReviewed): ?>
        <p class="mt-2 empty-msg">✅ Ви вже залишили відгук на цей фільм.</p>
    <?php endif; ?>
</section>

<script>
    const MOVIE_ID   = <?= (int)$movie['id'] ?>;
    const IS_AUTH    = <?= \App\Core\Session::isLoggedIn() ? 'true' : 'false' ?>;
    const APP_URL_JS = '<?= APP_URL ?>';
    const CSRF       = document.querySelector('meta[name="csrf"]')?.getAttribute('content') || '';

    document.getElementById('fDate').addEventListener('change', function () {
        const c = document.getElementById('sessContainer');
        c.innerHTML = '<p class="loading">⏳ Завантаження…</p>';

        fetch(`${APP_URL_JS}/api/sessions?movie_id=${MOVIE_ID}&date=${this.value}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(r => r.json())
            .then(d => {
                if (!d.sessions || !d.sessions.length) {
                    c.innerHTML = '<p class="empty-msg">Сеансів у цей день немає.</p>';
                    return;
                }
                c.innerHTML = d.sessions.map(s => {
                    const t   = new Date(s.starts_at.replace(' ', 'T'));
                    const tf  = t.toLocaleDateString('uk-UA', {day:'2-digit', month:'2-digit'})
                        + ' ' + t.toLocaleTimeString('uk-UA', {hour:'2-digit', minute:'2-digit'});
                    const vip = s.price_vip > 0
                        ? `<span class="price-vip">VIP: ${parseInt(s.price_vip)} грн</span>` : '';
                    const btn = IS_AUTH
                        ? `<a href="${APP_URL_JS}/booking/${s.id}" class="btn btn-primary btn-sm">Обрати місце</a>`
                        : `<a href="${APP_URL_JS}/login" class="btn btn-outline btn-sm">Увійти</a>`;
                    return `<div class="sess-card">
        <div class="sess-time">${tf}</div>
        <div class="sess-meta">${s.hall_name} · ${s.format} · ${s.language}</div>
        <div class="sess-price">
          <span class="price-std">Стд: ${parseInt(s.price)} грн</span>${vip}
        </div>
        ${btn}
      </div>`;
                }).join('');
            })
            .catch(() => { c.innerHTML = '<p class="empty-msg">Помилка завантаження.</p>'; });
    });

    function submitReview() {
        const rating = parseInt(document.getElementById('rRating').value);
        const body   = document.getElementById('rBody').value.trim();
        const msg    = document.getElementById('reviewMsg');

        if (!rating || rating < 1 || rating > 10) { showMsg(msg, 'Рейтинг від 1 до 10', true); return; }
        if (body.length < 10)                      { showMsg(msg, 'Відгук мінімум 10 символів', true); return; }

        fetch(`${APP_URL_JS}/api/reviews/add`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ movie_id: MOVIE_ID, rating, body, _csrf: CSRF }),
        })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    // Додаємо картку без перезавантаження (201 Created)
                    const stars = '⭐'.repeat(d.rating);
                    const card  = `<div class="review-card" id="rc-${d.review_id}">
        <div class="review-header">
          <strong>${d.user_name}</strong>
          <span class="review-stars">${stars}</span>
          <span class="review-rating">${d.rating}/10</span>
          <span class="review-date">${d.created_at}</span>
        </div>
        <p class="review-body">${d.body.replace(/\n/g,'<br>')}</p>
      </div>`;

                    const noRev = document.getElementById('noReviews');
                    if (noRev) noRev.remove();
                    document.getElementById('reviewList').insertAdjacentHTML('afterbegin', card);
                    const ratingEl = document.getElementById('movieRating');
                    if (ratingEl) ratingEl.textContent = d.avg_rating;
                    document.getElementById('reviewFormWrap').innerHTML =
                        '<p class="empty-msg">✅ Дякуємо за відгук!</p>';
                } else {
                    showMsg(msg, d.message || 'Помилка', true);
                }
            })
            .catch(() => showMsg(msg, 'Мережева помилка', true));
    }
    function delReview(id) {
        if (!confirm('Видалити відгук?')) return;
        fetch(`${APP_URL_JS}/api/reviews/${id}/delete`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ _csrf: CSRF }),
        })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    const el = document.getElementById('rc-' + id);
                    if (el) el.remove();
                }
            });
    }

    function showMsg(el, txt, err) {
        el.textContent = txt;
        el.className   = err ? 'ajax-err' : 'ajax-ok';
    }
</script>
