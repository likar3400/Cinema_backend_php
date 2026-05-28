<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">🎬</div>
    <h1>Вхід до <?= APP_NAME ?></h1>
    <?php if (!empty($errors)): ?>
    <div class="alert alert-error"><?php foreach($errors as $e): ?><p><?= htmlspecialchars($e) ?></p><?php endforeach; ?></div>
    <?php endif; ?>
    <form method="POST" action="<?= APP_URL ?>/login" novalidate id="loginForm">
      <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= \App\Core\Session::csrfToken() ?>">
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required value="<?= $old['email'] ?? '' ?>" placeholder="your@email.com" autofocus id="loginEmail">
        <span class="fhint" id="hLoginEmail"></span>
      </div>
      <div class="form-group">
        <label>Пароль</label>
        <div class="pwd-wrap">
          <input type="password" name="password" required placeholder="••••••••" id="loginPwd">
          <button type="button" class="eye-btn" onclick="togglePwd('loginPwd',this)" tabindex="-1">👁</button>
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-full" id="btnLogin">Увійти</button>
      <div id="loginMsg"></div>
    </form>
    <p class="auth-switch">Немає акаунту? <a href="<?= APP_URL ?>/register">Зареєструватися</a></p>
  </div>
</div>

<script>
// Валідація email при blur
document.getElementById('loginEmail').addEventListener('blur', function () {
  const ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value);
  const h = document.getElementById('hLoginEmail');
  if (this.value) {
    h.textContent = ok ? '✅ Формат правильний' : '❌ Невірний формат email';
    h.style.color  = ok ? '#22c55e' : '#ef4444';
  }
});

// AJAX логін (асинхронний)
document.getElementById('loginForm').addEventListener('submit', async function (e) {
  e.preventDefault();
  const btn = document.getElementById('btnLogin');
  const msg = document.getElementById('loginMsg');
  btn.disabled = true;
  btn.textContent = '⏳ Вхід...';
  msg.innerHTML = '';

  const fd = new FormData(this);

  try {
    const res = await fetch('<?= APP_URL ?>/api/login', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: fd,
    });

    if (res.status === 409) {
      msg.innerHTML = '<div class="ajax-err">⚠️ Конфлікт сесії. Спробуйте ще раз.</div>';
      btn.disabled = false; btn.textContent = 'Увійти'; return;
    }
    if (res.status === 403) {
      msg.innerHTML = '<div class="ajax-err">🚫 Доступ заборонено (CSRF).</div>';
      btn.disabled = false; btn.textContent = 'Увійти'; return;
    }
    if (res.status === 429) {
      msg.innerHTML = '<div class="ajax-err">⏱ Забагато спроб. Зачекайте хвилину.</div>';
      btn.disabled = false; btn.textContent = 'Увійти'; return;
    }

    const data = await res.json();

    if (data.success) {
      msg.innerHTML = '<div class="ajax-ok">✅ Вхід успішний! Перенаправлення...</div>';
      setTimeout(() => { window.location.href = data.redirect; }, 600);
    } else {
      msg.innerHTML = `<div class="ajax-err">❌ ${data.message || 'Невірний email або пароль.'}</div>`;
      btn.disabled = false; btn.textContent = 'Увійти';
    }
  } catch (err) {
    msg.innerHTML = '<div class="ajax-err">🔌 Помилка з\'єднання. Перевірте мережу.</div>';
    btn.disabled = false; btn.textContent = 'Увійти';
  }
});

function togglePwd(id, btn) {
  const input = document.getElementById(id);
  input.type = input.type === 'password' ? 'text' : 'password';
  btn.textContent = input.type === 'password' ? '👁' : '🙈';
}
</script>
