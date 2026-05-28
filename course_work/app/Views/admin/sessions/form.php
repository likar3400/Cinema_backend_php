<a href="<?= APP_URL ?>/admin/sessions" class="btn btn-outline btn-sm">← Назад</a>
<form method="POST" action="<?= APP_URL ?>/admin/sessions/<?= $session?$session['id'].'/edit':'create' ?>" class="admin-form">
  <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= \App\Core\Session::csrfToken() ?>">
  <div class="form-row">
    <div class="form-group"><label>Фільм *</label><select name="movie_id" required>
      <?php foreach($movies as $m): ?><option value="<?= $m['id'] ?>" <?= ($session['movie_id']??0)==$m['id']?'selected':'' ?>><?= htmlspecialchars($m['title']) ?></option><?php endforeach; ?>
    </select></div>
    <div class="form-group"><label>Зала *</label><select name="hall_id" required>
      <?php foreach($halls as $h): ?><option value="<?= $h['id'] ?>" <?= ($session['hall_id']??0)==$h['id']?'selected':'' ?>><?= htmlspecialchars($h['name']) ?></option><?php endforeach; ?>
    </select></div>
  </div>
  <div class="form-row">
    <div class="form-group"><label>Початок *</label><input type="datetime-local" name="starts_at" required value="<?= isset($session['starts_at'])?date('Y-m-d\TH:i',strtotime($session['starts_at'])):'' ?>"></div>
    <div class="form-group"><label>Кінець *</label><input type="datetime-local" name="ends_at" required value="<?= isset($session['ends_at'])?date('Y-m-d\TH:i',strtotime($session['ends_at'])):'' ?>"></div>
  </div>
  <div class="form-row">
    <div class="form-group"><label>Ціна стандарт (грн) *</label><input type="number" name="price" min="0" step="0.01" required value="<?= $session['price']??0 ?>"></div>
    <div class="form-group"><label>Ціна VIP (0 = без VIP)</label><input type="number" name="price_vip" min="0" step="0.01" value="<?= $session['price_vip']??0 ?>"></div>
    <div class="form-group"><label>Мова</label><select name="language">
      <?php foreach(['uk'=>'Українська','en'=>'Англійська','dub'=>'Дублювання'] as $v=>$l): ?>
      <option value="<?= $v ?>" <?= ($session['language']??'uk')===$v?'selected':'' ?>><?= $l ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label>Формат</label><select name="format">
      <?php foreach(['2D','3D','IMAX','4DX'] as $f): ?><option value="<?= $f ?>" <?= ($session['format']??'2D')===$f?'selected':'' ?>><?= $f ?></option><?php endforeach; ?></select></div>
  </div>
  <div class="form-actions">
    <button type="submit" class="btn btn-primary"><?= $session?'💾 Зберегти':'➕ Створити' ?></button>
    <a href="<?= APP_URL ?>/admin/sessions" class="btn btn-outline">Скасувати</a>
  </div>
</form>
