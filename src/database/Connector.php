<? 
declare(strict_types=1);
namespace GeoFort\database;

use PDO;
use PDOException;
use Dotenv\Dotenv;

class Connector {
    private static ?PDO $instance = null;
    private const CHARSET = 'utf8mb4';

    private function __construct(){}
    private function __clone(){}

    private static function loadEnv() :void {
        $path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
        $dotenv = Dotenv::createImmutable($path);
        $dotenv->load();
    }

    public static function getConnection(): PDO {
        if (!isset(self::$instance)){
            self::loadEnv();

            $host   = $_ENV['HOST']   ?? $_SERVER['HOST']   ?? '';
            $dbname = $_ENV['DBNAME'] ?? $_SERVER['DBNAME'] ?? '';
            $user   = $_ENV['DBUSER'] ?? $_SERVER['DBUSER'] ?? '';
            $pass   = $_ENV['PASS']   ?? $_SERVER['PASS']   ?? '';
            $port   = $_ENV['PORT']   ?? $_SERVER['PORT']   ?? '3306';

            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                $host,
                $port,
                $dbname,
                self::CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE               => PDO::ERRMODE_EXCEPTION, //throw exeptionss
                PDO::ATTR_DEFAULT_FETCH_MODE    => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES      => false                   //use native prepare statements
            ];

            try {
                self::$instance = new PDO(
                    $dsn,
                    $user,
                    $pass,
                    $options
                );

            } catch (PDOException $e){
                error_log("Gefaald database connectie: " . $e->getMessage());
                throw new PDOException("Unable to connect to database.", (int) $e->getCode(), $e);
            }


        }
        return self::$instance;
    }
}



?>