<?php
// Workforce management

// Include config file
include_once "./config/dependencies.php";

// Check if the user is already logged in, if yes then redirect him to welcome page
$Nutzergruppen = session_manager('mitarbeitermanagement_view');
if(in_array('admin', explode(',',$Nutzergruppen))){
    $Admin = true;
} else {
    $Admin = false;
}

if(empty($_GET['inactive'])){
    $Show = false;
} else {
    if($_GET['inactive']=='hide'){
        $Show = false;
    } elseif($_GET['inactive']=='show') {
        $Show = true;
    } else {
        $Show = false;
    }
}

// Build content
$HTML = "<h1 class='align-content-center'>Nutzerverwaltung</h1>";

// Check on special modes (add_user, edit_user)
if(isset($_POST['workforcemanagement_go_back'])){
    $HTML .= table_workforce_management(connect_db(),$Admin, $Show);
} elseif(isset($_POST['add_user_action'])){
    $HTML .= add_user_workforce_management(connect_db());
} elseif(isset($_POST['add_user_sondereinteilung_action'])){
    $HTML .= add_user_sondereinteilung_management(connect_db());
} elseif(isset($_POST['add_user_sondereinteilung_action_action'])){
    $HTML .= add_user_sondereinteilung_management(connect_db());
} elseif(isset($_POST['add_user_dienstgruppe_action'])){
    $HTML .= add_user_dienstgruppe_management(connect_db());
} elseif(isset($_POST['add_user_dienstgruppe_action_action'])){
    $HTML .= add_user_dienstgruppe_management(connect_db());
} elseif(isset($_POST['delete_user_sondereinteilung_action'])){
    $HTML .= delete_user_sondereinteilung_management(connect_db());
} elseif(isset($_POST['delete_user_sondereinteilung_action_action'])){
    $HTML .= delete_user_sondereinteilung_management(connect_db());
}elseif(isset($_POST['delete_user_dienstgruppe_action'])){
    $HTML .= delete_user_dienstgruppe_management(connect_db());
} elseif(isset($_POST['delete_user_dienstgruppe_action_action'])){
    $HTML .= delete_user_dienstgruppe_management(connect_db());
} elseif (isset($_POST['edit_user_action'])){
    $HTML .= edit_user_workforce_management(connect_db(), $Admin);
} elseif (isset($_POST['reset_user_password_action'])){
    $HTML .= reset_user_password_workforce_management(connect_db(), $Admin);
} elseif (isset($_POST['edit_user_sondereinteilung_action'])){
    $HTML .= edit_user_sondereinteilung_management(connect_db(), $Admin);
} elseif (isset($_POST['edit_user_sondereinteilung_action_action'])){
    $HTML .= edit_user_sondereinteilung_management(connect_db(), $Admin);
} elseif (isset($_POST['edit_user_dienstgruppe_action'])){
    $HTML .= edit_user_dienstgruppe_management(connect_db(), $Admin);
} elseif (isset($_POST['edit_user_dienstgruppe_action_action'])){
    $HTML .= edit_user_dienstgruppe_management(connect_db(), $Admin);
} elseif (isset($_POST['reset_user_password_action_action'])){
    $HTML .= reset_user_password_workforce_management(connect_db(), $Admin);
} elseif (isset($_POST['abort_user_sondereinteilung_action'])){
    $HTML .= edit_user_workforce_management(connect_db(), $Admin);
} else {
    if(empty($_GET['mode'])){
        $HTML .= table_workforce_management(connect_db(),$Admin, $Show);
    } elseif ($_GET['mode']=='add_user'){
        $HTML .= add_user_workforce_management(connect_db());
    } elseif ($_GET['mode']=='edit_user'){
        $HTML .= edit_user_workforce_management(connect_db(), $Admin);
    } else {
        $HTML .= table_workforce_management(connect_db(),$Admin, $Show);
    }
}

// Space Out stuff
$HTML = grid_gap_generator($HTML);

echo site_body('Nutzerverwaltung', $HTML, true, $Nutzergruppen);