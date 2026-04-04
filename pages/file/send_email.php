<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/file_module_service.php';
require_once __DIR__ . '/../../includes/file_mail_service.php';

$currentPage = 'file_send_email';

if (!file_module_has_agent()) {
    file_module_render_agent_required();
}

$fcn = trim((string) ($_GET['file_count_no'] ?? ''));
$uname = (string) ($_SESSION['user_name'] ?? '');
$skip = isset($_GET['skip']);

if ($fcn === '' || !file_module_user_can_access_file_count($mysqli, $fcn, $uname)) {
    require __DIR__ . '/../../includes/header.php';
    require __DIR__ . '/../../includes/nav.php';
    echo '<div class="p-4 alert alert-warning">Invalid file.</div>';
    require __DIR__ . '/../../includes/footer.php';
    exit;
}

if ($skip) {
    file_module_flash_set('success', 'Skipped email process.');
    header('Location: index.php?page=file_preview&file_count_no=' . rawurlencode($fcn));
    exit;
}

$res = file_mail_send_all($mysqli, $fcn, $uname);
$msg = implode(' ', $res['messages']);
if ($res['ok']) {
    file_module_flash_set('success', 'Emails sent. ' . $msg);
} else {
    file_module_flash_set('error', 'Email finished with issues. ' . $msg);
}
header('Location: index.php?page=file_preview&file_count_no=' . rawurlencode($fcn));
exit;
