<div class="admin-header">
  <h1>🏷 Категорії фільмів</h1>
</div>
<div class="card mb-3" style="max-width:420px">
  <h3>Додати категорію</h3>
  <form method="POST" action="<?= APP_URL ?>/admin/categories/create" style="display:flex;gap:.6rem;margin-top:.7rem">
    <input type="hidden" name="_csrf" value="<?= \App\Core\Session::csrfToken() ?>">
    <input type="text" name="name" placeholder="Назва категорії" required style="flex:1;padding:.5rem .8rem;border-radius:8px;background:var(--card);color:var(--text);border:1px solid var(--border)">
    <button type="submit" class="btn btn-primary">Додати</button>
  </form>
</div>
<div class="table-wrap">
  <table class="admin-table">
    <thead><tr><th>ID</th><th>Назва</th><th></th></tr></thead>
    <tbody>
      <?php if (empty($categories)): ?>
      <tr><td colspan="3" class="empty-msg">Категорій немає</td></tr>
      <?php else: ?>
      <?php foreach ($categories as $cat): ?>
      <tr>
        <td><?= $cat['id'] ?></td>
        <td><?= htmlspecialchars($cat['name']) ?></td>
        <td>
          <button class="btn btn-danger btn-sm" onclick="delCat(<?= $cat['id'] ?>, this)">Видалити</button>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>
<script>
function delCat(id, btn) {
  if (!confirm('Видалити категорію?')) return;
  fetch(`<?= APP_URL ?>/admin/categories/${id}/delete`, {
    method:'POST',
    headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
    body: JSON.stringify({_csrf: CSRF_TOKEN}),
  }).then(r=>r.json()).then(d=>{
    if (d.success) btn.closest('tr').remove();
  });
}
</script>
