<?php

function user_can_edit_dienstwunsch($mysqli, $Nutzerrollen, $Wunsch, $UE){

    $Nutzerrollen = explode(',',$Nutzerrollen);

    // Case 1: User is Admin -> always possible
    if(in_array('admin', $Nutzerrollen)){
        return true;
    } else {

        // Case 2: the user is from the HR department
        if(in_array('dienstplan_'.$UE, $Nutzerrollen)){

            // Permission is only granted when status is "Beantragt" i.e. the employee has not recieved an answer to the application
            #if($Abwesenheit['status_bearbeitung']=='Beantragt'){
            #   return true;
            #} else {
            #   return false;
            #}
            return true;
        }

        // Case 3: the application is from the active user themselves
        if(get_current_user_id() == $Wunsch['user']){

                // Only show edit buttons on applications, that haven't started yet
                $DienstwunschType = get_dienstwunsch_type_data($mysqli,$Wunsch['type']);
                $Department = get_department_infos($mysqli,$DienstwunschType['belongs_to_depmnt']);
                if(get_last_date_for_dienstwunsch_submission($Department['accept_user_dienst_wishes_until_months'], $Wunsch['date'])<$Wunsch['date']){
                    return true;
                } else {
                    return false;
                }

        } else {
            return false;
        }

    }

}

function get_wunschtype_details_by_type_id($Wunschtypen, $WunschTypeID){

    foreach ($Wunschtypen as $wunschtyp){
        if($wunschtyp['id']==$WunschTypeID){
            return $wunschtyp;
        }
    }

}

function get_sorted_list_of_all_dienstplanwünsche($mysqli, $SpecialSort=false){


    $Wishes = [];

    if($SpecialSort){
        $sql = "SELECT * FROM dienstwuensche WHERE delete_user IS NULL ORDER BY date, user, type ASC";
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

function check_dienstwunsch_date_overlap_user($UserID, $AllWuensche, $DatePlaceholder, $typePlaceholder, $IgnoreItem=0){

    $Catches = [];

    foreach ($AllWuensche as $Item){

        if($IgnoreItem>0){
            if($IgnoreItem!=$Item['id']){
                if($Item['user']==$UserID){
                    $Catch=false;
                    //Check non-overlap cases
                    //Case 1: Begin and End of Item are smaller
                    if($Item['date']==$DatePlaceholder){
                        if($Item['type']==$typePlaceholder){
                            $Catch=true;
                        }
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
                    if($Item['type']==$typePlaceholder){
                        $Catch=true;
                    }
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

function delete_dienstwunsch_db($mysqli, $idDienstwunsch, $idUser, $DeleteComment){

    $Antwort = [];
    $time = date('Y-m-d G:i:s');
    $stmnt = "UPDATE dienstwuensche SET delete_user = ?, delete_time = ?, delete_comment = ? WHERE id = ?";

    if($stmt = $mysqli->prepare($stmnt)){
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("issi", $idUser, $time, $DeleteComment, $idDienstwunsch);

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

function edit_dienstwunsch_db($mysqli, $idDienstwunsch, $typeDienstwunsch, $idUser, $UserComment){

    $Antwort = [];
    $time = date('Y-m-d G:i:s');
    $stmnt = "UPDATE dienstwuensche SET type = ?, create_comment = ? WHERE id = ?";

    if($stmt = $mysqli->prepare($stmnt)){
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("isi",$typeDienstwunsch, $UserComment, $idDienstwunsch);

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

function get_dienstwunsch_data($mysqli, $ID){

    $sql = "SELECT * FROM dienstwuensche WHERE id = ".$ID;
    if($stmt = $mysqli->query($sql)){
        return $stmt->fetch_assoc();
    }

}

function get_dienstwunsch_type_data($mysqli, $ID){

    $sql = "SELECT * FROM dienstwunsch_typen WHERE id = ".$ID;
    if($stmt = $mysqli->query($sql)){
        return $stmt->fetch_assoc();
    }

}

function day_is_a_weekend_or_holiday($Day){

    if((date('w',$Day)==0)OR(date('w',$Day)==6)){
        return true;
    } elseif (in_array(date('Y-m-d',$Day), explode(',',LISTEFEIERTAGE))){
        return true;
    } else {
        return false;
    }

}

function get_negative_bd_wishes_user_on_certain_day($user, $BDType, $Allwishes, $AllWishTypes, $AllBDTypes, $DateConcerned){

    $return = [];

    foreach ($Allwishes as $wish){

        if($wish['user'] == $user){

            if(strtotime($wish['date'])==$DateConcerned){

                //now lets check if the wish is a negative wish-type
                if(!check_wishtype_booleaniness($wish['type'], $AllWishTypes)){

                    //Check if wish-type corresponds to current BD-type
                    $DienstType = get_bereitschaftsdiensttype_details_by_type_id($AllBDTypes, $BDType);
                    $WishType = get_bereitschaftsdienstwuenschetype_details_by_type_id($AllWishTypes, $wish['type']);

                    if($WishType['type']==$DienstType['type']){
                        $return[] = $wish;
                    } elseif ($WishType['type']=='all_day'){
                        $return[] = $wish;
                    }

                }

            }

        }

    }

    return $return;

}

function get_positive_bd_wishes_user_on_certain_day($user, $BDType, $Allwishes, $AllWishTypes, $AllBDTypes, $DateConcerned){

    $return = [];

    foreach ($Allwishes as $wish){

        if($wish['user'] == $user){

            if(strtotime($wish['date'])==$DateConcerned){

                //now lets check if the wish is a positive wish-type
                if(check_wishtype_booleaniness($wish['type'], $AllWishTypes)){

                    //Check if wish-type corresponds to current BD-type
                    $DienstType = get_bereitschaftsdiensttype_details_by_type_id($AllBDTypes, $BDType);
                    $WishType = get_bereitschaftsdienstwuenschetype_details_by_type_id($AllWishTypes, $wish['type']);
                    if($WishType['type']==$DienstType['type']){
                        $return[] = $wish;
                    } elseif ($WishType['type']=='all_day'){
                        $return[] = $wish;
                    }

                }

            }

        }

    }

    return $return;
}

function check_wishtype_booleaniness($WishTypeID, $AllWishTypes){

    $WishType = [];
    foreach ($AllWishTypes as $AllWishType){
        if($AllWishType['id']==$WishTypeID){
            $WishType = $AllWishType;
        }
    }

    if($WishType['boolean']==0){
        return false;
    } else {
        return true;
    }
}