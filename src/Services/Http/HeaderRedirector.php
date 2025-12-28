<?php    
declare(strict_types=1);
namespace GeoFort\Services\Http;
use GeoFort\Services\Http\GlobalBaseUrlProvider;

final class HeaderRedirector implements Redirector {

    private static function base(): string {
        return rtrim(GlobalBaseUrlProvider::get()->getBaseUrl(), '');
    }

    public static function absolute(
        string $path,
        array $query = [],
        int $httpCode = 302
    ): never {
        $base = self::base();
        $rel = ltrim($path, '/');
        $qs = $query 
            ? ('?' . http_build_query($query)) 
            : '';
        header('Location: ' . $base . '/' . $rel . $qs, true, $httpCode);
        exit();
    }

    public static function toLogin(
        string $path = 'auth/login.php',
        ?string $inactiveMsg = null 
    ): never {
        $query = [];
        
        if ($inactiveMsg !== null && $inactiveMsg !== ''){
            $query['inactiveMsg'] = $inactiveMsg;
        }

        self::absolute($path, $query, 303);
    }
    
    public static function toDashboard(
        string $path = 'dashboard/index.php',
    ): never {
        $query = [];
        self::absolute($path, $query, 303);
    }

    public static function toError(
        string $path = '/error/index.php',
        int $errorCode = 500, 
        string  $message = ''
    ): never {
        $baseUrl = self::base();
        $query['code'] = $errorCode;
        if ($message !== ''){
            $query['message'] = $message;
        }

        self::absolute($path, $query, 302);
    }

}

/**
 * =======================================
 * HTTP STATUS CODE ` header('Location: ' . $base . '/' . $rel . $qs, true, $httpCode);`
 * ========================================
 * 
 * Redirect status
 * 
 * This code is sent by server to browser as instruction how to handle the redirect
 * 
 * Typeical redirect types: 302 / 303 / 307 / 308
 * 
 * 302 Found historcally used for everything
 * 
 * 303 See Other from a post form to a get page preveting a repost
 * 
 * 307 Temporary Redirect -> strict preservs prost on the new page
 * 
 * 308 Permanent redirect -? also strict
 * 
 * use 302 for get to get and 303 for post to get
 * 
 */


/**
 * =======================================
 * ERROR-CODE ` $query['code'] = $errorCode;`
 * ========================================
 * 
 * Error status
 * 
 * This code is a apllication code that is passed to the error page to indicated the status en the message
 * 
 */


/** 
 * ==========================================
 * ERROR CODES
 * ==========================================
 * 400 Bad Request: Malformed request, missing/invalid parameters.
 * 
 * 401 Unauthorized: Authentication required (often used with WWW-Authenticate).
 * 
 * 403 Forbidden: Authenticated but not authorized.
 * 
 * 404 Not Found: Resource doesn’t exist.
 * 
 * 409 Conflict: State conflict (e.g., duplicate email).
 * 
 * 422 Unprocessable Entity: Validation errors on input.
 * 
 * 500 Internal Server Error: Generic server error.
 * 
 * 502 Bad Gateway: Upstream/proxy error.
 * 
 * 503 Service Unavailable: Service or dependency is down / maintenance (your DB-down case is perfect here).
 * 
 * 504 Gateway Timeout: Upstream timed out.
 */
?>