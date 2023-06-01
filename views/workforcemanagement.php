<?php

function table_workforce_management($mysqli,$Admin=false,$ShowInactive=false){

    // deal with stupid "" and '' problems
    $bla = '"{"key": "value"}"';

    if($ShowInactive){
        $Users = get_sorted_list_of_all_users($mysqli, 'nachname ASC', true);
    } else {
        $Users = get_sorted_list_of_all_users($mysqli, 'nachname ASC', false, date('Y-m-d'));
    }

    // Setup Toolbar
    $HTML = '<div id="toolbar">';
    $HTML .= '<a id="add_user" class="btn btn-primary" href="workforce_management.php?mode=add_user"><i class="bi bi-person-fill-add"></i> Hinzufügen</a>';
    if(!$ShowInactive){
        $HTML .= ' <a id="show_inactive" class="btn btn-primary" href="workforce_management.php?inactive=show"><i class="bi bi-eye-fill"></i> zeige inaktive MitarbeiterInnen</a>';
    } else {
        $HTML .= ' <a id="show_inactive" class="btn btn-primary" href="workforce_management.php?inactive=hide"><i class="bi bi-eye-slash-fill"></i> inaktive MitarbeiterInnen ausblenden</a>';
    }
    #if($Admin){$HTML .= ' <a id="bulk_pswd_rst" class="btn btn-outline-danger" href="workforce_management.php?mode=bulk_user_pswd_rst"><i class="bi bi-arrow-counterclockwise"></i> Alle Passwörter zurücksetzen</a>';}
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

        $vornamePlaceholder = htmlspecialchars(trim($_POST['vorname']));
        $nachnamePlaceholder = htmlspecialchars(trim($_POST['nachname']));
        $mitarbeiternummerPlaceholder = htmlspecialchars(trim($_POST['mitarbeiternummer']));
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
    $vornameErr = $nachnameErr = $mitarbeiternummerErr = $mailErr = $inactiveErr = $edvErr = $ueErr = $gruppeErr = $urlaubErr = $vertragErr = $rollenErr = $sondereinteilungenErr = "";
    $DAUcheck = 0;
    $UserCounter = 0;
    $FoundUser = [];
    $DAUerr = "";

    if(isset($_POST['edit_user_action'])){
        $SelectedUserID = $_POST['user_id'];
    } elseif(isset($_POST['abort_user_sondereinteilung_action'])){
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
        $InactiveDatePlaceholder = date("Y-m-d", strtotime($FoundUser['inaktiv_seit']));
        $SondereinteilungenPlaceholder = get_user_depmnt_assignments($mysqli, $SelectedUserID, false);
        $DienstgruppenPlaceholder = get_user_dienstgruppen_zugehoerigkeiten($mysqli, $SelectedUserID, false);
        $RollenPlaceholder = explode(',',$FoundUser['nutzergruppen']);
        $freieTagePlaceholder = explode(',', $FoundUser['freie_tage']);

        // Parser
        if(isset($_POST['edit_user_action'])){

            $vornamePlaceholder = htmlspecialchars(trim($_POST['vorname']));
            $nachnamePlaceholder = htmlspecialchars(trim($_POST['nachname']));
            $mitarbeiternummerPlaceholder = htmlspecialchars(trim($_POST['mitarbeiternummer']));
            $mailPlaceholder = trim($_POST['mail']);
            #$edvPlaceholder = trim($_POST['edv']);
            $uePlaceholder = trim($_POST['ue']);
            $GruppePlaceholder = trim($_POST['gruppe']);
            $vertragPlaceholder = trim($_POST['vertrag']);
            $urlaubPlaceholder = trim($_POST['urlaub']);
            $RollenPlaceholder = $_POST['nutzergruppen'];
            $InactiveDatePlaceholder = $_POST['inaktive_date'];
            #$SondereinteilungenPlaceholder = $_POST['sondereinteilungen'];
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

            if(strtotime($InactiveDatePlaceholder)!=strtotime('')){
                if(!$admin){
                    if(time()>strtotime($InactiveDatePlaceholder)){
                        $inactiveErr = "Ein/e Mitarbeiter/in kann nicht nachträglich, sondern nur geplant inaktiviert werden! Bitte kontaktieren Sie den Administrator oder überprüfen die Eingabe.";
                        $DAUcheck++;
                    }
                }
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

                if(strtotime($InactiveDatePlaceholder)==strtotime('')){
                    $InactiveDatePlaceholder = '';
                } else {
                    $InactiveDatePlaceholder = $InactiveDatePlaceholder." 00:00:01";
                }

                $Result = edit_user($SelectedUserID, $vornamePlaceholder, $nachnamePlaceholder, $uePlaceholder, $mitarbeiternummerPlaceholder, $mailPlaceholder, $GruppePlaceholder, $RollenPlaceholder, $vertragPlaceholder, $urlaubPlaceholder, $freieTagePlaceholder, false, $InactiveDatePlaceholder);
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

            // UE Sondereinteilung
            $FormHTML .= form_group_dropdown_sondereinteilungen_unterabteilungen('Sondereinteilungen', 'sondereinteilungen[]', $SondereinteilungenPlaceholder, false, $sondereinteilungenErr, false);
            if(sizeof($SondereinteilungenPlaceholder)>0){
                $FormHTML .= form_group_sondereinteilungen_buttons(true, 'Hinzufügen', 'add_user_sondereinteilung_action', 'btn-primary', true, 'Bearbeiten', 'edit_user_sondereinteilung_action', 'btn-primary', true, 'Löschen', 'delete_user_sondereinteilung_action', 'btn-danger');
            } else {
                $FormHTML .= form_group_sondereinteilungen_buttons(true, 'Hinzufügen', 'add_user_sondereinteilung_action', 'btn-primary', false, 'Bearbeiten', 'edit_user_sondereinteilung_action', 'btn-primary', false, 'Löschen', 'delete_user_sondereinteilung_action', 'btn-danger');
            }

            // Dienstgruppenzugehörigkeit
            $FormHTML .= form_group_dropdown_dienstgruppenzugehörigkeiten('Zugehörigkeiten Dienstgruppen', 'dienstgruppen', $DienstgruppenPlaceholder, false, '', false);
            if(sizeof($DienstgruppenPlaceholder)>0){
                $FormHTML .= form_group_sondereinteilungen_buttons(true, 'Hinzufügen', 'add_user_dienstgruppe_action', 'btn-primary', true, 'Bearbeiten', 'edit_user_dienstgruppe_action', 'btn-primary', true, 'Löschen', 'delete_user_dienstgruppe_action', 'btn-danger');
            } else {
                $FormHTML .= form_group_sondereinteilungen_buttons(true, 'Hinzufügen', 'add_user_dienstgruppe_action', 'btn-primary', false, 'Bearbeiten', 'edit_user_dienstgruppe_action', 'btn-primary', false, 'Löschen', 'delete_user_dienstgruppe_action', 'btn-danger');
            }

            // User Inactivation
            $FormHTML .= form_hidden_input_generator('user_id', $SelectedUserID);
            $FormHTML .= form_group_input_date('Mitarbeiterin inaktiv ab', 'inaktive_date', $InactiveDatePlaceholder, true, $inactiveErr);

            // Admin Mode Controls
            if($admin) {
                $FormHTML .= form_group_dropdown_toolrollen('Tool-Rollen', 'nutzergruppen[]', $RollenPlaceholder, false, $rollenErr, false);
            } else {
                $RollenPlaceholder = implode(',',$RollenPlaceholder);
                $FormHTML .= form_hidden_input_generator('nutzergruppen[]', $RollenPlaceholder);
            }

            // Action Buttons
            $FormHTML .= form_group_continue_return_buttons(true, 'Bearbeiten', 'edit_user_action', 'btn-primary', true, 'Zurück', 'workforcemanagement_go_back', 'btn-primary');
            if(LOGINMODE != 'OIDC'){
                $FormHTML .= form_group_continue_return_buttons(true, 'Passwort zurücksetzen', 'reset_user_password_action', 'btn-danger', false, 'Zurück', 'workforcemanagement_go_back', 'btn-primary');
            }

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

function reset_user_password_workforce_management($mysqli){

    // Initialize Placeholder & Error Variables
    $FormHTML = "";
    $OutputMode = "show_form";
    $DAUcheck = $UserCounter = 0;
    $DAUerr = "";
    $FoundUser = [];

    if(isset($_POST['reset_user_password_action'])){
        $SelectedUserID = $_POST['user_id'];
    } elseif(isset($_POST['reset_user_password_action_action'])){
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
        return card_builder('Passwort Mitarbeiter/in zurücksetzen',$DAUerr, $FORM);
    } else {

        if(isset($_POST['reset_user_password_action_action'])){

            if(isset($_POST['reset_user_password_sendmail'])){
                $SendMail = true;
            } else {
                $SendMail = false;
            }

            $Result = reset_user_password($mysqli, $FoundUser, $SendMail);
            if($Result['success']){
                $ReturnMessage = "Passwort der Nutzer/in ".$MitarbeiterName." erfolgreich zurückgesetzt!<br>Das neue temporäre Passwort lautet:<b>".$Result['pass']."</b>";
            } else {
                $ReturnMessage = $Result['err'];
                $DAUcheck++;
            }

            //Set Output mode
            if($DAUcheck==0){
                $OutputMode = "return_card";
            }
        }

        if($OutputMode=="show_form"){
            $FormHTML .= form_hidden_input_generator('user_id', $_POST['user_id']);
            $FormHTML .= form_group_switch('Mail an Mitarbeiter/in senden', 'reset_user_password_sendmail', true);
            $FormHTML .= form_group_continue_return_buttons(true, 'Zurücksetzen', 'reset_user_password_action_action', 'btn-danger', true, 'Abbrechen', 'workforcemanagement_go_back', 'btn-primary');

            // Gap it
            $FormHTML = grid_gap_generator($FormHTML);
            $FORM = form_builder($FormHTML, 'self', 'POST');
            return card_builder('Passwort Mitarbeiter/in '.$MitarbeiterName.' zurücksetzen','Möchten Sie das Passwort wirklich zurücksetzen?<br>Optional kann das neue Passwort der/dem Mitarbeiter/in automatisch per Mail zugesendet werden.', $FORM);
        } else {
            $FormHTML = form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'workforcemanagement_go_back', 'btn-primary');
            $FORM = form_builder($FormHTML, 'self', 'POST');
            return card_builder('Passwort Mitarbeiter/in '.$MitarbeiterName.' zurücksetzen',$ReturnMessage, $FORM);
        }
    }

}

function add_user_sondereinteilung_management($mysqli){

    $UserInfos = get_current_user_infos($mysqli, $_POST['user_id']);
	$UserAssignments = get_user_depmnt_assignments($mysqli, $_POST['user_id']);

    #Initialize Placeholders & DAU handling
    $DAUcount = 0;
    $ShowReturn = false;
    $ReturnMessage = '';
    $uePlaceholder = $beginPlaceholder = $endPlaceholder = $commentPlaceholder = $ueErr = $beginErr = $endErr = "";

    # Populate Placeholders if action is clicked
    if(isset($_POST['add_user_sondereinteilung_action_action'])){
        $uePlaceholder = $_POST['ue_chosen'];
        $beginPlaceholder = $_POST['begin'];
        $endPlaceholder = $_POST['end'];
        $commentPlaceholder = htmlspecialchars($_POST['comment']);

		# Do some checks
		if(strtotime($endPlaceholder)<strtotime($beginPlaceholder)){
			$DAUcount++;
			$beginErr = $endErr = "Das Enddatum darf nicht vor dem Beginn der Sondereinteilung liegen!";
		}

		$Catches = 0;
		foreach ($UserAssignments as $assignment){
            $Catch=true;
            //Check non-overlap cases
            //Case 1: Begin and End of Item are smaller than end of new assignment
            if((strtotime($assignment['begin'])<strtotime($beginPlaceholder)) && (strtotime($assignment['end'])<strtotime($beginPlaceholder))){
                $Catch=false;
            }
            //Case 2: Begin and End of Item are bigger than end of new assignment
            if((strtotime($assignment['begin'])>$endPlaceholder) && (strtotime($assignment['end'])>$endPlaceholder)){
                $Catch=false;
            }

            if($Catch){
                $Catches++;
            }

        }

		if($Catches>0){
			$beginErr = "Im ausgewähltem Zeitraum liegt bereits eine Sondereinteilung für den/die Mitarbeiterin vor!";
			$DAUcount++;
		}

        if($DAUcount==0){
            # Add entry to db
            $ReturnVals = add_user_sondereinteilung($mysqli, $_POST['user_id'], $uePlaceholder, $beginPlaceholder, $endPlaceholder, $commentPlaceholder);
            if($ReturnVals['success']){
                $ShowReturn = true;
                $ReturnMessage = "Sondereinteilung erfolgreich angelegt!";
            } else {
                $ShowReturn = false;
                $ReturnMessage = $ReturnVals['err'];
            }
        }
    }

    if($ShowReturn){
        $FormHTML = form_hidden_input_generator('user_id', $_POST['user_id']);
        $FormHTML .= form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'abort_user_sondereinteilung_action', 'btn-primary');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');

        return card_builder('Sondereinteilung für '.$UserInfos['vorname'].' '.$UserInfos['nachname'].' anlegen',$ReturnMessage, $FORM);
    }else{
        #Build the form
        $FormHTML = form_hidden_input_generator('user_id', $_POST['user_id']);
        $FormHTML .= form_group_dropdown_unterabteilungen('Unterabteilung', 'ue_chosen', $uePlaceholder, true, $ueErr, false, 'Unterabteilung wählen');
        $FormHTML .= form_group_input_date('Beginn', 'begin', $beginPlaceholder, true, $beginErr, false);
        $FormHTML .= form_group_input_date('Ende', 'end', $endPlaceholder, true, $endErr, false);
        $FormHTML .= form_group_input_text('Kommentar (optional)', 'comment', $commentPlaceholder, false, '', false);
        $FormHTML .= form_group_continue_return_buttons(true, 'Anlegen', 'add_user_sondereinteilung_action_action', 'btn-primary', true, 'Abbrechen', 'abort_user_sondereinteilung_action', 'btn-danger');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');

        return card_builder('Sondereinteilung für '.$UserInfos['vorname'].' '.$UserInfos['nachname'].' anlegen','', $FORM);
    }

}

function edit_user_sondereinteilung_management($mysqli){

    $UserInfos = get_current_user_infos($mysqli, $_POST['user_id']);
    $AssignmentID = $_POST['sondereinteilungen'];
    $AssignmentInfos = get_dept_assignment_info($mysqli,$AssignmentID);
    $UserAssignments = get_user_depmnt_assignments($mysqli, $_POST['user_id'], false, $AssignmentID);

    // Define Placeholders
    $uePlaceholder = $AssignmentInfos['department'];
    $beginPlaceholder = $AssignmentInfos['begin'];
    $endPlaceholder = $AssignmentInfos['end'];
    $commentPlaceholder = htmlspecialchars($AssignmentInfos['create_comment']);
    $ueErr = $beginErr = $endErr = '';
    $DAUcount = 0;
    $ShowReturn = false;

    if(isset($_POST['edit_user_sondereinteilung_action_action'])){
        $uePlaceholder = $_POST['ue_chosen'];
        $beginPlaceholder = $_POST['begin'];
        $endPlaceholder = $_POST['end'];
        $commentPlaceholder = htmlspecialchars($_POST['comment']);

        # Do some checks
        if(strtotime($endPlaceholder)<strtotime($beginPlaceholder)){
            $DAUcount++;
            $beginErr = $endErr = "Das Enddatum darf nicht vor dem Beginn der Sondereinteilung liegen!";
        }

        $Catches = 0;
        foreach ($UserAssignments as $assignment){
            $Catch=true;
            //Check non-overlap cases
            //Case 1: Begin and End of Item are smaller than end of new assignment
            if((strtotime($assignment['begin'])<strtotime($beginPlaceholder)) && (strtotime($assignment['end'])<strtotime($beginPlaceholder))){
                $Catch=false;
            }
            //Case 2: Begin and End of Item are bigger than end of new assignment
            if((strtotime($assignment['begin'])>strtotime($endPlaceholder)) && (strtotime($assignment['end'])>strtotime($endPlaceholder))){
                $Catch=false;
            }

            if($Catch){
                $Catches++;
            }

        }

        if($Catches>0){
            $beginErr = "Im ausgewähltem Zeitraum liegt bereits eine Sondereinteilung für den/die Mitarbeiterin vor!";
            $DAUcount++;
        }

        if($DAUcount==0){
            $ReturnVals = edit_user_sondereinteilung($mysqli, $AssignmentID, $beginPlaceholder, $endPlaceholder, $commentPlaceholder);
            if($ReturnVals['success']){
                $ShowReturn = true;
                $ReturnMessage = "Sondereinteilung erfolgreich bearbeitet!";
            } else {
                $ShowReturn = false;
                $ReturnMessage = $ReturnVals['err'];
            }
        }
    }

    if($ShowReturn){
        $FormHTML = form_hidden_input_generator('user_id', $_POST['user_id']);
        $FormHTML .= form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'abort_user_sondereinteilung_action', 'btn-primary');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');

        return card_builder('Sondereinteilung für '.$UserInfos['vorname'].' '.$UserInfos['nachname'].' bearbeiten',$ReturnMessage, $FORM);
    }else {
        #Build the form
        $FormHTML = form_hidden_input_generator('user_id', $_POST['user_id']);
        $FormHTML .= form_hidden_input_generator('sondereinteilungen', $AssignmentID);
        $FormHTML .= form_hidden_input_generator('ue_chosen', $uePlaceholder);
        $FormHTML .= form_group_dropdown_unterabteilungen('Unterabteilung', 'ue_chosen', $uePlaceholder, true, $ueErr, true, 'Unterabteilung wählen');
        $FormHTML .= form_group_input_date('Beginn', 'begin', $beginPlaceholder, true, $beginErr, false);
        $FormHTML .= form_group_input_date('Ende', 'end', $endPlaceholder, true, $endErr, false);
        $FormHTML .= form_group_input_text('Kommentar (optional)', 'comment', $commentPlaceholder, false, '', false);
        $FormHTML .= form_group_continue_return_buttons(true, 'Bearbeiten', 'edit_user_sondereinteilung_action_action', 'btn-primary', true, 'Abbrechen', 'abort_user_sondereinteilung_action', 'btn-danger');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');

        return card_builder('Sondereinteilung für ' . $UserInfos['vorname'] . ' ' . $UserInfos['nachname'] . ' bearbeiten', '', $FORM);
    }
}

function delete_user_sondereinteilung_management($mysqli){

    $UserInfos = get_current_user_infos($mysqli, $_POST['user_id']);
    $AssignmentID = $_POST['sondereinteilungen'];
    $AssignmentInfos = get_dept_assignment_info($mysqli,$AssignmentID);

    // Define Placeholders
    $uePlaceholder = $AssignmentInfos['department'];
    $beginPlaceholder = $AssignmentInfos['begin'];
    $endPlaceholder = $AssignmentInfos['end'];
    $commentPlaceholder = htmlspecialchars($AssignmentInfos['create_comment']);
    $ueErr = $beginErr = $endErr = "";
    $DAUcount = 0;
    $ShowReturn = false;

    if(isset($_POST['delete_user_sondereinteilung_action_action'])){
        if($DAUcount==0){
            $ReturnVals = delete_user_sondereinteilung($mysqli, $AssignmentID, $beginPlaceholder, $endPlaceholder, $commentPlaceholder);
            if($ReturnVals['success']){
                $ShowReturn = true;
                $ReturnMessage = "Sondereinteilung erfolgreich gelöscht!";
            } else {
                $ShowReturn = true;
                $ReturnMessage = $ReturnVals['err'];
            }
        }
    }

    if($ShowReturn){
        $FormHTML = form_hidden_input_generator('user_id', $_POST['user_id']);
        $FormHTML .= form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'abort_user_sondereinteilung_action', 'btn-primary');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');

        return card_builder('Sondereinteilung für '.$UserInfos['vorname'].' '.$UserInfos['nachname'].' löschen',$ReturnMessage, $FORM);
    } else {
        #Build the form
        $FormHTML = form_hidden_input_generator('user_id', $_POST['user_id']);
        $FormHTML .= form_hidden_input_generator('sondereinteilungen', $AssignmentID);
        $FormHTML .= form_hidden_input_generator('ue_chosen', $uePlaceholder);
        $FormHTML .= form_group_dropdown_unterabteilungen('Unterabteilung', 'ue_chosen', $uePlaceholder, true, $ueErr, true, 'Unterabteilung wählen');
        $FormHTML .= form_group_input_date('Beginn', 'begin', $beginPlaceholder, true, $beginErr, true);
        $FormHTML .= form_group_input_date('Ende', 'end', $endPlaceholder, true, $endErr, true);
        $FormHTML .= form_group_input_text('Bisherige Kommentare', 'comment', $commentPlaceholder, false, '', true);
        $FormHTML .= form_group_input_text('Kommentare zum Löschen (optional)', 'delete_comment', '', false, '', false);
        $FormHTML .= form_group_continue_return_buttons(true, 'Löschen', 'delete_user_sondereinteilung_action_action', 'btn-primary', true, 'Abbrechen', 'abort_user_sondereinteilung_action', 'btn-danger');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');

        return card_builder('Sondereinteilung für ' . $UserInfos['vorname'] . ' ' . $UserInfos['nachname'] . ' löschen', '', $FORM);

    }
}

function add_user_dienstgruppe_management($mysqli){

    $UserInfos = get_current_user_infos($mysqli, $_POST['user_id']);
    $UserAssignments = get_user_dienstgruppen_zugehoerigkeiten($mysqli, $_POST['user_id']);

    #Initialize Placeholders & DAU handling
    $DAUcount = 0;
    $ShowReturn = false;
    $ReturnMessage = '';
    $dgPlaceholder = $beginPlaceholder = $endPlaceholder = $commentPlaceholder = $dgErr = $beginErr = $endErr = "";

    # Populate Placeholders if action is clicked
    if(isset($_POST['add_user_dienstgruppe_action_action'])){
        $dgPlaceholder = $_POST['dg_chosen'];
        $beginPlaceholder = $_POST['begin'];
        $endPlaceholder = $_POST['end'];
        if(empty($endPlaceholder)){
            $endPlaceholder = "2099-12-31";
        }
        $commentPlaceholder = htmlspecialchars($_POST['comment']);

        # Do some checks
        if(strtotime($endPlaceholder)<strtotime($beginPlaceholder)){
            $DAUcount++;
            $beginErr = $endErr = "Das Enddatum darf nicht vor dem Beginn der Dienstgruppenzuteilung liegen!";
        }

        $Catches = 0;
        foreach ($UserAssignments as $assignment){
            if($dgPlaceholder==$assignment['bd_type']){
                $Catches++;
            }
        }

        if($Catches>0){
            $beginErr = "Der/die Mitarbeiter/in ist bereits dieser Dienstgruppe zugeteilt worden!";
            $DAUcount++;
        }

        if($DAUcount==0){
            # Add entry to db
            $ReturnVals = add_user_dienstgruppe_zugehoerigkeit($mysqli, $_POST['user_id'], $dgPlaceholder, $beginPlaceholder, $endPlaceholder, $commentPlaceholder);
            if($ReturnVals['success']){
                $ShowReturn = true;
                $ReturnMessage = "Dienstgruppenzugehörigkeit erfolgreich angelegt!";
            } else {
                $ShowReturn = false;
                $ReturnMessage = $ReturnVals['err'];
            }
        }
    }

    if($ShowReturn){
        $FormHTML = form_hidden_input_generator('user_id', $_POST['user_id']);
        $FormHTML .= form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'abort_user_sondereinteilung_action', 'btn-primary');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');

        return card_builder('Dienstgruppenzugehörigkeit für '.$UserInfos['vorname'].' '.$UserInfos['nachname'].' anlegen',$ReturnMessage, $FORM);
    }else{
        #Build the form
        $FormHTML = form_hidden_input_generator('user_id', $_POST['user_id']);
        $FormHTML .= form_group_dropdown_dienstgruppen('Dienstgruppe', 'dg_chosen', $dgPlaceholder, true, $dgErr, false, 'Dienstgruppe wählen');
        $FormHTML .= form_group_input_date('Beginn', 'begin', $beginPlaceholder, true, $beginErr, false);
        $FormHTML .= form_group_input_date('Ende (optional)', 'end', $endPlaceholder, true, $endErr, false);
        $FormHTML .= form_group_input_text('Kommentar (optional)', 'comment', $commentPlaceholder, false, '', false);
        $FormHTML .= form_group_continue_return_buttons(true, 'Anlegen', 'add_user_dienstgruppe_action_action', 'btn-primary', true, 'Abbrechen', 'abort_user_sondereinteilung_action', 'btn-danger');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');

        return card_builder('Dienstgruppenzugehörigkeit für '.$UserInfos['vorname'].' '.$UserInfos['nachname'].' anlegen','', $FORM);
    }

}

function edit_user_dienstgruppe_management($mysqli){

    $UserInfos = get_current_user_infos($mysqli, $_POST['user_id']);
    $AssignmentID = $_POST['dienstgruppen'];
    $AssignmentInfos = get_bd_assignment_info($mysqli,$AssignmentID);
    $UserAssignments = get_user_dienstgruppen_zugehoerigkeiten($mysqli, $_POST['user_id'], false, $AssignmentID);

    // Define Placeholders
    $bdPlaceholder = $AssignmentInfos['bd_type'];
    $beginPlaceholder = $AssignmentInfos['begin'];
    $endPlaceholder = $AssignmentInfos['end'];
    $commentPlaceholder = htmlspecialchars($AssignmentInfos['create_comment']);
    $bdErr = $beginErr = $endErr = '';
    $DAUcount = 0;
    $ShowReturn = false;

    if(isset($_POST['edit_user_dienstgruppe_action_action'])){
        $bdPlaceholder = $_POST['bd_chosen'];
        $beginPlaceholder = $_POST['begin'];
        $endPlaceholder = $_POST['end'];
        $commentPlaceholder = htmlspecialchars($_POST['comment']);

        # Do some checks
        if(strtotime($endPlaceholder)<strtotime($beginPlaceholder)){
            $DAUcount++;
            $beginErr = $endErr = "Das Enddatum darf nicht vor dem Beginn der Dienstgruppenzugehörigkeit liegen!";
        }

        $Catches = 0;
        foreach ($UserAssignments as $assignment){
            if($bdPlaceholder==$assignment['id']){
                $Catches++;
            }
        }

        if($Catches>0){
            $beginErr = "Der/die Mitarbeiter/in ist bereits dieser Dienstgruppe zugeteilt worden!";
            $DAUcount++;
        }

        if($DAUcount==0){
            $ReturnVals = edit_user_dienstgruppe_zugehoerigkeit($mysqli, $AssignmentID, $beginPlaceholder, $endPlaceholder, $commentPlaceholder);
            if($ReturnVals['success']){
                $ShowReturn = true;
                $ReturnMessage = "Dienstgruppenzugehörigkeit erfolgreich bearbeitet!";
            } else {
                $ShowReturn = false;
                $ReturnMessage = $ReturnVals['err'];
            }
        }
    }

    if($ShowReturn){
        $FormHTML = form_hidden_input_generator('user_id', $_POST['user_id']);
        $FormHTML .= form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'abort_user_sondereinteilung_action', 'btn-primary');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');

        return card_builder('Dienstgruppenzugehörigkeit für '.$UserInfos['vorname'].' '.$UserInfos['nachname'].' bearbeiten',$ReturnMessage, $FORM);
    }else {
        #Build the form
        $FormHTML = form_hidden_input_generator('user_id', $_POST['user_id']);
        $FormHTML .= form_hidden_input_generator('dienstgruppen', $AssignmentID);
        $FormHTML .= form_hidden_input_generator('bd_chosen', $bdPlaceholder);
        $FormHTML .= form_group_dropdown_dienstgruppen('Dienstgruppe', 'bd_chosen', $bdPlaceholder, true, $bdErr, true, 'Dienstgruppe wählen');
        $FormHTML .= form_group_input_date('Beginn', 'begin', $beginPlaceholder, true, $beginErr, false);
        $FormHTML .= form_group_input_date('Ende', 'end', $endPlaceholder, true, $endErr, false);
        $FormHTML .= form_group_input_text('Kommentar (optional)', 'comment', $commentPlaceholder, false, '', false);
        $FormHTML .= form_group_continue_return_buttons(true, 'Bearbeiten', 'edit_user_dienstgruppe_action_action', 'btn-primary', true, 'Abbrechen', 'abort_user_sondereinteilung_action', 'btn-danger');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');

        return card_builder('Dienstgruppenzugehörigkeit für ' . $UserInfos['vorname'] . ' ' . $UserInfos['nachname'] . ' bearbeiten', '', $FORM);
    }
}

function delete_user_dienstgruppe_management($mysqli){

    $UserInfos = get_current_user_infos($mysqli, $_POST['user_id']);
    $AssignmentID = $_POST['dienstgruppen'];
    $AssignmentInfos = get_bd_assignment_info($mysqli,$AssignmentID);

    // Define Placeholders
    $bdPlaceholder = $AssignmentInfos['bd_type'];
    $beginPlaceholder = $AssignmentInfos['begin'];
    $endPlaceholder = $AssignmentInfos['end'];
    $commentPlaceholder = htmlspecialchars($AssignmentInfos['create_comment']);
    $DeleteCommentPlaceholder = "";
    $ueErr = $beginErr = $endErr = "";
    $DAUcount = 0;
    $ShowReturn = false;

    if(isset($_POST['delete_user_dienstgruppe_action_action'])){

        $bdPlaceholder = $_POST['bd_chosen'];
        $DeleteCommentPlaceholder = htmlspecialchars($_POST['delete_comment']);

        if($DAUcount==0){
            $ReturnVals = delete_user_dienstgruppe_zugehoerigkeit($mysqli, $AssignmentID, $DeleteCommentPlaceholder);
            if($ReturnVals['success']){
                $ShowReturn = true;
                $ReturnMessage = "Dienstgruppenzugehörigkeit erfolgreich gelöscht!";
            } else {
                $ShowReturn = true;
                $ReturnMessage = $ReturnVals['err'];
            }
        }
    }

    if($ShowReturn){
        $FormHTML = form_hidden_input_generator('user_id', $_POST['user_id']);
        $FormHTML .= form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'abort_user_sondereinteilung_action', 'btn-primary');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');

        return card_builder('Dienstgruppenzugehörigkeit für '.$UserInfos['vorname'].' '.$UserInfos['nachname'].' löschen',$ReturnMessage, $FORM);
    } else {
        #Build the form
        $FormHTML = form_hidden_input_generator('user_id', $_POST['user_id']);
        $FormHTML .= form_hidden_input_generator('bd_chosen', $bdPlaceholder);
        $FormHTML .= form_hidden_input_generator('dienstgruppen', $AssignmentID);
        $FormHTML .= form_group_dropdown_dienstgruppen('Dienstgruppe', 'bd_chosen', $bdPlaceholder, true, $ueErr, true, 'Dienstgruppe wählen');
        $FormHTML .= form_group_input_date('Beginn', 'begin', $beginPlaceholder, true, $beginErr, true);
        $FormHTML .= form_group_input_date('Ende', 'end', $endPlaceholder, true, $endErr, true);
        $FormHTML .= form_group_input_text('Bisherige Kommentare', 'comment', $commentPlaceholder, false, '', true);
        $FormHTML .= form_group_input_text('Kommentare zum Löschen (optional)', 'delete_comment', '', false, '', false);
        $FormHTML .= form_group_continue_return_buttons(true, 'Löschen', 'delete_user_dienstgruppe_action_action', 'btn-primary', true, 'Abbrechen', 'abort_user_sondereinteilung_action', 'btn-danger');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');

        return card_builder('Dienstgruppenzugehörigkeit für ' . $UserInfos['vorname'] . ' ' . $UserInfos['nachname'] . ' löschen', '', $FORM);

    }
}

function add_user_sonderrule_bd_automatik_management($mysqli){

    $UserInfos = get_current_user_infos($mysqli, $_POST['user_id']);
    $UserAssignments = get_user_dienstgruppen_zugehoerigkeiten($mysqli, $_POST['user_id']);

    #Initialize Placeholders & DAU handling
    $DAUcount = 0;
    $ShowReturn = false;
    $ReturnMessage = '';
    $dgPlaceholder = $beginPlaceholder = $endPlaceholder = $commentPlaceholder = $dgErr = $beginErr = $endErr = "";

    # Populate Placeholders if action is clicked
    if(isset($_POST['add_user_dienstgruppe_action_action'])){
        $dgPlaceholder = $_POST['dg_chosen'];
        $beginPlaceholder = $_POST['begin'];
        $endPlaceholder = $_POST['end'];
        if(empty($endPlaceholder)){
            $endPlaceholder = "2099-12-31";
        }
        $commentPlaceholder = htmlspecialchars($_POST['comment']);

        # Do some checks
        if(strtotime($endPlaceholder)<strtotime($beginPlaceholder)){
            $DAUcount++;
            $beginErr = $endErr = "Das Enddatum darf nicht vor dem Beginn der Dienstgruppenzuteilung liegen!";
        }

        $Catches = 0;
        foreach ($UserAssignments as $assignment){
            if($dgPlaceholder==$assignment['bd_type']){
                $Catches++;
            }
        }

        if($Catches>0){
            $beginErr = "Der/die Mitarbeiter/in ist bereits dieser Dienstgruppe zugeteilt worden!";
            $DAUcount++;
        }

        if($DAUcount==0){
            # Add entry to db
            $ReturnVals = add_user_dienstgruppe_zugehoerigkeit($mysqli, $_POST['user_id'], $dgPlaceholder, $beginPlaceholder, $endPlaceholder, $commentPlaceholder);
            if($ReturnVals['success']){
                $ShowReturn = true;
                $ReturnMessage = "Dienstgruppenzugehörigkeit erfolgreich angelegt!";
            } else {
                $ShowReturn = false;
                $ReturnMessage = $ReturnVals['err'];
            }
        }
    }

    if($ShowReturn){
        $FormHTML = form_hidden_input_generator('user_id', $_POST['user_id']);
        $FormHTML .= form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'abort_user_sondereinteilung_action', 'btn-primary');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');

        return card_builder('Dienstgruppenzugehörigkeit für '.$UserInfos['vorname'].' '.$UserInfos['nachname'].' anlegen',$ReturnMessage, $FORM);
    }else{
        #Build the form
        $FormHTML = form_hidden_input_generator('user_id', $_POST['user_id']);
        $FormHTML .= form_group_dropdown_dienstgruppen('Dienstgruppe', 'dg_chosen', $dgPlaceholder, true, $dgErr, false, 'Dienstgruppe wählen');
        $FormHTML .= form_group_input_date('Beginn', 'begin', $beginPlaceholder, true, $beginErr, false);
        $FormHTML .= form_group_input_date('Ende (optional)', 'end', $endPlaceholder, true, $endErr, false);
        $FormHTML .= form_group_input_text('Kommentar (optional)', 'comment', $commentPlaceholder, false, '', false);
        $FormHTML .= form_group_continue_return_buttons(true, 'Anlegen', 'add_user_dienstgruppe_action_action', 'btn-primary', true, 'Abbrechen', 'abort_user_sondereinteilung_action', 'btn-danger');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');

        return card_builder('Dienstgruppenzugehörigkeit für '.$UserInfos['vorname'].' '.$UserInfos['nachname'].' anlegen','', $FORM);
    }

}

function edit_user_sonderrule_bd_automatik_management($mysqli){

    $UserInfos = get_current_user_infos($mysqli, $_POST['user_id']);
    $AssignmentID = $_POST['dienstgruppen'];
    $AssignmentInfos = get_bd_assignment_info($mysqli,$AssignmentID);
    $UserAssignments = get_user_dienstgruppen_zugehoerigkeiten($mysqli, $_POST['user_id'], false, $AssignmentID);

    // Define Placeholders
    $bdPlaceholder = $AssignmentInfos['bd_type'];
    $beginPlaceholder = $AssignmentInfos['begin'];
    $endPlaceholder = $AssignmentInfos['end'];
    $commentPlaceholder = htmlspecialchars($AssignmentInfos['create_comment']);
    $bdErr = $beginErr = $endErr = '';
    $DAUcount = 0;
    $ShowReturn = false;

    if(isset($_POST['edit_user_dienstgruppe_action_action'])){
        $bdPlaceholder = $_POST['bd_chosen'];
        $beginPlaceholder = $_POST['begin'];
        $endPlaceholder = $_POST['end'];
        $commentPlaceholder = htmlspecialchars($_POST['comment']);

        # Do some checks
        if(strtotime($endPlaceholder)<strtotime($beginPlaceholder)){
            $DAUcount++;
            $beginErr = $endErr = "Das Enddatum darf nicht vor dem Beginn der Dienstgruppenzugehörigkeit liegen!";
        }

        $Catches = 0;
        foreach ($UserAssignments as $assignment){
            if($bdPlaceholder==$assignment['id']){
                $Catches++;
            }
        }

        if($Catches>0){
            $beginErr = "Der/die Mitarbeiter/in ist bereits dieser Dienstgruppe zugeteilt worden!";
            $DAUcount++;
        }

        if($DAUcount==0){
            $ReturnVals = edit_user_dienstgruppe_zugehoerigkeit($mysqli, $AssignmentID, $beginPlaceholder, $endPlaceholder, $commentPlaceholder);
            if($ReturnVals['success']){
                $ShowReturn = true;
                $ReturnMessage = "Dienstgruppenzugehörigkeit erfolgreich bearbeitet!";
            } else {
                $ShowReturn = false;
                $ReturnMessage = $ReturnVals['err'];
            }
        }
    }

    if($ShowReturn){
        $FormHTML = form_hidden_input_generator('user_id', $_POST['user_id']);
        $FormHTML .= form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'abort_user_sondereinteilung_action', 'btn-primary');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');

        return card_builder('Dienstgruppenzugehörigkeit für '.$UserInfos['vorname'].' '.$UserInfos['nachname'].' bearbeiten',$ReturnMessage, $FORM);
    }else {
        #Build the form
        $FormHTML = form_hidden_input_generator('user_id', $_POST['user_id']);
        $FormHTML .= form_hidden_input_generator('dienstgruppen', $AssignmentID);
        $FormHTML .= form_hidden_input_generator('bd_chosen', $bdPlaceholder);
        $FormHTML .= form_group_dropdown_dienstgruppen('Dienstgruppe', 'bd_chosen', $bdPlaceholder, true, $bdErr, true, 'Dienstgruppe wählen');
        $FormHTML .= form_group_input_date('Beginn', 'begin', $beginPlaceholder, true, $beginErr, false);
        $FormHTML .= form_group_input_date('Ende', 'end', $endPlaceholder, true, $endErr, false);
        $FormHTML .= form_group_input_text('Kommentar (optional)', 'comment', $commentPlaceholder, false, '', false);
        $FormHTML .= form_group_continue_return_buttons(true, 'Bearbeiten', 'edit_user_dienstgruppe_action_action', 'btn-primary', true, 'Abbrechen', 'abort_user_sondereinteilung_action', 'btn-danger');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');

        return card_builder('Dienstgruppenzugehörigkeit für ' . $UserInfos['vorname'] . ' ' . $UserInfos['nachname'] . ' bearbeiten', '', $FORM);
    }
}

function delete_user_sonderrule_bd_automatik_management($mysqli){

    $UserInfos = get_current_user_infos($mysqli, $_POST['user_id']);
    $AssignmentID = $_POST['dienstgruppen'];
    $AssignmentInfos = get_bd_assignment_info($mysqli,$AssignmentID);

    // Define Placeholders
    $bdPlaceholder = $AssignmentInfos['bd_type'];
    $beginPlaceholder = $AssignmentInfos['begin'];
    $endPlaceholder = $AssignmentInfos['end'];
    $commentPlaceholder = htmlspecialchars($AssignmentInfos['create_comment']);
    $DeleteCommentPlaceholder = "";
    $ueErr = $beginErr = $endErr = "";
    $DAUcount = 0;
    $ShowReturn = false;

    if(isset($_POST['delete_user_dienstgruppe_action_action'])){

        $bdPlaceholder = $_POST['bd_chosen'];
        $DeleteCommentPlaceholder = htmlspecialchars($_POST['delete_comment']);

        if($DAUcount==0){
            $ReturnVals = delete_user_dienstgruppe_zugehoerigkeit($mysqli, $AssignmentID, $DeleteCommentPlaceholder);
            if($ReturnVals['success']){
                $ShowReturn = true;
                $ReturnMessage = "Dienstgruppenzugehörigkeit erfolgreich gelöscht!";
            } else {
                $ShowReturn = true;
                $ReturnMessage = $ReturnVals['err'];
            }
        }
    }

    if($ShowReturn){
        $FormHTML = form_hidden_input_generator('user_id', $_POST['user_id']);
        $FormHTML .= form_group_continue_return_buttons(false, '', '', '', true, 'Zurück', 'abort_user_sondereinteilung_action', 'btn-primary');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');

        return card_builder('Dienstgruppenzugehörigkeit für '.$UserInfos['vorname'].' '.$UserInfos['nachname'].' löschen',$ReturnMessage, $FORM);
    } else {
        #Build the form
        $FormHTML = form_hidden_input_generator('user_id', $_POST['user_id']);
        $FormHTML .= form_hidden_input_generator('bd_chosen', $bdPlaceholder);
        $FormHTML .= form_hidden_input_generator('dienstgruppen', $AssignmentID);
        $FormHTML .= form_group_dropdown_dienstgruppen('Dienstgruppe', 'bd_chosen', $bdPlaceholder, true, $ueErr, true, 'Dienstgruppe wählen');
        $FormHTML .= form_group_input_date('Beginn', 'begin', $beginPlaceholder, true, $beginErr, true);
        $FormHTML .= form_group_input_date('Ende', 'end', $endPlaceholder, true, $endErr, true);
        $FormHTML .= form_group_input_text('Bisherige Kommentare', 'comment', $commentPlaceholder, false, '', true);
        $FormHTML .= form_group_input_text('Kommentare zum Löschen (optional)', 'delete_comment', '', false, '', false);
        $FormHTML .= form_group_continue_return_buttons(true, 'Löschen', 'delete_user_dienstgruppe_action_action', 'btn-primary', true, 'Abbrechen', 'abort_user_sondereinteilung_action', 'btn-danger');

        // Gap it
        $FormHTML = grid_gap_generator($FormHTML);
        $FORM = form_builder($FormHTML, 'self', 'POST');

        return card_builder('Dienstgruppenzugehörigkeit für ' . $UserInfos['vorname'] . ' ' . $UserInfos['nachname'] . ' löschen', '', $FORM);

    }
}