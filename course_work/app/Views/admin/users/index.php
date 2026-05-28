<div class="admin-header">
    <h1>👥 Користувачі</h1>
    <a href="<?= APP_URL ?>/admin/users/create" class="btn btn-primary">+ Додати</a>
</div>

<div class="filter-bar card mb-2">
    <input type="text" id="fSearch" placeholder="🔍 Ім'я або email..."
           style="padding:.45rem .8rem;border-radius:8px;background:var(--card);color:var(--text);border:1px solid var(--border);min-width:200px">
    <select id="fRole" style="padding:.45rem .8rem;border-radius:8px;background:var(--card);color:var(--text);border:1px solid var(--border)">
        <option value="">Всі ролі</option>
        <option value="admin">Адмін</option>
        <option value="user">Користувач</option>
    </select>
</div>

<div class="table-wrap">
    <table class="admin-table" id="usersTable">
        <thead><tr><th>ID</th><th>Ім'я</th><th>Email</th><th>Роль</th><th>Телефон</th><th>Дата реєстр.</th><th></th></tr></thead>
        <tbody>
        <?php if (empty($users)): ?>
            <tr><td colspan="7" class="empty-msg">Немає користувачів</td></tr>
        <?php else: foreach ($users as $u): ?>
            <tr data-search='<?= strtolower(strip_tags($u["name"]))." ".strtolower($u["email"]) ?>' data-role='<?= $u["role"] ?>'>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['name']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><span class="badge-role-<?= $u['role'] ?>"><?= $u['role'] === 'admin' ? '⚙ Адмін' : '👤 Юзер' ?></span></td>
                <td><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
                <td><?= date('d.m.Y', strtotime($u['created_at'])) ?></td>
                <td>
                    <a href="<?= APP_URL ?>/admin/users/<?= $u['id'] ?>/edit" class="btn btn-outline btn-sm">✏</a>
                    <?php if ((int)$u['id'] !== (int)\App\Core\Session::userId()): ?>
                        <button class="btn btn-danger btn-sm" onclick="delUser(<?= $u['id'] ?>)">🗑</button>
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
    function filterUsers() {
        const s = document.getElementById('fSearch').value.toLowerCase();
        const r = document.getElementById('fRole').value;
        document.querySelectorAll('#usersTable tbody tr[data-search]').forEach(row => {
            const ms = !s || row.dataset.search.includes(s);
            const mr = !r  || row.dataset.role === r;
            row.style.display = (ms && mr) ? '' : 'none';
        });
    }
    document.getElementById('fSearch').addEventListener('input', filterUsers);
    document.getElementById('fRole').addEventListener('change', filterUsers);

    function delUser(id) {
        if (!confirm('Видалити користувача #' + id + '?')) return;
        fetch(`<?= APP_URL ?>/admin/users/${id}/delete`, {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
            body: JSON.stringify({_csrf: CSRF_TOKEN}),
        }).then(r => r.json()).then(d => {
            if (d.success) document.querySelector(`button[onclick="delUser(${id})"]`).closest('tr').remove();
        });
    }
</script>
