<?php
declare(strict_types=1);
/* error_log(print_r($pageData, true)); */

/**collect data from controller */

$rangeDays = (int) ($pageData['rangeDays'] ?? 30);

$cards = $pageData['cards'] ?? [];
$charts = $pageData['charts'] ?? [];
$tables = $pageData['tables'] ?? [];

$dailyNewVisitors = $charts['dailyNewVisitors'] ?? [];
$devices = $charts['deviceDistribution'] ?? [];

$hasDaily = is_array($dailyNewVisitors) && count($dailyNewVisitors) > 0;
$hasDevices = is_array($devices) && count($devices) > 0;

$topPages = $tables['topPages'] ?? [];
$topReferrers = $tables['topReferrers'] ?? [];

?>

<script src="<?= htmlspecialchars(asset('scripts/dashboard/dashboard-charts.js')) ?>" defer></script>

<section class="dash-filterbar">
  <a 
    class = "dash-chip <?=$rangeDays === 7 ? 'is-active' : ''?>"
    href  = "?range=7"
  >7</a>

  <a 
    class = "dash-chip  <?=$rangeDays === 30 ? 'is-active' : ''?>"
    href  = "?range=30"
  >30</a>

  <a 
    class = "dash-chip  <?=$rangeDays === 90 ? 'is-active' : ''?>"
    href  = "?range=90"
  >90</a>
</section>

<section class="dash-overview-grid">
  <!-- Cards -->
  <article class="dash-card ng-panel">
    <div class="dash-card__label">Total visitors (all-time)</div>
    <div class="dash-card__value"><?= (int) ($cards['totalVisitorsAllTime'] ?? 0) ?></div>
  </article>

  <article class="dash-card ng-panel">
    <div 
      class="dash-card__label">New <?= htmlspecialchars($rangeDays !== 90 ? "$rangeString " : '')?>visitors<?= htmlspecialchars($rangeDays === 90 ? " $rangeString" : '')?></div>
    <div class="dash-card__value"><?= (int) ($cards['newVisitorsRange'] ?? 0) ?></div>
  </article>

  <article class="dash-card ng-panel">
    <div class="dash-card__label">New <?= htmlspecialchars($rangeDays !== 90 ? "$rangeString " : '')?>sessions<?= htmlspecialchars($rangeDays === 90 ? " $rangeString": '')?></div>
    <div class="dash-card__value"><?= (int) ($cards['sessionsRange'] ?? 0) ?></div>
  </article>

  <article class="dash-card ng-panel">
    <div class="dash-card__label">New <?= htmlspecialchars($rangeDays !== 90 ? "$rangeString " : '')?>pageviews<?= htmlspecialchars($rangeDays === 90 ? " $rangeString": '')?></div>
    <div class="dash-card__value"><?= (int) ($cards['sessionsRange'] ?? 0) ?></div>
  </article>

  <article class="dash-card ng-panel">
    <div class="dash-card__label">New <?= htmlspecialchars($rangeDays !== 90 ? "$rangeString " : '')?>BounceRate<?= htmlspecialchars($rangeDays === 90 ? " $rangeString": '')?></div>
    <div class="dash-card__value"><?= htmlspecialchars((string) ($cards['pageviewsRange'] ?? 0)) ?>%</div>
  </article>

  <!--charts-->
  <!--daily-->
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
          window.FUTURE_DASH.dailyLabels = <?= json_encode(array_column($dailyNewVisitors, 'day'), JSON_UNESCAPED_UNICODE) ?>;
          window.FUTURE_DASH.dailyValues = <?= json_encode(array_map('intval', array_column($dailyNewVisitors, 'visitors'))) ?>;
        </script>
      <?php endif; ?>
    </div>
  </article>
  
  <!--device donut-->
  <article class="dash-panel ng-panel">
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

  <!--table: top pages -->
  <article class="dash-panel dash-panel--wide ng-panel">
    <header class="dash-panel__header">
      <h2>Top pages</h2>
      <div class="dash-panel__body">
        <table class="dash-table">
          <thead><tr><th>Path</th><th>views</th><th>view-time <span class="subscript">(avg seconds)</span></tr></thead>
            <tbody>
              <?php foreach ($topPages as $r) : ?>
                <tr>
                  <td><?= htmlspecialchars($r['path'] ?? '') ?></td>
                  <td><?= (int) ($r['views'] ?? 0) ?></td>
                  <td><?= (int) ($r['view_time'] ?? 0) ?></td>
                </tr>
              <?php endforeach; ?>
            <tbody>
        </table>
      </div>
    </header>
  </article>

    <!-- Table: Top referrers -->
  <article class="dash-panel ng-panel">
    <header class="dash-panel__header"><h2>Top referrers</h2></header>
    <div class="dash-panel__body">
      <table class="dash-table">
        <thead><tr><th>Referrer</th><th>Sessions</th></tr></thead>
        <tbody>
          <?php foreach ($topReferrers as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r['referrer_host'] ?? '') ?></td>
              <td><?= (int) ($r['sessions'] ?? 0) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </article>
</section>
