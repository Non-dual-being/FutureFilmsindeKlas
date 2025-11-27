<?php
declare(strict_types=1);
namespace GeoFort\Services\Http;

final class GlobalBaseUrlProvider implements BaseUrlProvider {
    private static ?self $instance = null; /**singleton  */
    private string $environment;
    private string $baseUrl;
    private const MAP = [
        'development' => 'https://futurefilms.test', 
        'production'  => 'https://planetaryhealth.xyz/Futurefilmsindeklas'
    ];

    private function __construct(string $environment)
    {
       $this->environment = $environment;
       $this->baseUrl = self::MAP[$this->environment];
    }

    public static function init(string $environment)
    {
        if (self::$instance !== null){
            error_log("BaseUrlProvider already initialized by bootstrap, only one instance is allowed");
            return;
        }

        $env = strtolower($environment);

        if (!isset(self::MAP[$env])) 
            $env = 'production';
        self::$instance = new self($env);

        /**
         * todo: hier op productie zettenm zodat publieke pages werken, anders met een throw blokkeer de hele app 
         * 
         * */
        
    }

    public static function get(): self 
    {
        if (self::$instance === null){
            throw new \LogicException("GlobalBaseUrlProvider not initialized");
        }

        return self::$instance;
    }


    public function getBaseUrl(): string 
    {
        return $this->baseUrl;
    }

    public function getEnvironment(): string 
    {
        return $this->environment;
    }
}
?>