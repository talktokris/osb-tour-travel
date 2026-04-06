<?php
declare(strict_types=1);
/** @var array<string,string> $invoiceRow */
?>
<form method="post" action="index.php?page=invoice&mode=pay_single" class="space-y-3">
  <input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="invoice_submit" value="1"><input type="hidden" name="mode" value="pay_single"><input type="hidden" name="do_save" value="1">
  <input type="hidden" name="invoice_id" value="<?= h($invoiceRow['invoices_id']) ?>">
  <input type="hidden" name="return_mode" value="<?= h((string)($fv['return_mode'] ?? '')) ?>">
  <input type="hidden" name="search_agent" value="<?= h((string)($fv['search_agent'] ?? '')) ?>"><input type="hidden" name="search_supplier" value="<?= h((string)($fv['search_supplier'] ?? '')) ?>"><input type="hidden" name="search_ref" value="<?= h((string)($fv['search_ref'] ?? '')) ?>"><input type="hidden" name="from_date" value="<?= h((string)($fv['from_date'] ?? '')) ?>"><input type="hidden" name="to_date" value="<?= h((string)($fv['to_date'] ?? '')) ?>">
  <div class="voucher-shell">
    <div class="voucher-title">Payment Voucher</div>
    <div class="voucher-grid">
      <div class="voucher-label">Date:</div>
      <div></div>
      <div class="voucher-field"><input name="paid_date" class="voucher-input js-invoice-date-input" value="<?= h((string)($fv['paid_date'] ?? date('Y-m-d'))) ?>"></div>
      <div class="voucher-label">Cheque / Voucher No.:</div>
      <div></div>
      <div class="voucher-field"><input name="cheque_no" class="voucher-input" value="<?= h((string)($fv['cheque_no'] ?? '')) ?>"></div>
      <div class="voucher-label">Amount:</div>
      <div></div>
      <div class="voucher-field"><input name="paying_amt" class="voucher-input" value="<?= h((string)($fv['paying_amt'] ?? $invoiceRow['balance_amount'])) ?>"></div>
      <div class="voucher-label">Balance :</div>
      <div></div>
      <div class="voucher-field"><input class="voucher-input" value="<?= h((string)$invoiceRow['balance_amount']) ?>" readonly></div>
    </div>

    <div class="voucher-table-wrap">
      <table class="voucher-table">
        <thead>
          <tr><th>Invoice Date</th><th>Invoice No.</th><th>Supplier / Agent Name</th><th>Amount</th></tr>
        </thead>
        <tbody>
          <tr><td><?= h($invoiceRow['invoice_create_date']) ?></td><td><?= h($invoiceRow['invoices_id']) ?></td><td><?= h($invoiceRow['agent_supplier_name']) ?></td><td><?= h($invoiceRow['balance_amount']) ?></td></tr>
        </tbody>
      </table>
    </div>

    <div class="voucher-actions">
      <button type="submit" class="btn btn-success btn-sm voucher-save">Save</button>
    </div>
  </div>
</form>
