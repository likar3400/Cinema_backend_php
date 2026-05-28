<div class="admin-toolbar"><a href="<?= APP_URL ?>/admin/news/create" class="btn btn-primary">➕ Нова новина</a></div>
<div class="table-wrap"><table class="admin-table">
<thead><tr><th>#</th><th>Заголовок</th><th>Активна</th><th>Дата</th><th>Дії</th></tr></thead>
<tbody>
<?php foreach ($items as $n): ?>
<tr id="row-<?= $n['id'] ?>">
  <td><?= $n['id'] ?></td>
  <td><?= htmlspecialchars($n['title']) ?></td>
  <td><span class="badge-status <?= $n['is_active']?'active':'inactive' ?>"><?= $n['is_active']?'Так':'Ні' ?></span></td>
  <td><?= date('d.m.Y',strtotime($n['created_at'])) ?></td>
  <td class="actions">
    <a href="<?= APP_URL ?>/admin/news/<?= $n['id'] ?>/edit" class="btn btn-sm btn-outline">✏</a>
    <button class="btn btn-sm btn-danger" onclick="ajaxDel('/admin/news/<?= $n['id'] ?>/delete',<?= $n['id'] ?>)">🗑</button>
  </td>
</tr>
<?php endforeach; ?>
<?php if(empty($items)): ?><tr><td colspan="5" class="empty-td">Новин немає.</td></tr><?php endif; ?>
</tbody></table></div>
<?php if($pages>1): ?><div class="pagination"><?php for($i=1;$i<=$pages;$i++): ?><a href="?page=<?= $i ?>" class="<?= $i===$page?'active':'' ?>"><?= $i ?></a><?php endfor; ?></div><?php endif; ?>
