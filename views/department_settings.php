<?php

function table_management_department_events($mysqli){

    // deal with stupid "" and '' problems
    $bla = '"{"key": "value"}"';
    $Events = get_sorted_list_of_all_department_events($mysqli);
    $userList = get_sorted_list_of_all_users($mysqli);

    //Sort into Active and Passed Events
    $ActiveEvents = [];
    $PassedEventsThisYear = [];
    $PassedEventsOlderThanThisYear = [];
    $Now = time();
    $FirstDayThisYear = strtotime(date('Y').'01-01 00:00:01');
    foreach ($Events as $event){
        $EndOfCurrentEvent = strtotime($event['end']);
        if($EndOfCurrentEvent<$Now){
            $ActiveEvents[] = $event;
        } else {
            if($EndOfCurrentEvent>$FirstDayThisYear){
                $PassedEventsThisYear[] = $event;
            } else {
                $PassedEventsOlderThanThisYear[] = $event;
            }
        }
    }

    // Initialize Table Active Stuff
    $HTML = '<table data-toggle="table" 
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
    foreach ($ActiveEvents as $activeEvent) {
        // Build edit/delete Buttons
        $Options = '';
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
        $Options = '';
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

function calculate_department_events_table_cell($ThisDay, $AllDepartmentEvents, $AllUsers){

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
        $ItemInfos = "(".$Numerator.") ".$RelevantItems['name'].": ".$RelevantItems['details'];
        $TooltipContent .= $ItemInfos;
        if($Counter > 0){
            $TooltipContent .= '---';
        }
        $Counter++;
    }

    if($Counter > 0){
        $ToolTip = '<a href="#" data-bs-toggle="tooltip" data-bs-html="true" title="'.$TooltipContent.'"><i class="bi bi-megaphone-fill bi-xs"></i></a>';
    } else {
        $ToolTip = "";
    }

    return "<td>".$ToolTip."</td>";
}