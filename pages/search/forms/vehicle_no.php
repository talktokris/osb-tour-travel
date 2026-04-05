<?php
/** @var array<string, string> $fv */
?>
<input type="hidden" name="mode" value="vehicle_no">
<input type="hidden" name="search_submit" value="1">
<label class="form-control w-full">
    <span class="label-text text-xs">Search by Vehicle No.</span>
    <input type="text" name="search_word" class="js-ac w-full" data-ac-field="vehicle_no"
           value="<?= h((string) ($fv['search_word'] ?? '')) ?>" autocomplete="off">
</label>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-1">
    <label class="form-control w-full">
        <span class="label-text text-xs">From Date</span>
        <?php
        $name = 'from_date';
        $value = (string) ($fv['from_date'] ?? '');
        $id = 'search-vn-from';
        require __DIR__ . '/../date_field.php';
        ?>
    </label>
    <label class="form-control w-full">
        <span class="label-text text-xs">To Date</span>
        <?php
        $name = 'to_date';
        $value = (string) ($fv['to_date'] ?? '');
        $id = 'search-vn-to';
        require __DIR__ . '/../date_field.php';
        ?>
    </label>
</div>
