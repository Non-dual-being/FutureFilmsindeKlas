<?php
declare(strict_types=1);
namespace GeoFort\Enums\FlashTarget;

enum GeneralTarget: string implements FlashTargetContract {
    case General = 'general';

    public function getValue(): string {
        return $this->value;
    }

    public function varName(): string {
        return self::FLASHMESSAGE_PREFIX . $this->value;
    }

    public static function getWrapperClass(): string {
        return self::CONTAINER_CLASS;
    }
}
?>