<?php

function dashboard_view_abwesenheiten_user($mysqli, $userID, $Nutzergruppen){

    $AbwesenheitenTable = table_abwesenheiten_user($mysqli, $Nutzergruppen, $DashboardMode=true);
    return card_builder('Abwesenheiten dieses Jahr', '', $AbwesenheitenTable, true, 'h-100 text-center');

}

function dashboard_view_bereitschaftsdienstplan_user($mysqli, $userID, $h100Mode=true){

    $BDtable = table_bereitschaftsdienstplan_user($mysqli,$userID,date('m'),date('Y'));
    if($h100Mode){
        return card_builder('Bereitschaftsdienste diesen Monat', '', $BDtable, true, 'h-100 text-center');
    }else{
        return card_builder('Bereitschaftsdienste diesen Monat', '', $BDtable, true, '');
    }

}