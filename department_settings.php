<?php
// Department Settings

// Include config file
include_once "./config/dependencies.php";

// Check if the user is already logged in, if yes then redirect him to welcome page
$Nutzergruppen = session_manager('department_settings_view');
if(in_array('admin', explode(',',$Nutzergruppen))){
    $Admin = true;
} else {
    $Admin = false;
}
$mysqli = connect_db();

// Build content
$HTML = "<h1 class='align-content-center'>Abteilungseinstellungen</h1>";

//Default Content
//Show Cards with Veranstaltungen
$DEtable = table_management_department_events($mysqli);
$DefaultHTML = card_builder('Urlaubsplanerisch relevante Veranstaltungen', '', $DEtable, true, 'h-100');

//Set display mode depending on POST & GET variables
if(isset($_POST['add_department_event_action_action'])){
    $HTML .= add_department_event_management($mysqli);
} elseif (isset($_POST['edit_department_event_action_action'])){
    if(is_numeric($_POST['edit_department_event_id'])){
        $HTML .= edit_department_event_management($mysqli, $_POST['edit_department_event_id']);
    } else {
        $HTML .= $DefaultHTML;
    }
} elseif (isset($_POST['delete_department_event_action_action'])){
    if(is_numeric($_POST['delete_department_event_id'])){
        $HTML .= delete_department_event_management($mysqli, $_POST['delete_department_event_id']);
    } else {
        $HTML .= $DefaultHTML;
    }
} else {
    if(empty($_GET['mode'])){
        $HTML .= $DefaultHTML;
    } elseif ($_GET['mode']=="add_department_event"){
        $HTML .= add_department_event_management($mysqli);
    } elseif ($_GET['mode']=="edit_department_event"){
        if(is_numeric($_GET['event_id'])){
            $HTML .= edit_department_event_management($mysqli, $_GET['event_id']);
        } else {
            $HTML .= $DefaultHTML;
        }
    } elseif ($_GET['mode']=="delete_department_event"){
        if(is_numeric($_GET['event_id'])){
            $HTML .= delete_department_event_management($mysqli, $_GET['event_id']);
        } else {
            $HTML .= $DefaultHTML;
        }
    }
}

// Space Out stuff
$HTML = grid_gap_generator($HTML);

echo site_body('Abteilungseinstellungen', $HTML, true, $Nutzergruppen);