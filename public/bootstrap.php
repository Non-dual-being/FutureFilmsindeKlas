<?php
declare(strict_types=1);
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR .  'autoload.php';

use Dotenv\Dotenv;

use GeoFort\Security\AuthMiddleWare;

use GeoFort\Services\Http\GlobalBaseUrlProvider;
use GeoFort\Services\SQL\AnalyticsVisitorSQLService;

use GeoFort\Utils\Analytics\VisitorTracker;

use GeoFort\Database\Connector;

error_reporting(E_ALL);
ini_set('log_errors', 1);  
date_default_timezone_set('Europe/Amsterdam');


try {
        // The path should point to your project's root directory
    $path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
    $dotenv = Dotenv::createImmutable($path);
    $dotenv->load();

} catch (\Dotenv\Exception\InvalidPathException $e){
    // This error will happen if the .env file is not found.
    error_log("Could not find the .env file: " . $e->getMessage());
    die("Configuration error: Could not find the .env file. Please check the environment setup.");
}

$envActive = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'];
AuthMiddleWare::setEnvironment($envActive);

$baseUrl=$_ENV['BASE_URL'] ?? $_SERVER['BASE_URL'];
AuthMiddleWare::setBaseUrl($baseUrl);

GlobalBaseUrlProvider::init($envActive);

if ($envActive === 'development'){
    ini_set('display_errors', 1);  // Schakel weergave van fouten in de browser in
    ini_set('display_startup_errors', 1);

} else if ($envActive === 'production'){
    ini_set('display_errors', 0);  // Schakel weergave van fouten in de browser in
    ini_set('display_startup_errors', 0);
}

/**
 * setting up tracking to get visitor info
 */

try {
    $pdo = Connector::getConnection();
    $analyticsService = new AnalyticsVisitorSQLService($pdo);
    $salt = $_ENV['ANALYTICS_SALT'] ?? $_SERVER['ANALYTICS_SALT'] ?? '';

    if (empty($salt)){
        error_log("salt in bootstrap could not be innitiallised with env values, falling back to fallback value");
        $salt = $envActive === 'development'
            ? 'development-fallback-salt'
            : 'production-fallback-salt';
    }

    $tracker = new VisitorTracker($analyticsService, $salt);
    $tracker->track();

} catch (\Throwable $e){
    error_log("Analytics set up failed in bootstrap: " . $e->getMessage());
}





/**
 * require once omdat je de klassen maar 1 keer wilt inladen en niet per ongeuk twee keer
 */
?>