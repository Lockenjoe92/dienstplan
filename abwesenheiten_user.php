<?php
// Abwesenheiten Management - a site that shows all vacancies and allows management to manually add them

// Include config file
include_once "./config/dependencies.php";

// Check if the user is already logged in, if yes then redirect him to welcome page
$Nutzergruppen = session_manager('nutzer');
$mysqli = connect_db();

$HTML = "<h1 class='align-content-center'>Meine Abwesenheitsanträge</h1>";

if(isset($_POST['abwesenheitmanagement_go_back'])){
    $HTML .= table_abwesenheiten_user($mysqli);
} elseif(isset($_POST['add_abwesenheit_action'])){
    $HTML .= add_entry_abwesenheiten_user($mysqli);
}else {
    if(empty($_GET['mode'])){
        $HTML .= table_abwesenheiten_user($mysqli);
    } elseif ($_GET['mode']=='add_abwesenheit'){
        $HTML .= add_entry_abwesenheiten_user($mysqli);
    }
}

echo site_body('Meine Abwesenheitsanträge', $HTML, true, $Nutzergruppen);
