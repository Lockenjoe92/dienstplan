<?php

// Generates HTML-Body. Takes input-String and wraps it with the Footer & Skript commands
function site_body($SiteTitle, $Content, $loggedIn = false, $UserRoles=[], $UseContainer=true){

    //Initialize Doctype and Header
    $Return = '<!DOCTYPE html>
                <html lang="de">
                    <head>
                        <meta charset="UTF-8">
                        <title>'.$SiteTitle.'</title>
                        <link href="https://unpkg.com/bootstrap-table@1.21.2/dist/bootstrap-table.min.css" rel="stylesheet">
                        <link rel="stylesheet" type="text/css" href="css/bootstrap.css" />
                        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
                        <style>.table-condensed{font-size: 10px;}</style>
                    </head>';

    // Initialize and Fill Body
    if($UseContainer){
        $Content = container_builder($Content);
    }

    $Return .= '<body>
                    '.nav_bar($loggedIn,$UserRoles).'
                    '.$Content.'
                    
                    <script src="/js/main.js"></script>
                    <script src="/js/bootstrap.bundle.js"></script>
                    <script src="js/jquery-3.6.3.min.js"></script>
                    
                    <script src="https://unpkg.com/bootstrap-table@1.21.2/dist/bootstrap-table.min.js"></script>
                    <script src="https://unpkg.com/bootstrap-table@1.21.2/dist/extensions/multiple-sort/bootstrap-table-multiple-sort.js"></script>
                    <script src="https://unpkg.com/bootstrap-table@1.21.2/dist/bootstrap-table-locale-all.min.js"></script>
                </body>';

    return $Return;
}