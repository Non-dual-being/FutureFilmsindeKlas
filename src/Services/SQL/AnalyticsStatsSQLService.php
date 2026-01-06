<?php
declare(strict_types=1);
namespace GeoFort\Services\SQL;

use PDO;
use PDOException;

final class AnalyticsStatsSQLService 
{
    public function __construct(private PDO $pdo)
    {  
    }

    private function errorLog(string $msg = '', string $context = ''): void
    {
        if ($msg === '') return;
        error_log("[SQL-ERROR]" . "[" . __CLASS__ . "]" . "[$context] : " . $msg);
    }

    public function countPageViews(string $fromDateTime, string $toDateTime): int
    {
        try{
            $pageviews =
            "SELECT
                COUNT(*)
            FROM
                analytics_pageviews
            WHERE
                occurred_at BETWEEN :from_date AND :to_date
            ";

            $stmt = $this->pdo->prepare($pageviews);
            $stmt->execute([':from_date' => $fromDateTime, 'to_date' => $toDateTime]);
            $count = $stmt->fetchColumn();

            return ($count === false)
                ? null
                : (int) $count;


        } catch (PDOException $e){
            $this->errorLog($e->getMessage() ?? '', __FUNCTION__);
            return null;
        }

    }

    public function countSessions(string $fromDateTime, string $toDateTime): ?int
    {
        try {
            $sessionCount =
            "SELECT
                COUNT(*)
            FROM
                analytics_sessions
            WHERE
                started_at BETWEEN :from_date AND :to_date
            ";
            $stmt = $this->pdo->prepare($sessionCount);
            $stmt->execute([':from_date' => $fromDateTime, ':to_date' => $toDateTime]);

            $SessionsCount = $stmt->fetchColumn();

            return ($SessionsCount === false)
                ?   null
                :   $SessionsCount;


        } catch (PDOException $e){
            $this->errorLog($e->getMessage() ?? '', __FUNCTION__);
            return null;
        }
    }

    public function getDailyPageViews(string $fromDateTime, string $toDateTime): ?array
    {
        try{
            $dailyPageViews =
                "SELECT 
                    DATE(occurred_at) as visit_day,
                    COUNT(*) as pageviews
                FROM
                    analytics_pageviews
                WHERE
                    occurred_at BETWEEN :from_date AND :to_date
                GROUP BY
                    visit_day
                ORDER BY
                    visit_day DESC
            ";

            $stmt = $this->pdo->prepare($dailyPageViews);
            $stmt->execute([
                ':from_date' => $fromDateTime,
                ':to_date'   => $toDateTime
            ]);
            $PageVieWCount = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $PageVieWCount;

        } catch (PDOException $e){
            $this->errorLog($e->getMessage() ?? '', __FUNCTION__);
            return null;
        }
    

    }

    public function getDailyUniqueVisitors(string $fromDateTime, string $toDateTime): ?array
    {
        try{
            $uniqueVisits =
            "SELECT
                DATE(p.occurred_at) AS visit_day,
                COUNT(DISTINCT s.visitor_id) AS unique_visitor
            FROM
                analytics_pageviews p
            INNER JOIN
                analytics_sessions s
            ON
                s.id = p.session_id 
            WHERE
                p.occurred_at BETWEEN :from_date AND :to_date
            GROUP BY
                visit_day
            ORDER BY
                visit_day
            ";

            $stmt = $this->pdo->prepare($uniqueVisits);
            $stmt->execute([':from_date' => $fromDateTime, ':to_date' => $toDateTime]);
            $dailyvisitcount = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $dailyvisitcount;


        } catch (PDOException $e){
            $this->errorLog($e->getMessage() ?? '', __FUNCTION__);
            return null;
        }

    }

    public function getTopPages(
        string $fromDateTime,
        string $toDateTime,
        int $limit = 20
    ): ?array
    {
        try{
            $limit = max(1, min(100, $limit));
            
            $topPages =
            "SELECT
                path,
                count(*) as views
            FROM
                analytics_pageviews
            WHERE
                occurred_at BETWEEN :from_date AND :to_date
            GROUP BY
                path
            ORDER BY
                views DESC
            LIMIT
                :limit_placeholder
            ";
            
            $topPages = str_replace(':limit_placeholder', (string) $limit, $topPages);
            $stmt = $this->pdo->prepare($topPages);
            $stmt->execute([
                ':from_date'        => $fromDateTime,
                ':to_date'          => $toDateTime,
            ]);

            $topPagesOverview = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $topPagesOverview;


        } catch (PDOException $e){
            $this->errorLog($e->getMessage() ?? '', __FUNCTION__);
            return null;
        }

    }

    public function getTopReferrers(
        string $fromDateTime,
        string $toDateTime,
        int $limit = 20
    ): ?array
    {
        try{
        $limit =  max(1, min(100, $limit));

        $topReferrers =
        "SELECT
            referrer_host,
            count(*) as visitor_sessions
        FROM
            analytics_sessions
        WHERE
            started_at BETWEEN :from_date AND :to_date
            AND
            (referrer_host IS NOT NULL AND referrer_host <> '')
        GROUP BY
            referrer_host
        ORDER BY
            visitor_sessions DESC
        LIMIT
            :limit_placeholder
        ";
        $topReferrers = str_replace(':limit_placeholder', (string) $limit, $topReferrers);

        /**
         * Some Dbs have ar not capable of binding params in limit clause
         * Explicit int in param and casting to string makes is safe to hardcode the limit
         */


        $stmt = $this->pdo->prepare($topReferrers);
        $stmt->execute([
            ':from_date'    => $fromDateTime,
            ':to_date'      => $toDateTime
        ]);

        $topRefered = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $topRefered;

        } catch(PDOException $e){
            $this->errorLog($e->getMessage() ?? '', __FUNCTION__);
            return null;
        }
        
    }

    public function getBounceRate(string $fromDateTime, string $toDateTime): ?array
    {
        try {
            $bounceRate =
            "SELECT
                count(*) AS total_sessions,
                SUM(CASE WHEN pv_count = 1 THEN 1 ELSE 0 END) AS bounces,
                ROUND(
                    100 * SUM(CASE WHEN pv_count = 1 THEN 1 ELSE 0 END) 
                    /
                    COUNT(*), 1
                ) AS bounce_rate_percent
            FROM (
                SELECT 
                    s.id,
                    COUNT(p.id) AS pv_count
                FROM
                    analytics_sessions s
                INNER JOIN
                    analytics_pageviews p
                ON
                    p.session_id = s.id
                WHERE
                    s.started_at BETWEEN :from_date AND :to_date
                GROUP BY
                    s.id
            ) t
            ";

            $stmt = $this->pdo->prepare($bounceRate);
            $stmt->execute([':from_date' => $fromDateTime, ':to_date' => $toDateTime]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC) ?? [];

            return [
                'total_sessions'        => (int) ($row['total_sessions'] ?? 0),
                'bounces'               => (int) ($row['bounces'] ?? 0),
                'bounce_rate_percent'   => (int) ($row['bounce_rate_percent'] ?? 0)
            ];

        } catch(PDOException $e){
            $this->errorLog($e->getMessage() ?? '', __FUNCTION__);
            return null;
        }
        
    }
    
}
?>