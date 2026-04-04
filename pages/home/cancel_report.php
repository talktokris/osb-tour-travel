<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/home_dashboard_service.php';

require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';

$currentPage = 'home_cancel_report';
$flash = home_dashboard_flash_get();
$csrf = home_dashboard_csrf_token();
$countries = home_dashboard_countries($mysqli);
$cities = home_dashboard_cities($mysqli);

$searchPax = '';
$searchFileNo = '';
$countrySel = '';
$citySel = '';
$inlineError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string) ($_POST['_token'] ?? '');
    if (!home_dashboard_csrf_validate($token)) {
        $inlineError = 'Invalid request token.';
    } else {
        $searchPax = trim((string) ($_POST['search_pax'] ?? ''));
        $searchFileNo = trim((string) ($_POST['search_file_no'] ?? ''));
        $countrySel = trim((string) ($_POST['country'] ?? ''));
        $citySel = trim((string) ($_POST['city'] ?? ''));
    }
}

$cancelled = home_dashboard_cancelled_bookings($mysqli, 50);
$strip = home_dashboard_az_letter_strip();
?>

<main class="w-full max-w-[1000px] mx-auto px-3 sm:px-4 pb-6">
        <div class="space-y-4">
            <?php $breadcrumbCurrent = 'Cancel bookings report'; require __DIR__ . '/../../includes/breadcrumb.php'; ?>

            <?php if ($flash): ?>
                <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-error' ?> shadow-sm">
                    <span><?= h((string) $flash['message']) ?></span>
                </div>
            <?php endif; ?>
            <?php if ($inlineError !== ''): ?>
                <div class="alert alert-error shadow-sm"><span><?= h($inlineError) ?></span></div>
            <?php endif; ?>

            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body space-y-4">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <h2 class="card-title text-lg">Direct transport — cancel bookings</h2>
                        <a href="index.php?page=home" class="link link-primary text-sm font-semibold">Back to home</a>
                    </div>

                    <div class="bg-warning/15 border border-warning/40 rounded-box p-4">
                        <form method="post" action="index.php?page=home_cancel_report" class="space-y-3">
                            <input type="hidden" name="_token" value="<?= h($csrf) ?>">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                                <div class="form-control">
                                    <label class="label py-1"><span class="label-text text-xs font-medium">Guest name</span></label>
                                    <input type="text" name="search_pax" id="cancel-search-pax" class="input input-bordered input-sm w-full" value="<?= h($searchPax) ?>" autocomplete="off" list="cancel-dl-pax">
                                    <datalist id="cancel-dl-pax"></datalist>
                                </div>
                                <div class="form-control">
                                    <label class="label py-1"><span class="label-text text-xs font-medium">File no</span></label>
                                    <input type="text" name="search_file_no" id="cancel-search-file" class="input input-bordered input-sm w-full" value="<?= h($searchFileNo) ?>" autocomplete="off" list="cancel-dl-file">
                                    <datalist id="cancel-dl-file"></datalist>
                                </div>
                                <div class="form-control">
                                    <label class="label py-1"><span class="label-text text-xs font-medium">Country</span></label>
                                    <select name="country" class="select select-bordered select-sm w-full">
                                        <option value="">Select country</option>
                                        <?php foreach ($countries as $c): ?>
                                            <option value="<?= h($c) ?>" <?= $countrySel === $c ? 'selected' : '' ?>><?= h($c) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-control">
                                    <label class="label py-1"><span class="label-text text-xs font-medium">City</span></label>
                                    <select name="city" class="select select-bordered select-sm w-full">
                                        <option value="">Select city</option>
                                        <?php foreach ($cities as $c): ?>
                                            <option value="<?= h($c) ?>" <?= $citySel === $c ? 'selected' : '' ?>><?= h($c) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2 justify-between items-center">
                                <div class="flex flex-wrap gap-x-1 gap-y-2 text-sm font-semibold">
                                    <?php foreach ($strip as $item): ?>
                                        <?php
                                        $cnt = home_dashboard_count_nonconfirmed_supplier_like($mysqli, $item['pattern']);
                                        $cls = $cnt >= 1 ? 'text-error' : 'text-base-content/30';
                                        $href = 'index.php?' . http_build_query([
                                            'page' => 'home_browse',
                                            'letter' => home_dashboard_letter_query_value($item['label'], $item['pattern']),
                                        ]);
                                        ?>
                                        <a href="<?= h($href) ?>" class="<?= h($cls) ?> no-underline hover:underline px-1"><?= h($item['label']) ?></a>
                                    <?php endforeach; ?>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">Search</button>
                            </div>
                        </form>
                    </div>

                    <h3 class="text-error font-semibold text-center">Cancel booking list</h3>

                    <?php if ($cancelled === []): ?>
                        <p class="text-base-content/70 text-center">No results found.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto rounded-box border border-base-200">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>File no</th>
                                        <th>Supplier</th>
                                        <th>Agent</th>
                                        <th>Guest</th>
                                        <th>Pickup</th>
                                        <th>Drop-off</th>
                                        <th>Service</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cancelled as $row): ?>
                                        <tr>
                                            <td><?= h((string) ($row['file_no'] ?? '')) ?></td>
                                            <td><?= h((string) ($row['supplier_name'] ?? '')) ?></td>
                                            <td><?= h((string) ($row['agent_name'] ?? '')) ?></td>
                                            <td><?= h((string) ($row['first_name'] ?? '')) ?></td>
                                            <td><?= h((string) ($row['from_location'] ?? '')) ?></td>
                                            <td><?= h((string) ($row['to_location'] ?? '')) ?></td>
                                            <td><?= h((string) ($row['service'] ?? '')) ?></td>
                                            <td><?= h((string) ($row['service_date'] ?? '')) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
</main>

<script>
(function () {
    function wire(id, type, datalistId) {
        var input = document.getElementById(id);
        var list = document.getElementById(datalistId);
        if (!input || !list) return;
        var t;
        input.addEventListener('input', function () {
            clearTimeout(t);
            var q = input.value.trim();
            if (q.length < 1) { list.innerHTML = ''; return; }
            t = setTimeout(function () {
                fetch('index.php?page=home_autocomplete&type=' + encodeURIComponent(type) + '&q=' + encodeURIComponent(q))
                    .then(function (r) { return r.json(); })
                    .then(function (arr) {
                        if (!Array.isArray(arr)) return;
                        list.innerHTML = '';
                        arr.slice(0, 25).forEach(function (s) {
                            var o = document.createElement('option');
                            o.value = s;
                            list.appendChild(o);
                        });
                    }).catch(function () {});
            }, 200);
        });
    }
    wire('cancel-search-pax', 'pax', 'cancel-dl-pax');
    wire('cancel-search-file', 'file_no', 'cancel-dl-file');
})();
</script>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
