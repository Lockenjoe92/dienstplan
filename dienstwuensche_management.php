<?php
// Dienstwünsche Management - a site that shows all shift wishes and allows management to manually add them

// Include config file
include_once "./config/dependencies.php";

// Check if the user is already logged in, if yes then redirect him to welcome page
if(intval($_GET['org_ue'])>0){
    $UE = intval($_GET['org_ue']);
    $Role = "dienstplan_".$_GET['org_ue'];
} else {
    $Role = 'dienstplan';
}

$Nutzergruppen = session_manager($Role);
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

// Make Pretty Month Name
$format = new IntlDateFormatter('de_DE', IntlDateFormatter::NONE,
    IntlDateFormatter::NONE, NULL, NULL, "MMM");
$monthName = datefmt_format($format, mktime(0, 0, 0, $Month));

$HTML = "<h1 class='align-content-center'>Dienstwünsche im ".$monthName." ".$Year."</h1>";
$HTML .= wunschdienstplan_funktionsbuttons_management($Month, $Year);

if(isset($_POST['wunschdienst_go_back'])){
    $HTML .= table_wunschdienstplan_management($mysqli,$Nutzergruppen, $Month, $Year, $UE);
} elseif(isset($_POST['add_dienstwunsch_action'])){
    $HTML .= add_dienstwunsch_user($mysqli, $UE);
} elseif (isset($_POST['delete_dienstwunsch_action'])) {
    $dienstwunschObj = get_dienstwunsch_data($mysqli,intval($_POST['dienstwunsch_id']));
    if(user_can_edit_dienstwunsch($mysqli, $Nutzergruppen, $dienstwunschObj)){
        $HTML .= delete_dienstwunsch_management($mysqli, $dienstwunschObj, $UE);
    } else {
        $HTML .= table_wunschdienstplan_management($mysqli,$Nutzergruppen,$Month, $Year, $UE);
    }
} elseif (isset($_POST['edit_dienstwunsch_action'])) {
    $dienstwunschObj = get_dienstwunsch_data($mysqli,intval($_POST['dienstwunsch_id']));
    if(user_can_edit_dienstwunsch($mysqli, $Nutzergruppen, $dienstwunschObj)){
        $HTML .= edit_dienstwunsch_management($mysqli, $dienstwunschObj, $UE);
    } else {
        $HTML .= table_wunschdienstplan_management($mysqli,$Nutzergruppen, $Month, $Year, $UE);
    }
} else {
    if(empty($_GET['mode'])){
        $HTML .= table_wunschdienstplan_management($mysqli,$Nutzergruppen, $Month, $Year, $UE);
    } elseif ($_GET['mode']=='add_dienstwunsch'){
        $HTML .= add_dienstwunsch_user($mysqli, $UE);
    } elseif ($_GET['mode']=='delete_dienstwunsch'){
        if(is_numeric($_GET['dienstwunsch_id'])){
            $dienstwunschObj = get_dienstwunsch_data($mysqli,intval($_GET['dienstwunsch_id']));
            if(user_can_edit_dienstwunsch($mysqli, $Nutzergruppen, $dienstwunschObj)){
                $HTML .= delete_dienstwunsch_management($mysqli, $dienstwunschObj, $UE);
            } else {
                $HTML .= table_wunschdienstplan_management($mysqli,$Nutzergruppen, $Month, $Year, $UE);
            }
        } else {
            $HTML .= table_wunschdienstplan_management($mysqli,$Nutzergruppen, $Month, $Year, $UE);
        }
    } elseif ($_GET['mode']=='edit_dienstwunsch'){
        if(is_numeric($_GET['dienstwunsch_id'])){
            $dienstwunschObj = get_dienstwunsch_data($mysqli,intval($_GET['dienstwunsch_id']));
            if(user_can_edit_dienstwunsch($mysqli, $Nutzergruppen, $dienstwunschObj)){
                $HTML .= edit_dienstwunsch_management($mysqli, $dienstwunschObj, $UE);
            } else {
                $HTML .= table_wunschdienstplan_management($mysqli,$Nutzergruppen, $Month, $Year, $UE);
            }
        } else {
            $HTML .= table_wunschdienstplan_management($mysqli,$Nutzergruppen, $Month, $Year, $UE);
        }
    } else {
        $HTML .= table_wunschdienstplan_management($mysqli,$Nutzergruppen, $Month, $Year, $UE);
    }
}

$HTML = grid_gap_generator($HTML);

echo site_body('Wunschdienst Übersicht', $HTML, true, $Nutzergruppen);