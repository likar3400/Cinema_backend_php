<div class="page-header"><h1>📰 Новини</h1></div>
<div class="news-grid news-grid-full">
  <?php foreach ($items as $n): ?>
  <a href="<?= APP_URL ?>/news/<?= $n['id'] ?>" class="news-card">
    <h3><?= htmlspecialchars($n['title']) ?></h3>
    <p><?= htmlspecialchars(mb_substr(strip_tags($n['body']),0,150)) ?>…</p>
    <span class="news-date"><?= date('d.m.Y',strtotime($n['created_at'])) ?></span>
  </a>
  <?php endforeach; ?>
  <?php if(empty($items)): ?><p class="empty-msg">Новин немає.</p><?php endif; ?>
</div>
<?php if($pages>1): ?><div class="pagination"><?php for($i=1;$i<=$pages;$i++): ?><a href="?page=<?= $i ?>" class="<?= $i===$page?'active':'' ?>"><?= $i ?></a><?php endfor; ?></div><?php endif; ?>
