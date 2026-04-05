<?php
/** @var array<string, string> $fv */
/** @var list<string> $vehicleTypes */
/** @var list<string> $tourCats */
/** @var list<string> $serviceTypes */
?>
<input type="hidden" name="mode" value="combined">
<input type="hidden" name="search_submit" value="1">
<div class="grid grid-cols-1 md:grid-cols-2 gap-x-3 gap-y-2.5 w-full">
    <label class="form-control w-full">
        <span class="label-text text-xs">Agent Name</span>
        <input type="text" name="search_agent" class="js-ac w-full" data-ac-field="agent"
               value="<?= h((string) ($fv['search_agent'] ?? '')) ?>" autocomplete="off">
    </label>
    <label class="form-control w-full">
        <span class="label-text text-xs">Supplier Name</span>
        <input type="text" name="search_supplier" class="js-ac w-full" data-ac-field="supplier"
               value="<?= h((string) ($fv['search_supplier'] ?? '')) ?>" autocomplete="off">
    </label>
    <label class="form-control w-full">
        <span class="label-text text-xs">Ref No.</span>
        <input type="text" name="search_ref" class="js-ac w-full" data-ac-field="ref_no"
               value="<?= h((string) ($fv['search_ref'] ?? '')) ?>" autocomplete="off">
    </label>
    <label class="form-control w-full">
        <span class="label-text text-xs">File No.</span>
        <input type="text" name="search_file_no" class="js-ac w-full" data-ac-field="file_no"
               value="<?= h((string) ($fv['search_file_no'] ?? '')) ?>" autocomplete="off">
    </label>
    <label class="form-control w-full">
        <span class="label-text text-xs">Pax Name</span>
        <input type="text" name="search_pax" class="js-ac w-full" data-ac-field="pax"
               value="<?= h((string) ($fv['search_pax'] ?? '')) ?>" autocomplete="off">
    </label>
    <label class="form-control w-full">
        <span class="label-text text-xs">Vehicle Type</span>
        <select name="vehicle_search_word" class="select select-bordered select-sm search-field w-full bg-white">
            <option value="">None</option>
            <?php
            $vsel = (string) ($fv['vehicle_search_word'] ?? '');
            foreach ($vehicleTypes as $t) {
                echo '<option value="' . h($t) . '"' . ($vsel === $t ? ' selected' : '') . '>' . h($t) . '</option>';
            }
            ?>
        </select>
    </label>
    <label class="form-control w-full">
        <span class="label-text text-xs">Tour Type</span>
        <select name="tour_search_word" class="select select-bordered select-sm search-field w-full bg-white">
            <option value="">None</option>
            <?php
            $tsel = (string) ($fv['tour_search_word'] ?? '');
            foreach ($tourCats as $t) {
                echo '<option value="' . h($t) . '"' . ($tsel === $t ? ' selected' : '') . '>' . h($t) . '</option>';
            }
            ?>
        </select>
    </label>
    <label class="form-control w-full">
        <span class="label-text text-xs">Driver Name</span>
        <input type="text" name="search_driver" class="js-ac w-full" data-ac-field="driver"
               value="<?= h((string) ($fv['search_driver'] ?? '')) ?>" autocomplete="off">
    </label>
    <label class="form-control w-full">
        <span class="label-text text-xs">Vehicle No.</span>
        <input type="text" name="search_vehicles" class="js-ac w-full" data-ac-field="vehicle_no"
               value="<?= h((string) ($fv['search_vehicles'] ?? '')) ?>" autocomplete="off">
    </label>
    <label class="form-control w-full">
        <span class="label-text text-xs">Service Date (exact)</span>
        <?php
        $name = 'select_date';
        $value = (string) ($fv['select_date'] ?? '');
        $id = 'search-comb-sel';
        require __DIR__ . '/../date_field.php';
        ?>
    </label>
    <label class="form-control w-full">
        <span class="label-text text-xs">City Service</span>
        <input type="text" name="search_city" class="js-ac w-full" data-ac-field="city"
               value="<?= h((string) ($fv['search_city'] ?? '')) ?>" autocomplete="off">
    </label>
    <label class="form-control w-full md:col-span-2">
        <span class="label-text text-xs">Dep, Arrival &amp; Over</span>
        <select name="search_word" class="select select-bordered select-sm search-field w-full bg-white">
            <option value="">None</option>
            <?php
            $ssel = (string) ($fv['search_word'] ?? '');
            foreach ($serviceTypes as $t) {
                echo '<option value="' . h($t) . '"' . ($ssel === $t ? ' selected' : '') . '>' . h($t) . '</option>';
            }
            ?>
        </select>
    </label>
    <div class="form-control w-full md:col-span-2">
        <span class="label-text text-xs">From Date to To Date</span>
        <div class="flex flex-wrap items-end gap-2 sm:gap-3 mt-1">
            <div class="shrink-0">
                <?php
                $name = 'from_date';
                $value = (string) ($fv['from_date'] ?? '');
                $id = 'search-comb-from';
                require __DIR__ . '/../date_field.php';
                ?>
            </div>
            <span class="text-xs text-base-content/70 pb-2">to</span>
            <div class="shrink-0">
                <?php
                $name = 'to_date';
                $value = (string) ($fv['to_date'] ?? '');
                $id = 'search-comb-to';
                require __DIR__ . '/../date_field.php';
                ?>
            </div>
        </div>
    </div>
</div>
