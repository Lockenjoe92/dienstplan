<?php
// Bereitschaftsdienstplan Manager - a site that allows planning of Bereitschaftsdienste

// Include config file
include_once "./config/dependencies.php";

// Check if the user is already logged in, if yes then redirect him to welcome page
// Prepare calendar View
$Year = date('Y');
$Month = date('m');

// Kalender parser
if(isset($_POST['action_change_date'])){
    $Role = "bereitschaftsdienstplan_1";
    if(is_numeric($_POST['year'])){
        $Year = $_POST['year'];
    }
    if(is_numeric($_POST['month'])){
        $Month = $_POST['month'];
    }
} else {
    $Role = "bereitschaftsdienstplan_1";
}

$Nutzergruppen = session_manager($Role);
$mysqli = connect_db();

// Start dem outputs
$HTML = '';

// Make Pretty Month Name
$format = new IntlDateFormatter('de_DE', IntlDateFormatter::NONE,
    IntlDateFormatter::NONE, NULL, NULL, "MMM");
$monthName = datefmt_format($format, mktime(0, 0, 0, $Month));

$HTML .= "<h1 class='align-self-center'>Bereitschaftsdienstplan ".$monthName." ".$Year."</h1>";
$HTML .= bereitschaftsdienstplan_funktionsbuttons_management($Month, $Year);
$HTML .= bereitschaftsdienstplan_table_management($Month,$Year);

$HTML = grid_gap_generator($HTML);

echo site_body('Wunschdienst Übersicht', $HTML, true, $Nutzergruppen);