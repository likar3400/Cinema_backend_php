<div class="auth-page">
  <div class="auth-card auth-card-wide">
    <div class="auth-logo">🎬</div>
    <h1>Реєстрація</h1>
    <p class="auth-sub">Приєднуйся до <?= APP_NAME ?> — купуй квитки онлайн!</p>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-error"><?php foreach($errors as $e): ?><p><?= htmlspecialchars($e) ?></p><?php endforeach; ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= APP_URL ?>/register" novalidate id="regForm">
      <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= \App\Core\Session::csrfToken() ?>">

      <div class="form-row-2">
        <div class="form-group">
          <label>Повне ім'я *</label>
          <input type="text" name="name" required minlength="2"
                 value="<?= $old['name'] ?? '' ?>" placeholder="Іван Петренко" autofocus>
          <span class="fhint" id="hName"></span>
        </div>
        <div class="form-group">
          <label>Телефон</label>
          <input type="tel" name="phone" value="<?= $old['phone'] ?? '' ?>" placeholder="+380 XX XXX XX XX">
        </div>
      </div>

      <div class="form-group">
        <label>Email *</label>
        <input type="email" name="email" required
               value="<?= $old['email'] ?? '' ?>" placeholder="your@email.com" id="rEmail">
        <span class="fhint" id="hEmail"></span>
      </div>

      <div class="form-row-2">
        <div class="form-group">
          <label>Пароль * <small>(мін. 8 символів)</small></label>
          <div class="pwd-wrap">
            <input type="password" name="password" required minlength="8" placeholder="••••••••" id="rPwd">
            <button type="button" class="eye-btn" onclick="togglePwd('rPwd',this)" tabindex="-1">👁</button>
          </div>
          <div class="pwd-bar"><div class="pwd-fill" id="pwdFill"></div></div>
          <span class="fhint" id="hPwd"></span>
        </div>
        <div class="form-group">
          <label>Повторіть пароль *</label>
          <div class="pwd-wrap">
            <input type="password" name="confirm" required placeholder="••••••••" id="rConf">
            <button type="button" class="eye-btn" onclick="togglePwd('rConf',this)" tabindex="-1">👁</button>
          </div>
          <span class="fhint" id="hConf"></span>
        </div>
      </div>

      <div class="reg-benefits">
        <div class="rb-item">✅ Швидке бронювання місць</div>
        <div class="rb-item">🎟 Електронні квитки</div>
        <div class="rb-item">🍿 Замовлення попкорну онлайн</div>
      </div>

      <button type="submit" class="btn btn-primary btn-full" id="btnReg">Зареєструватися</button>
      <div id="regMsg"></div>
    </form>
    <p class="auth-switch">Вже маєте акаунт? <a href="<?= APP_URL ?>/login">Увійти</a></p>
  </div>
</div>

<script>
document.querySelector('[name="name"]').addEventListener('blur', function () {
  const h = document.getElementById('hName');
  const ok = this.value.trim().length >= 2;
  h.textContent = ok ? '✅ Добре' : '❌ Мінімум 2 символи';
  h.style.color = ok ? '#22c55e' : '#ef4444';
});

document.getElementById('rEmail').addEventListener('blur', function () {
  const ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value);
  const h = document.getElementById('hEmail');
  h.textContent = ok ? '✅ Формат правильний' : '❌ Невірний формат email';
  h.style.color = ok ? '#22c55e' : '#ef4444';
});

document.getElementById('rPwd').addEventListener('input', function () {
  const v=this.value, h=document.getElementById('hPwd'), f=document.getElementById('pwdFill');
  let score=0;
  if (v.length>=8) score++; if (/[A-Z]/.test(v)) score++; if (/[0-9]/.test(v)) score++; if (/[^A-Za-z0-9]/.test(v)) score++;
  const labels=['','Слабкий','Середній','Добрий','Відмінний'];
  const colors=['','#ef4444','#f59e0b','#3b82f6','#22c55e'];
  const widths=['0%','25%','50%','75%','100%'];
  h.textContent=v?labels[score]||'Слабкий':''; h.style.color=colors[score]||'#ef4444';
  f.style.width=widths[score]||'0%'; f.style.background=colors[score]||'#ef4444';
});

document.getElementById('rConf').addEventListener('input', function () {
  const ok=this.value===document.getElementById('rPwd').value;
  const h=document.getElementById('hConf');
  h.textContent=this.value?(ok?'✅ Паролі збігаються':'❌ Паролі не збігаються'):'';
  h.style.color=ok?'#22c55e':'#ef4444';
});

function togglePwd(id, btn) {
  const input = document.getElementById(id);
  input.type = input.type === 'password' ? 'text' : 'password';
  btn.textContent = input.type === 'password' ? '👁' : '🙈';
}
</script>
