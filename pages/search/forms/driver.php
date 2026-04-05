<?php
/** @var array<string, string> $fv */
?>
<input type="hidden" name="mode" value="driver">
<input type="hidden" name="search_submit" value="1">
<label class="form-control w-full max-w-xl">
    <span class="label-text">Search by Driver Name</span>
    <input type="text" name="search_word" class="input input-bordered w-full js-ac" data-ac-field="driver"
           value="<?= h((string) ($fv['search_word'] ?? '')) ?>" autocomplete="off">
</label>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-2xl mt-3">
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
