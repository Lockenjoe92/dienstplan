<?php

function add_department_event($mysqli, $Name, $Details, $From, $To, $TableClass='table-success'){

    // Prepare variables
    $Antwort = [];
    $CurrentUserID = get_current_user_id();

    // Prepare statement & DB Access
    $sql = "INSERT INTO department_events (name, details, table_color_class, begin, end, create_user) VALUES (?,?,?,?,?,?)";
    if($stmt = $mysqli->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("sssssi", $Name, $Details, $TableClass, $From, $To, $CurrentUserID);

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            // Return success + new users ID + Users Password
            $Antwort['success']=true;
            $Antwort['newID'] = $mysqli->insert_id;
            $ProtocolDetails = "Abteilungsveranstaltung #".$Antwort['newID']." ".$Name." wurde angelegt.";
            add_protocol_entry($mysqli, 'department_events', 'add', '', $CurrentUserID, $ProtocolDetails);

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

function edit_department_event($mysqli, $ID, $Name, $Details, $From, $To, $TableClass='table-success'){

    // Prepare variables
    $Antwort = [];
    $CurrentUserID = get_current_user_id();

    // Prepare statement & DB Access
    $sql = "UPDATE department_events SET name = ?, details = ?, table_color_class = ?, begin = ?, end = ? WHERE id = ?";
    if($stmt = $mysqli->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("sssssi", $Name, $Details, $TableClass, $From, $To, $ID);

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            // Return success + new users ID + Users Password
            $Antwort['success']=true;
            $ProtocolDetails = "Abteilungsveranstaltung #".$ID." ".$Name." wurde bearbeitet.";
            add_protocol_entry($mysqli, 'department_events', 'edit', '', $CurrentUserID, $ProtocolDetails);
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

function delete_department_event($mysqli, $ID, $DeleteComment){

    // Prepare variables
    $Antwort = [];
    $CurrentUserID = get_current_user_id();
    $CurrentTime = date('Y-m-d G:i:s');

    // Prepare statement & DB Access
    $sql = "UPDATE department_events SET delete_user = ?, delete_time = ?, delete_comment = ? WHERE id = ?";
    if($stmt = $mysqli->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("issi", $CurrentUserID, $CurrentTime, $DeleteComment, $ID);

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

function get_sorted_list_of_all_department_events($mysqli, $ShowDeleted=false){

    $Users = [];

    if($ShowDeleted){
        $sql = "SELECT * FROM department_events ORDER BY begin, name ASC";
    }else{
        $sql = "SELECT * FROM department_events WHERE delete_time IS NULL ORDER BY begin, name ASC";
    }

    if($stmt = $mysqli->query($sql)){
        while ($row = $stmt->fetch_assoc()) {
            $Users[] = $row;
        }
    }

    return $Users;

}

function get_department_event_infos_by_id_from_list($AllEvents, $ID){
    foreach ($AllEvents as $Event){
        if($Event['id']==$ID){
            return $Event;
        }
    }
}