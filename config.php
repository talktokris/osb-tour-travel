<?php
// Basic configuration shared by all pages

/**
 * Minimal .env loader for local development.
 */
$envPath = __DIR__ . '/.env';
if (is_file($envPath) && is_readable($envPath)) {
    $envLines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (is_array($envLines)) {
        foreach ($envLines as $envLine) {
            $envLine = trim((string) $envLine);
            if ($envLine === '' || str_starts_with($envLine, '#') || !str_contains($envLine, '=')) {
                continue;
            }
            [$envKey, $envValue] = explode('=', $envLine, 2);
            $envKey = trim($envKey);
            $envValue = trim($envValue, " \t\n\r\0\x0B\"'");
            if ($envKey !== '' && getenv($envKey) === false) {
                putenv($envKey . '=' . $envValue);
                $_ENV[$envKey] = $envValue;
            }
        }
    }
}

$env = static function (string $key, string $default = ''): string {
    $value = getenv($key);
    if ($value === false || $value === null || $value === '') {
        return $default;
    }
    return (string) $value;
};

// Database config (prefers environment / .env values)
$DB_HOST = $env('DB_HOST', '127.0.0.1');
$DB_PORT = (int) $env('DB_PORT', '3308');
$DB_NAME = $env('DB_DATABASE', 'ossb_tour');
$DB_USER = $env('DB_USERNAME', 'root');
$DB_PASS = $env('DB_PASSWORD', 'root');

// Connect to database
mysqli_report(MYSQLI_REPORT_OFF);
$mysqli = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
if ($mysqli->connect_errno) {
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

