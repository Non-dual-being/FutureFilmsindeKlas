<?php
declare(strict_types=1);
use GeoFort\Services\Http\GlobalBaseUrlProvider;

function asset(string $path): string {
    $base = rtrim(GlobalBaseUrlProvider::get()->getBaseUrl() . '/');
    $p    = ltrim($path, '/');
    return $base . '/' . $p;
}
?>