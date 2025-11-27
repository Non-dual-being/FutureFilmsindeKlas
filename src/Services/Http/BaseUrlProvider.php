<?php
declare(strict_types=1);
namespace GeoFort\Services\Http;

interface BaseUrlProvider
{
    public function getBaseUrl(): string;
}
?>