<?php
declare(strict_types=1);
use GeoFort\Services\Http\GlobalBaseUrlProvider;
use GeoFort\Support\FlashStore;
use GeoFort\Enums\FlashTarget\FlashTargetContract;

function asset(string $path): string {
    $base = rtrim(GlobalBaseUrlProvider::get()->getBaseUrl() . '/');
    $p    = ltrim($path, '/');
    return $base . '/' . $p;
}

function render(string $path, array $vars = []): void {
    extract($vars, EXTR_SKIP);
    require $path;
}

function flash(FlashTargetContract $target): object 
{
    $payload = FlashStore::get($target->getValue());

    error_log(print_r($payload, true));

    return (object) [
        'hasMessage' => is_array($payload) && (trim((string)$payload['message']) !== ''),
        'message' => (string) ($payload['message'] ?? ''),
        'type' => (string) ($payload['type'] ?? 'error'),

    ];
}

?>