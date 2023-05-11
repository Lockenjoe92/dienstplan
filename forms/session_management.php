<?php
function login_form($username = "", $password = "", $username_err='', $password_err='', $login_err=''){

    $CardHTML = $WrappedForm = "";

    // Show login alerts
    if(!empty($login_err)){
        $WrappedForm .= "<div class='p-3'></div>";
        $WrappedForm .= alert_builder($login_err);
    }

    //Build the Form Parts
    $FormHTML = '<div class="d-grid gap-2">';
    $FormHTML .= form_group_input_text('Mitarbeiternummer', 'username', $username, true, $username_err);
    $FormHTML .= form_group_input_password('Passwort', 'password', $password, true, $password_err);
    $FormHTML .= form_group_continue_return_buttons(true, 'Login', 'login', 'btn-primary', false);
    $FormHTML .= "</div>";

    // Put it in a card
    $CardHTML .= card_builder('Login', 'Bitte gib deine Anmeldedaten ein.', $FormHTML);

    // Wrap Form Parts in Form Object
    $WrappedForm .= '<div class="d-grid gap-3">';
    $WrappedForm .= "<div class='p-3'></div>";
    $WrappedForm .= '<div class="d-flex align-items-center justify-content-center p-3" style="height: 250px;">'.form_builder($CardHTML).'</div>';
    $WrappedForm .= "</div>";

    return $WrappedForm;
}

function login_card_oidc_mode($Message=''){

    $CardHTML = $WrappedForm = "";

    // Show login alerts
    if(!empty($Message)){
        $WrappedForm .= "<div class='p-3'></div>";
        $WrappedForm .= alert_builder($Message);
    }

    //Build the Form Parts
    $FormHTML = '<div class="d-grid gap-2">';
    $FormHTML .= form_group_continue_return_buttons(false, '', '', '', true, 'Login', 'login', 'btn-primary', true,'https://dienstplan.marcsprojects.de/login.php');
    $FormHTML .= "</div>";

    // Put it in a card
    $CardHTML .= card_builder('Login', 'Zur Anmeldung bitte den Login-Knopf drücken!', $FormHTML);

    // Wrap Form Parts in Form Object
    $WrappedForm .= '<div class="d-grid gap-3">';
    $WrappedForm .= "<div class='p-3'></div>";
    $WrappedForm .= '<div class="d-flex align-items-center justify-content-center p-3" style="height: 250px;">'.$CardHTML.'</div>';
    $WrappedForm .= "</div>";

    return $WrappedForm;

}