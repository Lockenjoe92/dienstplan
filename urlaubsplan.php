<?php
// Workforce management

// Include config file
include_once "./config/dependencies.php";

// Check if the user is already logged in, if yes then redirect him to welcome page
$Nutzergruppen = session_manager('urlaubsplan');

// Build content
$HTML = "<h1 class='align-content-center'>Urlaubsübersicht</h1>";
$HTML .= urlaubsplan_funktionsbuttons();
$HTML .= urlaubsplan_tabelle_management(01,2023);

// Space Out stuff
$HTML = grid_gap_generator($HTML);

echo site_body('Urlaubsübersicht', $HTML, true, $Nutzergruppen);