<?php
/**
 * Application Configuration
 * 
 * This file contains all application-wide configuration constants.
 */

// Environment flag: 'development' or 'production'
define('ENV', 'development');

// Error reporting (only in development)
if (ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'elearning_platform');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application base URL
// Adjust this to match your installation path
define('BASE_URL', 'http://localhost/e-learning-platform/public/');

// Site name
define('SITE_NAME', 'E-Learning Platform');

// Session configuration
define('SESSION_LIFETIME', 900); // 15 minutes in seconds
define('SESSION_NAME', 'ELP_SESSION');

// Security settings
define('REMEMBER_ME_LIFETIME', 2592000); // 30 days in seconds
define('MAX_LOGIN_ATTEMPTS', 7);
define('LOCKOUT_DURATION', 600); // 10 minutes in seconds
define('EMAIL_CODE_EXPIRY', 3600); // 1 hour in seconds

// File upload settings
define('UPLOAD_DIR', __DIR__ . '/../public/uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// PayPal configuration
// PayPal keys are in config/paypal.php

// Timezone
date_default_timezone_set('UTC');

// Path constants
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');
