<?php
declare(strict_types=1);

use Dotenv\Dotenv;
use GeoFort\Security\AuthMiddleWare;

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

if ($envActive === 'development'){
    ini_set('display_errors', 1);  // Schakel weergave van fouten in de browser in
    ini_set('display_startup_errors', 1);

} else if ($envActive === 'production'){
    ini_set('display_errors', 0);  // Schakel weergave van fouten in de browser in
    ini_set('display_startup_errors', 0);

}
error_reporting(E_ALL);
ini_set('log_errors', 1);  
date_default_timezone_set('Europe/Amsterdam');
?>