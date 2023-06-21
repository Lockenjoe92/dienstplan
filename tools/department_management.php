<?php

function get_department_infos($mysqli, $ID){

    $sql = "SELECT * FROM departments WHERE id = ".$ID;
    if($stmt = $mysqli->query($sql)){
        return $stmt->fetch_assoc();
    }

}

function get_all_user_depmnt_assignments($mysqli, $ShowDeleted=false, $AllAssignments=[], $UserObj=''){

    $Assignments = [];

    if(($mysqli==NULL)){

        foreach ($AllAssignments as $Assignment){

            if(is_array($UserObj)){
                if($Assignment['user']==$UserObj['id']){
                    $Assignments[] = $Assignment;
                }
            } else {
                if($Assignment['user']==$UserObj){
                    $Assignments[] = $Assignment;
                }
            }

        }

    } else {

        if($ShowDeleted){
            $sql = "SELECT * FROM user_department_assignments ORDER BY user ASC";
        }else{
            $sql = "SELECT * FROM user_department_assignments WHERE delete_user IS NULL ORDER BY user ASC";
        }

        if($stmt = $mysqli->query($sql)){
            while ($row = $stmt->fetch_assoc()) {
                $Assignments[] = $row;
            }
        }
    }

    return $Assignments;

}

function get_list_of_all_departments($mysqli, $ShowDeleted=false){

    $Users = [];

    if($ShowDeleted){
        $sql = "SELECT * FROM departments ORDER BY name ASC";
    }else{
        $sql = "SELECT * FROM departments WHERE hide_time IS NULL ORDER BY name ASC";
    }

    if($stmt = $mysqli->query($sql)){
        while ($row = $stmt->fetch_assoc()) {
            $Users[] = $row;
        }
    }

    return $Users;

}

function get_num_wishes_user_in_selected_month($UserID,$Date,$AllWuensche){

    $Counter=0;

    foreach ($AllWuensche as $wunsch){

        if($wunsch['user']==$UserID){

            if($wunsch['delete_time']===NULL){

                if(date('Y-m', strtotime($wunsch['date']))==date('Y-m', strtotime($Date))){
                    $Counter++;
                }

            }

        }

    }

    return $Counter;
}

function get_last_date_for_dienstwunsch_submission($DepartmentLastWishMonths, $DateWunsch){

    // First check if there is a special last entry date in concerned Month
    $AllSpecialEntryDateLimits = explode(',', LISTEGESONDERTEREINGABEFRISTENBDPLAN);
    $Catch = false;
    $LastDayOfLimit = "";

    if(!empty(LISTEGESONDERTEREINGABEFRISTENBDPLAN)){
        foreach ($AllSpecialEntryDateLimits as $SpecialLimit){

            // Unpack Limit ( CONCERNEDYEAR-CONCERNEDMONTH:LASTENTRYDATE(Y-m-d)
            $SpecialLimitunpacked = explode(':', $SpecialLimit);
            if(date('Y-m', strtotime($DateWunsch))==$SpecialLimitunpacked[0]){
                $Catch = true;
                $LastDayOfLimit = $SpecialLimitunpacked[1];
            }

        }
    }

    if($Catch){
        return $LastDayOfLimit;
    } else {
        // Get First Day of month in x months time
        $FirstDayThisMonth = date('Y-m-01');
        $CommandMonths = $DepartmentLastWishMonths;
        $Command = "+".($CommandMonths+1)." months";
        $newTime = strtotime($Command, strtotime($FirstDayThisMonth));
        $lastDate = date('Y-m-d', strtotime('-1 day',$newTime));
        return $lastDate;
    }
}

function get_dept_assignment_info($mysqli, $assignmentID){

    $sql = "SELECT * FROM user_department_assignments WHERE id = ".$assignmentID;
    if($stmt = $mysqli->query($sql)){
        return $stmt->fetch_assoc();
    }
}

function compareByTimeStamp($time1, $time2){
    if (strtotime($time1) < strtotime($time2))
        return 1;
    else if (strtotime($time1) > strtotime($time2))
        return -1;
    else
        return 0;
}