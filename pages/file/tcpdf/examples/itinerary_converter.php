<?php

declare(strict_types=1);

@ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
if (!ini_get('date.timezone')) {
    date_default_timezone_set('Asia/Kuala_Lumpur');
}

$appRoot = dirname(__DIR__, 4);
require_once $appRoot . '/config.php';
require_once $appRoot . '/includes/file_itinerary_pdf_service.php';

if (!($mysqli instanceof mysqli)) {
    http_response_code(500);
    echo 'Database not available';
    exit;
}

$fileCountNo = trim((string) ($_GET['file_count_no'] ?? ''));
if ($fileCountNo === '') {
    http_response_code(400);
    echo 'Missing file_count_no';
    exit;
}

file_itinerary_pdf_render($mysqli, $fileCountNo);
