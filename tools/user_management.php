<?php

function load_user_usergroups($mysqli, $ID){



    // Load Session based on Secret
    $sql = "SELECT id, nutzergruppen FROM users WHERE id = ?";
    if($stmt = $mysqli->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("i", $ID);

        // Attempt to execute the prepared statement
        if($stmt->execute()) {

            // Store result
            $stmt->store_result();

            // Check if only one session exists
            $Nutzergruppen = "";
            $stmt->bind_result($ID, $Nutzergruppen);
            if($stmt->fetch()){
                return $Nutzergruppen;
            }
        } else {
            return false;
        }
    } else {
        return false;
    }

}