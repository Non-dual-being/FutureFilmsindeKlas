<?php
declare(strict_types=1);
error_log(print_r($pageData, true));
$totals  = $pageData['totals'] ?? [];
$daily   = $pageData['daily'] ?? [];
$devices = $pageData['devices'] ?? [];

$hasDaily = is_array($daily) && count($daily) > 0;
$hasDevices = is_array($devices) && count($devices) > 0;
?>
<link rel="stylesheet" href="<?= htmlspecialchars(asset('styles/dashboard/analytics/stats.css'))?>" >
<script src="<?= htmlspecialchars(asset('scripts/dashboard/dashboard-charts.js')) ?>" defer></script>

<section class="dash-overview-grid">
  <?php /**stats cards (first row) */ ?>
  <article class="dash-card">
    <div class="dash-card__label">Total visitors</div>
    <div class="dash-card__value"><?= (int) ($totals['totalVisitors'] ?? 0) ?></div>
  </article>

  <article class="dash-card">
    <div class="dash-card__label">New weekly visitors</div>
    <div class="dash-card__value"><?= (int) ($totals['visitorsLast7d'] ?? 0) ?></div>
  </article>

  <article class="dash-card">
    <div class="dash-card__label">New monthly visitors</div>
    <div class="dash-card__value"><?= (int) ($totals['visitorsLast30d'] ?? 0) ?></div>
  </article>

  <?php /**stats grafics (second row) */ ?>
  <article class="dash-panel--wide">
    <header class="dash-panel__header">
      <h2>New Daily Visitors</h2>
    </header>

    <div class="dash-panel__body">
      <?php if (!$hasDaily): ?>
        <div class="dash-empty">
          <div class="dash-empty__title">Nog geen data</div>
          <div class="dash-empty__text">
            Zodra bezoekers de site openen, verschijnt hier een grafiek.
          </div>
        </div>
      <?php else: ?>
        <canvas id="visitorsPerDayChart" height="140"></canvas>
        <script>
          window.FUTURE_DASH = window.FUTURE_DASH || {};
          window.FUTURE_DASH.dailyLabels = <?= json_encode(array_column($daily, 'day'), JSON_UNESCAPED_UNICODE) ?>;
          window.FUTURE_DASH.dailyValues = <?= json_encode(array_map('intval', array_column($daily, 'visitors'))) ?>;
        </script>
      <?php endif; ?>
    </div>
  </article>

  <article class="dash-panel">
    <header class="dash-panel__header">
      <h2>Device Distribution</h2>
    </header>

    <div class="dash-panel__body">
      <?php if (!$hasDevices): ?>
        <div class="dash-empty">
          <div class="dash-empty__title">Nog geen data</div>
          <div class="dash-empty__text">
            Device data wordt zichtbaar zodra er bezoekers zijn.
          </div>
        </div>
      <?php else: ?>
        <canvas id="deviceChart" height="140"></canvas>
        <script>
          window.FUTURE_DASH = window.FUTURE_DASH || {};
          window.FUTURE_DASH.deviceLabels = <?= json_encode(array_column($devices, 'device_type'), JSON_UNESCAPED_UNICODE) ?>;
          window.FUTURE_DASH.deviceValues = <?= json_encode(array_map('intval', array_column($devices, 'visitors'))) ?>;
        </script>
      <?php endif; ?>
    </div>
  </article>
</section>
