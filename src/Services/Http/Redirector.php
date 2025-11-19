<?php
    declare(strict_types=1);
    namespace GeoFort\Services\Http;

    interface Redirector {
        public static function toLogin(
            string $baseUrl,
            string $path = 'loginthefuture.php',
            ?string $inactivityMsg = null
        ): never;

        public static function toError(
            string $baseUrl,
            string $path = 'error.php',
            int $errorCode = 500, 
            string $message = ''
        ): never;

        public static function absolute(
            string $baseUrl,
            string $path,
            array $query = [],
            int $httpCode = 302
        ): never;
    }
?>