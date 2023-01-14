<?php
// Abwesenheiten Management - a site that shows all vacancies and allows management to manually add them

// Include config file
include_once "./config/dependencies.php";

// Check if the user is already logged in, if yes then redirect him to welcome page
$Nutzergruppen = session_manager('ausfaelle');

$HTML = "<h1 class='align-content-center'>Abwesenheiten</h1>";
$HTML .= "<p class='align-content-center'>Tabelle und hinzufueger hier!</p>";

echo site_body('Abwesenheiten', $HTML, true, $Nutzergruppen);
