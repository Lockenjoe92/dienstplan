<?php

function table_abwesenheiten_management($mysqli){

    // deal with stupid "" and '' problems
    $bla = '"{"key": "value"}"';
    $Abwesenheiten = get_sorted_list_of_all_abwesenheiten($mysqli);
    $Users = get_sorted_list_of_all_users($mysqli);

    // Setup Toolbar
    $HTML = '<div id="toolbar">
                <a id="add_user" class="btn btn-primary" href="abwesenheiten_management.php?mode=add_abwesenheit">
                <i class="bi bi-person-fill-add"></i> Hinzufügen</a>
            </div>';

    // Initialize Table
    $HTML .= '<table data-toggle="table" 
data-search="true" 
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

        if($counter==1){
            $HTML .= '<td id="td-id-1" class="td-class-1" data-title="bootstrap table">'.$Abwesenheit['status_bearbeitung'].'</td>';
            $HTML .= '<td>'.$User['nachname'].', '.$User['vorname'].'</td>';
            $HTML .= '<td>'.$Abwesenheit['begin'].'</td>';
            $HTML .= '<td>'.$Abwesenheit['end'].'</td>';
            $HTML .= '<td>'.$Abwesenheit['type'].'</td>';
            $HTML .= '<td>'.date('Y-m-d',strtotime($Abwesenheit['create_date'])).'</td>';
            $HTML .= '<td>'.$Abwesenheit['urgency'].'</td>';
            $HTML .= '<td></td>';
        } else {
            $HTML .= '<td id="td-id-'.$counter.'" class="td-class-'.$counter.'"">'.$Abwesenheit['status_bearbeitung'].'</td>';
            $HTML .= '<td>'.$User['nachname'].', '.$User['vorname'].'</td>';
            $HTML .= '<td>'.$Abwesenheit['begin'].'</td>';
            $HTML .= '<td>'.$Abwesenheit['end'].'</td>';
            $HTML .= '<td>'.$Abwesenheit['type'].'</td>';
            $HTML .= '<td>'.date('Y-m-d',strtotime($Abwesenheit['create_date'])).'</td>';
            $HTML .= '<td>'.$Abwesenheit['urgency'].'</td>';
            $HTML .= '<td></td>';
        }

        // close row and count up
        $HTML .= "</tr>";
        $counter++;
    }
    $HTML .= '</tbody>';
    $HTML .= '</table>';

    return $HTML;
}

function add_entry_abwesenheiten_management($mysqli){

    // Initialize Placeholder & Error Variables
    $FormHTML = "";
    $OutputMode = "show_form";
    $DAUcheck = 0;
    $ReturnMessage = $userIDPlaceholder = $startDatePlaceholder = $endDatePlaceholder = $typePlaceholder = $urgencyPlaceholder = $entryDatePlaceholder = $commentPlaceholder = "";
    $startDateErr = $endDateErr = $entryDateErr = "";

    // Do stuff
    if(isset($_POST['add_abwesenheit_action'])){

        // Load Form content
        $userIDPlaceholder = trim($_POST['user']);
        $startDatePlaceholder = trim($_POST['start']);
        $endDatePlaceholder = trim($_POST['end']);
        $typePlaceholder = trim($_POST['type']);
        $urgencyPlaceholder = trim($_POST['urgency']);
        $entryDatePlaceholder = trim($_POST['entry-date']);
        $commentPlaceholder = trim($_POST['comment_user']);

        // Do some DAU-Checks here
        if($DAUcheck==0){

            $Return = add_abwesenheitsantrag($userIDPlaceholder, $startDatePlaceholder, $endDatePlaceholder, $typePlaceholder, $urgencyPlaceholder, $entryDatePlaceholder, $commentPlaceholder);
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
        $FormHTML .= form_group_dropdown_abwesenheitentypen('Abwesenheitstyp', 'type', $typePlaceholder, true, '');
        $FormHTML .= form_hidden_input_generator('plchldr2', '2');
        $FormHTML .= form_group_dropdown_abwesenheiten_dringlichkeiten_typen('Dringlichkeit', 'urgency', $urgencyPlaceholder, true, '');
        $FormHTML .= form_hidden_input_generator('plchldr3', '3');
        $FormHTML .= form_group_input_date('Beantragt am', 'entry-date', $entryDatePlaceholder, true, $entryDateErr, false);
        $FormHTML .= form_group_input_text('Kommentar des/der Antragstellers/in', 'comment_user', $commentPlaceholder, false);
        $FormHTML .= "<br>";
        $FormHTML .= form_group_continue_return_buttons(true, 'Anlegen', 'add_abwesenheit_action', 'btn-primary', true, 'Zurück', 'abwesenheitmanagement_go_back', 'btn-primary');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');
        return card_builder('Neue Abwesenheit anlegen','', $FORM);
    }else{
        $FormHTML = form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'abwesenheitmanagement_go_back', 'btn-primary');
        $FORM = form_builder($FormHTML, 'self', 'POST');
        return card_builder('Neue Abwesenheit anlegen',$ReturnMessage, $FORM);
    }
}