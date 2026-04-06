<?php

declare(strict_types=1);

@ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
if (!ini_get('date.timezone')) {
    date_default_timezone_set('UTC');
}
ob_start();
$targetDir = dirname(__DIR__, 4) . '/legacy/invoice/tcpdf/examples';
if (!is_dir($targetDir)) {
    http_response_code(500);
    echo 'Legacy PDF source folder not found.';
    exit;
}
$cwd = getcwd();
chdir($targetDir);
$_GET['statement_agent'] = (string) ($_GET['statement_agent'] ?? '');
include $targetDir . '/statement_agent_report_pdf.php';
chdir((string) $cwd);
$buf = ob_get_clean();
if ($buf !== '' && !headers_sent()) { echo $buf; }
