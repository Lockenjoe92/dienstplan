<?php

function nav_bar($LoggedIn=False, $UserRoles=''){

    if(!$LoggedIn){
        $response = '<nav class="navbar sticky-top navbar-expand-lg bg-light"><div class="container-fluid"><span class="navbar-brand mb-0 h1">'.SITENAME.'</span></div></nav>';
    } else {

        $UserRoles = explode(',',$UserRoles);

        $AbwesenheitenLinks = '<li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Urlaub & Abwesenheit
          </a>
          <ul class="dropdown-menu">';

        if(in_array('ausfaelle', $UserRoles)){
        $AbwesenheitenLinks .= '<li><a class="dropdown-item" href="urlaubsplan.php">Urlaubsplanung</a></li>
            <li><a class="dropdown-item" href="abwesenheiten_management.php">Abwesenheiten</a></li>
            <li><hr class="dropdown-divider"></li>';}

        $AbwesenheitenLinks .= '<li><a class="dropdown-item" href="abwesenheiten_user.php">Meine Abwesenheiten</a></li>
          </ul>
        </li>';

        $DienstplanLinks = '<li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Dienstplan
          </a>
          <ul class="dropdown-menu">';

        if(in_array('dienstplan', $UserRoles)){
            $DienstplanLinks .= '
            <li><a class="dropdown-item" href="dienstwuensche_management.php">Dienstplanwünsche</a></li>
            <li><hr class="dropdown-divider"></li>
            ';}

        $DienstplanLinks .= '
            <li><a class="dropdown-item" href="dienstplan_user.php">Mein Dienstplan</a></li>
          </ul>
        </li>';

        if(in_array('mitarbeitermanagement_view', $UserRoles)){
            $PersonalwesenLinks = '<li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Workforce
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="workforce_management.php">Mitarbeitermanagement</a></li>
          </ul>
        </li>';
        } else {
            $PersonalwesenLinks = '';
        }


        $UserSettingsLinks = '<li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Einstellungen
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="change_password_user.php">Passwort ändern</a></li>
          </ul>
        </li>';

        $response = '<nav class="navbar sticky-top navbar-expand-lg bg-light">
                <div class="container-fluid">
                        <a class="navbar-brand" href="dashboard.php">'.SITENAME.'</a>
                        <ul class="navbar-nav justify-content-end mb-2 mb-lg-0">
                            '.$DienstplanLinks.'
                            '.$AbwesenheitenLinks.'
                            '.$PersonalwesenLinks.'
                            '.$UserSettingsLinks.'
                            <li class="nav-item">
                                <a class="nav-link active" href="logout.php">Logout</a>
                            </li>
                        </ul>
                </div>
                </nav>';
    }

    return $response;

}

function container_builder($content, $Class='container'){

    return "<div class='".$Class."'>".$content."</div>";

}

function alert_builder($alert, $class='alert-danger'){

    return '<div class="alert '.$class.'">' . $alert . '</div>';

}

function card_builder($CardTitle, $CardSubtitle, $CardContent){

    $HTML = '<div class="card">
                   <div class="card-body">';

    $HTML .= '<h5 class="card-title">'.$CardTitle.'</h5>';

    if(!empty($CardSubtitle)){
        $HTML .= '<h6 class="card-subtitle mb-2 text-muted">'.$CardSubtitle.'</h6>';
    }

    $HTML .= '<p class="card-text">'.$CardContent.'</p>';

    $HTML .= '</div>
              </div>';
    return $HTML;
}

function grid_gap_generator($HTML, $GapSize=2){

    return '<div class="d-grid gap-'.$GapSize.'">'.$HTML.'</div>';

}