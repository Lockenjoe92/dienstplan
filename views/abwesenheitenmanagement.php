<?php

function table_abwesenheiten_management($mysqli, $Nutzerrollen,$UE=1){

    // deal with stupid "" and '' problems
    $bla = '"{"key": "value"}"';
    $AbwesenheitenAll = get_sorted_list_of_all_abwesenheiten($mysqli);
    $AllAssignments = get_all_user_depmnt_assignments($mysqli);
    $Users = get_sorted_list_of_all_users($mysqli);

    $ShowGenehmigt = false;
    if(isset($_GET['show_genehmigt'])){
        if($_GET['show_genehmigt']=='true'){
            $ShowGenehmigt = true;
        }
    }

    // Presort Abwesenheiten so we only have a list with users who actually ever work at said UE
    $Abwesenheiten = [];
    foreach ($AbwesenheitenAll as $Abwesenheit){

        if($Abwesenheit['status_bearbeitung']=='Genehmigt'){
            $AddApplication = $ShowGenehmigt;
        } else {
            $AddApplication = true;
        }

        foreach ($Users as $user){
            if($Abwesenheit['user']==$user['id']){
                if($user['default_abteilung']==$UE){
                    if($AddApplication){
                        $Abwesenheiten[]=$Abwesenheit;
                    }
                } else {
                    foreach ($AllAssignments as $assignment){
                        if($assignment['user']==$Abwesenheit['user']){
                            if($assignment['department']==$UE){
                                # Only show people who are assigned this year
                                $FirstDayThisYear = date('Y').'-m-d';
                                if($assignment['end']>$FirstDayThisYear){
                                    if($AddApplication){
                                        $Abwesenheiten[]=$Abwesenheit;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    // Setup Toolbar
    $HTML = '<div id="toolbar">';
    $HTML .= '<a id="add_user" class="btn btn-primary" href="abwesenheiten_management.php?org_ue='.$UE.'&mode=add_abwesenheit"><i class="bi bi-person-fill-add"></i> Hinzufügen</a> ';
    if($ShowGenehmigt){
        $HTML .= '<a id="add_user" class="btn btn-light" href="abwesenheiten_management.php?org_ue='.$UE.'&show_genehmigt=false"><i class="bi bi-eye-slash-fill"></i> genehmigte ausblenden</a>';
    } else {
        $HTML .= '<a id="add_user" class="btn btn-primary" href="abwesenheiten_management.php?org_ue='.$UE.'&show_genehmigt=true"><i class="bi bi-eye-fill"></i> genehmigte einblenden</a>';
    }
    $HTML .= '</div>';

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
                    <th data-field="status" data-sortable="true">Status</th>
                    <th data-field="user" data-sortable="true">AntragstellerIn</th>
                    <th data-field="beginn" data-sortable="true">Beginn</th>
                    <th data-field="ende" data-sortable="true">Ende</th>
                    <th data-field="Antragsart" data-sortable="true">Antragsart</th>
                    <th data-field="eintrag-datum" data-sortable="true">Beantragt am</th>
                    <th data-field="urgency" data-sortable="true">Dringlichkeit</th>
                    <th>Optionen</th>
                </tr>
              </thead>';

    // Fill table body
    $HTML .= '<tbody>';
    $counter = 1;
    foreach ($Abwesenheiten as $Abwesenheit){

        // Build rows
        if($counter==1){
            $HTML .= '<tr id="tr-id-1" class="tr-class-1" data-title="bootstrap table" data-object='.$bla.'>';
        } else {
            $HTML .= '<tr id="tr-id-'.$counter.'" class="tr-class-'.$counter.'">';
        }

        $User = [];
        foreach ($Users as $user){
            if($user['id']==$Abwesenheit['user']){
                $User = $user;
            }
        }

        // Build edit/delete Buttons
        $NutzerrollenArray = explode(',',$Nutzerrollen);
        if(in_array('admin', $NutzerrollenArray)){
            $Options = '<a href="abwesenheiten_management.php?org_ue='.$UE.'&mode=decline_abwesenheit&abwesenheit_id='.$Abwesenheit['id'].'"><i class="bi bi-x-circle-fill"></i></a> <a href="abwesenheiten_management.php?org_ue='.$UE.'&mode=accept_abwesenheit&abwesenheit_id='.$Abwesenheit['id'].'"><i class="bi bi-check-circle-fill"></i></a> <a href="abwesenheiten_management.php?org_ue='.$UE.'&mode=edit_abwesenheit&abwesenheit_id='.$Abwesenheit['id'].'"><i class="bi bi-pencil-fill"></i></a> <a href="abwesenheiten_management.php?org_ue='.$UE.'&mode=delete_abwesenheit&abwesenheit_id='.$Abwesenheit['id'].'"><i class="bi bi-trash3-fill"></i></a> ';
        } else {
            if(user_can_edit_abwesenheitsantrag($mysqli, $Nutzerrollen, $Abwesenheit)){
                $Options = '<a href="abwesenheiten_management.php?org_ue='.$UE.'&mode=decline_abwesenheit&abwesenheit_id='.$Abwesenheit['id'].'"><i class="bi bi-x-circle-fill"></i></a> <a href="abwesenheiten_management.php?org_ue='.$UE.'&mode=accept_abwesenheit&abwesenheit_id='.$Abwesenheit['id'].'"><i class="bi bi-check-circle-fill"></i></a> <a href="abwesenheiten_management.php?org_ue='.$UE.'&mode=edit_abwesenheit&abwesenheit_id='.$Abwesenheit['id'].'"><i class="bi bi-pencil-fill"></i></a> <a href="abwesenheiten_management.php?org_ue='.$UE.'&mode=delete_abwesenheit&abwesenheit_id='.$Abwesenheit['id'].'"><i class="bi bi-trash3-fill"></i></a> ';
            }else{
                $Options = '';
            }
        }

        // Optionally show comments
        if($Abwesenheit['create_comment']!=''){
            $Comment = '<a href="#" data-bs-toggle="tooltip" data-bs-html="true" title="'.$Abwesenheit['create_comment'].'"><i class="bi bi-megaphone-fill"></i></a>';
        } else {
            $Comment = '';
        }

        if($counter==1){
            $HTML .= '<td id="td-id-1" class="td-class-1" data-title="bootstrap table">'.$Abwesenheit['status_bearbeitung'].'</td>';
            $HTML .= '<td>'.$User['nachname'].', '.$User['vorname'].'</td>';
            $HTML .= '<td>'.$Abwesenheit['begin'].'</td>';
            $HTML .= '<td>'.$Abwesenheit['end'].'</td>';
            $HTML .= '<td>'.$Abwesenheit['type'].'</td>';
            $HTML .= '<td>'.date('Y-m-d',strtotime($Abwesenheit['create_date'])).'</td>';
            $HTML .= '<td>'.$Abwesenheit['urgency'].'</td>';
            $HTML .= '<td>'.$Options.$Comment.'</td>';
        } else {
            $HTML .= '<td id="td-id-'.$counter.'" class="td-class-'.$counter.'"">'.$Abwesenheit['status_bearbeitung'].'</td>';
            $HTML .= '<td>'.$User['nachname'].', '.$User['vorname'].'</td>';
            $HTML .= '<td>'.$Abwesenheit['begin'].'</td>';
            $HTML .= '<td>'.$Abwesenheit['end'].'</td>';
            $HTML .= '<td>'.$Abwesenheit['type'].'</td>';
            $HTML .= '<td>'.date('Y-m-d',strtotime($Abwesenheit['create_date'])).'</td>';
            $HTML .= '<td>'.$Abwesenheit['urgency'].'</td>';
            $HTML .= '<td>'.$Options.$Comment.'</td>';
        }

        // close row and count up
        $HTML .= "</tr>";
        $counter++;
    }
    $HTML .= '</tbody>';
    $HTML .= '</table>';

    return $HTML;
}

function table_abwesenheiten_user($mysqli, $Nutzerrollen){

    // deal with stupid "" and '' problems
    $bla = '"{"key": "value"}"';
    $Abwesenheiten = get_sorted_list_of_all_abwesenheiten($mysqli);
    $CurrentUser = get_current_user_id();

    // Setup Toolbar
    $HTML = '<div id="toolbar">
                <a id="add_user" class="btn btn-primary" href="abwesenheiten_user.php?mode=add_abwesenheit">
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
                    <th data-field="status" data-sortable="true">Status</th>
                    <th data-field="beginn" data-sortable="true">Beginn</th>
                    <th data-field="ende" data-sortable="true">Ende</th>
                    <th data-field="Antragsart" data-sortable="true">Antragsart</th>
                    <th data-field="eintrag-datum" data-sortable="true">Beantragt am</th>
                    <th data-field="urgency" data-sortable="true">Dringlichkeit</th>
                    <th>Optionen</th>
                </tr>
              </thead>';

    // Fill table body
    $HTML .= '<tbody>';
    $counter = 1;
    foreach ($Abwesenheiten as $Abwesenheit){

        if($Abwesenheit['user'] == $CurrentUser){

            //Coloring
            $ColorCommand="";
            foreach(explode(',', ABWESENHEITENBEARBEITUNGSSTATI) as $Status){
                $DeconstructStatus = explode(':', $Status);
                if($Abwesenheit['status_bearbeitung']==$DeconstructStatus[0]){
                    $ColorCommand = $DeconstructStatus[1];
                }
            }

            // Build rows
            if($counter==1){
                $HTML .= '<tr id="tr-id-1" class="tr-class-1 '.$ColorCommand.'" data-title="bootstrap table" data-object='.$bla.'>';
            } else {
                $HTML .= '<tr id="tr-id-'.$counter.'" class="tr-class-'.$counter.' '.$ColorCommand.'">';
            }

            // Build edit/delete Buttons
            if(user_can_edit_abwesenheitsantrag($mysqli, $Nutzerrollen, $Abwesenheit)){
                $Options = '<a href="abwesenheiten_user.php?mode=edit_abwesenheit&abwesenheit_id='.$Abwesenheit['id'].'"><i class="bi bi-pencil-fill"></i></a> <a href="abwesenheiten_user.php?mode=delete_abwesenheit&abwesenheit_id='.$Abwesenheit['id'].'"><i class="bi bi-trash3-fill"></i></a> ';
            }else{
                $Options = '';
            }

            // Optionally show comments
            if($Abwesenheit['create_comment']!=''){
                $Comment = '<i class="bi bi-megaphone-fill"> ';
            } else {
                $Comment = '';
            }

            if($counter==1){
                $HTML .= '<td id="td-id-1" class="td-class-1" data-title="bootstrap table">'.$Abwesenheit['status_bearbeitung'].'</td>';
                $HTML .= '<td>'.$Abwesenheit['begin'].'</td>';
                $HTML .= '<td>'.$Abwesenheit['end'].'</td>';
                $HTML .= '<td>'.$Abwesenheit['type'].'</td>';
                $HTML .= '<td>'.date('Y-m-d',strtotime($Abwesenheit['create_date'])).'</td>';
                $HTML .= '<td>'.$Abwesenheit['urgency'].'</td>';
                $HTML .= '<td> '.$Options.$Comment.'</td>';
            } else {
                $HTML .= '<td id="td-id-'.$counter.'" class="td-class-'.$counter.'"">'.$Abwesenheit['status_bearbeitung'].'</td>';
                $HTML .= '<td>'.$Abwesenheit['begin'].'</td>';
                $HTML .= '<td>'.$Abwesenheit['end'].'</td>';
                $HTML .= '<td>'.$Abwesenheit['type'].'</td>';
                $HTML .= '<td>'.date('Y-m-d',strtotime($Abwesenheit['create_date'])).'</td>';
                $HTML .= '<td>'.$Abwesenheit['urgency'].'</td>';
                $HTML .= '<td> '.$Options.$Comment.'</td>';
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

function add_entry_abwesenheiten_management($mysqli,$UE=1){

    // Initialize Placeholder & Error Variables
    $FormHTML = "";
    $OutputMode = "show_form";
    $DAUcheck = 0;
    $statusPlaceholder = "Beantragt";
    $entryDatePlaceholder = date('Y-m-d');
    $ReturnMessage = $userIDPlaceholder = $startDatePlaceholder = $endDatePlaceholder = $typePlaceholder = $urgencyPlaceholder = $commentPlaceholder = $approvalDatePlaceholder = "";
    $startDateErr = $endDateErr = $entryDateErr = $approvalDateErr = $ApprovalUser = "";

    // Do stuff
    if(isset($_POST['add_abwesenheit_action'])){

        $AllAbwesenheiten = get_sorted_list_of_all_abwesenheiten($mysqli);

        // Load Form content
        $userIDPlaceholder = trim($_POST['user']);
        $startDatePlaceholder = trim($_POST['start']);
        $endDatePlaceholder = trim($_POST['end']);
        $typePlaceholder = trim($_POST['type']);
        $urgencyPlaceholder = trim($_POST['urgency']);
        $entryDatePlaceholder = trim($_POST['entry-date']);
        $commentPlaceholder = trim($_POST['comment_user']);
        $statusPlaceholder = trim($_POST['status']);
        $approvalDatePlaceholder = trim($_POST['approval-date']);

        // Do some DAU-Checks here
        if(empty($userIDPlaceholder)){
            $DAUcheck++;
            $startDateErr = "Bitte wählen Sie eine/n zu erfassende/n Mitarbeiter/in aus!";
        }

        // Check fucked up date entries
        if($startDatePlaceholder>$endDatePlaceholder){
            $DAUcheck++;
            $startDateErr = "Das Anfangsdatum darf nicht nach dem Enddatum liegen!";
        }

        if($approvalDatePlaceholder!=''){
            if($statusPlaceholder=='Beantragt'){
                $DAUcheck++;
                $approvalDateErr = "Wenn der Antrag als bereits bearbeitet festgehalten werden soll, muss er entweder als genehmigt oder abgelehnt markiert sein!";
            }
            $ApprovalUser = get_current_user_id();
        }

        //Check overlaps!
        $Check = check_abwesenheit_date_overlap_user($userIDPlaceholder, $AllAbwesenheiten, $startDatePlaceholder, $endDatePlaceholder);
        if($Check['bool']){
            $DAUcheck++;
            $endDateErr = "Der eingegebene Antrag kollidiert mit anderen bereits erfassten Anträgen!";
        }

        if($DAUcheck==0){

            $Return = add_abwesenheitsantrag($userIDPlaceholder, $startDatePlaceholder, $endDatePlaceholder, $typePlaceholder, $urgencyPlaceholder, $entryDatePlaceholder, $commentPlaceholder, $statusPlaceholder, $approvalDatePlaceholder, $ApprovalUser);
            if($Return['success']){
                $OutputMode="show_return_card";
                $ReturnMessage = "Abwesenheit erfolgreich angelegt!";
            } else {
                $OutputMode="show_return_card";
                $ReturnMessage = $Return['err'];
            }
        }
    }

    if($OutputMode=="show_form"){
        //Build Form
        $FormHTML .= form_group_dropdown_all_users('Mitarbeiter/in', 'user', $userIDPlaceholder, true, '');
        $FormHTML .= form_hidden_input_generator('plchldr1', '1');
        $FormHTML .= form_group_input_date('Beginn', 'start', $startDatePlaceholder, true, $startDateErr, false);
        $FormHTML .= form_group_input_date('Ende', 'end', $endDatePlaceholder, true, $endDateErr, false);
        $FormHTML .= form_group_dropdown_abwesenheitentypen('Abwesenheitstyp', 'type', $typePlaceholder, true, '', false, 'management');
        $FormHTML .= form_hidden_input_generator('org_ue', $UE);
        $FormHTML .= form_group_dropdown_abwesenheiten_dringlichkeiten_typen('Dringlichkeit', 'urgency', $urgencyPlaceholder, true, '');
        $FormHTML .= form_hidden_input_generator('plchldr3', '3');
        $FormHTML .= form_group_input_date('Beantragt am', 'entry-date', $entryDatePlaceholder, true, $entryDateErr, false);
        $FormHTML .= form_group_input_text('Kommentar des/der Antragstellers/in', 'comment_user', $commentPlaceholder, false);
        $FormHTML .= "<h5>Optional: Bereits stattgefundene Bearbeitung festhalten</h5>";
        $FormHTML .= form_group_input_date('Bearbeitet am', 'approval-date', $approvalDatePlaceholder, true, $approvalDateErr, false);
        $FormHTML .= form_group_dropdown_bearbeitungsstati('Bearbeitungsstatus', 'status', $statusPlaceholder, false, $approvalDateErr, false);
        $FormHTML .= "<br>";
        $FormHTML .= form_group_continue_return_buttons(true, 'Anlegen', 'add_abwesenheit_action', 'btn-primary', true, 'Zurück', 'abwesenheitmanagement_go_back', 'btn-primary');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');
        return card_builder('Neue Abwesenheit anlegen','', $FORM);
    }else{
        $FormHTML = form_hidden_input_generator('org_ue', $UE);
        $FormHTML .= form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'abwesenheitmanagement_go_back', 'btn-primary');
        $FORM = form_builder($FormHTML, 'self', 'POST');
        return card_builder('Neue Abwesenheit anlegen',$ReturnMessage, $FORM);
    }
}

function add_entry_abwesenheiten_user($mysqli){

    // Initialize Placeholder & Error Variables
    $FormHTML = "";
    $OutputMode = "show_form";
    $DAUcheck = 0;
    $userIDPlaceholder = get_current_user_id();
    $entryDatePlaceholder = date('Y-m-d');
    $ReturnMessage = $startDatePlaceholder = $endDatePlaceholder = $typePlaceholder = $urgencyPlaceholder = $commentPlaceholder = "";
    $startDateErr = $endDateErr = "";

    // Do stuff
    if(isset($_POST['add_abwesenheit_action'])){

        $AllAbwesenheiten = get_sorted_list_of_all_abwesenheiten($mysqli);

        // Load Form content
        $startDatePlaceholder = trim($_POST['start']);
        $endDatePlaceholder = trim($_POST['end']);
        $typePlaceholder = trim($_POST['type']);
        $urgencyPlaceholder = trim($_POST['urgency']);
        $commentPlaceholder = trim($_POST['comment_user']);

        // Do some DAU-Checks here
        // Check fucked up date entries
        if($startDatePlaceholder>$endDatePlaceholder){
            $DAUcheck++;
            $startDateErr = "Das Anfangsdatum darf nicht nach dem Enddatum liegen!";
        }

        if($startDatePlaceholder<date('Y-m-d')){
            $DAUcheck++;
            $startDateErr = "Das Anfangsdatum darf nicht in der Vergangenheit liegen!";
        }

        //Check overlaps!
        $Check = check_abwesenheit_date_overlap_user($userIDPlaceholder, $AllAbwesenheiten, $startDatePlaceholder, $endDatePlaceholder);
        if($Check['bool']){
            $DAUcheck++;
            $endDateErr = "Der eingegebene Antrag kollidiert mit anderen bereits erfassten Anträgen!";
        }

        if($DAUcheck==0){

            $Return = add_abwesenheitsantrag($userIDPlaceholder, $startDatePlaceholder, $endDatePlaceholder, $typePlaceholder, $urgencyPlaceholder, $entryDatePlaceholder, $commentPlaceholder);
            if($Return['success']){
                $OutputMode="show_return_card";
                $ReturnMessage = "Abwesenheitsantrag erfolgreich angelegt!";
            } else {
                $OutputMode="show_return_card";
                $ReturnMessage = $Return['err'];
            }
        }
    }

    if($OutputMode=="show_form"){
        //Build Form
        $FormHTML .= form_hidden_input_generator('plchldr1', '1');
        $FormHTML .= form_group_input_date('Beginn', 'start', $startDatePlaceholder, true, $startDateErr, false);
        $FormHTML .= form_group_input_date('Ende', 'end', $endDatePlaceholder, true, $endDateErr, false);
        $FormHTML .= form_group_dropdown_abwesenheitentypen('Abwesenheitstyp', 'type', $typePlaceholder, true, '');
        $FormHTML .= form_hidden_input_generator('plchldr2', '2');
        $FormHTML .= form_group_dropdown_abwesenheiten_dringlichkeiten_typen('Dringlichkeit', 'urgency', $urgencyPlaceholder, true, '');
        $FormHTML .= form_hidden_input_generator('plchldr3', '3');
        $FormHTML .= form_group_input_text('Kommentar des/der Antragstellers/in', 'comment_user', $commentPlaceholder, false);
        $FormHTML .= "<br>";
        $FormHTML .= form_group_continue_return_buttons(true, 'Anlegen', 'add_abwesenheit_action', 'btn-primary', true, 'Zurück', 'abwesenheitmanagement_go_back', 'btn-primary');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');
        return card_builder('Abwesenheitsantrag anlegen','', $FORM);
    }else{
        $FormHTML = form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'abwesenheitmanagement_go_back', 'btn-primary');
        $FORM = form_builder($FormHTML, 'self', 'POST');
        return card_builder('Abwesenheitsantrag anlegen',$ReturnMessage, $FORM);
    }
}

function edit_entry_abwesenheiten_management($mysqli, $AbwesenheitObj,$UE=1){

    // Initialize Placeholder & Error Variables
    $FormHTML = "";
    $OutputMode = "show_form";
    $DAUcheck = 0;
    $ReturnMessage = "";
    $ItemIDplaceholder = $AbwesenheitObj['id'];
    $userIDPlaceholder  = $AbwesenheitObj['user'];
    $startDatePlaceholder = $AbwesenheitObj['begin'];
    $endDatePlaceholder =  $AbwesenheitObj['end'];
    $typePlaceholder =  $AbwesenheitObj['type'];
    $urgencyPlaceholder =  $AbwesenheitObj['urgency'];
    $commentPlaceholder =  $AbwesenheitObj['create_comment'];
    $entryDatePlaceholder = date('Y-m-d', strtotime($AbwesenheitObj['create_date']));
    $statusPlaceholder = $AbwesenheitObj['status_bearbeitung'];
    $commentApprovePlaceholder = $AbwesenheitObj['delete_comment'];
    $approvalDatePlaceholder = date('Y-m-d', strtotime($AbwesenheitObj['bearbeitet_am']));

    $startDateErr = $endDateErr = $entryDateErr = $approvalDateErr = "";

    // Do stuff
    if(isset($_POST['edit_abwesenheit_action'])){

        $AllAbwesenheiten = get_sorted_list_of_all_abwesenheiten($mysqli);

        // Load Form content
        $startDatePlaceholder = trim($_POST['start']);
        $endDatePlaceholder = trim($_POST['end']);
        $typePlaceholder = trim($_POST['type']);
        $urgencyPlaceholder = trim($_POST['urgency']);
        $entryDatePlaceholder = trim($_POST['entry-date']);
        $commentPlaceholder = trim($_POST['comment_user']);
        $statusPlaceholder = trim($_POST['status']);
        $commentApprovePlaceholder = trim($_POST['comment_approve']);
        $approvalDatePlaceholder = trim($_POST['approval-date']);

        // Do some DAU-Checks here
        if(empty($userIDPlaceholder)){
            $DAUcheck++;
            $startDateErr = "Bitte wählen Sie eine/n zu erfassende/n Mitarbeiter/in aus!";
        }

        // Check fucked up date entries
        if($startDatePlaceholder>$endDatePlaceholder){
            $DAUcheck++;
            $startDateErr = "Das Anfangsdatum darf nicht nach dem Enddatum liegen!";
        }

        if($approvalDatePlaceholder>date('Y-m-d')){
            $DAUcheck++;
            $startDateErr = "Das Bearbeitungsdatum darf nicht in der Zukunft liegen!";
        }

        if($approvalDatePlaceholder!=''){
            if($statusPlaceholder=='Beantragt'){
                $DAUcheck++;
                $approvalDateErr = "Wenn der Antrag als bereits bearbeitet festgehalten werden soll, muss er entweder als genehmigt oder abgelehnt markiert sein!";
            }
        }

        //Check overlaps!
        $Check = check_abwesenheit_date_overlap_user($userIDPlaceholder, $AllAbwesenheiten, $startDatePlaceholder, $endDatePlaceholder, $ItemIDplaceholder);
        if($Check['bool']){
            $DAUcheck++;
            $endDateErr = "Der eingegebene Antrag kollidiert mit anderen bereits erfassten Anträgen!";
        }

        if($DAUcheck==0){

            $Return = complete_edit_abwesenheitsantrag($mysqli, $AbwesenheitObj['id'], $startDatePlaceholder, $endDatePlaceholder, $typePlaceholder, $urgencyPlaceholder, $commentPlaceholder, $entryDatePlaceholder, $approvalDatePlaceholder, $statusPlaceholder, $commentApprovePlaceholder);
            if($Return['success']){
                $OutputMode="show_return_card";
                $ReturnMessage = "Abwesenheit erfolgreich bearbeitet!";
            } else {
                $OutputMode="show_return_card";
                $ReturnMessage = $Return['err'];
            }
        }
    }

    if($OutputMode=="show_form"){
        //Build Form
        $FormHTML .= form_group_dropdown_all_users('Mitarbeiter/in', 'user', $userIDPlaceholder, true, '', true);
        $FormHTML .= form_group_input_date('Beginn', 'start', $startDatePlaceholder, true, $startDateErr, false);
        $FormHTML .= form_group_input_date('Ende', 'end', $endDatePlaceholder, true, $endDateErr, false);
        $FormHTML .= form_group_dropdown_abwesenheitentypen('Abwesenheitstyp', 'type', $typePlaceholder, true, '', false, 'management');
        $FormHTML .= form_group_dropdown_abwesenheiten_dringlichkeiten_typen('Dringlichkeit', 'urgency', $urgencyPlaceholder, true, '');
        $FormHTML .= form_hidden_input_generator('abwesenheit_id', $AbwesenheitObj['id']);
        $FormHTML .= form_group_input_date('Beantragt am', 'entry-date', $entryDatePlaceholder, true, $entryDateErr, false);
        $FormHTML .= form_hidden_input_generator('org_ue', $UE);
        $FormHTML .= form_group_input_text('Kommentar des/der Antragstellers/in', 'comment_user', $commentPlaceholder, false);
        $FormHTML .= "<h5>Optional: Bereits stattgefundene Bearbeitung ändern</h5>";
        $FormHTML .= form_group_input_date('Bearbeitet am', 'approval-date', $approvalDatePlaceholder, true, $approvalDateErr, false);
        $FormHTML .= form_group_dropdown_bearbeitungsstati('Bearbeitungsstatus', 'status', $statusPlaceholder, false, $approvalDateErr, false);
        $FormHTML .= form_group_input_text('Kommentar(e) zur Bearbeitung', 'comment_approve', $commentApprovePlaceholder, false);
        $FormHTML .= "<br>";
        $FormHTML .= form_group_continue_return_buttons(true, 'Bearbeiten', 'edit_abwesenheit_action', 'btn-primary', true, 'Zurück', 'abwesenheitmanagement_go_back', 'btn-primary');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');
        return card_builder('Abwesenheit bearbeiten','', $FORM);
    }else{
        $FormHTML = form_hidden_input_generator('org_ue', $UE);
        $FormHTML .= form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'abwesenheitmanagement_go_back', 'btn-primary');
        $FORM = form_builder($FormHTML, 'self', 'POST');
        return card_builder('Abwesenheit bearbeiten',$ReturnMessage, $FORM);
    }
}

function edit_entry_abwesenheiten_user($mysqli, $AbwesenheitObj){

    // Initialize Placeholder & Error Variables
    $FormHTML = "";
    $OutputMode = "show_form";
    $DAUcheck = 0;
    $ReturnMessage = "";
    $ItemIDplaceholder = $AbwesenheitObj['id'];
    $userIDPlaceholder  = $AbwesenheitObj['user'];
    $startDatePlaceholder = $AbwesenheitObj['begin'];
    $endDatePlaceholder =  $AbwesenheitObj['end'];
    $typePlaceholder =  $AbwesenheitObj['type'];
    $urgencyPlaceholder =  $AbwesenheitObj['urgency'];
    $commentPlaceholder =  $AbwesenheitObj['create_comment'];
    $entryDatePlaceholder = $AbwesenheitObj['create_date'];
    $statusPlaceholder = $AbwesenheitObj['status_bearbeitung'];
    $commentApprovePlaceholder = $AbwesenheitObj['delete_comment'];
    $approvalDatePlaceholder = $AbwesenheitObj['bearbeitet_am'];

    $startDateErr = $endDateErr = $entryDateErr = $approvalDateErr = "";

    // Do stuff
    if(isset($_POST['edit_abwesenheit_action'])){

        $AllAbwesenheiten = get_sorted_list_of_all_abwesenheiten($mysqli);

        // Load Form content
        $startDatePlaceholder = trim($_POST['start']);
        $endDatePlaceholder = trim($_POST['end']);
        $typePlaceholder = trim($_POST['type']);
        $urgencyPlaceholder = trim($_POST['urgency']);
        $commentPlaceholder = trim($_POST['comment_user']);

        // Do some DAU-Checks here
        // Check fucked up date entries
        if($startDatePlaceholder>$endDatePlaceholder){
            $DAUcheck++;
            $startDateErr = "Das Anfangsdatum darf nicht nach dem Enddatum liegen!";
        }

        if($startDatePlaceholder<date('Y-m-d')){
            $DAUcheck++;
            $startDateErr = "Der Beantragungszeitraum darf nicht in der Vergangenheit liegen!";
        }

        //Check overlaps!
        $Check = check_abwesenheit_date_overlap_user($userIDPlaceholder, $AllAbwesenheiten, $startDatePlaceholder, $endDatePlaceholder, $ItemIDplaceholder);
        if($Check['bool']){
            $DAUcheck++;
            $endDateErr = "Der eingegebene Antrag kollidiert mit anderen bereits erfassten Anträgen!";
        }

        if($DAUcheck==0){

            $Return = complete_edit_abwesenheitsantrag($mysqli, $AbwesenheitObj['id'], $startDatePlaceholder, $endDatePlaceholder, $typePlaceholder, $urgencyPlaceholder, $commentPlaceholder, $entryDatePlaceholder, $approvalDatePlaceholder, $statusPlaceholder, $commentApprovePlaceholder);
            if($Return['success']){
                $OutputMode="show_return_card";
                $ReturnMessage = "Abwesenheit erfolgreich bearbeitet!";
            } else {
                $OutputMode="show_return_card";
                $ReturnMessage = $Return['err'];
            }
        }
    }

    if($OutputMode=="show_form"){
        //Build Form
        $FormHTML .= form_group_input_date('Beginn', 'start', $startDatePlaceholder, true, $startDateErr, false);
        $FormHTML .= form_group_input_date('Ende', 'end', $endDatePlaceholder, true, $endDateErr, false);
        $FormHTML .= form_group_dropdown_abwesenheitentypen('Abwesenheitstyp', 'type', $typePlaceholder, true, '', false, 'management');
        $FormHTML .= form_group_dropdown_abwesenheiten_dringlichkeiten_typen('Dringlichkeit', 'urgency', $urgencyPlaceholder, true, '');
        $FormHTML .= form_hidden_input_generator('abwesenheit_id', $AbwesenheitObj['id']);
        $FormHTML .= form_group_input_text('Kommentar des/der Antragstellers/in', 'comment_user', $commentPlaceholder, false);
        $FormHTML .= "<br>";
        $FormHTML .= form_group_continue_return_buttons(true, 'Bearbeiten', 'edit_abwesenheit_action', 'btn-primary', true, 'Zurück', 'abwesenheitmanagement_go_back', 'btn-primary');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');
        return card_builder('Abwesenheit bearbeiten','', $FORM);
    }else{
        $FormHTML = form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'abwesenheitmanagement_go_back', 'btn-primary');
        $FORM = form_builder($FormHTML, 'self', 'POST');
        return card_builder('Abwesenheit bearbeiten',$ReturnMessage, $FORM);
    }
}

function delete_entry_abwesenheiten_management($mysqli, $AbwesenheitObj,$UE=1){

    // Initialize Placeholder & Error Variables
    $FormHTML = "";
    $OutputMode = "show_form";
    $DAUcheck = 0;
    $ReturnMessage = "";
    $userIDPlaceholder  = $AbwesenheitObj['user'];
    $startDatePlaceholder = $AbwesenheitObj['begin'];
    $endDatePlaceholder =  $AbwesenheitObj['end'];
    $typePlaceholder =  $AbwesenheitObj['type'];
    $urgencyPlaceholder =  $AbwesenheitObj['urgency'];
    $commentPlaceholder =  $AbwesenheitObj['create_comment'];
    $deleteCommentPlaceholder =  "";
    $startDateErr = $endDateErr = $deleteErr = "";

    // Do stuff
    if(isset($_POST['delete_abwesenheit_action'])){

        $deleteCommentPlaceholder = trim($_POST['delete_comment']);

        // Do some DAU-Checks here
        if($DAUcheck==0){
            $Return = delete_abwesenheitsantrag($mysqli, $AbwesenheitObj['id'], get_current_user_id(), $deleteCommentPlaceholder);
            if($Return['success']){
                $OutputMode="show_return_card";
                $ReturnMessage = "Abwesenheitsantrag erfolgreich gelöscht!";
            } else {
                $OutputMode="show_return_card";
                $ReturnMessage = $Return['err'];
            }
        }
    }

    if($OutputMode=="show_form"){
        //Build Form
        $FormHTML .= form_group_dropdown_all_users('Mitarbeiter/in', 'user', $userIDPlaceholder, true, '', true);
        $FormHTML .= form_hidden_input_generator('abwesenheit_id', $AbwesenheitObj['id']);
        $FormHTML .= form_group_input_date('Beginn', 'start', $startDatePlaceholder, true, $startDateErr, true);
        $FormHTML .= form_group_input_date('Ende', 'end', $endDatePlaceholder, true, $endDateErr, true);
        $FormHTML .= form_group_dropdown_abwesenheitentypen('Abwesenheitstyp', 'type', $typePlaceholder, true, '', true);
        $FormHTML .= form_hidden_input_generator('org_ue', $UE);
        $FormHTML .= form_group_dropdown_abwesenheiten_dringlichkeiten_typen('Dringlichkeit', 'urgency', $urgencyPlaceholder, true, '', true);
        $FormHTML .= form_hidden_input_generator('plchldr3', '3');
        $FormHTML .= form_group_input_text('Kommentar des/der Antragstellers/in', 'comment_user', $commentPlaceholder, false, '', true);
        $FormHTML .= "<br>";
        $FormHTML .= form_group_input_text('Kommentar zum Löschvorgang', 'delete_comment', $deleteCommentPlaceholder, false, $deleteErr, false);
        $FormHTML .= form_group_continue_return_buttons(true, 'Löschen', 'delete_abwesenheit_action', 'btn-danger', true, 'Zurück', 'abwesenheitmanagement_go_back', 'btn-primary');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');
        return card_builder('Abwesenheitsantrag löschen','Möchten Sie diesen Abwesenheitsantrag wirklich löschen?', $FORM);
    }else{
        $FormHTML = form_hidden_input_generator('org_ue', $UE);
        $FormHTML .= form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'abwesenheitmanagement_go_back', 'btn-primary');
        $FORM = form_builder($FormHTML, 'self', 'POST');
        return card_builder('Abwesenheitsantrag löschen',$ReturnMessage, $FORM);
    }
}

function delete_entry_abwesenheiten_user($mysqli, $AbwesenheitObj){

    // Initialize Placeholder & Error Variables
    $FormHTML = "";
    $OutputMode = "show_form";
    $DAUcheck = 0;
    $ReturnMessage = "";
    $startDatePlaceholder = $AbwesenheitObj['begin'];
    $endDatePlaceholder =  $AbwesenheitObj['end'];
    $typePlaceholder =  $AbwesenheitObj['type'];
    $urgencyPlaceholder =  $AbwesenheitObj['urgency'];
    $commentPlaceholder =  $AbwesenheitObj['create_comment'];
    $startDateErr = $endDateErr = "";

    // Do stuff
    if(isset($_POST['delete_abwesenheit_action'])){

        // Do some DAU-Checks here
        if($DAUcheck==0){

            $DeleteComment = "Von MitarbeiterIn gelöscht";
            $Return = delete_abwesenheitsantrag($mysqli, $AbwesenheitObj['id'], get_current_user_id(), $DeleteComment);
            if($Return['success']){
                $OutputMode="show_return_card";
                $ReturnMessage = "Abwesenheitsantrag erfolgreich gelöscht!";
            } else {
                $OutputMode="show_return_card";
                $ReturnMessage = $Return['err'];
            }
        }
    }

    if($OutputMode=="show_form"){
        //Build Form
        $FormHTML .= form_hidden_input_generator('abwesenheit_id', $AbwesenheitObj['id']);
        $FormHTML .= form_group_input_date('Beginn', 'start', $startDatePlaceholder, true, $startDateErr, true);
        $FormHTML .= form_group_input_date('Ende', 'end', $endDatePlaceholder, true, $endDateErr, true);
        $FormHTML .= form_group_dropdown_abwesenheitentypen('Abwesenheitstyp', 'type', $typePlaceholder, true, '', true);
        $FormHTML .= form_hidden_input_generator('plchldr2', '2');
        $FormHTML .= form_group_dropdown_abwesenheiten_dringlichkeiten_typen('Dringlichkeit', 'urgency', $urgencyPlaceholder, true, '', true);
        $FormHTML .= form_hidden_input_generator('plchldr3', '3');
        $FormHTML .= form_group_input_text('Kommentar des/der Antragstellers/in', 'comment_user', $commentPlaceholder, false, '', true);
        $FormHTML .= "<br>";
        $FormHTML .= form_group_continue_return_buttons(true, 'Löschen', 'delete_abwesenheit_action', 'btn-danger', true, 'Zurück', 'abwesenheitmanagement_go_back', 'btn-primary');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');
        return card_builder('Abwesenheitsantrag löschen','Möchten Sie diesen Abwesenheitsantrag wirklich löschen?', $FORM);
    }else{
        $FormHTML = form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'abwesenheitmanagement_go_back', 'btn-primary');
        $FORM = form_builder($FormHTML, 'self', 'POST');
        return card_builder('Abwesenheitsantrag löschen',$ReturnMessage, $FORM);
    }
}

function allow_abwesenheiten_management($mysqli, $AbwesenheitObj, $Mode='accept',$UE=1){

    // Initialize Placeholder & Error Variables
    $FormHTML = "";
    $OutputMode = "show_form";
    $DAUcheck = 0;
    $ReturnMessage = "";
    $userIDPlaceholder  = $AbwesenheitObj['user'];
    $startDatePlaceholder = $AbwesenheitObj['begin'];
    $endDatePlaceholder =  $AbwesenheitObj['end'];
    $typePlaceholder =  $AbwesenheitObj['type'];
    $urgencyPlaceholder =  $AbwesenheitObj['urgency'];
    $commentPlaceholder =  $AbwesenheitObj['create_comment'];
    $deleteCommentPlaceholder =  "";
    $startDateErr = $endDateErr = $deleteErr = "";

    // Do stuff
    if(isset($_POST['accept_abwesenheit_action']) OR isset($_POST['decline_abwesenheit_action'])){

        $deleteCommentPlaceholder = trim($_POST['delete_comment']);

        // Do some DAU-Checks here
        if($DAUcheck==0){
            if($Mode=='accept'){
                $StatusMode = "Genehmigt";
                $ReturnMessage = "Abwesenheitsantrag erfolgreich genehmigt!";
            } elseif ($Mode=='decline'){
                $StatusMode = "Abgelehnt";
                $ReturnMessage = "Abwesenheitsantrag erfolgreich abgelehnt!";
            }

            $Return = bearbeite_abwesenheitsantrag($mysqli, $AbwesenheitObj['id'], $StatusMode, $deleteCommentPlaceholder);
            if($Return['success']){
                $OutputMode="show_return_card";
            } else {
                $OutputMode="show_return_card";
                $ReturnMessage = $Return['err'];
            }
        }
    }

    if($OutputMode=="show_form"){
        //Build Form
        $FormHTML .= form_group_dropdown_all_users('Mitarbeiter/in', 'user', $userIDPlaceholder, true, '', true);
        $FormHTML .= form_hidden_input_generator('abwesenheit_id', $AbwesenheitObj['id']);
        $FormHTML .= form_group_input_date('Beginn', 'start', $startDatePlaceholder, true, $startDateErr, true);
        $FormHTML .= form_group_input_date('Ende', 'end', $endDatePlaceholder, true, $endDateErr, true);
        $FormHTML .= form_group_dropdown_abwesenheitentypen('Abwesenheitstyp', 'type', $typePlaceholder, true, '', true);
        $FormHTML .= form_hidden_input_generator('org_ue', $UE);
        $FormHTML .= form_group_dropdown_abwesenheiten_dringlichkeiten_typen('Dringlichkeit', 'urgency', $urgencyPlaceholder, true, '', true);
        $FormHTML .= form_hidden_input_generator('plchldr3', '3');
        $FormHTML .= form_group_input_text('Kommentar des/der Antragstellers/in', 'comment_user', $commentPlaceholder, false, '', true);
        $FormHTML .= "<br>";
        $FormHTML .= form_group_input_text('Kommentar zum Bearbeitungsvorgang', 'delete_comment', $deleteCommentPlaceholder, false, $deleteErr, false);

        if($Mode=='accept'){
            $FormHTML .= form_group_continue_return_buttons(true, 'Freigeben', 'accept_abwesenheit_action', 'btn-danger', true, 'Zurück', 'abwesenheitmanagement_go_back', 'btn-primary');
        } elseif($Mode=='decline'){
            $FormHTML .= form_group_continue_return_buttons(true, 'Ablehnen', 'decline_abwesenheit_action', 'btn-danger', true, 'Zurück', 'abwesenheitmanagement_go_back', 'btn-primary');
        }

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');
        if($Mode=='accept'){
            return card_builder('Abwesenheitsantrag freigeben','Möchten Sie diesen Abwesenheitsantrag wirklich annehmen?', $FORM);
        } elseif($Mode=='decline'){
            return card_builder('Abwesenheitsantrag ablehnen','Möchten Sie diesen Abwesenheitsantrag wirklich ablehnen?', $FORM);
        }
    }else{
        $FormHTML = form_hidden_input_generator('org_ue', $UE);
        $FormHTML .= form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'abwesenheitmanagement_go_back', 'btn-primary');
        $FORM = form_builder($FormHTML, 'self', 'POST');
        if($Mode=='accept'){
            return card_builder('Abwesenheitsantrag freigeben',$ReturnMessage, $FORM);
        } elseif($Mode=='decline'){
            return card_builder('Abwesenheitsantrag ablehnen',$ReturnMessage, $FORM);
        }
    }
}

