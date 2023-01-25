<?php

function urlaubsplan_funktionsbuttons(){

    return "Hier kommen Steuerelemente hin<br>";

}


function urlaubsplan_tabelle_management($month, $year){

    $HTML = '';
    $mysqli = connect_db();
    $AllUsers = get_sorted_list_of_all_users($mysqli, 'abteilungsrollen DESC, nachname ASC');
    $AllAbwesenheiten = get_sorted_list_of_all_abwesenheiten($mysqli);
    $FirstDayOfCalendarString = "01-".$month."-".$year;
    $FirstDayOfCalendar = strtotime($FirstDayOfCalendarString);

    // Build Table header -> this means loading date information
    $TableHeader = "<thead>";
    $TableHeader .= "<th>MitarbeiterIn</th>";

    //Iterate as long as we are still in the same month
    for($a=0;$a<31;$a++){
        $Command = "+".$a." days";
        $ThisDay = strtotime($Command, $FirstDayOfCalendar);

        //Catch Month shift
        if(date("m", $ThisDay)==$month){
            $TableHeader .= "<th>".date("d", $ThisDay)."</th>";
        }

    }

    $TableHeader .= "</thead>";

    // Generate Rows based on Users
    $TableRows = "";
    foreach ($AllUsers as $User) {

        // Don't show people from HR
        if($User['abteilungsrollen']!="Verwaltung"){
            $TableRowContent = "<tr>";

            // Change first columns color depending on Employee status
            if($User['abteilungsrollen']=="OA"){
                $Coloring = "table-danger";
            } elseif ($User['abteilungsrollen']=="FA"){
                $Coloring = "table-warning";
            } elseif ($User['abteilungsrollen']=="AA"){
                $Coloring = "table-success";
            } else {
                $Coloring = "";
            }

            // Get the User Name
            $TableRowContent .= "<td class='".$Coloring."'>".$User['nachname'].", ".$User['vorname']."</td>";

            // Populate the days with information
            for($a=0;$a<31;$a++){
                $Command = "+".$a." days";
                $ThisDay = strtotime($Command, $FirstDayOfCalendar);

                //Catch Month shift
                if(date("m", $ThisDay)==$month){
                    $TableRowContent .= populate_day_urlaubsplan_tabelle_management($ThisDay,$User['id'],$AllAbwesenheiten);
                }

            }

            $TableRowContent .= "</tr>";
            $TableRows .= $TableRowContent;
        }
    }

    // Build table body
    $TableBody = '<tbody class="table-group-divider">'.$TableRows.'</tbody>';

    // Build that calendar
    $Table = "<table class='table table-sm table-condensed'>";
    $Table .= $TableHeader;
    $Table .= $TableBody;
    $Table .= "</table>";

    return $Table;

}

function populate_day_urlaubsplan_tabelle_management($Day,$UserID,$AllAbwesenheiten){

    return "<td></td>";

}