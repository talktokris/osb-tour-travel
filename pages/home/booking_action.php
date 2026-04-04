<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/home_dashboard_service.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=home');
    exit;
}

$token = (string) ($_POST['_token'] ?? '');
if (!home_dashboard_csrf_validate($token)) {
    home_dashboard_flash_set('error', 'Invalid request token.');
    header('Location: index.php?page=home');
    exit;
}

$fileId = (int) ($_POST['file_id'] ?? 0);
$action = (string) ($_POST['action'] ?? '');
$returnLetter = trim((string) ($_POST['return_letter'] ?? ''));
$returnAz = trim((string) ($_POST['return_az'] ?? ''));
$returnTo = trim((string) ($_POST['return_to'] ?? 'home'));
$returnSupplier = trim((string) ($_POST['return_supplier'] ?? ''));

if ($action === 'confirm') {
    $status = 'Confirmed';
} elseif ($action === 'cancel') {
    $status = 'Cancel';
} else {
    home_dashboard_flash_set('error', 'Invalid action.');
    header('Location: ' . home_dashboard_build_home_url([
        'letter' => $returnLetter,
        'az' => $returnAz,
        'to' => $returnTo,
        'supplier' => $returnSupplier,
    ]));
    exit;
}

if ($fileId < 1) {
    home_dashboard_flash_set('error', 'Invalid booking.');
} elseif (!home_dashboard_set_conform_status($mysqli, $fileId, $status)) {
    home_dashboard_flash_set('error', 'Could not update booking.');
} else {
    home_dashboard_flash_set('success', $status === 'Confirmed' ? 'Booking confirmed.' : 'Booking cancelled.');
}

header('Location: ' . home_dashboard_build_home_url([
    'letter' => $returnLetter,
    'az' => $returnAz,
    'to' => $returnTo,
    'supplier' => $returnSupplier,
]));
exit;
