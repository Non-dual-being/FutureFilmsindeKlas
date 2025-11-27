<?php
declare(strict_types=1);
use GeoFort\Database\Connector;
use GeoFort\Security\AuthMiddleWare;

use GeoFort\ErrorHandlers\FlashMessageHandler;
use GeoFort\ErrorHandlers\FormExceptionHandler;
use GeoFort\ErrorHandlers\GeneralException;

use GeoFort\Services\Http\HeaderRedirector;


$GeoFortSession = new AuthMiddleWare();
$GeoFortSession->privateSession();

try {
    $pdo = Connector::getConnection();
    error_log("ik ben op het dashboard geweest");

} catch (PDOException $e){
    error_log("DatabaseFout: " . $e->getMessage());
    $message = urlencode("The service is momentarily not available, is time to get a cup of coffee");
    HeaderRedirector::toError("error.php", 503);

    /**geen pdo is een 503 Service Unavailable */
}
?>