<?php
declare(strict_types=1);
namespace GeoFort\Services\SQL;

use PDO;
use PDOException;

final class AnalyticsPageviewSQLService {
    public function __construct(
        private PDO $pdo
    ){}

    private function errorLog(string $Msg = '', string $context = ''): void {
        if ($Msg === '') return;
        error_log("[SQL-ERROR]" .   "[" . __CLASS__ . "]" .     "[$context]: $Msg");
    }

    public function insertPageView(int $sessionId, string $path): ?int {
        try {
            $path = substr($path, 0, 255);
            $insertPageView =
            "INSERT INTO
                analytics_pageviews (
                    session_id,
                    occurred_at,
                    path,
                    duration_seconds
                ) VALUES (
                    :session_id,
                    NOW(),
                    :path,
                    NULL
                )
            ";

            $stmt = $this->pdo->prepare($insertPageView);
            $stmt->execute([
                ':session_id' => $sessionId,
                ':path'       => $path
            ]);

            return (int) $this->pdo->lastInsertId();


        } catch(PDOException $e){
            $this->errorLog($e->getMessage() ?? 'unkown error', __FUNCTION__);
            return null;
        }
    }

    public function findLastPageview(int $sessionId): ?array {
        try {
            $pageview = 
            "SELECT
                id, path, occurred_at
            FROM
                analytics_pageviews
            WHERE
                session_id = :session_id
            ORDER BY
                occurred_at DESC,
                id DESC
            LIMIT
             1
            ";
            
            $stmt = $this->pdo->prepare($pageview);
            $stmt->execute([
                ':session_id' => $sessionId
            ]);
            $lastPageview = $stmt->fetch(PDO::FETCH_ASSOC);
            return ($lastPageview === false)
                ? null 
                : $lastPageview;

        } catch(PDOException $e){
            $this->errorLog($e->getMessage() ?? 'unkown error', __FUNCTION__);
            return null;
        } 
    }

    public function updateDurationSeconds(int $pageviewId, int $durationSeconds): bool {
        try {

            $durationSeconds = max(0, $durationSeconds);

            $updatePageviewDuration = 
            "UPDATE
                analytics_pageviews
            SET
                duration_seconds = :pageview_duration
            WHERE
                id = :pageview_id
            ";

            $stmt = $this->pdo->prepare($updatePageviewDuration);
            $stmt->execute([
                ':pageview_duration' => $durationSeconds,
                ':pageview_id'       => $pageviewId
            ]);

            return true;

        } catch(PDOException $e){
            $this->errorLog($e->getMessage() ?? 'unkown error', __FUNCTION__);
            return false;
        } 

    }
}
?>