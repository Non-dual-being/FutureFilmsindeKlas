<?php
declare(strict_types=1);
require_once __DIR__ . '/../bootstrap.php';

use GeoFort\security\AuthMiddleWare;


$auth = new AuthMiddleWare();
$auth->publicSession();

$deny = function (
    int $status = 403, $context="unauthorized logout"
): never {
    http_response_code($status);
    error_log($context);
    exit('Forbidden');
};


if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST'){
    $deny(405, "wrong request");
}

$posted = $_POST['csrf'] ?? '';
$stored = $_SESSION['csrf'] ?? '';

$flag = (
    (!is_string($posted)) || ($posted === '')
                    ||
    (!is_string($stored)) || ($stored === '') 
);

if ($flag) $deny(403);

if (!hash_equals($stored, $posted)) $deny(403);

$auth->logoutSession();
?>