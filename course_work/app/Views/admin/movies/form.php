<div class="admin-header">
  <h1><?= $movie ? 'Редагувати фільм' : 'Новий фільм' ?></h1>
  <a href="<?= APP_URL ?>/admin/movies" class="btn btn-outline">← Назад</a>
</div>
<?php if (!empty($error)): ?><div class="flash flash-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<div class="form-wrap card">
<form method="POST" enctype="multipart/form-data">
  <input type="hidden" name="_csrf" value="<?= \App\Core\Session::csrfToken() ?>">

  <div class="fg">
    <label>Назва *</label>
    <input type="text" name="title" value="<?= htmlspecialchars($movie['title'] ?? '') ?>" required>
  </div>
  <div class="fg">
    <label>Опис</label>
    <textarea name="description" rows="4"><?= htmlspecialchars($movie['description'] ?? '') ?></textarea>
  </div>
  <div class="form-row">
    <div class="fg">
      <label>Жанр</label>
      <input type="text" name="genre" value="<?= htmlspecialchars($movie['genre'] ?? '') ?>">
    </div>
    <div class="fg">
      <label>Категорія</label>
      <select name="category_id">
        <option value="">— без категорії —</option>
        <?php foreach ($categories as $cat): ?>
        <option value="<?= $cat['id'] ?>" <?= ($movie['category_id'] ?? 0) == $cat['id'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($cat['name']) ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="form-row">
    <div class="fg">
      <label>Тривалість (хв)</label>
      <input type="number" name="duration" value="<?= $movie['duration'] ?? 90 ?>" min="1">
    </div>
    <div class="fg">
      <label>Рейтинг</label>
      <input type="number" name="rating" step="0.1" min="0" max="10" value="<?= $movie['rating'] ?? '' ?>">
    </div>
    <div class="fg">
      <label>Вікове обм.</label>
      <select name="age_rating">
        <?php foreach (['0+','6+','12+','16+','18+'] as $r): ?>
        <option <?= ($movie['age_rating'] ?? '0+') === $r ? 'selected' : '' ?>><?= $r ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="form-row">
    <div class="fg">
      <label>Дата виходу</label>
      <input type="date" name="release_date" value="<?= $movie['release_date'] ?? '' ?>">
    </div>
    <div class="fg">
      <label>Активний</label>
      <select name="is_active">
        <option value="1" <?= ($movie['is_active'] ?? 1) ? 'selected' : '' ?>>Так</option>
        <option value="0" <?= !($movie['is_active'] ?? 1) ? 'selected' : '' ?>>Ні</option>
      </select>
    </div>
  </div>
  <div class="fg">
    <label>Трейлер URL</label>
    <input type="url" name="trailer_url" value="<?= htmlspecialchars($movie['trailer_url'] ?? '') ?>">
  </div>
  <div class="fg">
    <label>Постер (файл)</label>
    <input type="file" name="poster" accept="image/jpeg,image/png,image/webp">
    <?php if (!empty($movie['poster'])): ?>
    <img src="<?= APP_URL . htmlspecialchars($movie['poster']) ?>" style="max-height:80px;margin-top:.5rem;border-radius:4px">
    <?php endif; ?>
  </div>
  <button type="submit" class="btn btn-primary">💾 Зберегти</button>
</form>
</div>
