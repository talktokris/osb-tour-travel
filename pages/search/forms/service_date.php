<?php
/** @var array<string, string> $fv */
?>
<input type="hidden" name="mode" value="service_date">
<input type="hidden" name="search_submit" value="1">
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-2xl">
    <label class="form-control">
        <span class="label-text">From Date (dd-mm-yyyy)</span>
        <input type="text" name="from_date" class="input input-bordered"
               value="<?= h((string) ($fv['from_date'] ?? '')) ?>">
    </label>
    <label class="form-control">
        <span class="label-text">To Date (dd-mm-yyyy)</span>
        <input type="text" name="to_date" class="input input-bordered"
               value="<?= h((string) ($fv['to_date'] ?? '')) ?>">
    </label>
</div>
