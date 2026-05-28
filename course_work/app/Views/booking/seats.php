<div class="seats-page">
    <div class="sess-info card mb-3">
        <?php if (!empty($session['poster'])): ?>
            <img src="<?= APP_URL . htmlspecialchars($session['poster']) ?>" alt="<?= htmlspecialchars($session['movie_title']) ?>" class="sess-info-poster">
        <?php endif; ?>
        <div>
            <h2><?= htmlspecialchars($session['movie_title']) ?></h2>
            <p>📅 <?= date('d.m.Y H:i',strtotime($session['starts_at'])) ?> &nbsp;·&nbsp;
                🏛 <?= htmlspecialchars($session['hall_name']) ?> &nbsp;·&nbsp;
                📽 <?= $session['format'] ?> &nbsp;·&nbsp;
                🔊 <?= $session['language'] ?></p>
            <p>💰 Стд: <strong><?= (int)$session['price'] ?> грн</strong>
                <?php if ($session['price_vip']>0): ?>&nbsp;VIP: <strong><?= (int)$session['price_vip'] ?> грн</strong><?php endif; ?></p>
        </div>
    </div>

    <div class="seat-legend">
        <span class="leg free">⬜ Вільне</span>
        <span class="leg vip">🟡 VIP</span>
        <span class="leg booked">🟥 Зайняте</span>
        <span class="leg selected">🟢 Обране</span>
    </div>

    <div class="screen-bar">🎬 ЕКРАН</div>

    <?php
    $grouped  = [];
    foreach ($seats as $seat) $grouped[$seat['row_num']][] = $seat;
    $bookedSet = array_flip($booked);
    ?>
    <div class="seatmap" id="seatmap">
        <?php foreach ($grouped as $rowNum => $rowSeats): ?>
            <div class="seat-row">
                <span class="row-num">Ряд <?= $rowNum ?></span>
                <?php foreach ($rowSeats as $seat):
                    $isBooked   = isset($bookedSet[$seat['id']]);
                    $isVip      = $seat['type'] === 'vip';
                    $isDisabled = $seat['type'] === 'disabled';
                    $cls   = $isDisabled ? 'seat seat-disabled'
                            : ($isBooked  ? 'seat seat-booked'
                                    : ($isVip     ? 'seat seat-vip' : 'seat'));
                    $price = ($isVip && $session['price_vip'] > 0)
                            ? (int)$session['price_vip']
                            : (int)$session['price'];
                    ?>
                    <button class="<?= $cls ?>"
                            <?= ($isBooked || $isDisabled) ? 'disabled' : '' ?>
                            data-id="<?= $seat['id'] ?>"
                            data-price="<?= $price ?>"
                            data-row="<?= $seat['row_num'] ?>"
                            data-col="<?= $seat['col_num'] ?>"
                            data-type="<?= $seat['type'] ?>"
                            title="Ряд <?= $seat['row_num'] ?>, Місце <?= $seat['col_num'] ?><?= $isVip?' (VIP)':'' ?><?= $isBooked?' — Зайняте':'' ?>"
                            onclick="selectSeat(this)"><?= $seat['col_num'] ?></button>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="booking-summary card mt-3" id="bookSummary" style="display:none">
        <h3>Обрані місця</h3>
        <p id="sumText"></p>
        <div id="bookMsg"></div>

        <div class="payment-box mt-2">
            <h4>💳 Тестова оплата</h4>
            <div class="form-row">
                <div class="fg">
                    <label>Номер картки</label>
                    <input type="text" id="cardNum" placeholder="4242 4242 4242 4242"
                           maxlength="19" oninput="fmtCard(this)" style="font-family:monospace">
                </div>
                <div class="fg">
                    <label>MM/YY</label>
                    <input type="text" id="cardExp" placeholder="12/28" maxlength="5" oninput="fmtExp(this)">
                </div>
                <div class="fg">
                    <label>CVV</label>
                    <input type="text" id="cardCvv" placeholder="123" maxlength="3">
                </div>
            </div>
        </div>

        <button class="btn btn-primary mt-2" id="btnBook" onclick="confirmBooking()">
            ✅ Підтвердити та оплатити
        </button>
        <button class="btn btn-outline mt-1" onclick="clearSeat()">Скасувати вибір</button>
    </div>

    <?php if (!empty($_GET['booked'])): ?>
        <div class="success-banner">
            <div class="sb-icon">🎉</div>
            <h2>Квиток заброньовано!</h2>
            <p>Код квитка: <span class="ticket-code"><?= htmlspecialchars($_GET['code'] ?? '') ?></span></p>
            <p>Ціна: <strong><?= htmlspecialchars($_GET['price'] ?? '') ?> грн</strong></p>
            <a href="<?= APP_URL ?>/profile/bookings" class="btn btn-primary">Мої квитки</a>
            <a href="<?= APP_URL ?>/shop" class="btn btn-outline">🍿 Замовити снеки</a>
        </div>
    <?php endif; ?>
</div>

<script>
    var APP_URL_JS = '<?= APP_URL ?>';
    var SESSION_ID = <?= (int)$session['id'] ?>;
    var CSRF       = document.querySelector('meta[name="csrf"]')?.getAttribute('content') || '';

    let selectedSeats = [];

    function selectSeat(btn) {
        const id = parseInt(btn.dataset.id);

        if (btn.classList.contains('seat-selected')) {
            btn.classList.remove('seat-selected');
            btn.classList.add(btn.dataset.type === 'vip' ? 'seat-vip' : 'seat');
            selectedSeats = selectedSeats.filter(s => s.id !== id);
        } else {
            btn.classList.remove('seat', 'seat-vip');
            btn.classList.add('seat-selected');
            selectedSeats.push({
                id:    id,
                price: parseInt(btn.dataset.price),
                row:   btn.dataset.row,
                col:   btn.dataset.col,
                type:  btn.dataset.type
            });
        }
        updateSummary();
    }

    function updateSummary() {
        const summary = document.getElementById('bookSummary');
        if (!selectedSeats.length) { summary.style.display = 'none'; return; }

        const total = selectedSeats.reduce((s, x) => s + x.price, 0);
        const list  = selectedSeats.map(s =>
            `Ряд ${s.row}, Місце ${s.col}${s.type === 'vip' ? ' 🌟' : ''} — ${s.price} грн`
        ).join('<br>');

        document.getElementById('sumText').innerHTML =
            `${list}<hr style="margin:.5rem 0;border-color:var(--border)"><strong>Разом: ${total} грн</strong>`;
        summary.style.display = 'block';
        summary.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function clearSeat() {
        selectedSeats = [];
        document.getElementById('bookSummary').style.display = 'none';
        document.getElementById('bookMsg').innerHTML = '';
        document.querySelectorAll('.seat-selected').forEach(b => {
            b.classList.remove('seat-selected');
            b.classList.add(b.dataset.type === 'vip' ? 'seat-vip' : 'seat');
        });
    }

    function fmtCard(el) {
        let v = el.value.replace(/\D/g, '').substring(0, 16);
        el.value = v.replace(/(.{4})/g, '$1 ').trim();
    }
    function fmtExp(el) {
        let v = el.value.replace(/\D/g, '').substring(0, 4);
        if (v.length > 2) v = v.slice(0, 2) + '/' + v.slice(2);
        el.value = v;
    }

    function confirmBooking() {
        if (!selectedSeats.length) return;

        const card = document.getElementById('cardNum').value.replace(/\s/g, '');
        const exp  = document.getElementById('cardExp').value;
        const cvv  = document.getElementById('cardCvv').value;
        const msg  = document.getElementById('bookMsg');

        if (card.length < 16)             { showMsg(msg, '❌ Введіть повний номер картки', true); return; }
        if (!/^\d{2}\/\d{2}$/.test(exp)) { showMsg(msg, '❌ Введіть дату MM/YY', true);          return; }
        if (cvv.length < 3)               { showMsg(msg, '❌ Введіть CVV', true);                 return; }

        const btnBook = document.getElementById('btnBook');
        btnBook.disabled    = true;
        btnBook.textContent = '⏳ Обробка оплати…';

        setTimeout(() => {
            fetch(`${APP_URL_JS}/api/booking`, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({
                    session_id: SESSION_ID,
                    seats:      selectedSeats.map(s => s.id),
                    _csrf:      CSRF
                }),
            })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        window.location.href =
                            `${APP_URL_JS}/booking/${SESSION_ID}?booked=1&code=${encodeURIComponent(d.ticket_code)}&price=${d.price}`;
                    } else {
                        showMsg(msg, '❌ ' + (d.message || 'Помилка бронювання'), true);
                        btnBook.disabled    = false;
                        btnBook.textContent = '✅ Підтвердити та оплатити';

                        if (d.message && d.message.includes('вже')) {
                            selectedSeats.forEach(s => {
                                const b = document.querySelector(`[data-id="${s.id}"]`);
                                if (b) {
                                    b.classList.add('seat-booked');
                                    b.classList.remove('seat', 'seat-vip', 'seat-selected');
                                    b.disabled = true;
                                }
                            });
                            clearSeat();
                        }
                    }
                })
                .catch(() => {
                    showMsg(msg, '❌ Помилка мережі. Спробуйте ще раз.', true);
                    btnBook.disabled    = false;
                    btnBook.textContent = '✅ Підтвердити та оплатити';
                });
        }, 900);
    }

    function showMsg(el, txt, err) {
        el.textContent = txt;
        el.className   = err ? 'ajax-err' : 'ajax-ok';
    }
</script>