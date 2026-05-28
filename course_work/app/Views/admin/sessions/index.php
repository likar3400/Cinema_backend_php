<div class="admin-toolbar"><a href="<?= APP_URL ?>/admin/sessions/create" class="btn btn-primary">➕ Додати сеанс</a></div>
<div class="table-wrap"><table class="admin-table">
<thead><tr><th>#</th><th>Фільм</th><th>Зала</th><th>Початок</th><th>Кінець</th><th>Ціна</th><th>VIP</th><th>Формат</th><th>Дії</th></tr></thead>
<tbody>
<?php foreach ($sessions as $s): ?>
<tr id="row-<?= $s['id'] ?>">
  <td><?= $s['id'] ?></td>
  <td><?= htmlspecialchars($s['movie_title']) ?></td>
  <td><?= htmlspecialchars($s['hall_name']) ?></td>
  <td><?= date('d.m.Y H:i',strtotime($s['starts_at'])) ?></td>
  <td><?= date('H:i',strtotime($s['ends_at'])) ?></td>
  <td><?= (int)$s['price'] ?> грн</td>
  <td><?= $s['price_vip']>0?(int)$s['price_vip'].' грн':'—' ?></td>
  <td><?= $s['format'] ?></td>
  <td class="actions">
    <a href="<?= APP_URL ?>/admin/sessions/<?= $s['id'] ?>/edit" class="btn btn-sm btn-outline">✏</a>
    <button class="btn btn-sm btn-danger" onclick="ajaxDel('/admin/sessions/<?= $s['id'] ?>/delete',<?= $s['id'] ?>)">🗑</button>
  </td>
</tr>
<?php endforeach; ?>
<?php if(empty($sessions)): ?><tr><td colspan="9" class="empty-td">Сеансів немає.</td></tr><?php endif; ?>
</tbody></table></div>
<?php if($pages>1): ?><div class="pagination"><?php for($i=1;$i<=$pages;$i++): ?><a href="?page=<?= $i ?>" class="<?= $i===$page?'active':'' ?>"><?= $i ?></a><?php endfor; ?></div><?php endif; ?>
