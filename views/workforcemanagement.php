<?php

function table_workforce_management($mysqli){

    // deal with stupid "" and '' problems
    $Mitarbeiters = [];
    $Mitarbeiters[] = array("nachname" => "Haefeker", "vorname" => "Marc", "mitarbeiternummer" => "123");
    $Mitarbeiters[] = array("nachname" => "Drexler", "vorname" => "Berthold", "mitarbeiternummer" => "456");
    $Mitarbeiters[] = array("nachname" => "Fideler", "vorname" => "Frank", "mitarbeiternummer" => "789");

    $bla = '"{"key": "value"}"';

    // Initialize Table
    $HTML = '<table data-toggle="table" data-search="true" data-show-columns="true">';

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
    foreach ($Mitarbeiters as $Mitarbeiter){

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