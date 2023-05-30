<?php
// Dashboard - Site that displays essential Information for Users based on their Roles

// Include config file
include_once "./config/dependencies.php";
require __DIR__ . '/vendor/autoload.php';

// Check if the user is already logged in, if yes then redirect him to welcome page
$Nutzergruppen = session_manager('nutzer');
$mysqli = connect_db();
$userID = get_current_user_id();

$HTML = "<h1 class='align-content-center'>Dashboard</h1>";
$HTML .= "Der aktuelle OAuth2-Token ist gültig bis: ".date("d.m.Y G:i:s", $_SESSION["accessTokenOauth"]->getExpires());

$HTML .= '<div class="row row-cols-1 row-cols-md-2 g-4">';
$HTML .= dashboard_view_abwesenheiten_user($mysqli, $userID, $Nutzergruppen);
$HTML .= dashboard_view_bereitschaftsdienstplan_user($mysqli, $userID);
$HTML .= '</div>';

echo site_body('Dashboard', $HTML, true, $Nutzergruppen);

?>

