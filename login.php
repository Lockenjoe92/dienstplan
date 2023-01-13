<?php
// Include config file
include_once "./config/dependencies.php";

// Check if the user is already logged in, if yes then redirect him to welcome page
session_start();
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: dashboard.php");
    exit;
}

// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Check if username is empty
    if(empty(trim($_POST["username"]))){
        $username_err = "Bitte gib ein Nutzernamen an.";
    } else{
        $username = trim($_POST["username"]);
    }

    // Check if password is empty
    if(empty(trim($_POST["password"]))){
        $password_err = "Bitte gib dein Passwort ein.";
    } else{
        $password = trim($_POST["password"]);
    }

    // Validate credentials
    if(empty($username_err) && empty($password_err)){

        // prevent brute force
        sleep(1);

        // Connect DB
        $mysqli = connect_db();

        // Prepare a select statement
        $sql = "SELECT id, password FROM users WHERE username = ?";

        if($stmt = $mysqli->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_username);

            // Set parameters
            $param_username = $username;

            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Store result
                $stmt->store_result();

                // Check if username exists, if yes then verify password
                if($stmt->num_rows == 1){
                    // Bind result variables
                    $stmt->bind_result($Userid, $hashed_password);
                    if($stmt->fetch()){

                        if(password_verify($password, $hashed_password)){
                            // Password is correct, so start a new session object
                            session_start();

                            // create a new session in database
                            $DBSession = create_session($Userid);
                            if($DBSession['success']){

                                // Store data in session variables
                                $_SESSION["loggedin"] = true;
                                $_SESSION["secret"] = $DBSession['secret'];
                                $_SESSION["user"] = $Userid;

                                // Redirect user to welcome page
                                header("location: dashboard.php");
                            } else {
                                $login_err = "Fehler beim Initiieren der Sitzung.";
                            }

                        } else{
                            // Password is not valid, display a generic error message
                            $login_err = "Nutzername oder Passwort falsch.";
                        }
                    }
                } else{
                    // Username doesn't exist, display a generic error message
                    $login_err = "Nutzername oder Passwort falsch.";
                }
            } else{
                echo "Oops! Da ist etwas schief gegangen. Bitte versuche es später noch einmal.";
            }

            // Close statement
            $stmt->close();
        }
    }

    // Close connection
    $mysqli->close();
}

// Output Login Form
echo site_body('Login', login_form($username, $password, $username_err, $password_err, $login_err));
?>