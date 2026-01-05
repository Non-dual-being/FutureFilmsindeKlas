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
}
?>