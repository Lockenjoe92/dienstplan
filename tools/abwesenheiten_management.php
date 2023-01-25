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

function get_abwesenheit_data($mysqli, $IDabwesenheit){

    $sql = "SELECT * FROM abwesenheitsantraege WHERE id = ".$IDabwesenheit;
        if($stmt = $mysqli->query($sql)){
            return $stmt->fetch_assoc();
        }
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