<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/file_module_service.php';

$currentPage = 'file_book';

if (!file_module_has_agent()) {
    file_module_render_agent_required();
}

$serviceId = (int) ($_GET['service_id'] ?? 0);
$service = $serviceId > 0 ? file_module_service_by_id($mysqli, $serviceId) : null;

require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';

$csrf = file_module_csrf_token();
$state = file_module_state();
$c = $state['criteria'];
$g = $state['guest'];
$flashErr = '';

if ($service === null) {
    echo '<div class="flex gap-6 w-full pb-6"><aside class="hidden lg:block w-72 shrink-0">';
    require __DIR__ . '/sidebar.php';
    echo '</aside><main class="flex-1"><div class="alert alert-warning">Invalid service.</div><a class="btn btn-sm" href="index.php?page=file">Back</a></div></div>';
    require __DIR__ . '/../../includes/footer.php';
    exit;
}

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

$fcnCur = $state['file_count_no'] ?? '';
$dmyShow = $c['service_date'];
?>

<div class="flex gap-6 w-full pb-6">
    <aside class="hidden lg:block w-72 shrink-0"><?php require __DIR__ . '/sidebar.php'; ?></aside>
    <main class="flex-1 min-w-0 space-y-4">
        <?php $breadcrumbCurrent = 'Book service'; require __DIR__ . '/../../includes/breadcrumb.php'; ?>
        <?php if ($flashErr !== ''): ?><div class="alert alert-error"><span><?= h($flashErr) ?></span></div><?php endif; ?>

        <div class="rounded-sm border border-base-300 bg-[#ffffee] p-4 text-sm max-w-3xl">
            <h2 class="text-success font-semibold mb-2">Your selected</h2>
            <div class="grid sm:grid-cols-2 gap-2">
                <div><strong>From country:</strong> <?= h($c['from_country']) ?></div>
                <div><strong>Service date:</strong> <?= h($c['service_date']) ?> (<?= h($c['service_cat']) ?>)</div>
                <div><strong>Pick up:</strong> <?= h($c['from_city']) ?></div>
                <div><strong>Location / zone:</strong> <?= h($c['from_location']) ?><?= $c['from_zone'] !== '' ? ' / ' . h($c['from_zone']) : '' ?></div>
                <div><strong>Drop off:</strong> <?= h($c['to_city']) ?></div>
                <div><strong>Location / zone:</strong> <?= h($c['to_location']) ?><?= $c['to_zone'] !== '' ? ' / ' . h($c['to_zone']) : '' ?></div>
                <div><strong>Adults / children / pax:</strong> <?= h($c['adults']) ?> / <?= h($c['children']) ?> / <?= h($c['no_of_pax']) ?></div>
                <div><strong>Service:</strong> <?= h((string) ($service['service_name_english'] ?? '')) ?></div>
            </div>
        </div>

        <form method="post" action="index.php?page=file_book&amp;service_id=<?= $serviceId ?>" class="space-y-4 max-w-3xl">
            <input type="hidden" name="_token" value="<?= h($csrf) ?>">
            <input type="hidden" name="file_add_basket" value="1">
            <input type="hidden" name="file_count_no" value="<?= h((string) $fcnCur) ?>">
            <input type="hidden" name="selling_price" value="<?= h($prices['selling']) ?>">
            <input type="hidden" name="buying_price" value="<?= h($prices['buying']) ?>">
            <input type="hidden" name="no_of_pax" value="<?= h($c['no_of_pax']) ?>">

            <label class="form-control"><span class="label-text">File number</span>
                <input type="text" name="file_no" class="input input-bordered input-sm bg-white" value="<?= h($state['file_no']) ?>"></label>

            <div class="bg-success text-success-content text-sm font-semibold px-2 py-1 rounded">Guest</div>
            <div class="flex flex-wrap gap-2 items-end">
                <select name="title" class="select select-bordered select-sm bg-white">
                    <?php foreach (['Mr', 'Mrs', 'Miss'] as $t): ?>
                        <option value="<?= h($t) ?>" <?= $g['title'] === $t ? 'selected' : '' ?>><?= h($t) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="last_name" placeholder="Last name" class="input input-bordered input-sm bg-white flex-1 min-w-[8rem]" value="<?= h($g['last_name']) ?>" required>
                <input type="text" name="first_name" placeholder="First name" class="input input-bordered input-sm bg-white flex-1 min-w-[8rem]" value="<?= h($g['first_name']) ?>" required>
            </div>
            <div class="flex flex-wrap gap-2">
                <input type="text" name="pax_mobile" placeholder="Guest mobile" class="input input-bordered input-sm bg-white" value="<?= h($g['pax_mobile']) ?>">
                <input type="text" name="ref_no" placeholder="Ref no" class="input input-bordered input-sm bg-white" value="<?= h($g['ref_no']) ?>">
            </div>

            <div class="bg-success text-success-content text-sm font-semibold px-2 py-1 rounded">Timing &amp; flight</div>
            <div class="flex flex-wrap gap-2 items-end">
                <label class="form-control"><span class="label-text text-xs">Service date</span>
                    <input type="text" name="service_date" class="input input-bordered input-sm bg-white" value="<?= h($dmyShow) ?>" required></label>
                <div class="flex gap-1 items-end"><span class="text-xs">Flight</span>
                    <select name="fhr" class="select select-bordered select-sm bg-white"><?php for ($h = 0; $h <= 23; $h++): ?><option value="<?= $h ?>"><?= $h ?></option><?php endfor; ?></select>
                    <select name="fmin" class="select select-bordered select-sm bg-white"><?php for ($m = 0; $m <= 59; $m++): ?><option value="<?= $m ?>"><?= $m ?></option><?php endfor; ?></select>
                </div>
                <input type="text" name="flight_no" class="input input-bordered input-sm bg-white w-28" placeholder="Flight no">
                <div class="flex gap-1 items-end"><span class="text-xs">Pickup</span>
                    <select name="phr" class="select select-bordered select-sm bg-white"><?php for ($h = 0; $h <= 23; $h++): ?><option value="<?= $h ?>"><?= $h ?></option><?php endfor; ?></select>
                    <select name="pmin" class="select select-bordered select-sm bg-white"><?php for ($m = 0; $m <= 59; $m++): ?><option value="<?= $m ?>"><?= $m ?></option><?php endfor; ?></select>
                </div>
            </div>
            <?php
            $pickDef = $c['from_zone'] !== '' ? $c['from_zone'] : $c['from_location'];
            $dropDef = $c['to_zone'] !== '' ? $c['to_zone'] : $c['to_location'];
            ?>
            <input type="text" name="pickup_from" class="input input-bordered input-sm bg-white w-full max-w-xl" value="<?= h($pickDef) ?>">
            <input type="text" name="drop_off" class="input input-bordered input-sm bg-white w-full max-w-xl" value="<?= h($dropDef) ?>">

            <div class="bg-success text-success-content text-sm font-semibold px-2 py-1 rounded">Supplier</div>
            <div class="flex flex-wrap gap-2">
                <select name="supplier_name" class="select select-bordered select-sm bg-white min-w-[12rem]" required>
                    <option value="">Select supplier</option>
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?= h($s) ?>"><?= h($s) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="driver_name" class="select select-bordered select-sm bg-white min-w-[12rem]">
                    <option value="">Select driver</option>
                    <?php foreach ($drivers as $d): ?>
                        <option value="<?= h($d['driver_name']) ?>"><?= h($d['driver_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <textarea name="remarks" class="textarea textarea-bordered textarea-sm bg-white w-full" rows="3" placeholder="Remarks"></textarea>

            <button type="submit" class="btn btn-success btn-sm text-white">Add to basket</button>
            <a href="index.php?page=file" class="btn btn-ghost btn-sm">Back to search</a>
        </form>
    </main>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
