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
//Feiertage
$FeiertageFormHTML = form_group_dropdown_gesetzte_feiertage('Bislang erfasste Feiertage', 'feiertage', '', false);
$FeiertageFormHTML .= form_group_continue_return_buttons(true, 'Hinzufügen', 'add_feiertag', 'btn btn-primary', true, 'Löschen', 'delete_feiertag', 'btn btn-danger', false);
$FeiertageFormHTML = grid_gap_generator($FeiertageFormHTML);
$FeiertageFormHTML = form_builder($FeiertageFormHTML, 'self', 'POST');
$DefaultHTML = card_builder('Feiertage', '', $FeiertageFormHTML, true, 'h-100');

//Besondere Dienstwunschgrenztage
$GrenztageFormHTML = form_group_dropdown_gesetzte_dw_grenztage('Bislang erfasste Dienstwunsch-Grenztage (Info: MitarbeiterInnen können bis <b>einschließlich</b> dieses Datums Dienstwünsche eingeben!', 'dw_grenztage', '', false);
$GrenztageFormHTML .= form_group_continue_return_buttons(true, 'Hinzufügen', 'add_grenztag', 'btn btn-primary', true, 'Löschen', 'delete_grenztag', 'btn btn-danger', false);
$GrenztageFormHTML = grid_gap_generator($GrenztageFormHTML);
$GrenztageFormHTML = form_builder($GrenztageFormHTML, 'self', 'POST');
$DefaultHTML .= card_builder('Dienstwunsch-Grenztage', '', $GrenztageFormHTML, true, 'h-100');


//Show Cards with Veranstaltungen
$DEtable = table_management_department_events($mysqli);
$DefaultHTML .= card_builder('Urlaubsplanerisch relevante Veranstaltungen', '', $DEtable, true, 'h-100');

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
} elseif (isset($_POST['add_feiertag'])){
    $HTML .= add_department_feiertag_management();
} elseif (isset($_POST['add_grenztag'])){
    $HTML .= add_department_dw_grenztag_management();
} elseif (isset($_POST['add_department_feiertag_action_action'])){
    $HTML .= add_department_feiertag_management();
} elseif (isset($_POST['add_department_dw_grenztag_action_action'])){
    $HTML .= add_department_dw_grenztag_management();
}elseif (isset($_POST['delete_feiertag'])){
    $HTML .= delete_department_feiertag_management();
} elseif (isset($_POST['delete_grenztag'])){
    $HTML .= delete_department_dw_grenztag_management();
} elseif (isset($_POST['delete_department_feiertag_action_action'])){
    $HTML .= delete_department_feiertag_management();
} elseif (isset($_POST['delete_department_dw_grenztag_action_action'])){
    $HTML .= delete_department_dw_grenztag_management();
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