<?php

function get_sorted_list_of_all_abwesenheiten($mysqli, $ShowDeleted=false){
    $Users = [];

    if($ShowDeleted){
        $sql = "SELECT * FROM abwesenheitsantraege ORDER BY create_date ASC";
    }else{
        $sql = "SELECT * FROM abwesenheitsantraege WHERE deleted_by IS NULL ORDER BY create_date ASC";
    }

    if($stmt = $mysqli->query($sql)){
        while ($row = $stmt->fetch_assoc()) {
            $Users[] = $row;
        }
    }

    return $Users;
}

function getWorkingDays($startDate,$endDate,$User,$holidays){

    //Found and adapted from here: https://stackoverflow.com/questions/336127/calculate-business-days
    $holidays = explode(',',$holidays);

    // do strtotime calculations just once
    $endDate = strtotime($endDate);
    $startDate = $currentDate = strtotime($startDate);

    while ($currentDate<=$endDate){

        $WeekdayCurrentDate = date('w', $currentDate);
        if(in_array($WeekdayCurrentDate, explode(',',$User['freie_tage']))){
            $holidays[] = date('Y-m-d', $currentDate);
        }

        $currentDate = strtotime('+1 day', $currentDate);
    }

    //The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
    //We add one to inlude both dates in the interval.
    $days = ($endDate - $startDate) / 86400 + 1;

    $no_full_weeks = floor($days / 7);
    $no_remaining_days = fmod($days, 7);

    //It will return 1 if it's Monday,.. ,7 for Sunday
    $the_first_day_of_week = date("N", $startDate);
    $the_last_day_of_week = date("N", $endDate);

    //---->The two can be equal in leap years when february has 29 days, the equal sign is added here
    //In the first case the whole interval is within a week, in the second case the interval falls in two weeks.
    if ($the_first_day_of_week <= $the_last_day_of_week) {
        if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) $no_remaining_days--;
        if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) $no_remaining_days--;
    }
    else {
        // (edit by Tokes to fix an edge case where the start day was a Sunday
        // and the end day was NOT a Saturday)

        // the day of the week for start is later than the day of the week for end
        if ($the_first_day_of_week == 7) {
            // if the start date is a Sunday, then we definitely subtract 1 day
            $no_remaining_days--;

            if ($the_last_day_of_week == 6) {
                // if the end date is a Saturday, then we subtract another day
                $no_remaining_days--;
            }
        }
        else {
            // the start date was a Saturday (or earlier), and the end date was (Mon..Fri)
            // so we skip an entire weekend and subtract 2 days
            $no_remaining_days -= 2;
        }
    }

    //The no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
//---->february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
    $workingDays = $no_full_weeks * 5;
    if ($no_remaining_days > 0 )
    {
        $workingDays += $no_remaining_days;
    }

    //We subtract the holidays
    foreach($holidays as $holiday){
        $time_stamp=strtotime($holiday);
        //If the holiday doesn't fall in weekend
        if ($startDate <= $time_stamp && $time_stamp <= $endDate && date("N",$time_stamp) != 6 && date("N",$time_stamp) != 7)
            $workingDays--;
    }

    return $workingDays;
}

function calculate_total_approved_holiday_days_for_user_in_selected_year($AllAbwesenheiten, $User, $Year){

    $FirstDaySelectedYear = "01-01-".$Year;
    $LastDaySelectedYear = "31-12-".$Year;
    $TotalCount = 0;

    foreach ($AllAbwesenheiten as $Abwesenheit){

        // Only this user
        if($Abwesenheit['user']==$User['id']){

            // Only approved
            if($Abwesenheit['status_bearbeitung']=='Genehmigt'){
                // Only this year
                if(($Abwesenheit['begin']>=$FirstDaySelectedYear) && ($Abwesenheit['end']<=$LastDaySelectedYear)){
                    //Only Urlaub counts
                    if($Abwesenheit['type']==='Urlaub'){
                        $TotalCount += floor(getWorkingDays($Abwesenheit['begin'],$Abwesenheit['end'],$User,LISTEFEIERTAGE));
                    }
                }
            }

        }

    }

    return $TotalCount;
}

function get_abwesenheit_data($mysqli, $IDabwesenheit){

    $sql = "SELECT * FROM abwesenheitsantraege WHERE id = ".$IDabwesenheit;
        if($stmt = $mysqli->query($sql)){
            return $stmt->fetch_assoc();
        }
}

function bearbeite_abwesenheitsantrag($mysqli, $AbwesenheitID, $StatusMode, $deleteCommentPlaceholder){

    $Timestamp = date('Y-m-d G:i:s');
    $CurrentUser = get_current_user_id();

    // Prepare statement & DB Access
    $sql = "UPDATE abwesenheitsantraege SET status_bearbeitung = ?, bearbeitet_am = ?, bearbeitet_von = ?, delete_comment = ? WHERE id = ?";
    if($stmt = $mysqli->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("ssisi", $StatusMode,$Timestamp,$CurrentUser,$deleteCommentPlaceholder, $AbwesenheitID);

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            $Antwort['success']=true;
        } else {
            $Antwort['success']=false;
            $Antwort['err']="Fehler beim Datenbankzugriff";
        }

        // Close statement
        $stmt->close();
    }

    $mysqli->close();
    return $Antwort;
}

function add_abwesenheitsantrag($User, $BeginDate, $EndDate, $Type, $Urgency, $EntryDate='', $EntryComment='', $Status = 'Beantragt', $DatumBearbeitet = ''){

    // Prepare variables and generate initial password
    $mysqli = connect_db();
    $Antwort = [];
    $CurrentUserID = get_current_user_id();

    // Prepare statement & DB Access
    $sql = "INSERT INTO abwesenheitsantraege (user, begin, end, type, urgency, create_date, create_user, create_comment, bearbeitet_am, status_bearbeitung) VALUES (?,?,?,?,?,?,?,?,?,?)";
    if($stmt = $mysqli->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("isssssssss", $User, $BeginDate, $EndDate, $Type, $Urgency, $EntryDate, $CurrentUserID, $EntryComment, $DatumBearbeitet, $Status);

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            // Return success + new users ID + Users Password
            $Antwort['success']=true;
            $Antwort['newID'] = $mysqli->insert_id;
        } else{
            $Antwort['success']=false;
            $Antwort['err']="Fehler beim Datenbankzugriff";
        }

        // Close statement
        $stmt->close();
    }
    $mysqli->close();

    return $Antwort;

}

function user_can_edit_abwesenheitsantrag($mysqli, $Nutzerrollen, $Abwesenheit){

    $Nutzerrollen = explode(',',$Nutzerrollen);

    // Case 1: User is Admin -> always possible
    if(in_array('admin', $Nutzerrollen)){
        return true;
    } else {

        // Case 2: the user is from the HR department
        if(in_array('ausfaelle', $Nutzerrollen)){

            // Permission is only granted when status is "Beantragt" i.e. the employee has not recieved an answer to the application
            if($Abwesenheit['status_bearbeitung']=='Beantragt'){
                return true;
            } else {
                return false;
            }

        }

        // Case 3: the application is from the active user themselves
        if(get_current_user_id() == $Abwesenheit['user']){
            // Permission is only granted when status is "Beantragt" i.e. the employee has not recieved an answer to the application
            if($Abwesenheit['status_bearbeitung']==='Beantragt'){

                // Only show edit buttons on applications, that haven't started yet
                if(time()<strtotime($Abwesenheit['begin'])){
                    return true;
                } else {
                    return false;
                }

            } else {
                return false;
            }
        } else {
            return false;
        }

    }

}

function delete_abwesenheitsantrag($mysqli, $AbwesenheitID, $UserID, $DeleteComment){

    $Antwort = [];
    $time = date('Y-m-d G:i:s');
    $stmnt = "UPDATE abwesenheitsantraege SET deleted_by = ?, deleted_on = ?, delete_comment = ? WHERE id = ?";

    if($stmt = $mysqli->prepare($stmnt)){
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("issi", $UserID, $time, $DeleteComment, $AbwesenheitID);

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            // Return success + new users ID + Users Password
            $Antwort['success']=true;
        } else{
            $Antwort['success']=false;
            $Antwort['err']="Fehler beim Datenbankzugriff";
        }

        // Close statement
        $stmt->close();
    }
    $mysqli->close();

    return $Antwort;

}

function check_abwesenheit_date_overlap_user($UserID,$AllItems,$Begin,$End){

    $Catches = [];

    foreach ($AllItems as $Item){

        if($Item['user']==$UserID){
            $Catch=true;
            //Check non-overlap cases
            //Case 1: Begin and End of Item are smaller
            if((strtotime($Item['begin'])<strtotime($Begin)) && (strtotime($Item['end'])<strtotime($Begin))){
                $Catch=false;
            }
            //Case 2: Begin and End of Item are bigger
            if((strtotime($Item['begin'])>strtotime($End)) && (strtotime($Item['end'])>strtotime($End))){
                $Catch=false;
            }
            if($Catch){
                $Catches[]=$Item;
            }
        }
    }
    if(sizeof($Catches)>0){
        $Return['bool']=true;
        $Return['items']=$Catches;
    } else {
        $Return['bool']=false;
    }

    return $Return;
}