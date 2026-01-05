<?php
declare(strict_types=1);
namespace GeoFort\Services\Analytics;

use DateTimeImmutable;

final class DashboardRange {
    public function __construct(
        public int $days,
        public string $from,
        public string $to
    ) {}

    public static function fromQuery(?string $range): self
    {
        $days = (int) ($range ?? 30);
        $days = in_array($day, [7, 14, 30, 90], true) ? $days : 30;

        $now  = new DateTimeImmutable();
        return new self(
            days: $days,
            from: $now->modify("-{$days} days")->format('Y-m-d 00:00:00'),
            to: $now->format('Y-m-d 23:59:29')
        );
    }
}


?>