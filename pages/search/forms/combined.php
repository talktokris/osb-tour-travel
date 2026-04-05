<?php
/** @var array<string, string> $fv */
/** @var list<string> $vehicleTypes */
/** @var list<string> $tourCats */
/** @var list<string> $serviceTypes */
?>
<input type="hidden" name="mode" value="combined">
<input type="hidden" name="search_submit" value="1">
<div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-w-4xl">
    <label class="form-control">
        <span class="label-text">Agent Name</span>
        <input type="text" name="search_agent" class="input input-bordered js-ac" data-ac-field="agent"
               value="<?= h((string) ($fv['search_agent'] ?? '')) ?>" autocomplete="off">
    </label>
    <label class="form-control">
        <span class="label-text">Supplier Name</span>
        <input type="text" name="search_supplier" class="input input-bordered js-ac" data-ac-field="supplier"
               value="<?= h((string) ($fv['search_supplier'] ?? '')) ?>" autocomplete="off">
    </label>
    <label class="form-control">
        <span class="label-text">Ref No.</span>
        <input type="text" name="search_ref" class="input input-bordered js-ac" data-ac-field="ref_no"
               value="<?= h((string) ($fv['search_ref'] ?? '')) ?>" autocomplete="off">
    </label>
    <label class="form-control">
        <span class="label-text">File No.</span>
        <input type="text" name="search_file_no" class="input input-bordered js-ac" data-ac-field="file_no"
               value="<?= h((string) ($fv['search_file_no'] ?? '')) ?>" autocomplete="off">
    </label>
    <label class="form-control">
        <span class="label-text">Pax Name</span>
        <input type="text" name="search_pax" class="input input-bordered js-ac" data-ac-field="pax"
               value="<?= h((string) ($fv['search_pax'] ?? '')) ?>" autocomplete="off">
    </label>
    <label class="form-control">
        <span class="label-text">Vehicle Type</span>
        <select name="vehicle_search_word" class="select select-bordered w-full">
            <option value="">None</option>
            <?php
            $vsel = (string) ($fv['vehicle_search_word'] ?? '');
            foreach ($vehicleTypes as $t) {
                echo '<option value="' . h($t) . '"' . ($vsel === $t ? ' selected' : '') . '>' . h($t) . '</option>';
            }
            ?>
        </select>
    </label>
    <label class="form-control">
        <span class="label-text">Tour Type</span>
        <select name="tour_search_word" class="select select-bordered w-full">
            <option value="">None</option>
            <?php
            $tsel = (string) ($fv['tour_search_word'] ?? '');
            foreach ($tourCats as $t) {
                echo '<option value="' . h($t) . '"' . ($tsel === $t ? ' selected' : '') . '>' . h($t) . '</option>';
            }
            ?>
        </select>
    </label>
    <label class="form-control">
        <span class="label-text">Driver Name</span>
        <input type="text" name="search_driver" class="input input-bordered js-ac" data-ac-field="driver"
               value="<?= h((string) ($fv['search_driver'] ?? '')) ?>" autocomplete="off">
    </label>
    <label class="form-control">
        <span class="label-text">Vehicle No.</span>
        <input type="text" name="search_vehicles" class="input input-bordered js-ac" data-ac-field="vehicle_no"
               value="<?= h((string) ($fv['search_vehicles'] ?? '')) ?>" autocomplete="off">
    </label>
    <label class="form-control">
        <span class="label-text">Service Date (exact, dd-mm-yyyy)</span>
        <input type="text" name="select_date" class="input input-bordered"
               value="<?= h((string) ($fv['select_date'] ?? '')) ?>">
    </label>
    <label class="form-control">
        <span class="label-text">City Service</span>
        <input type="text" name="search_city" class="input input-bordered js-ac" data-ac-field="city"
               value="<?= h((string) ($fv['search_city'] ?? '')) ?>" autocomplete="off">
    </label>
    <label class="form-control md:col-span-2">
        <span class="label-text">Dep, Arrival &amp; Over</span>
        <select name="search_word" class="select select-bordered w-full">
            <option value="">None</option>
            <?php
            $ssel = (string) ($fv['search_word'] ?? '');
            foreach ($serviceTypes as $t) {
                echo '<option value="' . h($t) . '"' . ($ssel === $t ? ' selected' : '') . '>' . h($t) . '</option>';
            }
            ?>
        </select>
    </label>
    <label class="form-control md:col-span-2">
        <span class="label-text">From Date to To Date (dd-mm-yyyy)</span>
        <div class="flex flex-wrap items-center gap-2">
            <input type="text" name="from_date" class="input input-bordered flex-1 min-w-[8rem]"
                   value="<?= h((string) ($fv['from_date'] ?? '')) ?>">
            <span>to</span>
            <input type="text" name="to_date" class="input input-bordered flex-1 min-w-[8rem]"
                   value="<?= h((string) ($fv['to_date'] ?? '')) ?>">
        </div>
    </label>
</div>
