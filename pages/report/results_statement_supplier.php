<?php

declare(strict_types=1);

/**
 * @var array{supplier_name: string, rows: list<array<string, string>>, totals: array{total: string, paid: string, balance: string}} $stmtData
 */
$supplier = $stmtData['supplier_name'];
$rows = $stmtData['rows'];
$totals = $stmtData['totals'];
?>
<div class="overflow-x-auto">
    <table class="table table-xs table-bordered w-full min-w-[900px] text-xs whitespace-nowrap">
        <thead>
        <tr>
            <th colspan="10" class="text-center text-sm">CREDITOR INVOICE COSTING REPORT</th>
        </tr>
        </thead>
        <tbody>
        <tr><td colspan="10">&nbsp;</td></tr>
        <tr>
            <td colspan="2"><strong>Transfer</strong></td>
            <td colspan="5"><?= h($supplier) ?></td>
            <td colspan="2"><strong>Invoice Status</strong></td>
            <td><strong>ACTIVE</strong></td>
        </tr>
        <tr>
            <td colspan="2"><strong>Account Status</strong></td>
            <td colspan="8">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2"><strong>Source Type</strong></td>
            <td colspan="8">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2"><strong>Currency</strong></td>
            <td colspan="8">Ringgit Malaysia</td>
        </tr>
        </tbody>
        <thead>
        <tr class="bg-base-200">
            <th>No.</th>
            <th>Doc ID.</th>
            <th>Issued Date</th>
            <th>O/S Ref.</th>
            <th>Agent</th>
            <th>Service Date</th>
            <th>Inv # No.</th>
            <th>Net Total</th>
            <th>Paid Amt.</th>
            <th>Balance</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r) { ?>
            <tr>
                <td><?= h($r['no']) ?></td>
                <td><?= h($r['doc_id']) ?></td>
                <td><?= h($r['issued_date']) ?></td>
                <td><?= h($r['ref_no']) ?></td>
                <td><?= h($r['agent']) ?></td>
                <td><?= h($r['service_date']) ?></td>
                <td><?= h($r['inv_no']) ?></td>
                <td><?= h($r['net_total']) ?></td>
                <td><?= h($r['paid_amt']) ?></td>
                <td><?= h($r['balance']) ?></td>
            </tr>
        <?php } ?>
        <tr>
            <td colspan="7" class="text-right font-semibold">Total</td>
            <td><?= h($totals['total']) ?></td>
            <td><?= h($totals['paid']) ?></td>
            <td><?= h($totals['balance']) ?></td>
        </tr>
        </tbody>
    </table>
</div>
