<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/sms_module_service.php';

require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';

$balanceText = '';
$cfg = sms_module_isms_config();
if ($cfg === null) {
    $balanceText = 'Configure ISMS_USERNAME and ISMS_PASSWORD in .env to check balance.';
} else {
    $balanceText = sms_module_fetch_balance();
    if ($balanceText === '') {
        $balanceText = '(Empty response from balance API.)';
    }
}

require __DIR__ . '/panel_styles.php';
?>

<div class="flex gap-6 w-full max-w-none pb-6">
    <aside class="hidden lg:block w-72 shrink-0">
        <?php require __DIR__ . '/sidebar.php'; ?>
    </aside>

    <main class="flex-1 min-w-0">
        <div class="space-y-4">
            <?php $breadcrumbCurrent = 'SMS credit'; require __DIR__ . '/../../includes/breadcrumb.php'; ?>

            <div class="sms-legacy-panel rounded-sm p-4 sm:p-5 max-w-2xl">
                <h2 class="sms-legacy-title mb-4">SMS Credits</h2>
                <div class="rounded-box border border-base-300 bg-warning/10 p-4">
                    <p class="text-sm">
                        <span class="font-medium">Balance Credits :</span>
                        <span class="ml-2 font-mono whitespace-pre-wrap"><?= h(normalize_arabic_text($balanceText)) ?></span>
                    </p>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
