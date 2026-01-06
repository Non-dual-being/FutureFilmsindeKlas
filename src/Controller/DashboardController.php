<?php
declare(strict_types=1);
namespace GeoFort\Controller;

use GeoFort\Services\Analytics\DashboardStatsService;
use GeoFort\Services\Analytics\DashboardRange;
use GeoFort\Services\ErrorHandlers\DashboardFlasher;
use GeoFort\Services\Http\HeaderRedirector;

use GeoFort\Enums\FlashTarget\DashboardFlashTarget;


final class DashboardController
{
    public const RANGEMAP = [
        7 => 'weekly',
        30 => 'monthly',
        90 => 'over 90 days'
    ];

    public function __construct(
        private DashboardStatsService $stats
    ) {}

    public function overview(): void 
    {
        $rangeRaw = $_GET['range'] ?? null;
        $range    = DashboardRange::fromQuery(is_string($rangeRaw) ? $rangeRaw : null);

        $invalidRange = isset($rangeRaw) && ((int) $rangeRaw !== $range->days);

        if ($invalidRange){
            HeaderRedirector::absolute(
                'dashboard/index.php',
                ['range' => $range->days]
            );
            exit;
        }

        $result   = $this->stats->getOverview($range);

        if (!$result->success) {
            $flasher = new DashboardFlasher();
            $flasher->result(
                $result->errorMessage ?? 'Unable to load stats'
            );
        }

        $rangeString = self::RANGEMAP[$range->days] ?? 'monthly';

        $pageData = $result->data ?? [
            'rangeDays' => $range->days,
            'from' => $range->from,
            'to' => $range->to,
            'cards' => [
                'totalVisitorsAllTime' => 0,
                'newVisitorsRange' => 0,
                'sessionsRange' => 0,
                'pageviewsRange' => 0,
                'bounceRate' => 0,
            ],
            'charts' => [
                'dailyNewVisitors' => [],
                'dailyPageviews' => [],
                'dailyUniqueVisitors' => [],
                'deviceDistribution' => [],
            ],
            'tables' => [
                'topPages' => [],
                'topReferrers' => [],
            ],
        ];

        $navItems = [
            'dashboard/index.php'       => 'Overview',
            'dashboard/pages.php'       => 'Pages',
            'dashboard/referrers.php'   => 'Referrers'
        ];

        $activePage         = 'dashboard/index.php';
        $currentUserEmail   =  (string) ($_SESSION['user_email'] ?? '');


        /**
         * Available from log-in
         * PHP SESSIE COOKie ensures that the values are available during session
         */

        require VIEW_PATH . '/layouts/dashboard.php';

    }
}

?>