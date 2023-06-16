<?php
// Department Settings

// Include config file
include_once "./config/dependencies.php";

// Check if the user is already logged in, if yes then redirect him to welcome page
$Nutzergruppen = session_manager('spx_eintragen');
if(in_array('admin', explode(',',$Nutzergruppen))){
    $Admin = true;
} else {
    $Admin = false;
}
$mysqli = connect_db();

// Parse Mode
if(isset($_GET['mode'])){
    $Mode = $_GET['mode'];
} else {
    $Mode = 'terminate';
}

// Build content
$HTML = "<h1 class='align-content-center'>SPX-Management</h1>";

if($Mode=='add_spx_entry'){

    if(isset($_GET['abwesenheit_id'])){
        $AbwesenheitID = $_GET['abwesenheit_id'];
    } else {
        if(isset($_POST['abwesenheit_id'])){
            $AbwesenheitID = $_POST['abwesenheit_id'];
        } else {
            header('Location: dashboard.php');
            die();
        }
    }

    //Run Parser
    $Parser = parse_add_spx_entry($mysqli, $AbwesenheitID);

    //Build Content
    if($Parser['success']==NULL){

        $AbwesenheitInfos = get_abwesenheit_data($mysqli, $AbwesenheitID);

        if($AbwesenheitInfos['bearbeitet_am']=='0000-00-00 00:00:00'){
            $Genehmiger = $AbwesenheitInfos['create_user'];
            $DatumGenehmigt = $AbwesenheitInfos['create_date'];
        } else {
            $Genehmiger = $AbwesenheitInfos['bearbeitet_von'];
            $DatumGenehmigt = $AbwesenheitInfos['bearbeitet_am'];
        }

        $FormHTML = form_group_dropdown_abwesenheitentypen('Abwesenheitstyp', 'type', $AbwesenheitInfos['type'], true, '', true);
        $FormHTML .= form_group_dropdown_all_users('MitarbeiterIn', 'user', $AbwesenheitInfos['user'], 'true', '', true);
        $FormHTML .= form_group_input_date('Beginn Abwesenheit', 'begin', $AbwesenheitInfos['begin'], 'true', '', true);
        $FormHTML .= form_group_input_date('Ende Abwesenheit', 'end', $AbwesenheitInfos['end'], 'true', '', true);
        $FormHTML .= form_group_dropdown_all_users('Genehmigt von', 'genehmiger', $Genehmiger, 'true', '', true);
        $FormHTML .= form_group_input_date('Genehmigt am','date_genehmigt', $DatumGenehmigt, 'true', '', true);
        $FormHTML .= form_hidden_input_generator('abwesenheit_id', $AbwesenheitID);
        $FormHTML .= form_group_continue_return_buttons(true, 'Eintrag festhalten', 'add_spx_action', 'btn btn-primary', true, 'Abbrechen', 'spx_abort', 'btn btn-danger', true, './dashboard.php');

        $FormHTML = form_builder($FormHTML, 'spx_management.php?mode=add_spx_entry', 'POST');

        $HTML = card_builder('SPX Eintrag festhalten', 'Möchten Sie folgende Abwesenheit als in SPX verbucht eintragen?', $FormHTML);

    } else {

        $FormHTML = form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'spx_abort', 'btn btn-primary', true, './dashboard.php');
        $FormHTML = form_builder($FormHTML, '', '');

        $HTML = card_builder('SPX Eintrag festhalten', $Parser['meldung'], $FormHTML);
    }

} else {
    header('Location: dashboard.php');
    die();
}

// Space Out stuff
$HTML = grid_gap_generator($HTML);

echo site_body('Abteilungseinstellungen', $HTML, true, $Nutzergruppen);