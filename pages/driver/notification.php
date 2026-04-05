<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/file_module_service.php';
require_once __DIR__ . '/../../includes/driver_module_service.php';

if (!file_module_has_agent()) {
    file_module_render_agent_required();
}

$currentPage = 'driver_notification';
$dName = trim((string) ($_GET['dName'] ?? ''));
$csrf = file_module_csrf_token();
$flash = file_module_flash_get();

$driverRow = driver_module_get_driver_by_name($mysqli, $dName);
if ($driverRow === null && $dName !== '') {
    file_module_flash_set('warning', 'Driver not found for the given name.');
}

$defaultMessage = 'New Job Has been assigned to you';

if (($_SERVER['REQUEST_METHOD'] === 'POST') && (($_POST['driver_notify_submit'] ?? '') === '1')) {
    $tok = trim((string) ($_POST['csrf'] ?? ''));
    if (!file_module_csrf_validate($tok)) {
        file_module_flash_set('error', 'Invalid security token.');
        header('Location: index.php?page=driver_notification&dName=' . rawurlencode($dName));
        exit;
    }
    $regId = trim((string) ($_POST['regId'] ?? ''));
    $message = trim((string) ($_POST['message'] ?? ''));
    if ($message === '') {
        $message = $defaultMessage;
    }

    global $DRIVER_PUSH_NOTIFICATION_URL;
    $pushUrl = isset($DRIVER_PUSH_NOTIFICATION_URL) ? trim((string) $DRIVER_PUSH_NOTIFICATION_URL) : '';

    if ($pushUrl === '') {
        file_module_flash_set('success', 'Message prepared. Push URL is not configured in config.php — nothing was sent to the device.');
    } elseif ($regId === '') {
        file_module_flash_set('warning', 'No device id for this driver — push was not sent.');
    } else {
        $send = driver_module_send_push_notification($pushUrl, $regId, $message);
        if ($send['ok']) {
            file_module_flash_set('success', 'Notification request completed.');
        } else {
            file_module_flash_set('warning', 'Push failed: ' . ($send['error'] ?? 'unknown'));
        }
    }
    header('Location: index.php?page=driver_notification&dName=' . rawurlencode($dName));
    exit;
}

$driverNameDisp = trim((string) ($driverRow['driver_name'] ?? $dName));
$usernameDisp = trim((string) ($driverRow['Username'] ?? ''));
$deviceId = trim((string) ($driverRow['device_id'] ?? ''));

require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';
?>

<div class="flex gap-6 w-full">
    <aside class="hidden lg:block w-72 shrink-0">
        <?php
        $driverSub = '';
        require __DIR__ . '/sidebar.php';
        ?>
    </aside>

    <main class="flex-1 min-w-0 px-4">
        <div class="space-y-4 max-w-xl">
            <?php $breadcrumbCurrent = 'Driver — App notification';
            require __DIR__ . '/../../includes/breadcrumb.php'; ?>

            <?php if ($flash !== null): ?>
                <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : ($flash['type'] === 'error' ? 'alert-error' : 'alert-warning') ?> text-sm">
                    <?= h((string) $flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body space-y-4">
                    <h3 class="card-title text-lg justify-center">Sending App Notification</h3>

                    <?php if ($driverRow === null): ?>
                        <p class="text-sm text-base-content/70">Select a driver by completing an assignment, or open this page with <code class="text-xs">?dName=</code> in the URL.</p>
                    <?php else: ?>
                        <form method="post" action="index.php?page=driver_notification&amp;dName=<?= h(rawurlencode($dName)) ?>" class="space-y-4">
                            <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                            <input type="hidden" name="driver_notify_submit" value="1">
                            <input type="hidden" name="regId" value="<?= h($deviceId) ?>">

                            <div class="text-sm space-y-1">
                                <div><span class="font-semibold">Driver Name:</span> <?= h($driverNameDisp) ?></div>
                                <div><span class="font-semibold">Driver Username:</span> <?= h($usernameDisp) ?></div>
                            </div>

                            <label class="form-control w-full">
                                <span class="label-text text-xs font-semibold">Message</span>
                                <textarea name="message" class="textarea textarea-bordered w-full text-sm" rows="3"><?= h($defaultMessage) ?></textarea>
                            </label>

                            <?php if ($deviceId === ''): ?>
                                <p class="text-xs text-warning">No device id on file for this driver — push cannot be delivered.</p>
                            <?php endif; ?>

                            <div class="flex justify-end">
                                <button type="submit" class="btn btn-success btn-sm text-white">Send</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
