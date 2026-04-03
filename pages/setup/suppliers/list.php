<?php

declare(strict_types=1);

if (!isset($mysqli)) {
    require __DIR__ . '/../../../config.php';
}
require_once __DIR__ . '/../../../includes/setup_suppliers_service.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_supplier') {
    $token = (string) ($_POST['_token'] ?? '');
    $supplierId = (int) ($_POST['supplier_id'] ?? 0);
    if (!setup_suppliers_csrf_validate($token)) {
        setup_suppliers_flash_set('error', 'Invalid request token.');
    } elseif ($supplierId <= 0) {
        setup_suppliers_flash_set('error', 'Invalid supplier.');
    } elseif (setup_suppliers_delete($mysqli, $supplierId)) {
        setup_suppliers_flash_set('success', 'Supplier deleted successfully.');
    } else {
        setup_suppliers_flash_set('error', 'Delete failed.');
    }
    header('Location: index.php?page=setup_suppliers');
    exit;
}

$fCountry = isset($_GET['f_country']) ? trim((string) $_GET['f_country']) : '';
$fCity = isset($_GET['f_city']) ? trim((string) $_GET['f_city']) : '';
$fSupplier = isset($_GET['f_supplier']) ? trim((string) $_GET['f_supplier']) : '';

$suppliers = setup_suppliers_list(
    $mysqli,
    $fCountry !== '' ? $fCountry : null,
    $fCity !== '' ? $fCity : null,
    $fSupplier !== '' ? $fSupplier : null
);
$countries = setup_suppliers_countries($mysqli);
$citiesAll = setup_suppliers_cities_all($mysqli);
$supplierNames = setup_suppliers_distinct_names($mysqli);
$flash = setup_suppliers_flash_get();
$csrf = setup_suppliers_csrf_token();

require __DIR__ . '/../../../includes/header.php';
require __DIR__ . '/../../../includes/nav.php';
?>

<div class="flex gap-6 w-full">
    <aside class="hidden lg:block w-72 shrink-0">
        <?php require __DIR__ . '/../sidebar.php'; ?>
    </aside>

    <main class="flex-1 px-4">
        <div class="space-y-4">
            <?php $breadcrumbParentLabel = 'Setup'; $breadcrumbParentHref = 'index.php?page=setup'; $breadcrumbCurrent = 'Supplier Setup'; require __DIR__ . '/../../../includes/breadcrumb.php'; ?>
            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body space-y-4">
                    <?php if (!empty($flash)): ?>
                        <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-error' ?>"><span><?= h((string) $flash['message']) ?></span></div>
                    <?php endif; ?>

                    <div class="max-w-5xl mx-auto border border-base-300 rounded-box overflow-hidden">
                        <div class="px-4 py-2.5 bg-linear-to-r from-sky-700 to-cyan-600 text-white font-bold text-base">Supplier Setup</div>
                        <form method="get" action="index.php" class="p-4 space-y-3 border-b border-base-300 bg-base-100">
                            <input type="hidden" name="page" value="setup_suppliers">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <label class="form-control w-full">
                                    <span class="label-text text-sm">Search country</span>
                                    <select name="f_country" id="supplier-filter-country" class="select select-bordered select-sm w-full">
                                        <option value="">All</option>
                                        <?php foreach ($countries as $c): ?>
                                            <option value="<?= h($c) ?>" <?= $fCountry === $c ? 'selected' : '' ?>><?= h($c) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label class="form-control w-full">
                                    <span class="label-text text-sm">Select city</span>
                                    <select name="f_city" id="supplier-filter-city" class="select select-bordered select-sm w-full">
                                        <option value="">All</option>
                                        <?php foreach ($citiesAll as $ct): ?>
                                            <option value="<?= h($ct['city_name']) ?>" data-country="<?= h($ct['city_country_name']) ?>" <?= $fCity === $ct['city_name'] ? 'selected' : '' ?>><?= h($ct['city_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label class="form-control w-full">
                                    <span class="label-text text-sm">Supplier name</span>
                                    <select name="f_supplier" class="select select-bordered select-sm w-full">
                                        <option value="">All</option>
                                        <?php foreach ($supplierNames as $n): ?>
                                            <option value="<?= h($n) ?>" <?= $fSupplier === $n ? 'selected' : '' ?>><?= h($n) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">Search</button>
                                <a href="index.php?page=setup_suppliers" class="btn btn-ghost btn-sm">Reset</a>
                            </div>
                        </form>
                    </div>

                    <div class="flex justify-center">
                        <a href="index.php?page=setup_supplier_create" class="btn btn-success btn-sm">Create Supplier</a>
                    </div>

                    <div class="overflow-x-auto rounded-box border border-base-300">
                        <table class="table table-zebra table-sm">
                            <thead>
                                <tr>
                                    <th>S.N</th>
                                    <th>Supplier Name</th>
                                    <th>Address</th>
                                    <th>Country</th>
                                    <th>City</th>
                                    <th>Email</th>
                                    <th>Contact</th>
                                    <th>View</th>
                                    <th>Edit</th>
                                    <th>Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $sn = 1; foreach ($suppliers as $row): ?>
                                    <tr>
                                        <td><?= $sn++ ?></td>
                                        <td class="font-medium"><?= h((string) $row['supplier_name']) ?></td>
                                        <td class="max-w-xs whitespace-normal"><?= h((string) $row['supplier_address']) ?></td>
                                        <td><?= h((string) $row['supplier_country']) ?></td>
                                        <td><?= h((string) $row['supplier_city']) ?></td>
                                        <td><?= h((string) $row['supplier_email']) ?></td>
                                        <td><?= h((string) $row['supplier_contact_no']) ?></td>
                                        <td><a class="btn btn-xs btn-info btn-outline" href="index.php?page=setup_supplier_view&id=<?= (int) $row['supplier_id'] ?>">View</a></td>
                                        <td><a class="btn btn-xs btn-outline" href="index.php?page=setup_supplier_edit&id=<?= (int) $row['supplier_id'] ?>">Edit</a></td>
                                        <td><button type="button" class="btn btn-xs btn-error btn-outline js-delete-supplier" data-supplier-id="<?= (int) $row['supplier_id'] ?>" data-supplier-name="<?= h((string) $row['supplier_name']) ?>">Delete</button></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<form id="delete-supplier-form" method="post" action="index.php?page=setup_suppliers" class="hidden" aria-hidden="true">
    <input type="hidden" name="_token" value="<?= h($csrf) ?>">
    <input type="hidden" name="action" value="delete_supplier">
    <input type="hidden" name="supplier_id" id="delete-supplier-form-id" value="">
</form>

<dialog id="delete-supplier-modal" class="agent-delete-dialog">
    <div class="agent-delete-dialog__surface">
        <h3 class="font-bold text-lg mb-1">Delete supplier?</h3>
        <p class="agent-delete-dialog__message" id="delete-supplier-modal-message">Are you sure? This cannot be undone.</p>
        <div class="agent-delete-dialog__actions">
            <button type="button" class="btn btn-outline" id="delete-supplier-modal-no">No</button>
            <button type="button" class="btn btn-error" id="delete-supplier-modal-yes">Yes</button>
        </div>
    </div>
</dialog>

<script>
(function () {
    var countrySel = document.getElementById('supplier-filter-country');
    var citySel = document.getElementById('supplier-filter-city');
    if (countrySel && citySel) {
        function filterCities() {
            var c = countrySel.value || '';
            Array.prototype.forEach.call(citySel.options, function (opt, i) {
                if (i === 0) return;
                opt.hidden = c !== '' && (opt.getAttribute('data-country') || '') !== c;
            });
            var sel = citySel.options[citySel.selectedIndex];
            if (sel && sel.hidden) citySel.value = '';
        }
        countrySel.addEventListener('change', filterCities);
        filterCities();
    }

    var modal = document.getElementById('delete-supplier-modal');
    var form = document.getElementById('delete-supplier-form');
    var idInput = document.getElementById('delete-supplier-form-id');
    var msg = document.getElementById('delete-supplier-modal-message');
    var btnNo = document.getElementById('delete-supplier-modal-no');
    var btnYes = document.getElementById('delete-supplier-modal-yes');
    if (!modal || !form || !idInput || !msg || !btnNo || !btnYes) return;

    document.querySelectorAll('.js-delete-supplier').forEach(function (btn) {
        btn.addEventListener('click', function () {
            idInput.value = this.getAttribute('data-supplier-id') || '';
            var name = this.getAttribute('data-supplier-name') || '';
            msg.textContent = name ? 'Are you sure you want to delete supplier "' + name + '"? This cannot be undone.' : 'Are you sure you want to delete this supplier? This cannot be undone.';
            modal.showModal();
        });
    });
    btnNo.addEventListener('click', function () { modal.close(); });
    btnYes.addEventListener('click', function () { modal.close(); form.submit(); });
})();
</script>

<?php require __DIR__ . '/../../../includes/footer.php'; ?>
