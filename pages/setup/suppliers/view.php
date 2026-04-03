<?php
declare(strict_types=1);
if (!isset($mysqli)) { require __DIR__ . '/../../../config.php'; }
require_once __DIR__ . '/../../../includes/setup_suppliers_service.php';

$supplierId = (int) ($_GET['id'] ?? 0);
$supplier = $supplierId > 0 ? setup_suppliers_find($mysqli, $supplierId) : null;
if (!$supplier) { setup_suppliers_flash_set('error', 'Supplier not found.'); header('Location: index.php?page=setup_suppliers'); exit; }
$flash = setup_suppliers_flash_get();
require __DIR__ . '/../../../includes/header.php';
require __DIR__ . '/../../../includes/nav.php';
?>
<div class="flex gap-6 w-full">
    <aside class="hidden lg:block w-72 shrink-0"><?php require __DIR__ . '/../sidebar.php'; ?></aside>
    <main class="flex-1 px-4">
        <div class="space-y-4">
            <?php $breadcrumbParentLabel = 'Supplier Setup'; $breadcrumbParentHref = 'index.php?page=setup_suppliers'; $breadcrumbCurrent = 'View Supplier'; require __DIR__ . '/../../../includes/breadcrumb.php'; ?>
            <div class="flex flex-wrap items-center gap-2">
                <a href="index.php?page=setup_suppliers" class="btn btn-sm btn-outline">Back to supplier list</a>
                <a href="index.php?page=setup_supplier_edit&id=<?= $supplierId ?>" class="btn btn-sm btn-success">Edit</a>
            </div>
            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body space-y-4">
                    <?php if (!empty($flash)): ?><div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-error' ?>"><span><?= h((string) $flash['message']) ?></span></div><?php endif; ?>
                    <div class="max-w-4xl mx-auto border border-base-300 rounded-box overflow-hidden">
                        <div class="px-4 py-2.5 bg-linear-to-r from-sky-700 to-cyan-600 text-white font-bold text-base">View Supplier</div>
                        <div class="divide-y divide-base-300">
                            <?php $rowClass='grid grid-cols-1 md:grid-cols-[190px_1fr] items-center gap-2 px-3 py-1.5'; $labelClass='font-semibold text-sm text-base-content/80'; $valueClass='text-sm text-base-content'; ?>
                            <div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">Supplier Name :</div><div class="<?= $valueClass ?>"><?= h((string) $supplier['supplier_name']) ?></div></div>
                            <div class="<?= $rowClass ?> md:items-start"><div class="<?= $labelClass ?> pt-1">Address :</div><div class="<?= $valueClass ?> whitespace-pre-wrap"><?= h((string) $supplier['supplier_address']) ?></div></div>
                            <div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">Country :</div><div class="<?= $valueClass ?>"><?= h((string) $supplier['supplier_country']) ?></div></div>
                            <div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">City :</div><div class="<?= $valueClass ?>"><?= h((string) $supplier['supplier_city']) ?></div></div>
                            <div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">Email :</div><div class="<?= $valueClass ?>"><?= h((string) $supplier['supplier_email']) ?></div></div>
                            <div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">Contact No :</div><div class="<?= $valueClass ?>"><?= h((string) $supplier['supplier_contact_no']) ?></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
<?php require __DIR__ . '/../../../includes/footer.php'; ?>
