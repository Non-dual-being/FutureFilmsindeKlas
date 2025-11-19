<?php
use Dotenv\Dotenv;
use GeoFort\Database\Connector;
use GeoFort\Security\AuthMiddleWare;

use GeoFort\ErrorHandlers\FlashMessageHandler;
use GeoFort\ErrorHandlers\FormExceptionHandler;
use GeoFort\ErrorHandlers\GeneralException;

use GeoFort\Services\ErrorHandlers\LoginFlasher;
use GeoFort\Services\Validators\InputValidator;
use GeoFort\Services\SQL\AdminUsersSQLService;
use GeoFort\Services\SQL\LoginAttemptsSQLService;
use GeoFort\Services\Http\ClientIpResolver;

use GeoFort\Enums\FlashTarget\LoginFlashTarget;

$GeoFortSession = new AuthMiddleWare();
$GeoFortSession->publicSession();

const CSRF = "csrf_token";
const MAX_ATTEMPTS = 5;

try {
    $pdo = Connector::getConnection();

} catch (PDOException $e){
    error_log("DatabaseFout: " . $e->getMessage());
    $message = urlencode("The service is momentarily not available, is time to get a cup of coffee");
    header("Location: errorPage.php?code=503&message={$message}");
    exit();
}

$LoginService = new LoginAttemptsSQLService($pdo);
$AdminService = new AdminUsersSQLService($pdo);

$Flasher = new LoginFlasher();
$FlashHandler = new FlashMessageHandler(LoginFlashTarget::class);
$validator = new InputValidator();

try{
    if (!isset($_SESSION[CSRF])) 
        $_SESSION[CSRF] = bin2hex(random_bytes(32));


    if (isset($_GET['InactivityMessage'])) 
        $Flasher->inactive($_GET['InactivityMessage'] ?? 'future-sessie verlopen');


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

        if ($correctPost){
            $resEmail = $validator->validateEmail($_POST['email']);
            if ($resEmail->haserror) $Flasher->inlogsubmit($resEmail->errors['email']);

            $resPassword = $validator->validatePassword($_POST['password']);
            if ($resPassword->haserror) $Flasher->inlogsubmit($resPassword->errors['password']);

            $ip = ClientIpResolver::getClientIp($_SERVER);
            if ($ip->haserror) $Flasher->inlogsubmit($ip->error ?? "Ongeldig ip address");

            $ipClient = $ip->ip;

            $blocked = $LoginService->checkBlockedIP($ipClient);
            if ($blocked) $Flasher->inlogsubmit("Je bent geblokkeerd, neemt contact op met beheerder");
            if (is_null($blocked)) $Flasher->general("Service fout, neem contact op met beheerder");

            if (!$blocked){
                $user = $AdminService->isAdminUser($resEmail->value);
                if ($user && !empty($user['password_hash']) && password_verify($resPassword->value, $user['password_hash'])){

                    // Sessie-ID regenereren voor beveiliging
                    session_regenerate_id(true);
                    $cleared = $LoginService->clearAttempts($ipClient);
                    if ($cleared === null) error_log("Login attempts could not be removed from database");
                    $_SESSION['user_name']  = $user['username'];
                    $_SESSION['user_id']  = $user['id'];
                    $_SESSION['user_email'] = $user['email'];

                    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
                    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
                    $_SESSION['loggedin']   = true;
                    $_SESSION['LAST_ACTIVITY'] = time();
                    $_SESSION['last_revalidation_time'] = time();

                    session_write_close(); // Sla sessie op en laat andere requests doorgaan
                        
                    //verwijder de pogingnen van de geldige admin
                    $LoginAttempsSQLService->clearAttempts($ip);
                        
                    // **Doorverwijzing naar dashboard.php**
                    header("Location: future-dashbaord.php");
                    exit();

                } else {
                   $failedLoginAttempt = $LoginService->checkInsertBlock($ipClient);
                   if ($failedLoginAttempt === null) $Flasher->general("Service niet beschikbaar");

                   if ($failedLoginAttempt->blocked) $Flasher->general("Inloggen geblokeerd, neem contact op met beheerder");

                   $Flasher->inlogsubmit("Foute inlog: {$failedLoginAttempt->left} inlog pogingen over");
                }
            }

        }  else {
             unset($_SESSION[CSRF]); // Token direct verwijderen
             $_SESSION[CSRF] = bin2hex(random_bytes(32));
             $Flasher->inlogsubmit("Onvolledige gegevens of incorrect inlogpoging");
        }
    }


}catch (FormExceptionHandler $e){
    unset($_SESSION[CSRF]); // Token direct verwijderen
    $_SESSION[CSRF] = bin2hex(random_bytes(32));
    $FlashHandler->handleException($e);
} catch (GeneralException $e){
    error_log("GeneralException: " . $e->getMessage());
    $Flasher->general("Er is een fout opgetreden. Probeer later opnieuw.");
}
?>