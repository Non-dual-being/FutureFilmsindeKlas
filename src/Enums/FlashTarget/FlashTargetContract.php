<?php
declare(strict_types=1);
namespace GeoFort\Enums\FlashTarget;

interface FlashTargetContract 
{
    public const FLASHMESSAGE_PREFIX = 'Flashmessage_'; // Centraal gedefinieerd
    public const CONTAINER_CLASS = "FlashTargetWrapper";
    
    public function getValue(): string;

    public function varName(): string;

    public static function getWrapperClass(): string;

}
?>