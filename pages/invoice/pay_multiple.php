<?php
declare(strict_types=1);
/** @var list<array<string,string>> $selectedRows */
?>
<form method="post" action="index.php?page=invoice&mode=pay_multiple" class="space-y-3">
  <input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="invoice_submit" value="1"><input type="hidden" name="mode" value="pay_multiple"><input type="hidden" name="do_save" value="1">
  <input type="hidden" name="selected_invoice_ids" value="<?= h(implode('|', array_map(static fn($x) => (string)$x['invoices_id'], $selectedRows))) ?>">
  <input type="hidden" name="return_mode" value="<?= h((string)($fv['return_mode'] ?? '')) ?>">
  <input type="hidden" name="search_agent" value="<?= h((string)($fv['search_agent'] ?? '')) ?>"><input type="hidden" name="search_supplier" value="<?= h((string)($fv['search_supplier'] ?? '')) ?>"><input type="hidden" name="search_ref" value="<?= h((string)($fv['search_ref'] ?? '')) ?>"><input type="hidden" name="from_date" value="<?= h((string)($fv['from_date'] ?? '')) ?>"><input type="hidden" name="to_date" value="<?= h((string)($fv['to_date'] ?? '')) ?>">
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 max-w-3xl">
    <label class="form-control"><span class="label-text text-xs">Date</span><input name="paid_date" class="input input-bordered input-sm js-invoice-date-input" value="<?= h((string)($fv['paid_date'] ?? date('Y-m-d'))) ?>"></label>
    <label class="form-control"><span class="label-text text-xs">Cheque / Voucher No.</span><input name="cheque_no" class="input input-bordered input-sm" value="<?= h((string)($fv['cheque_no'] ?? '')) ?>"></label>
    <label class="form-control"><span class="label-text text-xs">Amount</span><input name="paying_amt" class="input input-bordered input-sm" value="<?= h((string)($fv['paying_amt'] ?? '')) ?>"></label>
  </div>
  <div class="overflow-x-auto"><table class="table table-xs table-bordered max-w-4xl"><thead><tr><th>Invoice Date</th><th>Invoice No.</th><th>Supplier / Agent Name</th><th>Amount</th></tr></thead><tbody>
  <?php $tot=0.0; foreach($selectedRows as $r): $tot += (float)$r['balance_amount']; ?>
    <tr><td><?= h($r['invoice_create_date']) ?></td><td><?= h($r['invoices_id']) ?></td><td><?= h($r['agent_supplier_name']) ?></td><td><?= h($r['balance_amount']) ?></td></tr>
  <?php endforeach; ?>
  <tr><td colspan="3" class="text-right font-semibold">Total</td><td><?= h(number_format($tot,2,'.','')) ?></td></tr>
  </tbody></table></div>
  <button type="submit" class="btn btn-success btn-sm">Save</button>
</form>
