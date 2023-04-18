<?php
include_once "config/dependencies.php";

// Build content
$HTML = "<h1 class='align-content-center'>Passwort ändern</h1>";

// Define variables and initialize with empty values
$mysqli = connect_db();
$new_password = $confirm_password = "";
$new_password_err = $confirm_password_err = "";
$presentationMode = "show_card";

// Check if the user is already logged in, if yes then redirect him to welcome page
$Nutzergruppen = session_manager('nutzer');
$ActionLink = 'self';
$param_id = get_current_user_id();

// Code for resetting passwords manually -> needed if all admins are not capable of logging in
#if(isset($_GET['alt_user_id'])){
#    $SelectedUser = $_GET['alt_user_id'];
#    if($SelectedUser>0){
#        $ActionLink = 'change_password_user.php?alt_user_id='.$SelectedUser.'';
#        $param_id = $SelectedUser;
#    } else {
#        $ActionLink = 'self';
#        $param_id = get_current_user_id();
#    }
#} else {
#    $ActionLink = 'self';
#    $param_id = get_current_user_id();
#}

if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Validate new password
    if(empty(trim($_POST["new_password"]))){
        $new_password_err = "Bitte gib dein neues Passwort ein.";
    } elseif(strlen(trim($_POST["new_password"])) < 6){
        $new_password_err = "Passwort muss mindestens 6 Zeichen enthalten.";
    } else{
        $new_password = trim($_POST["new_password"]);
    }

    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Bitte bestätige dein Passwort.";
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($new_password_err) && ($new_password != $confirm_password)){
            $confirm_password_err = "Passwörter stimmen nicht überein.";
        }
    }

    // Check input errors before updating the database
    if(empty($new_password_err) && empty($confirm_password_err)){
        // Prepare an update statement
        $sql = "UPDATE users SET password = ? WHERE id = ?";

        if($stmt = $mysqli->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("si", $param_password, $param_id);

            // Set parameters
            $param_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Password updated successfully. Destroy the session, and redirect to login page
                session_destroy();
                header("location: login.php?err_mode=3");
                exit();
            } else{
                $presentationMode='show_card';
                $Error = 'Ändern des Passwortes fehlgeschlagen - bitte versuche es erneut!';
            }

            // Close statement
            $stmt->close();
        }
    }

    // Close connection
    $mysqli->close();
}

if($presentationMode=='show_card'){
    $FormHTML = form_group_input_password('Neues Passwort', 'new_password', $new_password, true, $new_password_err);
    $FormHTML .= form_group_input_password('Neues Passwort wiederholen', 'confirm_password', $confirm_password, true, $confirm_password_err);
    $FormHTML .= "<br>";
    $FormHTML .= form_group_continue_return_buttons(true, 'Ändern', 'change_password_action', 'btn-primary', true, 'Zurück', 'change_pass_go_back', 'btn-primary', true, 'dashboard.php');

    // Gap it
    $FormHTML = grid_gap_generator($FormHTML);
    $FORM = form_builder($FormHTML, $ActionLink, 'POST');
    $HTML .= card_builder('Passwort ändern','', $FORM);
} else {
    $FormHTML = $Error."<br>";
    $FormHTML .= form_group_continue_return_buttons(false, '', '', 'btn-primary', true, 'Zurück', 'change_pass_go_back', 'btn-primary', true, 'dashboard.php');
    $FORM = form_builder($FormHTML, $ActionLink, 'POST');
    $HTML .= card_builder('Passwort ändern','', $FORM);
}


echo site_body('Passwort ändern', $HTML, true, $Nutzergruppen);