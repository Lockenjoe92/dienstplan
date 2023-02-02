<?php

function table_wunschdienstplan_user($mysqli,$Nutzerrollen){

    // deal with stupid "" and '' problems
    $bla = '"{"key": "value"}"';
    $Wuensche = get_sorted_list_of_all_dienstplanwünsche($mysqli);
    $Wunschtypen = get_list_of_all_dienstplanwunsch_types($mysqli);
    $CurrentUser = get_current_user_id();

    // Setup Toolbar
    $HTML = '<div id="toolbar">
                <a id="add_user" class="btn btn-primary" href="dienstplan_user.php?mode=add_dienstwunsch">
                <i class="bi bi-person-fill-add"></i> Hinzufügen</a>
            </div>';

    // Initialize Table
    $HTML .= '<table data-toggle="table" 
data-search="true" 
data-locale="de-DE"
data-toolbar="#toolbar" 
data-show-columns="true" 
data-search-highlight="true" 
data-show-multi-sort="true"
  data-multiple-select-row="true"
  data-click-to-select="true"
  data-pagination="true">';

    // Setup Table Head
    $HTML .= '<thead>
                <tr class="tr-class-1">
                    <th data-field="day" data-sortable="true">Tag</th>
                    <th data-field="type" data-sortable="true">Wunsch</th>
                    <th data-field="comment" data-sortable="true">Kommentar</th>
                    <th data-field="eintrag-datum" data-sortable="true">Beantragt am</th>
                    <th>Optionen</th>
                </tr>
              </thead>';

    // Fill table body
    $HTML .= '<tbody>';
    $counter = 1;
    foreach ($Wuensche as $Wunsch){

        if($Wunsch['user'] == $CurrentUser){

            // Build rows
            if($counter==1){
                $HTML .= '<tr id="tr-id-1" class="tr-class-1" data-title="bootstrap table" data-object='.$bla.'>';
            } else {
                $HTML .= '<tr id="tr-id-'.$counter.'" class="tr-class-'.$counter.'">';
            }

            // Check user assignment at time of antrag
            $UE = get_user_assigned_department_at_date($mysqli, $CurrentUser, $Wunsch['date']);

            // Build edit/delete Buttons
            if(user_can_edit_dienstwunsch($mysqli, $Nutzerrollen, $Wunsch, $UE)){
                $Options = '<a href="dienstplan_user.php?mode=edit_dienstwunsch&dienstwunsch_id='.$Wunsch['id'].'"><i class="bi bi-pencil-fill"></i></a> <a href="dienstplan_user.php?mode=delete_dienstwunsch&dienstwunsch_id='.$Wunsch['id'].'"><i class="bi bi-trash3-fill"></i></a> ';
            }else{
                $Options = '';
            }

            $WunschType = get_wunschtype_details_by_type_id($Wunschtypen, $Wunsch['type']);

            if($counter==1){
                $HTML .= '<td id="td-id-1" class="td-class-1" data-title="bootstrap table">'.$Wunsch['date'].'</td>';
                $HTML .= '<td>'.$WunschType['name'].'</td>';
                $HTML .= '<td>'.$Wunsch['create_comment'].'</td>';
                $HTML .= '<td>'.date('Y-m-d',strtotime($Wunsch['create_time'])).'</td>';
                $HTML .= '<td> '.$Options.'</td>';
            } else {
                $HTML .= '<td id="td-id-'.$counter.'" class="td-class-'.$counter.'"">'.$Wunsch['date'].'</td>';
                $HTML .= '<td>'.$WunschType['name'].'</td>';
                $HTML .= '<td>'.$Wunsch['create_comment'].'</td>';
                $HTML .= '<td>'.date('Y-m-d',strtotime($Wunsch['create_time'])).'</td>';
                $HTML .= '<td> '.$Options.'</td>';
            }

            // close row and count up
            $HTML .= "</tr>";
            $counter++;
        }

    }
    $HTML .= '</tbody>';
    $HTML .= '</table>';

    return $HTML;

}

function table_wunschdienstplan_management($mysqli,$Nutzerrollen, $Month, $Year){

    // deal with stupid "" and '' problems
    $bla = '"{"key": "value"}"';
    $Wuensche = get_sorted_list_of_all_dienstplanwünsche($mysqli);
    $Wunschtypen = get_list_of_all_dienstplanwunsch_types($mysqli);
    $Users = get_sorted_list_of_all_users($mysqli);

    if(isset($_POST['org_ue'])){
        $UE = $_POST['org_ue'];
    } else {
        $UE = $_GET['org_ue'];
    }

    // Setup Toolbar
    $HTML = '<div id="toolbar">
                <a id="add_user" class="btn btn-primary" href="dienstwuensche_management.php?org_ue='.$UE.'&mode=add_dienstwunsch">
                <i class="bi bi-person-fill-add"></i> Hinzufügen</a>
            </div>';

    // Initialize Table
    $HTML .= '<table data-toggle="table" 
data-search="true" 
data-locale="de-DE"
data-toolbar="#toolbar" 
data-show-columns="true" 
data-search-highlight="true" 
data-show-multi-sort="true"
  data-multiple-select-row="true"
  data-click-to-select="true"
  data-pagination="true">';

    // Setup Table Head
    $HTML .= '<thead>
                <tr class="tr-class-1">
                    <th data-field="day" data-sortable="true">Tag</th>
                    <th data-field="user" data-sortable="true">MitarbeiterIn</th>
                    <th data-field="type" data-sortable="true">Wunsch</th>
                    <th data-field="comment" data-sortable="true">Kommentar</th>
                    <th data-field="eintrag-datum" data-sortable="true">Beantragt am</th>
                    <th>Optionen</th>
                </tr>
              </thead>';

    // Fill table body
    $HTML .= '<tbody>';
    $counter = 1;
    foreach ($Wuensche as $Wunsch){

        if(check_if_dienstwunsch_is_in_selected_month($Wunsch, $Month, $Year)){

            $User = [];
            foreach ($Users as $user){
                if($user['id']==$Wunsch['user']){
                    $User = $user;
                }
            }

            // Build rows
            if($counter==1){
                $HTML .= '<tr id="tr-id-1" class="tr-class-1" data-title="bootstrap table" data-object='.$bla.'>';
            } else {
                $HTML .= '<tr id="tr-id-'.$counter.'" class="tr-class-'.$counter.'">';
            }

            // Build edit/delete Buttons
            if(user_can_edit_dienstwunsch($mysqli, $Nutzerrollen, $Wunsch)){
                $Options = '<a href="dienstwuensche_management.php?org_ue='.$UE.'&mode=edit_dienstwunsch&dienstwunsch_id='.$Wunsch['id'].'"><i class="bi bi-pencil-fill"></i></a> <a href="dienstwuensche_management.php?org_ue='.$UE.'&mode=delete_dienstwunsch&dienstwunsch_id='.$Wunsch['id'].'"><i class="bi bi-trash3-fill"></i></a> ';
            }else{
                $Options = '';
            }

            $WunschType = get_wunschtype_details_by_type_id($Wunschtypen, $Wunsch['type']);

            if($counter==1){
                $HTML .= '<td id="td-id-1" class="td-class-1" data-title="bootstrap table">'.$Wunsch['date'].'</td>';
                $HTML .= '<td>'.$User['nachname'].', '.$User['vorname'].'</td>';
                $HTML .= '<td>'.$WunschType['name'].'</td>';
                $HTML .= '<td>'.$Wunsch['create_comment'].'</td>';
                $HTML .= '<td>'.date('Y-m-d',strtotime($Wunsch['create_time'])).'</td>';
                $HTML .= '<td> '.$Options.'</td>';
            } else {
                $HTML .= '<td id="td-id-'.$counter.'" class="td-class-'.$counter.'"">'.$Wunsch['date'].'</td>';
                $HTML .= '<td>'.$User['nachname'].', '.$User['vorname'].'</td>';
                $HTML .= '<td>'.$WunschType['name'].'</td>';
                $HTML .= '<td>'.$Wunsch['create_comment'].'</td>';
                $HTML .= '<td>'.date('Y-m-d',strtotime($Wunsch['create_time'])).'</td>';
                $HTML .= '<td> '.$Options.'</td>';
            }

            // close row and count up
            $HTML .= "</tr>";
            $counter++;
        }

    }
    $HTML .= '</tbody>';
    $HTML .= '</table>';

    return $HTML;

}

function wunschdienstplan_funktionsbuttons_user($Year){

    $FORMhtml = '<div class="row">';
    $FORMhtml .= "<div class='col'>".form_dropdown_years('year', $Year)."</div>";
    $FORMhtml .= "<div class='col'>".form_group_continue_return_buttons(true, 'Reset', 'reset_calendar', 'btn-primary', true, 'Zeitraum wählen', 'action_change_date', 'btn-primary')."</div>";
    $FORMhtml .= "</div>";

    $HTML = container_builder(form_builder($FORMhtml, 'self', 'POST'));

    return $HTML;

}

function wunschdienstplan_funktionsbuttons_management($Month,$Year){

    if(isset($_POST['org_ue'])){
        $UE = $_POST['org_ue'];
    } else {
        $UE = $_GET['org_ue'];
    }

    $FORMhtml = '<div class="row">';
    $FORMhtml .= "<div class='col'>".form_dropdown_months('month',$Month)."</div>";
    $FORMhtml .= "<div class='col'>".form_dropdown_years('year', $Year)."</div>";
    $FORMhtml .= "<div class='col'>".form_group_continue_return_buttons(true, 'Reset', 'reset_calendar', 'btn-primary', true, 'Zeitraum wählen', 'action_change_date', 'btn-primary')."</div>";
    $FORMhtml .= form_hidden_input_generator('org_ue', $UE);
    $FORMhtml .= "</div>";

    $HTML = container_builder(form_builder($FORMhtml, 'self', 'POST'));

    return $HTML;

}

function wunschdienstplan_uebersicht_kalender_user($Year){

    return "<h3>Hier entsteht eine Jahreskalender-Ansicht der DP-Wünsche der User.</h3>";

}

function wunschdienstplan_uebersicht_kalender_management($Month, $Year){

    $HTML = '';
    $mysqli = connect_db();
    $AllUsers = get_sorted_list_of_all_users($mysqli, 'abteilungsrollen DESC, nachname ASC');
    $AllAbwesenheiten = get_sorted_list_of_all_abwesenheiten($mysqli);
    $AllWishes = get_sorted_list_of_all_dienstplanwünsche($mysqli, true);
    $AllWishTypes = get_list_of_all_dienstplanwunsch_types($mysqli);
    $FirstDayOfCalendarString = "01-".$Month."-".$Year;
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
                if(date("m", $ThisDay)==$Month){
                    $ReturnValues = populate_day_wuup_tabelle_management($ThisDay,$User['id'],$AllAbwesenheiten,$AllWishes,$AllWishTypes,$User['abteilungsrollen'], $ThisDayData['total'], $ThisDayData['OA'], $ThisDayData['FA'], $ThisDayData['AA']);
                    $TableRowContent .= $ReturnValues['HTML'];
                    $NewDayStatData['total']=$ReturnValues['total'];
                    $NewDayStatData['OA']=$ReturnValues['OA'];
                    $NewDayStatData['FA']=$ReturnValues['FA'];
                    $NewDayStatData['AA']=$ReturnValues['AA'];
                    $DataDays[$a] = $NewDayStatData;
                }

            }

            $TableRowContent .= "<td>".calculate_total_approved_holiday_days_for_user_in_selected_year($AllAbwesenheiten, $User, $Year)."</td>";

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

        // Catch weekends or holidays, as this could mean different view rules
        if(day_is_a_weekend_or_holiday($ThisDay)){
            //Catch Month shift
            if(date("m", $ThisDay)==$Month){
                $TableHeaderRowUsers .= "<th colspan='2'>".date("d", $ThisDay)."</th>";
                $TableHeaderRowTotal .= "<th colspan='2'>".$DataDay['total']."</th>";
                $TableHeaderRowOA .= "<th colspan='2'>".$DataDay['OA']."</th>";
                $TableHeaderRowFA .= "<th colspan='2'>".$DataDay['FA']."</th>";
                $TableHeaderRowAA .= "<th colspan='2'>".$DataDay['AA']."</th>";
            }
        } else {
            //Catch Month shift
            if(date("m", $ThisDay)==$Month){
                $TableHeaderRowUsers .= "<th>".date("d", $ThisDay)."</th>";
                $TableHeaderRowTotal .= "<th>".$DataDay['total']."</th>";
                $TableHeaderRowOA .= "<th>".$DataDay['OA']."</th>";
                $TableHeaderRowFA .= "<th>".$DataDay['FA']."</th>";
                $TableHeaderRowAA .= "<th>".$DataDay['AA']."</th>";
            }
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

function add_dienstwunsch_user($mysqli){

    // Initialize Placeholder & Error Variables
    $FormHTML = "";
    $OutputMode = "show_form";
    $DAUcheck = 0;
    $allDepartments = get_list_of_all_departments($mysqli);
    $allWishTypes = get_list_of_all_dienstplanwunsch_types($mysqli);
    $userIDPlaceholder = get_current_user_id();

    //Initialize date input at earliest possible date according to current assignment
    $UE = get_user_assigned_department_at_date($mysqli, $userIDPlaceholder, date('Y-m-d'));
    $Department = get_department_infos($mysqli, $UE);
    $DatePlaceholder = date('Y-m-d', strtotime('+1 day', strtotime(get_last_date_for_dienstwunsch_submission($Department['accept_user_dienst_wishes_until_months']))));
    $ReturnMessage = $entryDatePlaceholder = $typePlaceholder = $commentPlaceholder = "";
    $DateErr = $TypeErr = "";

    // Do stuff
    if(isset($_POST['add_dienstwunsch_action'])){

        $AllWuensche = get_sorted_list_of_all_dienstplanwünsche($mysqli);
        $allHolidays = get_sorted_list_of_all_abwesenheiten($mysqli);

        // Load Form content
        $DatePlaceholder = trim($_POST['date']);
        $typePlaceholder = trim($_POST['type']);
        $commentPlaceholder = trim($_POST['comment_user']);

        // Do some DAU-Checks here
        // Check fucked up date entries
        if($DatePlaceholder<date('Y-m-d')){
            $DAUcheck++;
            $DateErr .= "Das Anfangsdatum darf nicht in der Vergangenheit liegen!";
        }

        //Check overlaps!
        $Check = check_dienstwunsch_date_overlap_user($userIDPlaceholder, $AllWuensche, $DatePlaceholder, $typePlaceholder);
        if($Check['bool']){
            $DAUcheck++;
            $DateErr .= "Der eingegebene Antrag kollidiert mit anderen bereits erfassten Dienstplanwünschen!";
        }

        $CheckHoliday = check_abwesenheit_date_overlap_user($userIDPlaceholder,$allHolidays,$DatePlaceholder,$DatePlaceholder);
        if($CheckHoliday['bool']){
            $DAUcheck++;
            $DateErr .= "Der eingegebene Antrag kollidiert mit einem anderen bereits erfassten Abwesenheits-/Urlaubsantrag!";
        }

        //Check if Diensttype fits to planned user org_einheit at chosen date
        $UserDepartmentAssignmentAtDate = get_user_assigned_department_at_date($mysqli, $userIDPlaceholder, $DatePlaceholder);
        foreach ($allDepartments as $department){
            if($department['id']==$UserDepartmentAssignmentAtDate){
                $DepartmentName = $department['name'];
                $DepartmentMaxWishes = $department['max_wishes_per_month'];
                $DepartmentLastWishMonths = $department['accept_user_dienst_wishes_until_months'];
            }
        }

        foreach ($allWishTypes as $wishType){
            if($wishType['id']==$typePlaceholder){
                if($wishType['belongs_to_depmnt']!=$UserDepartmentAssignmentAtDate){
                    $DAUcheck++;
                    $TypeErr .= "Der gewählte Dienstplanwunsch-Typ ist am angegebenen Tag nicht wählbar, da Sie dort in der Organisationseinheit ".$DepartmentName." geplant sind.";
                }
            }
        }

        // Max wishes
        $wishesThisMonth = get_num_wishes_user_in_selected_month($userIDPlaceholder,$DatePlaceholder,$AllWuensche);
        if($wishesThisMonth>=$DepartmentMaxWishes){
            $DAUcheck++;
            $DateErr .= "Die maximale Zahl an Dienstwünschen im ausgewählten Monat ist bereits erschöpft!";
        }

        // Don't permit users to submit wishes x Months in advance
        $DateLastPossibleEntry = get_last_date_for_dienstwunsch_submission($DepartmentLastWishMonths);
        if($DateLastPossibleEntry>$DatePlaceholder){
            $DAUcheck++;
            // Make Pretty Month Name
            $GetTime = strtotime($DatePlaceholder);
            $format = new IntlDateFormatter('de_DE', IntlDateFormatter::NONE,
                IntlDateFormatter::NONE, NULL, NULL, "MMM");
            $monthName = datefmt_format($format, mktime(0, 0, 0, date("m", $GetTime)));
            $DateErr .= "Die Dienst- und Urlaubswunscheingabe für den ".$monthName." ".date("Y", $GetTime)." ist bereits geschlossen!";
        }

        if($DAUcheck==0){

            $Return = dienstwunsch_anlegen($mysqli, $userIDPlaceholder, $DatePlaceholder, $typePlaceholder, $entryDatePlaceholder, $commentPlaceholder);
            if($Return['success']){
                $OutputMode="show_return_card";
                $ReturnMessage = "Dienstwunsch erfolgreich angelegt!";
            } else {
                $OutputMode="show_return_card";
                $ReturnMessage = $Return['err'];
            }
        }
    }

    if($OutputMode=="show_form"){
        //Build Form
        $FormHTML .= form_group_input_date('Datum', 'date', $DatePlaceholder, true, $DateErr, false);
        $FormHTML .= form_group_dropdown_dienstwunschtypen($mysqli, 'Dienstwunsch', 'type', $typePlaceholder, true, $TypeErr);
        $FormHTML .= form_group_input_text('Kommentar des/der Antragstellers/in', 'comment_user', $commentPlaceholder, false);
        $FormHTML .= "<br>";
        $FormHTML .= form_group_continue_return_buttons(true, 'Anlegen', 'add_dienstwunsch_action', 'btn-primary', true, 'Zurück', 'wunschdienst_go_back', 'btn-primary');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');
        return card_builder('Dienstwunsch anlegen','', $FORM);
    }else{
        $FormHTML = form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'wunschdienst_go_back', 'btn-primary');
        $FORM = form_builder($FormHTML, 'self', 'POST');
        return card_builder('Dienstwunsch anlegen',$ReturnMessage, $FORM);
    }
}

function add_dienstwunsch_management($mysqli){

    // Implement multi OrgUE Sites
    if(isset($_POST['org_ue'])){
        $UE = $_POST['org_ue'];
    } else {
        $UE = $_GET['org_ue'];
    }

    // Initialize Placeholder & Error Variables
    $FormHTML = "";
    $OutputMode = "show_form";
    $DAUcheck = 0;
    $allDepartments = get_list_of_all_departments($mysqli);
    $allWishTypes = get_list_of_all_dienstplanwunsch_types($mysqli);
    $DatePlaceholder = $ApplicationDatePlaceholder = date('Y-m-d');
    $ReturnMessage = $typePlaceholder = $commentPlaceholder = $userIDPlaceholder =  "";
    $DateErr = $TypeErr = $ApplicationDateErr = "";

    // Do stuff
    if(isset($_POST['add_dienstwunsch_action'])){

        $AllWuensche = get_sorted_list_of_all_dienstplanwünsche($mysqli);
        $allHolidays = get_sorted_list_of_all_abwesenheiten($mysqli);

        // Load Form content
        $userIDPlaceholder = trim($_POST['user']);
        $DatePlaceholder = trim($_POST['date']);
        $ApplicationDatePlaceholder = trim($_POST['application_date']);
        $typePlaceholder = trim($_POST['type']);
        $commentPlaceholder = trim($_POST['comment_user']);

        // Do some DAU-Checks here
        // Check fucked up date entries
        if($DatePlaceholder<date('Y-m-d')){
            $DAUcheck++;
            $DateErr .= "Das Anfangsdatum darf nicht in der Vergangenheit liegen!";
        }

        if($ApplicationDatePlaceholder>$DatePlaceholder){
            $DAUcheck++;
            $ApplicationDateErr .= "Das Antragsdatum kann nicht nach dem Anfangsdatum liegen!";
        }

        //Check overlaps!
        $Check = check_dienstwunsch_date_overlap_user($userIDPlaceholder, $AllWuensche, $DatePlaceholder, $typePlaceholder);
        if($Check['bool']){
            $DAUcheck++;
            $DateErr .= "Der eingegebene Antrag kollidiert mit anderen bereits erfassten Dienstplanwünschen!";
        }

        $CheckHoliday = check_abwesenheit_date_overlap_user($userIDPlaceholder,$allHolidays,$DatePlaceholder,$DatePlaceholder);
        if($CheckHoliday['bool']){
            $DAUcheck++;
            $DateErr .= "Der eingegebene Antrag kollidiert mit einem anderen bereits erfassten Abwesenheits-/Urlaubsantrag!";
        }

        //Check if Diensttype fits to planned user org_einheit at chosen date
        $UserDepartmentAssignmentAtDate = get_user_assigned_department_at_date($mysqli, $userIDPlaceholder, $DatePlaceholder);
        foreach ($allDepartments as $department){
            if($department['id']==$UserDepartmentAssignmentAtDate){
                $DepartmentName = $department['name'];
            }
        }

        foreach ($allWishTypes as $wishType){
            if($wishType['id']==$typePlaceholder){
                if($wishType['belongs_to_depmnt']!=$UserDepartmentAssignmentAtDate){
                    $DAUcheck++;
                    $TypeErr .= "Der gewählte Dienstplanwunsch-Typ ist am angegebenen Tag nicht wählbar, da der/die MitarbeiterIn dort in der Organisationseinheit ".$DepartmentName." geplant ist.";
                }
            }
        }

        //check if max. allowed wishes is already reached


        if($DAUcheck==0){

            $Return = dienstwunsch_anlegen($mysqli, $userIDPlaceholder, $DatePlaceholder, $typePlaceholder, $ApplicationDatePlaceholder, $commentPlaceholder);
            if($Return['success']){
                $OutputMode="show_return_card";
                $ReturnMessage = "Dienstwunsch erfolgreich angelegt!";
            } else {
                $OutputMode="show_return_card";
                $ReturnMessage = $Return['err'];
            }
        }
    }

    if($OutputMode=="show_form"){
        //Build Form
        $FormHTML .= form_hidden_input_generator('org_ue', $UE);
        $FormHTML .= form_group_input_date('Datum', 'date', $DatePlaceholder, true, $DateErr, false);
        $FormHTML .= form_group_dropdown_all_users('MitarbeiterIn', 'user', $userIDPlaceholder, true, '', false);
        $FormHTML .= form_group_dropdown_dienstwunschtypen($mysqli, 'Dienstwunsch', 'type', $typePlaceholder, true, $TypeErr);
        $FormHTML .= form_group_input_text('Kommentar des/der Antragstellers/in', 'comment_user', $commentPlaceholder, false);
        $FormHTML .= "<br>";
        $FormHTML .= form_group_input_date('Antragsdatum', 'application_date', $ApplicationDatePlaceholder, true, $ApplicationDateErr, false);
        $FormHTML .= "<br>";
        $FormHTML .= form_group_continue_return_buttons(true, 'Anlegen', 'add_dienstwunsch_action', 'btn-primary', true, 'Zurück', 'wunschdienst_go_back', 'btn-primary');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');
        return card_builder('Dienstwunsch anlegen','', $FORM);
    }else{
        $FormHTML = form_hidden_input_generator('org_ue', $UE);
        $FormHTML .= form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'wunschdienst_go_back', 'btn-primary');
        $FORM = form_builder($FormHTML, 'self', 'POST');
        return card_builder('Dienstwunsch anlegen',$ReturnMessage, $FORM);
    }
}

function delete_dienstwunsch_user($mysqli, $dienstwunsch){

    // Initialize Placeholder & Error Variables
    $FormHTML = "";
    $OutputMode = "show_form";
    $DAUcheck = 0;
    $ReturnMessage = "";
    $DatePlaceholder = $dienstwunsch['date'];
    $typePlaceholder =  $dienstwunsch['type'];
    $commentPlaceholder =  $dienstwunsch['create_comment'];
    $DateErr = "";

    // Do stuff
    if(isset($_POST['delete_dienstwunsch_action'])){

        // Do some DAU-Checks here
        if($DAUcheck==0){

            $DeleteComment = "Von MitarbeiterIn gelöscht";
            $Return = delete_dienstwunsch_db($mysqli, $dienstwunsch['id'], get_current_user_id(), $DeleteComment);
            if($Return['success']){
                $OutputMode="show_return_card";
                $ReturnMessage = "Dienstwunsch erfolgreich gelöscht!";
            } else {
                $OutputMode="show_return_card";
                $ReturnMessage = $Return['err'];
            }
        }
    }

    if($OutputMode=="show_form"){
        //Build Form
        $FormHTML .= form_hidden_input_generator('dienstwunsch_id', $dienstwunsch['id']);
        $FormHTML .= form_group_input_date('Datum', 'date', $DatePlaceholder, true, $DateErr, true);
        $FormHTML .= form_group_dropdown_dienstwunschtypen($mysqli, 'Dienstwunsch', 'type', $typePlaceholder, true, '', true);
        $FormHTML .= form_group_input_text('Kommentar des/der Antragstellers/in', 'comment_user', $commentPlaceholder, false, '', true);
        $FormHTML .= "<br>";
        $FormHTML .= form_group_continue_return_buttons(true, 'Löschen', 'delete_dienstwunsch_action', 'btn-danger', true, 'Zurück', 'wunschdienst_go_back', 'btn-primary');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');
        return card_builder('Dienstwunsch löschen','Möchten Sie diesen Dienstwunsch wirklich löschen?', $FORM);
    }else{
        $FormHTML = form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'wunschdienst_go_back', 'btn-primary');
        $FORM = form_builder($FormHTML, 'self', 'POST');
        return card_builder('Dienstwunsch löschen',$ReturnMessage, $FORM);
    }

}

function delete_dienstwunsch_management($mysqli, $dienstwunsch){

    // Initialize Placeholder & Error Variables
    $FormHTML = "";
    $OutputMode = "show_form";
    $DAUcheck = 0;
    $ReturnMessage = $DeleteComment = "";
    $UserPlaceholder = $dienstwunsch['user'];
    $DatePlaceholder = $dienstwunsch['date'];
    $typePlaceholder =  $dienstwunsch['type'];
    $commentPlaceholder =  $dienstwunsch['create_comment'];
    $DateErr = $DeleteCommentErr = "";

    // Implement multi OrgUE Sites
    if(isset($_POST['org_ue'])){
        $UE = $_POST['org_ue'];
    } else {
        $UE = $_GET['org_ue'];
    }

    // Do stuff
    if(isset($_POST['delete_dienstwunsch_action'])){

        $DeleteComment = trim($_POST['delete_comment']);

        if(empty($DeleteComment)){
            $DAUcheck++;
            $DeleteCommentErr = "Bitte geben Sie einen Kommentar zur Löschung des Dienstwunsches ab.";
        }

        // Do some DAU-Checks here
        if($DAUcheck==0){

            $Return = delete_dienstwunsch_db($mysqli, $dienstwunsch['id'], get_current_user_id(), $DeleteComment);
            if($Return['success']){
                $OutputMode="show_return_card";
                $ReturnMessage = "Dienstwunsch erfolgreich gelöscht!";
            } else {
                $OutputMode="show_return_card";
                $ReturnMessage = $Return['err'];
            }
        }
    }

    if($OutputMode=="show_form"){
        //Build Form
        $FormHTML .= form_hidden_input_generator('dienstwunsch_id', $dienstwunsch['id']);
        $FormHTML .= form_hidden_input_generator('org_ue', $UE);
        $FormHTML .= form_group_input_date('Datum', 'date', $DatePlaceholder, true, $DateErr, true);
        $FormHTML .= form_group_dropdown_all_users('MitarbeiterIn', 'user', $UserPlaceholder, true, '', true);
        $FormHTML .= form_group_dropdown_dienstwunschtypen($mysqli, 'Dienstwunsch', 'type', $typePlaceholder, true, '', true);
        $FormHTML .= form_group_input_text('Kommentar des/der Antragstellers/in', 'comment_user', $commentPlaceholder, false, '', true);
        $FormHTML .= "<br>";
        $FormHTML .= form_group_input_text('Kommentar zum Löschvorgang', 'delete_comment', $DeleteComment, true, $DeleteCommentErr, false);
        $FormHTML .= "<br>";
        $FormHTML .= form_group_continue_return_buttons(true, 'Löschen', 'delete_dienstwunsch_action', 'btn-danger', true, 'Zurück', 'wunschdienst_go_back', 'btn-primary');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');
        return card_builder('Dienstwunsch löschen','Möchten Sie diesen Dienstwunsch wirklich löschen?', $FORM);
    }else{
        $FormHTML = form_hidden_input_generator('org_ue', $UE);
        $FormHTML .= form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'wunschdienst_go_back', 'btn-primary');
        $FORM = form_builder($FormHTML, 'self', 'POST');
        return card_builder('Dienstwunsch löschen',$ReturnMessage, $FORM);
    }

}

function edit_dienstwunsch_user($mysqli, $dienstwunsch){

    // Initialize Placeholder & Error Variables
    $FormHTML = "";
    $OutputMode = "show_form";
    $DAUcheck = 0;
    $ReturnMessage = "";
    $DatePlaceholder = $dienstwunsch['date'];
    $typePlaceholder =  $dienstwunsch['type'];
    $commentPlaceholder =  $dienstwunsch['create_comment'];
    $DateErr = "";

    // Do stuff
    if(isset($_POST['edit_dienstwunsch_action'])){

        $commentPlaceholder = trim($_POST['comment_user']);

        // Do some DAU-Checks here
        if($DAUcheck==0){

            $DeleteComment = "Von MitarbeiterIn gelöscht";
            $Return = edit_dienstwunsch_db($mysqli, $dienstwunsch['id'], $typePlaceholder, get_current_user_id(), $commentPlaceholder);
            if($Return['success']){
                $OutputMode="show_return_card";
                $ReturnMessage = "Dienstwunsch erfolgreich bearbeitet!";
            } else {
                $OutputMode="show_return_card";
                $ReturnMessage = $Return['err'];
            }
        }
    }

    if($OutputMode=="show_form"){
        //Build Form
        $FormHTML .= "<h5>Sie können bei bereits angelegten Dienstwünschen lediglich den Kommentar bearbeiten. Bei Abweichenden Tagen oder Wunscharten bitte einen neuen Wunsch anlegen.</h5>";
        $FormHTML .= form_hidden_input_generator('dienstwunsch_id', $dienstwunsch['id']);
        $FormHTML .= form_group_input_date('Datum', 'date', $DatePlaceholder, true, $DateErr, true);
        $FormHTML .= form_group_dropdown_dienstwunschtypen($mysqli, 'Dienstwunsch', 'type', $typePlaceholder, true, '', true);
        $FormHTML .= form_group_input_text('Kommentar des/der Antragstellers/in', 'comment_user', $commentPlaceholder, false, '', false);
        $FormHTML .= "<br>";
        $FormHTML .= form_group_continue_return_buttons(true, 'Bearbeiten', 'edit_dienstwunsch_action', 'btn-primary', true, 'Zurück', 'wunschdienst_go_back', 'btn-primary');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');
        return card_builder('Dienstwunsch bearbeiten','Möchten Sie diesen Dienstwunsch wirklich bearbeiten?', $FORM);
    }else{
        $FormHTML = form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'wunschdienst_go_back', 'btn-primary');
        $FORM = form_builder($FormHTML, 'self', 'POST');
        return card_builder('Dienstwunsch bearbeiten',$ReturnMessage, $FORM);
    }

}

function edit_dienstwunsch_management($mysqli, $dienstwunsch){

    // Initialize Placeholder & Error Variables
    $FormHTML = "";
    $OutputMode = "show_form";
    $DAUcheck = 0;
    $ReturnMessage = "";
    $DatePlaceholder = $dienstwunsch['date'];
    $userIDPlaceholder = $dienstwunsch['user'];
    $typePlaceholder =  $dienstwunsch['type'];
    $commentPlaceholder =  $dienstwunsch['create_comment'];
    $DateErr = $TypeErr = $CommentErr = "";

    // Implement multi OrgUE Sites
    if(isset($_POST['org_ue'])){
        $UE = $_POST['org_ue'];
    } else {
        $UE = $_GET['org_ue'];
    }

    // Do stuff
    if(isset($_POST['edit_dienstwunsch_action'])){

        $typePlaceholder = trim($_POST['type']);
        $commentPlaceholder = trim($_POST['comment_user']);

        if(strlen($dienstwunsch['create_comment'])>strlen($commentPlaceholder)){
            $DAUcheck++;
            $CommentErr = "Bitte ERGÄNZEN Sie nur die Kommentarspalte, damit keine Informationen verloren gehen!";
        }

        if($typePlaceholder!=$dienstwunsch['type']){
            if(strlen($dienstwunsch['create_comment'])>=strlen($commentPlaceholder)){
                $DAUcheck++;
                $TypeErr = $CommentErr = "Sie haben den Typ des Dienstwunsches verändert. Bitte ERGÄNZEN Sie die Kommentarspalte, warum dies gemacht wurde.!";
            }
        }

        // Do some DAU-Checks here
        if($DAUcheck==0){

            $DeleteComment = "Von MitarbeiterIn gelöscht";
            $Return = edit_dienstwunsch_db($mysqli, $dienstwunsch['id'], $typePlaceholder, get_current_user_id(), $commentPlaceholder);
            if($Return['success']){
                $OutputMode="show_return_card";
                $ReturnMessage = "Dienstwunsch erfolgreich bearbeitet!";
            } else {
                $OutputMode="show_return_card";
                $ReturnMessage = $Return['err'];
            }
        }
    }

    if($OutputMode=="show_form"){
        //Build Form
        $FormHTML .= form_hidden_input_generator('dienstwunsch_id', $dienstwunsch['id']);
        $FormHTML .= form_hidden_input_generator('org_ue', $UE);
        $FormHTML .= form_group_input_date('Datum', 'date', $DatePlaceholder, true, $DateErr, true);
        $FormHTML .= form_group_dropdown_all_users('MitarbeiterIn', 'user', $userIDPlaceholder, true, '', true);
        $FormHTML .= form_group_dropdown_dienstwunschtypen($mysqli, 'Dienstwunsch', 'type', $typePlaceholder, true, $TypeErr, false);
        $FormHTML .= form_group_input_text('Kommentar des/der Antragstellers/in', 'comment_user', $commentPlaceholder, true, $CommentErr, false);
        $FormHTML .= "<br>";
        $FormHTML .= form_group_continue_return_buttons(true, 'Bearbeiten', 'edit_dienstwunsch_action', 'btn-primary', true, 'Zurück', 'wunschdienst_go_back', 'btn-primary');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');
        return card_builder('Dienstwunsch bearbeiten','Möchten Sie diesen Dienstwunsch wirklich bearbeiten?', $FORM);
    }else{
        $FormHTML = form_hidden_input_generator('org_ue', $UE);
        $FormHTML .= form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'wunschdienst_go_back', 'btn-primary');
        $FORM = form_builder($FormHTML, 'self', 'POST');
        return card_builder('Dienstwunsch bearbeiten',$ReturnMessage, $FORM);
    }

}

function populate_day_wuup_tabelle_management($Day,$UserID,$AllAbwesenheiten,$AllWishes,$WishTypes,$RollenUser,$Total,$OA=0,$FA=0,$AA=0){

    $ReturnVals=[];
    $Abwesenheitstypen = explode(',',ABWESENHEITENTYPEN);
    $HolidayWeekend = day_is_a_weekend_or_holiday($Day);
    $Answer = "<td></td>";

    //Colorize stuff in case field is empty based on weekend/holidays
    if($HolidayWeekend){
        $Answer = "<td class='table-secondary'></td><td class='table-secondary'></td>";
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

                if($Abwesenheit['status_bearbeitung']=="Beantragt"){
                    if($HolidayWeekend){
                        $Answer = "<td class='text-center table-warning' colspan='2'>".$Kuerzel."*</td>";
                    } else {
                        $Answer = "<td class='text-center table-warning'>".$Kuerzel."*</td>";
                    }
                } elseif ($Abwesenheit['status_bearbeitung']=="Genehmigt"){
                    if($HolidayWeekend){
                        $Answer = "<td class='text-center ".$Farbe."' colspan='2'>".$Kuerzel."</td>";
                    } else {
                        $Answer = "<td class='text-center ".$Farbe."'>".$Kuerzel."</td>";
                    }

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

    //Loop through all Wishes
    //Catch funky case where there can be two wishes on a single day - ordered anti-alphabetically (tag->nacht)
    $NightShit = "";

    foreach($AllWishes as $wish){

        // Only check Abwesenheiten that count for User
        if($wish['user']==$UserID) {

            //Check if Abwesenheit is active on this day
            if(strtotime($wish['date'])==$Day){

                foreach ($WishTypes as $wishType){

                    if($wishType['id']==$wish['type']){

                        // Generate Tooltip if Wish has a comment
                        if($wish['create_comment']!=''){
                            $Content = '<a href="#" data-bs-toggle="tooltip" data-bs-html="true" title="'.$wish['create_comment'].'">'.$wishType['name_short'].'</a>';
                        } else {
                            $Content = $wishType['name_short'];
                        }

                        if($HolidayWeekend){
                            if($wishType['type']=='ruf'){
                                $Answer = "<td class='text-center ".$wishType['colors']."' colspan='2'>".$Content."</td>";
                            } else {
                                //Catch funky case where there can be two wishes on a single day - ordered anti-alphabetically (tag->nacht)
                                if($wishType['type']=='nacht'){
                                    $NightShit = "<td class='text-center ".$wishType['colors']."'>".$Content."</td>";
                                }
                                if($wishType['type']=='tag'){
                                    if($NightShit!=""){
                                        $Answer = "<td class='text-center ".$wishType['colors']."'>".$Content."</td>".$NightShit;
                                        $NightShit = '';
                                    } else {
                                        $Answer = "<td class='text-center ".$wishType['colors']."'>".$Content."</td><td class='table-secondary'></td>";
                                    }
                                }
                            }
                        } else {
                            $Answer = "<td class='text-center ".$wishType['colors']."'>".$Content."</td>";
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