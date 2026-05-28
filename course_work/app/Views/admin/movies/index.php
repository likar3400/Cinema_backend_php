<div class="admin-header">
    <h1>🎬 Фільми (Афіша)</h1>
    <a href="<?= APP_URL ?>/admin/movies/create" class="btn btn-primary">+ Додати фільм</a>
</div>

<div class="filter-bar card mb-2">
    <input type="text" id="fSearch" placeholder="🔍 Назва або жанр..."
           style="padding:.45rem .8rem;border-radius:8px;background:var(--card);color:var(--text);border:1px solid var(--border);min-width:200px">
    <select id="fActive" style="padding:.45rem .8rem;border-radius:8px;background:var(--card);color:var(--text);border:1px solid var(--border)">
        <option value="">Всі</option>
        <option value="1">Активні</option>
        <option value="0">Неактивні</option>
    </select>
</div>

<div class="table-wrap">
    <table class="admin-table" id="moviesTable">
        <thead><tr><th>Постер</th><th>Назва</th><th>Жанр</th><th>Рейтинг</th><th>Тривалість</th><th>Активний</th><th></th></tr></thead>
        <tbody>
        <?php if (empty($movies)): ?>
            <tr><td colspan="7" class="empty-msg">Фільмів немає</td></tr>
        <?php else: foreach ($movies as $m): ?>
            <tr data-search='<?= strtolower(strip_tags($m["title"])." ".strip_tags($m["genre"])) ?>' data-active='<?= $m["is_active"] ?>'>
                <td>
                    <?php if ($m['poster']): ?>
                        <img src="<?= APP_URL . htmlspecialchars($m['poster']) ?>" alt=""
                             style="height:52px;width:36px;object-fit:cover;border-radius:4px">
                    <?php else: ?><span style="font-size:1.5rem">🎬</span><?php endif; ?>
                </td>
                <td><strong><?= htmlspecialchars($m['title']) ?></strong></td>
                <td><?= htmlspecialchars($m['genre']) ?></td>
                <td><?= $m['rating'] ? '⭐ '.$m['rating'] : '—' ?></td>
                <td><?= $m['duration'] ?> хв</td>
                <td><?= $m['is_active'] ? '✅' : '❌' ?></td>
                <td>
                    <a href="<?= APP_URL ?>/admin/movies/<?= $m['id'] ?>/edit" class="btn btn-outline btn-sm">✏ Ред.</a>
                    <button class="btn btn-danger btn-sm" onclick="delMovie(<?= $m['id'] ?>)">🗑</button>
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
    function filterMovies() {
        const s = document.getElementById('fSearch').value.toLowerCase();
        const a = document.getElementById('fActive').value;
        document.querySelectorAll('#moviesTable tbody tr[data-search]').forEach(row => {
            const ms = !s || row.dataset.search.includes(s);
            const ma = a === '' || row.dataset.active === a;
            row.style.display = (ms && ma) ? '' : 'none';
        });
    }
    document.getElementById('fSearch').addEventListener('input', filterMovies);
    document.getElementById('fActive').addEventListener('change', filterMovies);

    function delMovie(id) {
        if (!confirm('Видалити фільм?')) return;
        fetch(`<?= APP_URL ?>/admin/movies/${id}/delete`, {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
            body: JSON.stringify({_csrf: CSRF_TOKEN}),
        }).then(r => r.json()).then(d => {
            if (d.success) document.querySelector(`button[onclick="delMovie(${id})"]`).closest('tr').remove();
        });
    }
</script>
