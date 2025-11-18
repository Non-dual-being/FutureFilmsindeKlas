<?php
declare(strict_types=1);
namespace GeoFort\Services\Validators;

final class ValidationResult
{
    public function __construct(
        public readonly ?string $value,
        public readonly array $errors,
        public readonly bool $haserror
    ) {}
}
?>