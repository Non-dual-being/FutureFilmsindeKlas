<?php
declare(strict_types=1);
namespace GeoFort\Services\ErrorHandlers;

use GeoFort\Enums\FlashTarget\LoginFlashTarget;

final class LoginFlasher {
    private readonly FLashThrower $thrower;
    
    public function __construct()
    {
        $this->thrower = new FlashThrower(LoginFlashTarget::class);
    }

      public function inactive(
        string $msg,
        string $type = 'success',
        bool $throw = true
    ): void {
        $this->thrower->throw($msg, $type, LoginFlashTarget::InActive, $throw);
    }

    public function inlogsubmit(
        string $msg,
        string $type = 'error',
        bool $throw = true
    ): void {
        $this->thrower->throw($msg, $type, LoginFlashTarget::Inlog_Submit, $throw);
    }

    public function general(string $msg, string $type = 'error', bool $throw = true): void
    {
        $this->thrower->throw($msg, $type, LoginFlashTarget::General, $throw);
    }

}

?>