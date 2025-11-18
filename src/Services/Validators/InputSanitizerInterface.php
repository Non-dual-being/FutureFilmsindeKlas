<?php
declare(strict_types=1);
namespace GeoFort\Services\Validators;

interface InputSanitizerInterface
{
    public function sanitize(string $value): string;
}
?>