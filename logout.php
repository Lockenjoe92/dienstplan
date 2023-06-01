<?php
// Include config file
include_once "./config/dependencies.php";

if(LOGINMODE=='OIDC2'){
    require_once('./auth.php');

    $auth->logout();

    header('Location: index.php');
    die();

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