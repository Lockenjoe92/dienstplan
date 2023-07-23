<?php
// Include config file
include_once "./config/dependencies.php";
require __DIR__ . '/vendor/autoload.php';

if(LOGINMODE=='OIDC'){

    $provider = new League\OAuth2\Client\Provider\GenericProvider([
        'clientId'                => OIDCCLIENTID,    // The client ID assigned to you by the provider
        'clientSecret'            => OIDCCLIENTSECRET,    // The client password assigned to you by the provider
        'redirectUri'             => 'https://dienstplan.marcsprojects.de/login.php',
        'scopes' => ['openid groups'],
        'urlAuthorize'            => OIDCURLAUTHORIZE,
        'urlAccessToken'          => OIDCURLTOKEN,
        'urlResourceOwnerDetails' => OIDCURLRESOURCE]);

    // If we don't have an authorization code then get one
    session_start();
    if (!isset($_GET['code'])) {

        // Fetch the authorization URL from the provider; this returns the
        // urlAuthorize option and generates and applies any necessary parameters
        // (e.g. state).
        $authorizationUrl = $provider->getAuthorizationUrl();

        // Get the state generated for you and store it to the session.
        $_SESSION['oauth2state'] = $provider->getState();

        // Optional, only required when PKCE is enabled.
        // Get the PKCE code generated for you and store it to the session.
        $_SESSION['oauth2pkceCode'] = $provider->getPkceCode();

        // Redirect the user to the authorization URL.
        header('Location: ' . $authorizationUrl);
        exit;

// Check given state against previously stored one to mitigate CSRF attack
    } elseif (empty($_GET['state']) || empty($_SESSION['oauth2state']) || $_GET['state'] !== $_SESSION['oauth2state']) {

            if (isset($_SESSION['oauth2state'])) {
                unset($_SESSION['oauth2state']);
            }

        // Redirect the user to the Welcome Page with Error-Info.
        header('Location: ' . 'https://dienstplan.marcsprojects.de/welcome.php?mode=sess_err&details=OAUTH2 Err');
        exit;

    } else {

        try {

            // Optional, only required when PKCE is enabled.
            // Restore the PKCE code stored in the session.
            $provider->setPkceCode($_SESSION['oauth2pkceCode']);

            // Try to get an access token using the authorization code grant.
            $accessToken = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);

            // We have an access token, which we may use in authenticated
            // requests against the service provider's API.
            // Now double-check if it has expired
            if ($accessToken->hasExpired()) {
                header('Location: ' . 'https://dienstplan.marcsprojects.de/welcome.php?mode=timeout');
                exit;
            } else {

                // Now let's load user mail as ID and assigned User privileges
                $resourceOwnerArray = $provider->getResourceOwner($accessToken)->toArray();
                $UserMail = strtolower($resourceOwnerArray['sub']);
                #$UserGroups = explode(',', $resourceOwnerArray['groups']);

                // Prepare a select statement
                // Connect DB
                $mysqli = connect_db();
                $sql = "SELECT id, inaktiv_seit FROM users WHERE mail = ?";

                if ($stmt = $mysqli->prepare($sql)) {
                    // Bind variables to the prepared statement as parameters
                    $stmt->bind_param("s", $param_usermail);

                    // Set parameters
                    $param_usermail = $UserMail;

                    // Attempt to execute the prepared statement
                    if ($stmt->execute()) {

                        // Store result
                        $stmt->store_result();

                        // Check if username exists, if yes then verify password
                        if ($stmt->num_rows == 1) {
// Bind result variables
                            $stmt->bind_result($Userid, $InactiveDate);

                            if ($stmt->fetch()) {

                                $Continue = false;
                                if ($InactiveDate == null) {
                                    $Continue = true;
                                } else {
                                    if (time() < strtotime($InactiveDate)) {
                                        $Continue = true;
                                    }
                                }

                                if ($Continue) {
                                    // Password is correct, so start a new session object
                                    // create a new session in database
                                    $DBSession = create_session($Userid);
                                    if ($DBSession['success']) {

                                        // Store data in session variables
                                        $_SESSION["loggedin"] = true;
                                        $_SESSION["secret"] = $DBSession['secret'];
                                        $_SESSION["accessTokenOauth"] = $accessToken;
                                        $_SESSION["user"] = $Userid;

                                        // Redirect user to welcome page
                                        header("location: https://dienstplan.marcsprojects.de/dashboard.php");
                                        exit;
                                    } else {
                                        $login_err = "Fehler beim Initiieren der Sitzung.";
                                    }

                                } else {
                                    // Password is not valid, display a generic error message
                                    $login_err = "Nutzer ist nicht mehr im System aktiviert!";
                                }
                            }
                        } else {
                            // Username doesn't exist, display a generic error message
                            $login_err = "Nutzer nicht im System angelegt.";
                        }
                    } else {
                        $login_err = "Oops! Da ist etwas schief gegangen. Bitte versuche es später noch einmal.";
                    }

                    if (!empty($login_err)) {
                        header('Location: ' . 'https://dienstplan.marcsprojects.de/welcome.php?mode=sess_err&details='.$login_err);
                        exit;
                    }
                }

            }
        }
        catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            // Failed to get the access token or user details.
            // Redirect the user to the Welcome Page with Error-Info.
            header('Location: ' . 'https://dienstplan.marcsprojects.de/welcome.php?mode=sess_err&oidc='.$e->getMessage().'');
            exit;
            #exit($e->getMessage());
        }
    }

} elseif(LOGINMODE=='OIDC2'){

    $provider = new League\OAuth2\Client\Provider\GenericProvider([
        'clientId'                => OIDCCLIENTID,    // The client ID assigned to you by the provider
        'clientSecret'            => OIDCCLIENTSECRET,    // The client password assigned to you by the provider
        'redirectUri'             => 'https://dienstplan.marcsprojects.de/login.php',
        'scopes' => ['openid groups'],
        'urlAuthorize'            => OIDCURLAUTHORIZE,
        'urlAccessToken'          => OIDCURLTOKEN,
        'urlResourceOwnerDetails' => OIDCURLRESOURCE]);

    // Prepare the token request parameters
    $options = [
        'grant_type' => 'password',
        'username'   => 'anhaefm1',
        'password'   => 'Stocherkahn196',
    ];

    // Get the access token
    $accessToken = $provider->getAccessToken('password', $options);

    // Now let's load user mail as ID and assigned User privileges
    $resourceOwnerArray = $provider->getResourceOwner($accessToken)->toArray();
    $UserMail = strtolower($resourceOwnerArray['sub']);

    var_dump($UserMail);

} else {
    // Check if the user is already logged in, if yes then redirect him to welcome page
    session_start();
    if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
        header("location: dashboard.php");
        exit;
    }

// Define variables and initialize with empty values
    $username = $password = "";
    $username_err = $password_err = $login_err = "";

// Check if user has been bounced back from other page due to session expiration or failed validation
    if(isset($_GET['err_mode'])){
        if($_GET['err_mode']== 1){
            $login_err = "Die Sitzung konnte nicht verifiziert werden! Bitte melden Sie sich erneut an!";
        } elseif ($_GET['err_mode']== 2){
            $login_err = "Die Sitzung ist abgelaufen! Bitte melden Sie sich erneut an!";
        } elseif ($_GET['err_mode']== 3){
            $login_err = "Ihr Passwort wurde erfolgreich geändert! Bitte melden Sie sich erneut an!";
        }
    }

// Processing form data when form is submitted
    if($_SERVER["REQUEST_METHOD"] == "POST"){

        // Check if username is empty
        if(empty(trim($_POST["username"]))){
            $username_err = "Bitte gib deine Mitarbeiternummer an.";
        } else{
            $username = trim($_POST["username"]);
        }

        // Check if password is empty
        if(empty(trim($_POST["password"]))){
            $password_err = "Bitte gib dein Passwort ein.";
        } else{
            $password = trim($_POST["password"]);
        }

        // Connect DB
        $mysqli = connect_db();

        // Validate credentials
        if(empty($username_err) && empty($password_err)){

            // prevent brute force
            sleep(1);

            // Prepare a select statement
            $sql = "SELECT id, password FROM users WHERE mitarbeiternummer = ?";

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
}