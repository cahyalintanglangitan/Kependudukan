<?php
// config/config.php
// Basic configuration settings
define('APP_NAME', 'Dashboard Kependudukan');
define('APP_VERSION', '1.0.0');

// Environment settings - change to 'production' for live site
define('ENVIRONMENT', 'development'); // development | production

// URL Configuration - adjust according to your setup
define('BASE_URL', 'http://localhost/kependudukan/');
define('SITE_NAME', 'Data Analytics Kependudukan');

// Path Configuration
define('ROOT_PATH', dirname(dirname(__FILE__)) . '/');
define('ASSETS_PATH', BASE_URL . 'assets/');
define('API_PATH', BASE_URL . 'api/');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'kependudukan');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', '3306');

// Error Reporting based on environment
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// File upload limits (if needed for future features)
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '10M');

// Security Headers (optional - uncomment if needed)
/*
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
*/

// Application constants
define('DEFAULT_PAGE', 'akta');
define('RECORDS_PER_PAGE', 20);
define('MAX_CHART_ITEMS', 15);

// Color scheme for charts
define('CHART_COLORS', [
    'primary' => '#3498db',
    'success' => '#2ecc71',
    'warning' => '#f39c12',
    'danger' => '#e74c3c',
    'purple' => '#9b59b6',
    'pink' => '#e91e63',
    'info' => '#17a2b8',
    'dark' => '#2c3e50'
]);

// Helper function to check if running in development
function isDevelopment() {
    return ENVIRONMENT === 'development';
}

// Helper function to log errors
function logError($message, $context = []) {
    $log_message = date('Y-m-d H:i:s') . ' - ' . $message;
    if (!empty($context)) {
        $log_message .= ' - Context: ' . json_encode($context);
    }
    error_log($log_message);
}

// Helper function to sanitize input
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Helper function to validate required parameters
function validateRequired($params, $required) {
    $missing = [];
    foreach ($required as $field) {
        if (!isset($params[$field]) || empty($params[$field])) {
            $missing[] = $field;
        }
    }
    return $missing;
}
?>