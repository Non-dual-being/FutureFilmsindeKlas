<?php
declare(strict_types=1);
namespace GeoFort\Controller;

use GeoFort\Services\Analytics\DashboardStatsService;
use GeoFort\Services\Analytics\DashboardRange;
use GeoFort\ErrorHandlers\FlashMessageHandler;
use GeoFort\ErrorHandlers\FormExceptionHandler;
use GeoFort\Services\ErrorHandlers\DashboardFlasher;
use GeoFort\Enums\FlashTarget\DashboardFlashTarget;


final class DashboardController
{
    public function __construct(
        private DashboardStatsService $stats
    ) {}

    public function index(): void 
    {
        try {
            $flasher  = new DashboardFlasher();
            $FlashHandler = new FlashMessageHandler(DashboardFlashTarget::class);
            $result = $this->stats->getOverview();
       
            if (!$result->success) $flasher->result($result->errorMessage ?? 'statistieken kon niet geladen worden');

            $pageData = $result->data ?? [
                'totals' => [
                    'totalVisitors'     => 0,
                    'visitorsLast7d'    => 0,
                    'visitorsLast30d'   => 0,
                ],
                'daily'     => [],
                'devices'   => []
            ];

            $currentUserEmail = (string) ($_SESSION['user_email'] ?? 'Unkown'); 
            $navItems = [
                '/dashboard/index.php' => 'overview'
            ];

            $activePage = '/dashboard/index.php';


            /**
             * Available from log-in
             * PHP SESSIE COOKie ensures that the values are available during session
             */

        } catch(FormExceptionHandler $e) {
            $FlashHandler->handleException($e);
        }

        require VIEW_PATH . '/layouts/dashboard.php';
    }
}

?>