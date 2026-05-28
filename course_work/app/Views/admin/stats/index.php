<div class="stats-controls card mb-3">
    <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap">
        <div class="fg"><label>Місяць:</label>
            <input type="month" id="monthPicker" value="<?= htmlspecialchars($month ?: date('Y-m')) ?>">
        </div>
        <button class="btn btn-primary btn-sm" onclick="loadStats()">🔄 Оновити</button>
        <span class="empty-msg" id="statsLoading" style="display:none">⏳ Завантаження…</span>
    </div>
</div>

<?php $m = $monthly; ?>

<!-- Картки -->
<div class="stats-grid">
    <div class="stat-card accent-gold">
        <div class="stat-icon">🎟</div>
        <div class="stat-num" id="sTotal"><?= $m['total_seats'] ?></div>
        <div class="stat-label">Квитків продано</div>
    </div>
    <div class="stat-card accent-vip">
        <div class="stat-icon">💺</div>
        <div class="stat-num" id="sVip"><?= $m['vip_seats'] ?></div>
        <div class="stat-label">VIP-місць</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🪑</div>
        <div class="stat-num" id="sStd"><?= $m['standard_seats'] ?></div>
        <div class="stat-label">Стандартних місць</div>
    </div>
    <div class="stat-card accent-green">
        <div class="stat-icon">💰</div>
        <div class="stat-num" id="sRev"><?= number_format($m['revenue'],0,'.',' ') ?> грн</div>
        <div class="stat-label">Дохід від квитків</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🍿</div>
        <div class="stat-num" id="sShop"><?= number_format($m['shop_revenue'],0,'.',' ') ?> грн</div>
        <div class="stat-label">Дохід від магазину</div>
    </div>
    <div class="stat-card accent-blue">
        <div class="stat-icon">💵</div>
        <div class="stat-num" id="sAll"><?= number_format($m['revenue']+$m['shop_revenue'],0,'.',' ') ?> грн</div>
        <div class="stat-label">Загальний дохід</div>
    </div>
</div>

<!-- VIP vs Стандарт + Щоденна динаміка -->
<div class="stats-row">
    <div class="card stats-half">
        <h3>💺 VIP vs Стандарт</h3>
        <div id="ratioWrap">
            <?php
            $total = $m['vip_seats'] + $m['standard_seats'];
            $vipPct = $total > 0 ? round($m['vip_seats']/$total*100) : 50;
            $stdPct = $total > 0 ? round($m['standard_seats']/$total*100) : 50;
            ?>
            <?php if ($total > 0): ?>
                <div class="ratio-bar">
                    <div class="rb-vip" style="width:<?= $vipPct ?>%">VIP <?= $vipPct ?>%</div>
                    <div class="rb-std" style="width:<?= $stdPct ?>%">Стд <?= $stdPct ?>%</div>
                </div>
                <div class="ratio-legend">
                    <span><span class="dot dot-vip"></span> VIP: <?= $m['vip_seats'] ?></span>
                    <span><span class="dot dot-std"></span> Стандарт: <?= $m['standard_seats'] ?></span>
                </div>
            <?php else: ?><p class="empty-msg">Немає даних.</p><?php endif; ?>
        </div>
    </div>

    <div class="card stats-half">
        <h3>📈 Щоденна динаміка</h3>
        <div id="chartWrap">
            <?php if (!empty($m['daily'])): ?>
                <div class="daily-chart">
                    <?php $maxCnt = max(array_column($m['daily'],'cnt') ?: [1]);
                    foreach ($m['daily'] as $day):
                        $hh = max(4, round((int)$day['cnt']/$maxCnt*120)); ?>
                        <div class="dc-col" title="<?= $day['day'] ?>: <?= $day['cnt'] ?> квитків">
                            <div class="dc-bar" style="height:<?= $hh ?>px"></div>
                            <div class="dc-lbl"><?= date('d',strtotime($day['day'])) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?><p class="empty-msg">Немає даних.</p><?php endif; ?>
        </div>
    </div>
</div>

<!-- Зали -->
<div class="card mt-3">
    <h3>🏛 Огляд залів — продажі</h3>
    <div class="table-wrap">
        <table class="admin-table">
            <thead><tr><th>Зала</th><th>Тип</th><th>Квитків</th><th>VIP</th><th>Стандарт</th><th>Дохід (грн)</th></tr></thead>
            <tbody id="hallsTbody">
            <?php if (!empty($m['halls'])): foreach ($m['halls'] as $h): ?>
                <tr>
                    <td><?= htmlspecialchars($h['hall_name']) ?></td>
                    <td><?= strtoupper($h['hall_type']) ?></td>
                    <td><strong><?= $h['total'] ?></strong></td>
                    <td><?= $h['vip'] ?></td>
                    <td><?= $h['standard'] ?></td>
                    <td><?= number_format($h['revenue'],0,'.',' ') ?></td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="6" class="empty-msg">Немає даних</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Топ фільмів -->
<div class="card mt-3">
    <h3>🏆 Топ фільмів за продажами</h3>
    <div class="table-wrap">
        <table class="admin-table">
            <thead><tr><th>#</th><th>Фільм</th><th>Квитків</th><th>Дохід (грн)</th></tr></thead>
            <tbody id="moviesTbody">
            <?php if (!empty($m['top_movies'])): foreach ($m['top_movies'] as $i => $mv): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><?= htmlspecialchars($mv['title']) ?></td>
                    <td><?= $mv['cnt'] ?></td>
                    <td><?= number_format($mv['revenue'],0,'.',' ') ?></td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="4" class="empty-msg">Немає даних</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Щоденна таблиця -->
<div class="card mt-3">
    <h3>📋 Детально за <span id="monthLabel"><?= htmlspecialchars($month ?: date('Y-m')) ?></span></h3>
    <div class="table-wrap">
        <table class="admin-table">
            <thead><tr><th>Дата</th><th>Квитків</th><th>Дохід (грн)</th></tr></thead>
            <tbody id="dailyTbody">
            <?php if (!empty($m['daily'])): foreach ($m['daily'] as $d): ?>
                <tr>
                    <td><?= date('d.m.Y',strtotime($d['day'])) ?></td>
                    <td><?= $d['cnt'] ?></td>
                    <td><?= number_format($d['rev'],0,'.',' ') ?></td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="3" class="empty-msg">Немає даних</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function loadStats() {
        const month = document.getElementById('monthPicker').value;
        if (!month) return;
        document.getElementById('statsLoading').style.display = 'inline';

        fetch(`<?= APP_URL ?>/api/admin/stats?month=${month}`,
            { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(d => {
                document.getElementById('statsLoading').style.display = 'none';
                document.getElementById('sTotal').textContent = d.total_seats;
                document.getElementById('sVip').textContent   = d.vip_seats;
                document.getElementById('sStd').textContent   = d.standard_seats;
                document.getElementById('sRev').textContent   = fmt(d.revenue) + ' грн';
                document.getElementById('sShop').textContent  = fmt(d.shop_revenue) + ' грн';
                document.getElementById('sAll').textContent   = fmt(+d.revenue + +d.shop_revenue) + ' грн';
                const tot    = (+d.vip_seats) + (+d.standard_seats);
                const vipPct = tot > 0 ? Math.round(d.vip_seats / tot * 100) : 50;
                const stdPct = tot > 0 ? Math.round(d.standard_seats / tot * 100) : 50;
                document.getElementById('ratioWrap').innerHTML = tot > 0
                    ? `<div class="ratio-bar">
           <div class="rb-vip" style="width:${vipPct}%">VIP ${vipPct}%</div>
           <div class="rb-std" style="width:${stdPct}%">Стд ${stdPct}%</div>
         </div>
         <div class="ratio-legend">
           <span><span class="dot dot-vip"></span> VIP: ${d.vip_seats}</span>
           <span><span class="dot dot-std"></span> Стандарт: ${d.standard_seats}</span>
         </div>`
                    : '<p class="empty-msg">Немає даних.</p>';

                if (d.daily && d.daily.length) {
                    const maxC = Math.max(...d.daily.map(x => +x.cnt), 1);
                    document.getElementById('chartWrap').innerHTML =
                        '<div class="daily-chart">' +
                        d.daily.map(x => {
                            const hh = Math.max(4, Math.round(x.cnt / maxC * 120));
                            const lbl = x.day.slice(8); // день місяця
                            return `<div class="dc-col" title="${x.day}: ${x.cnt} квитків">
            <div class="dc-bar" style="height:${hh}px"></div>
            <div class="dc-lbl">${lbl}</div>
          </div>`;
                        }).join('') + '</div>';
                } else {
                    document.getElementById('chartWrap').innerHTML = '<p class="empty-msg">Немає даних.</p>';
                }

                document.getElementById('hallsTbody').innerHTML = (d.halls && d.halls.length)
                    ? d.halls.map(h => `<tr>
          <td>${h.hall_name}</td>
          <td>${h.hall_type.toUpperCase()}</td>
          <td><strong>${h.total}</strong></td>
          <td>${h.vip}</td>
          <td>${h.standard}</td>
          <td>${fmt(h.revenue)}</td>
        </tr>`).join('')
                    : '<tr><td colspan="6" class="empty-msg">Немає даних</td></tr>';

                document.getElementById('moviesTbody').innerHTML = (d.top_movies && d.top_movies.length)
                    ? d.top_movies.map((mv, i) => `<tr>
          <td>${i+1}</td>
          <td>${mv.title}</td>
          <td>${mv.cnt}</td>
          <td>${fmt(mv.revenue)}</td>
        </tr>`).join('')
                    : '<tr><td colspan="4" class="empty-msg">Немає даних</td></tr>';

                document.getElementById('monthLabel').textContent = month;
                document.getElementById('dailyTbody').innerHTML = (d.daily && d.daily.length)
                    ? d.daily.map(x => `<tr>
          <td>${fmtDate(x.day)}</td>
          <td>${x.cnt}</td>
          <td>${fmt(x.rev)}</td>
        </tr>`).join('')
                    : '<tr><td colspan="3" class="empty-msg">Немає даних</td></tr>';
            })
            .catch(() => {
                document.getElementById('statsLoading').style.display = 'none';
            });
    }

    function fmt(n)     { return parseInt(n).toLocaleString('uk-UA'); }
    function fmtDate(s) { const [y,m,d]=s.split('-'); return `${d}.${m}.${y}`; }

    document.getElementById('monthPicker').addEventListener('change', loadStats);
</script>
