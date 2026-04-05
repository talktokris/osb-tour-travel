<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/file_module_service.php';
require_once __DIR__ . '/../../includes/search_module_service.php';

if (!file_module_has_agent()) {
    file_module_render_agent_required();
}

$currentPage = 'search';
$mode = search_module_normalize_mode((string) ($_GET['mode'] ?? 'agent'));

$postTrim = static function (): array {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return [];
    }
    $o = [];
    foreach ($_POST as $k => $v) {
        $o[$k] = is_string($v) ? trim($v) : $v;
    }

    return $o;
};

$fv = $postTrim();
$searchOutcome = null;

if (($_SERVER['REQUEST_METHOD'] === 'POST') && (($fv['search_submit'] ?? '') === '1')) {
    $mode = search_module_normalize_mode((string) ($fv['mode'] ?? $mode));
    $searchOutcome = search_module_run($mysqli, $mode, $fv);
}

$flash = file_module_flash_get();
$csrf = file_module_csrf_token();
$redirectBack = 'index.php?page=search&mode=' . rawurlencode($mode);

$titles = [
    'agent' => 'Search by Agent',
    'supplier' => 'Search by Supplier',
    'file_no' => 'Search by File Number',
    'pax' => 'Search by Pax Name',
    'vehicle_type' => 'Search by Vehicle Type',
    'tour_type' => 'Search by Tour Type',
    'driver' => 'Search by Driver Name',
    'vehicle_no' => 'Search by Vehicle No.',
    'service_date' => 'Search by Service Date',
    'city' => 'Search by City Service',
    'arrival' => 'Search by Arrival / Departure / Over',
    'combined' => 'Search by (combined)',
    'departure' => 'Arrival, Dep, Tours — Departure (by agent)',
    'overland' => 'Arrival, Dep, Tours — Overland (by agent)',
    'tours' => 'Search by Tours (service category)',
];

$pageTitle = $titles[$mode] ?? 'Search';

$vehicleTypes = search_module_vehicle_type_names($mysqli);
$tourCats = search_module_tour_category_names($mysqli);
$serviceTypes = search_module_service_type_names($mysqli);

$formFile = __DIR__ . '/forms/' . $mode . '.php';
if (!is_file($formFile)) {
    $formFile = __DIR__ . '/forms/agent.php';
    $mode = 'agent';
    $pageTitle = $titles['agent'];
}

require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
/* Search module: compact form width, light autocomplete, date + calendar (match transfer search / file index) */
.search-module-scope .search-form-shell {
    max-width: 42rem;
    width: 100%;
    margin-left: auto;
    margin-right: auto;
}
.search-module-scope .search-form-yellow {
    padding: 1.25rem 1.35rem;
}
@media (min-width: 640px) {
    .search-module-scope .search-form-yellow {
        padding: 1.35rem 1.5rem;
    }
}
.search-module-scope .search-field {
    font-size: 0.8125rem;
    min-height: 2.25rem;
    height: 2.25rem;
}
.search-module-scope select.search-field {
    line-height: 1.2;
}
.search-module-scope .search-module-card .card-body {
    padding-top: 1rem;
    padding-bottom: 1rem;
}
/* Combined search: wider card, denser fields, less vertical padding */
.search-module-scope .search-form-shell--wide {
    max-width: none;
    width: 100%;
}
.search-module-scope .search-form-yellow--compact {
    padding: 0.75rem 0.9rem;
}
@media (min-width: 640px) {
    .search-module-scope .search-form-yellow--compact {
        padding: 0.85rem 1rem;
    }
}
.search-module-scope .search-module-card--combined .card-body {
    padding-top: 0.65rem;
    padding-bottom: 0.75rem;
}
.search-module-scope .search-module-card--combined .card-title {
    margin-bottom: 0.35rem;
    font-size: 1rem;
    line-height: 1.35;
}
/* Combined grid: real gutters (Tailwind gap-* may be missing from built tailwind.css for PHP files) */
.search-module-scope .search-form-combined {
    column-gap: 1.25rem;
    row-gap: 0.75rem;
}
@media (min-width: 640px) {
    .search-module-scope .search-form-combined {
        column-gap: 2rem;
        row-gap: 0.875rem;
    }
}
@media (min-width: 1280px) {
    .search-module-scope .search-form-combined {
        column-gap: 1.75rem;
        row-gap: 0.875rem;
    }
}
.search-module-scope .search-form-combined .form-control {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    padding: 0;
    margin: 0;
}
.search-module-scope .search-form-combined .label-text {
    font-size: 0.6875rem;
    line-height: 1.2;
    padding: 0;
    margin: 0;
    font-weight: 600;
    color: #475569;
    letter-spacing: 0.02em;
}
.search-module-scope .search-form-combined .input,
.search-module-scope .search-form-combined .select,
.search-module-scope .search-form-combined input.input {
    border-color: #94a3b8;
}
.search-module-scope .search-form-combined .input:focus,
.search-module-scope .search-form-combined .select:focus,
.search-module-scope .search-form-combined input.input:focus {
    outline: none;
    border-color: #1a6b5c;
    box-shadow: 0 0 0 2px color-mix(in oklab, #00a651 28%, transparent);
}
.search-module-scope .search-form-combined .search-dob-input {
    border-color: #94a3b8;
}
.search-module-scope .search-form-combined .search-form-date-row {
    gap: 0.35rem 0.5rem;
    margin-top: 0.2rem;
    align-items: center;
}
.search-module-scope .search-form-combined .search-form-date-to {
    color: rgb(100 116 139);
    font-size: 0.6875rem;
    font-weight: 600;
    padding: 0 0.125rem;
    flex-shrink: 0;
}
/* Autocomplete: full width, light panel (replaces dark native datalist) */
.search-ac-wrap {
    position: relative;
    width: 100%;
    display: block;
}
.search-ac-wrap > .js-ac,
.search-ac-wrap > input.js-ac {
    width: 100%;
}
.search-ac-dd {
    display: none;
    position: absolute;
    left: 0;
    right: 0;
    top: 100%;
    z-index: 60;
    margin-top: 3px;
    max-height: 240px;
    overflow-y: auto;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 0.375rem;
    box-shadow: 0 12px 32px rgba(15, 23, 42, 0.12), 0 4px 12px rgba(15, 23, 42, 0.06);
    list-style: none;
    margin: 0;
    padding: 0.25rem 0;
}
.search-ac-dd.is-open {
    display: block;
}
.search-ac-dd li {
    padding: 0.45rem 0.75rem;
    font-size: 0.8125rem;
    line-height: 1.35;
    color: #0f172a;
    cursor: pointer;
    border-bottom: 1px solid #f1f5f9;
}
.search-ac-dd li:last-child {
    border-bottom: 0;
}
.search-ac-dd li:hover,
.search-ac-dd li.is-active {
    background: #ecfdf5;
    color: #14532d;
}
.search-dob-wrap {
    position: relative;
    width: 100%;
}
.search-dob-input {
    width: 100%;
    height: 2.25rem;
    min-height: 2.25rem;
    padding-left: 0.5rem;
    padding-right: 2.15rem;
    font-size: 0.8125rem;
    line-height: 1.2;
    border: 1px solid #94a3b8;
    border-radius: 0.25rem;
    background: #fff;
    box-sizing: border-box;
    cursor: pointer;
}
.search-dob-input:hover { border-color: #64748b; }
.search-dob-input:focus {
    outline: 2px solid color-mix(in oklab, #00a651 40%, transparent);
    outline-offset: 1px;
    border-color: #1a6b5c;
}
.search-dob-cal-btn {
    position: absolute;
    right: 2px;
    top: 50%;
    transform: translateY(-50%);
    width: 1.85rem;
    height: 1.5rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin: 0;
    padding: 0;
    border: 0;
    border-radius: 0.2rem;
    background: transparent;
    cursor: pointer;
    color: #334155;
}
.search-dob-cal-btn:hover {
    background: rgba(26, 107, 92, 0.1);
    color: #1a6b5c;
}
.search-dob-cal-btn:focus-visible {
    outline: 2px solid #00a651;
    outline-offset: 1px;
}
.search-module-scope .flatpickr-calendar {
    border-radius: 10px;
    border: 1px solid #1a6b5c;
    box-shadow: 0 14px 44px rgba(15, 23, 42, 0.14), 0 4px 14px rgba(26, 107, 92, 0.1);
    font-family: inherit;
}
.search-module-scope .flatpickr-months .flatpickr-month {
    background: #1a6b5c !important;
    color: #fff !important;
    fill: #fff !important;
}
.search-module-scope .flatpickr-current-month .flatpickr-monthDropdown-months {
    background: rgba(255, 255, 255, 0.95) !important;
    color: #14532d !important;
    font-weight: 600;
    border-radius: 4px;
}
.search-module-scope .flatpickr-current-month input.cur-year {
    background: rgba(255, 255, 255, 0.15) !important;
    color: #fff !important;
    font-weight: 600;
    border-radius: 4px;
}
.search-module-scope .flatpickr-months .flatpickr-prev-month svg,
.search-module-scope .flatpickr-months .flatpickr-next-month svg {
    fill: #fff;
}
.search-module-scope .flatpickr-weekdays {
    background: #f0fdf4;
    border-bottom: 1px solid #d1fae5;
}
.search-module-scope span.flatpickr-weekday {
    color: #166534;
    font-weight: 600;
    font-size: 0.72rem;
}
.search-module-scope .flatpickr-day.selected,
.search-module-scope .flatpickr-day.startRange,
.search-module-scope .flatpickr-day.endRange {
    background: #00a651 !important;
    border-color: #00a651 !important;
    color: #fff !important;
}
.search-module-scope .flatpickr-day.today {
    border-color: #0d9488;
    color: #0f766e;
    font-weight: 700;
}
.search-module-scope .flatpickr-day:hover:not(.selected):not(.flatpickr-disabled) {
    background: #ecfdf5;
    border-color: #6ee7b7;
    color: #14532d;
}
/* Search outcome: center message boxes (validation / error / no results) like the form card */
.search-outcome-centered {
    display: flex;
    justify-content: center;
    width: 100%;
    box-sizing: border-box;
}
/* Search outcome: validation, errors, empty state */
.search-feedback {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
    max-width: 42rem; /* same as max-w-2xl / .search-form-shell */
    width: 100%;
    flex-shrink: 0;
    margin-left: auto;
    margin-right: auto;
    padding: 1.15rem 1.25rem;
    border-radius: 0.75rem;
    border: 1px solid #e2e8f0;
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06), 0 2px 8px rgba(15, 23, 42, 0.04);
}
.search-feedback__icon {
    flex-shrink: 0;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.6rem;
    display: flex;
    align-items: center;
    justify-content: center;
}
.search-feedback__body { min-width: 0; flex: 1; }
.search-feedback__title {
    margin: 0 0 0.35rem 0;
    font-size: 0.9375rem;
    font-weight: 700;
    line-height: 1.35;
}
.search-feedback__text {
    margin: 0;
    font-size: 0.8125rem;
    line-height: 1.5;
    color: #475569;
}
.search-feedback__list {
    margin: 0.5rem 0 0 0;
    padding-left: 1.15rem;
    font-size: 0.8125rem;
    line-height: 1.55;
    color: #334155;
}
.search-feedback__hint {
    margin: 0.65rem 0 0 0;
    font-size: 0.75rem;
    color: #64748b;
}
.search-feedback--validation {
    background: linear-gradient(135deg, #fffbeb 0%, #fff7ed 100%);
    border-color: #fcd34d;
}
.search-feedback--validation .search-feedback__icon {
    background: #fef3c7;
    color: #b45309;
}
.search-feedback--validation .search-feedback__title { color: #92400e; }
.search-feedback--error {
    background: linear-gradient(135deg, #fef2f2 0%, #fff1f2 100%);
    border-color: #fca5a5;
}
.search-feedback--error .search-feedback__icon {
    background: #fee2e2;
    color: #b91c1c;
}
.search-feedback--error .search-feedback__title { color: #991b1b; }
.search-feedback--empty {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-color: #cbd5e1;
}
.search-feedback--empty .search-feedback__icon {
    background: #e2e8f0;
    color: #475569;
}
.search-feedback--empty .search-feedback__title { color: #1e293b; }
</style>

<div class="flex gap-6 w-full search-module-scope">
    <aside class="hidden lg:block w-72 shrink-0">
        <?php require __DIR__ . '/sidebar.php'; ?>
    </aside>

    <main class="flex-1 min-w-0 px-4">
        <div class="space-y-4 w-full min-w-0">
            <?php $breadcrumbCurrent = 'Search';
            require __DIR__ . '/../../includes/breadcrumb.php'; ?>

            <?php if ($flash !== null): ?>
                <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-warning' ?> text-sm">
                    <?= h((string) $flash['message']) ?>
                </div>
            <?php endif; ?>

            <?php
            $isCombinedSearch = ($mode === 'combined');
            $searchFormSpaceClass = $isCombinedSearch ? 'space-y-3' : 'space-y-3 sm:space-y-3.5';
            $searchCardWidthClass = $isCombinedSearch ? 'max-w-5xl search-module-card--combined' : 'max-w-2xl';
            $searchShellClass = 'search-form-shell' . ($isCombinedSearch ? ' search-form-shell--wide' : '');
            $searchYellowClass = 'rounded-box border border-warning/40 search-form-yellow' . ($isCombinedSearch ? ' search-form-yellow--compact' : '');
            ?>
            <div class="card bg-base-100 shadow-xl border border-base-300 search-module-card <?= $searchCardWidthClass ?> w-full mx-auto">
                <div class="card-body">
                    <h3 class="card-title text-base sm:text-lg" style="color:#009900"><?= h($pageTitle) ?></h3>
                    <div class="<?= h($searchShellClass) ?>">
                    <div class="<?= h($searchYellowClass) ?>" style="background:#ffffe8">
                        <form method="post" action="index.php?page=search&amp;mode=<?= rawurlencode($mode) ?>" class="<?= h($searchFormSpaceClass) ?>">
                            <?php require $formFile; ?>
                            <div class="<?= $isCombinedSearch ? 'pt-0.5' : 'pt-1' ?>">
                                <button type="submit" class="btn btn-success btn-sm text-white <?= $isCombinedSearch ? 'min-h-8' : 'min-h-9' ?>">Search</button>
                            </div>
                        </form>
                    </div>
                    </div>
                </div>
            </div>

            <?php if ($searchOutcome !== null): ?>
                <?php
                $validationIssues = $searchOutcome['validation_issues'] ?? null;
                $isValidation = is_array($validationIssues) && $validationIssues !== [];
                $hasSearchError = !empty($searchOutcome['error']);
                $variant = (string) $searchOutcome['variant'];
                $rows = $searchOutcome['rows'];
                $groups = $searchOutcome['groups'] ?? null;
                $hasNested = $variant === 'nested' && is_array($groups) && $groups !== [];
                $hasFlat = $rows !== [];
                $showResultsTable = !$isValidation && !$hasSearchError && ($hasNested || $hasFlat);
                ?>
                <?php if ($showResultsTable): ?>
                    <?php
                    $redirect = $redirectBack;
                    require __DIR__ . '/results_table.php';
                    ?>
                <?php else: ?>
                    <div class="search-outcome-centered">
                    <?php if ($isValidation): ?>
                        <div class="search-feedback search-feedback--validation" role="alert" aria-live="polite">
                            <div class="search-feedback__icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            </div>
                            <div class="search-feedback__body">
                                <h4 class="search-feedback__title">Required fields</h4>
                                <p class="search-feedback__text">Please fill in the following before searching:</p>
                                <ul class="search-feedback__list">
                                    <?php foreach ($validationIssues as $issue): ?>
                                        <li><?= h((string) $issue) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <p class="search-feedback__hint">Date fields use format <strong>dd-mm-yyyy</strong> (calendar picker or typed).</p>
                            </div>
                        </div>
                    <?php elseif ($hasSearchError): ?>
                        <div class="search-feedback search-feedback--error" role="alert" aria-live="assertive">
                            <div class="search-feedback__icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                            </div>
                            <div class="search-feedback__body">
                                <h4 class="search-feedback__title">Search could not complete</h4>
                                <p class="search-feedback__text"><?= h((string) $searchOutcome['error']) ?></p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="search-feedback search-feedback--empty" role="status" aria-live="polite">
                            <div class="search-feedback__icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                            </div>
                            <div class="search-feedback__body">
                                <h4 class="search-feedback__title">No matching records</h4>
                                <p class="search-feedback__text">Nothing in your data matched this search. Try different keywords, widen the date range, or check spelling.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
(function () {
    /* Light-themed autocomplete, aligned to input width */
    var timer = null;
    function attachAc(input) {
        var field = input.getAttribute('data-ac-field');
        if (!field || input.closest('.search-ac-wrap')) return;
        input.removeAttribute('list');
        var wrap = document.createElement('div');
        wrap.className = 'search-ac-wrap';
        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);
        ['input', 'input-bordered', 'search-field', 'bg-white', 'w-full'].forEach(function (c) {
            if (!input.classList.contains(c)) {
                input.classList.add(c);
            }
        });
        var dd = document.createElement('ul');
        dd.className = 'search-ac-dd';
        dd.setAttribute('role', 'listbox');
        wrap.appendChild(dd);
        var items = [];
        var active = -1;
        function hide() {
            dd.classList.remove('is-open');
            active = -1;
            Array.prototype.forEach.call(dd.querySelectorAll('li'), function (el) { el.classList.remove('is-active'); });
        }
        function render() {
            dd.innerHTML = '';
            if (!items.length) {
                hide();
                return;
            }
            items.forEach(function (t, i) {
                var li = document.createElement('li');
                li.setAttribute('role', 'option');
                li.textContent = t;
                li.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    input.value = t;
                    hide();
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                });
                dd.appendChild(li);
            });
            dd.classList.add('is-open');
        }
        function run(q) {
            fetch('index.php?page=search_autocomplete&field=' + encodeURIComponent(field) + '&q=' + encodeURIComponent(q))
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    items = (data && data.items) ? data.items : [];
                    render();
                })
                .catch(function () { items = []; hide(); });
        }
        input.addEventListener('input', function () {
            clearTimeout(timer);
            var v = input.value.trim();
            timer = setTimeout(function () { run(v); }, 200);
        });
        input.addEventListener('focus', function () {
            run(input.value.trim());
        });
        input.addEventListener('blur', function () {
            setTimeout(hide, 180);
        });
        document.addEventListener('click', function (e) {
            if (!wrap.contains(e.target)) hide();
        });
    }
    document.querySelectorAll('.js-ac').forEach(attachAc);

    /* Flatpickr dd-mm-yyyy + calendar button */
    if (typeof flatpickr === 'function') {
        var fpMap = {};
        document.querySelectorAll('.js-search-date-input').forEach(function (inp) {
            if (inp._fp) return;
            var fp = flatpickr(inp, {
                dateFormat: 'd-m-Y',
                allowInput: true,
                clickOpens: true,
                animate: true
            });
            inp._fp = fp;
            fpMap[inp.id] = fp;
        });
        document.querySelectorAll('.js-search-date-cal').forEach(function (btn) {
            var tid = btn.getAttribute('data-target');
            if (!tid) return;
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var fp = fpMap[tid];
                if (fp) fp.open();
            });
        });
    }
})();
</script>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
