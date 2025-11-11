<?php
namespace GeoFort\Services\SQL;
use PDO;


class LoginAttemptsSQLService {
    private PDO $pdo;
    
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
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
                ip_address = :ip_address";

            $stmt = $this->pdo->prepare($SQL);
            $stmt->execute(['ip_address' => $ip]);
            $result = $stmt->fetchColumn();
            return $result ?: null;

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
                attempt_time > (NOW() - INTERVAL 1 HOUR);
            ";

            $stmt = $this->pdo->prepare($SQL);
            $stmt->execute(['ip_address' => $ip]);
            $attempts = $stmt->fetchColumn();
            return $attempts ?: 0;

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
            return $stmt->execute(['ip_address' => $ip]);

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
            return $stmt->execute([':ip_address' => $ip]);

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
            return $stmt->execute([':ip_address' => $ip]);

        }  catch (PDOException $e) {
            $this->errorLogException($e->getMessage(), "insertLoginAttempt");
            return null;
        }
    }
}
?>