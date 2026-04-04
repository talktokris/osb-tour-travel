<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/sms_module_service.php';

require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';

$csrf = sms_module_csrf_token();
$flash = sms_module_flash_get();

$fromDateInput = '';
$searchYmd = null;
$previews = [];
$searchError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string) ($_POST['_token'] ?? '');
    $action = (string) ($_POST['sms_action'] ?? '');

    if ($action === 'enqueue') {
        if (!sms_module_csrf_validate($token)) {
            $searchError = 'Invalid request token.';
        } else {
            $ymd = trim((string) ($_POST['service_ymd'] ?? ''));
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $ymd)) {
                $searchError = 'Invalid service date for send.';
            } else {
                $result = sms_module_enqueue_service_date($mysqli, $ymd);
                $parts = ['Queued ' . $result['queued'] . ' message(s).'];
                if ($result['skipped'] > 0) {
                    $parts[] = 'Skipped ' . $result['skipped'] . ' row(s) (missing or short mobile).';
                }
                if ($result['errors'] !== []) {
                    $parts[] = implode(' ', array_slice($result['errors'], 0, 3));
                }
                sms_module_flash_set('success', implode(' ', $parts));
                header('Location: index.php?page=sms');
                exit;
            }
        }
    } elseif ($action === 'search') {
        $fromDateInput = trim((string) ($_POST['from_date'] ?? ''));
        if (!sms_module_csrf_validate($token)) {
            $searchError = 'Invalid request token.';
        } else {
            $searchYmd = sms_module_parse_service_date_input($fromDateInput);
            if ($searchYmd === null) {
                $searchError = 'Enter a valid date (dd-mm-yyyy or use the picker).';
            } else {
                $templates = sms_module_load_templates($mysqli);
                foreach (sms_module_file_entries_for_service_date($mysqli, $searchYmd) as $row) {
                    $previews[] = sms_module_build_row_messages($mysqli, $templates, $row);
                }
            }
        }
    }
}

require __DIR__ . '/panel_styles.php';
?>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<style>
    /* User-edit style: date text field with calendar control inside the right edge (like “Date of Birth”). */
    .sms-send-sms-compact {
        width: fit-content;
        max-width: 100%;
    }
    .sms-dob-input-wrap {
        position: relative;
        flex: 1 1 auto;
        min-width: 0;
        max-width: 12.75rem;
    }
    .sms-dob-input-wrap .sms-dob-input {
        width: 100%;
        height: 2.25rem;
        min-height: 2.25rem;
        padding-left: 0.65rem;
        padding-right: 2.35rem;
        font-size: 0.875rem;
        line-height: 1.25rem;
        border: 1px solid color-mix(in oklab, var(--color-base-content, #64748b) 22%, transparent);
        border-radius: var(--rounded-btn, 0.5rem);
        background: #fff;
        box-sizing: border-box;
    }
    .sms-dob-input-wrap .sms-dob-input:focus {
        outline: 2px solid color-mix(in oklab, var(--color-primary, #2563eb) 35%, transparent);
        outline-offset: 1px;
        border-color: var(--color-primary, #2563eb);
    }
    .sms-dob-cal-btn {
        position: absolute;
        right: 3px;
        top: 50%;
        transform: translateY(-50%);
        width: 2rem;
        height: 1.75rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0;
        padding: 0;
        border: 0;
        border-radius: 0.25rem;
        background: transparent;
        cursor: pointer;
        color: #475569;
    }
    .sms-dob-cal-btn:hover {
        background: color-mix(in oklab, var(--color-base-content, #64748b) 10%, transparent);
        color: #1e293b;
    }
    .sms-dob-cal-btn:focus-visible {
        outline: 2px solid var(--color-primary, #2563eb);
        outline-offset: 1px;
    }
    .sms-send-sms-actions {
        display: flex;
        flex-wrap: nowrap;
        align-items: center;
        gap: 0.75rem;
        width: 100%;
        max-width: 19.5rem;
    }
    .sms-send-sms-actions .sms-search-btn {
        flex: 0 0 auto;
        min-width: 5.25rem;
        height: 2.25rem;
        min-height: 2.25rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
    }
</style>
<script>
$(function () {
    var $inp = $('#sms-from-date');
    $inp.datepicker({
        dateFormat: 'dd-mm-yy',
        changeMonth: true,
        changeYear: true,
        showOn: 'focus'
    });
    $('#sms-calendar-inline-btn').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $inp.datepicker('show');
    });
});
</script>

<div class="flex gap-8 lg:gap-10 w-full max-w-none px-3 sm:px-5 lg:px-6 pb-6">
    <aside class="hidden lg:block w-72 shrink-0">
        <?php require __DIR__ . '/sidebar.php'; ?>
    </aside>

    <main class="flex-1 min-w-0 lg:pl-2">
        <div class="space-y-4">
            <?php $breadcrumbCurrent = 'Send SMS list'; require __DIR__ . '/../../includes/breadcrumb.php'; ?>

            <?php if ($flash): ?>
                <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-error' ?> shadow-sm">
                    <span><?= h((string) $flash['message']) ?></span>
                </div>
            <?php endif; ?>
            <?php if ($searchError !== ''): ?>
                <div class="alert alert-error shadow-sm"><span><?= h($searchError) ?></span></div>
            <?php endif; ?>

            <div class="sms-legacy-panel rounded-md px-5 py-5 sm:px-6 sm:py-5 w-fit max-w-full shadow-sm sms-send-sms-compact">
                <h2 class="sms-legacy-title mb-4 text-base">Send SMS List</h2>
                <form method="post" action="index.php?page=sms">
                    <input type="hidden" name="_token" value="<?= h($csrf) ?>">
                    <input type="hidden" name="sms_action" value="search">
                    <div class="form-control w-full p-0">
                        <label class="label py-0 pb-2 justify-start px-0 min-h-0 h-auto" for="sms-from-date"><span class="label-text text-sm font-medium">Date :</span></label>
                        <div class="sms-send-sms-actions">
                            <div class="sms-dob-input-wrap">
                                <input type="text" name="from_date" id="sms-from-date" autocomplete="off"
                                       class="sms-dob-input"
                                       value="<?= h($fromDateInput) ?>" placeholder="dd-mm-yy">
                                <button type="button" class="sms-dob-cal-btn" id="sms-calendar-inline-btn" title="Show date picker" aria-label="Show date picker">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                </button>
                            </div>
                            <button type="submit" class="btn btn-sm sms-legacy-btn sms-search-btn px-5">Search</button>
                        </div>
                    </div>
                </form>
            </div>

            <?php
            $showSearchResults = $_SERVER['REQUEST_METHOD'] === 'POST'
                && (string) ($_POST['sms_action'] ?? '') === 'search'
                && $searchYmd !== null
                && $searchError === '';
            ?>
            <?php if ($showSearchResults): ?>
                <div class="space-y-3">
                    <h3 class="text-lg font-semibold text-base-content">SMS List for Service Date: <?= h($searchYmd) ?></h3>
                    <?php if ($previews === []): ?>
                        <p class="text-error font-medium">No Result Found</p>
                    <?php else: ?>
                        <form method="post" action="index.php?page=sms" class="space-y-4">
                            <input type="hidden" name="_token" value="<?= h($csrf) ?>">
                            <input type="hidden" name="sms_action" value="enqueue">
                            <input type="hidden" name="service_ymd" value="<?= h($searchYmd) ?>">
                            <div class="flex justify-end">
                                <button type="submit" class="btn btn-sm sms-legacy-btn px-6">Send list</button>
                            </div>
                        </form>
                        <div class="space-y-4 border border-base-300 rounded-box bg-base-100 p-4">
                            <?php foreach ($previews as $p): ?>
                                <div class="border-b border-base-200 pb-3 last:border-0">
                                    <p class="text-sm font-medium">Name: <?= h($p['name']) ?> — Mobile No: <?= h($p['mobile']) ?></p>
                                    <p class="text-sm text-base-content/90 whitespace-pre-wrap mt-1">Message: <?= h(normalize_arabic_text($p['message_preview'])) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
