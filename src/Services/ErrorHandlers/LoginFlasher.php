<?php
declare(strict_types=1);
namespace GeoFort\Services\ErrorHandlers;

use GeoFort\Enums\FlashTarget\LoginFlashTarget;
use GeoFort\ErrorHandlers\FlashWriter;

final class LoginFlasher {
    private readonly FlashWriter $writer;
    
    public function __construct()
    {
        $this->writer = new FlashWriter();
    }

      public function inactive(
        string $msg,
        string $type = 'success',
    ): void 
    {
        $this->writer->write($msg, $type, LoginFlashTarget::InActive);
    }

    public function inlogsubmit(
        string $msg,
        string $type = 'error',
    ): void 
    {
        $this->writer->write($msg, $type, LoginFlashTarget::Inlog_Submit);
    }

    public function general(string $msg, string $type = 'error'): void
    {
        $this->writer->write($msg, $type, LoginFlashTarget::General);
    }

}

?>