<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'house_rent');

// System Settings
define('SITE_NAME', 'HouseRent Africa');

// Dynamic Site URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];

// Detect if localhost (and potentially subdir) or production
if (!defined('SITE_URL')) {
    if ($host === 'localhost' || $host === '127.0.0.1') {
        define('SITE_URL', 'http://localhost/house');
    } else {
        define('SITE_URL', $protocol . "://" . $host);
    }
}

define('CURRENCY', 'ZMW');
define('SUBSCRIPTION_FEE', 20.00); // K20

// Email Configuration (SMTP)
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@example.com');
define('SMTP_PASS', 'your-app-password');
define('SMTP_FROM', 'no-reply@example.com');
define('SMTP_FROM_NAME', SITE_NAME);

// Google OAuth Configuration
define('GOOGLE_CLIENT_ID', 'your-google-client-id');
define('GOOGLE_CLIENT_SECRET', 'your-google-client-secret');
define('GOOGLE_REDIRECT_URL', SITE_URL . '/'); 

// Google Maps Configuration
define('GOOGLE_MAPS_API_KEY', 'your-google-maps-api-key');

// Lenco Payment Configuration
define('LENCO_BASE_URL', 'https://api.lenco.co/access/v2');
define('LENCO_KEY', 'your-lenco-key');
define('LENCO_SECRET', 'your-lenco-secret');
define('LENCO_WEBHOOK_SECRET', 'your-webhook-secret');

// Feature Flags
// Fetch from DB if available, else fallback to defaults
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
    $pdo_config = new PDO($dsn, DB_USER, DB_PASS);
    $pdo_config->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    
    // Fetch settings
    $stmt = $pdo_config->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('enable_free_trial', 'free_trial_duration')");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $enable_free_trial = isset($settings['enable_free_trial']) ? (bool)$settings['enable_free_trial'] : true;
    $free_trial_duration = isset($settings['free_trial_duration']) ? (int)$settings['free_trial_duration'] : 30;
    
    // Close connection
    $pdo_config = null;
} catch (Exception $e) {
    // Fallback if DB connection fails or table doesn't exist
    $enable_free_trial = true;
    $free_trial_duration = 30;
}

define('ENABLE_FREE_TRIAL', $enable_free_trial);
define('FREE_TRIAL_DURATION', $free_trial_duration); // Days

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start Session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Security Helpers
require_once __DIR__ . '/../includes/security.php';
?>