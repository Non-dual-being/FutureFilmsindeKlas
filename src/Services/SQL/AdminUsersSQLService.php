<?php
namespace GeoFort\Services\SQL;
use PDO;

class AdminUsersSQLService {
    private PDO $pdo;
    
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    private function errorLogException(string $e, string $context): void 
    {
        error_log("[SQL ERROR][$context] " . $e); 
    }

    public function isAdminUser(string $email): ?array {
        try {
            $SQL=
            "SELECT 
                *
            FROM
                admin_users
            WHERE
                email = :email
            LIMIT 1";

            $stmt = $this->pdo->prepare($SQL);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user ?: null;
        } catch (PDOException $e){
            $this->errorLogException($e->getMessage(), "checkBlockedIP");
            return null;
        } 
    }
}

?>