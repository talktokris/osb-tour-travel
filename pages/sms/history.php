<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/sms_module_service.php';

require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';

$csrf = sms_module_csrf_token();
$mobileFilter = '';
$rows = [];
$heading = '1000 SMS list of Latest';
$formError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string) ($_POST['_token'] ?? '');
    if (!sms_module_csrf_validate($token)) {
        $formError = 'Invalid request token.';
        $mobileFilter = trim((string) ($_POST['mobile'] ?? ''));
    } else {
        $mobileFilter = trim((string) ($_POST['mobile'] ?? ''));
        if ($mobileFilter !== '') {
            $heading = 'Search For Mobile No: ' . $mobileFilter;
        }
        $rows = sms_module_history_rows($mysqli, $mobileFilter !== '' ? $mobileFilter : null);
    }
} else {
    $rows = sms_module_history_rows($mysqli, null);
}

require __DIR__ . '/panel_styles.php';
?>

<div class="flex gap-6 w-full max-w-none px-3 sm:px-5 lg:px-6 pb-6">
    <aside class="hidden lg:block w-72 shrink-0">
        <?php require __DIR__ . '/sidebar.php'; ?>
    </aside>

    <main class="flex-1 min-w-0">
        <div class="space-y-4">
            <?php $breadcrumbCurrent = 'SMS list'; require __DIR__ . '/../../includes/breadcrumb.php'; ?>

            <?php if ($formError !== ''): ?>
                <div class="alert alert-error shadow-sm"><span><?= h($formError) ?></span></div>
            <?php endif; ?>

            <div class="sms-legacy-panel rounded-sm p-4 sm:p-5">
                <h2 class="sms-legacy-title mb-4">Send Search</h2>
                <form method="post" action="index.php?page=sms_history" class="flex flex-wrap items-end gap-3 max-w-xl">
                    <input type="hidden" name="_token" value="<?= h($csrf) ?>">
                    <div class="form-control flex-1 min-w-[200px]">
                        <label class="label py-1"><span class="label-text text-sm">Mobile No :</span></label>
                        <input type="text" name="mobile" class="input input-bordered input-sm bg-white w-full"
                               value="<?= h($mobileFilter) ?>" autocomplete="off">
                    </div>
                    <button type="submit" class="btn btn-sm sms-legacy-btn px-6 min-h-9">Search</button>
                </form>
            </div>

            <div class="space-y-2">
                <h3 class="text-base font-bold"><?= h($heading) ?></h3>
                <?php if ($rows === []): ?>
                    <p class="text-base-content/70 text-sm">No results.</p>
                <?php else: ?>
                    <div class="overflow-x-auto rounded-box border border-base-200 bg-base-100">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>SMS ID</th>
                                    <th>PHONE NO</th>
                                    <th>Message</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $r): ?>
                                    <tr>
                                        <td><?= h((string) ($r['sms_id'] ?? '')) ?></td>
                                        <td><?= h((string) ($r['sms_no'] ?? '')) ?></td>
                                        <td class="max-w-md whitespace-pre-wrap break-words text-sm"><?= h(normalize_arabic_text((string) ($r['sms_message'] ?? ''))) ?></td>
                                        <td><?= h((string) ($r['date'] ?? '')) ?></td>
                                        <td><?= h((string) ($r['status'] ?? '')) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
