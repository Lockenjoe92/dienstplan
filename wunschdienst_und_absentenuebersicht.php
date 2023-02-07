<?php
// Abwesenheiten Management - a site that shows all vacancies and allows management to manually add them

// Include config file
include_once "./config/dependencies.php";

// Prepare calendar View
$Year = date('Y');
$Month = date('m');

// Check if the user is already logged in, if yes then redirect him to welcome page
if(isset($_POST['action_change_date'])){
    $Role = "dienstplan_".$_POST['org_ue'];
    if(is_numeric($_POST['year'])){
        $Year = $_POST['year'];
    }
    if(is_numeric($_POST['month'])){
        $Month = $_POST['month'];
    }
} else {
    if(isset($_GET['org_ue'])){
        if(intval($_GET['org_ue'])>0){
            $Role = "dienstplan_".$_GET['org_ue'];
            $UE = $_GET['org_ue'];
        }
    } else {
        if(isset($_POST['org_ue'])) {
            $Role = "dienstplan_" . $_POST['org_ue'];
            $UE = $_POST['org_ue'];
        } else {
            $Role = 'dienstplan';
        }
    }
}

$Nutzergruppen = session_manager($Role);
$mysqli = connect_db();

// Make Pretty Month Name
$format = new IntlDateFormatter('de_DE', IntlDateFormatter::NONE,
    IntlDateFormatter::NONE, NULL, NULL, "MMM");
$monthName = datefmt_format($format, mktime(0, 0, 0, $Month));

// Start dem outputs
$HTML = "<h1 class='align-content-center'>Wunschdienst- und Abwesenheitenübersicht im ".$monthName." ".$Year."</h1>";
$HTML .= wunschdienstplan_funktionsbuttons_management($Month, $Year);
$HTML .= wunschdienstplan_uebersicht_kalender_management($Month, $Year, $UE);
$HTML = grid_gap_generator($HTML);

echo site_body('Wunschdienst- und Abwesenheitenübersicht', $HTML, true, $Nutzergruppen);
