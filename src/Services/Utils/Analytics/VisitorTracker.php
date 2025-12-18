<?php
declare(strict_types=1);
namespace GeoFort\Services\Utils\Analytics;
use GeoFort\Services\SQL\AnalyticsVisitorSQLService;

final class VisitorTracker {
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
    

    public function __construct(
        private AnalyticsVisitorSQLService $visitorTable,
        private string $salt
    ) {}

    public function track(): void 
    {
        if(PHP_SAPI === 'cli') return;

        /**
         * cli heeft aan de server vanuit een command line omgeving wordt gebruikt en dat er dus geen bezoeker is
         * 
         */

        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $uri = $_SERVER['REQUEST_URI']      ?? '';

        if ($ua === '' || $this->isBot($ua)) return;

        $fingerprint = $this->makeFingerprint($ip, $ua);
        $deviceType = $this->detectDeviceType($ua);
        [
            'browser' => $browser,
            'os'      => $os
        ] = $this->detectBrowserAndOs($ua);

        $this->visistorTable->upsertVisitor(
            fingerprint : $fingerprint,
            deviceType  : $deviceType,
            browser     : $browser,
            countryCode : null
        );
    }

    private function makeFingerprint(string $ip, string $ua): string 
    {
        return hash('sha256', $ip . '|' . $ua . '|' . $this->salt);
    }

    private function detectDeviceType(sting $ua): string 
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

    private function isBot(string $ua): bool 
    {
        $ua = strtolower($ua);

        foreach(self::BOTFRAGMENTS as $fragment){
            if (str_contains($ua, $fragment)){
                return true;
            }
        }

        return false;

    }

}

?>