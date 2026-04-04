<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/sms_module_service.php';

require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';

$csrf = sms_module_csrf_token();
$flash = sms_module_flash_get();

$fromDateInput = '';
$searchYmd = null;
$previews = [];
$searchError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string) ($_POST['_token'] ?? '');
    $action = (string) ($_POST['sms_action'] ?? '');

    if ($action === 'enqueue') {
        if (!sms_module_csrf_validate($token)) {
            $searchError = 'Invalid request token.';
        } else {
            $ymd = trim((string) ($_POST['service_ymd'] ?? ''));
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $ymd)) {
                $searchError = 'Invalid service date for send.';
            } else {
                $result = sms_module_enqueue_service_date($mysqli, $ymd);
                $parts = ['Queued ' . $result['queued'] . ' message(s).'];
                if ($result['skipped'] > 0) {
                    $parts[] = 'Skipped ' . $result['skipped'] . ' row(s) (missing or short mobile).';
                }
                if ($result['errors'] !== []) {
                    $parts[] = implode(' ', array_slice($result['errors'], 0, 3));
                }
                sms_module_flash_set('success', implode(' ', $parts));
                header('Location: index.php?page=sms');
                exit;
            }
        }
    } elseif ($action === 'search') {
        $fromDateInput = trim((string) ($_POST['from_date'] ?? ''));
        if (!sms_module_csrf_validate($token)) {
            $searchError = 'Invalid request token.';
        } else {
            $searchYmd = sms_module_parse_service_date_input($fromDateInput);
            if ($searchYmd === null) {
                $searchError = 'Enter a valid date (dd-mm-yyyy or use the picker).';
            } else {
                $templates = sms_module_load_templates($mysqli);
                foreach (sms_module_file_entries_for_service_date($mysqli, $searchYmd) as $row) {
                    $previews[] = sms_module_build_row_messages($mysqli, $templates, $row);
                }
            }
        }
    }
}

require __DIR__ . '/panel_styles.php';
?>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script>
$(function () {
    $('#sms-from-date').datepicker({ dateFormat: 'dd-mm-yy', changeMonth: true, changeYear: true });
});
</script>

<div class="flex gap-6 w-full max-w-none px-3 sm:px-5 lg:px-6 pb-6">
    <aside class="hidden lg:block w-72 shrink-0">
        <?php require __DIR__ . '/sidebar.php'; ?>
    </aside>

    <main class="flex-1 min-w-0">
        <div class="space-y-4">
            <?php $breadcrumbCurrent = 'Send SMS list'; require __DIR__ . '/../../includes/breadcrumb.php'; ?>

            <?php if ($flash): ?>
                <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-error' ?> shadow-sm">
                    <span><?= h((string) $flash['message']) ?></span>
                </div>
            <?php endif; ?>
            <?php if ($searchError !== ''): ?>
                <div class="alert alert-error shadow-sm"><span><?= h($searchError) ?></span></div>
            <?php endif; ?>

            <div class="sms-legacy-panel rounded-sm p-4 sm:p-5">
                <h2 class="sms-legacy-title mb-4">Send SMS List</h2>
                <form method="post" action="index.php?page=sms" class="space-y-4 max-w-lg">
                    <input type="hidden" name="_token" value="<?= h($csrf) ?>">
                    <input type="hidden" name="sms_action" value="search">
                    <div class="flex flex-wrap items-end gap-3">
                        <div class="form-control">
                            <label class="label py-1"><span class="label-text text-sm">Date :</span></label>
                            <input type="text" name="from_date" id="sms-from-date" autocomplete="off"
                                   class="input input-bordered input-sm bg-white w-52"
                                   value="<?= h($fromDateInput) ?>" placeholder="dd-mm-yyyy">
                        </div>
                        <button type="submit" class="btn btn-sm sms-legacy-btn px-6 min-h-9">Search</button>
                    </div>
                </form>
            </div>

            <?php
            $showSearchResults = $_SERVER['REQUEST_METHOD'] === 'POST'
                && (string) ($_POST['sms_action'] ?? '') === 'search'
                && $searchYmd !== null
                && $searchError === '';
            ?>
            <?php if ($showSearchResults): ?>
                <div class="space-y-3">
                    <h3 class="text-lg font-semibold text-base-content">SMS List for Service Date: <?= h($searchYmd) ?></h3>
                    <?php if ($previews === []): ?>
                        <p class="text-error font-medium">No Result Found</p>
                    <?php else: ?>
                        <form method="post" action="index.php?page=sms" class="space-y-4">
                            <input type="hidden" name="_token" value="<?= h($csrf) ?>">
                            <input type="hidden" name="sms_action" value="enqueue">
                            <input type="hidden" name="service_ymd" value="<?= h($searchYmd) ?>">
                            <div class="flex justify-end">
                                <button type="submit" class="btn btn-sm sms-legacy-btn px-6">Send list</button>
                            </div>
                        </form>
                        <div class="space-y-4 border border-base-300 rounded-box bg-base-100 p-4">
                            <?php foreach ($previews as $p): ?>
                                <div class="border-b border-base-200 pb-3 last:border-0">
                                    <p class="text-sm font-medium">Name: <?= h($p['name']) ?> — Mobile No: <?= h($p['mobile']) ?></p>
                                    <p class="text-sm text-base-content/90 whitespace-pre-wrap mt-1">Message: <?= h(normalize_arabic_text($p['message_preview'])) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
