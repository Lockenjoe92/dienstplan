<?php

function bereitschaftsdienstplan_funktionsbuttons_management($Month,$Year,$dienstgruppeErr=""){

    $placeholderStart = date('Y-m-d', strtotime($Year.'-'.$Month.'-01'));
    $placeholderEnd = date('Y-m-t', strtotime($placeholderStart));
    $placeholderDienstgruppe = $placeholderMode = "";
    if(isset($_POST['activate_automatik'])){
        $placeholderStart = $_POST['automatik_start_date'];
        $placeholderEnd = $_POST['automatik_end_date'];
        $placeholderDienstgruppe = $_POST['automatik_dienstgruppe'];
        $placeholderMode = $_POST['automatik_mode'];
    }


    $FORMhtmlDate = '<div class="row align-items-center">';
    $FORMhtmlDate .= "<div class='col'></div>";
    $FORMhtmlDate .= "<div class='col'>".form_dropdown_months('month',$Month)."</div>";
    $FORMhtmlDate .= "<div class='col'>".form_dropdown_years('year', $Year)."</div>";
    $FORMhtmlDate .= "<div class='col'></div>";
    $FORMhtmlDate .= "<div class='col'></div>";
    $FORMhtmlDate .= "<div class='col'>".form_group_continue_return_buttons(true, 'Reset', 'reset_calendar', 'btn-primary', true, 'Zeitraum wählen', 'action_change_date', 'btn-primary')."</div>";
    $FORMhtmlDate .= "</div>";

    // Automatik
    $FORMhtmlAutomatik = '<div class="row align-items-center">';
    $FORMhtmlAutomatik .= "<div class='col'><strong>Automatik</strong></div>";
    $FORMhtmlAutomatik .= "<div class='col'>".form_group_input_date('', 'automatik_start_date', $placeholderStart, false, '')."</div>";
    $FORMhtmlAutomatik .= "<div class='col'>".form_group_input_date('', 'automatik_end_date', $placeholderEnd, false, '')."</div>";
    $FORMhtmlAutomatik .= "<div class='col'>".form_group_dropdown_dienstgruppen('', 'automatik_dienstgruppe', $placeholderDienstgruppe, false, $dienstgruppeErr, false, 'Dienstgruppe')."</div>";
    $FORMhtmlAutomatik .= "<div class='col'>".form_dropdown_bd_automatik_mode('automatik_mode', $placeholderMode)."</div>";
    $FORMhtmlAutomatik .= "<div class='col'><input type='submit' class='btn btn-outline-danger' value='Automatik starten' name='activate_automatik'></div>";
    $FORMhtmlAutomatik .= "</div>";

    // Freigabesystem
    $FORMhtmlFreigabe = '<div class="row align-items-center">';
    $StatusMonat = lade_bd_freigabestatus_monat($Month, $Year);
    if(sizeof($StatusMonat)==0){
        $FORMhtmlFreigabe .= "<div class='col'><strong>Freigabestatus:</strong></div>";
        $FORMhtmlFreigabe .= "<div class='col'>Dieser Monat wurde noch nicht freigegeben!</div>";
        $FORMhtmlFreigabe .= "<div class='col'></div>";
        $FORMhtmlFreigabe .= "<div class='col'></div>";
        $FORMhtmlFreigabe .= "<div class='col'></div>";
        $FORMhtmlFreigabe .= "<div class='col'><input type='submit' class='btn btn-outline-primary' value='Freigeben' name='save_bd_month_freigabestatus_go'></div>";
    } else {
        $StatusMonat = $StatusMonat[0];
        $mysqli = connect_db();
        $SearchDate = $Year."-".$Month."-01";
        $LastDayOfConcideredMonth = date('Y-m-t', strtotime($SearchDate));
        $AllUsers = get_sorted_list_of_all_users($mysqli, 'abteilungsrollen DESC, nachname ASC', false, $LastDayOfConcideredMonth);

        $UserInfosFreigebender = get_user_infos_by_id_from_list($StatusMonat['freigegeben_von'], $AllUsers);
        $FORMhtmlFreigabe .= "<div class='col'><strong>Freigabestatus:</strong></div>";
        $FORMhtmlFreigabe .= "<div class='col'>Am ".date('d.m.Y', strtotime($StatusMonat['timestamp']))." von ".$UserInfosFreigebender['vorname']." ".$UserInfosFreigebender['nachname']." freigegeben!</div>";
        $FORMhtmlFreigabe .= "<div class='col'></div>";
        $FORMhtmlFreigabe .= "<div class='col'></div>";
        $FORMhtmlFreigabe .= "<div class='col'></div>";
        $FORMhtmlFreigabe .= "<div class='col'><input type='submit' class='btn btn-outline-danger' value='Freigabe zurücknehmen' name='save_bd_month_freigabestatus_delete'></div>";
    }
    $FORMhtmlFreigabe .= "</div>";

    $FORMhtml = $FORMhtmlDate.$FORMhtmlAutomatik.$FORMhtmlFreigabe;

    $HTML = container_builder(form_builder($FORMhtml, 'self', 'POST'));

    return $HTML;

}

function bereitschaftsdienstplan_funktionsbuttons_users($Month,$Year,$Freigaben=[]){

    $FORMhtml = '<div class="container text-center">';
    $FORMhtml .= '<div class="row align-items-center">';
    $FORMhtml .= "<div class='col'>".form_dropdown_months('month',$Month)."</div>";
    $FORMhtml .= "<div class='col'>".form_dropdown_years('year', $Year)."</div>";
    $FORMhtml .= "<div class='col'>".form_group_continue_return_buttons(true, 'Reset', 'reset_calendar', 'btn-primary', true, 'Zeitraum wählen', 'action_change_date', 'btn-primary')."</div>";
    $FORMhtml .= "</div>";

    $FORMhtml .= '<div class="row align-items-center">';
    if(sizeof($Freigaben)==0){
        $FORMhtml .= "<div class='col'><strong>Freigabestatus:</strong></div>";
        $FORMhtml .= "<div class='col'>Dieser Monat wurde noch nicht freigegeben!</div>";
        $FORMhtml .= "<div class='col'></div>";

    } else {
        $Freigaben = $Freigaben[0];
        $mysqli = connect_db();
        $SearchDate = $Year."-".$Month."-01";
        $LastDayOfConcideredMonth = date('Y-m-t', strtotime($SearchDate));
        $AllUsers = get_sorted_list_of_all_users($mysqli, 'abteilungsrollen DESC, nachname ASC', false, $LastDayOfConcideredMonth);
        $UserInfosFreigebender = get_user_infos_by_id_from_list($Freigaben['freigegeben_von'], $AllUsers);
        $FORMhtml .= "<div class='col'><strong>Freigabestatus:</strong></div>";
        $FORMhtml .= "<div class='col'>Am ".date('d.m.Y', strtotime($Freigaben['timestamp']))." von ".$UserInfosFreigebender['vorname']." ".$UserInfosFreigebender['nachname']." freigegeben.</div>";
        $FORMhtml .= "<div class='col'></div>";
    }
    $FORMhtml .= "</div>";
    $FORMhtml .= "</div>";

    return container_builder(form_builder($FORMhtml));

}

function bereitschaftsdienstplan_table_management($Month,$Year){

    //Initialze & fetch stuff
    $mysqli = connect_db();
    $AllUsers = get_sorted_list_of_all_users($mysqli, 'abteilungsrollen DESC, nachname ASC', true, '');
    $AllBDTypes = get_list_of_all_bd_types($mysqli);
    $AllBDmatrixes = get_list_of_all_bd_matrixes($mysqli);
    $AllBDeinteilungen = get_sorted_list_of_all_bd_einteilungen($mysqli);
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
            $Populate = populate_day_bd_plan_management($DateConcerned, $AllBDTypes, $AllBDmatrixes, $AllBDeinteilungen, $AllUsers, $Tabindex);
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
    #$Table .= "<script>
  #$(function () {
    #$('.popper').popover({
        #placement: 'bottom',
        #container: 'body',
       # html: true,
      #  content: function () {
     #       return $(this).next('.popper-content').html();
    #    }
   # });
    
 ##   $('#dynamic_content').html(make_bd_modal_loader());
#}
#)
#
  ##</script>";

    return $Table;

}

function bereitschaftsdienstplan_table_users($Month,$Year,$Freigaben=[]){

    //Initialze & fetch stuff
    $mysqli = connect_db();
    $SearchDate = $Year."-".$Month."-01";
    $LastDayOfConcideredMonth = date('Y-m-t', strtotime($SearchDate));
    $AllUsers = get_sorted_list_of_all_users($mysqli, 'abteilungsrollen DESC, nachname ASC', true, $LastDayOfConcideredMonth);
    $AllBDTypes = get_list_of_all_bd_types($mysqli);
    $AllBDmatrixes = get_list_of_all_bd_matrixes($mysqli);
    $AllBDeinteilungen = get_sorted_list_of_all_bd_einteilungen($mysqli);
    $FirstDayOfSelectedMonthString = $Year."-".$Month."-01";
    $FirstDayOfSelectedMonth = strtotime($FirstDayOfSelectedMonthString);
    if(sizeof($Freigaben)==0){
        $Freigegeben = false;
    } else {
        $Freigegeben = true;
    }

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
            $Populate = populate_day_bd_plan_users($DateConcerned, $AllBDTypes, $AllBDmatrixes, $AllBDeinteilungen, $AllUsers, $Freigegeben, $Tabindex);
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

function populate_day_bd_plan_management($DateConcerned, $AllBDTypes, $AllBDmatrixes, $AllBDeinteilungen, $AllUsers, $Tabindex){

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
                    #$ParserResults = parse_bd_candidates_on_day_for_certain_bd_type($DateConcerned, $BDType['id'], $AllBDeinteilungen, $Allwishes, $AllBDassignments, $AllAbwesenheiten, $AllWishTypes, $AllUsers, $AllBDTypes, $Holidayweekend);
                    #$EinteilungenHeute = $ParserResults['assigned_candidates'];
                    $Einteilung = get_bereitschaftsdienst_einteilungen_on_day($DateConcerned, $AllBDeinteilungen, $BDType['id']);
                    if(sizeof($Einteilung)==0){
                        //2. no entries -> parse wishlist
                        if(time()>$DateConcerned){
                            #$Row .= "<td class='text-center align-middle table-danger'>".build_modal_popup_bd_planung($Tabindex, $DateConcerned, $ParserResults['candidates'], $BDType['id'], [], $AllBDTypes)."</td>";
                            $Row .= "<td class='text-center align-middle table-danger'>".build_bd_edit_link('nachbesetzen', $DateConcerned, $BDType['id'])."</td>";
                        } else {
                            #$Row .= "<td class='text-center align-middle table-warning'>".build_modal_popup_bd_planung($Tabindex, $DateConcerned, $ParserResults['candidates'], $BDType['id'], [], $AllBDTypes)."</td>";
                            $Row .= "<td class='text-center align-middle table-warning'>".build_bd_edit_link('besetzen', $DateConcerned, $BDType['id'])."</td>";
                        }

                    } else {
                        $counter = 0;
                        $RowContent = "";
                        foreach($Einteilung as $User){
                            if($counter>0){
                                $RowContent .= "<br>";
                            }
                            // Build User Name String
                         $CurrentUserInfos = get_user_infos_by_id_from_list($User['user'], $AllUsers);
                            if(!$CurrentUserInfos){
                                var_dump($User['user'], date('d.m.Y', $DateConcerned));
                            }
                            $RowContent .= $CurrentUserInfos['nachname'].", ".$CurrentUserInfos['vorname'].".";
                         $counter++;
                        }

                        if(sizeof($Einteilung)==$BDType['req_employees_per_day']){
                            #$Row .= "<td class='text-center align-middle table-success'>".build_modal_popup_bd_planung($Tabindex, $DateConcerned, $ParserResults['candidates'], $BDType['id'], $EinteilungenHeute, $AllBDTypes)."</td>";
                            $Row .= "<td class='text-center align-middle table-success'>".build_bd_edit_link($RowContent, $DateConcerned, $BDType['id'])."</td>";
                        } else {
                            #$Row .= "<td class='text-center align-middle table-warning'>".build_modal_popup_bd_planung($Tabindex, $DateConcerned, $ParserResults['candidates'], $BDType['id'], $EinteilungenHeute, $AllBDTypes)."</td>";
                            $Row .= "<td class='text-center align-middle table-warning'>".build_bd_edit_link($RowContent, $DateConcerned, $BDType['id'])."</td>";
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

function build_bd_edit_link($StringAitem, $DateConcerned, $BDType){

    return "<a href='https://dienstplan.marcsprojects.de/bereitschaftsdienstplan_anaesthesie.php?action=edit_bd&concerneddate=".$DateConcerned."&type=".$BDType."'>".$StringAitem."</a>";

}

function populate_day_bd_plan_users($DateConcerned, $AllBDTypes, $AllBDmatrixes, $AllBDeinteilungen, $AllUsers, $Freigegeben, $Tabindex){
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
                    if($Freigegeben){
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
                    } else {
                        $Row .= "<td class='text-center align-middle table-light'></td>";
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

function build_modal_popup_bd_planung_with_loader($Tabindex, $DateConcerned, $AssignedUserHTML, $BDtype, $AllBDTypes){

    $DateString = date('d.m.Y', $DateConcerned);
    $BDtypeInfos = get_bd_type_infos_from_list($BDtype, $AllBDTypes);

    $buildPopup = '<a class="" data-bs-toggle="modal" data-bs-target="#myModal'.$Tabindex.'">'.$AssignedUserHTML.'</a>';
    $buildPopup .= '<!-- The Modal -->
    <div class="modal fade" id="myModal'.$Tabindex.'">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header">';
    $buildPopup .= '<h4 class="modal-title">'.$BDtypeInfos['kuerzel'].' am '.$DateString.' - Besetzung ändern</h4>';
    $buildPopup .= '<button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>';

    //Build the Popup Body
    $buildPopup .= '<!-- Modal body --><div class="modal-body">';
    $buildPopup .= '<iframe src="https://dienstplan.marcsprojects.de/bd_plan_details.php?date='.$DateConcerned.'&bd_type='.$BDtype.'"></iframe>';
    $buildPopup .= '</div>';
    $buildPopup .= '</div>';
    $buildPopup .= '</div>';

    return $buildPopup;

}

function build_table_bd_planung($Tabindex, $DateConcerned, $CandidatesList, $BDtype, $UserAssignmentsOnDay=[], $AllBDTypes=[]){

    $BDtypeInfos = get_bd_type_infos_from_list($BDtype, $AllBDTypes);
    $DateString = date('d.m.Y', $DateConcerned);

    $buildPopup = "";
    if(sizeof($UserAssignmentsOnDay)==0){
        //Edit mode firstly highlights already assigned users and removes them from the red list
        $EditMode = false;
        if (time()>$DateConcerned) {
            $buildPopup .= '<h4 class="modal-title">'.$BDtypeInfos['kuerzel'].' am '.$DateString.' - Besetzung nachtragen</h4>';
        } else {
            $buildPopup .= '<h4 class="modal-title">'.$BDtypeInfos['kuerzel'].' am '.$DateString.' - Dienst besetzen</h4>';
        }
    } else {
        $EditMode = true;
        $buildPopup .= '<h4 class="modal-title">'.$BDtypeInfos['kuerzel'].' am '.$DateString.' - Besetzung ändern</h4>';
    }

    $buildPopupBody = '<!-- Table body -->
		<table class="table">
			<thead><th></th><th>Name</th><th>Dienstgruppe</th><th>Verfügbarkeit</th><th>Auslastungspunkte</th><th>Anzahl Bereitschaftsdienste diesen Monat</th><th>Grund/Kommentar</th></thead>
			<tbody>';

    //Edit mode firstly highlights already assigned users and removes them from the red list
    if($EditMode){
        foreach($UserAssignmentsOnDay as $AssignedUser){
            $buildPopupBody .= '<tr class="table-info"><td><input type="checkbox" class="form-check-input" name="assigned_user_'.$AssignedUser['assignmentObject']['user'].'"></td><td>'.$AssignedUser['userNameLong'].'</td><td>'.$AssignedUser['highest_bd_rank_kuerzel'].'</td><td>Eingeteilt</td><td>'.$AssignedUser['dienstbelastung'].'</td><td>'.$AssignedUser['anzahl_dienste_monat'].'</td><td>'.$AssignedUser['reason'].'</td></tr>';
        }
    }

    foreach ($CandidatesList as $CandidateItem){
        $buildPopupBody .= '<tr class="'.$CandidateItem['table-color'].'"><td><input type="checkbox" class="form-check-input" name="chosen_user_'.$CandidateItem['userID'].'"></td><td>'.$CandidateItem['userName'].'</td><td>'.$CandidateItem['highest_bd_rank_kuerzel'].'</td><td>'.$CandidateItem['verfuegbarkeit'].'</td><td>'.$CandidateItem['dienstbelastung'].'</td><td>'.$CandidateItem['anzahl_dienste_monat'].'</td><td>'.$CandidateItem['reason'].'<input type="hidden" name="comment_chosen_user_'.$CandidateItem['userID'].'" value="'.$CandidateItem['reason'].'"></input></td></tr>';
    }

    $buildPopupBody .= '</tbody>
		</table>
		'.form_group_input_text("Kommentar (optional)", "comment", "", false);

    if($EditMode){
        $buildPopupBody .= '<!-- Modal footer -->
      <div>
	  	<input type="submit" class="btn btn-outline-primary" value="Zuteilung ändern" name="action_edit_bd_zuteilung"></input>
        <input type="submit" class="btn btn-outline-danger" value="Zuteilung löschen" name="action_delete_bd_zuteilung"></input>
        <input type="submit" class="btn btn-danger" value="Zurück" name="action_abort_bd_zuteilung"></input>
      </div>';
    } else {
        $buildPopupBody .= '<!-- Modal footer -->
      <div>
	  	<input type="submit" class="btn btn-primary" value="Zuteilen" name="action_add_bd_zuteilung"></input>
        <input type="submit" class="btn btn-danger" value="Zurück" name="action_abort_bd_zuteilung"></input>
      </div>';
    }

    $buildPopupBody .= form_hidden_input_generator('date_concerned', $DateConcerned);
    $buildPopupBody .= form_hidden_input_generator('bd_type', $BDtype);

    $buildPopup .= form_builder($buildPopupBody, 'bereitschaftsdienstplan_anaesthesie.php?action=return');

    $buildPopup .='</div>
  </div>
</div>';

    return $buildPopup;

}

function build_modal_popup_bd_planung($Tabindex, $DateConcerned, $CandidatesList, $BDtype, $UserAssignmentsOnDay=[], $AllBDTypes=[]){

        $LimDate = strtotime("+3 days", $DateConcerned);
        $BDtypeInfos = get_bd_type_infos_from_list($BDtype, $AllBDTypes);
        $DateString = date('d.m.Y', $DateConcerned);

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
                if($Counter>0){
                    $AssignedUserNames .= "<br>";
                }
                $AssignedUserNames .= $User['userName'];
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
                $buildPopup .= '<h4 class="modal-title">'.$BDtypeInfos['kuerzel'].' am '.$DateString.' - Besetzung nachtragen</h4>';
            } else {
                $buildPopup .= '<h4 class="modal-title">'.$BDtypeInfos['kuerzel'].' am '.$DateString.' - Dienst besetzen</h4>';
            }
        } else {
            $EditMode = true;
            $buildPopup .= '<h4 class="modal-title">'.$BDtypeInfos['kuerzel'].' am '.$DateString.' - Besetzung ändern</h4>';
        }


    $buildPopup .= '
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>';

    $buildPopupBody = '<!-- Modal body -->
      <div class="modal-body">
		<table class="table">
			<thead><th></th><th>Name</th><th>Dienstgruppe</th><th>Verfügbarkeit</th><th>Auslastungspunkte</th><th>Anzahl Bereitschaftsdienste diesen Monat</th><th>Grund/Kommentar</th></thead>
			<tbody>';

    //Edit mode firstly highlights already assigned users and removes them from the red list
    if($EditMode){
        foreach($UserAssignmentsOnDay as $AssignedUser){
            $buildPopupBody .= '<tr class="table-info"><td><input type="checkbox" class="form-check-input" name="assigned_user_'.$AssignedUser['assignmentObject']['user'].'"></td><td>'.$AssignedUser['userNameLong'].'</td><td>'.$AssignedUser['highest_bd_rank_kuerzel'].'</td><td>Eingeteilt</td><td>'.$AssignedUser['dienstbelastung'].'</td><td>'.$AssignedUser['anzahl_dienste_monat'].'</td><td>'.$AssignedUser['reason'].'</td></tr>';
        }
    }

    foreach ($CandidatesList as $CandidateItem){
        $buildPopupBody .= '<tr class="'.$CandidateItem['table-color'].'"><td><input type="checkbox" class="form-check-input" name="chosen_user_'.$CandidateItem['userID'].'"></td><td>'.$CandidateItem['userName'].'</td><td>'.$CandidateItem['highest_bd_rank_kuerzel'].'</td><td>'.$CandidateItem['verfuegbarkeit'].'</td><td>'.$CandidateItem['dienstbelastung'].'</td><td>'.$CandidateItem['anzahl_dienste_monat'].'</td><td>'.$CandidateItem['reason'].'<input type="hidden" name="comment_chosen_user_'.$CandidateItem['userID'].'" value="'.$CandidateItem['reason'].'"></input></td></tr>';
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
    $AllUsers = get_sorted_list_of_all_users($mysqli, 'nachname ASC', true);
    #$Wuensche = get_sorted_list_of_all_dienstplanwünsche($mysqli, false);
    #$Wunschtypen = get_list_of_all_dienstplanwunsch_types($mysqli);

    if(sizeof($BDFreigaben)==1){
        if(($SelectedMonth=='') && ($SelectedYear=='')){
            $BeginDate = date('Y-m-01');
        } else {
            $BeginDate = $SelectedYear."-".$SelectedMonth."-01";
        }
        $EndDate = date("Y-m-t", strtotime('+2 months', strtotime($BeginDate)));

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
                    <th>Team</th>
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


                    // Build Team Information
                    $Options = '';
                    $GetTeamOnDay = get_bd_colleagues_on_assignment_day($Einteilungen, $BDtypen, $AllUsers, $Einteilung['day'], $CurrentUser);
                    foreach ($GetTeamOnDay as $teamMember){
                        $Options .= ''.$teamMember['bd_type_kuerzel'].': '.$teamMember['user_name'].' ';
                    }
                    $Team = '<a href="#" data-bs-toggle="tooltip" data-bs-html="true" title="'.$Options.'"><i class="bi bi-people-fill"></i></a>';

                    $DienstType = get_bereitschaftsdiensttype_details_by_type_id($BDtypen, $Einteilung['bd_type']);

                    if($counter==1){
                        $HTML .= '<td id="td-id-1" class="td-class-1" data-title="bootstrap table">'.date('d.m.Y', strtotime($Einteilung['day'])).'</td>';
                        $HTML .= '<td>'.$DienstType['name'].'</td>';
                        $HTML .= '<td> '.$Team.'</td>';
                    } else {
                        $HTML .= '<td id="td-id-'.$counter.'" class="td-class-'.$counter.'"">'.date('d.m.Y', strtotime($Einteilung['day'])).'</td>';
                        $HTML .= '<td>'.$DienstType['name'].'</td>';
                        $HTML .= '<td> '.$Team.'</td>';
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

function get_bd_colleagues_on_assignment_day($Einteilungen, $BDtypen, $AllUsers, $Day, $ExcludeUser){

    $Others = array();
    foreach ($Einteilungen as $einteilung){

        // Only on selected Day
        if($einteilung['day']==$Day){

            //Only get other users than current User
            if($einteilung['user']!=$ExcludeUser){

                // Get User Infos
                $CurrentUserInfos = get_user_infos_by_id_from_list($einteilung['user'], $AllUsers);

                // Get BD Type Infos
                $BDtypeInfos = get_bereitschaftsdiensttype_details_by_type_id($BDtypen, $einteilung['bd_type']);

                //Build entry item
                $Entry['bd_type_kuerzel'] = $BDtypeInfos['kuerzel'];
                $Entry['bd_type_rank'] = $BDtypeInfos['reihung'];
                $Entry['user_name'] = $CurrentUserInfos['nachname'].', '.$CurrentUserInfos['vorname'];

                $Others[] = $Entry;
            }
        }
    }

    //Sort by rank
    $columns = array_column($Others, 'bd_type_rank');
    array_multisort($columns, SORT_ASC, $Others);

    return $Others;
}