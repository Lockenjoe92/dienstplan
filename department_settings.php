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

//Set display mode depending on POST & GET variables
if(isset($_POST['add_department_event_action_action'])){
    $HTML .= add_department_event_management($mysqli);
} else {
    if(empty($_GET['mode'])){
        //Show Cards with Veranstaltungen
        $DEtable = table_management_department_events($mysqli);
        $HTML .= card_builder('Urlaubsplanerisch relevante Veranstaltungen', '', $DEtable, true, 'h-100');
    } elseif ($_GET['mode']=="add_department_event"){
        $HTML .= add_department_event_management($mysqli);
    }
}

// Space Out stuff
$HTML = grid_gap_generator($HTML);

echo site_body('Abteilungseinstellungen', $HTML, true, $Nutzergruppen);