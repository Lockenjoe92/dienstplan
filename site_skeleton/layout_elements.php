<?php

function nav_bar($LoggedIn=False, $UserRoles=''){

    if(!$LoggedIn){
        $response = '<nav class="navbar sticky-top navbar-expand-lg bg-light"><div class="container-fluid"><span class="navbar-brand mb-0 h1">'.SITENAME.'</span></div></nav>';
    } else {

        $UserRoles = explode(',',$UserRoles);
        $Organisationseinheiten = get_list_of_all_departments(connect_db());

        $AbwesenheitenLinks = '<li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Urlaub & Abwesenheit
          </a>
          <ul class="dropdown-menu">';

        if(in_array('ausfaelle_1', $UserRoles)){
        $AbwesenheitenLinks .= '<li><a class="dropdown-item" href="urlaubsplan.php?org_ue=1">Urlaubsplanung Übersicht Anästhesie</a></li>
            <li><a class="dropdown-item" href="abwesenheiten_management.php?org_ue=1">Abwesenheitsanträge Anästhesie</a></li>
            <li><hr class="dropdown-divider"></li>';}

        if(in_array('ausfaelle_2', $UserRoles)){
            $AbwesenheitenLinks .= '<li><a class="dropdown-item" href="urlaubsplan.php?org_ue=2">Urlaubsplanung Übersicht Intensiv</a></li>
            <li><a class="dropdown-item" href="abwesenheiten_management.php?org_ue=2">Abwesenheitsanträge Intensiv</a></li>
            <li><hr class="dropdown-divider"></li>';}

        $AbwesenheitenLinks .= '<li><a class="dropdown-item" href="abwesenheiten_user.php">Meine Abwesenheiten</a></li>
          </ul>
        </li>';

        $DienstplanLinks = '<li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Dienstplan
          </a>
          <ul class="dropdown-menu">';


        foreach ($Organisationseinheiten as $UE){
            $srcstring = 'dienstplan_'.$UE['id'];
            if(in_array($srcstring, $UserRoles)){
                $DienstplanLinks .= '<li><a class="dropdown-item" href="wunschdienst_und_absentenuebersicht.php?org_ue='.$UE['id'].'">WuUP-Übersicht - '.$UE['name'].'</a></li>
            <li><a class="dropdown-item" href="dienstwuensche_management.php?org_ue='.$UE['id'].'">Dienstplanwünsche - '.$UE['name'].'</a></li>
            ';}
            if($UE['id']==1){
                $srcstring = 'bereitschaftsdienstplan_'.$UE['id'];
                if(in_array($srcstring, $UserRoles)){
                    $DienstplanLinks .= '<li><a class="dropdown-item" href="bereitschaftsdienstplan_anaesthesie.php">Bereitschaftsdienstplanung</a></li>';
                }
            }
            $DienstplanLinks .= '<li><hr class="dropdown-divider"></li>';
        }

        $DienstplanLinks .= '<li><a class="dropdown-item" href="bereitschaftsdienstplan_anaesthesie_user.php">Monatspläne Bereitschaftsdienst</a></li>';
        $DienstplanLinks .= '
            <li><a class="dropdown-item" href="dienstplan_user.php">Mein Bereitschaftsdienstplan</a></li>
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

        $UserSettingsCounter = 0;
        $UserSettingsLinks = '<li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Einstellungen
          </a>
          <ul class="dropdown-menu">';
        if(in_array('department_settings_view', $UserRoles)){
            $UserSettingsLinks .= '<li><a class="dropdown-item" href="department_settings.php">Abteilungseinstellungen</a></li>';
            if(LOGINMODE!='OIDC') {
                $UserSettingsLinks .= '<li><hr class="dropdown-divider"></li>';
            }
            $UserSettingsCounter++;
        }
        if(LOGINMODE!='OIDC'){
            $UserSettingsLinks .= '<li><a class="dropdown-item" href="change_password_user.php">Passwort ändern</a></li>';
            $UserSettingsCounter++;
        }
        $UserSettingsLinks .= '</ul></li>';

        // Dont show item when empty
        if($UserSettingsCounter==0){
            $UserSettingsLinks = '';
        }

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

function card_builder($CardTitle, $CardSubtitle, $CardContent, $wrapWithCol=false, $horizontzalMode=''){

    $HTML = '<div class="card '.$horizontzalMode.'">
                   <div class="card-body">';

    $HTML .= '<h5 class="card-title">'.$CardTitle.'</h5>';

    if(!empty($CardSubtitle)){
        $HTML .= '<h6 class="card-subtitle mb-2 text-muted">'.$CardSubtitle.'</h6>';
    }

    $HTML .= '<p class="card-text">'.$CardContent.'</p>';

    $HTML .= '</div>
              </div>';

    if($wrapWithCol){
        $HTML = "<div class='col'>".$HTML."</div>";
    }

    return $HTML;
}

function grid_gap_generator($HTML, $GapSize=2){

    return '<div class="d-grid gap-'.$GapSize.'">'.$HTML.'</div>';

}

function accordion_builder($HTML){

    $Answer = '<div class="accordion">'.$HTML.'</div>';

}

function accordion_item_builder($Header, $ContentHTML, $AlwaysOpen=false){

    $Answer = '<div class="accordion-item">';
    $Answer .= '</div>';

    $Answer .= '</div>';
    return $Answer;
}