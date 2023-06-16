<?php
// Include config file
include_once "./config/dependencies.php";
require __DIR__ . '/vendor/autoload.php';

if(LOGINMODE=='OIDC2'){

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
    header("location: welcome.php");
    exit;

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
    header("location: welcome.php");
    exit;
}


?>