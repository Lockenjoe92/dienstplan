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