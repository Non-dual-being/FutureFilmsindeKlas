<?php
declare(strict_types=1);
namespace GeoFort\security;
use GeoFort\Services\SQL\AdminUsersSQLService;
use PDO;


final class SessionQuard {
        private readonly int $timeoutSeconds;
        private readonly bool $strictSameSite;
        private readonly string $baseUrl;
        private readonly bool $isSecure;
        private readonly ?PDO $pdo;
        private readonly ?AdminUsersSQLService $adminUserSQL;

        private const LOGINPAGE = 'loginfuturepage.php';
        private const REVALIDATION_INTERVAL = 900; /**15 min */


    public function __construct(
        ?PDO $PDO,
        int $timeoutSeconds = 1800,
        bool $strictSameSite = true,
        string $baseUrl = 'https://planetaryhealth.xyz/Futurefilmsindeklas'
    ){
        $this->pdo = $pdo;
        if ($this->pdo){
            $this->adminUsersSQL = new AdminUsersSQLService($this->pdo);
        } else {
            $this->adminUsersSQL = null;
        }

        $this->adminUserSQL = $adminUserSQL;
        $this->timeoutSeconds = $timeoutSeconds;
        $this->strictSameSite = $strictSameSite;
        $this->baseUrl = rtrim($baseUrl, '/');

        $this->isSecure = (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== "off")
            ||
            ($_SERVER['SERVER_PORT'] ?? null == 443)
        );

    }

    public function privateSessionStart(): void
    {
       
        if (session_status() !== PHP_SESSION_ACTIVE){
            session_start([
                'use_only_cookies'  => 1,
                'use_strict_mode'   => 1,
                'cookie_httponly'   => 1,
                'cookie_secure'     => $this->isSecure /*&&  !str_contains($_SERVER['HTTP_HOST'], 'localhost') */,
                'cookie_samesite'   => $this->strictSameSite 
                    ? 'Strict' 
                    : 'Lax'
            ]);
        }

        $_SESSION['user_agent'] ??= ($_SERVER['HTTP_USER_AGENT'] ?? '');
        $_SESSION['ip_address'] ??= ($_SERVER['REMOTE_ADDR'] ?? '');

        /** ??=
         * if left doesnt exist or is null then assign it with right value
         */

        /* 
           * [-----------------------------------[COOKIES EXPLANATION]------------------]  
        */

        /**-------------]| use_only_cookies |
         * PHP gebruikt alleen cookies voor sessie-ID's (geen url parameter als SID)
         * VOorkomt session fixation via URL
         * 
        */


        /**-------------]| use_strict_mode |
         * PHP accpeteert geen geranden of reeds gebruikte sessie ids. 
         * ALs sessie id niet bestaat wordt er een nieuwe aangemaakt
         * ZOnder deze instelling kan een aanvallen eigen ID injecteren
         * 
        */

        /**-------------]| cookie_httponly |
         * De sessie cookie krijgt de vlag HTTP only waardoor het niet toegankelijk is via javascript
         * Verminderd XSS impact
         * Cookie kan niet uitgelezen worden via document.cookie
         * 
        */

        /**-------------]| cookie_secure |
         * Alleen cookie verzenden als het over een veilige verbinding draait
         * 
        */

        /**-------------]| cookie_samesite |
         * Strict: Sessiecookie gaat nuiet mee in crosss-sute requests ook niet via links
         * Lax: Cookie wordt niet meegestuurd bij cross-site POSTS maar wel bij top level navigaties (VIA GET)
         * None Staat cross site altijd toe, dit moet in combie met cookie secure
         * 
        */

        /* 
           * [-----------------------------------[FINGERPRINT]------------------]  
        */

                /** ---------] | user_agent | & | 'ip_address |
         * je instrueert php met cookie -en security opties
         * php genereert of hervat een sessie id via cookie PHPSESSID
         * $_SESSION een superglobale due gevuld en weggescheven wordt
         */



        /* 
           * [-----------------------------------[PHP SESSIE COOKIE]------------------]  
        */

        /** ---------] | session start |
         * je instrueert php met cookie -en security opties
         * php genereert of hervat een sessie id via cookie PHPSESSID
         * $_SESSION een superglobale due gevuld en weggescheven wordt
         */

        /* 
           * [-----------------------------------[BEST PRACTICES]------------------]  
        */

        /** secure + httponly + samesite/strict altijd op true instellen */

        /** sessie-id regeneren om fixation tegen te komen: `session_regenerate_id(true)` */

        /** session life-time + inactivity timeout instellen bijvoorbeeld 30 min */

        /** cookie_path idealiter op je app root */

        /** cookie_domein alleen als je subdomeinen wilt delen */

        /** fingerprint User Agent op basis van tolerante check (browsers kunnen het wijzigen) */

        /** fingerprint ID -> hard match kan te stevig zijn vanwege wisselingen: controleer op delen zoals ASN of laatste 3 cijfers */

        /** CSRF-tokens -> gebruik CSRF server side tokens voor extra beveiliging bij formulieren */

        /** XSS-hardening Httponly is stap 1 aanvullend strikte content security, output escaping,vermijden van inline scripts of zet nonce/hashes */

        /** logout unset alle sessie waarden, session_destroy gebruiken, sessesie cookie ongeldig maken */

        /** Bij ontbreken van https redirect of veilig falen */



        
        

    }

    public function publicSessionStart(): void 
    {
        if (session_status() !== PHP_SESSION_ACTIVE){
            session_start([
                'use_only_cookies'  => 1,
                'use_strict_mode'   => 1,
                'cookie_httponly'   => 1,
                'cookie_secure'     => $this->isSecure /* &&  !str_contains($_SERVER['HTTP_HOST'], 'localhost') */,
                'cookie_samesite'   => $this->strictSameSite 
                    ? 'Strict' 
                    : 'Lax'
            ]);
        }
    }

    private function makeBaseUrlLink(string $url = ''): string {
        $base = rtrim($this->baseUrl, '/');
        $secureUrl = ltrim($url, '/');
        return "$base/$secureUrl";
    } 

    public function assertAuthenticated(): void
    {
        //user_agent && ip check
        $uaMismatch = ($_SESSION['user_agent'] ?? '') !== ($_SERVER['HTTP_USER_AGENT'] ?? '');
        $ipMismatch = ($_SESSION['ip_address'] ?? '')  !== ($_SERVER['REMOTE_ADDR'] ?? '');
        $MisMatch = ($uaMismatch || $ipMismatch);

        if ($MisMatch) $this->logoutAndRedirect(self::LOGINPAGE);

        
        //username && loggedin sessios var check
        $validSessionSettings = 
        (
        (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) 
            &&
        (isset($_SESSION['user_id']) && is_int($_SESSION['user_id']))
            &&
        (isset($_SESSION['user_email']) && is_string($_SESSION['user_email']))

        );

        if (!$validSessionSettings) {
            error_log("ongeldig inlog door verkeerde authentificatie");
            $this->logoutAndRedirect(self::LOGINPAGE);
        }


        //als de servce onbeschikbaar is zelf
        if ($this->adminUsersSQL === null){
            error_log("Session revalidation failed: DB service is unavailable. Forcing logout.");
            $this->logoutAndRedirect(self::LOGINPAGE);
        }

        $lastRevalidation = $_SESSION['last_revalidation_time'] ?? 0;
        if ((time() - $lastRevalidation) > self::REVALIDATION_INTERVAL){

            $userExists = $this->adminUsersSQL->isUserStillValid($_SESSION['user_id'], $_SESSION['user_email']);

            if ($userExists === null){
                error_log("Session revalidation failed: DB service is unavailable. Forcing logout.");
                $this->logoutAndRedirect(self::LOGINPAGE);
            }

            if ($userExists === false){
                error_log("Session revalidation returned false for user with id " . $_SESSION['user_id']);
                $this->logoutAndRedirect(self::LOGINPAGE);
            }

            $_SESSION['last_revalidation_time'] = time();

        }


         // activity check
        $last = $_SESSION['LAST_ACTIVITY'] ?? null;
        $inactivityLimitReached = 
        (
            is_int($last) 
                && 
            ((time() - $last) > $this->timeoutSeconds)
        );
        
        if ($inactivityLimitReached) $this->logoutAndRedirect(self::LOGINPAGE . "?InactivityMessage=sessie_verlopen");
       
        $_SESSION['LAST_ACTIVITY'] = time();

    }


    public function logoutAndRedirect(string $location = self::LOGINPAGE): never
    {
        $secureLocation = $this->makeBaseUrlLink($location);
        $this->destroySession();
        $this->redirect($secureLocation);

        /**
         * never omdat je uitlogt en dus niet meer terug komt naar de functie
         * De functie kun niets teruggeven aan de gebruiker, want die wordt niet bereikt
         */
    }

    private function redirect(string $location): never
    {
        header('Location: ' . $location);
        exit();
    }

    private function destroySession(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    /** ini_get, getcoolie em session name
     * 
     * controller of de sessie id via de cookie PHPSESSID in browser wordt bewaard en gestuurd
     * De paramas van de sessie worden opgehaald, dus hoe de sessie is opgestart
     * session_name is staandaard: PHPSESSID, kan je ook handmatig instellen
     * Je zet de waarde leeg en laat hm verlopen door de verval tijd in het verleden te zetten
     * 
     */
}

?>