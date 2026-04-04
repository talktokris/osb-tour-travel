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

/**
 * Inline SVG icons (stroke, currentColor) for result cards.
 */
function file_results_icon_svg(string $name): string
{
    $a = 'xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="file-res-icon-svg" aria-hidden="true" focusable="false"';
    switch ($name) {
        case 'route':
            return '<svg ' . $a . '><circle cx="6" cy="19" r="3"/><path d="M9 19h8.5a1.5 1.5 0 0 0 0-3H9a3 3 0 0 1-3-3V8"/><circle cx="18" cy="5" r="3"/></svg>';
        case 'van':
            return '<svg ' . $a . '><path d="M8 6v6"/><path d="M15 6v6"/><path d="M2 12h19.5"/><path d="M17.5 12 15 18H3l-2.5-6"/><path d="M9.5 12H12"/><path d="M17.5 12 20 18"/></svg>';
        case 'luxury':
            return '<svg ' . $a . '><path d="M12 2v4"/><path d="m16.2 7.8.7.7"/><path d="M18 12h4"/><path d="m16.2 16.2.7.7"/><path d="M12 18v4"/><path d="m7.8 16.2-.7.7"/><path d="M6 12H2"/><path d="m7.8 7.8-.7.7"/><circle cx="12" cy="12" r="3"/></svg>';
        case 'users':
            return '<svg ' . $a . '><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>';
        case 'price':
            return '<svg ' . $a . '><rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>';
        case 'car':
        default:
            return '<svg ' . $a . '><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-.1-1.9-.2c-.5-.2-.9-.5-1.1-.9L12 5H5L3 7v12c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>';
    }
}

function file_results_vehicle_icon_key(string $vehicleType): string
{
    $t = strtolower($vehicleType);
    if (str_contains($t, 'luxury') || str_contains($t, 'limo') || str_contains($t, 'premium')) {
        return 'luxury';
    }
    if (str_contains($t, 'van') || str_contains($t, 'mini') || str_contains($t, 'bus') || str_contains($t, 'coach')) {
        return 'van';
    }
    return 'car';
}

function file_results_format_price_display(string $raw): string
{
    if ($raw === '' || !is_numeric($raw)) {
        return $raw;
    }
    return number_format((float) $raw, 0, '.', ',');
}

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
    --file-res-teal: #1a6b5c;
    --file-res-teal-soft: rgba(26, 107, 92, 0.12);
    --file-res-rose: #fce4ec;
    --file-res-rose-deep: #f8bbd9;
    border: 1px solid rgba(226, 184, 200, 0.85);
    background:
        linear-gradient(145deg, rgba(255, 240, 246, 0.95) 0%, rgba(255, 228, 236, 0.9) 45%, rgba(252, 228, 236, 0.85) 100%);
    padding: 1.25rem 1.25rem 1.5rem;
    border-radius: 12px;
    box-shadow: 0 8px 28px rgba(26, 107, 92, 0.06);
}
.file-res-results__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.5rem 1rem;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid rgba(26, 107, 92, 0.15);
}
.file-res-results__title {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1.05rem;
    font-weight: 700;
    color: #0d5c4a;
    margin: 0;
}
.file-res-results__title .file-res-icon-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 10px;
    background: linear-gradient(135deg, #1a6b5c, #0f766e);
    color: #fff;
    box-shadow: 0 2px 8px rgba(26, 107, 92, 0.35);
}
.file-res-results__count {
    font-size: 0.8125rem;
    font-weight: 600;
    color: #64748b;
    background: rgba(255, 255, 255, 0.75);
    padding: 0.35rem 0.75rem;
    border-radius: 9999px;
    border: 1px solid var(--file-res-teal-soft);
}
.file-res-card-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(min(100%, 320px), 1fr));
    gap: 1rem;
}
.file-res-card {
    position: relative;
    display: flex;
    gap: 0;
    background: #fff;
    border-radius: 14px;
    border: 1px solid rgba(226, 232, 240, 0.95);
    box-shadow: 0 4px 18px rgba(15, 23, 42, 0.06);
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.file-res-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 32px rgba(15, 23, 42, 0.1);
}
.file-res-card__rail {
    width: 5px;
    flex-shrink: 0;
    background: linear-gradient(180deg, #1a6b5c, #14b8a6);
}
.file-res-card__inner {
    flex: 1;
    min-width: 0;
    padding: 1rem 1rem 1rem 0.85rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.file-res-card__top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.5rem;
}
.file-res-card__badge {
    flex-shrink: 0;
    font-size: 0.6875rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: #0f766e;
    background: rgba(20, 184, 166, 0.12);
    border: 1px solid rgba(20, 184, 166, 0.28);
    padding: 0.2rem 0.5rem;
    border-radius: 6px;
}
.file-res-card__service {
    font-size: 0.9375rem;
    font-weight: 600;
    line-height: 1.35;
    color: #0f172a;
    margin: 0;
    display: flex;
    align-items: flex-start;
    gap: 0.45rem;
}
.file-res-card__service .file-res-icon-wrap--muted {
    flex-shrink: 0;
    margin-top: 0.1rem;
    color: #1a6b5c;
    opacity: 0.85;
}
.file-res-card__chips {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.file-res-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.8125rem;
    font-weight: 600;
    color: #334155;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 0.35rem 0.55rem;
    border-radius: 8px;
}
.file-res-chip--price {
    color: #0f766e;
    background: rgba(20, 184, 166, 0.08);
    border-color: rgba(20, 184, 166, 0.22);
    font-variant-numeric: tabular-nums;
}
.file-res-chip--luxury {
    color: #92400e;
    background: linear-gradient(135deg, rgba(253, 230, 138, 0.35), rgba(251, 191, 36, 0.15));
    border-color: rgba(217, 119, 6, 0.35);
}
.file-res-chip .file-res-icon-svg {
    flex-shrink: 0;
    opacity: 0.88;
}
.file-res-card__action {
    margin-top: auto;
    padding-top: 0.25rem;
}
.file-res-card__action .btn {
    width: 100%;
    font-weight: 700;
    border-radius: 10px;
    min-height: 2.5rem;
}
.file-res-empty {
    text-align: center;
    padding: 2rem 1.25rem;
    background: rgba(255, 255, 255, 0.65);
    border-radius: 12px;
    border: 1px dashed rgba(26, 107, 92, 0.25);
}
.file-res-empty__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 3rem;
    height: 3rem;
    margin-bottom: 0.75rem;
    border-radius: 50%;
    background: rgba(26, 107, 92, 0.1);
    color: #1a6b5c;
}
.file-res-empty p {
    margin: 0;
    font-weight: 600;
    color: #475569;
}
.file-res-icon-svg { display: block; }
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
                <div class="file-res-results__head">
                    <h3 class="file-res-results__title">
                        <span class="file-res-icon-wrap" aria-hidden="true"><?= file_results_icon_svg('route') ?></span>
                        Results
                    </h3>
                    <?php if ($results !== []): ?>
                        <span class="file-res-results__count"><?= count($results) ?> service<?= count($results) === 1 ? '' : 's' ?> found</span>
                    <?php endif; ?>
                </div>
                <?php if ($results === []): ?>
                    <div class="file-res-empty">
                        <div class="file-res-empty__icon" aria-hidden="true"><?= file_results_icon_svg('route') ?></div>
                        <p>No result found for your search.</p>
                        <p class="text-sm font-normal text-base-content/60 mt-2">Try adjusting locations or date, then search again.</p>
                    </div>
                <?php else: ?>
                    <?php
                    $adults = (int) $c['adults'];
                    $children = (int) $c['children'];
                    $paxDisp = h((string) $c['no_of_pax']);
                    ?>
                    <div class="file-res-card-grid">
                        <?php $n = 1; foreach ($results as $row): ?>
                            <?php
                            $pr = file_module_compute_prices($row, $adults, $children);
                            $sid = (int) ($row['service_id'] ?? 0);
                            $svcTitle = (string) ($row['service_name_english'] ?? '');
                            $vtype = (string) ($row['vehicle_type'] ?? '');
                            $vIcon = file_results_vehicle_icon_key($vtype);
                            $priceDisp = file_results_format_price_display($pr['selling']);
                            $vehChipClass = $vIcon === 'luxury' ? ' file-res-chip--luxury' : '';
                            $vehSvg = $vIcon === 'van' ? 'van' : ($vIcon === 'luxury' ? 'luxury' : 'car');
                            ?>
                            <article class="file-res-card">
                                <div class="file-res-card__rail" aria-hidden="true"></div>
                                <div class="file-res-card__inner">
                                    <div class="file-res-card__top">
                                        <p class="file-res-card__service">
                                            <span class="file-res-icon-wrap--muted"><?= file_results_icon_svg('route') ?></span>
                                            <span><?= h($svcTitle) ?></span>
                                        </p>
                                        <span class="file-res-card__badge">#<?= $n ?></span>
                                    </div>
                                    <div class="file-res-card__chips">
                                        <span class="file-res-chip<?= $vehChipClass ?>" title="Vehicle type">
                                            <?= file_results_icon_svg($vehSvg) ?>
                                            <?= h($vtype !== '' ? $vtype : 'Vehicle') ?>
                                        </span>
                                        <span class="file-res-chip file-res-chip--price" title="Total price">
                                            <?= file_results_icon_svg('price') ?>
                                            RM <?= h($priceDisp) ?>
                                        </span>
                                        <span class="file-res-chip" title="Maximum passengers">
                                            <?= file_results_icon_svg('users') ?>
                                            Max <?= $paxDisp ?> pax
                                        </span>
                                    </div>
                                    <div class="file-res-card__action">
                                        <a class="btn btn-success text-white shadow-md hover:shadow-lg" href="index.php?page=file_book&amp;service_id=<?= $sid ?>">Book now</a>
                                    </div>
                                </div>
                            </article>
                            <?php $n++; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
