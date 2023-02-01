<?php

function user_can_edit_dienstwunsch($mysqli, $Nutzerrollen, $Wunsch){

    return true;

}

function get_wunschtype_details_by_type_id($Wunschtypen, $WunschTypeID){

    $Return['name'] = "Kein Dienst";

    return $Return;

}

function get_sorted_list_of_all_dienstplanwünsche($mysqli, $ShowDeleted=false){


    $Wishes = [];

    if($ShowDeleted){
        $sql = "SELECT * FROM dienstwuensche ORDER BY create_date ASC";
    }else{
        $sql = "SELECT * FROM dienstwuensche WHERE delete_user IS NULL ORDER BY create_time ASC";
    }

    if($stmt = $mysqli->query($sql)){
        while ($row = $stmt->fetch_assoc()) {
            $Wishes[] = $row;
        }
    }

    return $Wishes;

}

function get_list_of_all_dienstplanwunsch_types($mysqli, $ShowDeleted=false){

    $WishTypes = [];

    if($ShowDeleted){
        $sql = "SELECT * FROM dienstwunsch_typen ORDER BY name ASC";
    }else{
        $sql = "SELECT * FROM dienstwunsch_typen WHERE delete_time IS NULL ORDER BY name ASC";
    }

    if($stmt = $mysqli->query($sql)){
        while ($row = $stmt->fetch_assoc()) {
            $WishTypes[] = $row;
        }
    }

    return $WishTypes;

}