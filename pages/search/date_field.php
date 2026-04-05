<?php

declare(strict_types=1);

/** @var string $name */
/** @var string $value */
/** @var string $id Unique HTML id for this date input */
/** @var bool $dateRequired When true, field is required (matches server compulsory rules). */

$__searchDateRequired = !empty($dateRequired);

?>
<div class="search-dob-wrap w-full max-w-46">
    <input type="text" name="<?= h($name) ?>" id="<?= h($id) ?>"
           class="search-dob-input js-search-date-input w-full"
           placeholder="dd-mm-yyyy" value="<?= h($value) ?>" autocomplete="off" inputmode="numeric"<?= $__searchDateRequired ? ' required' : '' ?>>
    <button type="button" class="search-dob-cal-btn js-search-date-cal" data-target="<?= h($id) ?>"
            title="Open calendar" aria-label="Open calendar">
        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    </button>
</div>
