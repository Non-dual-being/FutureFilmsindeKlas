<?php
declare(strict_types=1);
namespace GeoFort\Services\ErrorHandlers;

use GeoFort\Enums\FlashTarget\DashboardFlashTarget;

final class DashboardFlasher {
    private readonly FLashThrower $thrower;
    
    public function __construct()
    {
        $this->thrower = new FlashThrower(DashboardFlashTarget::class);
    }

      public function result(
        string $msg,
        string $type = 'success',
        bool $throw = true
    ): void {
        $this->thrower->throw($msg, $type, DashboardFlashTarget::Result, $throw);
    }

    public function user(
        string $msg,
        string $type = 'error',
        bool $throw = true
    ): void {
        $this->thrower->throw($msg, $type, DashboardFlashTarget::User, $throw);
    }

    public function general(string $msg, string $type = 'error', bool $throw = true): void
    {
        $this->thrower->throw($msg, $type, DashboardFlashTarget::General, $throw);
    }

}

?>