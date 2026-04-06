<?php

declare(strict_types=1);

/**
 * @var array{agent_name: string, agent_address: string, rows: list<array<string, string>>, totals: array{total: string, paid: string, balance: string}} $stmtData
 */
$agent = $stmtData['agent_name'];
$addr = $stmtData['agent_address'];
$rows = $stmtData['rows'];
$totals = $stmtData['totals'];
?>
<div class="overflow-x-auto">
    <table class="table table-xs table-bordered w-full min-w-[1100px] text-xs whitespace-nowrap">
        <thead>
        <tr>
            <th colspan="17" class="text-center text-sm">STATEMENT REPORT</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td colspan="3"><strong>Agent :</strong></td>
            <td colspan="14"><?= h($agent) ?></td>
        </tr>
        <tr>
            <td colspan="3"><strong>Address :</strong></td>
            <td colspan="14"><?= h($addr) ?></td>
        </tr>
        <tr>
            <td colspan="3"><strong>Currency :</strong></td>
            <td colspan="11">Ringgit Malaysia</td>
            <td>Type</td>
            <td>INVOICE</td>
        </tr>
        </tbody>
        <thead>
        <tr class="bg-base-200">
            <th>No.</th>
            <th>Invoice No.</th>
            <th>Issue Date</th>
            <th>Due Date</th>
            <th>Service Date</th>
            <th>Guest Name</th>
            <th>Description</th>
            <th>Qty</th>
            <th>Type</th>
            <th>Price</th>
            <th>Item Amount</th>
            <th>Total Invoice</th>
            <th>Paid</th>
            <th>Balance</th>
            <th>Acc. Status</th>
            <th>Status</th>
            <th>UserCreate</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $num = 1;
        foreach ($rows as $r) { ?>
            <tr>
                <td><?= (string) $num++ ?></td>
                <td><?= h($r['invoices_id']) ?></td>
                <td><?= h($r['invoice_create_date']) ?></td>
                <td></td>
                <td><?= h($r['service_date']) ?></td>
                <td><?= h($r['guest']) ?></td>
                <td><?= h($r['description']) ?></td>
                <td><?= h($r['qty']) ?></td>
                <td><?= h($r['type']) ?></td>
                <td><?= h($r['unit_price']) ?></td>
                <td><?= h($r['selling_price']) ?></td>
                <td><?= h($r['item_amount']) ?></td>
                <td><?= h($r['paid']) ?></td>
                <td><?= h($r['balance']) ?></td>
                <td><?= h($r['acc_status']) ?></td>
                <td><?= h($r['status']) ?></td>
                <td><?= h($r['user_create']) ?></td>
            </tr>
        <?php } ?>
        <tr>
            <td colspan="10" class="text-right font-semibold">Total</td>
            <td></td>
            <td><?= h($totals['total']) ?></td>
            <td><?= h($totals['paid']) ?></td>
            <td><?= h($totals['balance']) ?></td>
            <td colspan="3"></td>
        </tr>
        </tbody>
    </table>
</div>
