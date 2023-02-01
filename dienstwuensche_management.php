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

// Start dem outputs
$HTML = "<h1 class='align-content-center'>Wunschdienst Kalenderübersicht</h1>";
$HTML .= wunschdienstplan_funktionsbuttons_management($Month, $Year);
$HTML .= wunschdienstplan_uebersicht_kalender_management($Month, $Year);
$HTML = grid_gap_generator($HTML);

// Make Pretty Month Name
$format = new IntlDateFormatter('de_DE', IntlDateFormatter::NONE,
    IntlDateFormatter::NONE, NULL, NULL, "MMM");
$monthName = datefmt_format($format, mktime(0, 0, 0, $Month));

$HTML .= "<h1 class='align-content-center'>Dienstwünsche im ".$monthName." ".$Year."</h1>";

if(isset($_POST['wunschdienst_go_back'])){
    $HTML .= table_wunschdienstplan_management($mysqli,$Nutzergruppen, $Month, $Year);
} elseif(isset($_POST['add_dienstwunsch_action'])){
    $HTML .= add_dienstwunsch_user($mysqli);
} elseif (isset($_POST['delete_dienstwunsch_action'])) {
    $dienstwunschObj = get_abwesenheit_data($mysqli,intval($_POST['dienstwunsch_id']));
    if(user_can_edit_dienstwunsch($mysqli, $Nutzergruppen, $dienstwunschObj)){
        $HTML .= delete_dienstwunsch_management($mysqli, $dienstwunschObj);
    } else {
        $HTML .= table_wunschdienstplan_management($mysqli,$Nutzergruppen,$Month, $Year);
    }
} elseif (isset($_POST['edit_dienstwunsch_action'])) {
    $dienstwunschObj = get_dienstwunsch_data($mysqli,intval($_POST['dienstwunsch_id']));
    if(user_can_edit_dienstwunsch($mysqli, $Nutzergruppen, $dienstwunschObj)){
        $HTML .= edit_dienstwunsch_management($mysqli, $dienstwunschObj);
    } else {
        $HTML .= table_wunschdienstplan_management($mysqli,$Nutzergruppen, $Month, $Year);
    }
} else {
    if(empty($_GET['mode'])){
        $HTML .= table_wunschdienstplan_management($mysqli,$Nutzergruppen, $Month, $Year);
    } elseif ($_GET['mode']=='add_dienstwunsch'){
        $HTML .= add_dienstwunsch_user($mysqli);
    } elseif ($_GET['mode']=='delete_dienstwunsch'){
        if(is_numeric($_GET['dienstwunsch_id'])){
            $dienstwunschObj = get_dienstwunsch_data($mysqli,intval($_GET['dienstwunsch_id']));
            if(user_can_edit_dienstwunsch($mysqli, $Nutzergruppen, $dienstwunschObj)){
                $HTML .= delete_dienstwunsch_management($mysqli, $dienstwunschObj);
            } else {
                $HTML .= table_wunschdienstplan_management($mysqli,$Nutzergruppen, $Month, $Year);
            }
        } else {
            $HTML .= table_wunschdienstplan_management($mysqli,$Nutzergruppen, $Month, $Year);
        }
    } elseif ($_GET['mode']=='edit_dienstwunsch'){
        if(is_numeric($_GET['dienstwunsch_id'])){
            $dienstwunschObj = get_dienstwunsch_data($mysqli,intval($_GET['dienstwunsch_id']));
            if(user_can_edit_dienstwunsch($mysqli, $Nutzergruppen, $dienstwunschObj)){
                $HTML .= edit_dienstwunsch_management($mysqli, $dienstwunschObj);
            } else {
                $HTML .= table_wunschdienstplan_management($mysqli,$Nutzergruppen, $Month, $Year);
            }
        } else {
            $HTML .= table_wunschdienstplan_management($mysqli,$Nutzergruppen, $Month, $Year);
        }
    } else {
        $HTML .= table_wunschdienstplan_management($mysqli,$Nutzergruppen, $Month, $Year);
    }
}

echo site_body('Wunschdienst Übersicht', $HTML, true, $Nutzergruppen);