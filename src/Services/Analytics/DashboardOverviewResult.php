<?php
declare(strict_types=1);
namespace GeoFort\Services\Analytics;

final class DashboardOverviewResult {
    public function __construct(
        public bool $success,
        public ?array $data = null,
        public ?string $errorMessage = null
    ) {}
}
?>