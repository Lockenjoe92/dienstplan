<?php
// Workforce management

// Include config file
include_once "./config/dependencies.php";

// Check if the user is already logged in, if yes then redirect him to welcome page
$Nutzergruppen = session_manager('ausfaelle');

// Build content
$HTML = "<h1 class='align-content-center'>Nutzerverwaltung</h1>";

// Check on special modes (add_user, edit_user)
if(isset($_POST['workforcemanagement_go_back'])){
    $HTML .= table_workforce_management(connect_db());
} elseif(isset($_POST['add_user_action'])){
    $HTML .= add_user_workforce_management(connect_db());
} elseif (isset($_POST['edit_user_action'])){
    $HTML .= edit_user_workforce_management(connect_db());
} else {
    if(empty($_GET['mode'])){
        $HTML .= table_workforce_management(connect_db());
    } elseif ($_GET['mode']=='add_user'){
        $HTML .= add_user_workforce_management(connect_db());
    } elseif ($_GET['mode']=='edit_user'){
        $HTML .= edit_user_workforce_management(connect_db());
    }
}

// Space Out stuff
$HTML = grid_gap_generator($HTML);

echo site_body('Nutzerverwaltung', $HTML, true, $Nutzergruppen);