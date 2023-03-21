<?php

// This file is used to be able to access all functions in all scripts by simply calling one file
// Connection Settings to MySQL-Database
include_once "./config/db_config.php";

// Site configuration Settings
include_once "./config/settings_config.php";

// Toolboxes
include_once "./tools/session_management.php";
include_once "./tools/user_management.php";
include_once "./tools/abwesenheiten_management.php";
include_once "./tools/wunschdienstplan_management.php";
include_once "./tools/department_management.php";
include_once "./tools/mail_management.php";
include_once "./tools/protocol_management.php";
include_once "./tools/bereitschaftsdienstplan_management.php";

// Website Skeleton Functions
include_once "./site_skeleton/site_skeleton.php";
include_once "./site_skeleton/form_elements.php";
include_once "./site_skeleton/table_elements.php";
include_once "./site_skeleton/layout_elements.php";

// Forms
include_once "./forms/session_management.php";

// Views
include_once "./views/workforcemanagement.php";
include_once "./views/abwesenheitenmanagement.php";
include_once "./views/urlaubsplanmanagement.php";
include_once "./views/wunschdienstplanmanagement.php";
include_once "./views/bereitschaftsdienstplanmanagement.php";
include_once "./views/dashboardcards.php";
