<div class="admin-toolbar">
    <a href="<?= APP_URL ?>/admin/halls/create" class="btn btn-primary">➕ Додати зал</a>
</div>

<div class="table-wrap">
    <table class="admin-table">
        <thead>
        <tr><th>#</th><th>Назва</th><th>Тип</th><th>Рядів</th><th>Місць/ряд</th><th>Всього</th><th>Дії</th></tr>
        </thead>
        <tbody>
        <?php foreach ($halls as $h): ?>
            <tr id="row-<?= $h['id'] ?>">
                <td><?= $h['id'] ?></td>
                <td><?= htmlspecialchars($h['name']) ?></td>
                <td><span class="badge-type"><?= strtoupper($h['type']) ?></span></td>
                <td><?= $h['row_count'] ?></td>
                <td><?= $h['col_count'] ?></td>
                <td><?= $h['row_count'] * $h['col_count'] ?></td>
                <td class="actions">
                    <a href="<?= APP_URL ?>/admin/halls/<?= $h['id'] ?>/edit" class="btn btn-sm btn-outline">✏</a>
                    <button class="btn btn-sm btn-danger" onclick="delHall(<?= $h['id'] ?>)">🗑</button>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($halls)): ?>
            <tr><td colspan="7" class="empty-td">Залів немає.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    function delHall(id) {
        if (!confirm('Видалити зал #' + id + '? Всі місця також будуть видалені.')) return;
        fetch(`<?= APP_URL ?>/admin/halls/${id}/delete`, {
            method:  'POST',
            headers: {
                'Content-Type':     'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ _csrf: CSRF_TOKEN }),
        })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    const row = document.getElementById('row-' + id);
                    if (row) row.remove();
                } else {
                    alert('Помилка видалення: ' + (d.message || ''));
                }
            })
            .catch(err => alert('Помилка з\'єднання: ' + err));
    }
</script>
