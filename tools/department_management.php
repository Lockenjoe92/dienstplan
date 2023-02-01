<?php

function get_department_infos($mysqli, $ID){

    $sql = "SELECT * FROM departments WHERE id = ".$ID;
    if($stmt = $mysqli->query($sql)){
        return $stmt->fetch_assoc();
    }

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