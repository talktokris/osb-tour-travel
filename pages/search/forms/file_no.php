<?php
/** @var array<string, string> $fv */
?>
<input type="hidden" name="mode" value="file_no">
<input type="hidden" name="search_submit" value="1">
<label class="form-control w-full max-w-xl">
    <span class="label-text">Search by File No</span>
    <input type="text" name="search_word" class="input input-bordered w-full js-ac" data-ac-field="file_no"
           value="<?= h((string) ($fv['search_word'] ?? '')) ?>" autocomplete="off">
</label>
