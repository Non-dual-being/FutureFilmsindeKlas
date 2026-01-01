<?php
declare(strict_types=1);
namespace GeoFort\Services\Analytics;

use GeoFort\Services\SQL\AnalyticsPageViewSQLService;
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

    public function __construct(
        private AnalyticsPageViewSQLService $PageViewSql,
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
                error_log("bot detected");
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

        $path = self::getBaseRequestUrl($_SERVER);

        $stoptrack = (
            (str_starts_with($path, '/dashboard')) 
            || 
            (str_starts_with($path, '/auth')) 
            || 
            (str_starts_with($path, '/error'))
        );

        if ($stoptrack) return false;

        if (preg_match(self::STOP_TRACK_FLAG, $path)) return false;

        $ua = $server['HTTP_USER_AGENT'] ?? '';
        
        if ($ua === '' || self::isBot($ua)) return false;

        return true;
        
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
        $sessionId = $this->SessionSql->findActiveSessionId(
                visitorId: $visitorId,
                ttlSeconds: AnalyticsConfig::SESSION_TTL_SECONDS
            );

        if ($sessionId === null){
            $sessionId = $this->SessionSql->createSession(
                visitorId: $visitorId,
                ladingPath: $path,
                referrerHost: $referrerHost,
                utmSource: $utm['utm_source'],
                utmMedium: $utm['utm_medium'],
                utmCampaign: $utm['utm_campaign']
            );
        } else {
            $this->SessionSql->touchSession($sessionId);
        }

        /**pageview */
        $this->PageViewSql->insertPageView($sessionId, $path);
            
    }
}
?>