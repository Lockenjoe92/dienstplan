<?php
// Include config file
include_once "./config/dependencies.php";
require __DIR__ . '/vendor/autoload.php';

if(LOGINMODE=='OIDC2'){

        $provider = new League\OAuth2\Client\Provider\GenericProvider([
            'clientId'                => OIDCCLIENTID,    // The client ID assigned to you by the provider
            'clientSecret'            => OIDCCLIENTSECRET,    // The client password assigned to you by the provider
            'redirectUri'             => 'https://dienstplan.marcsprojects.de/welcome.php',
            'scopes' => ['openid groups'],
            'urlAuthorize'            => OIDCURLAUTHORIZE,
            'urlAccessToken'          => OIDCURLTOKEN,
            'urlResourceOwnerDetails' => OIDCURLRESOURCE]);

        // Redirect the user to the logout endpoint
    $logoutUrl = 'https://auth-dev.medizin.uni-tuebingen.de/oidc/logout';
    // Redirect the user to the logout endpoint
    $authorizationUrl = $provider->getAuthorizationUrl(['logout' => $logoutUrl, 'post_logout_redirect_uri'=>'https://dienstplan.marcsprojects.de/welcome.php']);

    header('Location: ' . $authorizationUrl);
    exit();

} else {
    // Initialize the session
    session_start();

    // Unset all of the session variables
    $_SESSION = array();

    // Destroy the session.
    session_destroy();

    //
    foreach ($_COOKIE as $name => $value) {
        setcookie($name, '', 1);
    }

    // Redirect to login page
    $logoutUrl = 'https://auth-dev.medizin.uni-tuebingen.de/oidc/logout';
    header("location: ".$logoutUrl);
    exit;
}


?>