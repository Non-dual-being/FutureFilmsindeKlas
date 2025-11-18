<?php
declare(strict_types=1);
namespace GeoFort\Services\Validators;

final class InputValidator {
    private InputSanitizerInterface $genericSanitizer;
    private InputSanitizerInterface $passwordSanitizer;

    public function __construct(
        ?InputSanitizerInterface $genericSanitizer = null,
        ?InputSanitizerInterface  $passwordSanitizer = null
    ){
        $this->genericSanitizer = $genericSanitizer ?? new BasicSanitizer();
        $this->passwordSanitizer = $passwordSanitizer ?? new PasswordSanitizer();
    }

    public function sanitizeAndLimit(
        string $value,
        int $maxLength,
        string $fieldName = 'error',
        ?InputSanitizerInterface $sanitizer = null
    ): ValidationResult {
        $sanitizer ??= $this->$genericSanitizer;
        $errors = [];
        $sanitized = $sanitizer->sanitize($value);

        if (mb_strlen($sanitized, 'UTF-8') > $maxLength) {
            $errors[$fieldName] = ucfirst($fieldName) . ": mag niet langer dan {$maxLength} tekens.";
            return new ValidationResult(null, $errors, true);
        }

        return new ValidationResult($sanitized, [], false);
    }

    public function validateEmail(
        string $email,
        int $maxLength = 254,
        string $fieldName = 'email'

    ): ValidationResult {
        $res = $this->sanitizeAndLimit($email, $maxLength, $fieldName, $this->genericSanitizer);
        if ($res->haserror) return $res;

        $errors = [];

        /**uc first makes the first char in string caps */

        if ($res->value === ''){
            $errors[$fieldName] = ucfirst($fieldName) . ": is verplicht.";
            return new ValidationResult(null, $errors, true);
        }

        if (filter_var($res->value, FILTER_VALIDATE_EMAIL) === false){
            $errors[$fieldName] = ucfirst($fieldName) . ": is ongeldig.";
            return new ValidationResult(null, $errors, true);
        }

        return new ValidationResult($res->value, [], false);

    }

    public function validatePassword(
        string $password,
        int $minLength = 8,
        int $maxLength = 4096,
        bool $requireSpecial = true,
        string $fieldName = 'password'
    ): ValidationResult {
        $erros = [];
        $sanitized = $this->passwordSanitizer->sanitize($password); /**only trim */
        $len = mb_strlen($sanitized, 'UTF-8');

        if ($len === 0 || $len < $minLength || $len > $maxLength) {
            $errors[$fieldName] = ucfirst($fieldName) . ": Vul een wachtwoord in van minimaal {$minLength} tekens en max {$maxLength} tekens";
            return new ValidationResult(null, $errors, true);
        }

        if(!preg_match('/\p{Ll}/u', $sanitized)){
            $errors[$fieldName] = ucfirst($fieldName) . ": moet minstens 1 kleine letter bevatten";
            return new ValidationResult(null, $errors, true);
        };

        if ($requireSpecial && !preg_match('/[^\p{L}\p{N}]/u', $sanitized)){
            $errors[$fieldName] = ucfirst($fieldName) . ": moet minstens 1 special teken bevetatten bevatten";
            return new ValidationResult(null, $errors, true);
        }

        return new ValidationResult($sanitized, [], false);

    }
}

?>