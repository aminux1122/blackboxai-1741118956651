<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'salon_beaute');

// Website configuration
define('SITE_NAME', 'Salon de Coiffure');
define('SITE_URL', 'http://localhost/salon-beaute');
define('ADMIN_EMAIL', 'admin@salon.com');

// Directory Paths
define('ROOT_PATH', dirname(dirname(__FILE__)));
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('UPLOADS_PATH', ROOT_PATH . '/assets/uploads');
define('LOGS_PATH', ROOT_PATH . '/logs');

// Session configuration
ini_set('session.cookie_lifetime', 60 * 60 * 24 * 7); // 1 week
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 7); // 1 week
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Africa/Casablanca');

// Default language
define('DEFAULT_LANG', 'fr');
