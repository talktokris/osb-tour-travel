<?php

declare(strict_types=1);

/** @var array<string, mixed> $fv */
/** @var list<string> $agents */
?>
<div class="report-form-fieldstack">
    <label class="form-control w-full max-w-md">
        <span class="label-text text-xs font-semibold">Report by Agent</span>
        <select name="search_word" class="select select-bordered select-sm w-full bg-white">
            <option value="">Select…</option>
            <?php
            $sel = (string) ($fv['search_word'] ?? '');
            foreach ($agents as $a) {
                $s = $sel === $a ? ' selected' : '';
                echo '<option value="' . h($a) . '"' . $s . '>' . h($a) . '</option>';
            }
            ?>
        </select>
    </label>
    <p class="text-xs text-base-content/70 max-w-xl">Optional date range filters confirmed services by service date. Leave blank for all dates.</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-2xl">
        <label class="form-control w-full">
            <span class="label-text text-xs font-semibold">From date</span>
            <input type="text" name="from_date" value="<?= h((string) ($fv['from_date'] ?? '')) ?>"
                   class="input input-bordered input-sm w-full bg-white js-report-date-input" placeholder="dd-mm-yyyy" autocomplete="off">
        </label>
        <label class="form-control w-full">
            <span class="label-text text-xs font-semibold">To date</span>
            <input type="text" name="to_date" value="<?= h((string) ($fv['to_date'] ?? '')) ?>"
                   class="input input-bordered input-sm w-full bg-white js-report-date-input" placeholder="dd-mm-yyyy" autocomplete="off">
        </label>
    </div>
</div>
