<?php
// Abwesenheiten Management - a site that shows all vacancies and allows management to manually add them

// Include config file
include_once "./config/dependencies.php";

// Check if the user is already logged in, if yes then redirect him to welcome page
$Nutzergruppen = session_manager('ausfaelle');
$mysqli = connect_db();

$HTML = "<h1 class='align-content-center'>Abwesenheiten</h1>";

if(isset($_POST['abwesenheitmanagement_go_back'])){
    $HTML .= table_abwesenheiten_management($mysqli,$Nutzergruppen);
} elseif(isset($_POST['add_abwesenheit_action'])){
    $HTML .= add_entry_abwesenheiten_management($mysqli);
} elseif (isset($_POST['delete_abwesenheit_action'])) {
    $AbwesenheitObj = get_abwesenheit_data($mysqli,intval($_POST['abwesenheit_id']));
    if(user_can_edit_abwesenheitsantrag($mysqli, $Nutzergruppen, $AbwesenheitObj)){
        $HTML .= delete_entry_abwesenheiten_management($mysqli, $AbwesenheitObj);
    } else {
        $HTML .= table_abwesenheiten_management($mysqli,$Nutzergruppen);
    }
} elseif (isset($_POST['decline_abwesenheit_action'])) {
    $AbwesenheitObj = get_abwesenheit_data($mysqli,intval($_POST['abwesenheit_id']));
    if(user_can_edit_abwesenheitsantrag($mysqli, $Nutzergruppen, $AbwesenheitObj)){
        $HTML .= allow_abwesenheiten_management($mysqli, $AbwesenheitObj, 'decline');
    } else {
        $HTML .= table_abwesenheiten_management($mysqli,$Nutzergruppen);
    }
} elseif (isset($_POST['accept_abwesenheit_action'])) {
    $AbwesenheitObj = get_abwesenheit_data($mysqli,intval($_POST['abwesenheit_id']));
    if(user_can_edit_abwesenheitsantrag($mysqli, $Nutzergruppen, $AbwesenheitObj)){
        $HTML .= allow_abwesenheiten_management($mysqli, $AbwesenheitObj, 'accept');
    } else {
        $HTML .= table_abwesenheiten_management($mysqli,$Nutzergruppen);
    }
} else {
    if(empty($_GET['mode'])){
        $HTML .= table_abwesenheiten_management($mysqli,$Nutzergruppen);
    } elseif ($_GET['mode']=='add_abwesenheit'){
        $HTML .= add_entry_abwesenheiten_management($mysqli);
    } elseif ($_GET['mode']=='delete_abwesenheit'){
        if(is_numeric($_GET['abwesenheit_id'])){
            $AbwesenheitObj = get_abwesenheit_data($mysqli,intval($_GET['abwesenheit_id']));
            if(user_can_edit_abwesenheitsantrag($mysqli, $Nutzergruppen, $AbwesenheitObj)){
                $HTML .= delete_entry_abwesenheiten_management($mysqli, $AbwesenheitObj);
            } else {
                $HTML .= table_abwesenheiten_management($mysqli,$Nutzergruppen);
            }
        } else {
            $HTML .= table_abwesenheiten_management($mysqli,$Nutzergruppen);
        }
    }  elseif ($_GET['mode']=='accept_abwesenheit'){
        if(is_numeric($_GET['abwesenheit_id'])){
            $AbwesenheitObj = get_abwesenheit_data($mysqli,intval($_GET['abwesenheit_id']));
            if(user_can_edit_abwesenheitsantrag($mysqli, $Nutzergruppen, $AbwesenheitObj)){
                $HTML .= allow_abwesenheiten_management($mysqli, $AbwesenheitObj, 'accept');
            } else {
                $HTML .= table_abwesenheiten_management($mysqli,$Nutzergruppen);
            }
        } else {
            $HTML .= table_abwesenheiten_management($mysqli,$Nutzergruppen);
        }
    }  elseif ($_GET['mode']=='decline_abwesenheit') {
        if(is_numeric($_GET['abwesenheit_id'])){
            $AbwesenheitObj = get_abwesenheit_data($mysqli,intval($_GET['abwesenheit_id']));
            if(user_can_edit_abwesenheitsantrag($mysqli, $Nutzergruppen, $AbwesenheitObj)){
                $HTML .= allow_abwesenheiten_management($mysqli, $AbwesenheitObj, 'decline');
            } else {
                $HTML .= table_abwesenheiten_management($mysqli,$Nutzergruppen);
            }
        } else {
            $HTML .= table_abwesenheiten_management($mysqli,$Nutzergruppen);
        }
    } else {
        $HTML .= table_abwesenheiten_management($mysqli,$Nutzergruppen);
    }
}

echo site_body('Abwesenheiten', $HTML, true, $Nutzergruppen);
