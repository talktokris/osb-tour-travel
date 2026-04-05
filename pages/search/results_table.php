<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/search_module_service.php';

/**
 * @var string $variant
 * @var list<array<string,mixed>> $rows
 * @var ?list<array{file_count_no:string,header:array<string,mixed>,lines:list<array<string,mixed>>}> $groups
 * @var string $csrf
 * @var string $redirect
 */

$previewBase = 'index.php?page=file_preview&file_count_no=';
$vehicleCell = static fn (array $r): string => (string) ($r['vehicle_no'] ?? '');

if ($variant === 'nested' && $groups !== null && $groups !== []) {
    foreach ($groups as $g) {
        $fcn = (string) $g['file_count_no'];
        $h = $g['header'];
        $agentName = h((string) ($h['agent_name'] ?? ''));
        $fileNo = h((string) ($h['file_no'] ?? ''));
        $sd = h(search_module_row_service_date_dmy($h));
        $cd = h((string) ($h['date'] ?? ''));
        ?>
        <div class="overflow-x-auto border border-base-300 rounded-lg mb-6 bg-base-100">
            <table class="table table-sm table-zebra w-full text-xs md:text-sm">
                <thead>
                <tr class="bg-base-200">
                    <th></th>
                    <th>Agent Name</th>
                    <th>File No</th>
                    <th>Service Date</th>
                    <th>Created Date</th>
                    <th>View</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td></td>
                    <td class="font-semibold"><?= $agentName ?></td>
                    <td class="font-semibold"><?= $fileNo ?></td>
                    <td class="font-semibold"><?= $sd ?></td>
                    <td class="font-semibold"><?= $cd ?></td>
                    <td>
                        <a class="btn btn-xs btn-info" href="<?= h($previewBase . rawurlencode($fcn)) ?>">View</a>
                    </td>
                </tr>
                <tr class="bg-base-200/80">
                    <th>Services No</th>
                    <th colspan="2">Service Name</th>
                    <th colspan="2">Guest Name</th>
                    <th>Del</th>
                </tr>
                <?php foreach ($g['lines'] as $line) {
                    $fid = (string) ($line['file_id'] ?? '');
                    $svc = h((string) ($line['service'] ?? ''));
                    $guest = h(trim((string) ($line['first_name'] ?? '') . ' ' . (string) ($line['last_name'] ?? '')));
                    ?>
                    <tr>
                        <td><?= h($fid) ?></td>
                        <td colspan="2"><?= $svc ?></td>
                        <td colspan="2"><?= $guest ?></td>
                        <td>
                            <button type="button" class="btn btn-xs btn-error btn-outline js-search-delete"
                                    data-file-id="<?= h($fid) ?>"
                                    data-label="<?= h($svc) ?>">Delete</button>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    require __DIR__ . '/delete_modal.php';

    return;
}

if ($rows === []) {
    return;
}

$lastColHeader = match ($variant) {
    'vehicle_type_last' => 'Vehicle Type',
    default => 'Service Type',
};

$lastColVal = static function (array $r) use ($variant): string {
    if ($variant === 'vehicle_type_last') {
        return (string) ($r['vehicle_type'] ?? '');
    }

    return (string) ($r['service_cat'] ?? '');
};

$useRoute = !in_array($variant, ['tours'], true);
$showVehicleTypeCol = $variant === 'combined';

?>
<div class="overflow-x-auto border border-base-300 rounded-lg bg-base-100">
    <table class="table table-sm table-zebra w-full text-xs md:text-sm whitespace-nowrap min-w-[960px]">
        <thead>
        <tr class="bg-base-200">
            <th></th>
            <th>File No</th>
            <th>Ref No</th>
            <th>Agent Name</th>
            <th>Supplier Name</th>
            <th>Service</th>
            <?php if ($variant === 'tours'): ?>
                <th>Guest Name</th>
                <th>Driver Name</th>
                <th>Vehicle No.</th>
                <th>Service Date</th>
                <th>Dep/Arv</th>
                <th>No. of Pax</th>
                <th>Vehicle Type</th>
            <?php else: ?>
                <th>Service Date</th>
                <th>Guest Name</th>
                <th>Driver Name</th>
                <?php if ($showVehicleTypeCol): ?>
                    <th>Vehicle Type</th>
                <?php endif; ?>
                <th>Vehicle No.</th>
                <th>Dep/Arv</th>
                <th>No. of Pax</th>
                <th><?= h($lastColHeader) ?></th>
            <?php endif; ?>
            <th>Del</th>
            <th>View</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r) {
            $fid = (string) ($r['file_id'] ?? '');
            $fcn = (string) ($r['file_count_no'] ?? '');
            $svcDisplay = $useRoute ? search_module_service_route($r) : (string) ($r['service'] ?? '');
            ?>
            <tr>
                <td><?= h($fid) ?></td>
                <td><?= h((string) ($r['file_no'] ?? '')) ?></td>
                <td><?= h((string) ($r['ref_no'] ?? '')) ?></td>
                <td><?= h((string) ($r['agent_name'] ?? '')) ?></td>
                <td><?= h((string) ($r['supplier_name'] ?? '')) ?></td>
                <td class="max-w-56 whitespace-normal"><?= h($svcDisplay) ?></td>
                <?php if ($variant === 'tours'): ?>
                    <td><?= h(trim((string) ($r['first_name'] ?? '') . ' ' . (string) ($r['last_name'] ?? ''))) ?></td>
                    <td><?= h((string) ($r['driver_name'] ?? '')) ?></td>
                    <td><?= h($vehicleCell($r)) ?></td>
                    <td><?= h(search_module_row_service_date_dmy($r)) ?></td>
                    <td><?= h((string) ($r['service_type'] ?? '')) ?></td>
                    <td><?= h((string) ($r['no_of_pax'] ?? '')) ?></td>
                    <td><?= h((string) ($r['vehicle_type'] ?? '')) ?></td>
                <?php else: ?>
                    <td><?= h(search_module_row_service_date_dmy($r)) ?></td>
                    <td><?= h(trim((string) ($r['first_name'] ?? '') . ' ' . (string) ($r['last_name'] ?? ''))) ?></td>
                    <td><?= h((string) ($r['driver_name'] ?? '')) ?></td>
                    <?php if ($showVehicleTypeCol): ?>
                        <td><?= h((string) ($r['vehicle_type'] ?? '')) ?></td>
                    <?php endif; ?>
                    <td><?= h($vehicleCell($r)) ?></td>
                    <td><?= h((string) ($r['service_type'] ?? '')) ?></td>
                    <td><?= h((string) ($r['no_of_pax'] ?? '')) ?></td>
                    <td><?= h($lastColVal($r)) ?></td>
                <?php endif; ?>
                <td>
                    <button type="button" class="btn btn-xs btn-error btn-outline js-search-delete"
                            data-file-id="<?= h($fid) ?>"
                            data-label="<?= h($svcDisplay) ?>">Delete</button>
                </td>
                <td>
                    <a class="btn btn-xs btn-info" href="<?= h($previewBase . rawurlencode($fcn)) ?>">View</a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/delete_modal.php'; ?>
