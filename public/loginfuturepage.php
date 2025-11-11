<?php
/**
 * use_strict_mode zorgt ervoor dat als de user zelf eeen id naar de server stuurt die niet wordt gekend
 * de user nieuwe sessie id geven vanuit server en niet vanuit de user
 * 
 */
require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'dashboard_modules' . DIRECTORY_SEPARATOR . 'SQLServices' . DIRECTORY_SEPARATOR . 'LoginAttemptsSQLService.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'dashboard_modules' . DIRECTORY_SEPARATOR . 'SQLServices' . DIRECTORY_SEPARATOR . 'AdminUsersSQLService.php';

use dashboard_modules\SQLServices\LoginAttemptsSQLService;
use dashboard_modules\SQLServices\AdminUsersSQLService;
use GeoFort\Security\AuthMiddleWare;
use GeoFort\Database\dataBaseConnector;
use Dotenv\Dotenv;

$MiddleWare = new AuthMiddleWare();
$MiddleWare->publicSession();

// Genereer een nieuwe CSRF-token
if(!isset($_SESSION['csrf_token'])){
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // 64-tekens lange token
}

$bodyClass = "fullBody";

try {
    $pdo = dataBaseConnector::getConnection();
} catch (PDOException $e){
    error_log($e->getMessage());
    $connectedToDB = false;  
    $pdo = false;
}

if ($pdo){
        // inactiviteits melding ontvangen vanuit een ingelogde sessie op het dashbaord
    if (isset($_GET['InactivityMessage']) && $_GET['InactivityMessage'] === 'sessie_verlopen' ){
        $inActivityMessage = "U bent uitgelogd na inactiviteit op het dashboard";
    }
        
    

    /** || -- gobal vars ---- || */
    $inActivityMessage = '';
    $connectedToDB = true;
    $noConnectionMessageHTML = '';
    $maxAttempts = 3;
    $Flashmessage_inlogSubmit = ''; 
    $FlashmessageType = ''; 
    $errors = []; 

    $LoginAttempsSQLService = new LoginAttemptsSQLService($pdo);
    $AdminUsersSQLService = new AdminUsersSQLService($pdo);
    

    $resetServerMessages = [
    'inlogSubmit' => function (&$FlashMessages){
        $FlashMessages['message']['inlogSubmit'] = '';
        $FlashMessages['type'] = '';
    }
    ];

    $FlashMessages = [
    'message' => [
        'inlogSubmit' => '',
    ],
    'type' => ''
    ];

    $logErrors = []; // Array om fouten te verzamelen


// Controleer of er fouten zijn en log deze
    if (!empty($logErrors)) {
        foreach ($logErrors as $error) {
            error_log("[LOGIN ERROR] " . $error);
    }}


    if ($_SERVER['REQUEST_METHOD'] === 'POST' 
    && isset($_POST['loginSubmit']) 
    && !empty($_POST['email']) 
    && !empty($_POST['password']) 
    && !empty($_POST['csrf_token']) 
    && isset($_SESSION['csrf_token']) 
    && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])

    ) {
        unset($_SESSION['csrf_token']); // Token direct verwijderen
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Nieuw token genereren
  
            // Controleer of er al een CSRF-token is, anders genereer er een

        /** email en wachtwoord valideren */
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Ongeldig e-mailadres.";
        } else {
            $email = sanitize_input($_POST['email'], 50, $errors, 'email');
        }

        if (empty($_POST['password'])) {
            $errors['password'] = "Voer een wachtwoord in.";
        } else {
            $password = sanitize_input($_POST['password'], 50, $errors, 'password');
        }

        if (empty($errors)){
            $blocked = false;
            $ip = get_ip_address();
            if (!$ip){
                list($Flashmessage_inlogSubmit, $FlashmessageType) = displayMessage('inlogSubmit', 'inloggen is geblokkeert', $resetServerMessages, $FlashMessages, 'error');     
            }

            $blockedSQL = $LoginAttempsSQLService->checkBlockedIP($ip);
      
            if ($blockedSQL) {
                list($Flashmessage_inlogSubmit, $FlashmessageType) = displayMessage('inlogSubmit', 'inloggen is geblokkeert', $resetServerMessages, $FlashMessages, 'error');
                $blocked = true;
            }

            if (!$blocked) {
                $ToMuchAttempts = false;
                // Controleer op mislukte pogingen binnen 1 uur
                $attempt_count = $LoginAttempsSQLService->checkLoginAttempts($ip);
                if ($attempt_count && $attempt_count >= $maxAttempts -1) {
                    // Blokkeer het IP-adres
                    $LoginAttempsSQLService->blockIP($ip);
                    $ToMuchAttempts = true;
                }

                if (!$ToMuchAttempts){
                    // Haal de gebruiker op uit de database
                    $user = $AdminUsersSQLService->isAdminUser($email);
                    
                    if ($user && !empty($user['password_hash']) && password_verify($password, $user['password_hash'])) {
                        
                        
                        // Sessie-ID regenereren voor beveiliging
                        session_regenerate_id(true);
                        /**
                         * Genereert een nieuwe sessie id en verwijderd de gegevens van de oude sessie (met true)
                         * Op die manier kan de oude sessie niet meer misbruikt worden door anderen
                         * Met argument true verwijder je alle gegevens van de oude sessie
                         * Je de sessie id wordt opgeslagen in een cookie
                         * 
                         */
                        $_SESSION['username'] = "GeoFort Planner";

                        error_log("my user agent :" . $_SERVER['HTTP_USER_AGENT']);

                        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
                        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];

                        /**'
                         * hardcoded username
                         * todo: meerdere accounts dan dynamisc op basis van een conditie
                         */


                        $_SESSION['loggedin'] = true;
                        session_write_close(); // Sla sessie op en laat andere requests doorgaan
                        
                        //verwijder de pogingnen van de geldige admin
                        $LoginAttempsSQLService->clearAttempts($ip);
                        
                        // **Doorverwijzing naar dashboard.php**
                        header("Location: db_dashBoard.php");
                        exit();
                
                    } else {
                        // Log de mislukte poging
                        $LoginAttempsSQLService->insertLoginAttempt($ip);
                        list($Flashmessage_inlogSubmit, $FlashmessageType) = displayMessage('inlogSubmit', 'ongeldige inlogpoging', $resetServerMessages, $FlashMessages, 'error');
                    }
                } else {
                    list($Flashmessage_inlogSubmit, $FlashmessageType) = displayMessage('inlogSubmit', 'inloggen is geblokkeert', $resetServerMessages, $FlashMessages, 'error');
                }
            }

        } else {
            list($Flashmessage_inlogSubmit, $FlashmessageType) = displayMessage('inlogSubmit', 'verkeerde inloggegevens opgegeven', $resetServerMessages, $FlashMessages, 'error');
        }
        
    } else {
        if((empty($_POST['email']) || empty($_POST['password'])) 
        && ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['loginSubmit']))){
            list($Flashmessage_inlogSubmit, $FlashmessageType) = displayMessage('inlogSubmit', 'vul zowel het mailadres als het wachtwoord in', $resetServerMessages, $FlashMessages, 'error');
        } 
    }
}?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeoFort Inlog Admin Panel</title>
    <link rel="stylesheet" href="styles.css">
    <?php if ($connectedToDB) : ?>
        <script src="./db_inlogPagina.js" defer></script>
    <?php endif; ?>
</head>
<body 
   class="<?php echo 'showpageInlog extraLayer' . (!$connectedToDB ? ' ' . htmlspecialchars($bodyClass) : ''); ?>"
>
<?php if (!$connectedToDB) : ?>
    <h1>INLOG PAGINA DASHBOARD</h1>
    <section class="foutMeldingFormulierAlgemeen">
        <h2 class="foutMeldingFormulierAlgemeen-H2">Inloggen niet beschikbaar</h2>
        <p class="foutMeldingFormulierAlgemeen-Para">Er is een technisch probleem waardoor inloggen niet mogelijk is: voor meer info neem contact op met:
            <p class = "foutMeldingFormulierAlgemeen-ParaInlog">
                <a href="mailto:kevin@geofort.nl" class="foutMeldingFormulierAlgemeen-ParaLink">kevin@geofort.nl</a>
                of
                <a href="mailto:kevin@geofort.nl" class="foutMeldingFormulierAlgemeen-ParaLink">koen@geofort.nl</a>
            </p>
        </p>
    </section>        
<?php endif; ?> 
<?php if ($connectedToDB) : ?>
    <h1 class="inlogh1">INLOG PAGINA DASHBOARD</h1>
    <form method="POST" class="login-form" id="login-form">
        <div 
            class="generalFlashmessageWrapperInlog"
        >
            <div 
                id="recieve-message-inlogSubmit" 
                class="flash-message <?php echo htmlspecialchars($FlashmessageType); ?> absolute-positioned-General__login"
            >
                <?php echo htmlspecialchars($Flashmessage_inlogSubmit); ?>
            </div>
            <div 
                id="recieve-inactivity-message" 
                class="flash-message success bsolute-positioned-General__login"
            >
            <?php echo htmlspecialchars($inActivityMessage); ?>
            </div>
        </div>
        
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="Email" autocomplete="email" required>
        
        <label for="password">Wachtwoord</label>
        <input type="password" autocomplete="current-password" id="password" name="password" placeholder="Wachtwoord" required>
        <!--De autocompletion is voor browsers zodat ze op een correcte manier het wachtwoord kunnen automatisch invulen-->
        <button type="submit" id="verzendknop" name="loginSubmit"class="submit-button">Inloggen</button>
    </form>
<?php endif; ?> 
</body>
</html>
