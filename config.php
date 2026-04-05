<?php
// Basic configuration shared by all pages

// Database config – adjust if your Docker MySQL changes
$DB_HOST = '127.0.0.1';
$DB_PORT = 3308;
$DB_NAME = 'ossb_tour';
$DB_USER = 'root';
$DB_PASS = 'root';

// Connect to database
$mysqli = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, (int) $DB_PORT);
if ($mysqli->connect_error) {
    die('Database connection failed: ' . htmlspecialchars($mysqli->connect_error));
}
$mysqli->set_charset('utf8mb4');
$mysqli->query("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simple escape helper
function h(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Attempt to recover Arabic text that was saved as mojibake.
 */
function normalize_arabic_text(string $value): string {
    if ($value === '') {
        return $value;
    }
    if (preg_match('/[\x{0600}-\x{06FF}]/u', $value)) {
        return $value;
    }
    if (!preg_match('/[ØÙ]/u', $value)) {
        return $value;
    }
    if (function_exists('mb_convert_encoding')) {
        $converted = @mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
        if (is_string($converted) && preg_match('/[\x{0600}-\x{06FF}]/u', $converted)) {
            return $converted;
        }
    }
    return $value;
}

/**
 * Optional base URL for driver app push (legacy GET: ?regId=&message=).
 * Example: http://wetrf.com/mobile_app_files/gcm_app_sms/send_message.php
 * Leave empty to skip network send; notification page still works for display.
 */
$DRIVER_PUSH_NOTIFICATION_URL = '';

