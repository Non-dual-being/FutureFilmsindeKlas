<?php
declare(strict_types=1);
namespace GeoFort\Services\Analytics;

use GeoFort\Services\SQL\AnalyticsPageviewSQLService;
use GeoFort\Services\SQL\AnalyticsSessionSQLService;
use GeoFort\Services\SQL\AnalyticsVisitorSQLService;
use GeoFort\Services\Http\ClientIpResolver;
use GeoFort\Services\Http\CountryCodeResolver;


final class AnalyticsTracker {
    private const DEVICE_TYPES = [
        'mobile'    => ['mobile', 'iphone', 'android'],
        'tablet'    => ['ipad', 'tablet']
    ];

    private const BROWSER_OS_TYPES = [
        'browser'   => [
            'FireFox'   => 'FireFox', 
            'Edg'       => 'Edge',
            'Chrome'    => 'Chrome',
            'Safari'    => 'Safari'
        ],
        'os'        => [
            'Windows'   => 'Windows',
            'Mac OS'    => 'macOs',
            'Macintosh' => 'macOs',
            'Android'   => 'Android',
            'iPhone'    => 'iOS',
            'iPad'      => 'iOS',
            'Linux'     => 'Linux'
        ]
    ];

    private const BOTFRAGMENTS = [
            'bot',
            'crawler',
            'spider',
            'slurp',
            'curl',
            'wget',
            'httpclient',
            'headless',
    ];

    private const STOP_TRACK_FLAG = '~\.(css|js|png|jpg|jpeg|webp|svg|ico)$~i';
    private const SESSION_COOKIE = 'ff_sid';

    private const EXCLUDED_PATHS = [
        '/dashboard',
        '/auth',
        '/error'
    ];

    public function __construct(
        private AnalyticsPageviewSQLService $PageViewSql,
        private AnalyticsSessionSQLService $SessionSql,
        private AnalyticsVisitorSQLService $VisitorSql,
        private string $salt,
        private ?CountryCodeResolver $countryResolver = null
    ){}

    private static function getBaseRequestUrl(array $server): string 
    {
        if (empty($server)) return '';
        $uri = $server['REQUEST_URI'] ?? '';
        $path = parse_url($uri, PHP_URL_PATH) ?: '';
        $path = substr($path, 0, 255);

        return $path;
    }

     private function makeFingerprint(string $ip, string $ua): string 
    {
        return hash('sha256', $ip . '|' . $ua . '|' . $this->salt);
    }

    private function detectDeviceType(string $ua): string 
    {
        $ua = strtolower($ua);

        foreach (self::DEVICE_TYPES as $device => $matchingValues){
            foreach($matchingValues as $match){
                if (str_contains($ua, $match)) return $device;
            }
        }

        return 'desktop';
    }

    private function detectBrowserAndOs(string $ua): array
    {
        $browser = 'unkown';
        $os      = 'unkown';

        $osTypes = self::BROWSER_OS_TYPES['os'];
        $browserTypes = self::BROWSER_OS_TYPES['browser'];

        foreach ($osTypes as $indicator => $type){
            if (stripos($ua, $indicator) !== False) {
                $os = $type;
                break;
            }
        }

        foreach ($browserTypes as $indicator => $type){
            if (stripos($ua, $indicator) !== False) {
                $browser = $type;
                break;
            }
        }

        return [
            'browser' => $browser,
            'os'      => $os
        ];

    }

    private static function isBot(string $ua): bool 
    {
        $ua = strtolower($ua);

        foreach(self::BOTFRAGMENTS as $fragment){
            if (str_contains($ua, $fragment)){
                /* error_log("bot detected"); */
                return true;
            }
        }

        return false;

    }

    private function getReferrerHost(array $server): ?string
    {
        if (empty($server)) return null;

        $ref = $server['HTTP_REFERER'] ?? null;
        if (!is_string($ref) || trim($ref) === '') return null;

        $host = parse_url($ref, PHP_URL_HOST);

        return (is_string($host) && $host !== '')
            ?   strtolower($host)
            :   null;
    }

    private function getUtm(array $query): array
    {
        $get = fn(string $k): ?string => (isset($query[$k]) && is_string($query[$k]))
            ?   $query[$k]
            :   null;
        
        return [
            'utm_source'    => $get('utm_source'),
            'utm_medium'    => $get('utm_medium'),
            'utm_campaign'  => $get('utm_campaign')
        ];
    }

    public static function shouldTrack(array $server): bool 
    {
        if (($server['REQUEST_METHOD'] ?? 'GET') !== 'GET'){
            return false;
        }

        if(PHP_SAPI === 'cli') return false;

        $path = self::getBaseRequestUrl($server);

        foreach(self::EXCLUDED_PATHS as $excluded){
            if (str_contains($path, $excluded)) return false;
        }

        if (preg_match(self::STOP_TRACK_FLAG, $path)) return false;

        $ua = $server['HTTP_USER_AGENT'] ?? '';
        
        if ($ua === '' || self::isBot($ua)) return false;

        return true;
        
    }

    private function isSecureRequest(array $server){
        return (
            (!empty($server['HTTPS']) && $server['HTTPS'] !== 'off') 
            ||
            (($server['SERVER_PORT'] ?? null) == 443)
        );
    }

    private function getSessionTokenFromCookie(): ?string
    {
        $token = $_COOKIE[self::SESSION_COOKIE] ?? null;

        error_log("token in getSession in tracker: " . $token);
    
        if (!is_string($token)) return null;

        $token = trim($token);

        if(!preg_match('/^[a-f0-9]{64}$/i', $token)) return null;

        return $token;
    }

    private function setSessionCookie(string $token, int $ttlSeconds): void 
    {
        $secure = $this-> isSecureRequest($_SERVER);

        error_log("secure session : " . $secure);

        setcookie(self::SESSION_COOKIE, $token, [
            'expires'   => time() + $ttlSeconds,
            'path'      => '/',
            'secure'    => $secure,
            'httponly'  => true,
            'samesite'  => 'Lax'
        ]);
    }

    public function track(): void 
    {
        //ip ophalen
        $ipObj = ClientIpResolver::getClientIp($_SERVER);
        $ip = $ipObj->haserror ? '' : $ipObj->ip;
        if ($ip === '') return;
     
        /**user agent */
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if ($ua === '' || self::isBot($ua)) return;

        /**path */
        $path = self::getBaseRequestUrl($_SERVER);
        if ($path === '') return;

        /**fingerprint - devicetype - browser- os */
        $fingerprint = $this->makeFingerprint($ip, $ua);
        $deviceType = $this->detectDeviceType($ua);
        ['browser' => $browser, 'os' => $os] = $this->detectBrowserAndOs($ua);

        $countryCode = null;
        if ($this->countryResolver !== null){
            $countryCode = $this->countryResolver->fromIp($ip);
        }

        $token = $this->getSessionTokenFromCookie();
        $ttl = AnalyticsConfig::SESSION_TTL_SECONDS;


        //visitor
        $visitorId = $this->VisitorSql->upsertVisitor(
            fingerprint:    $fingerprint,
            devicetype:     $deviceType,
            browser:        $browser,
            os:             $os,
            countryCode :   $countryCode
        );

        $referrerHost = $this->getReferrerHost($_SERVER);
        $utm = $this->getUtm($_GET);

        //session
        if ($token !== null){
            $sessionId = $this->SessionSql->findFreshSessionIdByToken($token, $ttl);
            if($sessionId !== null){
                $this->SessionSql->touchSessionByToken($token);
                /**Look wihtin active sessios if a page is already visited */
                $last = $this->PageViewSql->findLastPageview($sessionId);

                if ($last !== null){
                    $lastPath = (string) $last['path'];
                    $lastTime = strtotime((string) $last['occurred_at']) ?: null;
                    if ($lastTime !== null) {
                        $delta = time() - $lastTime;

                        if (($lastPath === $path) && ($delta < 2)){
                            /**zelfde pagina onder de twee seconden, geen update doen en ook geen insert */
                            /** cookie needs to be refreshed for inactivity time */
                            if (!headers_sent()) {
                                $this->setSessionCookie($token, $ttl);
                            }
                            return;
                           
                        }
                        /** pagina cannot be higher then in-active border session */
                        $duration = min($delta, AnalyticsConfig::SESSION_TTL_SECONDS);
                        $this->PageViewSql->updateDurationSeconds((int) $last['id'], $duration);

                    }
                }

                $insertPageViewActiefSes = $this->PageViewSql->insertPageView($sessionId, $path);
                /**update sessie cookie cuz if after 25 minutes someone hits the jukebox then reset inactivity time */
                if (!headers_sent()) $this->setSessionCookie($token, $ttl);
                return;
            }
        }
        
        $token = bin2hex(random_bytes(32));

        $sessionId = $this->SessionSql->createSession(
            visitorId: $visitorId,
            ladingPath: $path,
            referrerHost: $referrerHost,
            utmSource: $utm['utm_source'],
            utmMedium: $utm['utm_medium'],
            utmCampaign: $utm['utm_campaign'],
            token: $token
        );

        if ($sessionId === null) return;
        if (!headers_sent()) $this->setSessionCookie($token, $ttl);
        $this->PageViewSql->insertPageView($sessionId, $path);
    }
}
?>