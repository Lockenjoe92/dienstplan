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
$DEtable = table_management_department_events($mysqli);
$HTML .= card_builder('Urlaubsplanerisch relevante Veranstaltungen', '', $DEtable, true, 'h-100');

// Space Out stuff
$HTML = grid_gap_generator($HTML);

echo site_body('Abteilungseinstellungen', $HTML, true, $Nutzergruppen);