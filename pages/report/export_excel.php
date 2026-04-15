<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/file_module_service.php';
require_once __DIR__ . '/../../includes/report_module_service.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

/**
 * Remove a temp directory recursively.
 */
function report_export_delete_dir(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }
    $items = @scandir($dir);
    if (!is_array($items)) {
        return;
    }
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            report_export_delete_dir($path);
        } else {
            @unlink($path);
        }
    }
    @rmdir($dir);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=report');
    exit;
}

$token = trim((string) ($_POST['csrf'] ?? ''));
if (!file_module_csrf_validate($token)) {
    file_module_flash_set('error', 'Invalid session token. Please refresh and try again.');
    header('Location: index.php?page=report');
    exit;
}

$mode = report_module_normalize_mode((string) ($_POST['mode'] ?? 'agent'));
$post = [];
foreach ($_POST as $k => $v) {
    if (is_string($v)) {
        $post[$k] = trim($v);
    }
}
$post['mode'] = $mode;

$run = report_module_run($mysqli, $mode, $post);
if (!$run['ok']) {
    file_module_flash_set('warning', (string) ($run['error'] ?? 'No results found to export.'));
    header('Location: index.php?page=report&mode=' . rawurlencode($mode));
    exit;
}

$rows = report_module_export_rows($run);
if ($rows === []) {
    file_module_flash_set('warning', 'No rows available for Excel export.');
    header('Location: index.php?page=report&mode=' . rawurlencode($mode));
    exit;
}

$filename = report_module_export_filename($mode);

$writer = new Writer();
$tempBase = __DIR__ . '/../../uploads/spout_tmp';
if (!is_dir($tempBase)) {
    @mkdir($tempBase, 0777, true);
}
@chmod($tempBase, 0777);
if (!is_dir($tempBase) || !is_writable($tempBase)) {
    file_module_flash_set('warning', 'Export temp folder is not writable. Please contact admin.');
    header('Location: index.php?page=report&mode=' . rawurlencode($mode));
    exit;
}

$tempFolder = $tempBase . '/req_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4));
if (!is_dir($tempFolder)) {
    @mkdir($tempFolder, 0777, true);
}
@chmod($tempFolder, 0777);
if (!is_dir($tempFolder) || !is_writable($tempFolder)) {
    file_module_flash_set('warning', 'Export temp folder is not writable. Please contact admin.');
    header('Location: index.php?page=report&mode=' . rawurlencode($mode));
    exit;
}

// Force browser download behavior.
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$writer->getOptions()->setTempFolder($tempFolder);
$writer->openToBrowser($filename);
foreach ($rows as $r) {
    $writer->addRow(Row::fromValues($r));
}
$writer->close();
report_export_delete_dir($tempFolder);
exit;
