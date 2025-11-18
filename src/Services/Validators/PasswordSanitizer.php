<?php
declare(strict_types=1);
namespace GeoFort\Services\Validators;

final class PasswordSanitizer implements InputSanitizerInterface {
    public function sanitize(string $value): string {
        return trim($value);
    }
}
?>