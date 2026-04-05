<?php
/** @var array<string, string> $fv */
/** @var list<string> $tourCats */
?>
<input type="hidden" name="mode" value="tour_type">
<input type="hidden" name="search_submit" value="1">
<label class="form-control w-full max-w-xl">
    <span class="label-text">Search by Tour Type</span>
    <select name="search_word" class="select select-bordered w-full">
        <option value="">Select type</option>
        <?php
        $sel = (string) ($fv['search_word'] ?? '');
        foreach ($tourCats as $t) {
            echo '<option value="' . h($t) . '"' . ($sel === $t ? ' selected' : '') . '>' . h($t) . '</option>';
        }
        ?>
    </select>
</label>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-2xl mt-3">
    <label class="form-control">
        <span class="label-text">From Date (dd-mm-yyyy)</span>
        <input type="text" name="from_date" class="input input-bordered" placeholder="01-04-2026"
               value="<?= h((string) ($fv['from_date'] ?? '')) ?>">
    </label>
    <label class="form-control">
        <span class="label-text">To Date (dd-mm-yyyy)</span>
        <input type="text" name="to_date" class="input input-bordered" placeholder="30-04-2026"
               value="<?= h((string) ($fv['to_date'] ?? '')) ?>">
    </label>
</div>
