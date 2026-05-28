<a href="<?= APP_URL ?>/admin/halls" class="btn btn-outline btn-sm">← Назад</a>
<form method="POST" action="<?= APP_URL ?>/admin/halls/<?= $hall?$hall['id'].'/edit':'create' ?>" class="admin-form">
  <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= \App\Core\Session::csrfToken() ?>">
  <div class="form-group"><label>Назва залу *</label><input type="text" name="name" required value="<?= htmlspecialchars($hall['name']??'') ?>"></div>
  <div class="form-row">
    <div class="form-group"><label>Кількість рядів *</label><input type="number" name="row_count" min="1" max="30" required value="<?= $hall['row_count']??8 ?>"></div>
    <div class="form-group"><label>Місць у ряду *</label><input type="number" name="col_count" min="1" max="50" required value="<?= $hall['col_count']??12 ?>"></div>
    <div class="form-group"><label>Тип</label><select name="type">
      <?php foreach(['standard'=>'Стандарт','vip'=>'VIP','imax'=>'IMAX'] as $v=>$l): ?>
      <option value="<?= $v ?>" <?= ($hall['type']??'standard')===$v?'selected':'' ?>><?= $l ?></option>
      <?php endforeach; ?></select></div>
  </div>
  <?php if($hall): ?>
  <div class="form-group"><label class="checkbox-label"><input type="checkbox" name="is_active" value="1" <?= $hall['is_active']?'checked':'' ?>> Активний</label></div>
  <?php endif; ?>
  <p class="form-hint">💡 Перші 2 ряди автоматично стають VIP-місцями.</p>
  <div class="form-actions">
    <button type="submit" class="btn btn-primary"><?= $hall?'💾 Зберегти':'➕ Створити' ?></button>
    <a href="<?= APP_URL ?>/admin/halls" class="btn btn-outline">Скасувати</a>
  </div>
</form>
