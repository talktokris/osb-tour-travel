<?php
declare(strict_types=1);
/** @var list<array<string,string>> $rows */
$legacyPdfBase = 'http://localhost:8080/login/super/file/tcpdf/examples';
?>
<?php if ($rows===[]): ?><p class="text-sm text-base-content/70">No Result found</p><?php else: ?>
<div class="overflow-x-auto">
  <table class="table table-xs table-bordered w-full min-w-[900px] text-xs whitespace-nowrap">
    <thead><tr><th>Invoice No</th><th>Company Name</th><th>Company Type</th><th>Total Amount</th><th>Created Date</th><th>File No</th><th>Ref No</th><th>Status</th><th>Payments</th></tr></thead>
    <tbody>
    <?php foreach($rows as $r): $p=invoice_module_payment_rows($mysqli, (string)($r['invoices_id'] ?? '')); ?>
      <tr>
        <td><?= h($r['invoices_id']) ?></td><td><?php if (($r['invoice_type'] ?? '') === 'Supplier Invoice'): ?><a class="link js-invoice-pdf" href="<?= h($legacyPdfBase . '/invoice_pdf_supplier.php?file_count_no=' . rawurlencode((string) ($r['file_count_no'] . '|' . $r['agent_supplier_name']))) ?>"><?= h($r['agent_supplier_name']) ?></a><?php else: ?><a class="link js-invoice-pdf" href="<?= h($legacyPdfBase . '/invoice_pdf_converter.php?file_count_no=' . rawurlencode((string) $r['file_count_no'])) ?>"><?= h($r['agent_supplier_name']) ?></a><?php endif; ?></td><td><?= h($r['invoice_type']) ?></td><td><?= h($r['total_price']) ?></td><td><?= h($r['invoice_create_date']) ?></td><td><?= h($r['file_no']) ?></td><td><?= h($r['ref_no']) ?></td><td><?= h($r['paid_status']) ?></td>
        <td>
          <?php if ($p===[]): ?>-<?php else: ?>
            <table class="table table-xs"><thead><tr><th>Paid Date</th><th>Cheque No</th><th>Paid Amount(RM)</th></tr></thead><tbody>
            <?php foreach($p as $x): ?><tr><td><?= h($x['paid_date']) ?></td><td><?= h($x['cheque_no']) ?></td><td><?= h($x['paid_amount']) ?></td></tr><?php endforeach; ?>
            </tbody></table>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
