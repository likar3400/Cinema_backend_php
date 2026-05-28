<a href="<?= APP_URL ?>/admin/users" class="btn btn-outline btn-sm">← Назад</a>
<?php if(!empty($errors)): ?><div class="alert alert-error mt-2"><?php foreach($errors as $e): ?><p><?= htmlspecialchars($e) ?></p><?php endforeach; ?></div><?php endif; ?>
<form method="POST" action="<?= APP_URL ?>/admin/users/create" class="admin-form">
  <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= \App\Core\Session::csrfToken() ?>">
  <div class="form-row">
    <div class="form-group"><label>Ім'я *</label><input type="text" name="name" required value="<?= htmlspecialchars($old['name']??'') ?>"></div>
    <div class="form-group"><label>Телефон</label><input type="tel" name="phone" value="<?= htmlspecialchars($old['phone']??'') ?>"></div>
  </div>
  <div class="form-group"><label>Email *</label><input type="email" name="email" required value="<?= htmlspecialchars($old['email']??'') ?>"></div>
  <div class="form-row">
    <div class="form-group"><label>Пароль * (мін. 8 символів)</label><input type="password" name="password" required minlength="8"></div>
    <div class="form-group"><label>Роль</label><select name="role">
      <option value="user" <?= ($old['role']??'user')==='user'?'selected':'' ?>>👤 Користувач</option>
      <option value="admin" <?= ($old['role']??'')==='admin'?'selected':'' ?>>⚙ Адміністратор</option>
    </select></div>
  </div>
  <div class="form-actions">
    <button type="submit" class="btn btn-primary">➕ Створити користувача</button>
    <a href="<?= APP_URL ?>/admin/users" class="btn btn-outline">Скасувати</a>
  </div>
</form>
