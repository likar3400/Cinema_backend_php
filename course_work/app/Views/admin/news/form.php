<a href="<?= APP_URL ?>/admin/news" class="btn btn-outline btn-sm">← Назад</a>
<?php if(!empty($error)): ?>
    <div class="alert alert-error mt-2"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST"
      action="<?= APP_URL ?>/admin/news/<?= $item ? $item['id'].'/edit' : 'create' ?>"
      class="admin-form">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= \App\Core\Session::csrfToken() ?>">

    <div class="form-group">
        <label>Заголовок *</label>
        <input type="text" name="title" required value="<?= htmlspecialchars($item['title'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label>Текст *</label>
        <textarea name="body" rows="8" required><?= htmlspecialchars($item['body'] ?? '') ?></textarea>
    </div>

    <div class="form-group">
        <label>Статус</label>
        <!-- SELECT замість checkbox — завжди відправляє значення -->
        <select name="is_active">
            <option value="1" <?= ($item['is_active'] ?? 1) == 1 ? 'selected' : '' ?>>✅ Активна</option>
            <option value="0" <?= ($item['is_active'] ?? 1) == 0 ? 'selected' : '' ?>>❌ Неактивна</option>
        </select>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">
            <?= $item ? '💾 Зберегти' : '➕ Створити' ?>
        </button>
        <a href="<?= APP_URL ?>/admin/news" class="btn btn-outline">Скасувати</a>
    </div>
</form>
