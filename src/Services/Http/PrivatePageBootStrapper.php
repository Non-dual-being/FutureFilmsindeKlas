<?php
declare(strict_types=1);
namespace GeoFort\Services\Http;

use PDO;
use PDOException;

use GeoFort\Database\Connector;
use GeoFort\Security\AuthMiddleWare;
use GeoFort\Services\Http\HeaderRedirector;


final class PrivatePageBootStrapper {
    public static function init(): PDO {
        $GeoFortSession = new AuthMiddleWare();
        $GeoFortSession->privateSession();

        try {
            return Connector::getConnection();

        } catch(PDOException $e){
            error_log('Dashboard private page innit error: ' . $e->getMessage());
            HeaderRedirector::toError(
                errorCode: 503,
                message: 'Dashboard unavailable due to service error'
            );
            exit;

        }


    }
}
?>