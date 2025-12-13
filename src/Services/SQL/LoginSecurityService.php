<?php
namespace GeoFort\Services\SQL;
use PDO;
use PDOException;
use Datetime;
use DateInterval;


class LoginSecurityService {
    private PDO $pdo;
    private const ATTEMPTS_BEFORE_LOCKOUT = 5;
    private const BASE_LOCKOUT_SECONDS = 120;
    private const HARD_LOCKOUT_TIME = [
        'week' => 7 * 24 * 3600,
        'year' => 365 * 24 * 3600
    ];


    
    public function __construct(PDO $pdo, $max = 5)
    {
        $this->pdo = $pdo;
    }

    private function errorLogException(string $e, string $context): void 
    {
        error_log("[SQL ERROR][$context] " . $e); 
    }

    /**
         * Controleert of een e-mailadres of IP-adres momenteel geblokkeerd is.
         * Geeft de resterende blokkadetijd in seconden terug, of 0 als er geen blokkade is.
         * @return int|null Null on error.
     */

    public function checkLockOutTime(string $email, string $ip): ?int {
        try{
            $sql = 
            "SELECT 
                lockout_until
            FROM
                login_attempts
            WHERE
                (email_address = :email OR ip_address = :ip)
            ORDER BY 
                lockout_until DESC
            LIMIT
                1";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['email' => $email, 'ip' => $ip]);
            $lockoutUntil = $stmt->fetchColumn();

            /**
             * fetchColumn pakt de eerte kolom (als je maar 1 select hebt is het die column)
             * false als de eerste rij met limit 1 geen waarde heeft anders de waarde
             */

            if ($lockoutUntil === false || $lockoutUntil === null) return 0;

            $now = new DateTime();
            $lockoutDate = new DateTime($lockoutUntil);
            return max(0, $lockoutDate->getTimestamp() - $now->getTimestamp());
            /**
             * aantal absolute seconden door de min (aantal sec sinds 1970)
             */

        }catch(PDOException | \Exception $e){
            $this->errorLogException($e->getMessage(), __FUNCTION__);
            return null;
        }
    }

    public function checkLockOutInfo(string $email, string $ip): ?object {
        try {
            $seconds = $this->checkLockOutTime($email, $ip);
            if ($seconds === null) return null;

            $failedSinceSuccess = (int)$this->getAttemptsSinceLastSucces($email, $ip);
            if ($failedSinceSuccess === null) return null;

            $left = max(0, self::ATTEMPTS_BEFORE_LOCKOUT - $failedSinceSuccess);

            return $this->getLockOutInfo($seconds, $failedSinceSuccess);

            
        } catch (PDOException | \Exception $e){
            $this->errorLogException($e->getMessage(), __FUNCTION__);
            return null;
        }
        

    }

        /**
     * Registreert een mislukte inlogpoging en past eventueel een lockout toe.
     * @return array|null ['attempts_left' => int, 'lockout_seconds' => int]
     */
    private function getLockOutInfo(int $seconds, int $attemptsLeft = 0): object {
        if ($seconds === 0){
            return (object) [
                'attempts_left' => $attemptsLeft,
                'lockout_seconds' => 0,
                'lockout_until' => null,
                'blocked' => false,
            ];

        } else {
            $now = new DateTime();
            $rawLockOut = $now->add(new DateInterval("PT{$seconds}S"));
            $date = $rawLockOut->format('Y-m-d H:i:s');
            return (object) [
                'attempts_left' => 0,
                'lockout_seconds' => $seconds,
                'lockout_until' => $date,
                'blocked' => true,
            ];

        }


    }

    private function getAttemptsSinceLastSucces(string $email, string $ip): ?int 
    {
        try {
            $sql =
            "SELECT
                MAX(attempt_time)
            FROM
                login_attempts
            WHERE
                email_address = :email
                    AND
                successful = 1
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['email' => $email]);
            $lastSuccessAt = $stmt->fetchColumn();
            $since = $lastSuccessAt ?: '1970-01-01 00:00:00';

            $sql =
            "SELECT
                count(*)
            FROM
                login_attempts
            WHERE
                email_address = :email
                    AND
                successful = 0
                    AND
                attempt_time > :since
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['email' => $email, 'since' => $since]);
            $failedSinceSuccess =  $stmt->fetchColumn();

            return $failedSinceSuccess ?: 0;
            
        } catch (PDOException | \Exception $e){
            $this->errorLogException($e->getMessage(), __FUNCTION__);
            return null;
        }
    }

    public function recordFailedAttempt(string $email, string $ip): ?object {
        try {

            if (!$this->pdo->inTransaction()){
                $this->pdo->beginTransaction();
            }

            /**Eerst kijken is er een actieve lockout zo ja tijd uitzitten tot de volgende poging */

            //pdo fout
            $remainingLockOutSeconds = $this->checkLockOutTime($email, $ip);
            if ($remainingLockOutSeconds === null) return null;

            if ($remainingLockOutSeconds > 0){
                return $this->getLockOutInfo($remainingLockOutSeconds);
            }  

            $sql = 
            "INSERT INTO 
                login_attempts (email_address, ip_address, successful)
            VALUES 
                (:email, :ip, 0) 
            ";

            $stmt = $this->pdo->prepare($sql);
            if (!$stmt->execute(['email' => $email, 'ip' => $ip])){
                $this->pdo->rollBack();
                return null;
            }
            
            $failedSinceSuccess = (int) $this->getAttemptsSinceLastSucces($email, $ip);

            if ($failedSinceSuccess === null) return null;

            $lockOutSeconds = 0;

            switch (true) {
                case $failedSinceSuccess >= 9 :
                    $lockOutSeconds = self::HARD_LOCKOUT_TIME['year'];
                    break;
                case $failedSinceSuccess >= 7  :
                     $lockOutSeconds = self::HARD_LOCKOUT_TIME['week'];
                     break;
                case $failedSinceSuccess >= self::ATTEMPTS_BEFORE_LOCKOUT :
                    $multifactor = (int)floor($failedSinceSuccess / self::ATTEMPTS_BEFORE_LOCKOUT);
                    $lockOutSeconds = (int) (self::BASE_LOCKOUT_SECONDS * pow(2, $multifactor)); 
                    break;
                default:
                    $lockOutSeconds = 0;

            }

            if  ($lockOutSeconds === 0){
                $this->pdo->commit();
                $attemptsLeft = max(0, self::ATTEMPTS_BEFORE_LOCKOUT - $failedSinceSuccess);
                return $this->getLockOutInfo(0, $attemptsLeft);

            } else {
                $lockOutInfo = $this->getLockOutInfo($lockOutSeconds);

                $sql =
                "UPDATE
                    login_attempts
                SET 
                    lockout_until = :lockout_until
                WHERE 
                    email_address = :email
                        AND
                    ip_address = :ip
                        AND
                    successful = 0
                ORDER BY
                    attempt_time DESC
                LIMIT
                    1
                ";

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(['email' => $email, 'ip' => $ip, 'lockout_until' => $lockOutInfo->lockout_until]);

                $this->pdo->commit();

                return $lockOutInfo;
                
            }

        } catch (PDOException | \Exception $e){
            if ($this->pdo->inTransaction()){
                $this->pdo->rollBack();
            }
            $this->errorLogException($e->getMessage(), __FUNCTION__);
            return null;
        }
    }

    public function recordSuccessfullogin(string $email, string $ip): ?bool {
        
        try{
            $this->pdo->beginTransaction();

            $sql =
            "INSERT INTO
                login_attempts (email_address, ip_address, successful, lockout_until)
            VALUES
                (:email, :ip, 1, NULL) 
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['email' => $email, 'ip' => $ip]);

            $sql = 
            "DELETE FROM
                login_attempts
            WHERE 
                (email_address = :email
                OR
                    ip_address = :ip)
                AND
                    successful = 0
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['email' => $email, 'ip' => $ip]);

            $this->pdo->commit();
            return true;

        } catch (PDOException | \Exception $e){
            if ($this->pdo->inTransaction()){
                $this->pdo->rollBack();
            }
            $this->errorLogException($e->getMessage(), __FUNCTION__);
            return null;
        }
        
      
    }
    
}
?>