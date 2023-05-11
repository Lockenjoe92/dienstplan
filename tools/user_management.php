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

function add_new_user($Vorname, $Nachname, $Username, $Mitarbeiternummer, $Mail, $AbteilungRollen, $ToolRollen, $Vertrag, $Urlaubstage, $FreieTage='', $IstNotarzt=false){

    // Prepare variables and generate initial password
    $mysqli = connect_db();
    $Antwort = [];
    $CurrentUserID = get_current_user_id();
    $pass = bin2hex(random_bytes(7)); //creates cryptographically secure random string
    $pass_hash = password_hash($pass, PASSWORD_DEFAULT); // Creates a password hash

    if($IstNotarzt){
        $IstNotarzt = 1;
    } else {
        $IstNotarzt = 0;
    }

    // Prepare statement & DB Access
    $sql = "INSERT INTO users (username, mail, mitarbeiternummer, password, vorname, nachname, nutzergruppen, abteilungsrollen, vertrag, urlaubstage, freie_tage, hat_notarzt, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
    if($stmt = $mysqli->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("ssisssssiisii", $Username, $Mail, $Mitarbeiternummer, $pass_hash, $Vorname, $Nachname, $ToolRollen, $AbteilungRollen, $Vertrag, $Urlaubstage, $FreieTage, $IstNotarzt, $CurrentUserID);

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            // Return success + new users ID + Users Password
            $Antwort['success']=true;
            $Antwort['pass'] = $pass;
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

function edit_user($SelectedUser, $Vorname, $Nachname, $UEdefault, $Mitarbeiternummer, $Mail, $AbteilungRollen, $ToolRollen, $Vertrag, $Urlaubstage, $FreieTage='', $IstNotarzt=false, $InactiveDate=''){

    // Prepare variables and generate initial password
    $mysqli = connect_db();
    $Antwort = [];
    if(empty($InactiveDate)){
        $sql = "UPDATE users SET username = ?, mail = ?, mitarbeiternummer = ?, vorname = ?, nachname = ?, abteilungsrollen = ?, nutzergruppen = ?, vertrag = ?, urlaubstage = ?, freie_tage = ?, default_abteilung = ? WHERE id = ?";
    } else {
        $sql = "UPDATE users SET username = ?, mail = ?, mitarbeiternummer = ?, vorname = ?, nachname = ?, abteilungsrollen = ?, nutzergruppen = ?, vertrag = ?, urlaubstage = ?, freie_tage = ?, default_abteilung = ?, inaktiv_seit = ?, inaktiv_durch_user = ? WHERE id = ?";
        $CurrentUserID = get_current_user_id();
    }

    if($stmt = $mysqli->prepare($sql)){

        // Bind variables to the prepared statement as parameters
        if(empty($InactiveDate)){
            $stmt->bind_param("ssissssiisii", $Username, $Mail, $Mitarbeiternummer, $Vorname, $Nachname, $AbteilungRollen, $ToolRollen, $Vertrag, $Urlaubstage, $FreieTage, $UEdefault, $SelectedUser);
        } else {
            $stmt->bind_param("ssissssiisisii", $Username, $Mail, $Mitarbeiternummer, $Vorname, $Nachname, $AbteilungRollen, $ToolRollen, $Vertrag, $Urlaubstage, $FreieTage, $UEdefault, $InactiveDate, $CurrentUserID, $SelectedUser);
        }

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            // Return success + new users ID + Users Password
            $Antwort['success']=true;
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

function reset_user_password($mysqli, $UserObj, $SendMail=true){

    //Fetch current User Infos
    $CurrentUserInfos = get_current_user_infos($mysqli, get_current_user_id());

    //Generate new Password
    $pass = bin2hex(random_bytes(7)); //creates cryptographically secure random string
    $pass_hash = password_hash($pass, PASSWORD_DEFAULT); // Creates a password hash

    //Generate Mail Bausteine
    $Bausteine['[name_user]'] = $UserObj['vorname'].' '.$UserObj['nachname'];
    $Bausteine['[new_pass]'] = $pass;

    //Update User
    // Prepare statement & DB Access
    $sql = "UPDATE users SET password = ? WHERE id = ?";
    if($stmt = $mysqli->prepare($sql)){

        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("si", $pass_hash, $UserObj['id']);

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            // Return success + new users ID + Users Password
            $Antwort['success']=true;
            $Antwort['pass']=$pass;
            if($SendMail){
                $ProtocolDetails = "Das Passwort von ".$UserObj['nachname']." ".$UserObj['vorname']." wurde durch ".$CurrentUserInfos['vorname']." ".$CurrentUserInfos['nachname']." zurückgesetzt";
                mail_senden($mysqli, 'new-password', $UserObj, $Bausteine, 'new_password', $ProtocolDetails);
            }
        } else{
            $Antwort['success']=true;
            $Antwort['err']="Fehler beim Datenbankzugriff";
        }

        // Close statement
        $stmt->close();
    }

    return $Antwort;
}

function get_current_user_id(){
        session_start();
        return $_SESSION['user'];
}

function get_sorted_list_of_all_users($mysqli, $orderBy='nachname ASC', $includeInactive=false, $MinDate='2050-12-31 23:59:59'){

    $Users = [];
    $sql = "SELECT * FROM users ORDER BY ".$orderBy."";

    if($stmt = $mysqli->query($sql)){
        while ($row = $stmt->fetch_assoc()) {
            if($row['inaktiv_seit']!=NULL){
                if(strtotime($MinDate)<strtotime($row['inaktiv_seit'])){
                    $Users[] = $row;
                } else {
                    if($includeInactive){
                        $Users[] = $row;
                    }
                }
            } else {
                $Users[] = $row;
            }
        }
    }

    return $Users;

}

function get_current_user_infos($mysqli, $ID){

    $sql = "SELECT * FROM users WHERE id = ".$ID;
    if($stmt = $mysqli->query($sql)){
        return $stmt->fetch_assoc();
    }

}

function get_user_depmnt_assignments($mysqli, $ID, $ShowDeleted=false, $SkipAssignment=0){

    $Assignments = [];

    if($ShowDeleted){
        $sql = "SELECT * FROM user_department_assignments WHERE user = ".$ID." ORDER BY begin ASC";
    }else{
        $sql = "SELECT * FROM user_department_assignments WHERE user = ".$ID." AND delete_user IS NULL ORDER BY begin ASC";
    }

    if($stmt = $mysqli->query($sql)){
        while ($row = $stmt->fetch_assoc()) {
            if($SkipAssignment>0){
                if($row['id']!=$SkipAssignment){
                    $Assignments[] = $row;
                }
            } else {
                $Assignments[] = $row;
            }
        }
    }

    return $Assignments;

}

function get_user_dienstgruppen_zugehoerigkeiten($mysqli, $ID, $ShowDeleted=false, $SkipAssignment=0){

    $Assignments = [];

    if($ShowDeleted){
        $sql = "SELECT * FROM user_bereitschaftsdienst_assignments WHERE user = ".$ID." ORDER BY begin ASC";
    }else{
        $sql = "SELECT * FROM user_bereitschaftsdienst_assignments WHERE user = ".$ID." AND delete_user IS NULL ORDER BY begin ASC";
    }

    if($stmt = $mysqli->query($sql)){
        while ($row = $stmt->fetch_assoc()) {
            if($SkipAssignment>0){
                if($row['id']!=$SkipAssignment){
                    $Assignments[] = $row;
                }
            } else {
                $Assignments[] = $row;
            }
        }
    }

    return $Assignments;

}

function get_user_assigned_department_at_date($mysqli, $user, $Date, $AllAssignments=[]){

    $UserInfos = $user;

    //Catch different time formats
    if(substr_count($Date,'-')>0){
        $Date = strtotime($Date);
    }

    if($mysqli==NULL){
        $UserAssignments = get_all_user_depmnt_assignments(NULL, FALSE, $AllAssignments, $user);
    } else {
        $UserAssignments = get_user_depmnt_assignments($mysqli, $user['id']);
    }

    $DefaultDepartment = $UserInfos['default_abteilung'];
    if(sizeof($UserAssignments)==0){
        return $DefaultDepartment;
    } else {

        $Catches = [];

        // Loop through assignments until active assignment at date is found - if none are active, return default
        foreach ($UserAssignments as $assignment){
            $Catch=true;
            //Check non-overlap cases
            //Case 1: Begin and End of Item are smaller
            if((strtotime($assignment['begin'])<$Date) && (strtotime($assignment['end'])<$Date)){
                $Catch=false;
            }
            //Case 2: Begin and End of Item are bigger
            if((strtotime($assignment['begin'])>$Date) && (strtotime($assignment['end'])>$Date)){
                $Catch=false;
            }
			
            if($Catch){
                $Catches[]=$assignment;
            }

        }

        if(sizeof($Catches)==0){
            return $DefaultDepartment;
        } elseif(sizeof($Catches)==1){
            return $Catches[0]['department'];
        } else {
            return $DefaultDepartment;
        }
    }

}

function add_user_sondereinteilung($mysqli, $UserID, $uePlaceholder, $beginPlaceholder, $endPlaceholder, $commentPlaceholder){

    $CreateUser = get_current_user_id();

    // Prepare statement & DB Access
    $sql = "INSERT INTO user_department_assignments (user, department, begin, end, create_user, create_comment) VALUES (?,?,?,?,?,?)";
    if($stmt = $mysqli->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("iissis", $UserID, $uePlaceholder, $beginPlaceholder, $endPlaceholder, $CreateUser, $commentPlaceholder);

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            // Return success + new sondereinteilung ID
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

function edit_user_sondereinteilung($mysqli, $AssignmentID, $beginPlaceholder, $endPlaceholder, $commentPlaceholder){

    $CreateUser = get_current_user_id();

    // Prepare statement & DB Access
    $sql = "UPDATE user_department_assignments SET begin = ?, end = ?, create_comment = ? WHERE id = ?";
    if($stmt = $mysqli->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("sssi", $beginPlaceholder, $endPlaceholder, $commentPlaceholder, $AssignmentID);

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            // Return success + new sondereinteilung ID
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

function delete_user_sondereinteilung($mysqli, $AssignmentID, $commentPlaceholder){

    $DeleteUser = get_current_user_id();
    $DeleteTime = date('Y-m-d G:i:s');

    // Prepare statement & DB Access
    $sql = "UPDATE user_department_assignments SET delete_user = ?, delete_time = ?, delete_comment = ? WHERE id = ?";
    if($stmt = $mysqli->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("issi", $DeleteUser, $DeleteTime, $commentPlaceholder, $AssignmentID);

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            // Return success + new sondereinteilung ID
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

function add_user_dienstgruppe_zugehoerigkeit($mysqli, $UserID, $uePlaceholder, $beginPlaceholder, $endPlaceholder, $commentPlaceholder){

    $CreateUser = get_current_user_id();

    // Prepare statement & DB Access
    $sql = "INSERT INTO user_bereitschaftsdienst_assignments (user, bd_type, begin, end, create_user, create_comment) VALUES (?,?,?,?,?,?)";
    if($stmt = $mysqli->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("iissis", $UserID, $uePlaceholder, $beginPlaceholder, $endPlaceholder, $CreateUser, $commentPlaceholder);

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            // Return success + new sondereinteilung ID
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

function edit_user_dienstgruppe_zugehoerigkeit($mysqli, $AssignmentID, $beginPlaceholder, $endPlaceholder, $commentPlaceholder){

    $CreateUser = get_current_user_id();

    // Prepare statement & DB Access
    $sql = "UPDATE user_bereitschaftsdienst_assignments SET begin = ?, end = ?, create_comment = ? WHERE id = ?";
    if($stmt = $mysqli->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("sssi", $beginPlaceholder, $endPlaceholder, $commentPlaceholder, $AssignmentID);

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            // Return success + new sondereinteilung ID
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

function delete_user_dienstgruppe_zugehoerigkeit($mysqli, $AssignmentID, $commentPlaceholder){

    $DeleteUser = get_current_user_id();
    $DeleteTime = date('Y-m-d G:i:s');

    // Prepare statement & DB Access
    $sql = "UPDATE user_bereitschaftsdienst_assignments SET delete_user = ?, delete_time = ?, delete_comment = ? WHERE id = ?";
    if($stmt = $mysqli->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("issi", $DeleteUser, $DeleteTime, $commentPlaceholder, $AssignmentID);

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            // Return success + new sondereinteilung ID
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

function get_user_infos_by_id_from_list($userID, $userList){

    $Answer = false;

    foreach ($userList as $user){
        if($userID == $user['id']){
            $Answer = $user;
        }
    }

    return $Answer;
}