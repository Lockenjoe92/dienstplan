<?php
// Include config file
include_once "./config/dependencies.php";
require __DIR__ . '/jumbojett/vendor/autoload.php';
use Jumbojett\OpenIDConnectClient;

if(LOGINMODE=='OIDC'){

    // Initialize the session
    session_start();
    #$oidc = new Jumbojett\OpenIDConnectClient(OIDCURL, OIDCCLIENTID, OIDCCLIENTSECRET);
    #$oidc->signOut($_SESSION['tokenID'], null);

    // Kill the session
    $_SESSION = array();
    session_destroy();
    header("location: welcome.php?mode=logout");

} else {
    // Initialize the session
    session_start();

// Unset all of the session variables
    $_SESSION = array();

// Destroy the session.
    session_destroy();

// Redirect to login page
    header("location: login.php");
    exit;
}

?>