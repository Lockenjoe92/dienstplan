<?php
// Bereitschaftsdienstplan Manager - a site that allows planning of Bereitschaftsdienste

// Include config file
include_once "./config/dependencies.php";

// Check if the user is already logged in, if yes then redirect him to welcome page
// Prepare calendar View
$Year = date('Y');
$Month = date('m');
$ParserOutput = $Err = '';
$mysqli = connect_db();

// Kalender parser
if(isset($_POST['activate_automatik'])){
    $Role = "bereitschaftsdienstplan_1";
    if(is_numeric($_POST['year'])){
        $Year = $_POST['year'];
    }
    if(is_numeric($_POST['month'])){
        $Month = $_POST['month'];
    }

    $Parser = bd_automatik();
    $ParserOutput = $Parser['output'];

} elseif(isset($_POST['action_change_date'])){
    $Role = "bereitschaftsdienstplan_1";
    if(is_numeric($_POST['year'])){
        $Year = $_POST['year'];
    }
    if(is_numeric($_POST['month'])){
        $Month = $_POST['month'];
    }
} elseif (isset($_POST['action_add_bd_zuteilung'])){
    if(is_numeric($_POST['date_concerned'])){
        $DateSelected = $_POST['date_concerned'];
        $Year = date('Y', $DateSelected);
        $Month = date('m', $DateSelected);
    }

    $ParserOutput = parse_add_bd_entry($mysqli);

    $Role = "bereitschaftsdienstplan_1";
} elseif (isset($_POST['action_delete_bd_zuteilung'])){
    if(is_numeric($_POST['date_concerned'])){
        $DateSelected = $_POST['date_concerned'];
        $Year = date('Y', $DateSelected);
        $Month = date('m', $DateSelected);
    }

    $ParserOutput = parse_delete_bd_entry($mysqli);

    $Role = "bereitschaftsdienstplan_1";
} elseif (isset($_POST['action_abort_bd_zuteilung'])){
    if(is_numeric($_POST['date_concerned'])){
        $DateSelected = $_POST['date_concerned'];
        $Year = date('Y', $DateSelected);
        $Month = date('m', $DateSelected);
    }
    $Role = "bereitschaftsdienstplan_1";
} elseif (isset($_POST['action_edit_bd_zuteilung'])){
    if(is_numeric($_POST['date_concerned'])){
        $DateSelected = $_POST['date_concerned'];
        $Year = date('Y', $DateSelected);
        $Month = date('m', $DateSelected);
    }

    $ParserOutput = parse_edit_bd_entry($mysqli);

    $Role = "bereitschaftsdienstplan_1";
} elseif (isset($_POST['save_bd_month_freigabestatus_go'])){
    if(is_numeric($_POST['year'])){
        $Year = $_POST['year'];
    }
    if(is_numeric($_POST['month'])){
        $Month = $_POST['month'];
    }

    $Parser = bd_monat_freigeben($mysqli, $Month, $Year);

    if($Parser['success']){
        $ParserOutput = '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Erfolg!</strong> '.$Parser['meldung'].'<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    } else {
        $ParserOutput = '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Fehler!</strong> '.$Parser['meldung'].'<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    }

    $Role = "bereitschaftsdienstplan_1";
} elseif (isset($_POST['save_bd_month_freigabestatus_delete'])){
    if(is_numeric($_POST['year'])){
        $Year = $_POST['year'];
    }
    if(is_numeric($_POST['month'])){
        $Month = $_POST['month'];
    }

    $Parser = bd_monat_freigabe_zuruecknehmen($mysqli, $Month, $Year);

    if($Parser['success']){
        $ParserOutput = '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Erfolg!</strong> '.$Parser['meldung'].'<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    } else {
        $ParserOutput = '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Fehler!</strong> '.$Parser['meldung'].'<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    }

    $Role = "bereitschaftsdienstplan_1";
} else {
    $Role = "bereitschaftsdienstplan_1";
}

$Nutzergruppen = session_manager($Role);
$mysqli = connect_db();

// Start dem outputs
$HTML = '';

// Catch start Action from link
$ShowEditMode = false;
if(isset($_GET['action'])){
    if($_GET['action']=='edit_bd'){
        $ShowEditMode = true;
        $Month = date('m', $_GET['concerneddate']);
        $Year = date('Y', $_GET['concerneddate']);
    }
}

// Make Pretty Month Name
$format = new IntlDateFormatter('de_DE', IntlDateFormatter::NONE,
    IntlDateFormatter::NONE, NULL, NULL, "MMM");
$monthName = datefmt_format($format, mktime(0, 0, 0, $Month));
$HTML .= "<h1 class='align-self-center'>Bereitschaftsdienstplan ".$monthName." ".$Year."</h1>";

if($ShowEditMode){
    $LastDayOfConcideredMonth = date('Y-m-t', $_GET['concerneddate']);
    $AllUsers = get_sorted_list_of_all_users($mysqli, 'abteilungsrollen DESC, nachname ASC', false, $LastDayOfConcideredMonth);
    $AllBDTypes = get_list_of_all_bd_types($mysqli);
    $AllBDmatrixes = get_list_of_all_bd_matrixes($mysqli);
    $Allwishes = get_sorted_list_of_all_dienstplanwünsche($mysqli);
    $AllWishTypes = get_list_of_all_dienstplanwunsch_types($mysqli);
    $AllBDeinteilungen = get_sorted_list_of_all_bd_einteilungen($mysqli);
    $AllBDassignments = get_all_users_bd_assignments($mysqli);
    $AllAbwesenheiten = get_sorted_list_of_all_abwesenheiten($mysqli);
    $ParserResults = parse_bd_candidates_on_day_for_certain_bd_type($_GET['concerneddate'], $_GET['type'], $AllBDeinteilungen, $Allwishes, $AllBDassignments, $AllAbwesenheiten, $AllWishTypes, $AllUsers, $AllBDTypes, day_is_a_weekend_or_holiday($_GET['concerneddate']));
    $EinteilungenHeute = $ParserResults['assigned_candidates'];
    if(sizeof($EinteilungenHeute)==0){
        $HTML .= container_builder(build_table_bd_planung(0, $_GET['concerneddate'], $ParserResults['candidates'], $_GET['type'], [], $AllBDTypes));
    } else {
        $HTML .= container_builder(build_table_bd_planung(0, $_GET['concerneddate'], $ParserResults['candidates'], $_GET['type'], $EinteilungenHeute, $AllBDTypes));
    }

} else {
    $HTML .= bereitschaftsdienstplan_funktionsbuttons_management($Month, $Year, $Err);
    $HTML .= $ParserOutput;
    $HTML .= bereitschaftsdienstplan_table_management($Month,$Year);
}

$HTML = grid_gap_generator($HTML);

echo site_body('Bereitschaftsdienstplanung', $HTML, true, $Nutzergruppen);