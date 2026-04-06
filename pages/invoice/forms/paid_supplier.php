<?php
declare(strict_types=1);
?>
<div class="invoice-form-fieldstack">
  <label class="form-control w-full max-w-md">
    <span class="label-text text-xs font-semibold">Search by Supplier</span>
    <input type="text" name="search_supplier" class="input input-bordered input-sm w-full bg-white" value="<?= h((string) ($fv['search_supplier'] ?? '')) ?>" list="invoice-suppliers">
    <datalist id="invoice-suppliers"><?php foreach ($suppliers as $a): ?><option value="<?= h($a) ?>"><?php endforeach; ?></datalist>
  </label>
  <label class="form-control w-full max-w-md">
    <span class="label-text text-xs font-semibold">Search by Invoice No.</span>
    <input type="text" name="search_ref" class="input input-bordered input-sm w-full bg-white" value="<?= h((string) ($fv['search_ref'] ?? '')) ?>" list="invoice-refs">
    <datalist id="invoice-refs"><?php foreach ($invoiceRefs as $a): ?><option value="<?= h($a) ?>"><?php endforeach; ?></datalist>
  </label>
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-2xl">
    <label class="form-control"><span class="label-text text-xs font-semibold">From Date</span><input type="text" name="from_date" class="input input-bordered input-sm w-full bg-white js-invoice-date-input" placeholder="yyyy-mm-dd" value="<?= h((string) ($fv['from_date'] ?? date('Y-m-01'))) ?>"></label>
    <label class="form-control"><span class="label-text text-xs font-semibold">To Date</span><input type="text" name="to_date" class="input input-bordered input-sm w-full bg-white js-invoice-date-input" placeholder="yyyy-mm-dd" value="<?= h((string) ($fv['to_date'] ?? date('Y-m-d'))) ?>"></label>
  </div>
</div>
