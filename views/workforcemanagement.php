<?php

function table_workforce_management($mysqli){

    // deal with stupid "" and '' problems
    $bla = '"{"key": "value"}"';
    $Users = get_sorted_list_of_all_users($mysqli);

    // Setup Toolbar
    $HTML = '<div id="toolbar">
                <a id="add_user" class="btn btn-primary" href="workforce_management.php?mode=add_user">
                <i class="bi bi-person-fill-add"></i> Hinzufügen</a>
            </div>';

    // Initialize Table
    $HTML .= '<table data-toggle="table" data-search="true" data-toolbar="#toolbar" data-show-columns="true" data-search-highlight="true">';

    // Setup Table Head
    $HTML .= '<thead>
                <tr class="tr-class-1">
                    <th data-field="nachname">Nachname</th>
                    <th data-field="vorname">Vorname</th>
                    <th data-field="mitarbeiternummer">Mitarbeiternummer</th>
                </tr>
              </thead>';

    // Fill table body
    $HTML .= '<tbody>';
    $counter = 1;
    foreach ($Users as $Mitarbeiter){

        // Build rows
        if($counter==1){
            $HTML .= '<tr id="tr-id-1" class="tr-class-1" data-title="bootstrap table" data-object='.$bla.'>';
        } else {
            $HTML .= '<tr id="tr-id-'.$counter.'" class="tr-class-'.$counter.'">';
        }

        if($counter==1){
            $HTML .= '<td id="td-id-1" class="td-class-1" data-title="bootstrap table">'.$Mitarbeiter['nachname'].'</td>';
            $HTML .= '<td>'.$Mitarbeiter['vorname'].'</td>';
            $HTML .= '<td>'.$Mitarbeiter['mitarbeiternummer'].'</td>';
        } else {
            $HTML .= '<td id="td-id-'.$counter.'" class="td-class-'.$counter.'"">'.$Mitarbeiter['nachname'].'</td>';
            $HTML .= '<td>'.$Mitarbeiter['vorname'].'</td>';
            $HTML .= '<td>'.$Mitarbeiter['mitarbeiternummer'].'</td>';
        }

        // close row and count up
        $HTML .= "</tr>";
        $counter++;
    }
    $HTML .= '</tbody>';
    $HTML .= '</table>';

    return $HTML;
}

function add_user_workforce_management($mysqli){

    // Initialize Placeholder & Error Variables
    $FormHTML = "";
    $OutputMode = "show_form";
    $DAUcheck = 0;

    $vornamePlaceholder = $nachnamePlaceholder = $mitarbeiternummerPlaceholder = $mailPlaceholder = $edvPlaceholder = $GruppePlaceholder = '';
    $vornameErr = $nachnameErr = $mitarbeiternummerErr = $mailErr = $edvErr = $gruppeErr = "";

    // Parser
    if(isset($_POST['add_user_action'])){

        $vornamePlaceholder = trim($_POST['vorname']);
        $nachnamePlaceholder = trim($_POST['nachname']);
        $mitarbeiternummerPlaceholder = trim($_POST['mitarbeiternummer']);
        $mailPlaceholder = trim($_POST['mail']);
        $edvPlaceholder = trim($_POST['edv']);
        $GruppePlaceholder = trim($_POST['gruppe']);

        //DAUchecks
        //Check Empty Input
        if(empty($vornamePlaceholder)){
            $vornameErr = "Bitte geben Sie einen Vornamen des/r neuen Nutzer/in an!";
            $DAUcheck++;
        }

        if(empty($nachnamePlaceholder)){
            $nachnameErr = "Bitte geben Sie einen Nachnamen des/r neuen Nutzer/in an!";
            $DAUcheck++;
        }

        if(empty($mitarbeiternummerPlaceholder)){
            $mitarbeiternummerErr = "Bitte geben Sie eine Mitarbeiternummer des/r neuen Nutzer/in an!";
            $DAUcheck++;
        }

        if(!is_numeric($mitarbeiternummerPlaceholder)){
            $mitarbeiternummerErr = "Eine Mitarbeiternummer darf nur aus Zahlen bestehen!";
            $DAUcheck++;
        }

        if(empty($mailPlaceholder)){
            $mailErr = "Bitte geben Sie eine Mailadresse des/r neuen Nutzer/in an!";
            $DAUcheck++;
        }

        if(!filter_var($mailPlaceholder, FILTER_VALIDATE_EMAIL)){
            $mailErr = "Bitte geben Sie eine gültige Mailadresse des/r neuen Nutzer/in an!";
            $DAUcheck++;
        }

        if(empty($edvPlaceholder)){
            $edvErr = "Bitte geben Sie einen EDV-Login des/r neuen Nutzer/in an!";
            $DAUcheck++;
        }

        if(empty($GruppePlaceholder)){
            $gruppeErr = "Bitte wählen Sie die Mitarbeitergruppe des/r neuen Nutzer/in an!";
            $DAUcheck++;
        }

        // check if user with identical unique data exists
        $AllUsers = get_sorted_list_of_all_users($mysqli, 'id', true);
        foreach ($AllUsers as $User){
            if($mitarbeiternummerPlaceholder==$User['mitarbeiternummer']){
                $mitarbeiternummerErr = "Ein/e Nutzer/in mit dieser Mitarbeiternummer existiert bereits!";
                $DAUcheck++;
            }

            if($mailPlaceholder==$User['mail']){
                $mailErr = "Ein/e Nutzer/in mit dieser Mailadresse existiert bereits!";
                $DAUcheck++;
            }

            if($edvPlaceholder==$User['username']){
                $edvErr = "Ein/e Nutzer/in mit diesem EDV-Login existiert bereits!";
                $DAUcheck++;
            }
        }

        // Add user
        if($DAUcheck==0) {
            $Result = add_new_user($vornamePlaceholder, $nachnamePlaceholder, $edvPlaceholder, $mitarbeiternummerPlaceholder, $mailPlaceholder, $GruppePlaceholder, 'nutzer');
            if($Result['success']){
                $ReturnMessage = "Nutzer/in ".$vornamePlaceholder." ".$nachnamePlaceholder." erfolgreich angelegt!";
            } else {
                $DAUcheck++;
            }
        }


        //Set Output mode
        if($DAUcheck==0){
            $OutputMode = "return_card";
        }
    }

    if($OutputMode=="show_form"){
        //Build Form
        $FormHTML .= form_group_input_text('Vorname', 'vorname', $vornamePlaceholder, true, $vornameErr);
        $FormHTML .= form_group_input_text('Nachname', 'nachname', $nachnamePlaceholder, true, $nachnameErr);
        $FormHTML .= form_group_input_text('Mitarbeiternummer', 'mitarbeiternummer', $mitarbeiternummerPlaceholder, true, $mitarbeiternummerErr);
        $FormHTML .= form_group_input_text('UKT-Mail', 'mail', $mailPlaceholder, true, $mailErr);
        $FormHTML .= form_group_input_text('EDV-Kürzel', 'edv', $edvPlaceholder, true, $edvErr);
        $FormHTML .= form_group_dropdown_mitarbeitertypen('Mitarbeitergruppe', 'gruppe', $GruppePlaceholder, true, $gruppeErr, false);

        $FormHTML .= form_group_continue_return_buttons(true, 'Anlegen', 'add_user_action', 'btn-primary', true, 'Zurück', 'workforcemanagement_go_back', 'btn-primary');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');
        return card_builder('Neue/n Mitarbeiter/in anlegen','', $FORM);
    }else{
        $FormHTML = form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'workforcemanagement_go_back', 'btn-primary');
        $FORM = form_builder($FormHTML, 'self', 'POST');
        return card_builder('Neue/n Mitarbeiter/in anlegen',$ReturnMessage, $FORM);
    }



}