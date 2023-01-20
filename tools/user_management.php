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

function add_new_user($Vorname, $Nachname, $Username, $Mitarbeiternummer, $Mail, $AbteilungRollen, $ToolRollen){

    // Prepare variables and generate initial password
    $mysqli = connect_db();
    $Antwort = [];
    $CurrentUserID = get_current_user_id();
    $pass = bin2hex(random_bytes(30)); //creates cryptographically secure random string
    $pass_hash = password_hash($pass, PASSWORD_DEFAULT); // Creates a password hash

    // Prepare statement & DB Access
    $sql = "INSERT INTO users (username, mail, mitarbeiternummer, password, vorname, nachname, nutzergruppen, abteilungsrollen, created_by) VALUES (?,?,?,?,?,?,?,?,?)";
    if($stmt = $mysqli->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("ssisssssi", $Username, $Mail, $Mitarbeiternummer, $pass_hash, $Vorname, $Nachname, $ToolRollen, $AbteilungRollen, $CurrentUserID);

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            // Return success + new users ID + Users Password
            $Antwort['success']=true;
            $Antwort['pass'] = $pass;
            $Antwort['newID'] = $mysqli->insert_id;
        } else{
            $Antwort['success']=true;
            $Antwort['err']="Fehler beim Datenbankzugriff";
        }

        // Close statement
        $stmt->close();
    }
    $mysqli->close();

    return $Antwort;
}

function get_current_user_id(){
    session_start();
    return $_SESSION['user'];
}

function get_sorted_list_of_all_users($mysqli, $orderBy='nachname', $includeInactive=false){

    $Users = [];

    if(!$includeInactive){
        $sql = "SELECT * FROM users WHERE inaktiv_durch_user IS NULL ORDER BY ".$orderBy." ASC";
    } else {
        $sql = "SELECT * FROM users ORDER BY ".$orderBy." ASC";
    }

    if($stmt = $mysqli->query($sql)){
        while ($row = $stmt->fetch_assoc()) {
            $Users[] = $row;
        }
    }

    return $Users;

}