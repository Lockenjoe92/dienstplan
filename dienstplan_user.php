<?php
// Dashboard - Site that displays essential Information for Users based on their Roles

// Include config file
include_once "./config/dependencies.php";

// Check if the user is already logged in, if yes then redirect him to welcome page
$Nutzergruppen = session_manager('nutzer');
$mysqli =connect_db();
$userID = get_current_user_id();

// Prepare calendar View
$Year = date('Y');
if(isset($_POST['action_change_date'])){
    if(is_numeric($_POST['year'])){
        $Year = $_POST['year'];
    }
}

// Start dem outputs

$HTML = "<div class='p-3'></div>";
$HTMLstandard = '<div class="row row-cols-1 row-cols-md-2 g-4">';
$HTMLstandard .= card_builder('Meine Dienstwünsche', '', table_wunschdienstplan_user($mysqli,$Nutzergruppen), true, 'card h-100 text-center');
$HTMLstandard .= dashboard_view_bereitschaftsdienstplan_user($mysqli, $userID, true);
$HTMLstandard .= '</div>';

if(isset($_POST['wunschdienst_go_back'])){
    $HTML .= $HTMLstandard;
} elseif(isset($_POST['add_dienstwunsch_action'])){
    $HTML .= add_dienstwunsch_user($mysqli);
} elseif (isset($_POST['delete_dienstwunsch_action'])) {
    $dienstwunschObj = get_dienstwunsch_data($mysqli,intval($_POST['dienstwunsch_id']));
    $Diensttyp = get_dienstwunsch_type_data($mysqli, $dienstwunschObj['type']);
    $OrgUE = $Diensttyp['belongs_to_depmnt'];
    if(user_can_edit_dienstwunsch($mysqli, $Nutzergruppen, $dienstwunschObj,$OrgUE)){
        $HTML .= delete_dienstwunsch_user($mysqli, $dienstwunschObj);
    } else {
        $HTML .= $HTMLstandard;
    }
} elseif (isset($_POST['edit_dienstwunsch_action'])) {
    $dienstwunschObj = get_dienstwunsch_data($mysqli,intval($_POST['dienstwunsch_id']));
    $Diensttyp = get_dienstwunsch_type_data($mysqli, $dienstwunschObj['type']);
    $OrgUE = $Diensttyp['belongs_to_depmnt'];
    if(user_can_edit_dienstwunsch($mysqli, $Nutzergruppen, $dienstwunschObj, $OrgUE)){
        $HTML .= edit_dienstwunsch_user($mysqli, $dienstwunschObj);
    } else {
        $HTML .= $HTMLstandard;
    }
} else {
    if(empty($_GET['mode'])){
        $HTML .= $HTMLstandard;
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
                $HTML .= $HTMLstandard;
            }
        } else {
            $HTML .= $HTMLstandard;
        }
    } elseif ($_GET['mode']=='edit_dienstwunsch'){
        if(is_numeric($_GET['dienstwunsch_id'])){
            $dienstwunschObj = get_dienstwunsch_data($mysqli,intval($_GET['dienstwunsch_id']));
            $Diensttyp = get_dienstwunsch_type_data($mysqli, $dienstwunschObj['type']);
            $OrgUE = $Diensttyp['belongs_to_depmnt'];
            if(user_can_edit_dienstwunsch($mysqli, $Nutzergruppen, $dienstwunschObj,$OrgUE)){
                $HTML .= edit_dienstwunsch_user($mysqli, $dienstwunschObj);
            } else {
                $HTML .= $HTMLstandard;
            }
        } else {
            $HTML .= $HTMLstandard;
        }
    } else {
        $HTML .= $HTMLstandard;
    }
}

echo site_body('Meine Dienstplanwünsche', container_builder($HTML, 'container text-center'), true, $Nutzergruppen);