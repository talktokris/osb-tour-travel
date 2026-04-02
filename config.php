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

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simple escape helper
function h(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

