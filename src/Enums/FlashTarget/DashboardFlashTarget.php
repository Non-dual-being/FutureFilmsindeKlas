<?php
declare(strict_types=1);
namespace GeoFort\Enums\FlashTarget;

enum DashboardFlashTarget: string implements FlashTargetContract {
    case Result       =   'result';
    case User         =   'user';
    case General      =   'general';

    public static function safeFrom(?string $target): self {
        return self::tryFrom($target ?? '') ?? self::General;
    }

    public static function getValues(): array
    {
        $values = [];
        foreach (self::cases() as $case){
            $values[$case->value] = $case->value;
        }

        return $values ?? [];
    }

    public function varName(): string {
        return self::FLASHMESSAGE_PREFIX . $this->value;
    }

    public function getValue(): string {
        return $this->value;
    }

    public static function getWrapperClass(): string {
        return self::CONTAINER_CLASS;
    }
}
?>