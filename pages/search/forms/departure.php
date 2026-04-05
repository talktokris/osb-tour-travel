<?php
/** @var array<string, string> $fv */
?>
<input type="hidden" name="mode" value="departure">
<input type="hidden" name="search_submit" value="1">
<label class="form-control w-full">
    <span class="label-text text-xs">Search by Agent (exact)</span>
    <input type="text" name="search_word" class="js-ac w-full" data-ac-field="agent"
           value="<?= h((string) ($fv['search_word'] ?? '')) ?>" autocomplete="off" required>
</label>
<p class="text-xs text-base-content/60 pt-1">Grouped results per file.</p>
