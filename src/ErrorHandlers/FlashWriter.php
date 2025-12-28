<?php 
declare(strict_types=1);
namespace GeoFort\ErrorHandlers;

use GeoFort\Enums\FlashTarget\FlashTargetContract;
use GeoFort\Support\FlashStore;

final class FlashWriter
{
    public function write(
        string $msg,
        string $msgType,
        FlashTargetContract $target
    ): void {
        FlashStore::add($target->getValue(), $msg, $msgType);
    }
}

?>