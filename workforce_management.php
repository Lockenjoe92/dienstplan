<?php
// Workforce management

// Include config file
include_once "./config/dependencies.php";

// Check if the user is already logged in, if yes then redirect him to welcome page
$Nutzergruppen = session_manager('ausfaelle');

$HTML = "<h1 class='align-content-center'>Nutzerverwaltung</h1>";
$HTML .= "<p class='align-content-center'>Tabelle und hinzufueger hier!</p>";
$HTML .= table_workforce_management();

echo site_body('Nutzerverwaltung', $HTML, true, $Nutzergruppen);