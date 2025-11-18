<?php
namespace GeoFort\Services\SQL;
use PDO;


class LoginAttemptsSQLService {
    private PDO $pdo;
    private readonly int $MAX_ATTEMPTS;
    
    public function __construct(PDO $pdo, $max = 5)
    {
        $this->pdo = $pdo;
        $this->MAX_ATTEMPTS = $max;
    }

    private function errorLogException(string $e, string $context): void 
    {
        error_log("[SQL ERROR][$context] " . $e); 
    }

    public function checkBlockedIP(string $ip): ?bool {
        try {
            $SQL = 
            "SELECT 
                blocked 
            FROM 
                login_attempts 
            WHERE 
                ip_address = :ip_address
            ORDER BY
                attempt_time DESC
            LIMIT 1";


            $stmt = $this->pdo->prepare($SQL);
            $stmt->execute(['ip_address' => $ip]);
            $result = $stmt->fetchColumn();
            /**geen records */
            if ($result === false) return false;

            /** 0 geen block 1 wel block en wel ip  */
            return (bool) $result;


        } catch (PDOException $e){
            $this->errorLogException($e->getMessage(), "checkBlockedIP");
            return null;
        } 
    }
    
    public function checkLoginAttempts(string $ip): ?int {
        try {
            $SQL = 
            "SELECT 
                COUNT(*)
            FROM 
                login_attempts
            WHERE
                ip_address = :ip_address
            AND
                attempt_time > (NOW() - INTERVAL 1 HOUR)
            ";

            $stmt = $this->pdo->prepare($SQL);
            $stmt->execute(['ip_address' => $ip]);
            $attempts = $stmt->fetchColumn();
            return (int) $attempts ?: 0;

        } catch(PDOException $e) {
            $this->errorLogException($e->getMessage(), "checkLoginAttempts");
            return null;
        }
    }
    public function blockIP(string $ip): ?bool {
        try {
            $SQL=
            "UPDATE 
                login_attempts
            SET
                blocked = 1
            WHERE 
                ip_address = :ip_address
            ";
            $stmt = $this->pdo->prepare($SQL);
            return $stmt->execute(['ip_address' => $ip]) ?: false;

        } catch(PDOException $e) {
            $this->errorLogException($e->getMessage(), "blockIP");
            return null;
        }
    }
    public function clearAttempts(string $ip): ?bool {
        try {
            $SQL = 
            "DELETE FROM 
                login_attempts
            WHERE
                ip_address = :ip_address
            ";
            
            $stmt = $this->pdo->prepare($SQL);
            return $stmt->execute(['ip_address' => $ip]) ?: false;

        } catch (PDOException $e) {
            $this->errorLogException($e->getMessage(), "clearAttempts");
            return null;
        }
    }
    public function insertLoginAttempt(string $ip): ?bool {
        try {
            $SQL=
            "INSERT INTO
                login_attempts (ip_address, attempt_time)
            VALUES
                (:ip_address, NOW()) 
            ";
            
            $stmt = $this->pdo->prepare($SQL);
            return $stmt->execute(['ip_address' => $ip]);

        }  catch (PDOException $e) {
            $this->errorLogException($e->getMessage(), "insertLoginAttempt");
            return null;
        }
    }

    public function checkInsertBlock(string $ip): ?object {
        try {
           $blocked = false;

           if (!$this->pdo->inTransaction()) $this->pdo->beginTransaction();

            $ok = $this->insertLoginAttempt($ip);

            if ($ok === null || $ok === false) {
                 if ($this->pdo->inTransaction()) $this->pdo->rollBack();
                return null;
            }

            $currentAttempts = $this->checkLoginAttempts($ip);
            
            if ($currentAttempts === null) {
                 if ($this->pdo->inTransaction()) $this->pdo->rollBack();
                return null;
            }

           if (($currentAttempts) >= $this->MAX_ATTEMPTS) {
                 $blockattempt = $this->blockIP($ip);

                 if ($blockattempt === null) {
                    if ($this->pdo->inTransaction()) $this->pdo->rollBack();
                    return null;
                 }

                 $blocked = true;
           }
           
           $this->pdo->commit();

           return (object) [
                    'blocked' => $blocked,
                    'attempts' => $currentAttempts,
                    'left' => max(0, ($this->MAX_ATTEMPTS - $currentAttempts))
           ];


        } catch (PDOException $e) {
               if ($this->pdo->inTransaction())  $this->pdo->rollBack();
            $this->errorLogException($e->getMessage(), "checkInsertBlock");
            return null;
        }
    }
}
?>