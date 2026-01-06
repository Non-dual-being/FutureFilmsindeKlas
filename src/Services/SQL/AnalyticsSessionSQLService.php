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
                analytics_sessions
            WHERE 
                visitor_id = :visitor_id
                AND
                (
                    (ended_at IS NOT NULL AND ended_at >= (NOW() - INTERVAL :ttl SECOND))
                    OR
                    (ended_at IS NULL AND started_at >= (NOW() - INTERVAL :ttl SECOND))
                )
            ORDER BY
                COALESCE(ended_at, started_at) DESC
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
            $getSessionId = str_replace(':ttl', (string) $ttlSeconds, $getSessionId);

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
        ?string $utmCampaign,
         string $token
    ): ?int {
        try {
            $ladingPath = substr($ladingPath, 0, 255);
            $token = trim($token);

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
                        utm_campaign,
                        session_token
                    ) VALUES (
                        :visitor_id,
                        NOW(),
                        NOW(),
                        :referrer_host,
                        :landing_path,
                        :utm_source,
                        :utm_medium,
                        :utm_campaign,
                        :token
                    )
                ";

                $stmt = $this->pdo->prepare($insertSession);
                $stmt->execute([
                    ':visitor_id'       => $visitorId,
                    ':referrer_host'    => $referrerHost,
                    ':landing_path'     => $ladingPath,
                    ':utm_source'       => $utmSource,
                    ':utm_medium'       => $utmMedium,
                    ':utm_campaign'     => $utmCampaign,
                    ':token'            => $token
                ]);

                return (int) $this->pdo->lastInsertId();

        } catch (PDOException $e){
            $this->errorLog($e->getMessage() ?? 'unkown error', __FUNCTION__);
            return null;
        }
    }

    public function touchSessionById(int $id): ?bool
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

    public function touchSessionByToken(string $token): ?bool
    {

        try {
            $token = trim($token);
            if ($token === '') throw new PDOException("Empty token provided");

            $endSession =
            "UPDATE
                analytics_sessions
            SET
                ended_at = NOW()
            WHERE
                session_token = :token
            ";

            $stmt = $this->pdo->prepare($endSession);
            $stmt->execute([':token' => $token]);

            return (bool) $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            $this->errorLog($e->getMessage() ?? 'unkown error', __FUNCTION__);
            return null;
        }

    }

    public function findSessionByToken(string $token): ?array {
        try {
            $token = trim($token);
            if ($token === '') throw new PDOException("Provided token is empty");

            $lookupByToken = 
            "SELECT
                *
            FROM
                analytics_sessions
            WHERE
                session_token = :token
            LIMIT
                1
            ";
            $stmt = $this->pdo->prepare($lookupByToken);
            $stmt->execute([':token' => $token]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return ($row === false)
                ? null
                : $row;
            
        
            /**
             * fetch gebruiken hier je hebt maar 1 rij nodig anders moet je indexeren met $data[0] 
             */

        } catch (PDOException $e) {
            $this->errorLog($e->getMessage() ?? 'unkown error', __FUNCTION__);
            return null;
        }
    }

    public function isSessionStillFresh(int $sessionId, int $ttlSeconds): ?bool{
        try {
            $ttlSeconds = max(60, (int) $ttlSeconds);
            $sessionStillFresh =
            "SELECT
                id
            FROM
                analytics_sessions
            WHERE
                visitor_id = :visitor_id
            AND
                (ended_at IS NOT NULL AND ended_at >= (NOW() - INTERVAL :ttl SECOND))
            ORDER BY 
                ended_at
            LIMIT 1
            ";

            $sessionStillFresh = str_replace(':ttl', (string) $ttlSeconds, $sessionStillFresh);

            $stmt = $this->pdo->prepare($sessionStillFresh);
            $stmt->execute([':visitor_id' => $visitorId]);
            $id = $stmt->fetchColumn();

            return ($id === false)
                ? false
                : true;

        } catch (PDOException $e) {
            $this->errorLog($e->getMessage() ?? 'unkown error', __FUNCTION__);
            return null;
        }
    }

    public function findFreshSessionIdByToken(string $token, int $ttlSeconds): ?int {
        try{
            $token = trim($token);
            if ($token === '') throw new PDOException("Token provided is empty");

            $ttlSeconds = max(60, $ttlSeconds);

            $findFreshSessionByToken = 
            "SELECT
                id
            FROM
                analytics_sessions
            WHERE
                session_token = :token
                AND ended_at IS NOT NULL
                AND ended_at >= (NOW() - INTERVAL :ttl SECOND)
            ORDER BY 
                ended_at DESC
            LIMIT
                1
            ";

            $findFreshSessionByToken = str_replace(':ttl', (string) $ttlSeconds, $findFreshSessionByToken);

            $stmt = $this->pdo->prepare($findFreshSessionByToken );
            $stmt->execute([':token' => $token]);
            $id = $stmt->fetchColumn();

            return ($id === false)
                ? null
                : (int) $id;
        } catch (PDOException $e) {
            $this->errorLog($e->getMessage() ?? 'unkown error', __FUNCTION__);
            return null;
        }

    }

    public function findSessionIdByToken(string $token): ?int {
        try {
            $token = trim($token);
            if ($token === '') throw new PDOException("Provided token is empty");

            $lookupByToken = 
            "SELECT
                id
            FROM
                analytics_sessions
            WHERE
                session_token = :token
            LIMIT
                1
            ";
            $stmt = $this->pdo->prepare($lookupByToken);
            $stmt->execute(['token' => $token]);
            $id= $stmt->fetchColumn();

            return ($id === false)
                ? null
                : $id;
            
        
            /**
             * fetch gebruiken hier je hebt maar 1 rij nodig anders moet je indexeren met $data[0] 
             */

        } catch (PDOException $e) {
            $this->errorLog($e->getMessage() ?? 'unkown error', __FUNCTION__);
            return null;
        }
    }
        
}
?>