<?php
declare(strict_types=1);
namespace GeoFort\Services\ErrorHandlers;

use GeoFort\Enums\FlashTarget\DashboardFlashTarget;
use GeoFort\ErrorHandlers\FlashWriter;

final class DashboardFlasher {
    private readonly FLashWriter $writer;
    
    public function __construct()
    {
        $this->writer = new FlashWriter();

        /**
         * SESSION BASED
         * PUBLIC FUNC ADD requires flashtargetcontrect not the construct 
         * Session flashkey with value key flash enum
         * Read and consume
         */
    }

      public function result(
        string $msg,
        string $type = 'success',
    ): void {
        $this->thrower->throw($msg, $type, DashboardFlashTarget::Result);
    }

    public function user(
        string $msg,
        string $type = 'error',
    ): void {
        $this->thrower->throw($msg, $type, DashboardFlashTarget::User);
    }

    public function general(
        string $msg, 
        string $type = 'error'): void
    {
        $this->thrower->throw($msg, $type, DashboardFlashTarget::General);
    }

}

?>