<?php
// Workforce management

// Include config file
include_once "./config/dependencies.php";

// Check if the user is already logged in, if yes then redirect him to welcome page
$Nutzergruppen = session_manager('ausfaelle');

$HTML = "<h1 class='align-content-center'>Nutzerverwaltung</h1>";
$HTML .= table_workforce_management(connect_db());

echo site_body('Nutzerverwaltung', $HTML, true, $Nutzergruppen);