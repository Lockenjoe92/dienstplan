<?php

function dashboard_view_abwesenheiten_user($mysqli, $userID, $Nutzergruppen){

    $AbwesenheitenTable = table_abwesenheiten_user($mysqli, $Nutzergruppen, $DashboardMode=true);
    return card_builder('Abwesenheiten dieses Jahr', '', $AbwesenheitenTable, true, 'h-100');

}

function dashboard_view_bereitschaftsdienstplan_user($mysqli, $userID){

    $BDtable = table_bereitschaftsdienstplan_user($mysqli,$userID,date('m'),date('Y'));
    return card_builder('Bereitschaftsdienste diesen Monat', '', $BDtable, true, 'h-100');

}