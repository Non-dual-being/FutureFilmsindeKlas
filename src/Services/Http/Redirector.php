<?php
    declare(strict_types=1);
    namespace GeoFort\Services\Http;

    interface Redirector {
        public static function toLogin(
            string $path = 'auth/login-page.php',
            ?string $inactivityMsg = null
        ): never;

        public static function toError(
            string $path = 'error/index.php',
            int $errorCode = 500, 
            string $message = ''
        ): never;

        public static function absolute(
            string $path,
            array $query = [],
            int $httpCode = 302
        ): never;
    }
?>