<?php
// config/config.php

// Application Settings
define('APP_NAME', 'User Management System');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/user_management_system/public/');

// Session Settings
define('SESSION_LIFETIME', 3600); // 1 hour
define('REMEMBER_ME_LIFETIME', 2592000); // 30 days

// Pagination
define('RECORDS_PER_PAGE', 10);

// File Upload Settings
define('MAX_FILE_SIZE', 5242880); // 5MB
define('UPLOAD_PATH', '../uploads/');

// Date/Time Format
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'M d, Y');
define('DISPLAY_DATETIME_FORMAT', 'M d, Y h:i A');

// Security
define('ENCRYPTION_KEY', 'your-secret-encryption-key-here');
define('PASSWORD_MIN_LENGTH', 8);

// Error Reporting (Set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Manila');

// Autoload Classes
spl_autoload_register(function($class) {
    $paths = [
        '../models/',
        '../controllers/',
        '../includes/'
    ];
    
    foreach($paths as $path) {
        $file = $path . $class . '.php';
        if(file_exists($file)) {
            require_once $file;
            break;
        }
    }
});

// Start Session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    session_start();
}

// Include helper files
require_once '../includes/functions.php';
require_once '../includes/security.php';
require_once '../includes/session.php';
