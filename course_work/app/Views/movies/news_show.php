<div class="news-detail">
  <a href="<?= APP_URL ?>/news" class="btn btn-outline btn-sm">← Всі новини</a>
  <h1><?= htmlspecialchars($item['title']) ?></h1>
  <p class="news-meta">📅 <?= date('d.m.Y H:i',strtotime($item['created_at'])) ?></p>
  <div class="news-body"><?= nl2br(htmlspecialchars($item['body'])) ?></div>
</div>
