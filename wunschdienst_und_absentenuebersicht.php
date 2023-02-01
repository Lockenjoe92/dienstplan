<?php
// Abwesenheiten Management - a site that shows all vacancies and allows management to manually add them

// Include config file
include_once "./config/dependencies.php";

// Check if the user is already logged in, if yes then redirect him to welcome page
$Nutzergruppen = session_manager('dienstplan');
$mysqli = connect_db();

// Prepare calendar View
$Year = date('Y');
$Month = date('m');

if(isset($_POST['action_change_date'])){
    if(is_numeric($_POST['year'])){
        $Year = $_POST['year'];
    }
    if(is_numeric($_POST['month'])){
        $Month = $_POST['month'];
    }
}

// Make Pretty Month Name
$format = new IntlDateFormatter('de_DE', IntlDateFormatter::NONE,
    IntlDateFormatter::NONE, NULL, NULL, "MMM");
$monthName = datefmt_format($format, mktime(0, 0, 0, $Month));

// Start dem outputs
$HTML = "<h1 class='align-content-center'>Wunschdienst- und Abwesenheitenübersicht im ".$monthName." ".$Year."</h1>";
$HTML .= wunschdienstplan_funktionsbuttons_management($Month, $Year);
$HTML .= wunschdienstplan_uebersicht_kalender_management($Month, $Year);
$HTML = grid_gap_generator($HTML);

echo site_body('Wunschdienst- und Abwesenheitenübersicht', $HTML, true, $Nutzergruppen);
