<?php
/** @var array<string, string> $fv */
/** @var list<string> $vehicleTypes */
?>
<input type="hidden" name="mode" value="vehicle_type">
<input type="hidden" name="search_submit" value="1">
<label class="form-control w-full">
    <span class="label-text text-xs">Search by Vehicle Type</span>
    <select name="search_word" class="select select-bordered select-sm search-field w-full bg-white">
        <option value="">Select type</option>
        <?php
        $sel = (string) ($fv['search_word'] ?? '');
        foreach ($vehicleTypes as $t) {
            echo '<option value="' . h($t) . '"' . ($sel === $t ? ' selected' : '') . '>' . h($t) . '</option>';
        }
        ?>
    </select>
</label>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-1">
    <label class="form-control w-full">
        <span class="label-text text-xs">From Date</span>
        <?php
        $name = 'from_date';
        $value = (string) ($fv['from_date'] ?? '');
        $id = 'search-vt-from';
        require __DIR__ . '/../date_field.php';
        ?>
    </label>
    <label class="form-control w-full">
        <span class="label-text text-xs">To Date</span>
        <?php
        $name = 'to_date';
        $value = (string) ($fv['to_date'] ?? '');
        $id = 'search-vt-to';
        require __DIR__ . '/../date_field.php';
        ?>
    </label>
</div>
