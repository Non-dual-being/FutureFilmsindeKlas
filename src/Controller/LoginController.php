<?php
use Dotenv\Dotenv;
use GeoFort\Database\Connector;
use GeoFort\Security\AuthMiddleWare;

use GeoFort\Services\ErrorHandlers\LoginFlasher;
use GeoFort\Services\Http\ClientIpResolver;
use GeoFort\Services\Http\HeaderRedirector;
use GeoFort\Services\Validators\InputValidator;
use GeoFort\Services\SQL\AdminUsersSQLService;
use GeoFort\Services\SQL\LoginSecurityService;

use GeoFort\Utils\DateFormatter;


$GeoFortSession = new AuthMiddleWare();
$GeoFortSession->publicSession();

const CSRF = "csrf_token";
const MAX_ATTEMPTS = 5;
const LOGIN_PATH = 'auth/login-page.php';
const ERROR_PATH = 'error/index.php';

try {
    $pdo = Connector::getConnection();

} catch (PDOException $e){
    error_log("DatabaseFout: " . $e->getMessage());
    HeaderRedirector::toError("error.php", 503); 
    exit();
}


$LoginService = new LoginSecurityService($pdo);
$AdminService = new AdminUsersSQLService($pdo);
$validator = new InputValidator();
$Flasher = new LoginFlasher();


if (!isset($_SESSION[CSRF])) 
    $_SESSION[CSRF] = bin2hex(random_bytes(32));

/**inactiveMsg flash (GET) */

$inactiveMsg = filter_input(INPUT_GET, 'inactiveMsg', FILTER_UNSAFE_RAW) ?? null;

$flahInactiveMsg = (
    ($_SERVER['REQUEST_METHOD'] === 'GET')
    &&
    ($inactiveMsg !== null)
    &&
    (trim($inactiveMsg) !== '')
);

if ($flahInactiveMsg) {
    $Flasher->inactive($inactiveMsg); /**session var */
    HeaderRedirector::absolute(LOGIN_PATH, [], 303);
    exit;
}
    
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $correctPost = (
        isset($_POST['loginSubmit'])
        &&
        !empty($_POST['email'])
        &&
        !empty($_POST['password'])
        &&
        (!empty($_POST[CSRF]) && isset($_SESSION[CSRF]))
        &&
        (hash_equals($_SESSION[CSRF], $_POST[CSRF]))
    );

    if (!$correctPost) {
        $_SESSION[CSRF] = bin2hex(random_bytes(32));
        $Flasher->inlogsubmit('Ongeldige sessie variabelen');
        HeaderRedirector::absolute(LOGIN_PATH, [], 303);
        exit;            
    }

    $resEmail = $validator->validateEmail((string) ($_POST['email'] ?? ''));
    if ($resEmail->haserror) {
        $Flasher->inlogsubmit($resEmail->errors['email']);
        HeaderRedirector::absolute(LOGIN_PATH, [], 303);
        exit;            

    };

    $resPassword = $validator->validatePassword((string) ($_POST['password'] ?? ''));
    if ($resPassword->haserror) {
        $Flasher->inlogsubmit($resPassword->errors['password']);
        HeaderRedirector::absolute(LOGIN_PATH, [], 303);
        exit;
    }

    $ip = ClientIpResolver::getClientIp($_SERVER);
    if ($ip->haserror) {
        $Flasher->inlogsubmit($ip->error ?? "Ongeldig ip address");
        HeaderRedirector::absolute(LOGIN_PATH, [], 303);
        exit;
    }

    $ipClient = $ip->ip;
    $emailClient = $resEmail->value;

    $Lock = $LoginService->checkLockOutInfo($emailClient, $ipClient);


    if ($Lock === null) {
        HeaderRedirector::toError(ERROR_PATH, 503, 'Future Service Error');
        exit;
    }
    if ($Lock->blocked){
        $lockOutDateNL = DateFormatter::parseToLongDutchDate($Lock->lockout_until) ?? "onbekende tijd";
        $Flasher->inlogsubmit("Inloggen geblokkeert tot {$lockOutDateNL}");
        HeaderRedirector::absolute(LOGIN_PATH, [], 303);
        exit;
        
    } 


    $user = $AdminService->isAdminUser($emailClient);
    $invalid = (
        !$user 
        || empty($user['password_hash']) 
        || !password_verify($resPassword->value, $user['password_hash'])
    );

    if ($invalid) {
        $failed = $LoginService->recordFailedAttempt($emailClient, $ipClient);

        if ($failed === null){
            HeaderRedirector::toError(ERROR_PATH, 503, 'Future Service Error');
            exit;
        }

        if ($failed->blocked) {
            $lockOutDateNL = DateFormatter::parseToLongDutchDate($failed->lockout_until) ?? 'onbekende tijd';
            $Flasher->inlogsubmit("Inloggen geblokkeerd tot {$lockOutDateNL}");
        } else {
            $attemptsLeft = (int) $failed->attempts_left;
            $Flasher->inlogsubmit(
                $attemptsLeft
                    ?   "{$attemptsLeft} inlogpoggingen over tot blokkade"
                    :   "Na 5 gefaalde pogingen wordt er geblokkeerd"
            );
        }
        HeaderRedirector::absolute(LOGIN_PATH, [], 303);
        exit;
    }

    //succesfull login request

    session_regenerate_id(true);
    $cleared = $LoginService->recordSuccessfullogin($emailClient, $ipClient);
    if ($cleared === null) error_log("failed at clearing attempts after succesful login request");

    $_SESSION['user_name'] = $user['username'];
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
    $_SESSION['loggedin'] = true;
    $_SESSION['LAST_ACTIVITY'] = time();
    $_SESSION['last_revalidation_time'] = time();

    session_write_close();
    HeaderRedirector::toDashboard();
    exit;
}   
?>