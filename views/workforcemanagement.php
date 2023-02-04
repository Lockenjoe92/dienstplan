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
                    <th data-field="nachname" data-sortable="true">Nachname</th>
                    <th data-field="vorname" data-sortable="true">Vorname</th>
                    <th data-field="mitarbeiternummer" data-sortable="true">Mitarbeiternummer</th>
                    <th data-field="mail" data-sortable="true">Mail</th>
                    <th data-field="abteilungsrollen" data-sortable="true">Rolle</th>
                    <th data-field="vertrag" data-sortable="true">Vertragsumfang</th>
                    <th>Optionen</th>
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
            $HTML .= '<td>'.$Mitarbeiter['mail'].'</td>';
            $HTML .= '<td>'.$Mitarbeiter['abteilungsrollen'].'</td>';
            $HTML .= '<td>'.$Mitarbeiter['vertrag'].'%</td>';
            $HTML .= '<td><a href="workforce_management.php?mode=edit_user&user_id='.$Mitarbeiter['id'].'"><i class="bi bi-pencil-fill"></a></td>';
        } else {
            $HTML .= '<td id="td-id-'.$counter.'" class="td-class-'.$counter.'"">'.$Mitarbeiter['nachname'].'</td>';
            $HTML .= '<td>'.$Mitarbeiter['vorname'].'</td>';
            $HTML .= '<td>'.$Mitarbeiter['mitarbeiternummer'].'</td>';
            $HTML .= '<td>'.$Mitarbeiter['mail'].'</td>';
            $HTML .= '<td>'.$Mitarbeiter['abteilungsrollen'].'</td>';
            $HTML .= '<td>'.$Mitarbeiter['vertrag'].'%</td>';
            $HTML .= '<td><a href="workforce_management.php?mode=edit_user&user_id='.$Mitarbeiter['id'].'"><i class="bi bi-pencil-fill"></a></td>';
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
    $FreieTagePlaceholder = [];
    $vornamePlaceholder = $nachnamePlaceholder = $mitarbeiternummerPlaceholder = $mailPlaceholder = $edvPlaceholder = $GruppePlaceholder = '';
    $vertragPlaceholder = VERTRAGSUMFANG;
    $urlaubPlaceholder = DEFAULTURLAUBSTAGE;
    $vornameErr = $nachnameErr = $mitarbeiternummerErr = $mailErr = $edvErr = $freietageErr = $gruppeErr = $urlaubErr = $vertragErr = "";

    // Parser
    if(isset($_POST['add_user_action'])){

        $vornamePlaceholder = trim($_POST['vorname']);
        $nachnamePlaceholder = trim($_POST['nachname']);
        $mitarbeiternummerPlaceholder = trim($_POST['mitarbeiternummer']);
        $mailPlaceholder = trim($_POST['mail']);
        #$edvPlaceholder = trim($_POST['edv']);
        $GruppePlaceholder = trim($_POST['gruppe']);
        if(isset($_POST['free_days'])){
            $FreieTagePlaceholder = $_POST['free_days'];
        }
        $vertragPlaceholder = trim($_POST['vertrag']);
        $urlaubPlaceholder = trim($_POST['urlaub']);

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

        if(empty($vertragPlaceholder)){
            $vertragErr = "Bitte geben Sie den Vertragsumfang des/r neuen Nutzer/in an!";
            $DAUcheck++;
        }

        if(!is_numeric($vertragPlaceholder)){
            $vertragErr = "Der Vertragsumfang darf nur aus Zahlen bestehen!";
            $DAUcheck++;
        }

        if($vertragPlaceholder<0){
            $vertragErr = "Der Vertragsumfang muss mindestens 1% betragen!";
            $DAUcheck++;
        }

        if($vertragPlaceholder>100){
            $vertragErr = "Der Vertragsumfang darf höchstens 100% betragen!";
            $DAUcheck++;
        }

        if(empty($urlaubPlaceholder)){
            $urlaubErr = "Bitte geben Sie die Menge jährlicher Urlaubstage des/r neuen Nutzer/in an!";
            $DAUcheck++;
        }

        if(!is_numeric($urlaubPlaceholder)){
            $urlaubErr = "Die Menge jährlicher Urlaubstage darf nur aus Zahlen bestehen!";
            $DAUcheck++;
        }

        if($vertragPlaceholder<0){
            $vertragErr = "Mitarbeiter/innen benötigen mindestens einen Urlaubstag im Jahr!";
            $DAUcheck++;
        }

        if($vertragPlaceholder>100){
            $vertragErr = "Der Urlaubsumfang darf höchstens 100 Tage betragen!";
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

        #if(empty($edvPlaceholder)){
        #    $edvErr = "Bitte geben Sie einen EDV-Login des/r neuen Nutzer/in an!";
        #    $DAUcheck++;
        #}

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

            #if($mailPlaceholder==$User['mail']){
             #   $mailErr = "Ein/e Nutzer/in mit dieser Mailadresse existiert bereits!";
              #  $DAUcheck++;
            #}

            #if($edvPlaceholder==$User['username']){
             #   $edvErr = "Ein/e Nutzer/in mit diesem EDV-Login existiert bereits!";
              #  $DAUcheck++;
            #}
        }

        // Add user
        if($DAUcheck==0) {
            $edvPlaceholder = "anxyzz1";

            if(is_array($FreieTagePlaceholder)){
                $FreieTagePlaceholder = implode(',',$FreieTagePlaceholder);
            }

            $Result = add_new_user($vornamePlaceholder, $nachnamePlaceholder, $edvPlaceholder, $mitarbeiternummerPlaceholder, $mailPlaceholder, $GruppePlaceholder, 'nutzer', $vertragPlaceholder, $urlaubPlaceholder, $FreieTagePlaceholder);
            if($Result['success']){
                $ReturnMessage = "Nutzer/in ".$vornamePlaceholder." ".$nachnamePlaceholder." erfolgreich angelegt!<br>Das Initalpasswort lautet: <b>".$Result['pass']."</b>";
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
        #$FormHTML .= form_group_input_text('EDV-Kürzel', 'edv', $edvPlaceholder, true, $edvErr);
        $FormHTML .= form_group_input_text('Urlaubstage', 'urlaub', $urlaubPlaceholder, true, $urlaubErr);
        $FormHTML .= form_group_input_text('Vertragsumfang in %', 'vertrag', $vertragPlaceholder, true, $vertragErr);
        $FormHTML .= form_group_dorpdown_arbeitstage('Arbeitsfreie Tage', 'free_days[]', $FreieTagePlaceholder, false, $freietageErr, false);
        $FormHTML .= form_hidden_input_generator('plchldr1', '1');
        $FormHTML .= form_group_dropdown_mitarbeitertypen('Mitarbeitergruppe', 'gruppe', $GruppePlaceholder, true, $gruppeErr, false);
        $FormHTML .= form_hidden_input_generator('plchldr2', '2');

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

function edit_user_workforce_management($mysqli, $admin=false){

    // Initialize Placeholder & Error Variables
    $FormHTML = "";
    $OutputMode = "show_form";
    $vornameErr = $nachnameErr = $mitarbeiternummerErr = $mailErr = $edvErr = $ueErr = $gruppeErr = $urlaubErr = $vertragErr = $rollenErr = "";
    $DAUcheck = 0;
    $UserCounter = 0;
    $FoundUser = [];
    $DAUerr = "";

    if(isset($_POST['edit_user_action'])){
        $SelectedUserID = $_POST['user_id'];
    } else {
        $SelectedUserID = $_GET['user_id'];
    }


    // Catch first DAU scenarios
    if(!is_numeric($SelectedUserID)){
        $DAUcheck++;
        $DAUerr = "Kein/e gültige/n User/in ausgewählt. Bitte nochmal probieren!";
    } else {
        $AllUsers = get_sorted_list_of_all_users($mysqli, 'id', true);
        foreach ($AllUsers as $User){
            if($User['id']==$SelectedUserID){
                $FoundUser = $User;
                $MitarbeiterName = $FoundUser['vorname']." ".$FoundUser['nachname'];
                $UserCounter++;
            }
        }

        if($UserCounter==0){
            $DAUcheck++;
            $DAUerr = "Kein/e gültige/n User/in ausgewählt. Bitte nochmal probieren!";
        }
    }

    // Show return card in case no valid user id was passed
    if($DAUcheck>0){
        $FormHTML = form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'workforcemanagement_go_back', 'btn-primary');
        $FORM = form_builder($FormHTML, 'self', 'POST');
        return card_builder('Mitarbeiter/in bearbeiten',$DAUerr, $FORM);
    } else {

        // Inititalize Form variables from Database object
        $vornamePlaceholder = $FoundUser['vorname'];
        $nachnamePlaceholder = $FoundUser['nachname'];
        $mitarbeiternummerPlaceholder = $FoundUser['mitarbeiternummer'];
        $mailPlaceholder = $FoundUser['mail'];
        $edvPlaceholder = $FoundUser['username'];
        $GruppePlaceholder = $FoundUser['abteilungsrollen'];
        $vertragPlaceholder = $FoundUser['vertrag'];
        $urlaubPlaceholder = $FoundUser['urlaubstage'];
        $uePlaceholder = $FoundUser['default_abteilung'];
        $RollenPlaceholder = explode(',',$FoundUser['nutzergruppen']);
        $freieTagePlaceholder = explode(',', $FoundUser['freie_tage']);

        // Parser
        if(isset($_POST['edit_user_action'])){

            $vornamePlaceholder = trim($_POST['vorname']);
            $nachnamePlaceholder = trim($_POST['nachname']);
            $mitarbeiternummerPlaceholder = trim($_POST['mitarbeiternummer']);
            $mailPlaceholder = trim($_POST['mail']);
            #$edvPlaceholder = trim($_POST['edv']);
            $uePlaceholder = trim($_POST['ue']);
            $GruppePlaceholder = trim($_POST['gruppe']);
            $vertragPlaceholder = trim($_POST['vertrag']);
            $urlaubPlaceholder = trim($_POST['urlaub']);
            $RollenPlaceholder = $_POST['nutzergruppen'];
            if(isset($_POST['free_days'])){
                $freieTagePlaceholder = $_POST['free_days'];
            }

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

            if(empty($vertragPlaceholder)){
                $vertragErr = "Bitte geben Sie den Vertragsumfang des/r neuen Nutzer/in an!";
                $DAUcheck++;
            }

            if(!is_numeric($vertragPlaceholder)){
                $vertragErr = "Der Vertragsumfang darf nur aus Zahlen bestehen!";
                $DAUcheck++;
            }

            if($vertragPlaceholder<0){
                $vertragErr = "Der Vertragsumfang muss mindestens 1% betragen!";
                $DAUcheck++;
            }

            if($vertragPlaceholder>100){
                $vertragErr = "Der Vertragsumfang darf höchstens 100% betragen!";
                $DAUcheck++;
            }

            if(empty($urlaubPlaceholder)){
                $urlaubErr = "Bitte geben Sie die Menge jährlicher Urlaubstage des/r neuen Nutzer/in an!";
                $DAUcheck++;
            }

            if(!is_numeric($urlaubPlaceholder)){
                $urlaubErr = "Die Menge jährlicher Urlaubstage darf nur aus Zahlen bestehen!";
                $DAUcheck++;
            }

            if($vertragPlaceholder<0){
                $vertragErr = "Mitarbeiter/innen benötigen mindestens einen Urlaubstag im Jahr!";
                $DAUcheck++;
            }

            if($vertragPlaceholder>100){
                $vertragErr = "Der Urlaubsumfang darf höchstens 100 Tage betragen!";
                $DAUcheck++;
            }

            if(empty($mailPlaceholder)){
                $mailErr = "Bitte geben Sie eine Mailadresse des/r Nutzer/in an!";
                $DAUcheck++;
            }

            if(!filter_var($mailPlaceholder, FILTER_VALIDATE_EMAIL)){
                $mailErr = "Bitte geben Sie eine gültige Mailadresse des/r Nutzer/in an!";
                $DAUcheck++;
            }

            #if(empty($edvPlaceholder)){
             #   $edvErr = "Bitte geben Sie einen EDV-Login des/r Nutzer/in an!";
              #  $DAUcheck++;
            #}

            if(empty($GruppePlaceholder)){
                $gruppeErr = "Bitte wählen Sie die Mitarbeitergruppe des/r Nutzer/in an!";
                $DAUcheck++;
            }

            if(empty($uePlaceholder)){
                $ueErr = "Bitte weisen Sie die/den Nutzer/in einer primären Untereinheit zu!";
                $DAUcheck++;
            }

            if(sizeof($RollenPlaceholder)==0){
                $rollenErr = "Bitte wählen Sie mindestens eine Systemrolle des/r Nutzer/in aus!";
                $DAUcheck++;
            }

            // check if user with identical unique data exists
            foreach ($AllUsers as $User){

                // Catch duplications of key-user information
                if($User['id']!=$FoundUser['id']){
                    if($mitarbeiternummerPlaceholder==$User['mitarbeiternummer']){
                        $mitarbeiternummerErr = "Ein/e andere/r Nutzer/in mit dieser Mitarbeiternummer existiert bereits!";
                        $DAUcheck++;
                    }

                    if($mailPlaceholder==$User['mail']){
                        $mailErr = "Ein/e andere/r Nutzer/in mit dieser Mailadresse existiert bereits!";
                        $DAUcheck++;
                    }

                    #if($edvPlaceholder==$User['username']){
                     #   $edvErr = "Ein/e andere/r Nutzer/in mit diesem EDV-Login existiert bereits!";
                      #  $DAUcheck++;
                    #}
                }

            }

            // Add user
            if($DAUcheck==0) {

                // encode select multiple items
                if(is_array($RollenPlaceholder)){
                    $RollenPlaceholder = implode(',',$RollenPlaceholder);
                }

                if(is_array($freieTagePlaceholder)){
                    $freieTagePlaceholder = implode(',',$freieTagePlaceholder);
                }

                $Result = edit_user($SelectedUserID, $vornamePlaceholder, $nachnamePlaceholder, $uePlaceholder, $mitarbeiternummerPlaceholder, $mailPlaceholder, $GruppePlaceholder, $RollenPlaceholder, $vertragPlaceholder, $urlaubPlaceholder, $freieTagePlaceholder);
                if($Result['success']){
                    $ReturnMessage = "Nutzer/in ".$vornamePlaceholder." ".$nachnamePlaceholder." erfolgreich bearbeitet!";
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
            #$FormHTML .= form_group_input_text('EDV-Kürzel', 'edv', $edvPlaceholder, true, $edvErr);
            $FormHTML .= form_group_input_text('Vertragsumfang in %', 'vertrag', $vertragPlaceholder, true, $vertragErr);
            $FormHTML .= form_group_dorpdown_arbeitstage('Arbeitsfreie Tage', 'free_days[]', $freieTagePlaceholder, false);
            $FormHTML .= form_hidden_input_generator('plchldr1', '1');
            $FormHTML .= form_group_input_text('Urlaubstage', 'urlaub', $urlaubPlaceholder, true, $urlaubErr);
            $FormHTML .= form_group_dropdown_mitarbeitertypen('Mitarbeitergruppe', 'gruppe', $GruppePlaceholder, false, $gruppeErr, false);
            $FormHTML .= form_group_dropdown_unterabteilungen('Gehört primär zu Unterabteilung', 'ue', $uePlaceholder, true, $ueErr, false);
            $FormHTML .= form_hidden_input_generator('user_id', $SelectedUserID);

            if($admin) {
                $FormHTML .= form_group_dropdown_toolrollen('Tool-Rollen', 'nutzergruppen[]', $RollenPlaceholder, false, $rollenErr, false);
            } else {
                $RollenPlaceholder = implode(',',$RollenPlaceholder);
                $FormHTML .= form_hidden_input_generator('nutzergruppen[]', $RollenPlaceholder);
            }
            $FormHTML .= form_group_continue_return_buttons(true, 'Bearbeiten', 'edit_user_action', 'btn-primary', true, 'Zurück', 'workforcemanagement_go_back', 'btn-primary');

            // Gap it
            $FormHTML = grid_gap_generator($FormHTML);
            $FORM = form_builder($FormHTML, 'self', 'POST');
            return card_builder('Mitarbeiter/in '.$MitarbeiterName.' bearbeiten','', $FORM);
        }else{
            $FormHTML = form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'workforcemanagement_go_back', 'btn-primary');
            $FORM = form_builder($FormHTML, 'self', 'POST');
            return card_builder('Mitarbeiter/in '.$MitarbeiterName.' bearbeiten',$ReturnMessage, $FORM);
        }

    }

}