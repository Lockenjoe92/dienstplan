<?php

function add_protocol_entry($mysqli, $Topic, $Type, $ConcernedUser, $ActionUser, $Details){

    // Prepare statement & DB Access
    $sql = "INSERT INTO protocol (topic,type,concerned_user,action_user,details) VALUES (?,?,?,?,?)";
    if($stmt = $mysqli->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("ssiis", $Topic, $Type, $ConcernedUser, $ActionUser, $Details);

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            // Return success + new users ID + Users Password
            $Antwort['success']=true;
            $Antwort['protocolID'] = $mysqli->insert_id;
        } else{
            $Antwort['success']=false;
            $Antwort['err']="Fehler beim Datenbankzugriff";
        }

        // Close statement
        $stmt->close();
    }

    return $Antwort;

}