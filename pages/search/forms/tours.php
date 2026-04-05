<?php
/** @var array<string, string> $fv */
/** @var list<string> $tourCats */
?>
<input type="hidden" name="mode" value="tours">
<input type="hidden" name="search_submit" value="1">
<label class="form-control w-full max-w-xl">
    <span class="label-text">Search by Tour / Service category</span>
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
