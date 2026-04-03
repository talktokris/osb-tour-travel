<?php
declare(strict_types=1);
if (!isset($mysqli)) require __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../includes/setup_itinerary_labels_service.php';
$id = (int) ($_GET['id'] ?? 1);
$row = setup_itinerary_labels_get($mysqli, $id);
if (!$row) { setup_itinerary_labels_flash_set('error', 'Itinerary label record not found.'); header('Location: index.php?page=setup_itinerary_labels'); exit; }
$fieldMap = setup_itinerary_labels_field_map();
$form = [];
foreach ($fieldMap as $k => $_label) $form[$k] = (string) ($row[$k] ?? '');
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!setup_itinerary_labels_csrf_validate((string) ($_POST['_token'] ?? ''))) {
        $errors[] = 'Invalid request token.';
    } else {
        foreach (array_keys($fieldMap) as $k) $form[$k] = trim((string) ($_POST[$k] ?? ''));
        $res = setup_itinerary_labels_update($mysqli, $id, $form);
        if (!empty($res['ok'])) {
            setup_itinerary_labels_flash_set('success', 'Itinerary labels updated successfully.');
            header('Location: index.php?page=setup_itinerary_labels');
            exit;
        }
        $errors = $res['errors'] ?? ['Update failed.'];
    }
}
$csrf = setup_itinerary_labels_csrf_token();
require __DIR__ . '/../../../includes/header.php';
require __DIR__ . '/../../../includes/nav.php';
?>
<div class="flex gap-6 w-full">
    <aside class="hidden lg:block w-72 shrink-0"><?php require __DIR__ . '/../sidebar.php'; ?></aside>
    <main class="flex-1 px-4">
        <div class="space-y-4">
            <?php $breadcrumbParentLabel='Itinerary Label'; $breadcrumbParentHref='index.php?page=setup_itinerary_labels'; $breadcrumbCurrent='Edit Itinerary Label'; require __DIR__ . '/../../../includes/breadcrumb.php'; ?>
            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body space-y-4">
                    <?php if ($errors): ?><div class="alert alert-error"><span><?= h(implode(' ', $errors)) ?></span></div><?php endif; ?>
                    <form method="post" class="space-y-3">
                        <input type="hidden" name="_token" value="<?= h($csrf) ?>">
                        <div class="max-w-5xl mx-auto border border-base-300 rounded-box overflow-hidden">
                            <div class="px-4 py-2.5 bg-linear-to-r from-sky-700 to-cyan-600 text-white font-bold text-base">Edit Itinerary Label</div>
                            <div class="divide-y divide-base-300">
                                <?php foreach ($fieldMap as $k => $label): ?>
                                    <div class="grid grid-cols-1 md:grid-cols-[220px_1fr] items-center gap-2 px-3 py-1.5">
                                        <label class="font-semibold text-sm text-base-content/80"><?= h($label) ?> :</label>
                                        <?php if ($k === 'city_five_fills'): ?>
                                            <textarea name="<?= h($k) ?>" dir="rtl" lang="ar" class="textarea textarea-bordered textarea-sm text-sm w-full max-w-4xl input-arabic" rows="8"><?= h($form[$k]) ?></textarea>
                                        <?php else: ?>
                                            <input name="<?= h($k) ?>" value="<?= h($form[$k]) ?>" dir="rtl" lang="ar" class="input input-bordered input-sm text-sm w-full max-w-4xl input-arabic" type="text" autocomplete="off">
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="flex justify-center"><button class="btn btn-primary" type="submit">Update</button></div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>
<?php require __DIR__ . '/../../../includes/footer.php'; ?>

