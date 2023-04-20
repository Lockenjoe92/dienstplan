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
}  elseif (isset($_POST['action_edit_bd_zuteilung'])){
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

// Make Pretty Month Name
$format = new IntlDateFormatter('de_DE', IntlDateFormatter::NONE,
    IntlDateFormatter::NONE, NULL, NULL, "MMM");
$monthName = datefmt_format($format, mktime(0, 0, 0, $Month));

$HTML .= "<h1 class='align-self-center'>Bereitschaftsdienstplan ".$monthName." ".$Year."</h1>";
$HTML .= bereitschaftsdienstplan_funktionsbuttons_management($Month, $Year, $Err);
$HTML .= $ParserOutput;
$HTML .= bereitschaftsdienstplan_table_management($Month,$Year);

$HTML = grid_gap_generator($HTML);

echo site_body('Bereitschaftsdienstplanung', $HTML, true, $Nutzergruppen);