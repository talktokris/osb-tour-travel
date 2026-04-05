<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/file_module_service.php';

$currentPage = 'file_book';

if (!file_module_has_agent()) {
    file_module_render_agent_required();
}

$serviceId = (int) ($_GET['service_id'] ?? 0);
$service = $serviceId > 0 ? file_module_service_by_id($mysqli, $serviceId) : null;

if ($service === null) {
    require __DIR__ . '/../../includes/header.php';
    require __DIR__ . '/../../includes/nav.php';
    echo '<div class="flex gap-6 w-full pb-6"><aside class="hidden lg:block w-72 shrink-0">';
    require __DIR__ . '/sidebar.php';
    echo '</aside><main class="flex-1"><div class="alert alert-warning">Invalid service.</div><a class="btn btn-sm" href="index.php?page=file">Back</a></div></div>';
    require __DIR__ . '/../../includes/footer.php';
    exit;
}

$csrf = file_module_csrf_token();
$state = file_module_state();
$c = $state['criteria'];
$g = $state['guest'];
$flashErr = '';

$adults = max(0, (int) $c['adults']);
$children = max(0, (int) $c['children']);
$prices = file_module_compute_prices($service, $adults, $children);
$suppliers = file_module_supplier_names($mysqli);
$drivers = file_module_drivers($mysqli);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_add_basket'])) {
    $token = (string) ($_POST['_token'] ?? '');
    if (!file_module_csrf_validate($token)) {
        $flashErr = 'Invalid token.';
    } else {
        $fcn = trim((string) ($_POST['file_count_no'] ?? ''));
        if ($fcn === '') {
            $fcn = file_module_next_file_count_no($mysqli);
            file_module_set_file_count_no($fcn);
        } else {
            $uname = (string) ($_SESSION['user_name'] ?? '');
            if (!file_module_user_can_access_file_count($mysqli, $fcn, $uname)) {
                $flashErr = 'Invalid file group.';
                $fcn = '';
            }
        }
        if ($flashErr === '' && $fcn !== '') {
            $fileNo = trim((string) ($_POST['file_no'] ?? ''));
            $title = trim((string) ($_POST['title'] ?? 'Mr'));
            $last = trim((string) ($_POST['last_name'] ?? ''));
            $first = trim((string) ($_POST['first_name'] ?? ''));
            if (strlen($last) < 2 || strlen($first) < 2) {
                $flashErr = 'Last name and first name must be at least 2 characters.';
            } else {
                file_module_set_file_no($fileNo);
                file_module_save_guest([
                    'title' => $title,
                    'last_name' => $last,
                    'first_name' => $first,
                    'pax_mobile' => (string) ($_POST['pax_mobile'] ?? ''),
                    'ref_no' => (string) ($_POST['ref_no'] ?? ''),
                ]);
                $g = file_module_state()['guest'];
                $svcName = (string) ($service['service_name_english'] ?? '');
                $svcType = (string) ($service['service_type'] ?? '');
                $veh = (string) ($service['vehicle_type'] ?? '');
                $dmy = (string) ($_POST['service_date'] ?? $c['service_date']);
                $ymd = file_module_parse_service_date($dmy);
                if ($ymd === null) {
                    $flashErr = 'Invalid service date (use dd-mm-yyyy).';
                } else {
                    $driverName = trim((string) ($_POST['driver_name'] ?? ''));
                    $dm = file_module_driver_mobile($mysqli, $driverName);
                    $fhr = (string) ($_POST['fhr'] ?? '0');
                    $fmin = (string) ($_POST['fmin'] ?? '0');
                    $phr = (string) ($_POST['phr'] ?? '0');
                    $pmin = (string) ($_POST['pmin'] ?? '0');
                    $row = [
                        'agent_name' => file_module_agent_name(),
                        'from_location' => $c['from_location'],
                        'from_country' => $c['from_country'],
                        'from_city' => $c['from_city'],
                        'from_zone' => $c['from_zone'],
                        'to_location' => $c['to_location'],
                        'to_country' => $c['to_country'] !== '' ? $c['to_country'] : $c['from_country'],
                        'to_city' => $c['to_city'],
                        'to_zone' => $c['to_zone'],
                        'service' => $svcName,
                        'service_id' => (string) $serviceId,
                        'service_type' => $svcType,
                        'service_cat' => $c['service_cat'],
                        'vehicle_type' => $veh,
                        'service_date' => $ymd,
                        'adults' => (string) $adults,
                        'children' => (string) $children,
                        'no_of_pax' => (string) ($_POST['no_of_pax'] ?? $c['no_of_pax']),
                        'title' => $title,
                        'last_name' => $last,
                        'first_name' => $first,
                        'pax_mobile' => (string) ($_POST['pax_mobile'] ?? ''),
                        'ref_no' => (string) ($_POST['ref_no'] ?? ''),
                        'flight_time' => file_module_time_hm($fhr, $fmin),
                        'flight_no' => (string) ($_POST['flight_no'] ?? ''),
                        'pickup_time' => file_module_time_hm($phr, $pmin),
                        'pickup_from' => (string) ($_POST['pickup_from'] ?? ''),
                        'drop_off' => (string) ($_POST['drop_off'] ?? ''),
                        'supplier_name' => (string) ($_POST['supplier_name'] ?? ''),
                        'driver_name' => $driverName,
                        'driver_mobile' => $dm,
                        'remarks' => (string) ($_POST['remarks'] ?? ''),
                        'book_status' => 'Pending',
                        'file_no' => $fileNo,
                        'invoice_no' => '',
                        'selling_price' => (string) ($_POST['selling_price'] ?? $prices['selling']),
                        'buying_price' => (string) ($_POST['buying_price'] ?? $prices['buying']),
                        'file_count_no' => $fcn,
                        'user_enter_by' => (string) ($_SESSION['user_name'] ?? ''),
                        'date' => date('Y-m-d'),
                        'ip' => (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
                    ];
                    if (file_module_insert_file_entry($mysqli, $row)) {
                        header('Location: index.php?page=file_preview&file_count_no=' . rawurlencode($fcn));
                        exit;
                    }
                    $flashErr = 'Could not save booking.';
                }
            }
        }
    }
}

$state = file_module_state();
$c = $state['criteria'];
$g = $state['guest'];
$fcnCur = (string) ($state['file_count_no'] ?? '');
$pickDef = $c['from_zone'] !== '' ? $c['from_zone'] : $c['from_location'];
$dropDef = $c['to_zone'] !== '' ? $c['to_zone'] : $c['to_location'];

$posted = $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_add_basket']);
$post = $posted ? $_POST : [];

$fv = [
    'file_no' => $posted ? trim((string) ($post['file_no'] ?? '')) : (string) ($state['file_no'] ?? ''),
    'title' => $posted ? trim((string) ($post['title'] ?? 'Mr')) : $g['title'],
    'last_name' => $posted ? trim((string) ($post['last_name'] ?? '')) : $g['last_name'],
    'first_name' => $posted ? trim((string) ($post['first_name'] ?? '')) : $g['first_name'],
    'pax_mobile' => $posted ? (string) ($post['pax_mobile'] ?? '') : $g['pax_mobile'],
    'ref_no' => $posted ? (string) ($post['ref_no'] ?? '') : $g['ref_no'],
    'service_date' => $posted ? trim((string) ($post['service_date'] ?? '')) : $c['service_date'],
    'fhr' => $posted ? (string) ($post['fhr'] ?? '0') : '0',
    'fmin' => $posted ? (string) ($post['fmin'] ?? '0') : '0',
    'flight_no' => $posted ? (string) ($post['flight_no'] ?? '') : '',
    'phr' => $posted ? (string) ($post['phr'] ?? '0') : '0',
    'pmin' => $posted ? (string) ($post['pmin'] ?? '0') : '0',
    'pickup_from' => $posted ? (string) ($post['pickup_from'] ?? '') : $pickDef,
    'drop_off' => $posted ? (string) ($post['drop_off'] ?? '') : $dropDef,
    'supplier_name' => $posted ? (string) ($post['supplier_name'] ?? '') : '',
    'driver_name' => $posted ? (string) ($post['driver_name'] ?? '') : '',
    'remarks' => $posted ? (string) ($post['remarks'] ?? '') : '',
    'file_count_no' => $posted ? trim((string) ($post['file_count_no'] ?? '')) : $fcnCur,
    'selling_price' => $posted ? (string) ($post['selling_price'] ?? $prices['selling']) : $prices['selling'],
    'buying_price' => $posted ? (string) ($post['buying_price'] ?? $prices['buying']) : $prices['buying'],
];

$priceShow = ($prices['selling'] !== '' && is_numeric($prices['selling']))
    ? number_format((float) $prices['selling'], 0, '.', ',')
    : $prices['selling'];
$vehLabel = trim((string) ($service['vehicle_type'] ?? ''));

require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';
?>

<style>
.book-sel {
    border: 1px solid #c9b87a;
    background: #fffce8;
    padding: 6px 8px 8px;
    margin-bottom: 2px;
}
.book-sel__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 4px;
    padding-bottom: 4px;
    border-bottom: 1px solid rgba(26, 107, 92, 0.18);
}
.book-sel__title {
    color: #00a651;
    font-size: 0.8125rem;
    font-weight: 700;
    margin: 0;
}
.book-sel__grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 2px 10px;
    font-size: 11px;
    line-height: 1.35;
}
@media (max-width: 900px) {
    .book-sel__grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 520px) {
    .book-sel__grid { grid-template-columns: 1fr; }
}
.book-sel__row {
    display: grid;
    grid-template-columns: minmax(5.5rem, auto) 1fr;
    gap: 4px 6px;
    align-items: baseline;
}
.book-sel__row strong { color: #1e293b; font-weight: 700; }
.book-sel__row--full { grid-column: 1 / -1; }
.book-sel__price {
    margin-top: 4px;
    padding: 4px 8px;
    border-radius: 4px;
    background: rgba(20, 184, 166, 0.12);
    border: 1px solid rgba(20, 184, 166, 0.28);
    font-size: 0.75rem;
    font-weight: 700;
    color: #0f766e;
}
.book-form--compact { font-size: 12px; }
.book-form--compact .book-card {
    border: 1px solid rgba(26, 107, 92, 0.22);
    border-radius: 4px;
    background: #fff;
    box-shadow: none;
    overflow: hidden;
    margin-bottom: 0.25rem;
}
.book-form--compact .book-card > .book-guest-head:first-child {
    border-radius: 3px 3px 0 0;
}
.book-form--compact .book-card__head {
    background: #0f766e;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.01em;
    padding: 0.22rem 0.45rem;
    margin: 0;
    line-height: 1.2;
}
.book-form--compact .book-card__body {
    padding: 0.3rem 0.45rem 0.4rem;
}
.book-row {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 0.25rem 0.5rem;
}
.book-row--center { align-items: center; }
.book-row--pick {
    align-items: center;
    gap: 0.35rem 0.5rem;
}
.book-inline-lbl {
    font-size: 11px;
    font-weight: 600;
    color: #334155;
    white-space: nowrap;
    flex-shrink: 0;
}
.book-stack {
    display: flex;
    flex-direction: column;
    gap: 1px;
    min-width: 0;
}
.book-stack__lbl {
    font-size: 10px;
    font-weight: 600;
    color: #64748b;
    line-height: 1.1;
}
.book-guest-head {
    display: grid;
    grid-template-columns: 1.25rem 4.5rem 1fr 1fr;
    gap: 0.35rem;
    align-items: center;
    font-size: 10px;
    font-weight: 700;
    color: #fff;
    text-transform: none;
    padding: 0.22rem 0.45rem;
    margin: 0;
    width: 100%;
    box-sizing: border-box;
    background: #0f766e;
    border-bottom: 1px solid rgba(255, 255, 255, 0.15);
}
.book-guest-row {
    display: grid;
    grid-template-columns: 1.25rem 4.5rem 1fr 1fr;
    gap: 0.35rem;
    align-items: center;
}
.book-guest-row2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem 1rem;
    margin-top: 0.25rem;
}
@media (max-width: 640px) {
    .book-guest-head,
    .book-guest-row { grid-template-columns: 1.25rem 4rem 1fr; }
    .book-guest-head span:nth-child(4),
    .book-guest-row > :nth-child(4) { grid-column: 2 / -1; }
}
.book-timing-row {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 0.35rem 0.55rem;
}
.book-timing-row .book-inline-lbl--muted {
    font-size: 11px;
    color: #475569;
    align-self: center;
    padding-right: 0.15rem;
}
.book-form--compact .input.input-sm,
.book-form--compact .select.select-sm {
    min-height: 1.55rem;
    height: 1.55rem;
    padding: 0.05rem 0.3rem;
    font-size: 12px;
    line-height: 1.2;
}
.book-form--compact .textarea.textarea-sm {
    min-height: 2.75rem;
    padding: 0.2rem 0.35rem;
    font-size: 12px;
    line-height: 1.25;
}
.book-form--compact .select.select-sm {
    padding-top: 0;
    padding-bottom: 0;
}
.book-form--compact .w-time { width: 3.1rem; min-width: 3.1rem; }
.book-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    align-items: center;
    margin-top: 0.35rem;
}
@media (min-width: 768px) {
    .book-actions { justify-content: flex-end; }
}
.file-ts-dob-wrap {
    position: relative;
    max-width: 9.5rem;
    width: 100%;
}
.file-ts-dob-wrap .file-ts-dob-input {
    width: 100%;
    height: 1.55rem;
    min-height: 1.55rem;
    padding-left: 0.35rem;
    padding-right: 1.85rem;
    font-size: 12px;
    border: 1px solid #94a3b8;
    border-radius: 0.2rem;
    background: #fff;
    box-sizing: border-box;
}
.file-ts-dob-wrap .file-ts-dob-input:focus {
    outline: 2px solid color-mix(in oklab, #00a651 40%, transparent);
    outline-offset: 1px;
    border-color: #1a6b5c;
}
.file-ts-dob-cal-btn {
    position: absolute;
    right: 1px;
    top: 50%;
    transform: translateY(-50%);
    width: 1.6rem;
    height: 1.35rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin: 0;
    padding: 0;
    border: 0;
    border-radius: 0.2rem;
    background: transparent;
    cursor: pointer;
    color: #334155;
}
.file-ts-dob-cal-btn:hover {
    background: rgba(26, 107, 92, 0.1);
    color: #1a6b5c;
}
.flatpickr-calendar {
    border-radius: 10px;
    border: 1px solid #1a6b5c;
    box-shadow: 0 14px 44px rgba(15, 23, 42, 0.14), 0 4px 14px rgba(26, 107, 92, 0.1);
    font-family: inherit;
}
.flatpickr-months .flatpickr-month {
    background: #1a6b5c !important;
    color: #fff !important;
    fill: #fff !important;
}
.flatpickr-current-month .flatpickr-monthDropdown-months {
    background: rgba(255, 255, 255, 0.95) !important;
    color: #14532d !important;
    font-weight: 600;
    border-radius: 4px;
}
.flatpickr-current-month input.cur-year {
    background: rgba(255, 255, 255, 0.15) !important;
    color: #fff !important;
    font-weight: 600;
    border-radius: 4px;
}
.flatpickr-months .flatpickr-prev-month svg,
.flatpickr-months .flatpickr-next-month svg { fill: #fff; }
.flatpickr-weekdays {
    background: #f0fdf4;
    border-bottom: 1px solid #d1fae5;
}
span.flatpickr-weekday {
    color: #166534;
    font-weight: 600;
    font-size: 0.72rem;
}
.flatpickr-day.selected,
.flatpickr-day.startRange,
.flatpickr-day.endRange {
    background: #00a651 !important;
    border-color: #00a651 !important;
    color: #fff !important;
}
.flatpickr-day.today {
    border-color: #0d9488;
    color: #0f766e;
    font-weight: 700;
}
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<div class="flex gap-6 w-full pb-6">
    <aside class="hidden lg:block w-72 shrink-0"><?php require __DIR__ . '/sidebar.php'; ?></aside>
    <main class="flex-1 min-w-0 space-y-2 max-w-5xl">
        <?php $breadcrumbCurrent = 'Book service'; require __DIR__ . '/../../includes/breadcrumb.php'; ?>
        <?php if ($flashErr !== ''): ?><div class="alert alert-error"><span><?= h($flashErr) ?></span></div><?php endif; ?>

        <div class="book-sel">
            <div class="book-sel__head">
                <h2 class="book-sel__title">Your selected</h2>
                <a href="index.php?page=file" class="link link-primary font-medium text-xs">Edit</a>
            </div>
            <div class="book-sel__grid">
                <div class="book-sel__row"><strong>From country :</strong><span><?= h($c['from_country']) ?></span></div>
                <div class="book-sel__row"><strong>Service date :</strong><span><?= h($c['service_date']) ?> <strong>( <?= h($c['service_cat']) ?> )</strong></span></div>
                <div class="book-sel__row"><strong>Pick up :</strong><span><?= h($c['from_city']) ?></span></div>
                <div class="book-sel__row"><strong>Location / zone :</strong><span><?= h($c['from_location']) ?><?= $c['from_zone'] !== '' ? ' / ' . h($c['from_zone']) : '' ?></span></div>
                <div class="book-sel__row"><strong>Drop off :</strong><span><?= h($c['to_city']) ?></span></div>
                <div class="book-sel__row"><strong>Location / zone :</strong><span><?= h($c['to_location']) ?><?= $c['to_zone'] !== '' ? ' / ' . h($c['to_zone']) : '' ?></span></div>
                <div class="book-sel__row"><strong>No of adults :</strong><span><?= h($c['adults']) ?></span></div>
                <div class="book-sel__row"><strong>No of children :</strong><span><?= h($c['children']) ?></span></div>
                <div class="book-sel__row"><strong>Total pax :</strong><span><?= h($c['no_of_pax']) ?></span></div>
                <?php if ($vehLabel !== ''): ?>
                    <div class="book-sel__row"><strong>Vehicle :</strong><span><?= h($vehLabel) ?></span></div>
                <?php endif; ?>
                <div class="book-sel__row book-sel__row--full"><strong>Service name :</strong><span><?= h((string) ($service['service_name_english'] ?? '')) ?></span></div>
            </div>
            <div class="book-sel__price" aria-live="polite">Total <span class="tabular-nums">RM <?= h($priceShow) ?></span> <span class="font-normal text-xs opacity-80">(for this search)</span></div>
        </div>

        <form method="post" action="index.php?page=file_book&amp;service_id=<?= $serviceId ?>" class="book-form--compact space-y-1">
            <input type="hidden" name="_token" value="<?= h($csrf) ?>">
            <input type="hidden" name="file_add_basket" value="1">
            <input type="hidden" name="file_count_no" value="<?= h($fv['file_count_no']) ?>">
            <input type="hidden" name="selling_price" value="<?= h($fv['selling_price']) ?>">
            <input type="hidden" name="buying_price" value="<?= h($fv['buying_price']) ?>">
            <input type="hidden" name="no_of_pax" value="<?= h($c['no_of_pax']) ?>">

            <div class="book-card">
                <div class="book-card__body">
                    <div class="book-row book-row--center">
                        <span class="book-inline-lbl">File number :</span>
                        <input type="text" name="file_no" class="input input-bordered input-sm bg-white flex-1 min-w-48 max-w-xl" value="<?= h($fv['file_no']) ?>">
                    </div>
                </div>
            </div>

            <div class="book-card">
                <div class="book-guest-head" aria-hidden="true">
                    <span></span>
                    <span>Title</span>
                    <span>Last name / surname</span>
                    <span>First name</span>
                </div>
                <div class="book-card__body">
                    <div class="book-guest-row">
                        <span class="book-inline-lbl tabular-nums">1.</span>
                        <select name="title" class="select select-bordered select-sm bg-white w-full" aria-label="Title">
                            <?php foreach (['Mr', 'Mrs', 'Miss'] as $t): ?>
                                <option value="<?= h($t) ?>" <?= $fv['title'] === $t ? 'selected' : '' ?>><?= h($t) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="last_name" class="input input-bordered input-sm bg-white w-full" value="<?= h($fv['last_name']) ?>" required aria-label="Last name">
                        <input type="text" name="first_name" class="input input-bordered input-sm bg-white w-full" value="<?= h($fv['first_name']) ?>" required aria-label="First name">
                    </div>
                    <div class="book-guest-row2">
                        <div class="book-stack">
                            <span class="book-stack__lbl">Guest mobile</span>
                            <input type="text" name="pax_mobile" class="input input-bordered input-sm bg-white w-full" value="<?= h($fv['pax_mobile']) ?>" inputmode="tel" autocomplete="tel">
                        </div>
                        <div class="book-stack">
                            <span class="book-stack__lbl">Ref no</span>
                            <input type="text" name="ref_no" class="input input-bordered input-sm bg-white w-full" value="<?= h($fv['ref_no']) ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="book-card">
                <p class="book-card__head">Timing &amp; flight details</p>
                <div class="book-card__body space-y-1">
                    <div class="book-timing-row">
                        <span class="book-inline-lbl--muted">Flight details</span>
                        <div class="book-stack">
                            <span class="book-stack__lbl">Service date</span>
                            <div class="file-ts-dob-wrap">
                                <input type="text" name="service_date" id="fb-service-date" class="file-ts-dob-input" placeholder="dd-mm-yyyy" value="<?= h($fv['service_date']) ?>" required autocomplete="off" inputmode="numeric">
                                <button type="button" class="file-ts-dob-cal-btn" id="fb-service-date-cal" title="Open calendar" aria-label="Open calendar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="book-stack">
                            <span class="book-stack__lbl">Flight time</span>
                            <div class="flex gap-0.5 items-center">
                                <select name="fhr" class="select select-bordered select-sm bg-white w-time" aria-label="Flight hour"><?php for ($h = 0; $h <= 23; $h++): ?>
                                    <option value="<?= $h ?>" <?= (string) $h === $fv['fhr'] ? 'selected' : '' ?>><?= $h ?></option><?php endfor; ?></select>
                                <select name="fmin" class="select select-bordered select-sm bg-white w-time" aria-label="Flight minute"><?php for ($m = 0; $m <= 59; $m++): ?>
                                    <option value="<?= $m ?>" <?= (string) $m === $fv['fmin'] ? 'selected' : '' ?>><?= str_pad((string) $m, 2, '0', STR_PAD_LEFT) ?></option><?php endfor; ?></select>
                            </div>
                        </div>
                        <div class="book-stack">
                            <span class="book-stack__lbl">Flight no</span>
                            <input type="text" name="flight_no" class="input input-bordered input-sm bg-white w-24 max-w-28" value="<?= h($fv['flight_no']) ?>">
                        </div>
                        <div class="book-stack">
                            <span class="book-stack__lbl">Pickup time</span>
                            <div class="flex gap-0.5 items-center">
                                <select name="phr" class="select select-bordered select-sm bg-white w-time" aria-label="Pickup hour"><?php for ($h = 0; $h <= 23; $h++): ?>
                                    <option value="<?= $h ?>" <?= (string) $h === $fv['phr'] ? 'selected' : '' ?>><?= $h ?></option><?php endfor; ?></select>
                                <select name="pmin" class="select select-bordered select-sm bg-white w-time" aria-label="Pickup minute"><?php for ($m = 0; $m <= 59; $m++): ?>
                                    <option value="<?= $m ?>" <?= (string) $m === $fv['pmin'] ? 'selected' : '' ?>><?= str_pad((string) $m, 2, '0', STR_PAD_LEFT) ?></option><?php endfor; ?></select>
                            </div>
                        </div>
                    </div>
                    <div class="book-row book-row--pick">
                        <span class="book-inline-lbl">Pick up from :</span>
                        <input type="text" name="pickup_from" class="input input-bordered input-sm bg-white flex-1 min-w-40" value="<?= h($fv['pickup_from']) ?>">
                    </div>
                    <div class="book-row book-row--pick">
                        <span class="book-inline-lbl">Drop off at :</span>
                        <input type="text" name="drop_off" class="input input-bordered input-sm bg-white flex-1 min-w-40" value="<?= h($fv['drop_off']) ?>">
                    </div>
                </div>
            </div>

            <div class="book-card">
                <p class="book-card__head">Supplier details</p>
                <div class="book-card__body space-y-1">
                    <div class="book-row book-row--pick flex-wrap">
                        <span class="book-inline-lbl">Supplier name :</span>
                        <select name="supplier_name" class="select select-bordered select-sm bg-white flex-1 min-w-40 max-w-md" required>
                            <option value="">Select supplier</option>
                            <?php foreach ($suppliers as $s): ?>
                                <option value="<?= h($s) ?>" <?= $fv['supplier_name'] === $s ? 'selected' : '' ?>><?= h($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="book-inline-lbl">Driver name</span>
                        <select name="driver_name" class="select select-bordered select-sm bg-white flex-1 min-w-40 max-w-md">
                            <option value="">Select driver</option>
                            <?php foreach ($drivers as $d): ?>
                                <option value="<?= h($d['driver_name']) ?>" <?= $fv['driver_name'] === $d['driver_name'] ? 'selected' : '' ?>><?= h($d['driver_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="book-row book-row--pick items-start">
                        <span class="book-inline-lbl pt-1">Remark :</span>
                        <textarea name="remarks" class="textarea textarea-bordered textarea-sm bg-white flex-1 min-h-11" rows="2" placeholder="Remarks"><?= h($fv['remarks']) ?></textarea>
                    </div>
                </div>
            </div>

            <div class="book-actions">
                <a href="index.php?page=file" class="btn btn-ghost btn-sm order-2 md:order-1">Back to search</a>
                <button type="submit" class="btn btn-success btn-sm text-white order-1 md:order-2">Add to basket</button>
            </div>
        </form>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
(function () {
    var dateInp = document.getElementById('fb-service-date');
    var dateCalBtn = document.getElementById('fb-service-date-cal');
    if (typeof flatpickr === 'function' && dateInp) {
        var fp = flatpickr(dateInp, {
            dateFormat: 'd-m-Y',
            allowInput: true,
            clickOpens: true,
            animate: true
        });
        if (dateCalBtn && fp) {
            dateCalBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                fp.open();
            });
        }
    }
})();
</script>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
