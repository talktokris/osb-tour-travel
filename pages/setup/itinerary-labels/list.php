<?php
declare(strict_types=1);
if (!isset($mysqli)) require __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../includes/setup_itinerary_labels_service.php';
$row = setup_itinerary_labels_get($mysqli, 1);
if (!$row) { setup_itinerary_labels_flash_set('error', 'Itinerary label record not found.'); header('Location: index.php?page=setup'); exit; }
$flash = setup_itinerary_labels_flash_get();
$fieldMap = setup_itinerary_labels_field_map();
require __DIR__ . '/../../../includes/header.php';
require __DIR__ . '/../../../includes/nav.php';
?>
<div class="flex gap-6 w-full">
    <aside class="hidden lg:block w-72 shrink-0"><?php require __DIR__ . '/../sidebar.php'; ?></aside>
    <main class="flex-1 px-4">
        <div class="space-y-4">
            <?php $breadcrumbParentLabel='Setup'; $breadcrumbParentHref='index.php?page=setup'; $breadcrumbCurrent='Itinerary Label'; require __DIR__ . '/../../../includes/breadcrumb.php'; ?>
            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body space-y-4">
                    <?php if(!empty($flash)): ?><div class="alert <?= $flash['type']==='success' ? 'alert-success' : 'alert-error' ?>"><span><?= h((string)$flash['message']) ?></span></div><?php endif; ?>
                    <div class="overflow-x-auto rounded-box border border-base-300">
                        <table class="table table-zebra table-sm">
                            <thead><tr><th>English Label</th><th>Arabic Label</th><th>Edit</th></tr></thead>
                            <tbody>
                                <?php $first = true; foreach ($fieldMap as $col => $label): ?>
                                    <tr>
                                        <td><?= h($label) ?></td>
                                        <td class="max-w-3xl whitespace-normal text-right" dir="rtl" lang="ar"><?= h((string)($row[$col] ?? '')) ?></td>
                                        <td><?= $first ? '<a class="btn btn-xs btn-outline" href="index.php?page=setup_itinerary_label_edit&id=1">Edit</a>' : '' ?></td>
                                    </tr>
                                <?php $first = false; endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
<?php require __DIR__ . '/../../../includes/footer.php'; ?>

