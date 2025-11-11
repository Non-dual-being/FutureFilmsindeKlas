<?php
use GeoFort\Database\Connector;
use GeoFort\Security\AuthMiddleWare;
use GeoFort\Services\SQL\AdminUsersSQLService;
use GeoFort\Services\SQL\LoginAttemptsSQLService;

$MiddleWare = new AuthMiddleWare();
$MiddleWare->publicSession();

if (!isset($_SESSION['crsf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

//standaardwaarden
$inActivityMessage = '';
$flashMessage = '';
$flashType = '';
$errors = [];
$connectedToDB = true;

try {
    $pdo = Connector::getConnection();

} catch (PDOEXCEPTION $e) {
    error_log($e->getMessage());
    $connectedToDB = false;
}


?>