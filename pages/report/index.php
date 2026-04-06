<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/file_module_service.php';
require_once __DIR__ . '/../../includes/report_module_service.php';

$currentPage = 'report';
$mode = report_module_normalize_mode((string) ($_GET['mode'] ?? 'agent'));

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
$reportOutcome = null;
$reportError = null;

if (($_SERVER['REQUEST_METHOD'] === 'POST') && (($fv['report_submit'] ?? '') === '1')) {
    $mode = report_module_normalize_mode((string) ($fv['mode'] ?? $mode));
    $token = (string) ($fv['csrf'] ?? '');
    if (!file_module_csrf_validate($token)) {
        $reportError = 'Invalid session token. Please refresh and try again.';
    } else {
        $run = report_module_run($mysqli, $mode, $fv);
        if ($run['ok']) {
            $reportOutcome = $run;
        } else {
            $reportError = (string) ($run['error'] ?? 'Search failed.');
        }
    }
}

$flash = file_module_flash_get();
$csrf = file_module_csrf_token();

$titles = [
    'agent' => 'Report by Agent',
    'supplier' => 'Search by Supplier',
    'vehicle_type' => 'Search by Vehicle Type',
    'private_sic' => 'Search by Tour Type',
    'driver_name' => 'Search by Driver Name',
    'vehicle_no' => 'Search by Vehicle No.',
    'city' => 'Search by City',
    'tour_arrival' => 'Search by Arrival',
    'statement_agent' => 'Report by Agent',
    'statement_supplier' => 'Report by Supplier',
];

$pageTitle = $titles[$mode] ?? 'Report';

$agents = report_module_agent_names($mysqli);
$suppliers = report_module_supplier_names($mysqli);
$serviceTypes = report_module_service_type_names($mysqli);
$tourTypes = report_module_tour_type_names($mysqli);
$vehicleTypes = report_module_vehicle_type_names($mysqli);
$drivers = report_module_driver_names($mysqli);
$vehicleNos = report_module_vehicle_numbers($mysqli);
$cities = report_module_city_names($mysqli);

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
.report-module-scope .report-form-yellow {
    background: #ffffe8;
    box-sizing: border-box;
    padding: 1.35rem 1.5rem 1.45rem;
    border-radius: var(--rounded-box, 1rem);
}
@media (min-width: 640px) {
    .report-module-scope .report-form-yellow {
        padding: 1.55rem 1.85rem 1.6rem;
    }
}
.report-module-scope .report-form-fieldstack {
    display: flex;
    flex-direction: column;
    gap: 1.15rem;
}
.report-module-scope .report-form-fieldstack .form-control {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}
.report-module-scope .report-form-shell {
    max-width: 42rem;
    width: 100%;
    margin-left: auto;
    margin-right: auto;
}
/* Print: TCPDF-style PDF export is planned as phase 2; use browser print for now. */
@media print {
    body * {
        visibility: hidden;
    }
    .report-print-area,
    .report-print-area * {
        visibility: visible;
    }
    .report-print-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        padding: 0.5rem;
    }
    .report-print-area table {
        font-size: 9pt;
    }
}
</style>

<div class="flex gap-6 w-full report-module-scope">
    <aside class="hidden lg:block w-72 shrink-0 report-no-print">
        <?php require __DIR__ . '/sidebar.php'; ?>
    </aside>

    <main class="flex-1 min-w-0 px-4">
        <div class="space-y-4 w-full min-w-0">
            <?php $breadcrumbCurrent = 'Report — ' . $pageTitle;
            require __DIR__ . '/../../includes/breadcrumb.php'; ?>

            <?php if ($flash !== null): ?>
                <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-warning' ?> text-sm report-no-print">
                    <?= h((string) $flash['message']) ?>
                </div>
            <?php endif; ?>

            <?php if ($reportError !== null): ?>
                <div class="alert alert-warning text-sm report-no-print"><?= h($reportError) ?></div>
            <?php endif; ?>

            <div class="card bg-base-100 shadow-xl border border-base-300 w-full report-no-print">
                <div class="card-body">
                    <h3 class="card-title text-lg" style="color:#009900"><?= h($pageTitle) ?></h3>
                    <div class="report-form-shell">
                        <div class="rounded-box border border-warning/40 report-form-yellow">
                            <form method="post" action="index.php?page=report&amp;mode=<?= h(rawurlencode($mode)) ?>" class="report-form-fieldstack">
                                <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                                <input type="hidden" name="report_submit" value="1">
                                <input type="hidden" name="mode" value="<?= h($mode) ?>">
                                <?php require $formFile; ?>
                                <div class="flex flex-wrap gap-3 justify-center pt-2">
                                    <button type="submit" class="btn btn-success btn-sm">Search</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($reportOutcome !== null && $reportOutcome['ok']): ?>
                <div class="flex justify-end report-no-print mb-2">
                    <button type="button" class="btn btn-success btn-sm" onclick="window.print()">Print</button>
                </div>
                <!-- Phase 2: optional TCPDF routes mirroring legacy tcpdf/examples/*_report_pdf.php -->
                <div id="report-results" class="report-print-area card bg-base-100 shadow border border-base-300">
                    <div class="card-body overflow-x-auto">
                        <?php if ($reportOutcome['kind'] === 'transfer'): ?>
                            <?php
                            $sections = $reportOutcome['transfer']['sections'] ?? [];
                            require __DIR__ . '/results_transfer.php';
                            ?>
                        <?php elseif ($reportOutcome['kind'] === 'statement_agent'): ?>
                            <?php
                            $stmtData = $reportOutcome['statement_agent'];
                            require __DIR__ . '/results_statement_agent.php';
                            ?>
                        <?php elseif ($reportOutcome['kind'] === 'statement_supplier'): ?>
                            <?php
                            $stmtData = $reportOutcome['statement_supplier'];
                            require __DIR__ . '/results_statement_supplier.php';
                            ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
(function () {
    if (typeof flatpickr !== 'function') return;
    document.querySelectorAll('.js-report-date-input').forEach(function (inp) {
        if (inp._fp) return;
        inp._fp = flatpickr(inp, {
            dateFormat: 'd-m-Y',
            allowInput: true,
            clickOpens: true
        });
    });
})();
</script>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
