<?php
// Dienstwünsche Management - a site that shows all shift wishes and allows management to manually add them

// Include config file
include_once "./config/dependencies.php";

// Check if the user is already logged in, if yes then redirect him to welcome page
// Prepare calendar View
$Year = date('Y');
$Month = date('m');

// Kalender parser
if(isset($_POST['action_change_date'])){
    if(is_numeric($_POST['year'])){
        $Year = $_POST['year'];
    }
    if(is_numeric($_POST['month'])){
        $Month = $_POST['month'];
    }
}

if((isset($_POST['wunschdienst_go_back'])) OR (isset($_POST['add_dienstwunsch_action'])) OR (isset($_POST['delete_dienstwunsch_action'])) OR (isset($_POST['edit_dienstwunsch_action']))){
    $Role = "dienstplan_".$_POST['org_ue'];
    $UE = $_POST['org_ue'];
} else {
    if(intval($_GET['org_ue'])>0){
        $Role = "dienstplan_".$_GET['org_ue'];
        $UE = $_GET['org_ue'];
    } else {
        $Role = 'dienstplan';
    }
}

$Nutzergruppen = session_manager($Role);
$mysqli = connect_db();

if(isset($_POST['action_change_date'])){
    if(is_numeric($_POST['year'])){
        $Year = $_POST['year'];
    }
    if(is_numeric($_POST['month'])){
        $Month = $_POST['month'];
    }
}

// Start dem outputs
$HTML = '';

// Make Pretty Month Name
$format = new IntlDateFormatter('de_DE', IntlDateFormatter::NONE,
    IntlDateFormatter::NONE, NULL, NULL, "MMM");
$monthName = datefmt_format($format, mktime(0, 0, 0, $Month));

if(isset($_POST['wunschdienst_go_back'])){
    $HTML .= "<h1 class='align-content-center'>Dienstwünsche im ".$monthName." ".$Year."</h1>";
    $HTML .= wunschdienstplan_funktionsbuttons_management($Month, $Year);
    $HTML .= table_wunschdienstplan_management($mysqli,$Nutzergruppen, $Month, $Year);
} elseif(isset($_POST['add_dienstwunsch_action'])){
    $HTML .= add_dienstwunsch_management($mysqli);
} elseif (isset($_POST['delete_dienstwunsch_action'])) {
    $dienstwunschObj = get_dienstwunsch_data($mysqli,intval($_POST['dienstwunsch_id']));
    if(user_can_edit_dienstwunsch($mysqli, $Nutzergruppen, $dienstwunschObj, $UE)){
        $HTML .= delete_dienstwunsch_management($mysqli, $dienstwunschObj);
    } else {
        $HTML .= "<h1 class='align-content-center'>Dienstwünsche im ".$monthName." ".$Year."</h1>";
        $HTML .= wunschdienstplan_funktionsbuttons_management($Month, $Year);
        $HTML .= table_wunschdienstplan_management($mysqli,$Nutzergruppen,$Month, $Year);
    }
} elseif (isset($_POST['edit_dienstwunsch_action'])) {
    $dienstwunschObj = get_dienstwunsch_data($mysqli,intval($_POST['dienstwunsch_id']));
    if(user_can_edit_dienstwunsch($mysqli, $Nutzergruppen, $dienstwunschObj, $UE)){
        $HTML .= edit_dienstwunsch_management($mysqli, $dienstwunschObj);
    } else {
        $HTML .= "<h1 class='align-content-center'>Dienstwünsche im ".$monthName." ".$Year."</h1>";
        $HTML .= wunschdienstplan_funktionsbuttons_management($Month, $Year);
        $HTML .= table_wunschdienstplan_management($mysqli,$Nutzergruppen, $Month, $Year);
    }
} else {
    if(empty($_GET['mode'])){
        $HTML .= "<h1 class='align-content-center'>Dienstwünsche im ".$monthName." ".$Year."</h1>";
        $HTML .= wunschdienstplan_funktionsbuttons_management($Month, $Year);
        $HTML .= table_wunschdienstplan_management($mysqli,$Nutzergruppen, $Month, $Year);
    } elseif ($_GET['mode']=='add_dienstwunsch'){
        $HTML .= add_dienstwunsch_management($mysqli);
    } elseif ($_GET['mode']=='delete_dienstwunsch'){
        if(is_numeric($_GET['dienstwunsch_id'])){
            $dienstwunschObj = get_dienstwunsch_data($mysqli,intval($_GET['dienstwunsch_id']));
            if(user_can_edit_dienstwunsch($mysqli, $Nutzergruppen, $dienstwunschObj, $UE)){
                $HTML .= delete_dienstwunsch_management($mysqli, $dienstwunschObj);
            } else {
                $HTML .= "<h1 class='align-content-center'>Dienstwünsche im ".$monthName." ".$Year."</h1>";
                $HTML .= wunschdienstplan_funktionsbuttons_management($Month, $Year);
                $HTML .= table_wunschdienstplan_management($mysqli,$Nutzergruppen, $Month, $Year);
            }
        } else {
            $HTML .= "<h1 class='align-content-center'>Dienstwünsche im ".$monthName." ".$Year."</h1>";
            $HTML .= wunschdienstplan_funktionsbuttons_management($Month, $Year);
            $HTML .= table_wunschdienstplan_management($mysqli,$Nutzergruppen, $Month, $Year);
        }
    } elseif ($_GET['mode']=='edit_dienstwunsch'){
        if(is_numeric($_GET['dienstwunsch_id'])){
            $dienstwunschObj = get_dienstwunsch_data($mysqli,intval($_GET['dienstwunsch_id']));
            if(user_can_edit_dienstwunsch($mysqli, $Nutzergruppen, $dienstwunschObj, $UE)){
                $HTML .= edit_dienstwunsch_management($mysqli, $dienstwunschObj);
            } else {
                $HTML .= "<h1 class='align-content-center'>Dienstwünsche im ".$monthName." ".$Year."</h1>";
                $HTML .= wunschdienstplan_funktionsbuttons_management($Month, $Year);
                $HTML .= table_wunschdienstplan_management($mysqli,$Nutzergruppen, $Month, $Year);
            }
        } else {
            $HTML .= "<h1 class='align-content-center'>Dienstwünsche im ".$monthName." ".$Year."</h1>";
            $HTML .= wunschdienstplan_funktionsbuttons_management($Month, $Year);
            $HTML .= table_wunschdienstplan_management($mysqli,$Nutzergruppen, $Month, $Year);
        }
    } else {
        $HTML .= "<h1 class='align-content-center'>Dienstwünsche im ".$monthName." ".$Year."</h1>";
        $HTML .= wunschdienstplan_funktionsbuttons_management($Month, $Year);
        $HTML .= table_wunschdienstplan_management($mysqli,$Nutzergruppen, $Month, $Year);
    }
}

$HTML = grid_gap_generator($HTML);

echo site_body('Wunschdienst Übersicht', $HTML, true, $Nutzergruppen);