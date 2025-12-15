<?php
namespace GeoFort\Services\SQL;

use PDO;
use PDOException;

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

    public function isUserStillValid(int $id, string $email): ?bool {
        try{
            $SQL = 
            "SELECT
                1
            FROM
                admin_users
            WHERE
                id = :id AND email = :email
            LIMIT 1
            ";
            error_log("user id in sql : " . $id);
            error_log("user email in sql : " . $email);
            $stmt = $this->pdo->prepare($SQL);
            $stmt->execute([
                ':id' => $id,
                ':email' => $email
            ]);
            $val = $stmt->fetchColumn();
            error_log('isUserStillValid fetchColumn: ' . var_export($val, true));
            return  $val !== false;

            /**
             * 1 bij gevonden is niet geljk aan false dus true
             * bij niet gevonden false, meer false is niet niet geljk aan false, dus false
             * 
             */


        } catch (PDOException $e){
            $this->errorLogException($e->getMessage(), "isUserStillValid");
            return null;
        }
    }
}

?>