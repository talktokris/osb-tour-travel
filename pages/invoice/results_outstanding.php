<?php
declare(strict_types=1);
/** @var list<array<string,string>> $rows */
/** @var array<string,string> $context */
?>
<form method="post" action="index.php?page=invoice&mode=pay_multiple" class="space-y-3">
  <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
  <input type="hidden" name="invoice_submit" value="1">
  <input type="hidden" name="mode" value="pay_multiple">
  <input type="hidden" name="search_agent" value="<?= h($context['search_agent'] ?? '') ?>">
  <input type="hidden" name="search_supplier" value="<?= h($context['search_supplier'] ?? '') ?>">
  <input type="hidden" name="search_ref" value="<?= h($context['search_ref'] ?? '') ?>">
  <input type="hidden" name="from_date" value="<?= h($context['from_date'] ?? '') ?>">
  <input type="hidden" name="to_date" value="<?= h($context['to_date'] ?? '') ?>">
  <input type="hidden" name="return_mode" value="<?= h((string) $mode) ?>">
  <div><button type="submit" class="btn btn-success btn-sm">Pay Multiple</button></div>
  <?php if ($rows===[]): ?><p class="text-sm text-base-content/70">No Result found</p><?php else: ?>
  <div class="overflow-x-auto">
    <table class="table table-xs table-bordered w-full min-w-[1100px] text-xs whitespace-nowrap">
      <thead><tr><th></th><th>Invoice No</th><th>Invoice Date</th><th>Company Name</th><th>Invoice Type</th><th>File No</th><th>Ref No</th><th>Total Amount</th><th>Balance Amount</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
      <?php foreach($rows as $r): ?>
        <tr>
          <td><input type="checkbox" name="selected_ids[]" value="<?= h($r['invoices_id']) ?>"></td>
          <td><?= h($r['invoices_id']) ?></td>
          <td><?= h($r['invoice_create_date']) ?></td>
          <td>
            <?php if (($r['invoice_type'] ?? '') === 'Supplier Invoice'): ?>
              <a class="link" target="_blank" href="index.php?page=invoice_pdf_supplier_converter&file_count_no=<?= h(rawurlencode((string) ($r['file_count_no'] . '|' . $r['agent_supplier_name']))) ?>"><?= h($r['agent_supplier_name']) ?></a>
            <?php else: ?>
              <a class="link" target="_blank" href="index.php?page=invoice_pdf_converter&file_count_no=<?= h(rawurlencode($r['file_count_no'])) ?>"><?= h($r['agent_supplier_name']) ?></a>
            <?php endif; ?>
          </td>
          <td><?= h($r['invoice_type']) ?></td>
          <td><?= h($r['file_no']) ?></td>
          <td><?= h($r['ref_no']) ?></td>
          <td><?= h($r['total_price']) ?></td>
          <td><?= h($r['balance_amount']) ?></td>
          <td><?= h($r['paid_status']) ?></td>
          <td><a class="link link-error" href="index.php?page=invoice&mode=pay_single&invoice_id=<?= h(rawurlencode($r['invoices_id'])) ?>&search_agent=<?= h(rawurlencode($context['search_agent'] ?? '')) ?>&search_supplier=<?= h(rawurlencode($context['search_supplier'] ?? '')) ?>&search_ref=<?= h(rawurlencode($context['search_ref'] ?? '')) ?>&from_date=<?= h(rawurlencode($context['from_date'] ?? '')) ?>&to_date=<?= h(rawurlencode($context['to_date'] ?? '')) ?>&return_mode=<?= h(rawurlencode((string)$mode)) ?>">Pay</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</form>
