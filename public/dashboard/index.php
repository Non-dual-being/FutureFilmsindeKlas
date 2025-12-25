<?php
declare(strict_types=1);
require_once  __DIR__ . '/../bootstrap.php';

use GeoFort\Services\Http\PrivatePageBootStrapper;
use GeoFort\Services\SQL\AnalyticsVisitorSQLService;
use GeoFort\Services\Analytics\DashboardStatsService;
use GeoFort\Controller\DashboardController;

$pdo = PrivatePageBootStrapper::init();

$visitorSql = new AnalyticsVisitorSQLService($pdo);
$statsService = new DashboardStatsService($visitorSql);
$controller = new DashboardController($statsService);
$controller->index();
?>