<?php

function bereitschaftsdienstplan_funktionsbuttons_management($Month,$Year){

    $FORMhtml = '<div class="container text-center">';
    $FORMhtml .= '<div class="row align-items-center">';
    $FORMhtml .= "<div class='col'>".form_dropdown_months('month',$Month)."</div>";
    $FORMhtml .= "<div class='col'>".form_dropdown_years('year', $Year)."</div>";
    $FORMhtml .= "<div class='col'>".form_group_continue_return_buttons(true, 'Reset', 'reset_calendar', 'btn-primary', true, 'Zeitraum wählen', 'action_change_date', 'btn-primary')."</div>";
    $FORMhtml .= "</div>";

    $FORMhtml .= '<div class="row align-items-center">';
    $StatusMonat = lade_bd_freigabestatus_monat($Month, $Year);
    if(sizeof($StatusMonat)==0){
        $FORMhtml .= "<div class='col'><strong>Freigabestatus:</strong></div>";
        $FORMhtml .= "<div class='col'>Dieser Monat wurde noch nicht freigegeben!</div>";
        $FORMhtml .= "<div class='col'><input type='submit' class='btn btn-outline-primary' value='Freigeben' name='save_bd_month_freigabestatus_go'></div>";
    } else {
        $StatusMonat = $StatusMonat[0];
        $mysqli = connect_db();
        $AllUsers = get_sorted_list_of_all_users($mysqli);
        $UserInfosFreigebender = get_user_infos_by_id_from_list($StatusMonat['freigegeben_von'], $AllUsers);
        $FORMhtml .= "<div class='col'><strong>Freigabestatus:</strong></div>";
        $FORMhtml .= "<div class='col'>Am ".date('d.m.Y, G:i', strtotime($StatusMonat['timestamp']))." Uhr von ".$UserInfosFreigebender['vorname']." ".$UserInfosFreigebender['nachname']." freigegeben!</div>";
        $FORMhtml .= "<div class='col'><input type='submit' class='btn btn-outline-danger' value='Freigabe zurücknehmen' name='save_bd_month_freigabestatus_delete'></div>";
    }
    $FORMhtml .= "</div>";
    $FORMhtml .= "</div>";

    $HTML = container_builder(form_builder($FORMhtml, 'self', 'POST'));

    return $HTML;

}

function bereitschaftsdienstplan_funktionsbuttons_users($Month,$Year){

    $FORMhtml = '<div class="container text-center">';
    $FORMhtml .= '<div class="row align-items-center">';
    $FORMhtml .= "<div class='col'>".form_dropdown_months('month',$Month)."</div>";
    $FORMhtml .= "<div class='col'>".form_dropdown_years('year', $Year)."</div>";
    $FORMhtml .= "<div class='col'>".form_group_continue_return_buttons(true, 'Reset', 'reset_calendar', 'btn-primary', true, 'Zeitraum wählen', 'action_change_date', 'btn-primary')."</div>";
    $FORMhtml .= "</div>";
    $FORMhtml .= "</div>";

    $HTML = container_builder(form_builder($FORMhtml, 'self', 'POST'));

    return $HTML;

}

function bereitschaftsdienstplan_table_management($Month,$Year){

    //Initialze & fetch stuff
    $mysqli = connect_db();
    $AllUsers = get_sorted_list_of_all_users($mysqli);
    $AllBDTypes = get_list_of_all_bd_types($mysqli);
    $AllBDmatrixes = get_list_of_all_bd_matrixes($mysqli);
    $Allwishes = get_sorted_list_of_all_dienstplanwünsche($mysqli);
    $AllWishTypes = get_list_of_all_dienstplanwunsch_types($mysqli);
    $AllBDeinteilungen = get_sorted_list_of_all_bd_einteilungen($mysqli);
    $AllBDassignments = get_all_users_bd_assignments($mysqli);
    $AllAbwesenheiten = get_sorted_list_of_all_abwesenheiten($mysqli);
    $FirstDayOfSelectedMonthString = $Year."-".$Month."-01";
    $FirstDayOfSelectedMonth = strtotime($FirstDayOfSelectedMonthString);

    // Initialize Table
    $Table = "<table class='table table-bordered table-sm table-condensed'>";

    //Setup Table Header
    $TableHeader = "<thead><tr>";
    $TableHeader .= "<th>Datum</th>";
    foreach ($AllBDTypes as $BDType) {
        $TableHeader .= "<th>".$BDType['kuerzel']."</th>";
    }
    $TableHeader .= "</tr></thead>";

    //Populate Table rows
    $TableRows = "";
    $Tabindex = 0;
    for($a=0;$a<=30;$a++){
        $CommandDate = "+" . $a . " days";
        $DateConcerned = strtotime($CommandDate, $FirstDayOfSelectedMonth);
        if(date("m", $DateConcerned)==$Month) {
            $Populate = populate_day_bd_plan_management($DateConcerned, $AllBDTypes, $AllBDmatrixes, $AllBDeinteilungen, $Allwishes, $AllBDassignments, $AllAbwesenheiten, $AllWishTypes, $AllUsers, $Tabindex);
            $Tabindex = $Populate['tabindex'];
            $TableRows .= $Populate['HTML'];
        }
    }

    //Setup Table Body
    $TableBody = '<tbody class="table-group-divider">'.$TableRows.'</tbody>';

    // Build that calendar
    $Table .= $TableHeader;
    $Table .= $TableBody;
    $Table .= "</table>";
    $Table .= "<script>
  $(function () {
    $('.popper').popover({
        placement: 'bottom',
        container: 'body',
        html: true,
        content: function () {
            return $(this).next('.popper-content').html();
        }
    })
})

  </script>";

    return $Table;

}

function bereitschaftsdienstplan_table_users($Month,$Year){

    //Initialze & fetch stuff
    $mysqli = connect_db();
    $AllUsers = get_sorted_list_of_all_users($mysqli);
    $AllBDTypes = get_list_of_all_bd_types($mysqli);
    $AllBDmatrixes = get_list_of_all_bd_matrixes($mysqli);
    $Allwishes = get_sorted_list_of_all_dienstplanwünsche($mysqli);
    $AllWishTypes = get_list_of_all_dienstplanwunsch_types($mysqli);
    $AllBDeinteilungen = get_sorted_list_of_all_bd_einteilungen($mysqli);
    $AllBDassignments = get_all_users_bd_assignments($mysqli);
    $AllAbwesenheiten = get_sorted_list_of_all_abwesenheiten($mysqli);
    $FirstDayOfSelectedMonthString = $Year."-".$Month."-01";
    $FirstDayOfSelectedMonth = strtotime($FirstDayOfSelectedMonthString);

    // Initialize Table
    $Table = "<table class='table table-bordered table-sm table-condensed'>";

    //Setup Table Header
    $TableHeader = "<thead><tr>";
    $TableHeader .= "<th>Datum</th>";
    foreach ($AllBDTypes as $BDType) {
        $TableHeader .= "<th>".$BDType['kuerzel']."</th>";
    }
    $TableHeader .= "</tr></thead>";

    //Populate Table rows
    $TableRows = "";
    $Tabindex = 0;
    for($a=0;$a<=30;$a++){
        $CommandDate = "+" . $a . " days";
        $DateConcerned = strtotime($CommandDate, $FirstDayOfSelectedMonth);
        if(date("m", $DateConcerned)==$Month) {
            $Populate = populate_day_bd_plan_users($DateConcerned, $AllBDTypes, $AllBDmatrixes, $AllBDeinteilungen, $AllBDassignments, $AllUsers, $Tabindex);
            $Tabindex = $Populate['tabindex'];
            $TableRows .= $Populate['HTML'];
        }
    }

    //Setup Table Body
    $TableBody = '<tbody class="table-group-divider">'.$TableRows.'</tbody>';

    // Build that calendar
    $Table .= $TableHeader;
    $Table .= $TableBody;
    $Table .= "</table>";
    $Table .= "<script>
  $(function () {
    $('.popper').popover({
        placement: 'bottom',
        container: 'body',
        html: true,
        content: function () {
            return $(this).next('.popper-content').html();
        }
    })
})

  </script>";

    return $Table;

}

function populate_day_bd_plan_management($DateConcerned, $AllBDTypes, $AllBDmatrixes, $AllBDeinteilungen, $Allwishes, $AllBDassignments, $AllAbwesenheiten, $AllWishTypes, $AllUsers, $Tabindex){

    if(day_is_a_weekend_or_holiday($DateConcerned)){
        $Holidayweekend = true;
        $searchedDayType = 'weekend';
    } else {
        $Holidayweekend = false;
        $searchedDayType = 'normal';
    }

    $Matrix = [];
    foreach ($AllBDmatrixes as $BDmatrix){
        if($BDmatrix['type_of_day']==$searchedDayType){
            $Matrix = $BDmatrix;
        }
    }

    //Deconstruct BD Matrix
    $MatrixUnpacked = explode(',', $Matrix['matrix']);

    $Row = "<tr>";
    if($Holidayweekend){
        $Row .= "<td class='table-secondary'>".date('d.m.Y', $DateConcerned)."</td>";
    } else {
        $Row .= "<td>".date('d.m.Y', $DateConcerned)."</td>";
    }

    foreach ($AllBDTypes as $BDType) {

        foreach ($MatrixUnpacked as $item){

            $Exploded = explode(':', $item);

            if($Exploded[0]==$BDType['id']){

                if($Exploded[1]==0){
                    $Row .= "<td class='text-center align-middle table-secondary'></td>";
                } else {
                    //1. check if this item already has been planned
                    $Einteilung = get_bereitschaftsdienst_einteilungen_on_day($DateConcerned, $AllBDeinteilungen, $BDType['id']);
                    $ParserResults = parse_bd_candidates_on_day_for_certain_bd_type($DateConcerned, $BDType['id'], $AllBDeinteilungen, $Allwishes, $AllBDassignments, $AllAbwesenheiten, $AllWishTypes, $AllUsers, $AllBDTypes);

                    if(sizeof($Einteilung)==0){
                        //2. no entries -> parse wishlist
                        if(time()>$DateConcerned){
                            $Row .= "<td class='text-center align-middle table-danger'>".build_modal_popup_bd_planung($Tabindex, $DateConcerned, $ParserResults['candidates'], $BDType['id'])."</td>";
                        } else {
                            if($ParserResults['num_found_candidates']>0){
                                $Row .= "<td class='text-center align-middle table-warning'>".build_modal_popup_bd_planung($Tabindex, $DateConcerned, $ParserResults['candidates'], $BDType['id'])."</td>";
                            } else {
                                $Row .= "<td class='text-center align-middle table-danger'>".build_modal_popup_bd_planung($Tabindex, $DateConcerned, $ParserResults['candidates'], $BDType['id'])."</td>";
                            }
                        }

                    } else {
                        if(sizeof($Einteilung)==$BDType['req_employees_per_day']){
                            $Row .= "<td class='text-center align-middle table-success'>".build_modal_popup_bd_planung($Tabindex, $DateConcerned, $ParserResults['candidates'], $BDType['id'], $Einteilung, $AllUsers)."</td>";
                        } else {
                            $Row .= "<td class='text-center align-middle table-warning'>".build_modal_popup_bd_planung($Tabindex, $DateConcerned, $ParserResults['candidates'], $BDType['id'], $Einteilung, $AllUsers)."</td>";
                        }
                    }
                    $Tabindex++;
                }
            }
        }
    }

    $Row .= "<tr>";

    $ReturnVals['HTML'] = $Row;
    $ReturnVals['tabindex'] = $Tabindex;

    return $ReturnVals;
}

function populate_day_bd_plan_users($DateConcerned, $AllBDTypes, $AllBDmatrixes, $AllBDeinteilungen, $AllBDassignments, $AllUsers, $Tabindex){
    if(day_is_a_weekend_or_holiday($DateConcerned)){
        $Holidayweekend = true;
        $searchedDayType = 'weekend';
    } else {
        $Holidayweekend = false;
        $searchedDayType = 'normal';
    }

    $Matrix = [];
    foreach ($AllBDmatrixes as $BDmatrix){
        if($BDmatrix['type_of_day']==$searchedDayType){
            $Matrix = $BDmatrix;
        }
    }

    //Deconstruct BD Matrix
    $MatrixUnpacked = explode(',', $Matrix['matrix']);

    $Row = "<tr>";
    if($Holidayweekend){
        $Row .= "<td class='table-secondary'>".date('d.m.Y', $DateConcerned)."</td>";
    } else {
        $Row .= "<td>".date('d.m.Y', $DateConcerned)."</td>";
    }

    foreach ($AllBDTypes as $BDType) {

        foreach ($MatrixUnpacked as $item){

            $Exploded = explode(':', $item);

            if($Exploded[0]==$BDType['id']){

                if($Exploded[1]==0){
                    $Row .= "<td class='text-center align-middle table-secondary'></td>";
                } else {
                    //1. check if this item already has been planned
                    $Einteilung = get_bereitschaftsdienst_einteilungen_on_day($DateConcerned, $AllBDeinteilungen, $BDType['id']);
                    if(sizeof($Einteilung)==0){
                        //2. no entries -> parse wishlist
                        $Row .= "<td class='text-center align-middle table-danger'></td>";
                    } else {
                        $counter = 0;
                        $RowContent = "";
                        foreach($Einteilung as $User){
                            if($counter>0){
                                $RowContent .= "<br>";
                            }

                            // Build User Name String
                            $CurrentUserInfos = get_user_infos_by_id_from_list($User['user'], $AllUsers);
                            $RowContent .= $CurrentUserInfos['nachname'].", ".$CurrentUserInfos['vorname'][0].".";
                            $counter++;
                        }
                        $Row .= "<td class='text-center align-middle table-success'>".$RowContent."</td>";
                    }
                    $Tabindex++;
                }
            }
        }
    }

    $Row .= "<tr>";

    $ReturnVals['HTML'] = $Row;
    $ReturnVals['tabindex'] = $Tabindex;

    return $ReturnVals;
}

function build_modal_popup_bd_planung($Tabindex, $DateConcerned, $CandidatesList, $BDtype, $UserAssignmentsOnDay=[], $AllUsers=[]){

        $LimDate = strtotime("+3 days", $DateConcerned);

        if(sizeof($UserAssignmentsOnDay)==0){
            if(time()>$LimDate){
                $buildPopup = 'unbesetzt';
            } elseif (time()>$DateConcerned) {
                $buildPopup = '<a class="" data-bs-toggle="modal" data-bs-target="#myModal'.$Tabindex.'">nachtragen</a>';
            } else {
                $buildPopup = '<a class="" data-bs-toggle="modal" data-bs-target="#myModal'.$Tabindex.'">besetzen</a>';
            }
        } else {
            $AssignedUserNames = "";
            $Counter = 0;
            foreach ($UserAssignmentsOnDay as $User) {
                $SelectedUserInfos = get_user_infos_by_id_from_list($User['user'], $AllUsers);
                if($Counter>0){
                    $AssignedUserNames .= "<br>";
                }
                $AssignedUserNames .= $SelectedUserInfos['nachname'].', '.$SelectedUserInfos['vorname'][0].'.';
                $Counter++;
            }

            if(time()>$LimDate){
                $buildPopup = $AssignedUserNames;
            } else {
                $buildPopup = '<a class="" data-bs-toggle="modal" data-bs-target="#myModal'.$Tabindex.'">'.$AssignedUserNames.'</a>';
            }
        }

    $buildPopup .= '<!-- The Modal -->
<div class="modal fade" id="myModal'.$Tabindex.'">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">';

        if(sizeof($UserAssignmentsOnDay)==0){
            //Edit mode firstly highlights already assigned users and removes them from the red list
            $EditMode = false;
            if (time()>$DateConcerned) {
                $buildPopup .= '<h4 class="modal-title">Besetzung nachtragen</h4>';
            } else {
                $buildPopup .= '<h4 class="modal-title">Dienst besetzen</h4>';
            }
        } else {
            $EditMode = true;
            $buildPopup .= '<h4 class="modal-title">Besetzung ändern</h4>';
        }


    $buildPopup .= '
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>';

    $buildPopupBody = '<!-- Modal body -->
      <div class="modal-body">
		<table class="table">
			<thead><th></th><th>Name</th><th>Verfügbarkeit</th><th>Grund/Kommentar</th></thead>
			<tbody>';

    //Edit mode firstly highlights already assigned users and removes them from the red list
    if($EditMode){
        $IDsOfAssignedUsers = [];
        foreach($UserAssignmentsOnDay as $AssignedUser){
            $SelectedUserInfos = get_user_infos_by_id_from_list($AssignedUser['user'], $AllUsers);
            $buildPopupBody .= '<tr class="table-info"><td><input type="checkbox" class="form-check-input" name="assigned_user_'.$AssignedUser['user'].'"></td><td>'.$SelectedUserInfos['nachname'].', '.$SelectedUserInfos['vorname'].'</td><td>Eingeteilt</td><td>'.$AssignedUser['create_comment'].'</td></tr>';
            $IDsOfAssignedUsers[] = $AssignedUser['user'];
        }

        //Now remove Assigned Users from Candidate-List
        $UpdatedCandidatesList = [];
        foreach ($CandidatesList as $CandidateItem){
            if(!in_array($CandidateItem['userID'], $IDsOfAssignedUsers)){
                $UpdatedCandidatesList[] = $CandidateItem;
            }
        }

        $CandidatesList = $UpdatedCandidatesList;
    }

    foreach ($CandidatesList as $CandidateItem){
        $buildPopupBody .= '<tr class="'.$CandidateItem['table-color'].'"><td><input type="checkbox" class="form-check-input" name="chosen_user_'.$CandidateItem['userID'].'"></td><td>'.$CandidateItem['userName'].'</td><td>'.$CandidateItem['verfuegbarkeit'].'</td><td>'.$CandidateItem['reason'].'<input type="hidden" name="comment_chosen_user_'.$CandidateItem['userID'].'" value="'.$CandidateItem['reason'].'"></input></td></tr>';
    }

    $buildPopupBody .= '</tbody>
		</table>
		'.form_group_input_text("Kommentar (optional)", "comment", "", false).'
      </div>';

    if($EditMode){
        $buildPopupBody .= '<!-- Modal footer -->
      <div class="modal-footer">
	  	<input type="submit" class="btn btn-outline-primary" value="Zuteilung ändern" name="action_edit_bd_zuteilung"></input>
        <input type="submit" class="btn btn-outline-danger" value="Zuteilung löschen" name="action_delete_bd_zuteilung"></input>
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Schließen</button>
      </div>';
    } else {
        $buildPopupBody .= '<!-- Modal footer -->
      <div class="modal-footer">
	  	<input type="submit" class="btn btn-primary" value="Zuteilen" name="action_add_bd_zuteilung"></input>
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Schließen</button>
      </div>';
    }

    $buildPopupBody .= form_hidden_input_generator('date_concerned', $DateConcerned);
    $buildPopupBody .= form_hidden_input_generator('bd_type', $BDtype);

    $buildPopup .= form_builder($buildPopupBody);

    $buildPopup .='</div>
  </div>
</div>';

    return $buildPopup;
}

function table_bereitschaftsdienstplan_user($mysqli,$CurrentUser,$SelectedMonth='',$SelectedYear=''){

    // deal with stupid "" and '' problems
    $bla = '"{"key": "value"}"';
    $Einteilungen = get_sorted_list_of_all_bd_einteilungen($mysqli);
    $BDtypen = get_list_of_all_bd_types($mysqli);
    $BDFreigaben = lade_bd_freigabestatus_monat(date('m'), date('Y'));
    #$Wuensche = get_sorted_list_of_all_dienstplanwünsche($mysqli, false);
    #$Wunschtypen = get_list_of_all_dienstplanwunsch_types($mysqli);

    if(sizeof($BDFreigaben)==1){
        if(($SelectedMonth=='') && ($SelectedYear=='')){
            $BeginDate = "01-".date('m-Y');
        } else {
            $BeginDate = "01-".$SelectedMonth."-".$SelectedYear;
        }
        $EndDate = date("Y-m-t", strtotime($BeginDate));

        // Initialize Table
        $HTML = '<table data-toggle="table" 
data-locale="de-DE"
data-show-columns="true" 
  data-multiple-select-row="true"
  data-click-to-select="true"
  data-pagination="true">';

        // Setup Table Head
        $HTML .= '<thead>
                <tr class="tr-class-1">
                    <th data-field="day" data-sortable="true">Tag</th>
                    <th data-field="type" data-sortable="true">Diensttyp</th>
                    <th>Optionen</th>
                </tr>
              </thead>';

        // Fill table body
        $HTML .= '<tbody>';
        $counter = 1;
        foreach ($Einteilungen as $Einteilung){

            if($Einteilung['user'] == $CurrentUser){
                if(($Einteilung['day']>=$BeginDate) && ($Einteilung['day']<=$EndDate)){
                    // Build rows
                    if($counter==1){
                        $HTML .= '<tr id="tr-id-1" class="tr-class-1" data-title="bootstrap table" data-object='.$bla.'>';
                    } else {
                        $HTML .= '<tr id="tr-id-'.$counter.'" class="tr-class-'.$counter.'">';
                    }


                    // Build edit/delete Buttons
                    $Options = '';

                    $DienstType = get_bereitschaftsdiensttype_details_by_type_id($BDtypen, $Einteilung['bd_type']);

                    if($counter==1){
                        $HTML .= '<td id="td-id-1" class="td-class-1" data-title="bootstrap table">'.$Einteilung['day'].'</td>';
                        $HTML .= '<td>'.$DienstType['name'].'</td>';
                        $HTML .= '<td> '.$Options.'</td>';
                    } else {
                        $HTML .= '<td id="td-id-'.$counter.'" class="td-class-'.$counter.'"">'.$Einteilung['day'].'</td>';
                        $HTML .= '<td>'.$DienstType['name'].'</td>';
                        $HTML .= '<td> '.$Options.'</td>';
                    }

                    // close row and count up
                    $HTML .= "</tr>";
                    $counter++;
                }
            }

        }
        $HTML .= '</tbody>';
        $HTML .= '</table>';
    } else {
        $HTML = "<div class='container text-center'><div class='row'><div class='col align-self-center'><b>Bereitschaftsdienstplan noch nicht freigegeben!</b></div></div></div>";
    }

    return $HTML;

}