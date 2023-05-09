<?php

function urlaubsplan_funktionsbuttons($Month,$Year, $UE=1){

    $FORMhtml = '<div class="row">';
    $FORMhtml .= form_hidden_input_generator('org_ue', $UE);
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
    $CurrentUserInfos = get_current_user_infos($mysqli, $CurrentUser);
    $SearchDate = $year."-".$month."-01";
    $LastDayOfConcideredMonth = date('Y-m-t', strtotime($SearchDate));
    $AllUsers = get_sorted_list_of_all_users($mysqli, 'abteilungsrollen DESC, nachname ASC', false, $LastDayOfConcideredMonth);
    $AllAbwesenheiten = get_sorted_list_of_all_abwesenheiten($mysqli);
    $AllAssignments = get_all_user_depmnt_assignments($mysqli);
    $AllDepartmentEvents = get_sorted_list_of_all_department_events($mysqli);
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
                    $UEviewingUser = get_user_assigned_department_at_date(NULL, $CurrentUserInfos, $ThisDay,$AllAssignments);
                    $ReturnValues = populate_day_urlaubsplan_tabelle_user($ThisDay,$User,$AllAbwesenheiten, $User['abteilungsrollen'],$UEviewingUser, $AllAssignments, $ThisDayData['total'], $ThisDayData['OA'], $ThisDayData['FA'], $ThisDayData['AA']);
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
    $TableHeaderRowEvents = "<tr><th>Veranstaltungen in Abteilung</th>";
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
            $UEviewingUser = get_user_assigned_department_at_date(NULL, $CurrentUserInfos, $ThisDay,$AllAssignments);

            # Start coloring the header
            $ColoringAbteilungsuebersicht = 'class="table-success"';
            if($UEviewingUser==1){
                if ($DataDay['total']>SHOWYELLOWWISHESURLAUB){
                    $ColoringAbteilungsuebersicht = 'class="table-warning"';
                }

                if($DataDay['total']>SHOWREDWISHESURLAUB) {
                    $ColoringAbteilungsuebersicht = 'class="table-danger"';
                }
            } else {

                if($CurrentUserInfos['abteilungsrollen']=="OA"){

                    if ($DataDay['OA']>SHOWYELLOWWISHESURLAUBIPSOA){
                        $ColoringAbteilungsuebersicht = 'class="table-warning"';
                    }

                    if($DataDay['OA']>SHOWREDWISHESURLAUBIPSOA) {
                        $ColoringAbteilungsuebersicht = 'class="table-danger"';
                    }
                } else {

                    $FAAACountToday = $DataDay['FA'] + $DataDay['AA'];
                    if ($FAAACountToday>SHOWYELLOWWISHESURLAUBIPS){
                        $ColoringAbteilungsuebersicht = 'class="table-warning"';
                    }

                    if($FAAACountToday>SHOWREDWISHESURLAUBIPS) {
                        $ColoringAbteilungsuebersicht = 'class="table-danger"';
                    }
                }

            }
            if($UEviewingUser==1){
                $TableHeaderRowTotal .= "<th ".$ColoringAbteilungsuebersicht.">".$DataDay['total']."</th>";
            } else {
                if($CurrentUserInfos['abteilungsrollen']=="OA"){
                    $TableHeaderRowTotal .= "<th ".$ColoringAbteilungsuebersicht.">".$DataDay['OA']."</th>";
                } else {
                    $FAAACountToday = $DataDay['FA'] + $DataDay['AA'];
                    $TableHeaderRowTotal .= "<th ".$ColoringAbteilungsuebersicht.">".$FAAACountToday."</th>";
                }
            }
            #$TableHeaderRowOA .= "<th>".$DataDay['OA']."</th>";
            #$TableHeaderRowFA .= "<th>".$DataDay['FA']."</th>";
            #$TableHeaderRowAA .= "<th>".$DataDay['AA']."</th>";

            # Start calculating Department Events Row
            $TableHeaderRowEvents .= calculate_department_events_table_cell($ThisDay, $AllDepartmentEvents);
        }

    }

    #$TableHeaderRowUsers .= "<th rowspan='2' class='rotate'>Ges.<br>dieses<br>Jahr</th></tr>";
    $TableHeaderRowUsers .= "</tr>";
    $TableHeaderRowEvents .= "</tr>";
    $TableHeaderRowTotal .= "</tr>";
    #$TableHeaderRowOA .= "</tr>";
    #$TableHeaderRowFA .= "</tr>";
    #$TableHeaderRowAA .= "</tr>";

    //Build table head rows as wished
    $TableHeader .= $TableHeaderRowUsers;
    $TableHeader .= $TableHeaderRowEvents;
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

function urlaubsplan_tabelle_management($month, $year, $UE=1){

    $HTML = '';
    $mysqli = connect_db();
    $SearchDate = $year."-".$month."-01";
    $LastDayOfConcideredMonth = date('Y-m-t', strtotime($SearchDate));
    $AllUsers = get_sorted_list_of_all_users($mysqli, 'abteilungsrollen DESC, nachname ASC', false, $LastDayOfConcideredMonth);
    $AllAbwesenheiten = get_sorted_list_of_all_abwesenheiten($mysqli);
    $AllAssignments = get_all_user_depmnt_assignments($mysqli);
    $AllDepartmentEvents = get_sorted_list_of_all_department_events($mysqli);
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

        $AssignmentCounter = 0;

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
                    $ReturnValues = populate_day_urlaubsplan_tabelle_management($ThisDay,$User,$AllAbwesenheiten,$User['abteilungsrollen'], $AllAssignments, $UE, $ThisDayData['total'], $ThisDayData['OA'], $ThisDayData['FA'], $ThisDayData['AA']);
                    $TableRowContent .= $ReturnValues['HTML'];
                    $NewDayStatData['total']=$ReturnValues['total'];
                    $NewDayStatData['OA']=$ReturnValues['OA'];
                    $NewDayStatData['FA']=$ReturnValues['FA'];
                    $NewDayStatData['AA']=$ReturnValues['AA'];
                    $DataDays[$a] = $NewDayStatData;
                    if($ReturnValues['assignment_today']){
                        $AssignmentCounter++;
                    }
                }

            }

            $TableRowContent .= "<td>".calculate_total_approved_holiday_days_for_user_in_selected_year($AllAbwesenheiten, $User, $year)."</td>";

            $TableRowContent .= "</tr>";

            if($AssignmentCounter>0){
                $TableRows .= $TableRowContent;
            }
        }
    }

    // Build Table header -> this means loading date information
    $TableHeader = "<thead>";

    $TableHeaderRowUsers = "<tr><th></th>";
    $TableHeaderRowEvents = "<tr><th>Veranstaltungen</th>";
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
            if($UE==1){
                $TableHeaderRowEvents .= calculate_department_events_table_cell($ThisDay, $AllDepartmentEvents);
                $TableHeaderRowOA .= "<th>".$DataDay['OA']."</th>";
                $TableHeaderRowFA .= "<th>".$DataDay['FA']."</th>";
                $TableHeaderRowAA .= "<th>".$DataDay['AA']."</th>";
            } else {
                if($DataDay['OA']<SHOWYELLOWWISHESURLAUBIPSOA){
                    $TableHeaderRowOA .= "<th class='table-success'>".$DataDay['OA']."</th>";
                } elseif (($DataDay['OA']>=SHOWYELLOWWISHESURLAUBIPSOA)&&($DataDay['OA']<SHOWREDWISHESURLAUBIPSOA)) {
                    $TableHeaderRowOA .= "<th class='table-warning'>".$DataDay['OA']."</th>";
                } elseif ($DataDay['OA']>=SHOWREDWISHESURLAUBIPSOA){
                    $TableHeaderRowOA .= "<th class='table-danger'>".$DataDay['OA']."</th>";
                }

                if($DataDay['FA']<SHOWYELLOWWISHESURLAUBIPSFA){
                    $TableHeaderRowFA .= "<th class='table-success'>".$DataDay['FA']."</th>";
                } elseif (($DataDay['FA']>=SHOWYELLOWWISHESURLAUBIPSFA)&&($DataDay['FA']<SHOWREDWISHESURLAUBIPSFA)) {
                    $TableHeaderRowFA .= "<th class='table-warning'>".$DataDay['FA']."</th>";
                } elseif ($DataDay['FA']>=SHOWREDWISHESURLAUBIPSFA){
                    $TableHeaderRowFA .= "<th class='table-danger'>".$DataDay['FA']."</th>";
                }

                if($DataDay['AA']<SHOWYELLOWWISHESURLAUBIPSAA){
                    $TableHeaderRowAA .= "<th class='table-success'>".$DataDay['AA']."</th>";
                } elseif (($DataDay['AA']>=SHOWYELLOWWISHESURLAUBIPSAA)&&($DataDay['AA']<SHOWREDWISHESURLAUBIPSAA)) {
                    $TableHeaderRowAA .= "<th class='table-warning'>".$DataDay['AA']."</th>";
                } elseif ($DataDay['AA']>=SHOWREDWISHESURLAUBIPSAA){
                    $TableHeaderRowAA .= "<th class='table-danger'>".$DataDay['AA']."</th>";
                }
            }
        }

    }

    $TableHeaderRowUsers .= "</tr>";
    $TableHeaderRowEvents .= "</tr>";
    $TableHeaderRowTotal .= "</tr>";
    $TableHeaderRowOA .= "</tr>";
    $TableHeaderRowFA .= "</tr>";
    $TableHeaderRowAA .= "</tr>";

    //Build table head rows as wished
    $TableHeader .= $TableHeaderRowUsers;
    if($UE==1){
    $TableHeader .= $TableHeaderRowEvents;
    }
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

function populate_day_urlaubsplan_tabelle_management($Day,$User,$AllAbwesenheiten,$RollenUser,$AllUserAssignments,$UE,$Total,$OA=0,$FA=0,$AA=0){

    $ReturnVals=[];
    $Abwesenheitstypen = explode(',',ABWESENHEITENTYPEN);
    $AssignmentToday = get_user_assigned_department_at_date(NULL, $User, $Day, $AllUserAssignments);
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
        if($Abwesenheit['user']==$User['id']){

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
                    $Content = '<a href="#" data-bs-toggle="tooltip" data-bs-html="true" title="'.htmlspecialchars($Abwesenheit['create_comment']).'">'.$Kuerzel.'</a>';
                } else {
                    $Content = $Kuerzel;
                }

                if($Abwesenheit['status_bearbeitung']=="Beantragt"){
                    $Answer = "<td class='text-center table-warning'>".$Content."*</td>";
                } elseif ($Abwesenheit['status_bearbeitung']=="Genehmigt"){

                    $Answer = "<td class='text-center ".$Farbe."'>".$Content."</td>";

                    //Sum up statistics
                    if($AssignmentToday==$UE) {
                        $Total++;
                        if ($RollenUser == 'OA') {
                            $OA++;
                        }
                        if ($RollenUser == 'FA') {
                            $FA++;
                        }
                        if ($RollenUser == 'AA') {
                            $AA++;
                        }
                    }
                }
            }
        }
    }

    // Check if user is actually in the chosen UE at selected day - this is used for deciding if user is to be displayed in other function
    if($UE==$AssignmentToday){
        $ReturnVals['assignment_today']=true;
    } else {
        $ReturnVals['assignment_today']=false;
    }

    $ReturnVals['HTML']=$Answer;
    $ReturnVals['total']=$Total;
    $ReturnVals['OA']=$OA;
    $ReturnVals['FA']=$FA;
    $ReturnVals['AA']=$AA;

    return $ReturnVals;

}

function populate_day_urlaubsplan_tabelle_user($Day,$UserID,$AllAbwesenheiten,$RollenUser,$UEviewingUser,$AllAssignments,$Total,$OA=0,$FA=0,$AA=0){

    $ReturnVals=[];
    $AssignedUEthisUserToday = get_user_assigned_department_at_date(NULL,$UserID,$Day,$AllAssignments);

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
        if($Abwesenheit['user']==$UserID['id']){

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
                    $Content = '<a href="#" data-bs-toggle="tooltip" data-bs-html="true" title="'.htmlspecialchars($Abwesenheit['create_comment']).'">'.$Kuerzel.'</a>';
                } else {
                    $Content = $Kuerzel;
                }

                if($Abwesenheit['status_bearbeitung']=="Beantragt"){
                    $Answer = "<td class='text-center table-warning'>".$Content."*</td>";
                } elseif ($Abwesenheit['status_bearbeitung']=="Genehmigt"){

                    $Answer = "<td class='text-center ".$Farbe."'>".$Content."</td>";

                    //Sum up statistics
                    if($AssignedUEthisUserToday==$UEviewingUser){
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
    }

    $ReturnVals['HTML']=$Answer;
    $ReturnVals['total']=$Total;
    $ReturnVals['OA']=$OA;
    $ReturnVals['FA']=$FA;
    $ReturnVals['AA']=$AA;

    return $ReturnVals;

}