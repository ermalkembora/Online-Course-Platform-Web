<?php
/**
 * Main Entry Point
 */

// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define application root
define('ROOT', dirname(__DIR__));
define('APPROOT', ROOT . '/app');


// Load configuration
require_once ROOT . '/config/config.php';
require_once ROOT . '/config/database.php';

// Load helpers
require_once ROOT . '/app/helpers/session_helper.php';
require_once ROOT . '/app/helpers/auth_helper.php';
require_once ROOT . '/app/helpers/url_helper.php';

// Load core classes
require_once ROOT . '/app/core/Controller.php';
require_once ROOT . '/app/core/App.php';

// Initialize and run the application
$app = new App();
$app->run();
