<?php

function table_management_department_events($mysqli){

    // deal with stupid "" and '' problems
    $bla = '"{"key": "value"}"';
    $Events = get_sorted_list_of_all_department_events($mysqli);
    $userList = get_sorted_list_of_all_users($mysqli, 'id ASC', true);

    //Sort into Active and Passed Events
    $ActiveEvents = [];
    $PassedEventsThisYear = [];
    $PassedEventsOlderThanThisYear = [];
    $Now = time();
    $FirstDayThisYear = strtotime(date('Y').'01-01 00:00:01');
    foreach ($Events as $event){
        $EndOfCurrentEvent = strtotime($event['end']);
        if($EndOfCurrentEvent>$Now){
            $ActiveEvents[] = $event;
        } else {
            if($EndOfCurrentEvent>$FirstDayThisYear){
                $PassedEventsThisYear[] = $event;
            } else {
                $PassedEventsOlderThanThisYear[] = $event;
            }
        }
    }

    // Setup Toolbar
    $HTML = '<div id="toolbar">';
    $HTML .= '<a id="add_event" class="btn btn-primary" href="department_settings.php?mode=add_department_event"><i class="bi bi-person-fill-add"></i> Hinzufügen</a> ';
    $HTML .= '</div>';

    // Initialize Table Active Stuff
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
    <th data-field="name" data-sortable="true">Name</th>
    <th data-field="begin" data-sortable="true">Begin</th>
    <th data-field="ende" data-sortable="true">Ende</th>
    <th data-field="details" data-sortable="false">Details</th>
    <th data-field="creator" data-sortable="true">Angelegt von</th>
    <th>Optionen</th>
    </tr>
    </thead>';

    // Fill table body
    $HTML .= '<tbody>';
    $counter = 1;
    foreach ($ActiveEvents as $activeEvent) {
        // Build edit/delete Buttons
        $Options = '<a href="department_settings.php?mode=edit_department_event&event_id='.$activeEvent['id'].'"><i class="bi bi-pencil-fill"></i></a> <a href="department_settings.php?mode=delete_department_event&event_id='.$activeEvent['id'].'"><i class="bi bi-trash3-fill"></i></a>';
        $Anleger = get_user_infos_by_id_from_list($activeEvent['create_user'], $userList);
        $AnlegerInfos = $Anleger['nachname'].', '.$Anleger['vorname'];

        if($counter==1){
            $HTML .= '<td id="td-id-1" class="td-class-1" data-title="bootstrap table">'.$activeEvent['name'].'</td>';
            $HTML .= '<td>'.date("d.m.Y", strtotime($activeEvent['begin'])).'</td>';
            $HTML .= '<td>'.date("d.m.Y", strtotime($activeEvent['end'])).'</td>';
            $HTML .= '<td>'.$activeEvent['details'].'</td>';
            $HTML .= '<td>'.$AnlegerInfos.'</td>';
            $HTML .= '<td> '.$Options.'</td>';
        } else {
            $HTML .= '<td id="td-id-'.$counter.'" class="td-class-'.$counter.'"">'.$activeEvent['name'].'</td>';
            $HTML .= '<td>'.date("d.m.Y", strtotime($activeEvent['begin'])).'</td>';
            $HTML .= '<td>'.date("d.m.Y", strtotime($activeEvent['end'])).'</td>';
            $HTML .= '<td>'.$activeEvent['details'].'</td>';
            $HTML .= '<td>'.$AnlegerInfos.'</td>';
            $HTML .= '<td> '.$Options.'</td>';
        }

        // close row and count up
        $HTML .= "</tr>";
        $counter++;
    }

    $HTML .= '</tbody>';
    $HTML .= '</table>';

    // Build divider
    $HTML .= "<h4>Vergangene Veranstaltungen dieses Jahr</h4>";

    // Initialize Table Past Stuff
    $HTML .= '<table data-toggle="table" 
    data-locale="de-DE"
    data-show-columns="true" 
    data-multiple-select-row="true"
    data-click-to-select="true"
    data-pagination="true">';

    // Setup Table Head
    $HTML .= '<thead>
    <tr class="tr-class-1">
    <th data-field="name" data-sortable="true">Name</th>
    <th data-field="begin" data-sortable="true">Begin</th>
    <th data-field="ende" data-sortable="true">Ende</th>
    <th data-field="details" data-sortable="false">Details</th>
    <th data-field="creator" data-sortable="true">Angelegt von</th>
    <th>Optionen</th>
    </tr>
    </thead>';

    // Fill table body
    $HTML .= '<tbody>';
    $counter = 1;
    foreach ($PassedEventsThisYear as $activeEvent) {
        // Build edit/delete Buttons
        $Options = '<a href="department_settings.php?mode=edit_department_event&event_id='.$activeEvent['id'].'"><i class="bi bi-pencil-fill"></i></a> <a href="department_settings.php?mode=delete_department_event&event_id='.$activeEvent['id'].'"><i class="bi bi-trash3-fill"></i></a>';
        $Anleger = get_user_infos_by_id_from_list($activeEvent['create_user'], $userList);
        $AnlegerInfos = $Anleger['nachname'].', '.$Anleger['vorname'];

        if($counter==1){
            $HTML .= '<td id="td-id-1" class="td-class-1" data-title="bootstrap table">'.$activeEvent['name'].'</td>';
            $HTML .= '<td>'.date("d.m.Y", strtotime($activeEvent['begin'])).'</td>';
            $HTML .= '<td>'.date("d.m.Y", strtotime($activeEvent['end'])).'</td>';
            $HTML .= '<td>'.$activeEvent['details'].'</td>';
            $HTML .= '<td>'.$AnlegerInfos.'</td>';
            $HTML .= '<td> '.$Options.'</td>';
        } else {
            $HTML .= '<td id="td-id-'.$counter.'" class="td-class-'.$counter.'"">'.$activeEvent['name'].'</td>';
            $HTML .= '<td>'.date("d.m.Y", strtotime($activeEvent['begin'])).'</td>';
            $HTML .= '<td>'.date("d.m.Y", strtotime($activeEvent['end'])).'</td>';
            $HTML .= '<td>'.$activeEvent['details'].'</td>';
            $HTML .= '<td>'.$AnlegerInfos.'</td>';
            $HTML .= '<td> '.$Options.'</td>';
        }

        // close row and count up
        $HTML .= "</tr>";
        $counter++;
    }

    $HTML .= '</tbody>';
    $HTML .= '</table>';

    return $HTML;

}

function calculate_department_events_table_cell($ThisDay, $AllDepartmentEvents, $tdthMode="td", $Colspan=1){

    $FoundRelevantEventsOnSelectedDay = [];
    foreach ($AllDepartmentEvents as $Event){
        if(($ThisDay >= strtotime($Event['begin'])) && ($ThisDay <= strtotime($Event['end']))){
            $FoundRelevantEventsOnSelectedDay[] = $Event;
        }
    }

    //Now Build Content
    $TooltipContent = "";
    $Counter = 0;
    foreach ($FoundRelevantEventsOnSelectedDay as $RelevantItems) {
        $Numerator = $Counter + 1;
        $ItemInfos = "(".$Numerator.") ".htmlspecialchars($RelevantItems['name']).": ".htmlspecialchars($RelevantItems['details']);
        if($Counter > 0){
            $TooltipContent .= '  ---  ';
        }
        $TooltipContent .= $ItemInfos;
        $Counter++;
    }

    if($Counter > 0){
        $ToolTip = '<a href="#" data-bs-toggle="tooltip" data-bs-html="true" title="'.$TooltipContent.'"><i class="bi bi-calendar3 bi-xs"></i></a>';
    } else {
        $ToolTip = "";
    }

    if($Colspan==1){
        return "<".$tdthMode.">".$ToolTip."</".$tdthMode.">";
    } elseif ($Colspan==2){
        return "<".$tdthMode." colspan='2'>".$ToolTip."</".$tdthMode.">";
    }
}

function add_department_event_management($mysqli){

    $KnownEvents = get_sorted_list_of_all_department_events($mysqli);

    #Initialize Placeholders & DAU handling
    $DAUcount = 0;
    $ShowReturn = false;
    $ReturnMessage = '';
    $namePlaceholder = $beginPlaceholder = $endPlaceholder = $detailPlaceholder = $beginErr = $endErr = $nameErr = "";

    # Populate Placeholders if action is clicked
    if(isset($_POST['add_department_event_action_action'])){
        $namePlaceholder = htmlspecialchars($_POST['name']);
        $beginPlaceholder = $_POST['begin'];
        $endPlaceholder = $_POST['end'];
        $detailPlaceholder = htmlspecialchars($_POST['details']);

        # Do some checks
        if(strtotime($endPlaceholder)<strtotime($beginPlaceholder)){
            $DAUcount++;
            $beginErr = $endErr = "Das Enddatum darf nicht vor dem Beginn der Veranstaltung liegen!";
        }

        if(empty($namePlaceholder)){
            $DAUcount++;
            $nameErr = "Bitte geben Sie einen Veranstaltungstitel an!<br>";
        }

        $Catches = 0;
        foreach ($KnownEvents as $KnownEvent){
            $Catch=true;
            //Check non-overlap cases
            //Case 1: Begin and End of Item are smaller than end of new assignment
            if((strtotime($KnownEvent['begin'])<strtotime($beginPlaceholder)) && (strtotime($KnownEvent['end'])<strtotime($beginPlaceholder))){
                $Catch=false;
            }
            //Case 2: Begin and End of Item are bigger than end of new assignment
            if((strtotime($KnownEvent['begin'])>$endPlaceholder) && (strtotime($KnownEvent['end'])>$endPlaceholder)){
                $Catch=false;
            }

            if($Catch){
                if($KnownEvent['name']==$namePlaceholder){
                    $Catches++;
                }
            }
        }

        if($Catches>0){
            $beginErr = "Im ausgewähltem Zeitraum liegt bereits eine Veranstaltung mit dem identischen Titel vor!";
            $DAUcount++;
        }

        if($DAUcount==0){
            # Add entry to db
            $ReturnVals = add_department_event($mysqli, $namePlaceholder, $detailPlaceholder, $beginPlaceholder, $endPlaceholder);
            if($ReturnVals['success']){
                $ShowReturn = true;
                $ReturnMessage = "Veranstaltung erfolgreich angelegt!";
            } else {
                $ShowReturn = false;
                $ReturnMessage = $ReturnVals['err'];
            }
        }
    }

    if($ShowReturn){
        $FormHTML = form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'abort_user_sondereinteilung_action', 'btn-primary');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');

        return card_builder('Veranstaltung '.$namePlaceholder.' erfolgreich angelegt!',$ReturnMessage, $FORM);
    }else{
        #Build the form
        $FormHTML = form_group_input_text('Veranstaltungstitel', 'name', $namePlaceholder, true, $nameErr, false, 'Unterabteilung wählen');
        $FormHTML .= form_group_input_text('Details', 'details', $detailPlaceholder, false, '', false, 'Unterabteilung wählen');
        $FormHTML .= form_group_input_date('Beginn', 'begin', $beginPlaceholder, true, $beginErr, false);
        $FormHTML .= form_group_input_date('Ende', 'end', $endPlaceholder, true, $endErr, false);
        $FormHTML .= form_group_continue_return_buttons(true, 'Anlegen', 'add_department_event_action_action', 'btn-primary', true, 'Abbrechen', 'abort_department_event_action', 'btn-danger');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');

        return card_builder('Neue Veranstaltung anlegen','', $FORM);
    }

}

function edit_department_event_management($mysqli, $selectedID=0){

    $KnownEvents = get_sorted_list_of_all_department_events($mysqli);
    $EventInfos = get_department_event_infos_by_id_from_list($KnownEvents, $selectedID);

    #Initialize Placeholders & DAU handling
    $DAUcount = 0;
    $ShowReturn = false;
    $ReturnMessage = '';
    $namePlaceholder = htmlspecialchars($EventInfos['name']);
    $beginPlaceholder = $EventInfos['begin'];
    $endPlaceholder = $EventInfos['end'];
    $detailPlaceholder = htmlspecialchars($EventInfos['details']);
    $beginErr = $endErr = $nameErr = "";

    # Populate Placeholders if action is clicked
    if(isset($_POST['edit_department_event_action_action'])){

        $namePlaceholder = htmlspecialchars($_POST['name']);
        $beginPlaceholder = $_POST['begin'];
        $endPlaceholder = $_POST['end'];
        $detailPlaceholder = htmlspecialchars($_POST['details']);

        # Do some checks
        if(strtotime($endPlaceholder)<strtotime($beginPlaceholder)){
            $DAUcount++;
            $beginErr = $endErr = "Das Enddatum darf nicht vor dem Beginn der Veranstaltung liegen!";
        }

        if(empty($namePlaceholder)){
            $DAUcount++;
            $nameErr = "Bitte geben Sie einen Veranstaltungstitel an!<br>";
        }

        $Catches = 0;
        foreach ($KnownEvents as $KnownEvent){
            if($KnownEvent['id']!=$selectedID){
                $Catch=true;
                //Check non-overlap cases
                //Case 1: Begin and End of Item are smaller than end of new assignment
                if((strtotime($KnownEvent['begin'])<strtotime($beginPlaceholder)) && (strtotime($KnownEvent['end'])<strtotime($beginPlaceholder))){
                    $Catch=false;
                }
                //Case 2: Begin and End of Item are bigger than end of new assignment
                if((strtotime($KnownEvent['begin'])>$endPlaceholder) && (strtotime($KnownEvent['end'])>$endPlaceholder)){
                    $Catch=false;
                }

                if($Catch){
                    if($KnownEvent['name']==$namePlaceholder){
                        $Catches++;
                    }
                }
            }
        }

        if($Catches>0){
            $beginErr = "Im ausgewähltem Zeitraum liegt bereits eine Veranstaltung mit dem identischen Titel vor!";
            $DAUcount++;
        }

        if($DAUcount==0){
            # edit entry in db
            $ReturnVals = edit_department_event($mysqli, $selectedID, $namePlaceholder, $detailPlaceholder, $beginPlaceholder, $endPlaceholder);
            if($ReturnVals['success']){
                $ShowReturn = true;
                $ReturnMessage = "Veranstaltung ".$namePlaceholder." erfolgreich bearbeitet!";
            } else {
                $ShowReturn = false;
                $ReturnMessage = $ReturnVals['err'];
            }
        }
    }

    if($ShowReturn){
        $FormHTML = form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'abort_user_sondereinteilung_action', 'btn-primary');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');

        return card_builder('Veranstaltung '.$namePlaceholder.' erfolgreich bearbeitet!',$ReturnMessage, $FORM);
    }else{
        #Build the form
        $FormHTML = form_hidden_input_generator('edit_department_event_id', $selectedID);
        $FormHTML .= form_group_input_text('Veranstaltungstitel', 'name', $namePlaceholder, true, $nameErr, false, 'Unterabteilung wählen');
        $FormHTML .= form_group_input_text('Details', 'details', $detailPlaceholder, false, '', false, 'Unterabteilung wählen');
        $FormHTML .= form_group_input_date('Beginn', 'begin', $beginPlaceholder, true, $beginErr, false);
        $FormHTML .= form_group_input_date('Ende', 'end', $endPlaceholder, true, $endErr, false);
        $FormHTML .= form_group_continue_return_buttons(true, 'Bearbeiten', 'edit_department_event_action_action', 'btn-primary', true, 'Abbrechen', 'abort_department_event_action', 'btn-danger');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');

        return card_builder('Veranstaltung '.$namePlaceholder.' bearbeiten','', $FORM);
    }
}

function delete_department_event_management($mysqli, $selectedID=0){

    $KnownEvents = get_sorted_list_of_all_department_events($mysqli);
    $EventInfos = get_department_event_infos_by_id_from_list($KnownEvents, $selectedID);

    #Initialize Placeholders & DAU handling
    $DAUcount = 0;
    $ShowReturn = false;
    $ReturnMessage = '';
    $namePlaceholder = htmlspecialchars($EventInfos['name']);
    $beginPlaceholder = $EventInfos['begin'];
    $endPlaceholder = $EventInfos['end'];
    $detailPlaceholder = htmlspecialchars($EventInfos['details']);
    $deleteCommentPlaceholder = $beginErr = $endErr = $nameErr = "";

    # Populate Placeholders if action is clicked
    if(isset($_POST['delete_department_event_action_action'])){

        $deleteCommentPlaceholder = htmlspecialchars($_POST['delete_comment']);

        if($DAUcount==0){
            # edit entry in db
            $ReturnVals = delete_department_event($mysqli, $selectedID, $deleteCommentPlaceholder);
            if($ReturnVals['success']){
                $ShowReturn = true;
                $ReturnMessage = "Veranstaltung ".$namePlaceholder." erfolgreich gelöscht!";
            } else {
                $ShowReturn = false;
                $ReturnMessage = $ReturnVals['err'];
            }
        }
    }

    if($ShowReturn){
        $FormHTML = form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'abort_user_sondereinteilung_action', 'btn-primary');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');

        return card_builder('Veranstaltung '.$namePlaceholder.' erfolgreich gelöscht!',$ReturnMessage, $FORM);
    }else{
        #Build the form
        $FormHTML = form_hidden_input_generator('delete_department_event_id', $selectedID);
        $FormHTML .= form_group_input_text('Veranstaltungstitel', 'name', $namePlaceholder, true, $nameErr, true, 'Unterabteilung wählen');
        $FormHTML .= form_group_input_text('Details', 'details', $detailPlaceholder, false, '', true, 'Unterabteilung wählen');
        $FormHTML .= form_group_input_date('Beginn', 'begin', $beginPlaceholder, true, $beginErr, true);
        $FormHTML .= form_group_input_date('Ende', 'end', $endPlaceholder, true, $endErr, true);
        $FormHTML .= form_group_input_text('Kommentar zum Löschvorgang (optional)', 'delete_comment', $deleteCommentPlaceholder, false, '', false, 'Unterabteilung wählen');
        $FormHTML .= form_group_continue_return_buttons(true, 'Löschen', 'delete_department_event_action_action', 'btn-primary', true, 'Abbrechen', 'abort_department_event_action', 'btn-danger');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');

        return card_builder('Veranstaltung '.$namePlaceholder.' löschen','Möchten Sie die Veranstaltung '.$namePlaceholder.' wirklich löschen?', $FORM);
    }
}

function add_department_feiertag_management(){

    if(!empty(LISTEFEIERTAGE)) {
        $Tage = explode(',', LISTEFEIERTAGE);
    } else {
        $Tage = array();
    }

    #Initialize Placeholders & DAU handling
    $DAUcount = 0;
    $ShowReturn = false;
    $ReturnMessage = '';
    $dayPlaceholder = $dayErr = "";

    # Populate Placeholders if action is clicked
    if(isset($_POST['add_department_feiertag_action_action'])){
        $dayPlaceholder = htmlspecialchars($_POST['date']);

        # Do some checks
        if(empty($dayPlaceholder)){
            $DAUcount++;
            $dayErr .= "Bitte ein Datum auswählen!";
        } else {
            if(in_array($dayPlaceholder, $Tage)){
                $DAUcount++;
                $dayErr .= "Es ist bereits ein Feiertag an diesem Datum erfasst!";
            }

            if(strtotime($dayPlaceholder)<time()){
                $DAUcount++;
                $dayErr = "Es können keine Feiertage in der Vergangenheit angelegt werden!";
            }
        }

        if($DAUcount==0){
            # Add entry to xml after sorting
            $Tage[] = $dayPlaceholder;
            usort($Tage, "compareByTimeStamp");
            update_xml_einstellung('defined_feiertage_dates', implode(',', $Tage));

            $ShowReturn = true;
            $ReturnMessage = "Feiertag erfolgreich angelegt!";
        }
    }

    if($ShowReturn){
        $FormHTML = form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'abort_user_sondereinteilung_action', 'btn-primary');
        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');
        return card_builder('Feiertag angelegen',$ReturnMessage, $FORM);
    }else{
        #Build the form
        $FormHTML = form_group_input_date('Datum', 'date', $dayPlaceholder, true, $dayErr, false);
        $FormHTML .= form_group_continue_return_buttons(true, 'Anlegen', 'add_department_feiertag_action_action', 'btn-primary', true, 'Abbrechen', 'abort_department_event_action', 'btn-danger');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');

        return card_builder('Feiertag angelegen','', $FORM);
    }

}

function add_department_dw_grenztag_management(){

    if(!empty(LISTEGESONDERTEREINGABEFRISTENBDPLAN)) {
        $Tage = explode(',', LISTEGESONDERTEREINGABEFRISTENBDPLAN);
    } else {
        $Tage = array();
    }

    #Initialize Placeholders & DAU handling
    $DAUcount = 0;
    $ShowReturn = false;
    $ReturnMessage = '';
    $InFourMonths = strtotime('+4 months', strtotime(date('Y-m-01')));
    $monthPlaceholder = date('m', $InFourMonths);
    $yearPlaceholder = date('Y', $InFourMonths);
    $dayPlaceholder = date('Y-m-d', strtotime('-1 day -3 months', $InFourMonths));
    $SearchMonth = $dayErr = "";

    # Populate Placeholders if action is clicked
    if(isset($_POST['add_department_dw_grenztag_action_action'])){

        $yearPlaceholder = htmlspecialchars($_POST['year']);
        $monthPlaceholder = htmlspecialchars($_POST['month']);
        $dayPlaceholder = htmlspecialchars($_POST['date']);

        # Do some checks
        if(empty($dayPlaceholder)){
            $DAUcount++;
            $dayErr .= "Bitte ein Grenzdatum auswählen!<br>";
        } elseif (empty($yearPlaceholder)){
            $DAUcount++;
            $dayErr .= "Bitte ein Jahr auswählen!<br>";
        } elseif (empty($monthPlaceholder)){
            $DAUcount++;
            $dayErr .= "Bitte einen Monat auswählen!<br>";
        } else {

            //Only allow one entry per month
            $SearchMonth = $yearPlaceholder."-".$monthPlaceholder;
            foreach($Tage as $tag){

                $tagExploded = explode(':', $tag);
                if($tagExploded[0]==$SearchMonth){
                    $DAUcount++;
                    $dayErr .= "Es ist bereits ein Dienstwunsch-Grenztag für diesen Monat erfasst!";
                }
            }

        }

        if($DAUcount==0){
            # Add entry to xml after sorting
            $Tage[] = $SearchMonth.":".$dayPlaceholder;

            // Sort the Tage Array
            //Create temporary multidim Array for sorting (i know this can be done better - quickfix here)
            $tempArray = array();
            foreach($Tage as $tag){
                $tagExploded = explode(':', $tag);
                $item['time'] = strtotime($tagExploded[0].'-01');
                $item['monat'] = $tagExploded[0];
                $item['grenze'] = $tagExploded[1];
                $tempArray[] = $item;
            }
            // sort it
            $columns = array_column($tempArray, 'time');
            array_multisort($columns, SORT_DESC, $tempArray);

            // Rebuild XML String
            $TageNew = array();
            foreach ($tempArray as $value){
                $TageNew[] = $value['monat'].':'.$value['grenze'];
            }

            update_xml_einstellung('defined_last_dienstwunsch_dates', implode(',', $TageNew));

            $ShowReturn = true;
            $ReturnMessage = "Dienstwunsch-Grenztag erfolgreich angelegt!";
        }
    }

    if($ShowReturn){
        $FormHTML = form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'abort_user_sondereinteilung_action', 'btn-primary');
        // Gap it
        $FORM = form_builder($FormHTML, 'self', 'POST');
        return card_builder('Dienstwunsch-Grenztag angelegen',$ReturnMessage, $FORM);
    }else{
        #Build the form
        $FormHTML = form_dropdown_months('month', $monthPlaceholder).form_dropdown_years('year', $yearPlaceholder);
        $FormHTML .= form_group_input_date('Grenzdatum', 'date', $dayPlaceholder, true, $dayErr, false);
        $FormHTML .= form_group_continue_return_buttons(true, 'Anlegen', 'add_department_dw_grenztag_action_action', 'btn-primary', true, 'Abbrechen', 'abort_department_event_action', 'btn-danger');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');

        return card_builder('Dienstwunsch-Grenztag angelegen','', $FORM);
    }

}

function delete_department_feiertag_management(){

    if(!empty(LISTEFEIERTAGE)) {
        $Tage = explode(',', LISTEFEIERTAGE);
    } else {
        $Tage = array();
    }

    #Initialize Placeholders & DAU handling
    $DAUcount = 0;
    $ShowReturn = false;
    $ReturnMessage = '';
    $dayPlaceholder = $dayErr = "";

    if(empty($_POST['feiertage'])){
        $ShowReturn = false;
        $dayErr = 'Keinen Feiertag ausgewählt! Bitte erneut versuchen!';
    }

    # Populate Placeholders if action is clicked
    if(isset($_POST['delete_department_feiertag_action_action'])){
        $dayPlaceholder = htmlspecialchars($_POST['feiertage']);

        # Do some checks
        if(empty($dayPlaceholder)){
            $DAUcount++;
            $dayErr .= "Bitte ein zu löschendes Datum auswählen!";
        } else {
            if(!in_array($dayPlaceholder, $Tage)){
                $DAUcount++;
                $dayErr .= "Das ausgewählte Datum scheint nicht mehr in der Liste zu sein!";
            }
        }

        if($DAUcount==0){
            # Update entry to xml after removing from array
            if (($key = array_search($dayPlaceholder, $Tage)) !== false) {
                unset($Tage[$key]);
            }
            //Restore array index
            $Tage = array_values($Tage);

            update_xml_einstellung('defined_feiertage_dates', implode(',', $Tage));

            $ShowReturn = true;
            $ReturnMessage = "Feiertag erfolgreich gelöscht! Bitte beachten Sie, dass etwaige (Bereitschafts-)dienstplanungen nun ggf. überarbeitet werden müssen, da sich das erforderliche Mitarbeiterkontingent geändert haben könnte!";
        }
    }

    if($ShowReturn){
        $FormHTML = form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'abort_user_sondereinteilung_action', 'btn-primary');
        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');
        return card_builder('Feiertag löschen',$ReturnMessage, $FORM);
    }else{
        #Build the form
        if(!empty($dayErr)){
            $FormHTML = alert_builder($dayErr);
            $FormHTML .= form_hidden_input_generator('feiertage', $_POST['feiertage']);
            $FormHTML .= form_group_continue_return_buttons(true, 'Löschen', 'delete_department_feiertag_action_action', 'btn btn-danger', true, 'Abbrechen', 'abort_department_event_action', 'btn btn-primary');
        } else {
            $FormHTML = form_hidden_input_generator('feiertage', $_POST['feiertage']);
            $FormHTML .= form_group_continue_return_buttons(true, 'Löschen', 'delete_department_feiertag_action_action', 'btn btn-danger', true, 'Abbrechen', 'abort_department_event_action', 'btn btn-primary');
        }

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');
        if(!empty($_POST['feiertage'])){
            $Promt = "Feiertag am ".date('d.m.Y', strtotime($_POST['feiertage']))." löschen";
        } else {
            $Promt = "";
        }

        return card_builder('Feiertag löschen',$Promt, $FORM);
    }

}

function delete_department_dw_grenztag_management(){

    if(!empty(LISTEGESONDERTEREINGABEFRISTENBDPLAN)) {
        $Tage = explode(',', LISTEGESONDERTEREINGABEFRISTENBDPLAN);
    } else {
        $Tage = array();
    }

    #Initialize Placeholders & DAU handling
    $DAUcount = 0;
    $ShowReturn = false;
    $ReturnMessage = '';
    $dayPlaceholder = $dayErr = "";

    if(empty($_POST['dw_grenztage'])){
        $ShowReturn = false;
        $dayErr = 'Keinen Dienstwunsch-Grenztag ausgewählt! Bitte erneut versuchen!';
    }

    # Populate Placeholders if action is clicked
    if(isset($_POST['delete_department_dw_grenztag_action_action'])){
        $dayPlaceholder = htmlspecialchars($_POST['dw_grenztage']);

        # Do some checks
        if(empty($dayPlaceholder)){
            $DAUcount++;
            $dayErr .= "Bitte ein zu löschendes Datum auswählen!";
        } else {
            if(!in_array($dayPlaceholder, $Tage)){
                $DAUcount++;
                $dayErr .= "Das ausgewählte Grenzdatum scheint nicht mehr in der Liste zu sein!";
            }
        }

        if($DAUcount==0){
            # Update entry to xml after removing from array
            if (($key = array_search($dayPlaceholder, $Tage)) !== false) {
                unset($Tage[$key]);
            }

            //Restore array index
            $Tage = array_values($Tage);

            update_xml_einstellung('defined_last_dienstwunsch_dates', implode(',', $Tage));

            $ShowReturn = true;
            $ReturnMessage = "Dienstwunsch-Grenztag erfolgreich gelöscht!";
        }
    }

    if($ShowReturn){
        $FormHTML = form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'abort_user_sondereinteilung_action', 'btn-primary');
        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');
        return card_builder('Dienstwunsch-Grenztag löschen',$ReturnMessage, $FORM);
    }else{
        #Build the form
        if(!empty($dayErr)){
            $FormHTML = alert_builder($dayErr);
            $FormHTML .= form_hidden_input_generator('dw_grenztage', $_POST['dw_grenztage']);
            $FormHTML .= form_group_continue_return_buttons(true, 'Löschen', 'delete_department_dw_grenztag_action_action', 'btn btn-danger', true, 'Abbrechen', 'abort_department_event_action', 'btn btn-primary');
        } else {
            $FormHTML = form_hidden_input_generator('dw_grenztage', $_POST['dw_grenztage']);
            $FormHTML .= form_group_continue_return_buttons(true, 'Löschen', 'delete_department_dw_grenztag_action_action', 'btn btn-danger', true, 'Abbrechen', 'abort_department_event_action', 'btn btn-primary');
        }

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');

        if(!empty($_POST['dw_grenztage'])){
            $Deconstruct = explode(':', $_POST['dw_grenztage']);
            $Promt = "Dienstwunsch-Grenztag für ".$Deconstruct[0]." löschen";
        } else {
            $Promt = "";
        }

        return card_builder('Dienstwunsch-Grenztag löschen',$Promt, $FORM);
    }

}