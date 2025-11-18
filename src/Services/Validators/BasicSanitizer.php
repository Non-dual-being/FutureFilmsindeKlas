<?php
declare(strict_types=1);
namespace GeoFort\Services\Validators;
final class BasicSanitizer implements InputSanitizerInterface {
    public function __construct(
        private readonly bool $stripTags = true,
        private readonly bool $stripSlashes = true
    ){}

    public function sanitize(string $value): string 
    {
        $v = trim($value);
        if ($this->stripSlashes) 
            $v = stripslashes($v);
        if ($this->stripTags) 
            $v = strip_tags($v);

        return $v;
    }
}
?>