<?php
declare(strict_types=1);
namespace GeoFort\Services\SQL;

use PDO;
use PDOException;

final class AnalyticsVisitorSQLService {
    private PDO $pdo;

    public function __construct(PDO $pdo){
        $this->pdo = $pdo;
    }

    private function errorLogException(string $message = '', string $context = "AnalyticsVisitorSQLService"): void {
        error_log("[Analytics][{$context} - error]: {$message}");
    }

    public function upsertVisitor(
        string $fingerprint,
        string $devicetype,
        ?string $browser,
        ?string $os,
        ?string $countryCode
    ): ?int {

        try {
            $exists =
            "SELECT
                id
            FROM
                analytics_visitors
            WHERE
                fingerprint = :fp
            LIMIT
                1
            ";

            $stmt = $this->pdo->prepare($exists);
            $stmt->execute(['fp' => $fingerprint]);
            $id = $stmt->fetchColumn();


            /**
             * fetchColumn zonder arguementen pakt de eerste column en de eerste rij
             * bij de tweede aanroep zou fetchcolumn van dezelfde column de tweede rij pakken
             * bij geen waarde krijg je false
             */

            if ($id !== false)  {
                $update = 
                "UPDATE
                    analytics_visitors
                SET
                    last_seen_at = NOW(),
                    device_type  = :device_type,
                    browser      = :browser,
                    os           = :os,
                    country_code = :country
                WHERE
                    id = :id
                ";

                $stmt = $this->pdo->prepare($update);
                $stmt->execute([
                    'device_type' => $devicetype,
                    'browser'     => $browser,
                    'os'          => $os,
                    'country'     => $countryCode,
                    'id'          => $id
                ]);

                return (int) $id;
            }

            $insert =
            "INSERT INTO
                analytics_visitors (
                    fingerprint,
                    first_seen_at,
                    last_seen_at,
                    device_type,
                    browser,
                    os,
                    country_code
                )
            VALUES  (
                    :fp,
                    NOW(),
                    NOW(),
                    :device_type,
                    :browser,
                    :os,
                    :country
                )
            ";

            $stmt = $this->pdo->prepare($insert);
            if (!$stmt->execute([
                'fp'            => $fingerprint,
                'device_type'   => $devicetype,
                'browser'       => $browser,
                'os'            => $os,
                'country'       => $countryCode 
            ])) return null; //not needed cuz of PDO PDO::ERRMODE_EXCEPTION set in connetor

            return (int) $this->pdo->lastInsertId();

        } catch (PDOException $e){
            $this->errorLogException($e->getMessage(), "checkBlockedIP");
            return null;
        }
    }

        public function countTotalVisitorsSince(string $fromDateTime): ?int {
        try{
            $count = 
            "SELECT 
                COUNT(*)
            FROM
                analytics_visitors
            WHERE
                first_seen_at >= :mydate
            ";

            $stmt = $this->pdo->prepare($count);
            $stmt->execute([':mydate' => $fromDateTime]);
            return (int) $stmt->fetchColumn(); // false is cast to zero with int

        } catch (PDOException $e){
            $this->errorLogException($e->getMessage(), __FUNCTION__);
            return null;
        }
                
    }

    public function getAllVisitors(): ?int {
        try {
            $count = 
            "SELECT 
                COUNT(*)
            FROM
                analytics_visitors
            ";

            $stmt = $this->pdo->query($count);
            return $stmt->fetchColumn();

        } catch (PDOException $e) {
            $this->errorLogException($e->getMessage(), __FUNCTION__);
            return null;

        }
    }


    public function getDailyNewVisitors(string $from, string $to): ?array {

        try {
            $vistorsbyday =
            "SELECT 
                DATE(first_seen_at) AS day,
                count(*) AS visitors
            FROM
                analytics_visitors
            WHERE
                first_seen_at BETWEEN :from AND :to
            GROUP BY
                day
            ORDER BY
                day 
            ";
            
            $stmt = $this->pdo->prepare($vistorsbyday);
            $stmt->execute([
                'from' => $from,
                'to'   => $to
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC); //FETCH ASSOC is the default set in connector

        } catch (PDOException $e){
            $this->errorLogException($e->getMessage(), __FUNCTION__);
            return null;
        }
       
    }

    public function getDeviceDistribution(): ?array {
        try {
            $devices =
            "SELECT
                device_type,
                count(*) as visitors
            FROM
                analytics_visitors
            GROUP BY
                device_type
            ORDER BY
                visitors DESC
            ";

            $stmt = $this->pdo->query($devices);
            /**
             * query direcly leads to a pdostatemet-> save to use here cuz no users paramaters
             */
            return $stmt->fetchAll();

        }  catch (PDOException $e){
            $this->errorLogException($e->getMessage(), __FUNCTION__);
            return null;
        }
    }

}
?>