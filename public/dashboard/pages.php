<?php
require_once __DIR__ . '/../bootstrap.php';
use GeoFort\Services\Htpp\GlobalBaseUrlProvider;

header(
    'Location: ' . htmlspecialchars(
        rtrim(GlobalBaseUrlProvider::get()->getBaseUrl(), '/')
    ) . '/dashboard/index.php', 
    true, 
    302
);
/**singleton, get instante, then called method in provider */
exit;
?>