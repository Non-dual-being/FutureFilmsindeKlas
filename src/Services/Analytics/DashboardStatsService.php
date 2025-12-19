<?php
declare(strict_types=1);
namespace GeoFort\Services\Analytics;

use GeoFort\Services\SQL\AnalyticsVisitorSQLService;
use DateTimeImmutable;


final class DashboardStatsService {
    /**
     * Constructor Property Promotion
     * -->kort voor AnalyticsVisitorSQLService $visitors met $this->vistors = $visitors
     */
    public function __construct(
        private AnalyticsVisitorSQLService $visitors
    ){}

    public function getOverview(): array 
    {
        try {
            $now = new DateTimeImmutable();
            $totals = [
                'totalVisitors' => $this->visitors->countTotalVisitors(),
                'visitorsLast7d' => $this->visitors->countVisitorsSince(
                    $now->modify('-7 days')->format('Y-m-d 00:00:00')
                ),
                'visitorsLast30d' => $this->visitors->countVisitorsSince(
                    $now->modify('-30 days')->format('Y-m-d 00:00:00')
                )

            ];

            $daily = $this->visitors->getDailyNewVisitors(
                from:  $now->modify('-14 days')->format('Y-m-d'),
                to :  $now->format('Y-m-d')
            );

            $devices = $this->visitors->getDeviceDistribution();

            return new DashboardOverviewResult(
                success: true,
                data: [
                    'totals'  => $totals,
                    'daily'   => $daily,
                    'devices' => $devices,
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