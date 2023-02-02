<?php
// Dashboard - Site that displays essential Information for Users based on their Roles

// Include config file
include_once "./config/dependencies.php";

// Check if the user is already logged in, if yes then redirect him to welcome page
$Nutzergruppen = session_manager('nutzer');
$mysqli =connect_db();

// Prepare calendar View
$Year = date('Y');
if(isset($_POST['action_change_date'])){
    if(is_numeric($_POST['year'])){
        $Year = $_POST['year'];
    }
}

// Start dem outputs
#$HTML = "<h1 class='align-content-center'>Wunschdienst Kalenderübersicht</h1>";
#$HTML .= wunschdienstplan_funktionsbuttons_user($Year);
#$HTML .= wunschdienstplan_uebersicht_kalender_user($Year);
#$HTML = grid_gap_generator($HTML);

$HTML = "<h1 class='align-content-center'>Meine Wünsche</h1>";

if(isset($_POST['wunschdienst_go_back'])){
    $HTML .= table_wunschdienstplan_user($mysqli,$Nutzergruppen);
} elseif(isset($_POST['add_dienstwunsch_action'])){
    $HTML .= add_dienstwunsch_user($mysqli);
} elseif (isset($_POST['delete_dienstwunsch_action'])) {
    $dienstwunschObj = get_dienstwunsch_data($mysqli,intval($_POST['dienstwunsch_id']));
    $Diensttyp = get_dienstwunsch_type_data($mysqli, $dienstwunschObj['type']);
    $OrgUE = $Diensttyp['belongs_to_depmnt'];
    if(user_can_edit_dienstwunsch($mysqli, $Nutzergruppen, $dienstwunschObj,$OrgUE)){
        $HTML .= delete_dienstwunsch_user($mysqli, $dienstwunschObj);
    } else {
        $HTML .= table_wunschdienstplan_user($mysqli,$Nutzergruppen);
    }
} elseif (isset($_POST['edit_dienstwunsch_action'])) {
    $dienstwunschObj = get_dienstwunsch_data($mysqli,intval($_POST['dienstwunsch_id']));
    $Diensttyp = get_dienstwunsch_type_data($mysqli, $dienstwunschObj['type']);
    $OrgUE = $Diensttyp['belongs_to_depmnt'];
    if(user_can_edit_dienstwunsch($mysqli, $Nutzergruppen, $dienstwunschObj, $OrgUE)){
        $HTML .= edit_dienstwunsch_user($mysqli, $dienstwunschObj);
    } else {
        $HTML .= table_wunschdienstplan_user($mysqli,$Nutzergruppen);
    }
} else {
    if(empty($_GET['mode'])){
        $HTML .= table_wunschdienstplan_user($mysqli,$Nutzergruppen);
    } elseif ($_GET['mode']=='add_dienstwunsch'){
        $HTML .= add_dienstwunsch_user($mysqli);
    } elseif ($_GET['mode']=='delete_dienstwunsch'){
        if(is_numeric($_GET['dienstwunsch_id'])){
            $dienstwunschObj = get_dienstwunsch_data($mysqli,intval($_GET['dienstwunsch_id']));
            $Diensttyp = get_dienstwunsch_type_data($mysqli, $dienstwunschObj['type']);
            $OrgUE = $Diensttyp['belongs_to_depmnt'];
            if(user_can_edit_dienstwunsch($mysqli, $Nutzergruppen, $dienstwunschObj,$OrgUE)){
                $HTML .= delete_dienstwunsch_user($mysqli, $dienstwunschObj);
            } else {
                $HTML .= table_wunschdienstplan_user($mysqli,$Nutzergruppen);
            }
        } else {
            $HTML .= table_wunschdienstplan_user($mysqli,$Nutzergruppen);
        }
    } elseif ($_GET['mode']=='edit_dienstwunsch'){
        if(is_numeric($_GET['dienstwunsch_id'])){
            $dienstwunschObj = get_dienstwunsch_data($mysqli,intval($_GET['dienstwunsch_id']));
            $Diensttyp = get_dienstwunsch_type_data($mysqli, $dienstwunschObj['type']);
            $OrgUE = $Diensttyp['belongs_to_depmnt'];
            if(user_can_edit_dienstwunsch($mysqli, $Nutzergruppen, $dienstwunschObj,$OrgUE)){
                $HTML .= edit_dienstwunsch_user($mysqli, $dienstwunschObj);
            } else {
                $HTML .= table_wunschdienstplan_user($mysqli,$Nutzergruppen);
            }
        } else {
            $HTML .= table_wunschdienstplan_user($mysqli,$Nutzergruppen);
        }
    } else {
        $HTML .= table_wunschdienstplan_user($mysqli,$Nutzergruppen);
    }
}

echo site_body('Meine Dienstplanwünsche', $HTML, true, $Nutzergruppen);