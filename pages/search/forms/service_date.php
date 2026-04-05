<?php
/** @var array<string, string> $fv */
?>
<input type="hidden" name="mode" value="service_date">
<input type="hidden" name="search_submit" value="1">
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
    <label class="form-control w-full">
        <span class="label-text text-xs">From Date</span>
        <?php
        $name = 'from_date';
        $value = (string) ($fv['from_date'] ?? '');
        $id = 'search-sd-from';
        require __DIR__ . '/../date_field.php';
        ?>
    </label>
    <label class="form-control w-full">
        <span class="label-text text-xs">To Date</span>
        <?php
        $name = 'to_date';
        $value = (string) ($fv['to_date'] ?? '');
        $id = 'search-sd-to';
        require __DIR__ . '/../date_field.php';
        ?>
    </label>
</div>
