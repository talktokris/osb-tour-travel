<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/home_dashboard_service.php';

$currentPage = 'home';

require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';

$flash = home_dashboard_flash_get();
$csrf = home_dashboard_csrf_token();
$countries = home_dashboard_countries($mysqli);
$cities = home_dashboard_cities($mysqli);

$pattern = home_dashboard_resolve_supplier_like_pattern();
$returnLetter = isset($_GET['letter']) ? (string) $_GET['letter'] : '';
$returnAz = isset($_GET['az']) ? trim((string) $_GET['az']) : '';

$searchPax = '';
$searchFileNo = '';
$countrySel = '';
$citySel = '';
$bookingRows = null;
$bookingSearchError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['home_booking_search'])) {
    $token = (string) ($_POST['_token'] ?? '');
    if (!home_dashboard_csrf_validate($token)) {
        $bookingSearchError = 'Invalid request token.';
    } else {
        $searchPax = trim((string) ($_POST['search_pax'] ?? ''));
        $searchFileNo = trim((string) ($_POST['search_file_no'] ?? ''));
        $countrySel = trim((string) ($_POST['country'] ?? ''));
        $citySel = trim((string) ($_POST['city'] ?? ''));
        $bookingRows = home_dashboard_search_pending_bookings($mysqli, $searchPax, $searchFileNo, $countrySel, $citySel);
    }
}

$strip = home_dashboard_az_letter_strip();
$supplierSummary = null;
$pendingEntries = [];
$showLetterView = $pattern !== null && $bookingRows === null;

if ($showLetterView) {
    $pendingEntries = home_dashboard_file_entries_pending_by_supplier_like($mysqli, $pattern);
} elseif ($bookingRows === null) {
    $supplierSummary = home_dashboard_suppliers_pending_summary($mysqli);
}
?>

<style>
    .home-legacy-booking-panel {
        background: #ffffee;
        border: 1px solid #b8d4a8;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.6);
    }
    .home-legacy-agent-panel {
        border: 1px solid #c5ccd6;
        background: #fff;
    }
    .home-legacy-title-green {
        color: #009900;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 1.125rem;
        font-weight: 700;
        letter-spacing: 0.01em;
    }
    /* Match legacy home.php: fixed left column + fluid right (always side-by-side from 600px) */
    .home-dashboard-columns {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
        width: 100%;
    }
    @media (min-width: 600px) {
        .home-dashboard-columns {
            flex-direction: row;
            align-items: flex-start;
            gap: 1.5rem; /* same as setup: gap-6 */
        }
        /* Match setup pages sidebar: Tailwind w-72 = 18rem */
        .home-dashboard-agent-col {
            width: 18rem;
            max-width: 18rem;
            flex-shrink: 0;
        }
        .home-dashboard-main-col {
            flex: 1;
            min-width: 0;
        }
    }
    /* Icon + text in one control (avoids absolute positioning quirks) */
    .home-agent-input-join {
        display: flex;
        align-items: center;
        width: 100%;
        min-height: 2.75rem;
        height: 2.75rem;
        padding: 0 0.75rem;
        gap: 0.5rem;
        border: 1px solid color-mix(in oklab, var(--color-base-content, #64748b) 20%, transparent);
        border-radius: var(--rounded-btn, 0.5rem);
        background: var(--color-base-100, #fff);
        box-sizing: border-box;
    }
    .home-agent-input-join:focus-within {
        border-color: var(--color-primary, #2563eb);
        outline: 2px solid color-mix(in oklab, var(--color-primary, #2563eb) 35%, transparent);
        outline-offset: 1px;
    }
    .home-agent-input-join input {
        flex: 1 1 0%;
        min-width: 0;
        height: 100%;
        border: 0;
        background: transparent;
        font-size: 0.875rem;
        line-height: 1.25rem;
        outline: none;
    }
    .home-agent-input-join svg {
        flex-shrink: 0;
        width: 1rem;
        height: 1rem;
        opacity: 0.45;
    }
    /* Full-width A–Z: equal columns */
    .home-az-strip {
        display: flex;
        flex-direction: row;
        flex-wrap: nowrap;
        width: 100%;
        align-items: center;
        justify-content: space-between;
        gap: 0.125rem;
        box-sizing: border-box;
    }
    .home-az-link {
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
    .home-az-link:hover {
        text-decoration: underline;
    }
    @media (max-width: 599px) {
        .home-az-strip {
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.25rem 0.15rem;
        }
        .home-az-link {
            flex: 0 0 auto;
            min-width: 1.5rem;
            padding: 0.25rem 0.35rem;
        }
    }
</style>

<main class="w-full max-w-[1000px] mx-auto px-3 sm:px-4 pb-6">
        <div class="space-y-4">
            <?php if ($flash): ?>
                <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-error' ?> shadow-sm text-sm">
                    <span><?= h((string) $flash['message']) ?></span>
                </div>
            <?php endif; ?>

            <!-- Legacy home.php: left = Agent Search box, right = booking + lists -->
            <div class="home-dashboard-columns">
                <aside class="home-dashboard-agent-col home-legacy-agent-panel rounded-sm p-4 flex flex-col min-h-[168px] w-full" aria-label="Agent search">
                    <h2 class="home-legacy-title-green mb-3">Agent Search</h2>
                    <form method="post" action="index.php?page=home_agent_search" class="flex flex-col gap-3 flex-1">
                        <input type="hidden" name="_token" value="<?= h($csrf) ?>">
                        <div class="home-agent-input-join w-full" role="group" aria-label="Search agents">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            <input type="text" name="search_word" id="home-dash-agent-input" placeholder="Code or name"
                                   maxlength="100" autocomplete="off" list="home-dash-agent-dl">
                        </div>
                        <datalist id="home-dash-agent-dl"></datalist>
                        <div class="flex justify-end pt-1">
                            <button type="submit" class="btn btn-success btn-sm px-6 min-h-9 h-9 border-0 text-white font-semibold shadow-sm" style="background:linear-gradient(180deg,#5cb85c,#449d44);">Search</button>
                        </div>
                    </form>
                </aside>

                <div class="home-dashboard-main-col flex flex-col gap-4 w-full min-w-0">
                <div class="home-legacy-booking-panel rounded-sm p-4 sm:p-5 w-full">
                    <div class="flex flex-wrap items-start justify-between gap-2 mb-3">
                        <h2 class="home-legacy-title-green leading-tight">Direct Transport Booking Pending Home</h2>
                        <a href="index.php?page=home_cancel_report" class="text-sm font-bold text-error hover:underline shrink-0">Cancel Bookings</a>
                    </div>
                    <form method="post" action="index.php?page=home" class="space-y-3">
                        <input type="hidden" name="_token" value="<?= h($csrf) ?>">
                        <input type="hidden" name="home_booking_search" value="1">
                        <!-- Row 1: same horizontal layout as old app -->
                        <div class="flex flex-wrap xl:flex-nowrap items-end gap-2 xl:gap-3 w-full">
                            <div class="flex flex-col min-w-[8.5rem] flex-1">
                                <span class="text-xs font-medium text-center sm:text-left mb-1 text-base-content/80">Guest Name :</span>
                                <input type="text" name="search_pax" id="home-dash-pax" class="input input-bordered input-sm w-full h-9 bg-white text-sm" value="<?= h($searchPax) ?>" autocomplete="off" list="home-dash-pax-dl">
                                <datalist id="home-dash-pax-dl"></datalist>
                            </div>
                            <div class="flex flex-col min-w-[8.5rem] flex-1">
                                <span class="text-xs font-medium text-center sm:text-left mb-1 text-base-content/80">File No:</span>
                                <input type="text" name="search_file_no" id="home-dash-file" class="input input-bordered input-sm w-full h-9 bg-white text-sm" value="<?= h($searchFileNo) ?>" autocomplete="off" list="home-dash-file-dl">
                                <datalist id="home-dash-file-dl"></datalist>
                            </div>
                            <div class="flex flex-col min-w-[9rem] flex-1">
                                <span class="text-xs font-medium text-center sm:text-left mb-1 text-base-content/80">Country:</span>
                                <select name="country" class="select select-bordered select-sm w-full h-9 min-h-9 bg-white text-sm">
                                    <option value="">Select Country</option>
                                    <?php foreach ($countries as $c): ?>
                                        <option value="<?= h($c) ?>" <?= $countrySel === $c ? 'selected' : '' ?>><?= h($c) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="flex flex-col min-w-[9rem] flex-1">
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
                        <?php if ($bookingSearchError !== ''): ?>
                            <p class="text-error text-sm"><?= h($bookingSearchError) ?></p>
                        <?php endif; ?>
                        <!-- Row 2: A–Z strip — full width, equal spacing -->
                        <div class="home-az-strip pt-2 pb-0.5 border-t border-amber-200/80">
                            <?php foreach ($strip as $item): ?>
                                <?php
                                $cnt = home_dashboard_count_pending_supplier_like($mysqli, $item['pattern']);
                                $isActive = $pattern !== null && $item['pattern'] === $pattern;
                                $cls = $cnt >= 1 ? 'text-error' : 'text-base-content/35';
                                $href = 'index.php?' . http_build_query([
                                    'page' => 'home',
                                    'letter' => home_dashboard_letter_query_value($item['label'], $item['pattern']),
                                ]);
                                ?>
                                <a href="<?= h($href) ?>" class="home-az-link <?= h($cls) ?> <?= $isActive ? 'underline decoration-2 underline-offset-2 ring-1 ring-base-300/80 bg-base-200/40' : '' ?>"><?= h($item['label']) ?></a>
                            <?php endforeach; ?>
                        </div>
                    </form>
                    <p class="text-[11px] text-base-content/55 mt-3 pt-2 border-t border-amber-200/60">
                        <a href="index.php?page=home_browse" class="text-primary font-semibold hover:underline">Supplier directory (A–Z)</a>
                        — browse suppliers by letter, then open pending lines per supplier.
                    </p>
                </div>

            <?php if (is_array($bookingRows)): ?>
                <div class="card bg-base-100 shadow-xl border border-base-300">
                    <div class="card-body">
                        <h3 class="font-semibold mb-3">Booking search results</h3>
                        <?php if ($bookingRows === []): ?>
                            <p class="text-base-content/70">No pending bookings match these filters.</p>
                        <?php else: ?>
                            <div class="overflow-x-auto rounded-box border border-base-200">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>File no</th>
                                            <th>Supplier</th>
                                            <th>Guest</th>
                                            <th>Agent</th>
                                            <th>Pickup</th>
                                            <th>Drop-off</th>
                                            <th>Service</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bookingRows as $row): ?>
                                            <?php $guest = trim((string) ($row['first_name'] ?? '') . ' ' . (string) ($row['last_name'] ?? '')); ?>
                                            <tr>
                                                <td><?= h((string) ($row['file_no'] ?? '')) ?></td>
                                                <td><?= h((string) ($row['supplier_name'] ?? '')) ?></td>
                                                <td><?= h(trim($guest)) ?></td>
                                                <td><?= h((string) ($row['agent_name'] ?? '')) ?></td>
                                                <td class="max-w-[120px] truncate"><?= h((string) ($row['from_location'] ?? '')) ?></td>
                                                <td class="max-w-[120px] truncate"><?= h((string) ($row['to_location'] ?? '')) ?></td>
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
            <?php elseif ($showLetterView): ?>
                <div class="card bg-base-100 shadow-xl border border-base-300">
                    <div class="card-body space-y-4">
                        <h3 class="font-semibold">Pending by supplier name (letter filter)</h3>
                        <?php if ($pendingEntries === []): ?>
                            <p class="text-base-content/70">No pending bookings for this letter filter.</p>
                        <?php else: ?>
                            <div class="overflow-x-auto rounded-box border border-base-200">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>File no</th>
                                            <th>Supplier</th>
                                            <th>Guest</th>
                                            <th>Agent</th>
                                            <th>Pickup</th>
                                            <th>Drop-off</th>
                                            <th>Service</th>
                                            <th>Date</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pendingEntries as $row): ?>
                                            <?php
                                            $fid = (int) ($row['file_id'] ?? 0);
                                            $guest = trim((string) ($row['first_name'] ?? '') . ' ' . (string) ($row['last_name'] ?? ''));
                                            ?>
                                            <tr>
                                                <td><?= h((string) ($row['file_no'] ?? '')) ?></td>
                                                <td><?= h((string) ($row['supplier_name'] ?? '')) ?></td>
                                                <td><?= h(trim($guest)) ?></td>
                                                <td><?= h((string) ($row['agent_name'] ?? '')) ?></td>
                                                <td class="max-w-[120px] truncate"><?= h((string) ($row['from_location'] ?? '')) ?></td>
                                                <td class="max-w-[120px] truncate"><?= h((string) ($row['to_location'] ?? '')) ?></td>
                                                <td><?= h((string) ($row['service'] ?? '')) ?></td>
                                                <td><?= h((string) ($row['service_date'] ?? '')) ?></td>
                                                <td class="text-end whitespace-nowrap">
                                                    <button type="button" class="btn btn-xs btn-success js-home-confirm" data-file-id="<?= $fid ?>">Confirm</button>
                                                    <button type="button" class="btn btn-xs btn-error js-home-cancel-open" data-file-id="<?= $fid ?>">Cancel</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <form id="home-main-action-form" method="post" action="index.php?page=home_booking_action" class="hidden">
                                <input type="hidden" name="_token" value="<?= h($csrf) ?>">
                                <input type="hidden" name="file_id" id="home-main-action-file-id" value="">
                                <input type="hidden" name="action" id="home-main-action-action" value="">
                                <input type="hidden" name="return_letter" value="<?= h($returnLetter) ?>">
                                <input type="hidden" name="return_az" value="<?= h($returnAz) ?>">
                                <input type="hidden" name="return_to" value="home">
                            </form>

                            <dialog id="home-main-cancel-dialog" class="agent-delete-dialog">
                                <div class="agent-delete-dialog__surface">
                                    <h3 class="font-bold text-lg mb-1">Cancel booking?</h3>
                                    <p class="agent-delete-dialog__message">This marks the booking as cancelled (status only). Continue?</p>
                                    <div class="agent-delete-dialog__actions">
                                        <button type="button" class="btn btn-outline" id="home-main-cancel-no">No</button>
                                        <button type="button" class="btn btn-error" id="home-main-cancel-yes">Yes, cancel</button>
                                    </div>
                                </div>
                            </dialog>

                            <script>
                            (function () {
                                var form = document.getElementById('home-main-action-form');
                                var fid = document.getElementById('home-main-action-file-id');
                                var act = document.getElementById('home-main-action-action');
                                var dlg = document.getElementById('home-main-cancel-dialog');
                                var noBtn = document.getElementById('home-main-cancel-no');
                                var yesBtn = document.getElementById('home-main-cancel-yes');
                                if (!form || !fid || !act || !dlg || !noBtn || !yesBtn) return;
                                document.querySelectorAll('.js-home-confirm').forEach(function (btn) {
                                    btn.addEventListener('click', function () {
                                        fid.value = this.getAttribute('data-file-id') || '';
                                        act.value = 'confirm';
                                        form.submit();
                                    });
                                });
                                document.querySelectorAll('.js-home-cancel-open').forEach(function (btn) {
                                    btn.addEventListener('click', function () {
                                        fid.value = this.getAttribute('data-file-id') || '';
                                        act.value = 'cancel';
                                        dlg.showModal();
                                    });
                                });
                                noBtn.addEventListener('click', function () { dlg.close(); });
                                yesBtn.addEventListener('click', function () { dlg.close(); form.submit(); });
                            })();
                            </script>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif (is_array($supplierSummary)): ?>
                <div class="card bg-base-100 shadow-xl border border-base-300">
                    <div class="card-body">
                        <h3 class="font-semibold mb-3">Suppliers with pending direct transport bookings</h3>
                        <?php if ($supplierSummary === []): ?>
                            <p class="text-base-content/70">No pending bookings right now.</p>
                        <?php else: ?>
                            <div class="overflow-x-auto rounded-box border border-base-200">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Supplier</th>
                                            <th>Country</th>
                                            <th>City</th>
                                            <th>Pending</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($supplierSummary as $row): ?>
                                            <tr>
                                                <td>
                                                    <a class="link link-primary font-medium" href="index.php?page=home_supplier_bookings&amp;supplier=<?= h(rawurlencode($row['supplier_name'])) ?>"><?= h($row['supplier_name']) ?></a>
                                                </td>
                                                <td><?= h($row['supplier_country']) ?></td>
                                                <td><?= h($row['supplier_city']) ?></td>
                                                <td><?= (int) $row['pending_count'] ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

                </div><!-- /.home-dashboard-main-col -->
            </div><!-- /.home-dashboard-columns -->

            <p class="text-sm text-base-content/70">
                Welcome, <span class="font-medium"><?= h($_SESSION['user_name'] ?? 'User') ?></span>.
            </p>
        </div>
</main>

<script>
(function () {
    function wire(inputId, type, listId) {
        var input = document.getElementById(inputId);
        var list = document.getElementById(listId);
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
    wire('home-dash-agent-input', 'agent', 'home-dash-agent-dl');
    wire('home-dash-pax', 'pax', 'home-dash-pax-dl');
    wire('home-dash-file', 'file_no', 'home-dash-file-dl');
})();
</script>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
