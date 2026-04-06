<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/file_module_service.php';
require_once __DIR__ . '/../../includes/invoice_module_service.php';

if (!file_module_has_agent()) {
    file_module_render_agent_required();
}

$currentPage = 'invoice';
$mode = invoice_module_normalize_mode((string) ($_GET['mode'] ?? 'outstanding_agent'));

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
$outcome = null;
$invoiceRow = null;
$selectedRows = [];
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($fv['invoice_submit'] ?? '') === '1')) {
    $mode = invoice_module_normalize_mode((string) ($fv['mode'] ?? $mode));
    if (!file_module_csrf_validate((string) ($fv['csrf'] ?? ''))) {
        $error = 'Invalid session token. Please refresh and try again.';
    } else {
        if ($mode === 'outstanding_agent' || $mode === 'outstanding_supplier') {
            $outcome = invoice_module_run_invoice_list($mysqli, $fv, false);
        } elseif ($mode === 'paid_agent' || $mode === 'paid_supplier') {
            $outcome = invoice_module_run_invoice_list($mysqli, $fv, true);
        } elseif ($mode === 'statement_agent' || $mode === 'statement_supplier') {
            $outcome = invoice_module_run_statement($mysqli, $mode, $fv);
        } elseif ($mode === 'pay_single' && (($fv['do_save'] ?? '') === '1')) {
            $save = invoice_module_apply_single_payment($mysqli, $fv);
            if ($save['ok']) {
                file_module_flash_set('success', 'Data is Saved');
                $retMode = trim((string) ($fv['return_mode'] ?? ''));
                if ($retMode === '') {
                    $retMode = trim((string) ($fv['search_supplier'] ?? '')) !== '' ? 'outstanding_supplier' : 'outstanding_agent';
                }
                $q = http_build_query([
                    'page' => 'invoice',
                    'mode' => invoice_module_normalize_mode($retMode),
                    'search_agent' => (string) ($fv['search_agent'] ?? ''),
                    'search_supplier' => (string) ($fv['search_supplier'] ?? ''),
                    'search_ref' => (string) ($fv['search_ref'] ?? ''),
                    'from_date' => (string) ($fv['from_date'] ?? ''),
                    'to_date' => (string) ($fv['to_date'] ?? ''),
                ]);
                header('Location: index.php?' . $q);
                exit;
            }
            $error = (string) ($save['error'] ?? 'Could not save.');
        } elseif ($mode === 'pay_multiple' && (($fv['do_save'] ?? '') === '1')) {
            $save = invoice_module_apply_multi_payment($mysqli, $fv);
            if ($save['ok']) {
                file_module_flash_set('success', 'Data is Saved');
                $retMode = trim((string) ($fv['return_mode'] ?? ''));
                if ($retMode === '') {
                    $retMode = trim((string) ($fv['search_supplier'] ?? '')) !== '' ? 'outstanding_supplier' : 'outstanding_agent';
                }
                $q = http_build_query([
                    'page' => 'invoice',
                    'mode' => invoice_module_normalize_mode($retMode),
                    'search_agent' => (string) ($fv['search_agent'] ?? ''),
                    'search_supplier' => (string) ($fv['search_supplier'] ?? ''),
                    'search_ref' => (string) ($fv['search_ref'] ?? ''),
                    'from_date' => (string) ($fv['from_date'] ?? ''),
                    'to_date' => (string) ($fv['to_date'] ?? ''),
                ]);
                header('Location: index.php?' . $q);
                exit;
            }
            $error = (string) ($save['error'] ?? 'Could not save.');
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    foreach (['search_agent', 'search_supplier', 'search_ref', 'from_date', 'to_date', 'country', 'city', 'search_word'] as $k) {
        if (!isset($fv[$k]) && isset($_GET[$k]) && is_string($_GET[$k])) {
            $fv[$k] = trim($_GET[$k]);
        }
    }
}

if ($mode === 'pay_single' && $invoiceRow === null) {
    $invId = trim((string) ($fv['invoice_id'] ?? ($_GET['invoice_id'] ?? '')));
    if ($invId !== '') {
        $row = invoice_module_invoice_by_id($mysqli, $invId);
        if ($row !== null) {
            $invoiceRow = invoice_module_format_invoice_row($row);
            if (!isset($fv['return_mode'])) {
                $fv['return_mode'] = (string) ($_GET['return_mode'] ?? '');
            }
        }
    }
}


if ($outcome === null && ($mode === 'outstanding_agent' || $mode === 'outstanding_supplier')) {
    $hasDates = trim((string) ($fv['from_date'] ?? '')) !== '' && trim((string) ($fv['to_date'] ?? '')) !== '';
    if ($hasDates || trim((string) ($fv['search_agent'] ?? '')) !== '' || trim((string) ($fv['search_supplier'] ?? '')) !== '' || trim((string) ($fv['search_ref'] ?? '')) !== '') {
        $outcome = invoice_module_run_invoice_list($mysqli, $fv, false);
    }
}
if ($outcome === null && ($mode === 'paid_agent' || $mode === 'paid_supplier')) {
    $hasDates = trim((string) ($fv['from_date'] ?? '')) !== '' && trim((string) ($fv['to_date'] ?? '')) !== '';
    if ($hasDates || trim((string) ($fv['search_agent'] ?? '')) !== '' || trim((string) ($fv['search_supplier'] ?? '')) !== '' || trim((string) ($fv['search_ref'] ?? '')) !== '') {
        $outcome = invoice_module_run_invoice_list($mysqli, $fv, true);
    }
}
if ($outcome === null && ($mode === 'statement_agent' || $mode === 'statement_supplier') && trim((string) ($fv['search_word'] ?? '')) !== '') {
    $outcome = invoice_module_run_statement($mysqli, $mode, $fv);
}

if ($mode === 'pay_multiple') {
    $ids = [];
    if (isset($fv['selected_ids']) && is_array($fv['selected_ids'])) {
        foreach ($fv['selected_ids'] as $id) {
            if (is_string($id) && trim($id) !== '') {
                $ids[] = trim($id);
            }
        }
    } elseif (isset($fv['selected_invoice_ids'])) {
        $ids = array_values(array_filter(array_map('trim', explode('|', (string) $fv['selected_invoice_ids']))));
    }
    foreach ($ids as $id) {
        $r = invoice_module_invoice_by_id($mysqli, $id);
        if ($r !== null) {
            $selectedRows[] = invoice_module_format_invoice_row($r);
        }
    }
    if (!isset($fv['return_mode'])) {
        $fv['return_mode'] = trim((string) ($fv['search_supplier'] ?? '')) !== '' ? 'outstanding_supplier' : 'outstanding_agent';
    }
}

$countries = invoice_module_country_names($mysqli);
$cities = invoice_module_city_names($mysqli, (string) ($fv['country'] ?? ''));
$agents = invoice_module_agent_names($mysqli, (string) ($fv['country'] ?? ''), (string) ($fv['city'] ?? ''));
$suppliers = invoice_module_supplier_names($mysqli, (string) ($fv['country'] ?? ''), (string) ($fv['city'] ?? ''));
$invoiceRefs = invoice_module_invoice_refs($mysqli);

$titles = [
    'outstanding_agent' => 'Outstanding Invoice by Agent',
    'outstanding_supplier' => 'Outstanding Invoice by Supplier',
    'paid_agent' => 'Paid Invoice by Agent',
    'paid_supplier' => 'Paid Invoice by Supplier',
    'statement_agent' => 'Report by Agent',
    'statement_supplier' => 'Report by Supplier',
    'pay_single' => 'Payment Voucher',
    'pay_multiple' => 'Payment Voucher',
];
$pageTitle = $titles[$mode] ?? 'Invoice';
$formFile = __DIR__ . '/forms/' . $mode . '.php';
$flash = file_module_flash_get();
$csrf = file_module_csrf_token();

require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
.invoice-module-scope .invoice-form-yellow { background:#ffffe8; box-sizing:border-box; padding:1.35rem 1.5rem 1.45rem; border-radius:var(--rounded-box,1rem); }
.invoice-module-scope .invoice-form-fieldstack { display:flex; flex-direction:column; gap:1rem; }
.invoice-module-scope .invoice-form-shell { max-width:48rem; width:100%; margin-left:auto; margin-right:auto; }
.invoice-module-scope .voucher-shell { max-width:50rem; margin-left:auto; margin-right:auto; }
.invoice-module-scope .voucher-title { text-align:center; font-weight:700; font-size:1.08rem; color:#374151; margin-bottom:0.9rem; }
.invoice-module-scope .voucher-grid { display:grid; grid-template-columns: 1fr auto 16rem; gap:.55rem .8rem; align-items:center; max-width:34rem; margin-left:auto; }
.invoice-module-scope .voucher-grid .voucher-label { font-size:.76rem; color:#374151; text-align:right; font-weight:600; }
.invoice-module-scope .voucher-grid .voucher-field { grid-column:3/4; }
.invoice-module-scope .voucher-input { height:1.85rem; border:1px solid #bfc8d3; background:#fff; border-radius:0; width:100%; padding:.2rem .4rem; font-size:.78rem; }
.invoice-module-scope .voucher-table-wrap { margin-top:.95rem; border:1px solid #8db2d3; }
.invoice-module-scope .voucher-table { width:100%; border-collapse:collapse; font-size:.78rem; }
.invoice-module-scope .voucher-table thead th { background:#1b77b8; color:#fff; border:1px solid #7ea4c6; padding:.28rem .35rem; text-align:left; }
.invoice-module-scope .voucher-table tbody td { border:1px solid #bdd1e3; padding:.28rem .35rem; background:#fff; }
.invoice-module-scope .voucher-actions { display:flex; justify-content:flex-end; margin-top:.95rem; }
.invoice-module-scope .voucher-save { min-height:1.95rem; font-size:.76rem; border-radius:.35rem; padding:0 .95rem; }
</style>

<div class="flex gap-6 w-full invoice-module-scope">
    <aside class="hidden lg:block w-72 shrink-0">
        <?php require __DIR__ . '/sidebar.php'; ?>
    </aside>

    <main class="flex-1 min-w-0 px-4">
        <div class="space-y-4 w-full min-w-0">
            <?php $breadcrumbCurrent = 'Invoice — ' . $pageTitle;
            require __DIR__ . '/../../includes/breadcrumb.php'; ?>

            <?php if ($flash !== null): ?>
                <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-warning' ?> text-sm"><?= h((string) $flash['message']) ?></div>
            <?php endif; ?>
            <?php if ($error !== null): ?>
                <div class="alert alert-warning text-sm"><?= h($error) ?></div>
            <?php endif; ?>

            <?php if ($mode === 'pay_single' && $invoiceRow !== null): ?>
                <div class="card bg-base-100 shadow-xl border border-base-300">
                    <div class="card-body">
                        <h3 class="card-title text-lg" style="color:#009900"><?= h($pageTitle) ?></h3>
                        <div class="invoice-form-yellow border border-warning/40 rounded-box">
                            <?php require __DIR__ . '/pay_single.php'; ?>
                        </div>
                    </div>
                </div>
            <?php elseif ($mode === 'pay_multiple'): ?>
                <div class="card bg-base-100 shadow-xl border border-base-300">
                    <div class="card-body">
                        <h3 class="card-title text-lg" style="color:#009900"><?= h($pageTitle) ?></h3>
                        <div class="invoice-form-yellow border border-warning/40 rounded-box">
                            <?php if ($selectedRows === []): ?>
                                <p class="text-sm text-base-content/70">No invoices selected.</p>
                            <?php else: ?>
                                <?php require __DIR__ . '/pay_multiple.php'; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card bg-base-100 shadow-xl border border-base-300">
                    <div class="card-body">
                        <h3 class="card-title text-lg" style="color:#009900"><?= h($pageTitle) ?></h3>
                        <div class="invoice-form-shell">
                            <div class="invoice-form-yellow border border-warning/40 rounded-box">
                                <form method="post" action="index.php?page=invoice&amp;mode=<?= h(rawurlencode($mode)) ?>" class="invoice-form-fieldstack">
                                    <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                                    <input type="hidden" name="invoice_submit" value="1">
                                    <input type="hidden" name="mode" value="<?= h($mode) ?>">
                                    <?php if (is_file($formFile)) {
                                        require $formFile;
                                    } ?>
                                    <div class="flex flex-wrap gap-3 justify-center pt-2">
                                        <button type="submit" name="login" class="btn btn-success btn-sm">Search</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($outcome !== null && ($outcome['ok'] ?? false)): ?>
                    <div class="card bg-base-100 shadow border border-base-300">
                        <div class="card-body overflow-x-auto">
                            <?php if ($mode === 'outstanding_agent' || $mode === 'outstanding_supplier'): ?>
                                <?php $rows = $outcome['rows'] ?? []; $context = $outcome['context'] ?? []; require __DIR__ . '/results_outstanding.php'; ?>
                            <?php elseif ($mode === 'paid_agent' || $mode === 'paid_supplier'): ?>
                                <?php $rows = $outcome['rows'] ?? []; require __DIR__ . '/results_paid.php'; ?>
                            <?php elseif ($mode === 'statement_agent'): ?>
                                <div class="flex justify-end mb-3">
                                    <a class="btn btn-success btn-sm" target="_blank" href="index.php?page=invoice_pdf_statement_agent&amp;statement_agent=<?= h(rawurlencode((string) ($fv['search_word'] ?? '') . '|' . (string) ($fv['from_date'] ?? '') . '|' . (string) ($fv['to_date'] ?? ''))) ?>">Print</a>
                                </div>
                                <?php $stmtData = $outcome['statement_agent']; require __DIR__ . '/results_statement_agent.php'; ?>
                            <?php elseif ($mode === 'statement_supplier'): ?>
                                <div class="flex justify-end mb-3">
                                    <a class="btn btn-success btn-sm" target="_blank" href="index.php?page=invoice_pdf_statement_supplier&amp;statement_supplier=<?= h(rawurlencode((string) ($fv['search_word'] ?? '') . '|' . (string) ($fv['from_date'] ?? '') . '|' . (string) ($fv['to_date'] ?? ''))) ?>">Print</a>
                                </div>
                                <?php $stmtData = $outcome['statement_supplier']; require __DIR__ . '/results_statement_supplier.php'; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php elseif ($outcome !== null): ?>
                    <div class="alert alert-warning text-sm"><?= h((string) ($outcome['error'] ?? 'No Result found')) ?></div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
(function () {
  if (typeof flatpickr !== 'function') return;
  document.querySelectorAll('.js-invoice-date-input').forEach(function (inp) {
    if (inp._fp) return;
    inp._fp = flatpickr(inp, {dateFormat: 'Y-m-d', allowInput: true, clickOpens: true});
  });
})();
</script>
<?php require __DIR__ . '/../../includes/footer.php'; ?>

