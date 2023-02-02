<?php

function urlaubsplan_funktionsbuttons($Month,$Year){

    $FORMhtml = '<div class="row">';
    $FORMhtml .= "<div class='col'>".form_dropdown_months('month',$Month)."</div>";
    $FORMhtml .= "<div class='col'>".form_dropdown_years('year', $Year)."</div>";
    $FORMhtml .= "<div class='col'>".form_group_continue_return_buttons(true, 'Reset', 'reset_calendar', 'btn-primary', true, 'Zeitraum wählen', 'action_change_date', 'btn-primary')."</div>";
    $FORMhtml .= "</div>";

    $HTML = container_builder(form_builder($FORMhtml, 'self', 'POST'));

    return $HTML;

}

function urlaubsplan_tabelle_user($month, $year){

    $HTML = '';
    $mysqli = connect_db();
    $CurrentUser = get_current_user_id();
    $AllUsers = get_sorted_list_of_all_users($mysqli);
    $AllAbwesenheiten = get_sorted_list_of_all_abwesenheiten($mysqli);
    $FirstDayOfCalendarString = "01-".$month."-".$year;
    $FirstDayOfCalendar = strtotime($FirstDayOfCalendarString);

    //Initialize Array to hold stats
    $DataDays = [];
    $Day = [];
    for($d=0;$d<=31;$d++){
        $Day['total']=0;
        $Day['OA']=0;
        $Day['FA']=0;
        $Day['AA']=0;
        $DataDays[] = $Day;
    }

    // Generate Rows based on Users
    $TableRows = "";
    foreach ($AllUsers as $User) {

        // Don't show people from HR
        if($User['abteilungsrollen']!="Verwaltung"){
            $TableRowContent = "<tr>";

            // Change first columns color depending on Employee status
            if($User['abteilungsrollen']=="OA"){
                $Coloring = "table-danger";
            } elseif ($User['abteilungsrollen']=="FA"){
                $Coloring = "table-warning";
            } elseif ($User['abteilungsrollen']=="AA"){
                $Coloring = "table-success";
            } else {
                $Coloring = "";
            }

            // Get the User Name
            $TableRowContent .= "<td class='".$Coloring."'>".$User['nachname'].", ".$User['vorname']."</td>";

            // Populate the days with information
            for($a=0;$a<31;$a++){
                $Command = "+".$a." days";
                $ThisDay = strtotime($Command, $FirstDayOfCalendar);
                $ThisDayData = $DataDays[$a];

                //Catch Month shift
                if(date("m", $ThisDay)==$month){
                    $ReturnValues = populate_day_urlaubsplan_tabelle_management($ThisDay,$User['id'],$AllAbwesenheiten,$User['abteilungsrollen'], $ThisDayData['total'], $ThisDayData['OA'], $ThisDayData['FA'], $ThisDayData['AA']);
                    $TableRowContent .= $ReturnValues['HTML'];
                    $NewDayStatData['total']=$ReturnValues['total'];
                    $NewDayStatData['OA']=$ReturnValues['OA'];
                    $NewDayStatData['FA']=$ReturnValues['FA'];
                    $NewDayStatData['AA']=$ReturnValues['AA'];
                    $DataDays[$a] = $NewDayStatData;
                }

            }

            $TableRowContent .= "<td>".calculate_total_approved_holiday_days_for_user_in_selected_year($AllAbwesenheiten, $User, $year)."</td>";

            $TableRowContent .= "</tr>";

            if($User['id']==$CurrentUser){
                $TableRows .= $TableRowContent;
            }
        }
    }

    // Build Table header -> this means loading date information
    $TableHeader = "<thead>";

    $TableHeaderRowUsers = "<tr><th></th>";
    $TableHeaderRowTotal = "<tr><th>Gesamtabwesenheiten in Abteilung</th>";
    #$TableHeaderRowOA = "<tr><th>OA</th>";
    #$TableHeaderRowFA = "<tr><th>FA</th>";
    #$TableHeaderRowAA = "<tr><th>AA</th>";

    //Iterate as long as we are still in the same month
    for($a=0;$a<31;$a++){
        $Command = "+".$a." days";
        $ThisDay = strtotime($Command, $FirstDayOfCalendar);
        $DataDay = $DataDays[$a];

        //Catch Month shift
        if(date("m", $ThisDay)==$month){
            $TableHeaderRowUsers .= "<th>".date("d", $ThisDay)."</th>";
            $TableHeaderRowTotal .= "<th>".$DataDay['total']."</th>";
            #$TableHeaderRowOA .= "<th>".$DataDay['OA']."</th>";
            #$TableHeaderRowFA .= "<th>".$DataDay['FA']."</th>";
            #$TableHeaderRowAA .= "<th>".$DataDay['AA']."</th>";
        }

    }

    $TableHeaderRowUsers .= "</tr>";
    $TableHeaderRowTotal .= "</tr>";
    #$TableHeaderRowOA .= "</tr>";
    #$TableHeaderRowFA .= "</tr>";
    #$TableHeaderRowAA .= "</tr>";

    //Build table head rows as wished
    $TableHeader .= $TableHeaderRowUsers;
    $TableHeader .= $TableHeaderRowTotal;
    #$TableHeader .= $TableHeaderRowOA;
    #$TableHeader .= $TableHeaderRowFA;
    #$TableHeader .= $TableHeaderRowAA;
    $TableHeader .= "</thead>";

    // Build table body
    $TableBody = '<tbody class="table-group-divider">'.$TableRows.'</tbody>';

    // Build that calendar
    $Table = "<table class='table table-bordered table-sm table-condensed'>";
    $Table .= $TableHeader;
    $Table .= $TableBody;
    $Table .= "</table>";

    return $Table;
}

function urlaubsplan_tabelle_management($month, $year){

    $HTML = '';
    $mysqli = connect_db();
    $AllUsers = get_sorted_list_of_all_users($mysqli, 'abteilungsrollen DESC, nachname ASC');
    $AllAbwesenheiten = get_sorted_list_of_all_abwesenheiten($mysqli);
    $FirstDayOfCalendarString = "01-".$month."-".$year;
    $FirstDayOfCalendar = strtotime($FirstDayOfCalendarString);

    //Initialize Array to hold stats
    $DataDays = [];
    $Day = [];
    for($d=0;$d<=31;$d++){
        $Day['total']=0;
        $Day['OA']=0;
        $Day['FA']=0;
        $Day['AA']=0;
        $DataDays[] = $Day;
    }

    // Generate Rows based on Users
    $TableRows = "";
    foreach ($AllUsers as $User) {

        // Don't show people from HR
        if($User['abteilungsrollen']!="Verwaltung"){
            $TableRowContent = "<tr>";

            // Change first columns color depending on Employee status
            if($User['abteilungsrollen']=="OA"){
                $Coloring = "table-danger";
            } elseif ($User['abteilungsrollen']=="FA"){
                $Coloring = "table-warning";
            } elseif ($User['abteilungsrollen']=="AA"){
                $Coloring = "table-success";
            } else {
                $Coloring = "";
            }

            // Get the User Name
            $TableRowContent .= "<td class='".$Coloring."'>".$User['nachname'].", ".$User['vorname']."</td>";

            // Populate the days with information
            for($a=0;$a<31;$a++){
                $Command = "+".$a." days";
                $ThisDay = strtotime($Command, $FirstDayOfCalendar);
                $ThisDayData = $DataDays[$a];

                //Catch Month shift
                if(date("m", $ThisDay)==$month){
                    $ReturnValues = populate_day_urlaubsplan_tabelle_management($ThisDay,$User['id'],$AllAbwesenheiten,$User['abteilungsrollen'], $ThisDayData['total'], $ThisDayData['OA'], $ThisDayData['FA'], $ThisDayData['AA']);
                    $TableRowContent .= $ReturnValues['HTML'];
                    $NewDayStatData['total']=$ReturnValues['total'];
                    $NewDayStatData['OA']=$ReturnValues['OA'];
                    $NewDayStatData['FA']=$ReturnValues['FA'];
                    $NewDayStatData['AA']=$ReturnValues['AA'];
                    $DataDays[$a] = $NewDayStatData;
                }

            }

            $TableRowContent .= "<td>".calculate_total_approved_holiday_days_for_user_in_selected_year($AllAbwesenheiten, $User, $year)."</td>";

            $TableRowContent .= "</tr>";
            $TableRows .= $TableRowContent;
        }
    }

    // Build Table header -> this means loading date information
    $TableHeader = "<thead>";

    $TableHeaderRowUsers = "<tr><th></th>";
    $TableHeaderRowTotal = "<tr><th>Gesamt</th>";
    $TableHeaderRowOA = "<tr><th>OA</th>";
    $TableHeaderRowFA = "<tr><th>FA</th>";
    $TableHeaderRowAA = "<tr><th>AA</th>";

    //Iterate as long as we are still in the same month
    for($a=0;$a<31;$a++){
        $Command = "+".$a." days";
        $ThisDay = strtotime($Command, $FirstDayOfCalendar);
        $DataDay = $DataDays[$a];

        //Catch Month shift
        if(date("m", $ThisDay)==$month){
            $TableHeaderRowUsers .= "<th>".date("d", $ThisDay)."</th>";
            $TableHeaderRowTotal .= "<th>".$DataDay['total']."</th>";
            $TableHeaderRowOA .= "<th>".$DataDay['OA']."</th>";
            $TableHeaderRowFA .= "<th>".$DataDay['FA']."</th>";
            $TableHeaderRowAA .= "<th>".$DataDay['AA']."</th>";
        }

    }

    $TableHeaderRowUsers .= "</tr>";
    $TableHeaderRowTotal .= "</tr>";
    $TableHeaderRowOA .= "</tr>";
    $TableHeaderRowFA .= "</tr>";
    $TableHeaderRowAA .= "</tr>";

    //Build table head rows as wished
    $TableHeader .= $TableHeaderRowUsers;
    $TableHeader .= $TableHeaderRowTotal;
    $TableHeader .= $TableHeaderRowOA;
    $TableHeader .= $TableHeaderRowFA;
    $TableHeader .= $TableHeaderRowAA;
    $TableHeader .= "</thead>";

    // Build table body
    $TableBody = '<tbody class="table-group-divider">'.$TableRows.'</tbody>';

    // Build that calendar
    $Table = "<table class='table table-bordered table-sm table-condensed'>";
    $Table .= $TableHeader;
    $Table .= $TableBody;
    $Table .= "</table>";

    return $Table;

}

function populate_day_urlaubsplan_tabelle_management($Day,$UserID,$AllAbwesenheiten,$RollenUser,$Total,$OA=0,$FA=0,$AA=0){

    $ReturnVals=[];
    $Abwesenheitstypen = explode(',',ABWESENHEITENTYPEN);
    $Answer = "<td></td>";

    //Colorize stuff in case field is empty based on weekend/holidays
    if((date('w',$Day)==0)OR(date('w',$Day)==6)){
        $Answer = "<td class='table-secondary'></td>";
    } elseif (in_array(date('Y-m-d',$Day), explode(',',LISTEFEIERTAGE))){
        $Answer = "<td class='table-secondary'></td>";
    }

    //Loop through all Abwesenheiten
    foreach ($AllAbwesenheiten as $Abwesenheit){

        // Only check Abwesenheiten that count for User
        if($Abwesenheit['user']==$UserID){

            //Check if Abwesenheit is active on this day
            if(($Day>=strtotime($Abwesenheit['begin']))&&($Day<=strtotime($Abwesenheit['end']))){

                $Kuerzel = "U";
                $Farbe = "table-primary";

                // Fetch type
                foreach ($Abwesenheitstypen as $Abwesenheitstyp) {
                    $DetailsAbwesenheitstyp = explode(':', $Abwesenheitstyp);
                    if($Abwesenheit['type']==$DetailsAbwesenheitstyp[0]){
                        $Kuerzel = $DetailsAbwesenheitstyp[1];
                        if($DetailsAbwesenheitstyp[2]!=''){
                            $Farbe = $DetailsAbwesenheitstyp[2];
                        }
                    }
                }

                // Generate Tooltip if Antrag has a comment
                if($Abwesenheit['create_comment']!=''){
                    $Content = '<a href="#" data-bs-toggle="tooltip" data-bs-html="true" title="'.$Abwesenheit['create_comment'].'">'.$Kuerzel.'</a>';
                } else {
                    $Content = $Kuerzel;
                }

                if($Abwesenheit['status_bearbeitung']=="Beantragt"){
                    $Answer = "<td class='text-center table-warning'>".$Content."*</td>";
                } elseif ($Abwesenheit['status_bearbeitung']=="Genehmigt"){

                    $Answer = "<td class='text-center ".$Farbe."'>".$Content."</td>";

                    //Sum up statistics
                    $Total++;
                    if($RollenUser=='OA'){
                        $OA++;
                    }
                    if($RollenUser=='FA'){
                        $FA++;
                    }
                    if($RollenUser=='AA'){
                        $AA++;
                    }
                }
            }
        }
    }

    $ReturnVals['HTML']=$Answer;
    $ReturnVals['total']=$Total;
    $ReturnVals['OA']=$OA;
    $ReturnVals['FA']=$FA;
    $ReturnVals['AA']=$AA;

    return $ReturnVals;

}