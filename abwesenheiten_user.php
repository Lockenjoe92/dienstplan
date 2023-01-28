<?php
// Abwesenheiten Management - a site that shows all vacancies and allows management to manually add them

// Include config file
include_once "./config/dependencies.php";

// Check if the user is already logged in, if yes then redirect him to welcome page
$Nutzergruppen = session_manager('nutzer');
$mysqli = connect_db();


// Prepare calendar View
$Month = date('m');
$Year = date('Y');
if(isset($_POST['action_change_date'])){
    if(is_numeric($_POST['month'])){
        $Month = $_POST['month'];
    }
    if(is_numeric($_POST['year'])){
        $Year = $_POST['year'];
    }
}

// Start dem outputs
$HTML = "<h1 class='align-content-center'>Urlaubsübersicht</h1>";
$HTML .= urlaubsplan_funktionsbuttons($Month, $Year);
$HTML .= urlaubsplan_tabelle_user($Month, $Year);
$HTML = grid_gap_generator($HTML);

$HTML .= "<h1 class='align-content-center'>Meine Abwesenheitsanträge</h1>";

if(isset($_POST['abwesenheitmanagement_go_back'])){
    $HTML .= table_abwesenheiten_user($mysqli,$Nutzergruppen);
} elseif(isset($_POST['add_abwesenheit_action'])){
    $HTML .= add_entry_abwesenheiten_user($mysqli);
} elseif (isset($_POST['delete_abwesenheit_action'])) {
    $AbwesenheitObj = get_abwesenheit_data($mysqli,intval($_POST['abwesenheit_id']));
    if(user_can_edit_abwesenheitsantrag($mysqli, $Nutzergruppen, $AbwesenheitObj)){
        $HTML .= delete_entry_abwesenheiten_user($mysqli, $AbwesenheitObj);
    } else {
        $HTML .= table_abwesenheiten_user($mysqli,$Nutzergruppen);
    }
} elseif (isset($_POST['edit_abwesenheit_action'])) {
    $AbwesenheitObj = get_abwesenheit_data($mysqli,intval($_POST['abwesenheit_id']));
    if(user_can_edit_abwesenheitsantrag($mysqli, $Nutzergruppen, $AbwesenheitObj)){
        $HTML .= edit_entry_abwesenheiten_user($mysqli, $AbwesenheitObj);
    } else {
        $HTML .= table_abwesenheiten_user($mysqli,$Nutzergruppen);
    }
} else {
    if(empty($_GET['mode'])){
        $HTML .= table_abwesenheiten_user($mysqli,$Nutzergruppen);
    } elseif ($_GET['mode']=='add_abwesenheit'){
        $HTML .= add_entry_abwesenheiten_user($mysqli);
    } elseif ($_GET['mode']=='delete_abwesenheit'){
        if(is_numeric($_GET['abwesenheit_id'])){
            $AbwesenheitObj = get_abwesenheit_data($mysqli,intval($_GET['abwesenheit_id']));
            if(user_can_edit_abwesenheitsantrag($mysqli, $Nutzergruppen, $AbwesenheitObj)){
                $HTML .= delete_entry_abwesenheiten_user($mysqli, $AbwesenheitObj);
            } else {
                $HTML .= table_abwesenheiten_user($mysqli,$Nutzergruppen);
            }
        } else {
            $HTML .= table_abwesenheiten_user($mysqli,$Nutzergruppen);
        }
    } elseif ($_GET['mode']=='edit_abwesenheit'){
        if(is_numeric($_GET['abwesenheit_id'])){
            $AbwesenheitObj = get_abwesenheit_data($mysqli,intval($_GET['abwesenheit_id']));
            if(user_can_edit_abwesenheitsantrag($mysqli, $Nutzergruppen, $AbwesenheitObj)){
                $HTML .= edit_entry_abwesenheiten_user($mysqli, $AbwesenheitObj);
            } else {
                $HTML .= table_abwesenheiten_user($mysqli,$Nutzergruppen);
            }
        } else {
            $HTML .= table_abwesenheiten_user($mysqli,$Nutzergruppen);
        }
    } else {
        $HTML .= table_abwesenheiten_user($mysqli,$Nutzergruppen);
    }
}

echo site_body('Meine Abwesenheitsanträge', $HTML, true, $Nutzergruppen);
