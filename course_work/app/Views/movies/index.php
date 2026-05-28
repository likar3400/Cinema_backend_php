<div class="page-header"><h1>🎬 Афіша</h1></div>
<div class="movies-grid">
  <?php foreach ($movies as $m): ?>
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
        <?php if (!empty($m['release_date'])): ?><span>📅 <?= date('Y', strtotime($m['release_date'])) ?></span><?php endif; ?>
      </div>
      <p class="movie-desc"><?= htmlspecialchars(mb_substr($m['description'] ?? '', 0, 100)) ?>…</p>
      <a href="<?= APP_URL ?>/movies/<?= $m['id'] ?>" class="btn btn-outline btn-sm">Детальніше</a>
    </div>
  </div>
  <?php endforeach; ?>
  <?php if (empty($movies)): ?><p class="empty-msg">Фільмів немає.</p><?php endif; ?>
</div>
<?php if ($pages > 1): ?>
<div class="pagination">
  <?php for ($i=1;$i<=$pages;$i++): ?>
  <a href="?page=<?= $i ?>" class="<?= $i===$page?'active':'' ?>"><?= $i ?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>
