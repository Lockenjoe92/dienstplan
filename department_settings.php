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

// Build content
$HTML = "<h1 class='align-content-center'>Abteilungseinstellungen</h1>";

// Space Out stuff
$HTML = grid_gap_generator($HTML);

echo site_body('Abteilungseinstellungen', $HTML, true, $Nutzergruppen);