<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/file_module_service.php';
require_once __DIR__ . '/../../includes/driver_module_service.php';

$currentPage = 'driver_assign';
$userEnterBy = trim((string) ($_SESSION['user_name'] ?? ''));
$fileId = (int) ($_GET['file_id'] ?? $_POST['file_id'] ?? 0);
$csrf = file_module_csrf_token();
$flash = file_module_flash_get();

$row = driver_module_get_file_for_assign($mysqli, $fileId, $userEnterBy);
if ($row === null) {
    http_response_code(404);
    $currentPage = 'driver_assign';
    require __DIR__ . '/../../includes/header.php';
    require __DIR__ . '/../../includes/nav.php';
    echo '<div class="px-4"><p class="text-error">Job not found or access denied.</p>';
    echo '<a class="link link-primary" href="index.php?page=driver">Back to Driver</a></div>';
    require __DIR__ . '/../../includes/footer.php';
    exit;
}

$vehicles = driver_module_vehicle_numbers($mysqli);
$drivers = file_module_drivers($mysqli);

if (($_SERVER['REQUEST_METHOD'] === 'POST') && (($_POST['driver_assign_submit'] ?? '') === '1')) {
    $fv = $_POST;
    $tok = trim((string) ($fv['csrf'] ?? ''));
    if (!file_module_csrf_validate($tok)) {
        file_module_flash_set('error', 'Invalid security token.');
        header('Location: index.php?page=driver_assign&file_id=' . $fileId);
        exit;
    }
    $vehicleNo = trim((string) ($fv['vehicles_no'] ?? ''));
    $driverName = trim((string) ($fv['driver_name'] ?? ''));
    $res = driver_module_assign_post($mysqli, $fileId, $userEnterBy, $vehicleNo, $driverName);
    if ($res['ok']) {
        header('Location: index.php?page=driver_notification&dName=' . rawurlencode($driverName));
        exit;
    }
    file_module_flash_set('error', (string) ($res['error'] ?? 'Save failed.'));
    header('Location: index.php?page=driver_assign&file_id=' . $fileId);
    exit;
}

$pd = driver_module_pickup_drop($row);
$serviceDateDmy = file_module_format_date_ymd_to_dmy((string) ($row['service_date'] ?? ''));
$curVehicle = trim((string) ($row['vehicle_no'] ?? ''));
$curDriver = trim((string) ($row['driver_name'] ?? ''));

require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';
?>

<div class="flex gap-6 w-full">
    <aside class="hidden lg:block w-72 shrink-0">
        <?php
        $driverSub = '';
        require __DIR__ . '/sidebar.php';
        ?>
    </aside>

    <main class="flex-1 min-w-0 px-4">
        <div class="space-y-4 max-w-2xl">
            <?php $breadcrumbCurrent = 'Driver — Assign job';
            require __DIR__ . '/../../includes/breadcrumb.php'; ?>

            <?php if ($flash !== null): ?>
                <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-warning' ?> text-sm">
                    <?= h((string) $flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body space-y-4">
                    <form method="post" action="index.php?page=driver_assign&amp;file_id=<?= $fileId ?>" class="space-y-4">
                        <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                        <input type="hidden" name="file_id" value="<?= $fileId ?>">
                        <input type="hidden" name="driver_assign_submit" value="1">

                        <div class="grid sm:grid-cols-2 gap-4 items-end">
                            <label class="form-control w-full">
                                <span class="label-text text-xs font-semibold">Vehicles No.</span>
                                <select name="vehicles_no" class="select select-bordered select-sm w-full">
                                    <?php if ($curVehicle === '' && $vehicles === []): ?>
                                        <option value="">— No vehicles in setup —</option>
                                    <?php endif; ?>
                                    <?php if ($curVehicle !== ''): ?>
                                        <option value="<?= h($curVehicle) ?>" selected><?= h($curVehicle) ?></option>
                                    <?php endif; ?>
                                    <?php foreach ($vehicles as $vn):
                                        if ($vn === $curVehicle) {
                                            continue;
                                        } ?>
                                        <option value="<?= h($vn) ?>"><?= h($vn) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label class="form-control w-full">
                                <span class="label-text text-xs font-semibold">Driver Name</span>
                                <select name="driver_name" class="select select-bordered select-sm w-full">
                                    <?php if ($curDriver !== ''): ?>
                                        <option value="<?= h($curDriver) ?>" selected><?= h($curDriver) ?></option>
                                    <?php endif; ?>
                                    <?php foreach ($drivers as $d):
                                        $dn = $d['driver_name'];
                                        if ($dn === $curDriver || $dn === '') {
                                            continue;
                                        } ?>
                                        <option value="<?= h($dn) ?>"><?= h($dn) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="btn btn-success btn-sm text-white">Action</button>
                        </div>
                    </form>

                    <div class="divider"></div>
                    <h4 class="font-semibold text-sm">Job details</h4>
                    <table class="table table-sm">
                        <tbody class="text-sm">
                        <tr><th class="w-40 text-right">Ref No.</th><td><?= h((string) ($row['ref_no'] ?? '')) ?></td></tr>
                        <tr><th class="text-right">File No.</th><td><?= h((string) ($row['file_no'] ?? '')) ?></td></tr>
                        <tr><th class="text-right">Agent Name</th><td><?= h((string) ($row['agent_name'] ?? '')) ?></td></tr>
                        <tr><th class="text-right">Supplier</th><td><?= h((string) ($row['supplier_name'] ?? '')) ?></td></tr>
                        <tr><th class="text-right">Guest Name</th><td><?= h(trim((string) ($row['last_name'] ?? '') . ' ' . (string) ($row['first_name'] ?? ''))) ?></td></tr>
                        <tr><th class="text-right">Service Date</th><td><?= h($serviceDateDmy) ?></td></tr>
                        <tr><th class="text-right">Service Name</th><td><?= h((string) ($row['service'] ?? '')) ?></td></tr>
                        <tr><th class="text-right">Pick up</th><td><?= h($pd['pickup']) ?></td></tr>
                        <tr><th class="text-right">Drop off</th><td><?= h($pd['drop']) ?></td></tr>
                        <tr><th class="text-right">Pickup Time</th><td><?= h(driver_module_format_hm((string) ($row['pickup_time'] ?? ''))) ?></td></tr>
                        <tr><th class="text-right">Flight No.</th><td><?= h((string) ($row['flight_no'] ?? '')) ?></td></tr>
                        <tr><th class="text-right">Flight Time</th><td><?= h(driver_module_format_hm((string) ($row['flight_time'] ?? ''))) ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
