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
        $sql = "SELECT * FROM dienstwunsch_typen ORDER BY belongs_to_depmnt,name ASC";
    }else{
        $sql = "SELECT * FROM dienstwunsch_typen WHERE delete_time IS NULL ORDER BY belongs_to_depmnt,name ASC";
    }

    if($stmt = $mysqli->query($sql)){
        while ($row = $stmt->fetch_assoc()) {
            $WishTypes[] = $row;
        }
    }

    return $WishTypes;

}

function check_dienstwunsch_date_overlap_user($UserID, $AllWuensche, $DatePlaceholder, $IgnoreItem=0){

    $Catches = [];

    foreach ($AllWuensche as $Item){

        if($IgnoreItem>0){
            if($IgnoreItem!=$Item['id']){
                if($Item['user']==$UserID){
                    $Catch=false;
                    //Check non-overlap cases
                    //Case 1: Begin and End of Item are smaller
                    if($Item['date']==$DatePlaceholder){
                        $Catch=true;
                    }
                    if($Catch){
                        $Catches[]=$Item;
                    }
                }
            }
        } else {
            if($Item['user']==$UserID){
                $Catch=false;
                //Check non-overlap cases
                //Case 1: Begin and End of Item are smaller
                if($Item['date']==$DatePlaceholder){
                    $Catch=true;
                }
                if($Catch){
                    $Catches[]=$Item;
                }
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

function check_if_dienstwunsch_is_in_selected_month($Wunsch, $Month, $Year){

    $WunschDate = strtotime($Wunsch['date']);

    if((date('m', $WunschDate)==$Month) && (date('Y', $WunschDate)==$Year)){
        return true;
    } else {
        return false;
    }

}

function dienstwunsch_anlegen($mysqli, $User, $Date, $type, $entryDate, $commentPlaceholder){

    // Prepare variables and generate initial password
    $Antwort = [];
    $CurrentUserID = get_current_user_id();

    // Prepare statement & DB Access
    $sql = "INSERT INTO dienstwuensche (user, date, type, create_time, create_user, create_comment) VALUES (?,?,?,?,?,?)";
    if($stmt = $mysqli->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("isssis", $User, $Date, $type, $entryDate, $CurrentUserID, $commentPlaceholder);

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