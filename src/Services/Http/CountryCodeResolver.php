<?php
declare(strict_types=1);
namespace GeoFort\Services\Http;
use GeoIp2\Database\Reader;

final class CountryCodeResolver {
    public function __construct(private string $dbPath) {}

    private function errorLogger(string $message = '', string $context = 'CountryCodeResolver'): void {
        error_log("[CountryResolver][{$context} - error] : {$message}" );
    }

    public function fromIp(?string $ip): ?string {
        if ($ip === null || trim($ip) === '') return null;

        $invalidIp = (
                filter_var(
                $ip,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            ) === false
        );

        if ($invalidIp) return null;

        if (!is_file($this->dbPath)){
            error_log('Geo DB Missing at' . $this->dbPath);
            return null;
        }

        try {
            $reader = new Reader($this->dbPath);
            $record = $reader->country($ip);
            $code   = $recoder->country->isoCode;

            if (!is_string($code) || $code === '') return null;

            return strtoupper($code);

        } catch (\Throwable $e) {
            $this->errorLogger($e->getMessage() ?? 'error in fromip', __FUNCTION__);
            return null;
        }

    }

}
?>