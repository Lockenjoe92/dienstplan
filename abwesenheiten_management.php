<?php
// Abwesenheiten Management - a site that shows all vacancies and allows management to manually add them

// Include config file
include_once "./config/dependencies.php";

// Check if the user is already logged in, if yes then redirect him to welcome page
if(isset($_GET['org_ue'])){
    if(intval($_GET['org_ue'])>0){
        $Role = "ausfaelle_".$_GET['org_ue'];
        $UE = $_GET['org_ue'];
    }
} else {
    if(isset($_POST['org_ue'])) {
        $Role = "ausfaelle_" . $_POST['org_ue'];
        $UE = $_POST['org_ue'];
    } else {
        $Role = 'abort';
    }
}

// Check if the user is already logged in, if yes then redirect him to welcome page
$Nutzergruppen = session_manager($Role);
$mysqli = connect_db();
$UEInfos = get_department_infos($mysqli,$UE);
$HTML = "<h1 class='align-content-center'>Abwesenheiten ".$UEInfos['name']."</h1>";

if(isset($_POST['abwesenheitmanagement_go_back'])){
    $HTML .= table_abwesenheiten_management($mysqli,$Nutzergruppen,$UE);
} elseif(isset($_POST['add_abwesenheit_action'])){
    $HTML .= add_entry_abwesenheiten_management($mysqli,$UE);
} elseif (isset($_POST['delete_abwesenheit_action'])) {
    $AbwesenheitObj = get_abwesenheit_data($mysqli,intval($_POST['abwesenheit_id']));
    if(user_can_edit_abwesenheitsantrag($mysqli, $Nutzergruppen, $AbwesenheitObj)){
        $HTML .= delete_entry_abwesenheiten_management($mysqli, $AbwesenheitObj,$UE);
    } else {
        $HTML .= table_abwesenheiten_management($mysqli,$Nutzergruppen,$UE);
    }
} elseif (isset($_POST['decline_abwesenheit_action'])) {
    $AbwesenheitObj = get_abwesenheit_data($mysqli,intval($_POST['abwesenheit_id']));
    if(user_can_edit_abwesenheitsantrag($mysqli, $Nutzergruppen, $AbwesenheitObj)){
        $HTML .= allow_abwesenheiten_management($mysqli, $AbwesenheitObj, 'decline',$UE);
    } else {
        $HTML .= table_abwesenheiten_management($mysqli,$Nutzergruppen,$UE);
    }
} elseif (isset($_POST['accept_abwesenheit_action'])) {
    $AbwesenheitObj = get_abwesenheit_data($mysqli,intval($_POST['abwesenheit_id']));
    if(user_can_edit_abwesenheitsantrag($mysqli, $Nutzergruppen, $AbwesenheitObj)){
        $HTML .= allow_abwesenheiten_management($mysqli, $AbwesenheitObj, 'accept',$UE);
    } else {
        $HTML .= table_abwesenheiten_management($mysqli,$Nutzergruppen,$UE);
    }
}elseif (isset($_POST['edit_abwesenheit_action'])) {
    $AbwesenheitObj = get_abwesenheit_data($mysqli,intval($_POST['abwesenheit_id']));
    if(user_can_edit_abwesenheitsantrag($mysqli, $Nutzergruppen, $AbwesenheitObj)){
        $HTML .= edit_entry_abwesenheiten_management($mysqli, $AbwesenheitObj,$UE);
    } else {
        $HTML .= table_abwesenheiten_management($mysqli,$Nutzergruppen,$UE);
    }
} else {
    if(empty($_GET['mode'])){
        $HTML .= table_abwesenheiten_management($mysqli,$Nutzergruppen,$UE);
    } elseif ($_GET['mode']=='add_abwesenheit'){
        $HTML .= add_entry_abwesenheiten_management($mysqli,$UE);
    } elseif ($_GET['mode']=='delete_abwesenheit'){
        if(is_numeric($_GET['abwesenheit_id'])){
            $AbwesenheitObj = get_abwesenheit_data($mysqli,intval($_GET['abwesenheit_id']));
            if(user_can_edit_abwesenheitsantrag($mysqli, $Nutzergruppen, $AbwesenheitObj)){
                $HTML .= delete_entry_abwesenheiten_management($mysqli, $AbwesenheitObj,$UE);
            } else {
                $HTML .= table_abwesenheiten_management($mysqli,$Nutzergruppen,$UE);
            }
        } else {
            $HTML .= table_abwesenheiten_management($mysqli,$Nutzergruppen,$UE);
        }
    }  elseif ($_GET['mode']=='accept_abwesenheit'){
        if(is_numeric($_GET['abwesenheit_id'])){
            $AbwesenheitObj = get_abwesenheit_data($mysqli,intval($_GET['abwesenheit_id']));
            if(user_can_edit_abwesenheitsantrag($mysqli, $Nutzergruppen, $AbwesenheitObj)){
                $HTML .= allow_abwesenheiten_management($mysqli, $AbwesenheitObj, 'accept',$UE);
            } else {
                $HTML .= table_abwesenheiten_management($mysqli,$Nutzergruppen,$UE);
            }
        } else {
            $HTML .= table_abwesenheiten_management($mysqli,$Nutzergruppen,$UE);
        }
    }  elseif ($_GET['mode']=='decline_abwesenheit') {
        if(is_numeric($_GET['abwesenheit_id'])){
            $AbwesenheitObj = get_abwesenheit_data($mysqli,intval($_GET['abwesenheit_id']));
            if(user_can_edit_abwesenheitsantrag($mysqli, $Nutzergruppen, $AbwesenheitObj)){
                $HTML .= allow_abwesenheiten_management($mysqli, $AbwesenheitObj, 'decline',$UE);
            } else {
                $HTML .= table_abwesenheiten_management($mysqli,$Nutzergruppen,$UE);
            }
        } else {
            $HTML .= table_abwesenheiten_management($mysqli,$Nutzergruppen,$UE);
        }
    }elseif ($_GET['mode']=='edit_abwesenheit') {
        if (is_numeric($_GET['abwesenheit_id'])) {
            $AbwesenheitObj = get_abwesenheit_data($mysqli, intval($_GET['abwesenheit_id']));
            if (user_can_edit_abwesenheitsantrag($mysqli, $Nutzergruppen, $AbwesenheitObj)) {
                $HTML .= edit_entry_abwesenheiten_management($mysqli, $AbwesenheitObj,$UE);
            } else {
                $HTML .= table_abwesenheiten_management($mysqli, $Nutzergruppen,$UE);
            }
        } else {
            $HTML .= table_abwesenheiten_management($mysqli, $Nutzergruppen,$UE);
        }
    } else {
        $HTML .= table_abwesenheiten_management($mysqli,$Nutzergruppen,$UE);
    }
}

echo site_body('Abwesenheiten', $HTML, true, $Nutzergruppen);
