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

<div class="flex gap-6 w-full">
    <aside class="hidden lg:block w-72 shrink-0">
        <?php require __DIR__ . '/sidebar.php'; ?>
    </aside>

    <main class="flex-1 min-w-0 px-4 max-w-6xl">
        <div class="space-y-4">
            <?php $breadcrumbCurrent = 'Search';
            require __DIR__ . '/../../includes/breadcrumb.php'; ?>

            <?php if ($flash !== null): ?>
                <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-warning' ?> text-sm">
                    <?= h((string) $flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body">
                    <h3 class="card-title text-lg" style="color:#009900"><?= h($pageTitle) ?></h3>
                    <div class="rounded-box p-5 border border-warning/40" style="background:#ffffe8">
                        <form method="post" action="index.php?page=search&amp;mode=<?= rawurlencode($mode) ?>" class="space-y-4">
                            <?php require $formFile; ?>
                            <div class="pt-2">
                                <button type="submit" class="btn btn-success text-white">Search</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <?php if ($searchOutcome !== null): ?>
                <?php if (!empty($searchOutcome['error'])): ?>
                    <div class="alert alert-warning text-sm"><?= h((string) $searchOutcome['error']) ?></div>
                    <?php else: ?>
                        <?php
                        $variant = (string) $searchOutcome['variant'];
                        $rows = $searchOutcome['rows'];
                        $groups = $searchOutcome['groups'] ?? null;
                        $hasNested = $variant === 'nested' && is_array($groups) && $groups !== [];
                        $hasFlat = $rows !== [];
                        if (!$hasNested && !$hasFlat): ?>
                        <p class="text-sm text-base-content/80 py-4">No result found.</p>
                    <?php else:
                        $redirect = $redirectBack;
                        require __DIR__ . '/results_table.php';
                        ?>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
(function () {
    var timer = null;
    function attach(input) {
        var field = input.getAttribute('data-ac-field');
        if (!field) return;
        var listId = 'ac-list-' + field + '-' + Math.random().toString(36).slice(2);
        var dl = document.createElement('datalist');
        dl.id = listId;
        input.setAttribute('list', listId);
        input.parentNode.appendChild(dl);
        function run(q) {
            fetch('index.php?page=search_autocomplete&field=' + encodeURIComponent(field) + '&q=' + encodeURIComponent(q))
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data || !data.items) return;
                    dl.innerHTML = '';
                    data.items.forEach(function (t) {
                        var opt = document.createElement('option');
                        opt.value = t;
                        dl.appendChild(opt);
                    });
                })
                .catch(function () {});
        }
        input.addEventListener('input', function () {
            clearTimeout(timer);
            var v = input.value.trim();
            timer = setTimeout(function () { run(v); }, 200);
        });
        run('');
    }
    document.querySelectorAll('.js-ac').forEach(attach);
})();
</script>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
