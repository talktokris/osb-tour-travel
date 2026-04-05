<?php
/** @var array<string, string> $fv */
?>
<input type="hidden" name="mode" value="overland">
<input type="hidden" name="search_submit" value="1">
<label class="form-control w-full max-w-xl">
    <span class="label-text">Search by Agent (exact)</span>
    <input type="text" name="search_word" class="input input-bordered w-full js-ac" data-ac-field="agent"
           value="<?= h((string) ($fv['search_word'] ?? '')) ?>" autocomplete="off">
</label>
<p class="text-xs text-base-content/70 mt-2 max-w-xl">Shows grouped services per file (Overland style).</p>
