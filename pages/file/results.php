<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/file_module_service.php';

$currentPage = 'file_results';

if (!file_module_has_agent()) {
    file_module_render_agent_required();
}

/**
 * @param array<string, string> $c
 */
function file_results_criteria_incomplete(array $c): bool
{
    $toCountry = trim((string) ($c['to_country'] ?? ''));
    if ($toCountry === '') {
        $toCountry = trim((string) ($c['from_country'] ?? ''));
    }
    return trim((string) ($c['from_country'] ?? '')) === ''
        || trim((string) ($c['from_city'] ?? '')) === ''
        || trim((string) ($c['from_location'] ?? '')) === ''
        || trim((string) ($c['to_city'] ?? '')) === ''
        || trim((string) ($c['to_location'] ?? '')) === ''
        || trim((string) ($c['service_date'] ?? '')) === ''
        || trim((string) ($c['no_of_pax'] ?? '')) === '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_do_search'])) {
    $token = (string) ($_POST['_token'] ?? '');
    if (!file_module_csrf_validate($token)) {
        file_module_flash_set('warning', 'Invalid request token.');
        header('Location: index.php?page=file');
        exit;
    }
    $post = [
        'from_country' => (string) ($_POST['from_country'] ?? ''),
        'from_city' => (string) ($_POST['from_city'] ?? ''),
        'from_location' => (string) ($_POST['from_location'] ?? ''),
        'from_zone' => (string) ($_POST['from_zone'] ?? ''),
        'to_country' => (string) ($_POST['to_country'] ?? ''),
        'to_city' => (string) ($_POST['to_city'] ?? ''),
        'to_location' => (string) ($_POST['to_location'] ?? ''),
        'to_zone' => (string) ($_POST['to_zone'] ?? ''),
        'service_name' => (string) ($_POST['service_name'] ?? ''),
        'vehicle_type' => (string) ($_POST['vehicle_type'] ?? ''),
        'no_of_vachile' => (string) ($_POST['no_of_vachile'] ?? '1'),
        'service_cat' => (string) ($_POST['service_cat'] ?? 'Private'),
        'service_date' => (string) ($_POST['service_date'] ?? ''),
        'adults' => (string) ($_POST['adults'] ?? ''),
        'children' => (string) ($_POST['children'] ?? ''),
        'no_of_pax' => (string) ($_POST['no_of_pax'] ?? ''),
    ];
    if ($post['to_country'] === '') {
        $post['to_country'] = $post['from_country'];
    }
    if ($post['from_country'] === '' || $post['from_city'] === '' || $post['from_location'] === ''
        || $post['to_city'] === '' || $post['to_location'] === '' || $post['service_date'] === ''
        || $post['no_of_pax'] === '') {
        file_module_flash_set('warning', 'Please complete country, pick-up, drop-off, service date, and pax.');
        header('Location: index.php?page=file');
        exit;
    }
    if (!in_array($post['service_cat'], ['Private', 'SIC'], true)) {
        file_module_flash_set('warning', 'Choose Private or SIC.');
        header('Location: index.php?page=file');
        exit;
    }
    file_module_save_criteria($post);
    header('Location: index.php?page=file_results');
    exit;
}

$state = file_module_state();
$c = $state['criteria'];
if (file_results_criteria_incomplete($c)) {
    header('Location: index.php?page=file');
    exit;
}

$results = file_module_search_services($mysqli, $c);
$flash = file_module_flash_get();

/** @param array<string, string> $c */
$fmtLocZone = static function (array $c, string $locKey, string $zoneKey): string {
    $loc = trim((string) ($c[$locKey] ?? ''));
    $z = trim((string) ($c[$zoneKey] ?? ''));
    if ($loc === '') {
        return '';
    }
    return $z !== '' ? $loc . ' / ' . $z : $loc;
};

$fromLocZ = $fmtLocZone($c, 'from_location', 'from_zone');
$toLocZ = $fmtLocZone($c, 'to_location', 'to_zone');
$svcName = trim((string) ($c['service_name'] ?? ''));

require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';
?>

<style>
.file-res-selected {
    border: 1px solid #1a6b5c;
    background: #fffce8;
    padding: 8px 12px 10px;
    margin-bottom: 12px;
}
.file-res-selected__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 8px;
    border-bottom: 1px solid rgba(26, 107, 92, 0.2);
    padding-bottom: 6px;
}
.file-res-selected__title {
    color: #00a651;
    font-size: 1rem;
    font-weight: 700;
    margin: 0;
}
.file-res-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px 24px;
    font-size: 12px;
}
@media (max-width: 640px) {
    .file-res-grid { grid-template-columns: 1fr; }
}
.file-res-row {
    display: grid;
    grid-template-columns: minmax(7rem, auto) 1fr;
    gap: 8px;
    align-items: baseline;
}
.file-res-row strong { color: #1e293b; }
.file-res-results {
    border: 1px solid #e2b8c8;
    background: rgba(255, 204, 223, 0.35);
    padding: 1rem;
    border-radius: 0.125rem;
    overflow-x: auto;
}
</style>

<div class="flex gap-6 w-full pb-6">
    <aside class="hidden lg:block w-72 shrink-0"><?php require __DIR__ . '/sidebar.php'; ?></aside>
    <main class="flex-1 min-w-0">
        <div class="space-y-4">
            <?php $breadcrumbCurrent = 'Transfer search results'; require __DIR__ . '/../../includes/breadcrumb.php'; ?>

            <?php if ($flash): ?>
                <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : ($flash['type'] === 'warning' ? 'alert-warning' : 'alert-info') ?>"><span><?= h($flash['message']) ?></span></div>
            <?php endif; ?>

            <div class="file-res-selected">
                <div class="file-res-selected__head">
                    <h2 class="file-res-selected__title">Your Selected</h2>
                    <a href="index.php?page=file" class="link link-primary font-medium text-sm">Edit</a>
                </div>
                <div class="file-res-grid">
                    <div class="space-y-1">
                        <div class="file-res-row"><strong>From Country :</strong><span><?= h((string) ($c['from_country'] ?? '')) ?></span></div>
                        <div class="file-res-row"><strong>Pick Up :</strong><span><?= h((string) ($c['from_city'] ?? '')) ?></span></div>
                        <div class="file-res-row"><strong>Drop Off :</strong><span><?= h((string) ($c['to_city'] ?? '')) ?></span></div>
                        <div class="file-res-row"><strong>No of Adults :</strong><span><?= h((string) ($c['adults'] ?? '')) ?></span></div>
                        <div class="file-res-row"><strong>Service Name :</strong><span><?= $svcName !== '' ? h($svcName) : '—' ?></span></div>
                    </div>
                    <div class="space-y-1">
                        <div class="file-res-row">
                            <strong>Service Date :</strong>
                            <span><?= h((string) ($c['service_date'] ?? '')) ?> <strong>( <?= h((string) ($c['service_cat'] ?? 'Private')) ?> )</strong></span>
                        </div>
                        <div class="file-res-row"><strong>Location / Zone :</strong><span><?= h($fromLocZ) ?></span></div>
                        <div class="file-res-row"><strong>Location / Zone :</strong><span><?= h($toLocZ) ?></span></div>
                        <div class="file-res-row"><strong>No of Children :</strong><span><?= h((string) ($c['children'] ?? '')) ?></span></div>
                        <div class="file-res-row"><strong>Total Pax :</strong><span><?= h((string) ($c['no_of_pax'] ?? '')) ?></span></div>
                    </div>
                </div>
            </div>

            <div class="file-res-results">
                <h3 class="font-semibold text-success mb-2">Results</h3>
                <?php if ($results === []): ?>
                    <p class="text-success font-medium">No result found</p>
                <?php else: ?>
                    <?php
                    $adults = (int) $c['adults'];
                    $children = (int) $c['children'];
                    ?>
                    <table class="table table-sm table-zebra bg-base-100">
                        <thead><tr><th>No.</th><th>Service</th><th>Vehicle</th><th>Price</th><th>Max pax</th><th></th></tr></thead>
                        <tbody>
                        <?php $n = 1; foreach ($results as $row): ?>
                            <?php
                            $pr = file_module_compute_prices($row, $adults, $children);
                            $sid = (int) ($row['service_id'] ?? 0);
                            ?>
                            <tr>
                                <td><?= $n++ ?></td>
                                <td><?= h((string) ($row['service_name_english'] ?? '')) ?></td>
                                <td><?= h((string) ($row['vehicle_type'] ?? '')) ?></td>
                                <td class="font-semibold"><?= h($pr['selling']) ?></td>
                                <td><?= h($c['no_of_pax']) ?></td>
                                <td><a class="btn btn-xs btn-success text-white" href="index.php?page=file_book&amp;service_id=<?= $sid ?>">Book now</a></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
