<?php
// Workforce management

// Include config file
include_once "./config/dependencies.php";

// Prepare calendar View
$Year = date('Y');
$Month = date('m');

// Check if the user is already logged in, if yes then redirect him to welcome page
if(isset($_POST['action_change_date'])){
    $Role = "urlaubsplan_".$_POST['org_ue'];
    $UE = $_POST['org_ue'];
    if(is_numeric($_POST['year'])){
        $Year = $_POST['year'];
    }
    if(is_numeric($_POST['month'])){
        $Month = $_POST['month'];
    }
} else {
    if(isset($_GET['org_ue'])){
        if(intval($_GET['org_ue'])>0){
            $Role = "urlaubsplan_".$_GET['org_ue'];
            $UE = $_GET['org_ue'];
        }
    } else {
        if(isset($_POST['org_ue'])) {
            $Role = "urlaubsplan_" . $_POST['org_ue'];
            $UE = $_POST['org_ue'];
        } else {
            $Role = 'abort';
        }
    }
}

// Check if the user is already logged in, if yes then redirect him to welcome page
$Nutzergruppen = session_manager($Role);
$mysqli = connect_db();

// Build content
$mysqli = connect_db();
$UEInfos = get_department_infos($mysqli,$UE);
$HTML = "<h1 class='text-center'>Urlaubsübersicht ".$UEInfos['name']."</h1>";
$HTML .= urlaubsplan_funktionsbuttons($Month,$Year);
$HTML .= urlaubsplan_tabelle_management($Month,$Year,$UE);

// Space Out stuff
$HTML = grid_gap_generator($HTML);

echo site_body('Urlaubsübersicht', $HTML, true, $Nutzergruppen, true);