<?php
declare(strict_types=1);
namespace GeoFort\Services\Analytics;

use GeoFort\Services\SQL\AnalyticsVisitorSQLService;
use GeoFort\Services\SQL\AnalyticsStatsSQLService;
use DateTimeImmutable;
use Throwable;


final class DashboardStatsService {
    /**
     * Constructor Property Promotion
     * -->kort voor AnalyticsVisitorSQLService $visitors met $this->vistors = $visitors
     */
    public function __construct(
        private AnalyticsVisitorSQLService $visitors,
        private AnalyticsStatsSQLService $stats
    ){}

    public function getOverview(DashboardRange $range): DashboardOverviewResult
    {
        try {
            //total visitors
            $totalVisitorsAllTime = (int) ($this->visitors->getAllVisitors() ?? 0);
            
            //visitors-based range metrics
            $newVisitorRange      = (int) ($this->visitors->countTotalVisitorsSince(
                $range->from
            ) ?? 0);
            
            $dailyNewVisitors     = $this->visitors->getDailyNewVisitors(
                                            from: substr($range->from, 0, 10),
                                            to: substr($range->to, 0, 10)
                                        ) ?? [];

            $deviceDistribution = $this->visitors->getDeviceDistribution() ?? [];

            //sessions - pageviews - bounce (rangebased)
            $pageviewsRange       = (int) $this->stats->countPageViews($range->from, $range->to);
            $sessionsRange        = (int) $this->stats->countSessions($range->from, $range->to);

            $bounce               = $this->stats->getBounceRate($range->from, $range->to);
            $bounceRate           = (float) ($bounce['bounce_rate_percent'] ?? 0.0);

            $dailyPageViews       = $this->stats->getDailyPageViews($range->from, $range->to) ?? [];
            $dailyUniqueVisitors  = $this->stats->getDailyUniqueVisitors($range->from, $range->to) ?? [];

            $topPages             = $this->stats->getTopPages($range->from, $range->to, 10) ?? [];
            $topReferrers         = $this->stats->getTopReferrers($range->from, $range->to, 10) ?? [];

            return new DashboardOverviewResult(
                success: true,
                data: [
                    'rangeDays' => $range->days,
                    'from' => $range->from,
                    'to' => $range->to,

                    'cards' => [
                        'totalVisitorsAllTime' => $totalVisitorsAllTime,
                        'newVisitorsRange' => $newVisitorRange,
                        'sessionsRange' => $sessionsRange,
                        'pageviewsRange' => $pageviewsRange,
                        'bounceRate' => $bounceRate,
                    ],

                    // Je kunt kiezen: je huidige chart blijft dailyNewVisitors,
                    // maar we voegen ook traffic chart data toe:
                    'charts' => [
                        'dailyNewVisitors' => $dailyNewVisitors,
                        'dailyPageviews' => $dailyPageViews,
                        'dailyUniqueVisitors' => $dailyUniqueVisitors,
                        'deviceDistribution' => $deviceDistribution,
                    ],

                    'tables' => [
                        'topPages' => $topPages,
                        'topReferrers' => $topReferrers,
                    ],
                ],
                errorMessage: null

            );
        } catch (Throwable $e){
            error_log('DashboardStatsService encounterd errors in building data: ' . $e->getMessage());
            return new DashboardOverviewResult(
                success: false,
                data: null,
                errorMessage: 'unable to load stats'
            );
        }
       
        
    }
}
?>