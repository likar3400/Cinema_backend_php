<div class="admin-header">
  <h1>💬 Відгуки</h1>
</div>
<div class="table-wrap">
  <table class="admin-table">
    <thead>
      <tr><th>ID</th><th>Фільм</th><th>Користувач</th><th>Рейтинг</th><th>Відгук</th><th>Дата</th><th></th></tr>
    </thead>
    <tbody>
      <?php if (empty($reviews)): ?>
      <tr><td colspan="7" class="empty-msg">Відгуків немає</td></tr>
      <?php else: ?>
      <?php foreach ($reviews as $r): ?>
      <tr>
        <td><?= $r['id'] ?></td>
        <td><?= htmlspecialchars($r['movie_title']) ?></td>
        <td><?= htmlspecialchars($r['user_name']) ?></td>
        <td>⭐ <?= $r['rating'] ?>/10</td>
          <td><?= htmlspecialchars(mb_substr($r['body'] ?? $r['text'] ?? '', 0, 60)) ?>…</td>
        <td><?= date('d.m.Y', strtotime($r['created_at'])) ?></td>
        <td>
          <button class="btn btn-danger btn-sm" onclick="delReview(<?= $r['id'] ?>)">Видалити</button>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>
<?php if ($pages > 1): ?>
<div class="pagination">
  <?php for ($i=1;$i<=$pages;$i++): ?>
  <a href="?page=<?= $i ?>" class="<?= $i===$page?'active':'' ?>"><?= $i ?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>
<script>
function delReview(id) {
  if (!confirm('Видалити відгук #'+id+'?')) return;
  fetch(`<?= APP_URL ?>/api/reviews/${id}/delete`, {
    method:'POST',
    headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
    body: JSON.stringify({_csrf: CSRF_TOKEN}),
  }).then(r=>r.json()).then(d=>{
    if (d.success) document.querySelector(`tr:has(button[onclick="delReview(${id})"])`).remove();
  });
}
</script>
