<?php

function session_manager($requiredRole='User'){

    // Initialize the session
    session_start();

    return false;

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