<?php
// Bereitschaftsdienstplan for users

// Include config file
include_once "./config/dependencies.php";

// Check if the user is already logged in, if yes then redirect him to welcome page
// Prepare calendar View
$Year = date('Y');
$Month = date('m');
$ParserOutput = '';
$mysqli = connect_db();

// Kalender parser
if(isset($_POST['action_change_date'])){
    if(is_numeric($_POST['year'])){
        $Year = $_POST['year'];
    }
    if(is_numeric($_POST['month'])){
        $Month = $_POST['month'];
    }
}

$Nutzergruppen = session_manager();
$mysqli = connect_db();
$Freigaben = lade_bd_freigabestatus_monat($Month, $Year);

// Start dem outputs
$HTML = '';

// Make Pretty Month Name
$format = new IntlDateFormatter('de_DE', IntlDateFormatter::NONE,
    IntlDateFormatter::NONE, NULL, NULL, "MMM");
$monthName = datefmt_format($format, mktime(0, 0, 0, $Month));

$HTML .= "<h1 class='align-self-center'>Bereitschaftsdienstplan ".$monthName." ".$Year."</h1>";
$HTML .= bereitschaftsdienstplan_funktionsbuttons_users($Month, $Year, $Freigaben);
$HTML .= $ParserOutput;
$HTML .= bereitschaftsdienstplan_table_users($Month,$Year,$Freigaben);

$HTML = grid_gap_generator($HTML);

echo site_body('Bereitschaftsdienstplan Anästhesie', $HTML, true, $Nutzergruppen);