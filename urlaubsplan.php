<?php
// Workforce management

// Include config file
include_once "./config/dependencies.php";

// Check if the user is already logged in, if yes then redirect him to welcome page
$Nutzergruppen = session_manager('urlaubsplan');

// Prepare calendar View
$Month = date('m');
$Year = date('Y');
if(isset($_POST['action_change_date'])){
    if(is_numeric($_POST['month'])){
        $Month = $_POST['month'];
    }
    if(is_numeric($_POST['year'])){
        $Year = $_POST['year'];
    }
}

// Build content
$HTML = "<h1 class='text-center'>Urlaubsübersicht</h1>";
$HTML .= urlaubsplan_funktionsbuttons($Month,$Year);
$HTML .= urlaubsplan_tabelle_management($Month,$Year);

// Space Out stuff
$HTML = grid_gap_generator($HTML);

echo site_body('Urlaubsübersicht', $HTML, true, $Nutzergruppen, true);