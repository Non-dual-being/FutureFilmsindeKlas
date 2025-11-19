<?php
declare(strict_types=1);
namespace GeoFort\security;

use GeoFort\Database\Connector;
use GeoFort\Service\SQL\AdminUsersSQLService;
use PDO;
use PDOException;

final class AuthMiddleWare 
{
    private const ALLOWED_BASEURLS = ['https://futurefilms.test', 'https://planetaryhealth.xyz/Futurefilmsindeklas'];
    private const ALLOWED_ENVS = ['development', 'production'];

    private static string $environment;
    private static string $baseUrl;
    private SessionQuard $Quard;
    private ?PDO $pdo = null;

    public function __construct()
    {
        
        try {
            $pdo = Connector::getConnection();
            $this->pdo = $pdo;
        } catch (PDOException $e) {
            error_log("Verbindingsfout in AuthMiddleware: " . $e->getMessage());
        }
        $timeOut = (int) $this->timeout();
        $this->Quard = new SessionQuard(
            pdo: $this->pdo,
            timeoutSeconds: $timeOut,
            strictSameSite: true,
            baseUrl: self::$baseUrl
        );
    }

    public static function setEnvironment(string $env): void {
        if (in_array($env, self::ALLOWED_ENVS)) 
            self::$environment = $env;
    }

    public static function setBaseUrl(string $baseUrl): void {
        $secureBaseUrl = rtrim($baseUrl, '/');
        if (in_array($secureBaseUrl, self::ALLOWED_BASEURLS)) 
            self::$baseUrl = $secureBaseUrl;
    }

    public static function getBaseUrl(): ?string {
        if (in_array(self::$baseUrl, self::ALLOWED_BASEURLS)){
            return self::$baseUrl;
        } else return null;
    }

    public static function getEnvironment(): ?string {
        if (in_array(self::$environment, self::ALLOWED_BASEURLS, true)){
            return self::$environment;
        } else return null;
    }

    private function timeout(): int {
        return match(self::$environment){
            'production' => 1800,
            'development' => 3600,
            default => 1800
        };
    }

    public function privateSession(): void {
        $this->Quard->privateSessionStart();
        $this->Quard->assertAuthenticated();
    }

    public function publicSession(): void {
        $this->Quard->publicSessionStart();
    }

    public function logoutSession(): never {
        $this->Quard->logoutAndRedirect();
    }
}
?>