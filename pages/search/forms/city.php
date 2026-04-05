<?php
/** @var array<string, string> $fv */
?>
<input type="hidden" name="mode" value="city">
<input type="hidden" name="search_submit" value="1">
<label class="form-control w-full">
    <span class="label-text text-xs">Search by City</span>
    <input type="text" name="search_word" class="js-ac w-full" data-ac-field="city"
           value="<?= h((string) ($fv['search_word'] ?? '')) ?>" autocomplete="off" required>
</label>
