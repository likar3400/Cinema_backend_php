<div class="admin-header">
  <h1>🎟 Бронювання</h1>
</div>

<div class="filter-bar card mb-2">
  <input type="text" id="fSearch" placeholder="🔍 Email або ім'я..." style="padding:.45rem .8rem;border-radius:8px;background:var(--card);color:var(--text);border:1px solid var(--border);min-width:200px">
  <select id="fStatus" style="padding:.45rem .8rem;border-radius:8px;background:var(--card);color:var(--text);border:1px solid var(--border)">
    <option value="">Всі статуси</option>
    <option value="confirmed">Підтверджено</option>
    <option value="cancelled">Скасовано</option>
  </select>
  <button class="btn btn-outline btn-sm" onclick="filterTable()">Фільтрувати</button>
</div>

<div class="table-wrap">
<table class="admin-table" id="bookTable">
  <thead>
    <tr><th>ID</th><th>Користувач</th><th>Фільм</th><th>Сеанс</th><th>Зала</th><th>Місце</th><th>Ціна</th><th>Код</th><th>Статус</th><th></th></tr>
  </thead>
  <tbody>
  <?php if (empty($bookings)): ?>
    <tr><td colspan="10" class="empty-msg">Бронювань немає</td></tr>
  <?php else: foreach ($bookings as $b): ?>
  <tr data-status="<?= $b['status'] ?>" data-user="<?= strtolower($b['user_name'].$b['user_email']) ?>">
    <td><?= $b['id'] ?></td>
    <td><?= htmlspecialchars($b['user_name']) ?><br><small><?= htmlspecialchars($b['user_email']) ?></small></td>
    <td><?= htmlspecialchars($b['movie_title']) ?></td>
    <td><?= date('d.m.Y H:i',strtotime($b['starts_at'])) ?></td>
    <td><?= htmlspecialchars($b['hall_name']) ?></td>
    <td>Р<?= $b['row_num'] ?> М<?= $b['col_num'] ?> <span class="badge-<?= $b['seat_type'] ?>"><?= $b['seat_type'] ?></span></td>
    <td><?= number_format($b['price_paid'],0) ?> грн</td>
    <td class="mono"><?= $b['ticket_code'] ?></td>
    <td><span class="status-<?= $b['status'] ?>"><?= $b['status']==='confirmed'?'✅ Підтверджено':'❌ Скасовано' ?></span></td>
    <td>
      <?php if ($b['status']==='confirmed'): ?>
      <button class="btn btn-danger btn-sm" onclick="cancelBooking(<?= $b['id'] ?>)">Скасувати</button>
      <?php endif; ?>
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
function filterTable() {
  const search = document.getElementById('fSearch').value.toLowerCase();
  const status = document.getElementById('fStatus').value;
  document.querySelectorAll('#bookTable tbody tr[data-status]').forEach(row => {
    const matchStatus = !status || row.dataset.status === status;
    const matchSearch = !search  || row.dataset.user.includes(search);
    row.style.display = (matchStatus && matchSearch) ? '' : 'none';
  });
}
document.getElementById('fSearch').addEventListener('input', filterTable);
document.getElementById('fStatus').addEventListener('change', filterTable);

function cancelBooking(id) {
  if (!confirm('Скасувати бронювання #'+id+'?')) return;
  fetch(`<?= APP_URL ?>/admin/bookings/${id}/cancel`,{
    method:'POST',
    headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
    body: JSON.stringify({_csrf:CSRF_TOKEN}),
  }).then(r=>r.json()).then(d=>{
    if (d.success) location.reload();
  });
}
</script>
