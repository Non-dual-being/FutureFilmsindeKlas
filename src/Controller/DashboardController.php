<?php
declare(strict_types=1);
use GeoFort\Database\Connector;
use GeoFort\Security\AuthMiddleWare;

use GeoFort\ErrorHandlers\FlashMessageHandler;
use GeoFort\ErrorHandlers\FormExceptionHandler;
use GeoFort\ErrorHandlers\GeneralException;

$GeoFortSession = new AuthMiddleWare();
$GeoFortSession->privateSession();

try {
    $pdo = Connector::getConnection();

} catch (PDOException $e){
    error_log("DatabaseFout: " . $e->getMessage());
    $message = urlencode("The service is momentarily not available, is time to get a cup of coffee");
    header("Location: errorPage.php?code=503&message={$message}");
    exit();
}
?>