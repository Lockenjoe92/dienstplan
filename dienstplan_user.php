<?php
// Dashboard - Site that displays essential Information for Users based on their Roles

// Include config file
include_once "./config/dependencies.php";

// Check if the user is already logged in, if yes then redirect him to welcome page
$Nutzergruppen = session_manager('nutzer');

$HTML = "<h1 class='align-content-center'>Dienstplan</h1>";
$HTML .= "<p class='align-content-center'>Hier entsteht ab Mittwoch eine Ansicht zur Abgabe und Übersicht deiner Dienstplanwünsche.</p>";

echo site_body('Dashboard', $HTML, true, $Nutzergruppen);

?>