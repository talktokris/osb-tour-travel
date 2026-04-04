<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/sms_module_service.php';

require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';

$csrf = sms_module_csrf_token();
$flash = sms_module_flash_get();
$resultRaw = '';
$resultError = '';
$destVal = '';
$msgVal = '';
$typeVal = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string) ($_POST['_token'] ?? '');
    if (!sms_module_csrf_validate($token)) {
        $resultError = 'Invalid request token.';
    } else {
        $destVal = (string) ($_POST['dest'] ?? '');
        $msgVal = (string) ($_POST['msg'] ?? '');
        $typeVal = (int) ($_POST['type'] ?? 1);
        $send = sms_module_send_test_sms($destVal, $msgVal, $typeVal);
        if (!empty($send['error'])) {
            $resultError = $send['error'];
        } else {
            $resultRaw = $send['raw'];
        }
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
            <?php $breadcrumbCurrent = 'SMS test'; require __DIR__ . '/../../includes/breadcrumb.php'; ?>

            <?php if ($flash): ?>
                <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-error' ?> shadow-sm">
                    <span><?= h((string) $flash['message']) ?></span>
                </div>
            <?php endif; ?>

            <div class="sms-legacy-panel rounded-sm p-4 sm:p-5 max-w-2xl">
                <h2 class="sms-legacy-title mb-4">Send Test SMS</h2>

                <?php if ($resultError !== ''): ?>
                    <p class="text-error font-medium text-sm mb-3"><?= h($resultError) ?></p>
                <?php endif; ?>
                <?php if ($resultRaw !== ''): ?>
                    <p class="text-sm text-base-content mb-3 whitespace-pre-wrap"><?= h($resultRaw) ?></p>
                <?php endif; ?>

                <form method="post" action="index.php?page=sms_test" class="space-y-3">
                    <input type="hidden" name="_token" value="<?= h($csrf) ?>">
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text">Destination Number :</span></label>
                        <div class="flex flex-wrap items-center gap-2">
                            <input type="text" name="dest" class="input input-bordered input-sm bg-white flex-1 min-w-[200px]"
                                   value="<?= h($destVal) ?>" autocomplete="off">
                            <span class="text-sm text-base-content/70">Ex. (0120000000)</span>
                        </div>
                    </div>
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text">Message :</span></label>
                        <textarea name="msg" rows="4" class="textarea textarea-bordered textarea-sm bg-white w-full"><?= h($msgVal) ?></textarea>
                    </div>
                    <div class="form-control">
                        <span class="label-text text-sm mb-2 block">Message Type :</span>
                        <label class="label cursor-pointer justify-start gap-2 py-1">
                            <input type="radio" name="type" value="1" class="radio radio-sm" <?= $typeVal === 1 ? 'checked' : '' ?>>
                            <span class="text-sm">ASCII (English)</span>
                        </label>
                        <label class="label cursor-pointer justify-start gap-2 py-1">
                            <input type="radio" name="type" value="2" class="radio radio-sm" <?= $typeVal === 2 ? 'checked' : '' ?>>
                            <span class="text-sm">Unicode (Arabic)</span>
                        </label>
                    </div>
                    <button type="submit" name="submit" value="1" class="btn btn-sm sms-legacy-btn px-8">Send</button>
                </form>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
