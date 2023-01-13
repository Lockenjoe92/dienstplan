<?php

function nav_bar($LoggedIn=False, $UserRoles=[]){

    if(!$LoggedIn){
        $response = '<nav class="navbar" style="background-color: #eeeee4;"><div class="container-fluid"><span class="navbar-brand mb-0 h1">'.SITENAME.'</span></div></nav>';
    } else {
        $response = '<nav class="navbar" style="background-color: #eeeee4;"><div class="container-fluid"><a class="navbar-brand" href="dashboard.php">'.SITENAME.'</a></div></nav>';
    }

    return $response;

}

function container_builder($content, $Class='container'){

    return "<div class='".$Class."'>".$content."</div>";

}

function alert_builder($alert, $class='alert-danger'){

    return '<div class="alert '.$class.'">' . $alert . '</div>';

}