<?php    
    declare(strict_types=1);
    namespace GeoFort\Services\Http;
    final class HeaderRedirector implements Redirector {

        public static function absolute(
            string $baseUrl,
            string $path,
            array $query = [],
            int $httpCode = 302
        ): never {
            $base = rtrim($baseUrl, '/');
            $rel = ltrim($path, '/');
            $qs = $query 
                ? ('?' . http_build_query($query)) 
                : '';
            header('Locaction: ' . $base . '/' . $rel . $qs, true, $httpCode);
            exit();
        }
        public static function toLogin(
            string $baseUrl,
            string $path = 'loginthefuture.php',
            ?string $inactiveMsg = null 
        ): never {
            $qs = [];
            if ($inactiveMsg !== null && $inactiveMsg !== ''){
                $query['inactiveMsg'] = $inactiveMsg;
            }

            self::absolute($baseUrl, $path, $query, 303);
        }

        public static function toError(
            string $baseUrl,
            string $path = 'error.php',
            int $errorCode = 500, 
            string  $message = ''
        ): never {
            $query['code'] = $errorCode;
            if ($message !== ''){
                $query['message'] = $message;
            }

            self::absolute($baseUrl, $path, $query);
        }

    }
?>