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

            // Build edit/delete Buttons
            if(user_can_edit_dienstwunsch($mysqli, $Nutzerrollen, $Wunsch)){
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

function wunschdienstplan_funktionsbuttons_user($Year){

    $FORMhtml = '<div class="row">';
    $FORMhtml .= "<div class='col'>".form_dropdown_years('year', $Year)."</div>";
    $FORMhtml .= "<div class='col'>".form_group_continue_return_buttons(true, 'Reset', 'reset_calendar', 'btn-primary', true, 'Zeitraum wählen', 'action_change_date', 'btn-primary')."</div>";
    $FORMhtml .= "</div>";

    $HTML = container_builder(form_builder($FORMhtml, 'self', 'POST'));

    return $HTML;

}

function wunschdienstplan_uebersicht_kalender_user($Year){

    return "<h3>Hier entsteht eine Jahreskalender-Ansicht der DP-Wünsche der User.</h3>";

}