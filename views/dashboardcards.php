<?php

function dashboard_view_abwesenheiten_user($mysqli, $userID, $Nutzergruppen){

    $AbwesenheitenTable = table_abwesenheiten_user($mysqli, $Nutzergruppen, $DashboardMode=true);
    return card_builder('Genehmigte Abwesenheiten dieses Jahr', '', $AbwesenheitenTable, true, 'h-100 text-center');

}

function dashboard_view_bereitschaftsdienstplan_user($mysqli, $userID, $h100Mode=true){

    $BDtable = table_bereitschaftsdienstplan_user($mysqli,$userID,date('m'),date('Y'));
    if($h100Mode){
        return card_builder('Bereitschaftsdienste diesen und nächsten Monat', '', $BDtable, true, 'h-100 text-center');
    }else{
        return card_builder('Bereitschaftsdienste diesen und nächsten Monat', '', $BDtable, true, '');
    }

}

function dashboard_view_spx_eintraege($mysqli, $h100Mode=true){

    $AllAbwesenheiten = get_sorted_list_of_all_abwesenheiten($mysqli);
    $AllUsers = get_sorted_list_of_all_users($mysqli, 'nachname ASC', true);

    //Build le table
    // deal with stupid "" and '' problems
    $bla = '"{"key": "value"}"';
    $TableRowsHTML = '';
    $a = 1;
    foreach ($AllAbwesenheiten as $abwesenheit){

        // Only accepted abwesenheiten have to be passed to SPX
        if($abwesenheit['status_bearbeitung']=='Genehmigt'){

            // Only display untransferred Entries
            if($abwesenheit['spx_entry_date']==NULL){

                $CurrentUserInfos = get_user_infos_by_id_from_list($abwesenheit['user'], $AllUsers);
                $Team = $CurrentUserInfos['nachname'].', '.$CurrentUserInfos['vorname'];

                if($abwesenheit['bearbeitet_von']==0){
                    $FreigeberUserInfos = get_user_infos_by_id_from_list($abwesenheit['create_user'], $AllUsers);
                } else {
                    $FreigeberUserInfos = get_user_infos_by_id_from_list($abwesenheit['bearbeitet_von'], $AllUsers);
                }

                $TeamFreigabe = $FreigeberUserInfos['nachname'].', '.$FreigeberUserInfos['vorname'];

                // Build rows
                //Build Option items
                $Options = '<a href="spx_management.php?mode=add_spx_entry&abwesenheit_id='.$abwesenheit['id'].'"><i class="bi bi-check-circle-fill"></i></a> ';

                //initialize Row
                if($a==1){
                    $TableRowsHTML .= '<tr id="tr-id-1" class="tr-class-1" data-title="bootstrap table" data-object='.$bla.'>';
                } else {
                    $TableRowsHTML .= '<tr id="tr-id-'.$a.'" class="tr-class-'.$a.'">';
                }

                // Build Cells
                if($a==1){
                    $TableRowsHTML .= '<td id="td-id-1" class="td-class-1" data-title="bootstrap table">'.date('d.m.Y', strtotime($abwesenheit['begin'])).'</td>';
                    $TableRowsHTML .= '<td>'.date('d.m.Y', strtotime($abwesenheit['end'])).'</td>';
                    $TableRowsHTML .= '<td> '.$Team.'</td>';
                    $TableRowsHTML .= '<td> '.$abwesenheit['type'].'</td>';
                    if($abwesenheit['bearbeitet_am']=='0000-00-00 00:00:00'){
                        $TableRowsHTML .= '<td>'.date('d.m.Y', strtotime($abwesenheit['create_date'])).'</td>';
                    } else {
                        $TableRowsHTML .= '<td>'.date('d.m.Y', strtotime($abwesenheit['bearbeitet_am'])).'</td>';
                    }
                    $TableRowsHTML .= '<td> '.$TeamFreigabe.'</td>';
                    $TableRowsHTML .= '<td>'.$Options.'</td>';
                } else {
                    $TableRowsHTML .= '<td id="td-id-'.$a.'" class="td-class-'.$a.'"">'.date('d.m.Y', strtotime($abwesenheit['begin'])).'</td>';
                    $TableRowsHTML .= '<td>'.date('d.m.Y', strtotime($abwesenheit['end'])).'</td>';
                    $TableRowsHTML .= '<td> '.$Team.'</td>';
                    $TableRowsHTML .= '<td> '.$abwesenheit['type'].'</td>';
                    if($abwesenheit['bearbeitet_am']=='0000-00-00 00:00:00'){
                        $TableRowsHTML .= '<td>'.date('d.m.Y', strtotime($abwesenheit['create_date'])).'</td>';
                    } else {
                        $TableRowsHTML .= '<td>'.date('d.m.Y', strtotime($abwesenheit['bearbeitet_am'])).'</td>';
                    }                    $TableRowsHTML .= '<td> '.$TeamFreigabe.'</td>';
                    $TableRowsHTML .= '<td>'.$Options.'</td>';

                }

                //Close Row
                $TableRowsHTML .= '</tr>';

                $a++;
            }
        }

    }

    // Initialize Table
    $HTML = '<table data-toggle="table" 
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
                    <th data-field="day" data-sortable="true">Beginn Abwesenheit</th>
                    <th data-field="type" data-sortable="true">Ende Abwesenheit</th>
                    <th>AntragsstellerIn</th>
                    <th>Antragstyp</th>
                    <th>Freigabe am</th>
                    <th>Freigabe von</th>
                    <th>Optionen</th>
                </tr>
              </thead>';

    // Fill table body
    $HTML .= '<tbody>';
    $HTML .= $TableRowsHTML;
    $HTML .= '</tbody>';
    $HTML .= '</table>';

    if($h100Mode){
        return card_builder('Fehlende SPX-Einträge - Abwesenheiten', '', $HTML, true, 'h-100 text-center');
    }else{
        return card_builder('Fehlende SPX-Einträge - Abwesenheiten', '', $HTML, true, '');
    }

}