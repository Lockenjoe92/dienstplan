<?php

function nav_bar($LoggedIn=False, $UserRoles=[]){

    if(!$LoggedIn){
        $response = '<nav class="navbar sticky-top navbar-expand-lg bg-light"><div class="container-fluid"><span class="navbar-brand mb-0 h1">'.SITENAME.'</span></div></nav>';
    } else {
        $response = '<nav class="navbar sticky-top navbar-expand-lg bg-light">
                <div class="container-fluid">
                        <a class="navbar-brand" href="dashboard.php">'.SITENAME.'</a>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                        </button>
                        <ul class="navbar-nav justify-content-end mb-2 mb-lg-0">
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