<?php

function get_sorted_list_of_all_abwesenheiten($mysqli){
    $Users = [];
    $sql = "SELECT * FROM abwesenheitsantraege ORDER BY create_date ASC";

    if($stmt = $mysqli->query($sql)){
        while ($row = $stmt->fetch_assoc()) {
            $Users[] = $row;
        }
    }

    return $Users;
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