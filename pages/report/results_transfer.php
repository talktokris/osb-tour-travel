<?php

declare(strict_types=1);

/**
 * @var list<array{title: string, rows: list<array<string, string>>}> $sections
 */
if ($sections === []) {
    echo '<p class="text-sm text-base-content/70 py-4">No records in this date range.</p>';

    return;
}
$first = true;
foreach ($sections as $sec) {
    $title = $sec['title'];
    $rows = $sec['rows'];
    ?>
    <div class="mb-8 overflow-x-auto">
        <table class="table table-xs table-bordered w-full min-w-[1200px] text-xs whitespace-nowrap">
            <tbody>
            <tr>
                <td colspan="15" class="text-center font-bold text-base"><?= $first ? 'Report :' : '' ?></td>
            </tr>
            <tr>
                <td colspan="15" class="text-right font-bold text-base"><?= h($title) ?> Transfer:</td>
            </tr>
            </tbody>
            <thead>
            <tr class="bg-base-200">
                <th>Supplier Name</th>
                <th>Agent Name</th>
                <th>File No.</th>
                <th>Client Name</th>
                <th>Service Date</th>
                <th>Service Name</th>
                <th>Flight No.</th>
                <th>Flight Time</th>
                <th>Pick Up Time</th>
                <th>Pick Up</th>
                <th>Drop Off</th>
                <th>Vehicle Type</th>
                <th>Driver Name</th>
                <th>PAX SIM NO</th>
                <th>Tour type</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r) { ?>
                <tr>
                    <td><?= h($r['supplier_name']) ?></td>
                    <td><?= h($r['agent_name']) ?></td>
                    <td><?= h($r['file_no']) ?></td>
                    <td><?= h($r['client_name']) ?></td>
                    <td><?= h($r['service_date']) ?></td>
                    <td><?= h($r['service']) ?></td>
                    <td><?= h($r['flight_no']) ?></td>
                    <td><?= h($r['flight_time']) ?></td>
                    <td><?= h($r['pickup_time']) ?></td>
                    <td><?= h($r['pick_up']) ?></td>
                    <td><?= h($r['drop_off']) ?></td>
                    <td><?= h($r['vehicle_type']) ?></td>
                    <td><?= h($r['driver_name']) ?></td>
                    <td><?= h($r['pax_mobile']) ?></td>
                    <td><?= h($r['service_cat']) ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <?php
    $first = false;
}
