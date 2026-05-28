<a href="<?= APP_URL ?>/admin/users" class="btn btn-outline btn-sm">← Назад</a>
<?php if(!empty($error)): ?><div class="alert alert-error mt-2"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="POST" action="<?= APP_URL ?>/admin/users/<?= $user['id'] ?>/edit" class="admin-form">
  <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= \App\Core\Session::csrfToken() ?>">
  <div class="form-row">
    <div class="form-group"><label>Ім'я *</label><input type="text" name="name" required value="<?= htmlspecialchars($user['name']) ?>"></div>
    <div class="form-group"><label>Email *</label><input type="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>"></div>
  </div>
  <div class="form-row">
    <div class="form-group"><label>Телефон</label><input type="tel" name="phone" value="<?= htmlspecialchars($user['phone']??'') ?>"></div>
    <div class="form-group"><label>Роль</label><select name="role">
      <option value="user" <?= $user['role']==='user'?'selected':'' ?>>👤 Користувач</option>
      <option value="admin" <?= $user['role']==='admin'?'selected':'' ?>>⚙ Адміністратор</option>
    </select></div>
  </div>
  <div class="form-row">
    <div class="form-group"><label>Новий пароль <small>(порожньо — не змінювати)</small></label><input type="password" name="password" minlength="8"></div>
    <div class="form-group"><label>Підтвердити пароль</label><input type="password" name="confirm"></div>
  </div>
  <div class="form-actions">
    <button type="submit" class="btn btn-primary">💾 Зберегти</button>
    <a href="<?= APP_URL ?>/admin/users" class="btn btn-outline">Скасувати</a>
  </div>
</form>
