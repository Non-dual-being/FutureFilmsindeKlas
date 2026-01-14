<?php
declare(strict_types=1);
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR .  'autoload.php';

use Dotenv\Dotenv;
use GeoFort\Security\AuthMiddleWare;

use GeoFort\Services\Http\GlobalBaseUrlProvider;
use GeoFort\Services\Http\CountryCodeResolver;
use GeoFort\Services\Http\HeaderRedirector;
use GeoFort\Services\SQL\AnalyticsVisitorSQLService;
use GeoFort\Services\SQL\AnalyticsSessionSQLService;
use GeoFort\Services\SQL\AnalyticsPageviewSQLService;
use GeoFort\Services\Analytics\AnalyticsTracker;
use GeoFort\Services\Analytics\AnalyticsConfig;
use GeoFort\Database\Connector;


/**-----------[BASE SETUP]----------------- */

$requestedPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$validRoutes = [
    '/', 
    '/index.php', 
    'index.php', 
    'videoplayer.php', 
    'auth/login-page.php',
    'dashboard/index.php',
    'dashboard/pages.php',
    'dashboard/referrers.php',
    'error/index.php',

];

if (!in_array(ltrim($requestedPath, '/'))) {
    HeaderRedirector::toError(

    );
}

error_reporting(E_ALL);
ini_set('log_errors', 1);  
date_default_timezone_set('Europe/Amsterdam');

/** PADEN DEFINIEREN */
if (!defined('VIEW_PATH')) define('VIEW_PATH', __DIR__ . '/../views');
if (!defined('PUBLIC_PATH')) define('PUBLIC_PATH', __DIR__);


/**ENVIRONMENT ENV LADEN */
try {
    // The path should point to your project's root directory
    $path = dirname(__DIR__);
    $dotenv = Dotenv::createImmutable($path);
    $dotenv->load();

} catch (\Dotenv\Exception\InvalidPathException $e){
    // This error will happen if the .env file is not found.
    error_log("Could not find the .env file: " . $e->getMessage());
    die("Configuration error: Could not find the .env file. Please check the environment setup.");
}


/**ENVIRONEMENT CHECK */
$envActive = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? '';
if ($envActive === ''){
    error_log("failed bootstrap: env-var could not be loaded correctly");
    exit("FutureFilms has a critical error");
}
AuthMiddleWare::setEnvironment($envActive);
GlobalBaseUrlProvider::init($envActive);

/**BASEURL CHECK */
$baseUrl=$_ENV['BASE_URL'] ?? $_SERVER['BASE_URL'] ?? '';
if ($baseUrl === ''){
    error_log("failed bootstrap: base_url could not be loaded correctly");
    exit("FutureFilms has a critical error");
}
AuthMiddleWare::setBaseUrl($baseUrl);

/**INITIAL SETUP BASED UPON ENVIRONMENT */
if ($envActive === 'development'){
    ini_set('display_errors', 1);  // Schakel weergave van fouten in de browser in
    ini_set('display_startup_errors', 1);

} else if ($envActive === 'production'){
    ini_set('display_errors', 0);  // Schakel weergave van fouten in de browser in
    ini_set('display_startup_errors', 0);
} else {
    error_log("failed bootstrap: env-var could not be loaded correctly");
    exit("FutureFilms has a critical error");
}

/***--------------------------------------------------------- */
/***----------------------[TRACKING]------------------------- */
/***--------------------------------------------------------- */

try {
    $shouldTrack = false;
    $salt = $_ENV['ANALYTICS_SALT'] ?? $_SERVER['ANALYTICS_SALT'] ?? '';

    if ($salt === '' && $envActive === 'production'){
        error_log("warning: track disable salt key not accesable in production mode");
    } else {
        if ($salt === '' && $envActive === 'development'){
            $salt = 'some_random_salt_development_key';
            $shouldTrack = true;
            error_log("using salt fallback key in environment development instead of initiel key");
        } else if ($salt !== '' && in_array($envActive, ['production','development'])) {
            $shouldTrack = true;
        }
      

    }
    
   

    if (AnalyticsTracker::shouldTrack($_SERVER) && $shouldTrack){
        $pdo = Connector::getConnection();
        $visitors = new AnalyticsVisitorSQLService($pdo);
        $sessions = new AnalyticsSessionSQLService($pdo);
        $pageviews = new AnalyticsPageviewSQLService($pdo);

        $countryCodeDbPath = $_ENV['GEOLITE_PATH'] ?? $_SERVER['GEOLITE_PATH'] ?? null;
        $countryCodeResolver = null;

        if (is_string($countryCodeDbPath) && $countryCodeDbPath !== ''){
            $GeoLite = dirname(__DIR__) . '/' . ltrim($countryCodeDbPath, '/');
            $countryCodeResolver = new CountryCodeResolver($GeoLite) ?? null;
        } else {
            error_log("path to GeoLite DB not found");
        }

        $tracker = new AnalyticsTracker(
            VisitorSql: $visitors,
            SessionSql: $sessions,
            PageViewSql: $pageviews,
            salt: $salt,
            countryResolver: $countryCodeResolver
        );

        $tracker->track();

    }
} catch (\Throwable $e){
    error_log("Analytics set up failed in bootstrap: " . $e->getMessage());
}

?>