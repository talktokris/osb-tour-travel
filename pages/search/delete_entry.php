<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/search_module_service.php';
require_once __DIR__ . '/../../includes/file_module_service.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=search');
    exit;
}

$token = (string) ($_POST['_token'] ?? '');
if (!file_module_csrf_validate($token)) {
    file_module_flash_set('error', 'Invalid security token.');
    $fallback = 'index.php?page=search';
    header('Location: ' . search_module_safe_redirect((string) ($_POST['redirect'] ?? ''), $fallback));
    exit;
}

$fileId = trim((string) ($_POST['file_id'] ?? ''));
$user = search_module_username();
$fallback = 'index.php?page=search';
$redirect = search_module_safe_redirect((string) ($_POST['redirect'] ?? ''), $fallback);

if (search_module_delete_entry($mysqli, $user, $fileId)) {
    file_module_flash_set('success', 'Record deleted.');
} else {
    file_module_flash_set('error', 'Could not delete this record. It may not exist or you may not have access.');
}

header('Location: ' . $redirect);
exit;
