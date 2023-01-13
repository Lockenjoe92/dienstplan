<?php

// Generates HTML-Body. Takes input-String and wraps it with the Footer & Skript commands
function site_body($SiteTitle, $Content, $loggedIn = false, $UserRoles=[]){

    //Initialize Doctype and Header
    $Return = '<!DOCTYPE html>
                <html lang="de">
                    <head>
                        <meta charset="UTF-8">
                        <title>'.$SiteTitle.'</title>
                        <link rel="stylesheet" type="text/css" href="css/bootstrap.css" />
                    </head>';

    // Initialize and Fill Body
    $Return .= '<body>
                    '.container_builder(nav_bar($loggedIn,$UserRoles)).'
                    '.container_builder($Content).'
                    <script src="/js/bootstrap.bundle.js"></script>
                    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
                    <script src="js/main.js"></script>
                </body>';

    return $Return;
}