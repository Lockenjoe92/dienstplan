<?php
include_once "./config/dependencies.php";
$Message = "";

if(isset($_GET['mode'])){
    if($_GET['mode']=='logout'){
        $Message .= "Erfolgreich ausgeloggt!";
    } elseif ($_GET['mode']=='sess_err'){
        $Message .= "Fehler mit der Sitzung! Bitte melden Sie sich erneut an!";
    } elseif ($_GET['mode']=='unidentified'){
        $Message .= "Fehler mit der Sitzung! Bitte melden Sie sich erneut an!";
    } elseif ($_GET['mode']=='unvalidated'){
        $Message .= "Fehler mit der Sitzung! Bitte melden Sie sich erneut an!";
    } elseif ($_GET['mode']=='timeout'){
        $Message .= "Ihre Sitzung ist abgelaufen! Bitte melden Sie sich erneut an!";
    }
}

$Content = login_card_oidc_mode($Message);
echo site_body('Login', $Content);

