<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/file_module_service.php';
require_once __DIR__ . '/../../includes/driver_module_service.php';

if (!file_module_has_agent()) {
    file_module_render_agent_required();
}

$currentPage = 'driver';
$userEnterBy = trim((string) ($_SESSION['user_name'] ?? ''));
$driverSub = driver_module_normalize_sub((string) ($_GET['sub'] ?? 'search'));
$csrf = file_module_csrf_token();
$flash = file_module_flash_get();

$postTrim = static function (): array {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return [];
    }
    $o = [];
    foreach ($_POST as $k => $v) {
        $o[$k] = is_string($v) ? trim($v) : $v;
    }
    return $o;
};
$fv = $postTrim();

$searchRows = null;
$pendingRows = null;

if (($_SERVER['REQUEST_METHOD'] === 'POST') && (($fv['driver_search_submit'] ?? '') === '1') && $driverSub === 'search') {
    if (!file_module_csrf_validate((string) ($fv['csrf'] ?? ''))) {
        file_module_flash_set('error', 'Invalid security token. Please try again.');
        header('Location: index.php?page=driver&sub=search');
        exit;
    }
    $filters = [
        'search_driver' => (string) ($fv['search_driver'] ?? ''),
        'search_ref' => (string) ($fv['search_ref'] ?? ''),
        'search_file' => (string) ($fv['search_file'] ?? ''),
        'search_agent' => (string) ($fv['search_agent'] ?? ''),
        'search_supplier' => (string) ($fv['search_supplier'] ?? ''),
        'search_pax' => (string) ($fv['search_pax'] ?? ''),
        'status' => (string) ($fv['status'] ?? ''),
        'select_date' => (string) ($fv['select_date'] ?? ''),
    ];
    $searchRows = driver_module_search_rows($mysqli, $filters, $userEnterBy);
}

if (($_SERVER['REQUEST_METHOD'] === 'POST') && (($fv['driver_pending_submit'] ?? '') === '1') && $driverSub === 'pending') {
    if (!file_module_csrf_validate((string) ($fv['csrf'] ?? ''))) {
        file_module_flash_set('error', 'Invalid security token. Please try again.');
        header('Location: index.php?page=driver&sub=pending');
        exit;
    }
    $pendingRows = driver_module_pending_rows($mysqli, [
        'search_supplier' => (string) ($fv['search_supplier'] ?? ''),
        'select_date' => (string) ($fv['select_date'] ?? ''),
    ], $userEnterBy);
}

$completedRows = $driverSub === 'completed' ? driver_module_completed_rows($mysqli) : null;
$recentRows = $driverSub === 'recent' ? driver_module_recent_rows($mysqli, $userEnterBy) : null;

if ($driverSub === 'pending' && $pendingRows === null) {
    $pendingRows = driver_module_pending_rows($mysqli, [], $userEnterBy);
}

$statuses = driver_module_completed_statuses($mysqli);

$subTitles = [
    'search' => 'Search Job',
    'pending' => 'Pending Job',
    'completed' => 'Completed Job',
    'recent' => 'Recent Assigned Job',
];
$pageTitle = $subTitles[$driverSub] ?? 'Driver';

require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
.driver-module-scope .search-ac-wrap { position: relative; width: 100%; display: block; }
.driver-module-scope .search-ac-dd {
    display: none; position: absolute; left: 0; right: 0; top: 100%; z-index: 60; margin-top: 3px;
    max-height: 240px; overflow-y: auto; background: #fff; border: 1px solid #cbd5e1; border-radius: 0.375rem;
    box-shadow: 0 12px 32px rgba(15, 23, 42, 0.12); list-style: none; margin: 0; padding: 0.25rem 0;
}
.driver-module-scope .search-ac-dd.is-open { display: block; }
.driver-module-scope .search-ac-dd li {
    padding: 0.45rem 0.75rem; font-size: 0.8125rem; cursor: pointer; border-bottom: 1px solid #f1f5f9;
}
.driver-module-scope .search-ac-dd li:hover, .driver-module-scope .search-ac-dd li.is-active { background: #ecfdf5; color: #14532d; }
.driver-module-scope .driver-form-yellow {
    background: #ffffe8;
    box-sizing: border-box;
    padding: 1.35rem 1.5rem 1.45rem;
}
@media (min-width: 640px) {
    .driver-module-scope .driver-form-yellow {
        padding: 1.55rem 1.85rem 1.6rem;
    }
}
.driver-module-scope .driver-form-fieldstack {
    display: flex;
    flex-direction: column;
    gap: 1.15rem;
}
.driver-module-scope .driver-form-fieldstack .form-control {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}
.driver-module-scope .driver-form-actions {
    margin-top: 0.35rem;
    padding-top: 0.85rem;
}
</style>

<div class="flex gap-6 w-full driver-module-scope">
    <aside class="hidden lg:block w-72 shrink-0">
        <?php require __DIR__ . '/sidebar.php'; ?>
    </aside>

    <main class="flex-1 min-w-0 px-4">
        <div class="space-y-4 w-full min-w-0">
            <?php $breadcrumbCurrent = 'Driver — ' . $pageTitle;
            require __DIR__ . '/../../includes/breadcrumb.php'; ?>

            <?php if ($flash !== null): ?>
                <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-warning' ?> text-sm">
                    <?= h((string) $flash['message']) ?>
                </div>
            <?php endif; ?>

            <?php if ($driverSub === 'search'): ?>
                <div class="card bg-base-100 shadow-xl border border-base-300 max-w-3xl w-full">
                    <div class="card-body">
                        <h3 class="card-title text-lg" style="color:#009900">Search by</h3>
                        <div class="rounded-box border border-warning/40 driver-form-yellow">
                            <form method="post" action="index.php?page=driver&amp;sub=search">
                                <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                                <input type="hidden" name="driver_search_submit" value="1">
                                <div class="driver-form-fieldstack">
                                <label class="form-control w-full">
                                    <span class="label-text text-xs font-semibold">Search by Driver Name</span>
                                    <input type="text" name="search_driver" value="<?= h((string) ($fv['search_driver'] ?? '')) ?>"
                                           class="input input-bordered input-sm w-full js-ac" data-ac-field="d_driver" autocomplete="off">
                                </label>
                                <label class="form-control w-full">
                                    <span class="label-text text-xs font-semibold">Search by Ref No.</span>
                                    <input type="text" name="search_ref" value="<?= h((string) ($fv['search_ref'] ?? '')) ?>"
                                           class="input input-bordered input-sm w-full js-ac" data-ac-field="d_ref" autocomplete="off">
                                </label>
                                <label class="form-control w-full">
                                    <span class="label-text text-xs font-semibold">Search by File No.</span>
                                    <input type="text" name="search_file" value="<?= h((string) ($fv['search_file'] ?? '')) ?>"
                                           class="input input-bordered input-sm w-full js-ac" data-ac-field="d_file" autocomplete="off">
                                </label>
                                <label class="form-control w-full">
                                    <span class="label-text text-xs font-semibold">Search by Agent Name</span>
                                    <input type="text" name="search_agent" value="<?= h((string) ($fv['search_agent'] ?? '')) ?>"
                                           class="input input-bordered input-sm w-full js-ac" data-ac-field="d_agent" autocomplete="off">
                                </label>
                                <label class="form-control w-full">
                                    <span class="label-text text-xs font-semibold">Search by Supplier</span>
                                    <input type="text" name="search_supplier" value="<?= h((string) ($fv['search_supplier'] ?? '')) ?>"
                                           class="input input-bordered input-sm w-full js-ac" data-ac-field="d_supplier" autocomplete="off">
                                </label>
                                <label class="form-control w-full">
                                    <span class="label-text text-xs font-semibold">Search by First Name</span>
                                    <input type="text" name="search_pax" value="<?= h((string) ($fv['search_pax'] ?? '')) ?>"
                                           class="input input-bordered input-sm w-full js-ac" data-ac-field="d_pax" autocomplete="off">
                                </label>
                                <label class="form-control w-full">
                                    <span class="label-text text-xs font-semibold">Search by Pax Status</span>
                                    <?php $curSt = (string) ($fv['status'] ?? ''); ?>
                                    <select name="status" class="select select-bordered select-sm w-full">
                                        <option value=""<?= $curSt === '' ? ' selected' : '' ?>>Select Status</option>
                                        <?php foreach ($statuses as $st): ?>
                                            <option value="<?= h($st) ?>"<?= $curSt === $st ? ' selected' : '' ?>><?= h($st) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label class="form-control w-full">
                                    <span class="label-text text-xs font-semibold">Search by Service Date (dd-mm-yyyy)</span>
                                    <div class="flex gap-2 items-center">
                                        <input type="text" name="select_date" id="driver_search_date" value="<?= h((string) ($fv['select_date'] ?? '')) ?>"
                                               class="input input-bordered input-sm flex-1 js-driver-date-input" placeholder="dd-mm-yyyy" autocomplete="off">
                                        <button type="button" class="btn btn-ghost btn-sm border border-base-300 js-driver-date-cal" data-target="driver_search_date" title="Calendar">📅</button>
                                    </div>
                                </label>
                                </div>
                                <div class="driver-form-actions flex justify-end">
                                    <button type="submit" class="btn btn-success btn-sm text-white">Search</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <?php if ($searchRows !== null): ?>
                    <?php if ($searchRows === []): ?>
                        <p class="text-sm text-base-content/70">No Result found</p>
                    <?php else: ?>
                        <div class="overflow-x-auto border border-base-300 rounded-lg bg-base-100">
                            <table class="table table-sm table-zebra w-full text-xs md:text-sm whitespace-nowrap min-w-[900px]">
                                <thead>
                                <tr class="bg-base-200">
                                    <th>S.N</th>
                                    <th>Ref No</th>
                                    <th>File No</th>
                                    <th>Agent Name</th>
                                    <th>Supplier Name</th>
                                    <th>Guest Name</th>
                                    <th>Service Date</th>
                                    <th>Vehicle No.</th>
                                    <th>Driver Name</th>
                                    <th>Status</th>
                                    <th>Assign</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($searchRows as $r): ?>
                                    <tr>
                                        <td><?= (int) ($r['file_id'] ?? 0) ?></td>
                                        <td><?= h((string) ($r['ref_no'] ?? '')) ?></td>
                                        <td><?= h((string) ($r['file_no'] ?? '')) ?></td>
                                        <td><?= h((string) ($r['agent_name'] ?? '')) ?></td>
                                        <td><?= h((string) ($r['supplier_name'] ?? '')) ?></td>
                                        <td><?= h(trim((string) ($r['last_name'] ?? '') . ' ' . (string) ($r['first_name'] ?? ''))) ?></td>
                                        <td><?= h(file_module_format_date_ymd_to_dmy((string) ($r['service_date'] ?? ''))) ?></td>
                                        <td><?= h((string) ($r['vehicle_no'] ?? '')) ?></td>
                                        <td><?= h((string) ($r['driver_name'] ?? '')) ?></td>
                                        <td><?= h((string) ($r['job_complited'] ?? '')) ?></td>
                                        <td>
                                            <a class="btn btn-xs btn-primary" href="index.php?page=driver_assign&amp;file_id=<?= (int) ($r['file_id'] ?? 0) ?>">Open</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

            <?php elseif ($driverSub === 'pending'): ?>
                <div class="card bg-base-100 shadow-xl border border-base-300 max-w-xl w-full">
                    <div class="card-body">
                        <h3 class="card-title text-lg" style="color:#009900">Search by</h3>
                        <div class="rounded-box border border-warning/40 driver-form-yellow">
                            <form method="post" action="index.php?page=driver&amp;sub=pending">
                                <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                                <input type="hidden" name="driver_pending_submit" value="1">
                                <div class="driver-form-fieldstack">
                                <label class="form-control w-full">
                                    <span class="label-text text-xs font-semibold">Search by Supplier</span>
                                    <input type="text" name="search_supplier" value="<?= h((string) ($fv['search_supplier'] ?? '')) ?>"
                                           class="input input-bordered input-sm w-full js-ac" data-ac-field="d_supplier" autocomplete="off">
                                </label>
                                <label class="form-control w-full">
                                    <span class="label-text text-xs font-semibold">Search by Service Date</span>
                                    <div class="flex gap-2 items-center">
                                        <input type="text" name="select_date" id="driver_pending_date" value="<?= h((string) ($fv['select_date'] ?? '')) ?>"
                                               class="input input-bordered input-sm flex-1 js-driver-date-input" placeholder="dd-mm-yyyy" autocomplete="off">
                                        <button type="button" class="btn btn-ghost btn-sm border border-base-300 js-driver-date-cal" data-target="driver_pending_date">📅</button>
                                    </div>
                                </label>
                                </div>
                                <div class="driver-form-actions flex justify-center">
                                    <button type="submit" class="btn btn-success btn-sm text-white">Search</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <?php if ($pendingRows !== null): ?>
                    <?php if ($pendingRows === []): ?>
                        <p class="text-sm text-base-content/70">No Result found</p>
                    <?php else: ?>
                        <div class="overflow-x-auto border border-base-300 rounded-lg bg-base-100">
                            <table class="table table-sm table-zebra w-full text-xs md:text-sm whitespace-nowrap min-w-[960px]">
                                <thead>
                                <tr class="bg-base-200">
                                    <th>S.N</th>
                                    <th>Ref No</th>
                                    <th>File No</th>
                                    <th>Service</th>
                                    <th>Agent Name</th>
                                    <th>Supplier Name</th>
                                    <th>Guest Name</th>
                                    <th>Service Date</th>
                                    <th>Vehicle No.</th>
                                    <th>Driver Name</th>
                                    <th>Assign</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $n = 1;
                                foreach ($pendingRows as $r): ?>
                                    <tr>
                                        <td><?= $n++ ?></td>
                                        <td><?= h((string) ($r['ref_no'] ?? '')) ?></td>
                                        <td><?= h((string) ($r['file_no'] ?? '')) ?></td>
                                        <td class="max-w-48 whitespace-normal"><?= h((string) ($r['service'] ?? '')) ?></td>
                                        <td><?= h((string) ($r['agent_name'] ?? '')) ?></td>
                                        <td><?= h((string) ($r['supplier_name'] ?? '')) ?></td>
                                        <td><?= h(trim((string) ($r['last_name'] ?? '') . ' ' . (string) ($r['first_name'] ?? ''))) ?></td>
                                        <td><?= h(file_module_format_date_ymd_to_dmy((string) ($r['service_date'] ?? ''))) ?></td>
                                        <td><?= h((string) ($r['vehicle_no'] ?? '')) ?></td>
                                        <td><?= h((string) ($r['driver_name'] ?? '')) ?></td>
                                        <td>
                                            <a class="btn btn-xs btn-primary" href="index.php?page=driver_assign&amp;file_id=<?= (int) ($r['file_id'] ?? 0) ?>">Open</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

            <?php elseif ($driverSub === 'completed'): ?>
                <?php if ($completedRows === []): ?>
                    <p class="text-sm text-base-content/70">No Result found</p>
                <?php else: ?>
                    <div class="overflow-x-auto border border-base-300 rounded-lg bg-base-100">
                        <table class="table table-sm table-zebra w-full text-xs md:text-sm whitespace-nowrap min-w-[800px]">
                            <thead>
                            <tr class="bg-base-200">
                                <th>S.N</th>
                                <th>Ref No</th>
                                <th>File No</th>
                                <th>Guest Name</th>
                                <th>Location</th>
                                <th>Service Date</th>
                                <th>Driver Name</th>
                                <th>Vehicle No.</th>
                                <th>Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $n = 1;
                            foreach ($completedRows as $r): ?>
                                <tr>
                                    <td><?= $n++ ?></td>
                                    <td><?= h((string) ($r['ref_no'] ?? '')) ?></td>
                                    <td><?= h((string) ($r['file_no'] ?? '')) ?></td>
                                    <td><?= h(trim((string) ($r['last_name'] ?? '') . ' ' . (string) ($r['first_name'] ?? ''))) ?></td>
                                    <td><?= h((string) ($r['from_city'] ?? '') . '-' . (string) ($r['to_city'] ?? '')) ?></td>
                                    <td><?= h(file_module_format_date_ymd_to_dmy((string) ($r['service_date'] ?? ''))) ?></td>
                                    <td><?= h((string) ($r['driver_name'] ?? '')) ?></td>
                                    <td><?= h((string) ($r['vehicle_no'] ?? '')) ?></td>
                                    <td><?= h((string) ($r['job_complited'] ?? '')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

            <?php elseif ($driverSub === 'recent'): ?>
                <?php if ($recentRows === []): ?>
                    <p class="text-sm text-base-content/70">No Result found</p>
                <?php else: ?>
                    <div class="overflow-x-auto border border-base-300 rounded-lg bg-base-100">
                        <table class="table table-sm table-zebra w-full text-xs md:text-sm whitespace-nowrap min-w-[960px]">
                            <thead>
                            <tr class="bg-base-200">
                                <th>S.N</th>
                                <th>Ref No</th>
                                <th>File No</th>
                                <th>Service</th>
                                <th>Agent Name</th>
                                <th>Supplier Name</th>
                                <th>Guest Name</th>
                                <th>Service Date</th>
                                <th>Vehicle No.</th>
                                <th>Driver Name</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $n = 1;
                            foreach ($recentRows as $r): ?>
                                <tr>
                                    <td><?= $n++ ?></td>
                                    <td><?= h((string) ($r['ref_no'] ?? '')) ?></td>
                                    <td><?= h((string) ($r['file_no'] ?? '')) ?></td>
                                    <td class="max-w-48 whitespace-normal"><?= h((string) ($r['service'] ?? '')) ?></td>
                                    <td><?= h((string) ($r['agent_name'] ?? '')) ?></td>
                                    <td><?= h((string) ($r['supplier_name'] ?? '')) ?></td>
                                    <td><?= h(trim((string) ($r['last_name'] ?? '') . ' ' . (string) ($r['first_name'] ?? ''))) ?></td>
                                    <td><?= h(file_module_format_date_ymd_to_dmy((string) ($r['service_date'] ?? ''))) ?></td>
                                    <td><?= h((string) ($r['vehicle_no'] ?? '')) ?></td>
                                    <td><?= h((string) ($r['driver_name'] ?? '')) ?></td>
                                    <td>
                                        <a class="btn btn-xs btn-primary" href="index.php?page=driver_assign&amp;file_id=<?= (int) ($r['file_id'] ?? 0) ?>">Open</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
(function () {
    var acUrl = 'index.php?page=driver_autocomplete&field=';
    var timer = null;
    function attachAc(input) {
        var field = input.getAttribute('data-ac-field');
        if (!field || input.closest('.search-ac-wrap')) return;
        var wrap = document.createElement('div');
        wrap.className = 'search-ac-wrap';
        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);
        var dd = document.createElement('ul');
        dd.className = 'search-ac-dd';
        dd.setAttribute('role', 'listbox');
        wrap.appendChild(dd);
        var items = [];
        function hide() { dd.classList.remove('is-open'); }
        function render() {
            dd.innerHTML = '';
            if (!items.length) { hide(); return; }
            items.forEach(function (t) {
                var li = document.createElement('li');
                li.setAttribute('role', 'option');
                li.textContent = t;
                li.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    input.value = t;
                    hide();
                });
                dd.appendChild(li);
            });
            dd.classList.add('is-open');
        }
        function run(q) {
            fetch(acUrl + encodeURIComponent(field) + '&q=' + encodeURIComponent(q))
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    items = (data && data.items) ? data.items : [];
                    render();
                })
                .catch(function () { items = []; hide(); });
        }
        input.addEventListener('input', function () {
            clearTimeout(timer);
            var v = input.value.trim();
            timer = setTimeout(function () { run(v); }, 200);
        });
        input.addEventListener('focus', function () { run(input.value.trim()); });
        input.addEventListener('blur', function () { setTimeout(hide, 180); });
    }
    document.querySelectorAll('.js-ac').forEach(attachAc);

    if (typeof flatpickr === 'function') {
        var fpMap = {};
        document.querySelectorAll('.js-driver-date-input').forEach(function (inp) {
            if (inp._fp) return;
            inp._fp = flatpickr(inp, { dateFormat: 'd-m-Y', allowInput: true });
            fpMap[inp.id] = inp._fp;
        });
        document.querySelectorAll('.js-driver-date-cal').forEach(function (btn) {
            var tid = btn.getAttribute('data-target');
            if (!tid) return;
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                var fp = fpMap[tid];
                if (fp) fp.open();
            });
        });
    }
})();
</script>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
