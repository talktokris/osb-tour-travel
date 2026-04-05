<?php
/** @var array<string, string> $fv */
?>
<input type="hidden" name="mode" value="agent">
<input type="hidden" name="search_submit" value="1">
<label class="form-control w-full">
    <span class="label-text text-xs">Search by Agent</span>
    <input type="text" name="search_word" class="js-ac w-full" data-ac-field="agent"
           value="<?= h((string) ($fv['search_word'] ?? '')) ?>" autocomplete="off">
</label>
