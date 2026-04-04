<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/home_dashboard_service.php';

require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';

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

/* Legacy cancel_report.php: list is all cancelled rows (limit 50); form filters do not change SQL */
$cancelled = home_dashboard_cancelled_bookings($mysqli, 50);
$strip = home_dashboard_az_letter_strip();
?>

<style>
    .cancel-legacy-booking-panel {
        background: #ffffee;
        border: 1px solid #b8d4a8;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.6);
    }
    .cancel-legacy-title-green {
        color: #009900;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 1.125rem;
        font-weight: 700;
        letter-spacing: 0.01em;
    }
    .cancel-az-box {
        width: 100%;
        box-sizing: border-box;
        padding: 0.5rem 0.4rem 0.55rem;
        margin-top: 0.35rem;
        background: #f6fff6;
        border: 1px solid #b8d4a8;
        border-radius: 0.375rem;
    }
    .cancel-az-strip {
        display: flex;
        flex-direction: row;
        flex-wrap: nowrap;
        width: 100%;
        align-items: center;
        justify-content: space-between;
        gap: 0.125rem;
        box-sizing: border-box;
    }
    .cancel-az-link {
        flex: 1 1 0%;
        min-width: 0;
        text-align: center;
        font-size: 0.8125rem;
        font-weight: 700;
        line-height: 1.2;
        padding: 0.35rem 0.05rem;
        border-radius: 0.25rem;
        text-decoration: none;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .cancel-az-link:hover {
        text-decoration: underline;
    }
    .cancel-az-red {
        color: #990000;
    }
    .cancel-az-gray {
        color: #cccccc;
    }
    @media (max-width: 599px) {
        .cancel-az-strip {
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.25rem 0.15rem;
        }
        .cancel-az-link {
            flex: 0 0 auto;
            min-width: 1.5rem;
            padding: 0.25rem 0.35rem;
        }
    }
</style>

<main class="w-full max-w-none px-3 sm:px-5 lg:px-6 pb-6">
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

        <!-- Legacy cancel_report.php: yellow panel, horizontal row + A–Z (red/gray = non-confirmed counts) -->
        <div class="cancel-legacy-booking-panel rounded-sm p-4 sm:p-5 w-full">
            <div class="flex flex-wrap items-start justify-between gap-2 mb-3">
                <h2 class="cancel-legacy-title-green leading-tight">Direct Transport Booking Pending Home</h2>
                <a href="index.php?page=home_cancel_report" class="text-sm font-bold text-error hover:underline shrink-0">Cancel Bookings</a>
            </div>

            <form method="post" action="index.php?page=home_cancel_report" class="space-y-3">
                <input type="hidden" name="_token" value="<?= h($csrf) ?>">
                <div class="flex flex-wrap xl:flex-nowrap items-end gap-2 xl:gap-3 w-full">
                    <div class="flex flex-col min-w-34 flex-1">
                        <span class="text-xs font-medium text-center sm:text-left mb-1 text-base-content/80">Guest Name :</span>
                        <input type="text" name="search_pax" id="cancel-search-pax" class="input input-bordered input-sm w-full h-9 bg-white text-sm" value="<?= h($searchPax) ?>" autocomplete="off" list="cancel-dl-pax">
                        <datalist id="cancel-dl-pax"></datalist>
                    </div>
                    <div class="flex flex-col min-w-34 flex-1">
                        <span class="text-xs font-medium text-center sm:text-left mb-1 text-base-content/80">File No:</span>
                        <input type="text" name="search_file_no" id="cancel-search-file" class="input input-bordered input-sm w-full h-9 bg-white text-sm" value="<?= h($searchFileNo) ?>" autocomplete="off" list="cancel-dl-file">
                        <datalist id="cancel-dl-file"></datalist>
                    </div>
                    <div class="flex flex-col min-w-36 flex-1">
                        <span class="text-xs font-medium text-center sm:text-left mb-1 text-base-content/80">Country:</span>
                        <select name="country" class="select select-bordered select-sm w-full h-9 min-h-9 bg-white text-sm">
                            <option value="">Select Country</option>
                            <?php foreach ($countries as $c): ?>
                                <option value="<?= h($c) ?>" <?= $countrySel === $c ? 'selected' : '' ?>><?= h($c) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex flex-col min-w-36 flex-1">
                        <span class="text-xs font-medium text-center sm:text-left mb-1 text-base-content/80">City :</span>
                        <select name="city" class="select select-bordered select-sm w-full h-9 min-h-9 bg-white text-sm">
                            <option value="">Select City</option>
                            <?php foreach ($cities as $c): ?>
                                <option value="<?= h($c) ?>" <?= $citySel === $c ? 'selected' : '' ?>><?= h($c) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex flex-col justify-end shrink-0 pb-px">
                        <span class="text-xs mb-1 opacity-0 select-none hidden xl:block">.</span>
                        <button type="submit" class="btn btn-success btn-sm px-5 min-h-9 h-9 border-0 text-white font-semibold whitespace-nowrap shadow-sm" style="background:linear-gradient(180deg,#5cb85c,#449d44);">Search</button>
                    </div>
                </div>

                <div class="cancel-az-box">
                    <div class="cancel-az-strip">
                        <?php foreach ($strip as $item): ?>
                            <?php
                            $cnt = home_dashboard_count_nonconfirmed_supplier_like($mysqli, $item['pattern']);
                            $countCls = $cnt >= 1 ? 'cancel-az-red' : 'cancel-az-gray';
                            $href = 'index.php?' . http_build_query([
                                'page' => 'home_browse',
                                'letter' => home_dashboard_letter_query_value($item['label'], $item['pattern']),
                            ]);
                            ?>
                            <a href="<?= h($href) ?>" class="cancel-az-link <?= h($countCls) ?>"><?= h($item['label']) ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </form>
        </div>

        <div class="text-center space-y-1 pt-1">
            <h2 class="text-xl font-bold text-[#990000]">Cancel Booking List</h2>
            <?php if ($cancelled === []): ?>
                <p class="text-sm font-semibold text-[#990000]">No Result found</p>
            <?php else: ?>
                <div class="overflow-x-auto rounded-box border border-base-200 bg-base-100 text-left mt-3">
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
                            <?php foreach ($cancelled as $i => $row): ?>
                                <tr class="<?= $i % 2 === 0 ? 'bg-base-200/30' : '' ?>">
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
