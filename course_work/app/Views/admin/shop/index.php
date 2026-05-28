<div class="admin-header">
  <h1>🍿 Магазин — товари</h1>
  <a href="<?= APP_URL ?>/admin/shop/create" class="btn btn-primary">+ Додати товар</a>
</div>

<div class="filter-bar card mb-2">
  <input type="text" id="fSearch" placeholder="🔍 Назва..." style="padding:.45rem .8rem;border-radius:8px;background:var(--card);color:var(--text);border:1px solid var(--border);min-width:180px">
  <select id="fCat" style="padding:.45rem .8rem;border-radius:8px;background:var(--card);color:var(--text);border:1px solid var(--border)">
    <option value="">Всі категорії</option>
    <option value="popcorn">Попкорн</option>
    <option value="drink">Напої</option>
    <option value="snack">Снеки</option>
    <option value="combo">Комбо</option>
  </select>
  <button class="btn btn-outline btn-sm" onclick="filterShop()">Фільтр</button>
</div>

<div class="table-wrap">
<table class="admin-table" id="shopTable">
  <thead><tr><th>Фото</th><th>Назва</th><th>Категорія</th><th>Ціна</th><th>Активний</th><th></th></tr></thead>
  <tbody>
  <?php if (empty($items)): ?>
    <tr><td colspan="6" class="empty-msg">Товарів немає</td></tr>
  <?php else: foreach ($items as $it): ?>
  <tr data-name="<?= strtolower(htmlspecialchars($it['name'])) ?>" data-cat="<?= $it['category'] ?>">
    <td>
      <?php if ($it['image']): ?>
      <img src="<?= APP_URL . htmlspecialchars($it['image']) ?>" alt="<?= htmlspecialchars($it['name']) ?>"
           style="height:48px;width:48px;object-fit:cover;border-radius:6px">
      <?php else: ?>
      <span style="font-size:1.8rem"><?= ['popcorn'=>'🍿','drink'=>'🥤','snack'=>'🍫','combo'=>'🎁'][$it['category']] ?? '📦' ?></span>
      <?php endif; ?>
    </td>
    <td><strong><?= htmlspecialchars($it['name']) ?></strong><br><small><?= htmlspecialchars(mb_substr($it['description']??'',0,50)) ?></small></td>
    <td><?= $it['category'] ?></td>
    <td><?= number_format($it['price'],0) ?> грн</td>
    <td><?= $it['is_active'] ? '✅' : '❌' ?></td>
    <td>
      <a href="<?= APP_URL ?>/admin/shop/<?= $it['id'] ?>/edit" class="btn btn-outline btn-sm">✏ Ред.</a>
      <button class="btn btn-danger btn-sm" onclick="delItem(<?= $it['id'] ?>)">🗑</button>
    </td>
  </tr>
  <?php endforeach; endif; ?>
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
function filterShop() {
  const s = document.getElementById('fSearch').value.toLowerCase();
  const c = document.getElementById('fCat').value;
  document.querySelectorAll('#shopTable tbody tr[data-name]').forEach(row => {
    const ms = !s || row.dataset.name.includes(s);
    const mc = !c || row.dataset.cat === c;
    row.style.display = ms && mc ? '' : 'none';
  });
}
document.getElementById('fSearch').addEventListener('input', filterShop);
document.getElementById('fCat').addEventListener('change', filterShop);

function delItem(id) {
  if (!confirm('Видалити товар?')) return;
  fetch(`<?= APP_URL ?>/admin/shop/${id}/delete`,{
    method:'POST',headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
    body:JSON.stringify({_csrf:CSRF_TOKEN}),
  }).then(r=>r.json()).then(d=>{ if(d.success) document.querySelector(`tr[onclick],button[onclick="delItem(${id})"]`).closest('tr').remove(); location.reload(); });
}
</script>
