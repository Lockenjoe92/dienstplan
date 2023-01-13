<?php

function session_manager($requiredRole='nutzer'){

    // Initialize stuff
    session_start();
    $mysqli = connect_db();
    $Validated = session_valid($mysqli, $_SESSION['user'], $_SESSION['secret']);

    if(!$Validated){
        // Session could not be verified
        session_destroy();
        header("location: login.php");
        exit;
    } else {

        // Check TTL
        if(strtotime($Validated) < time()){

            // Session has timed out
            session_destroy();
            header("location: login.php");
            exit;

        } else {

            // Session is valid an has not timed out - now check if user has necessary privileges
            $Nutzergruppen = load_user_usergroups($mysqli, $_SESSION['user']);
            if(in_array($requiredRole,explode(',', $Nutzergruppen))){
                return $Nutzergruppen;
            } else {

                // User is logged in and in a valid session, but lacks permissions -> send them back to dashboard
                header("location: dashboard.php");
                exit;

            }
        }

    }
}

function session_valid($mysqli, $User, $Secret){

    // initilize variable
    $TTL = "";

    // Load Session based on Secret
    $sql = "SELECT id, ttl FROM sessions WHERE user = ? AND secret = ?";
    if($stmt = $mysqli->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("is", $User, $Secret);

        // Attempt to execute the prepared statement
        if($stmt->execute()) {

            // Store result
            $stmt->store_result();

            // Check if only one session exists
            if ($stmt->num_rows == 1) {
                $stmt->bind_result($ID, $TTL);
                if($stmt->fetch()){
                    return $TTL;
                }
            }
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function create_session($User){

    $mysqli = connect_db();
    $Answer=[];
    $randomString = bin2hex(random_bytes(30));
    $TTLCommand = "+".SESSIONLIFETIME." minutes";

    // Prepare an insert statement
    $sql = "INSERT INTO sessions (user,secret,ttl) VALUES (?,?,?)";
    if($stmt = $mysqli->prepare($sql)){

        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("iss", $param_userid, $param_secret, $param_ttl);

        // Set parameters
        $param_userid = $User;
        $param_secret = $randomString;
        $param_ttl = date('Y-m-d G:i:s', strtotime($TTLCommand));

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            $stmt->close();
            $Answer['success']=true;
            $Answer['secret']=$randomString;
            return $Answer;
        } else{
            $stmt->close();
            $Answer['success']=false;
            return $Answer;
        }

    } else {
        $Answer['success']=false;
        return $Answer;
    }
}

function login_form($username = "", $password = "", $username_err='', $password_err='', $login_err=''){

    $HTML = "<h2>Login</h2><p>Bitte gib deine Anmeldedaten ein.</p>";

    // Show login alerts
    if(!empty($login_err)){
        $HTML .= alert_builder($login_err);
    }

    //Build the Form Parts
    $FormHTML = form_group_input_text('Nutzername', 'username', $username, true, $username_err);
    $FormHTML .= form_group_input_password('Passwort', 'password', $password, true, $password_err);
    $FormHTML .= form_group_continue_return_buttons(true, 'Login', 'login', 'btn-primary', false);

    // Wrap Form Parts in Form Object
    $HTML .= form_builder($FormHTML);

    return $HTML;
}