<?php

function get_department_infos($mysqli, $ID){

    $sql = "SELECT * FROM departments WHERE id = ".$ID;
    if($stmt = $mysqli->query($sql)){
        return $stmt->fetch_assoc();
    }

}

function get_all_user_depmnt_assignments($mysqli, $ShowDeleted=false, $AllAssignments=[], $UserObj=[]){

    $Assignments = [];

    if(($mysqli==NULL)){

        foreach ($AllAssignments as $Assignment){

            if($Assignment['user']==$UserObj['id']){
                $Assignments[] = $Assignment;
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

function get_last_date_for_dienstwunsch_submission($DepartmentLastWishMonths){

    $FirstDayThisMonth = date('Y-m-01');
    $CommandMonths = $DepartmentLastWishMonths+1;
    $Command = "+".$CommandMonths." months";
    $WarpUpxMonths = date('Y-m-d', strtotime($Command, strtotime($FirstDayThisMonth)));
    $LastDayOfMonthBefore = date('Y-m-d', strtotime('-1 day', strtotime($WarpUpxMonths)));
    return $LastDayOfMonthBefore;
}