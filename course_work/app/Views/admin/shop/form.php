<div class="admin-header">
  <h1><?= $item ? 'Редагувати товар' : 'Новий товар' ?></h1>
  <a href="<?= APP_URL ?>/admin/shop" class="btn btn-outline">← Назад</a>
</div>
<div class="form-wrap card">
<form method="POST" enctype="multipart/form-data">
  <input type="hidden" name="_csrf" value="<?= \App\Core\Session::csrfToken() ?>">

  <div class="fg">
    <label>Назва *</label>
    <input type="text" name="name" value="<?= htmlspecialchars($item['name'] ?? '') ?>" required>
  </div>
  <div class="fg">
    <label>Опис</label>
    <textarea name="description" rows="3"><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
  </div>
  <div class="form-row">
    <div class="fg">
      <label>Ціна (грн) *</label>
      <input type="number" name="price" step="0.01" min="0" value="<?= $item['price'] ?? '' ?>" required>
    </div>
    <div class="fg">
      <label>Категорія</label>
      <select name="category">
        <?php foreach (['popcorn'=>'🍿 Попкорн','drink'=>'🥤 Напої','snack'=>'🍫 Снеки','combo'=>'🎁 Комбо'] as $v=>$l): ?>
        <option value="<?= $v ?>" <?= ($item['category'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="fg">
      <label>Активний</label>
      <select name="is_active">
        <option value="1" <?= ($item['is_active'] ?? 1) ? 'selected' : '' ?>>Так</option>
        <option value="0" <?= !($item['is_active'] ?? 1) ? 'selected' : '' ?>>Ні</option>
      </select>
    </div>
  </div>

  <div class="fg">
    <label>Зображення</label>
    <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
    <?php if (!empty($item['image'])): ?>
    <div style="margin-top:.5rem;display:flex;align-items:center;gap:1rem">
      <img src="<?= APP_URL . htmlspecialchars($item['image']) ?>"
           alt="<?= htmlspecialchars($item['name'] ?? '') ?>"
           style="height:80px;border-radius:8px;object-fit:cover;border:1px solid var(--border)">
      <span class="hint">Поточне: <?= htmlspecialchars($item['image']) ?></span>
    </div>
    <?php else: ?>
    <p class="hint mt-1">Картинка не завантажена. Завантажте файл або скористайтесь назвами вище.</p>
    <?php endif; ?>
  </div>

  <button type="submit" class="btn btn-primary">💾 Зберегти</button>
</form>
</div>
