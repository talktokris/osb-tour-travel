<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/file_module_service.php';

$currentPage = 'file_preview';

if (!file_module_has_agent()) {
    file_module_render_agent_required();
}

$fcn = trim((string) ($_GET['file_count_no'] ?? ''));
$uname = (string) ($_SESSION['user_name'] ?? '');

if ($fcn === '' || !file_module_user_can_access_file_count($mysqli, $fcn, $uname)) {
    require __DIR__ . '/../../includes/header.php';
    require __DIR__ . '/../../includes/nav.php';
    echo '<div class="p-4"><div class="alert alert-warning">Invalid or inaccessible file.</div><a class="btn btn-sm" href="index.php?page=file">Home</a></div>';
    require __DIR__ . '/../../includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_confirm'])) {
    $token = (string) ($_POST['_token'] ?? '');
    if (!file_module_csrf_validate($token)) {
        file_module_flash_set('error', 'Invalid token.');
    } elseif (trim((string) ($_POST['file_count_no'] ?? '')) !== $fcn) {
        file_module_flash_set('error', 'Mismatch.');
    } else {
        file_module_set_all_on_request($mysqli, $fcn);
        header('Location: index.php?page=file_send_email&file_count_no=' . rawurlencode($fcn));
        exit;
    }
}

require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';

$csrf = file_module_csrf_token();
$flash = file_module_flash_get();
$rows = file_module_entries_for_count($mysqli, $fcn);
$total = 0.0;
foreach ($rows as $r) {
    $total += (float) ($r['selling_price'] ?? 0);
}
$head = $rows[0] ?? [];
$fileNo = (string) ($head['file_no'] ?? '');
?>

<div class="flex gap-6 w-full pb-6">
    <aside class="hidden lg:block w-72 shrink-0"><?php require __DIR__ . '/sidebar.php'; ?></aside>
    <main class="flex-1 min-w-0 space-y-4">
        <?php $breadcrumbCurrent = 'File preview'; require __DIR__ . '/../../includes/breadcrumb.php'; ?>
        <?php if ($flash): ?>
            <?php $ft = $flash['type'] ?? ''; ?>
            <div class="alert <?= $ft === 'success' ? 'alert-success' : ($ft === 'error' ? 'alert-error' : 'alert-info') ?>"><span><?= h($flash['message']) ?></span></div>
        <?php endif; ?>

        <div class="rounded-sm border border-base-300 bg-[#ffffee] p-4 max-w-4xl">
            <h2 class="text-success font-semibold">My booking : <span class="text-error"><?= h($fileNo !== '' ? $fileNo : $fcn) ?></span></h2>
            <p class="text-sm">File group #<?= h($fcn) ?> — <?= count($rows) ?> service(s)</p>
        </div>

        <?php foreach ($rows as $r): ?>
            <div class="border border-base-300 rounded-sm p-3 bg-base-100 text-sm space-y-1 max-w-4xl">
                <div class="font-semibold text-success"><?= h((string) ($r['service'] ?? '')) ?></div>
                <div>Status: <?= h((string) ($r['book_status'] ?? '')) ?> · Supplier: <?= h((string) ($r['supplier_name'] ?? '')) ?></div>
                <div>Date: <?= h((string) ($r['service_date'] ?? '')) ?> · Pax: <?= h((string) ($r['no_of_pax'] ?? '')) ?></div>
                <div>Price: <?= h((string) ($r['selling_price'] ?? '')) ?> · Guest: <?= h(trim((string) ($r['first_name'] ?? '') . ' ' . (string) ($r['last_name'] ?? ''))) ?></div>
            </div>
        <?php endforeach; ?>

        <div class="text-right font-bold text-error max-w-4xl">Grand total: <?= h(number_format($total, 2)) ?> MYR</div>

        <div class="flex flex-wrap gap-2 items-center">
            <a href="index.php?page=file" class="link link-success text-sm">Add more transfer service</a>
            <a href="index.php?page=file&amp;new=1" class="link text-sm">Start new file</a>
        </div>

        <form method="post" action="index.php?page=file_preview&amp;file_count_no=<?= h(rawurlencode($fcn)) ?>" class="flex flex-wrap gap-2">
            <input type="hidden" name="_token" value="<?= h($csrf) ?>">
            <input type="hidden" name="file_confirm" value="1">
            <input type="hidden" name="file_count_no" value="<?= h($fcn) ?>">
            <button type="submit" class="btn btn-success btn-sm text-white">Confirm</button>
            <a class="btn btn-outline btn-sm" href="index.php?page=file_send_email&amp;file_count_no=<?= h(rawurlencode($fcn)) ?>">Resend email</a>
        </form>
    </main>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
