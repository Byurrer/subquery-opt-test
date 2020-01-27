<?php

ini_set('max_execution_time', 900);
ini_set("memory_limit", "1024M");

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

define("__DEBUG", true);

define('DB_NAME', 'test_db');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8');

define('SIZE_PATCH_GEN', 1000);
