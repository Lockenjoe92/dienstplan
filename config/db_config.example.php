<?php
/* Database credentials. Assuming you are running MySQL
change according to your setup! */
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'demo');

/* Attempt to connect to MySQL database */
function connect_db()
{   $mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    // Check connection
    if ($mysqli === false) {
        die("ERROR: Could not connect. " . $mysqli->connect_error);
    } else {
        return $mysqli;
    }
}
