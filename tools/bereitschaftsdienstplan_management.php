<?php

function get_list_of_all_bd_types($mysqli, $ShowDeleted=false){

    $BDtypes = [];

    if($ShowDeleted){
        $sql = "SELECT * FROM bereitschaftsdienst_typen ORDER BY rank, kuerzel ASC";
    }else{
        $sql = "SELECT * FROM bereitschaftsdienst_typen WHERE delete_time IS NULL ORDER BY reihung,kuerzel ASC";
    }

    if($stmt = $mysqli->query($sql)){
        while ($row = $stmt->fetch_assoc()) {
            $BDtypes[] = $row;
        }
    }

    return $BDtypes;

}

function get_list_of_all_bd_matrixes($mysqli, $ShowDeleted=false){

    $BDtypes = [];

    if($ShowDeleted){
        $sql = "SELECT * FROM bereitschaftsdienst_matrix";
    }else{
        $sql = "SELECT * FROM bereitschaftsdienst_matrix";
    }

    if($stmt = $mysqli->query($sql)){
        while ($row = $stmt->fetch_assoc()) {
            $BDtypes[] = $row;
        }
    }

    return $BDtypes;

}

function get_bd_assignment_info($mysqli, $assignmentID){

    $sql = "SELECT * FROM user_bereitschaftsdienst_assignments WHERE id = ".$assignmentID;
    if($stmt = $mysqli->query($sql)){
        return $stmt->fetch_assoc();
    }
}

function get_sorted_list_of_all_bd_einteilungen($mysqli, $ShowDeleted=false){
    $BDeinteilungen = [];

    if($ShowDeleted){
        $sql = "SELECT * FROM bereitschaftsdienstplan ORDER BY day ASC";
    }else{
        $sql = "SELECT * FROM bereitschaftsdienstplan WHERE delete_time IS NULL ORDER BY day ASC";
    }

    if($stmt = $mysqli->query($sql)){
        while ($row = $stmt->fetch_assoc()) {
            $BDeinteilungen[] = $row;
        }
    }

    return $BDeinteilungen;
}

function get_list_of_all_public_bd_plans($mysqli){

    $BDfreigegeben = [];

    $sql = "SELECT * FROM bereitschaftsdienstplan_freigeschaltete_monate ORDER BY year,month ASC";

    if($stmt = $mysqli->query($sql)){
        while ($row = $stmt->fetch_assoc()) {
            $BDfreigegeben[] = $row;
        }
    }

    return $BDfreigegeben;

}

function get_bereitschaftsdiensttype_details_by_type_id($Wunschtypen, $WunschTypeID){

    foreach ($Wunschtypen as $wunschtyp){
        if($wunschtyp['id']==$WunschTypeID){
            return $wunschtyp;
        }
    }

}