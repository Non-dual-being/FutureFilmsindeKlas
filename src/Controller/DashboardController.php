<?php
declare(strict_types=1);
namespace GeoFort\Controller;
use GeoFort\Services\DashboardStatsService;
use GeoFort\ErrorHandlers\FlashMessageHandler;
use GeoFort\Services\ErrorHandlers\DashboardFlasher;
use GeoFort\Enums\FlashTarget\DashboardFlashTarget;

final class DashboardController
{
    public function __construct(
        private DashboardStatsService $stats
    ) {}

    public function index(): void 
    {
        $flasher  = new DashboardFlasher();
        $flashHandler = new FlashMessageHandler(DashboardFlashTarget::class);
        $pageData = $this->stats->getOverview();
        require __DIR__ 
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . 'views'
            . DIRECTORY_SEPARATOR . 'dashboard-view.php';
    }
}

?>