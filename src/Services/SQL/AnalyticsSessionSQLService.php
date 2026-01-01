<?php
declare(strict_types=1);
namespace GeoFort\Services\SQL;
use PDO;
use PDOException;

final class AnalyticsSessionSQLService {
    public function __construct(
        private PDO $pdo
    ) {}

    private function errorLog(string $Msg = '', string $context = ''): void {
        if ($Msg === '') return;

        error_log("[SQL-ERROR]" .   "[" . __CLASS__ . "]" .     "[$context]: $Msg");

    }

    public function findActiveSessionId(int $visitorId, int $ttlSeconds): ?int {
        try {
            $getSessionId = 
            "SELECT 
                id
            FROM
                analytics_session
            WHERE 
                visitor_id = :visitorId
                AND
                (
                    (ended_at IS NOT NULL AND ended_at >= (NOW() - INTERVAL :ttl SECOND))
                    OR
                    (ended_at IS NULL AND started_at >= (NOW() - INTERVAL :ttl SECOND))
                )
            ORDER BY
                COALESCE(endend_at, started_at) DESC
            LIMIT 1
            ";
            /**
             * COALESCE retourneert de eerste waarde die niet NULL is 
             * Is de sessie al geeindigd, kijk dan naar de laatste sessie die geeindigd is 
             * Anders kijk naar de laatste start tijd en pakt die sessie
             */

            /**
             * 
             */

            $ttlSeconds = max(60, $ttlSeconds);
            $getSessionId = str_replace(':tt', (string) $ttlSeconds, $sql);

                 /**
             * de explicite int in de paramater zorgt ervoor dat alleen een interger wordt geaccepteerd en dus kan de query niet gemanipuleert worden
             * een aanvaller kan niet "'600; DELETE FROM DOEN "
             * 
             */

            $stmt = $this->pdo->prepare($getSessionId);
            $stmt->execute([':visitor_id' => $visitorId]);

            $id = $stmt->fetchColumn();

            return $id === false
                ? null
                : (int) $id;

        } catch (PDOException $e){
            $this->errorLog($e->getMessage() ?? 'unkown error', __FUNCTION__);
            return null;

        }
    }

    public function createSession(
        int $visitorId,
        string $ladingPath,
        ?string $referrerHost,
        ?string $utmSource,
        ?string $utmMedium,
        ?string $utmCampaign
    ): ?int {
        try {
            $insertSession = 
                "INSERT INTO
                    analytics_sessions (
                        visitor_id,
                        started_at,
                        ended_at,
                        referrer_host,
                        landing_path,
                        utm_source,
                        utm_medium,
                        utm_campaign
                    ) VALUES (
                        :visitor_id,
                        NOW(),
                        NOW(),
                        :referrer_host,
                        :landing_path,
                        :utm_source,
                        :utm_medium,
                        :utm_campaign
                    )
                ";

                $stmt = $this->pdo->prepare($insertSession);
                $stmt->execute([
                    ':visitor_id'       => $visitorId,
                    ':referrer_host'    => $referrerHost,
                    ':landing_path'     => $ladingPath,
                    ':utm_source'       => $utmSource,
                    ':utm_medium'       => $utmMedium,
                    ':utm_campaign'     => $utmCampaign
                ]);

                return (int) $this->pdo->lastInsertId();

        } catch (PDOException $e){
            $this->errorLog($e->getMessage() ?? 'unkown error', __FUNCTION__);
            return null;
        }
    }

    public function touchSession(int $id): ?bool
    {

        try {
            $endSession =
            "UPDATE
                analytics_sessions
            SET
                ended_at = NOW()
            WHERE
                id = :id
            ";

            $stmt = $this->pdo->prepare($endSession);
            $stmt->execute([':id' => $id]);

            return (bool) $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            $this->errorLog($e->getMessage() ?? 'unkown error', __FUNCTION__);
            return null;
        }

    }
        
}
?>