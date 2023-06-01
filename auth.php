<?php
require_once('./vendor/autoload.php');
require_once "./config/dependencies.php";
use Jumbojett\OpenIDConnectClient;

class Auth {
    /**
     * @var OpenIDConnectClient oidc client
     */
    private $oidc;
    private $postLogoutRedirectUri;

    public function __construct() {
        $oidc = new OpenIDConnectClient(
            OIDCURLAUTHORIZE,
            OIDCCLIENTID,
            OIDCCLIENTSECRET);

        $oidc->setResponseTypes('id_token token');
        $oidc->addScope(array('openid GROUPS'));
        $oidc->setAllowImplicitFlow(true);
        $oidc->addAuthParam(array('response_mode' => 'form_post'));
        // Handle PlusAuth response after login
        $oidc->setRedirectURL('https://dienstplan.marcsprojects.de/login.php');

        // For development mode only
        $oidc->setVerifyHost(false);
        $oidc->setVerifyPeer(false);

        $this->oidc = $oidc; // Crate oidc object at page load
        $this->postLogoutRedirectUri = "https://dienstplan.marcsprojects.de/";
    }

    public function login() {
        if ($this->isLoggedIn() == false) {
            $this->oidc->authenticate();
            $this->setIdToken($this->oidc->getIdToken());
            $this->setUser($this->oidc->requestUserInfo());
        }
        // User information is in the session if the user logged in
    }

    public function logout() {
        // Clear session, user will still be logged in on PlusAuth
        $idToken = $this->getIdToken();
        unset($_SESSION['idToken']);
        unset($_SESSION['user']);

        // RP initiated logout, user will be logged out from PlusAuth too
        return $this->oidc->signOut($idToken, $this->postLogoutRedirectUri);
    }
}

session_start();
$auth = new Auth();