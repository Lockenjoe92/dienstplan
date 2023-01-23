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
