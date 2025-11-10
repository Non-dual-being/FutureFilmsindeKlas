<?php
declare(strict_types=1);
namespace GeoFort\security;

final class SessionQuard {
        private readonly int $timeoutSeconds;
        private readonly string $requiredUsername;
        private readonly bool $strictSameSite;
        private readonly string $baseUrl;
        private readonly bool $isSecure;

    public function __construct(
        int $timeoutSeconds = 1800,
        string $requiredUsername = 'Future GeoFort Docent',
        bool $strictSameSite = true,
        string $baseUrl = 'https://planetaryhealth.xyz/Futurefilmsindeklas'

    ){
        $this->timeoutSeconds = $timeoutSeconds;
        $this->requiredUsername = $requiredUsername;
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
                'cookie_secure'     => $this->isSecure && !str_contains($_SERVER['HTTP_HOST'], 'localhost'),
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

    }

    public function publicSessionStart(): void 
    {
        if (session_status() !== PHP_SESSION_ACTIVE){
            session_start([
                'use_only_cookies'  => 1,
                'use_strict_mode'   => 1,
                'cookie_httponly'   => 1,
                'cookie_secure'     => $this->isSecure && !str_contains($_SERVER['HTTP_HOST'], 'localhost'),
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

        if ($MisMatch) $this->logoutAndRedirect('db_inlogPagina.php');

        
        //username && loggedin sessios var check
        $validSessionSettings = 
        (
        (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) 
            &&
        ($_SESSION['username'] ?? null === $this->requiredUsername)

        );

        if (!$validSessionSettings) {
            error_log("ongewenste login");
            $this->logoutAndRedirect('db_inlogPagina.php');
        }

         // activity check
        $last = $_SESSION['LAST_ACTIVITY'] ?? null;
        $inactivityLimitReached = 
        (
            is_int($last) 
                && 
            ((time() - $last) > $this->timeoutSeconds)
        );
        
        if ($inactivityLimitReached) $this->logoutAndRedirect('db_inlogPagina.php?InactivityMessage=sessie_verlopen');
       
        $_SESSION['LAST_ACTIVITY'] = time();

    }


    public function logoutAndRedirect(string $location = 'db_inlogPagina.php'): never
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