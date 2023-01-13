<?php
// Dashboard - Site that displays essential Information for Users based on their Roles

// Include config file
include_once "./config/dependencies.php";

// Check if the user is already logged in, if yes then redirect him to welcome page
$Nutzergruppen = session_manager('nutzer');

$HTML = "<h1>Dashboard</h1>";
$HTML .= "Willkommen an Bord";

echo site_body('Dashboard', $HTML, true, $Nutzergruppen);

?>

