<div class="error-page">
  <div class="err-code">404</div>
  <h1>Сторінку не знайдено</h1>
  <p><?= htmlspecialchars($message??'Запитувана сторінка не існує.') ?></p>
  <a href="<?= APP_URL ?>/" class="btn btn-primary">На головну</a>
</div>
