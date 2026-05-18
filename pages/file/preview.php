<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/file_module_service.php';

$currentPage = 'file_preview';

if (!file_module_has_agent()) {
    file_module_render_agent_required();
}

$fcn = trim((string) ($_GET['file_count_no'] ?? ''));
$uname = (string) ($_SESSION['user_name'] ?? '');

if ($fcn === '' || !file_module_user_can_access_file_count($mysqli, $fcn, $uname)) {
    require __DIR__ . '/../../includes/header.php';
    require __DIR__ . '/../../includes/nav.php';
    echo '<div class="p-4"><div class="alert alert-warning">Invalid or inaccessible file.</div><a class="btn btn-sm" href="index.php?page=file">Home</a></div>';
    require __DIR__ . '/../../includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_confirm'])) {
    $token = (string) ($_POST['_token'] ?? '');
    if (!file_module_csrf_validate($token)) {
        file_module_flash_set('error', 'Invalid token.');
    } elseif (trim((string) ($_POST['file_count_no'] ?? '')) !== $fcn) {
        file_module_flash_set('error', 'Mismatch.');
    } else {
        file_module_set_all_on_request($mysqli, $fcn);
        header('Location: index.php?page=file_send_email&file_count_no=' . rawurlencode($fcn));
        exit;
    }
}

require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';

$csrf = file_module_csrf_token();
$flash = file_module_flash_get();
$rows = file_module_entries_for_count($mysqli, $fcn);
$headerRow = file_module_entry_header_for_count($mysqli, $fcn);
$invoices = file_module_invoices_for_count($mysqli, $fcn);
$total = 0.0;
foreach ($rows as $r) {
    $total += (float) ($r['selling_price'] ?? 0);
}
$head = $headerRow ?? ($rows[0] ?? []);
$fileNo = (string) ($head['file_no'] ?? '');
$fileNoDisplay = $fileNo !== '' ? $fileNo : $fcn;

$previewFmtDate = static function (string $ymd): string {
    $ymd = trim($ymd);
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $ymd)) {
        return file_module_format_date_ymd_to_dmy($ymd);
    }

    return $ymd;
};

$previewZone = static function (string $zone, string $loc): string {
    $z = trim($zone);
    if ($z !== '') {
        return $z;
    }

    return trim($loc);
};

/** Legacy search/file_booking_preview: supplier confirm sets conform_status, not book_status. */
$conformStatusLabel = static function (string $conform): string {
    if ($conform === 'Confirmed') {
        return 'Confirmed';
    }
    if ($conform === 'Cancel') {
        return 'Cancel';
    }

    return 'On Request';
};

$statusSummary = '—';
foreach ($rows as $r) {
    if ((string) ($r['conform_status'] ?? '') === 'Cancel') {
        continue;
    }
    $statusSummary = $conformStatusLabel((string) ($r['conform_status'] ?? ''));
}

$showConfirm = file_module_show_agent_confirm_button($rows);
$conformStatusFind = (string) ($headerRow['conform_status'] ?? '');
$isSupplierConfirmed = $conformStatusFind === 'Confirmed';
$addMoreHref = 'index.php?page=file&file_group_no=' . rawurlencode($fcn);

$filePreviewPdfIcon = static function (string $accent = '#dc2626'): string {
    return '<svg class="file-preview__pdf-icon" viewBox="0 0 32 40" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">'
        . '<path fill="#f9fafb" stroke="#d1d5db" stroke-width="1" d="M6 1h12l8 8v30H6V1z"/>'
        . '<path fill="#e5e7eb" d="M18 1v8h8"/>'
        . '<rect x="7" y="18" width="18" height="12" rx="2" fill="' . htmlspecialchars($accent, ENT_QUOTES, 'UTF-8') . '"/>'
        . '<text x="16" y="27" text-anchor="middle" fill="#fff" font-size="7" font-weight="700" font-family="Arial,sans-serif">PDF</text>'
        . '</svg>';
};

$filePreviewExtIcon = '<svg class="file-preview__ext-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">'
    . '<path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"/>'
    . '<path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z"/>'
    . '</svg>';

$fromCountry = (string) ($head['from_country'] ?? '');
$svcDateHead = $previewFmtDate((string) ($head['service_date'] ?? ''));
$svcCatHead = (string) ($head['service_cat'] ?? '');
$fromCity = (string) ($head['from_city'] ?? '');
$fromLoc = (string) ($head['from_location'] ?? '');
$fromZone = (string) ($head['from_zone'] ?? '');
$fromLocZone = $fromLoc . ($fromZone !== '' ? ' / ' . $fromZone : '');
$toCity = (string) ($head['to_city'] ?? '');
$toLoc = (string) ($head['to_location'] ?? '');
$toZone = (string) ($head['to_zone'] ?? '');
$toLocZone = $toLoc . ($toZone !== '' ? ' / ' . $toZone : '');
$adultsH = (string) ($head['adults'] ?? '');
$childrenH = (string) ($head['children'] ?? '');
$paxH = (string) ($head['no_of_pax'] ?? '');
$svcNameH = (string) ($head['service'] ?? '');
$refH = (string) ($head['ref_no'] ?? '');
$paxMobileH = (string) ($head['pax_mobile'] ?? '');

?>

<style>
.file-preview {
    font-size: 12px;
    width: 100%;
    max-width: none;
    box-sizing: border-box;
}
.file-preview__summary {
    border: 1px solid #2a8f4a;
    background: #fffce8;
    padding: 8px 10px 10px;
    margin-bottom: 6px;
    box-sizing: border-box;
}
.file-preview__summary-title {
    margin: 0 0 6px;
    font-size: 16px;
    font-weight: 700;
    font-family: Arial, Helvetica, sans-serif;
    line-height: 1.25;
}
.file-preview__green {
    color: #00cc00;
}
.file-preview__red {
    color: #990000;
}
.file-preview__summary-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}
.file-preview__summary-table td {
    padding: 2px 6px 3px 0;
    vertical-align: top;
}
.file-preview__summary-table strong {
    color: #1a1a1a;
}
.file-preview__transport-wrap {
    border: 1px solid #ccc;
    background: #fff;
    margin-bottom: 8px;
    box-sizing: border-box;
}
.file-preview__transport-head {
    margin: 0;
    padding: 6px 8px;
    font-size: 16px;
    font-family: Arial, Helvetica, sans-serif;
    font-weight: 700;
    line-height: 1.3;
    border-bottom: 1px solid #ddd;
}
.file-preview__blue {
    color: #127afa;
}
.file-preview__status-lbl {
    color: #333;
    font-size: 13px;
    font-weight: 400;
}
.file-preview__status-val {
    color: #0099ff;
    font-size: 13px;
    font-weight: 600;
}
.file-preview__detail-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}
.file-preview__detail-table td {
    padding: 3px 8px 4px;
    border-bottom: 1px solid #e8e8e8;
    vertical-align: top;
}
.file-preview__detail-table strong {
    color: #000;
}
.file-preview__hl {
    color: #990000;
    font-weight: 600;
}
.file-preview__guest-wrap {
    padding: 6px 8px 8px;
}
.file-preview__guest-table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #009900;
}
.file-preview__guest-table th {
    background: #009900;
    color: #fff;
    font-weight: 700;
    text-align: left;
    padding: 6px 8px;
    font-size: 12px;
}
.file-preview__guest-table td {
    background: #ffffe8;
    padding: 6px 8px;
    font-size: 14px;
}
.file-preview__guest-name {
    color: #990000;
    font-size: 16px;
    font-weight: 600;
}
.file-preview__guest-price {
    color: #000;
    font-size: 16px;
}
.file-preview__grand {
    text-align: right;
    margin: 10px 0 8px;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 18px;
    font-weight: 700;
    color: #00cc00;
}
.file-preview__grand-amt {
    font-size: 20px;
    color: #990000;
    font-weight: 700;
}
.file-preview__footer-wrap {
    margin-top: 16px;
    display: grid;
    grid-template-columns: 1fr;
    gap: 14px;
}
@media (min-width: 768px) {
    .file-preview__footer-wrap {
        grid-template-columns: 1fr auto;
        align-items: stretch;
    }
}
.file-preview__panel {
    border: 1px solid #c5e0cc;
    background: linear-gradient(165deg, #ffffff 0%, #f7fcf8 55%, #f0faf2 100%);
    border-radius: 10px;
    padding: 16px 18px;
    box-shadow: 0 2px 8px rgba(26, 107, 58, 0.08);
}
.file-preview__panel-head {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 14px;
    padding-bottom: 12px;
    border-bottom: 1px solid #dcefe2;
}
.file-preview__panel-head-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: linear-gradient(135deg, #1a7a42 0%, #2a9f55 100%);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.file-preview__panel-head-icon svg {
    width: 20px;
    height: 20px;
}
.file-preview__panel-title {
    margin: 0;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 0.02em;
    color: #14532d;
    line-height: 1.25;
}
.file-preview__panel-sub {
    margin: 2px 0 0;
    font-size: 11px;
    font-weight: 500;
    color: #6b7280;
}
.file-preview__section-label {
    margin: 0 0 8px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #4b5563;
}
.file-preview__quick-links {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 16px;
}
.file-preview__quick-link {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 14px;
    font-size: 13px;
    font-weight: 600;
    color: #14532d;
    background: #fff;
    border: 1px solid #b8dfc4;
    border-radius: 8px;
    text-decoration: none;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    transition: background 0.15s, border-color 0.15s, box-shadow 0.15s, transform 0.12s;
}
.file-preview__quick-link svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
    opacity: 0.85;
}
.file-preview__quick-link:hover {
    background: #ecfdf3;
    border-color: #2a8f4a;
    box-shadow: 0 2px 6px rgba(42, 143, 74, 0.12);
    transform: translateY(-1px);
}
.file-preview__downloads {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 10px;
}
.file-preview__doc-list {
    margin: 0;
    padding: 0;
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.file-preview__doc-item a {
    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 8px;
    background: #fafafa;
    border: 1px solid #e5e7eb;
    color: #111827;
    text-decoration: none;
    transition: border-color 0.15s, background 0.15s, box-shadow 0.15s, transform 0.12s;
}
.file-preview__doc-item a:hover {
    background: #fff;
    border-color: #86c99a;
    box-shadow: 0 3px 10px rgba(42, 143, 74, 0.1);
    transform: translateY(-1px);
}
.file-preview__pdf-icon {
    width: 32px;
    height: 40px;
    flex-shrink: 0;
    display: block;
    filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.08));
}
.file-preview__doc-body {
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.file-preview__doc-meta-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
}
.file-preview__doc-badge {
    flex-shrink: 0;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 2px 8px;
    border-radius: 999px;
    line-height: 1.5;
}
.file-preview__doc-badge--agent {
    background: #dbeafe;
    color: #1e40af;
}
.file-preview__doc-badge--supplier {
    background: #ffedd5;
    color: #9a3412;
}
.file-preview__doc-badge--itinerary {
    background: #dcfce7;
    color: #166534;
}
.file-preview__doc-title {
    font-size: 13px;
    font-weight: 600;
    color: #1f2937;
    line-height: 1.35;
    word-break: break-word;
}
.file-preview__doc-hint {
    font-size: 11px;
    font-weight: 500;
    color: #9ca3af;
}
.file-preview__doc-action {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    flex-shrink: 0;
    color: #6b7280;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.file-preview__ext-icon {
    width: 18px;
    height: 18px;
    opacity: 0.7;
}
.file-preview__doc-item a:hover .file-preview__doc-action {
    color: #1a7a42;
}
.file-preview__empty-hint {
    margin: 0;
    padding: 12px 14px;
    font-size: 12px;
    color: #6b7280;
    background: #f9fafb;
    border: 1px dashed #d1d5db;
    border-radius: 8px;
    line-height: 1.45;
}
.file-preview__actions-col {
    display: flex;
    flex-direction: column;
    align-items: stretch;
    justify-content: flex-start;
    gap: 10px;
    padding-top: 4px;
}
@media (min-width: 768px) {
    .file-preview__actions-col {
        align-items: stretch;
        min-width: 172px;
        padding-top: 0;
    }
}
.file-preview__btn-go {
    display: inline-block;
    padding: 8px 18px;
    font-size: 13px;
    font-weight: 600;
    color: #fff !important;
    background: #009900;
    border: 1px solid #007700;
    border-radius: 6px;
    text-decoration: none;
    cursor: pointer;
    font-family: inherit;
    text-align: center;
    transition: background 0.15s;
}
.file-preview__btn-go:hover {
    background: #00b300;
}
.file-preview__btn-go--secondary {
    background: #fff;
    color: #0d5c2e !important;
    border-color: #2a8f4a;
}
.file-preview__btn-go--secondary:hover {
    background: #e8f9ec;
}
</style>

<div class="flex gap-6 w-full pb-6">
    <aside class="hidden lg:block w-72 shrink-0"><?php require __DIR__ . '/sidebar.php'; ?></aside>
    <main class="flex-1 min-w-0 space-y-3 max-w-5xl">
        <?php $breadcrumbCurrent = 'File preview'; require __DIR__ . '/../../includes/breadcrumb.php'; ?>
        <?php if ($flash): ?>
            <?php $ft = $flash['type'] ?? ''; ?>
            <div class="alert <?= $ft === 'success' ? 'alert-success' : ($ft === 'error' ? 'alert-error' : 'alert-info') ?>"><span><?= h($flash['message']) ?></span></div>
        <?php endif; ?>

        <div class="file-preview">
            <?php if ($head === []): ?>
                <div class="file-preview__summary">
                    <p class="text-sm">No services in this file group.</p>
                    <a href="index.php?page=file" class="file-preview__link-add">Back to search</a>
                </div>
            <?php else: ?>
                <div class="file-preview__summary">
                    <h2 class="file-preview__summary-title">
                        <span class="file-preview__green">My booking :</span>
                        <span class="file-preview__red"><?= h($fileNoDisplay) ?></span>
                        <span class="file-preview__green">&nbsp;&nbsp;&nbsp;Booking status :</span>
                        <span class="file-preview__red"><?= h($statusSummary) ?></span>
                    </h2>
                    <table class="file-preview__summary-table" role="presentation">
                        <tr>
                            <td><strong>From country :</strong></td>
                            <td><?= h($fromCountry) ?></td>
                            <td><strong>Service date :</strong></td>
                            <td><?= h($svcDateHead) ?><?= $svcCatHead !== '' ? ' <strong>( ' . h($svcCatHead) . ' )</strong>' : '' ?></td>
                        </tr>
                        <tr>
                            <td><strong>Pick up :</strong></td>
                            <td><?= h($fromCity) ?></td>
                            <td><strong>Location / zone :</strong></td>
                            <td><?= h($fromLocZone) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Drop off :</strong></td>
                            <td><?= h($toCity) ?></td>
                            <td><strong>Location / zone :</strong></td>
                            <td><?= h($toLocZone) ?></td>
                        </tr>
                        <tr>
                            <td><strong>No of adults :</strong></td>
                            <td><?= h($adultsH) ?></td>
                            <td><strong>No of children :</strong></td>
                            <td><?= h($childrenH) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Total pax :</strong></td>
                            <td colspan="3"><?= h($paxH) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Service name :</strong></td>
                            <td><?= h($svcNameH) ?></td>
                            <td><strong>Ref no. :</strong></td>
                            <td><?= h($refH) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Guest H/P no :</strong></td>
                            <td colspan="3"><?= h($paxMobileH) ?></td>
                        </tr>
                    </table>
                    <p class="text-xs text-base-content/70 mt-1 mb-0">File group #<?= h($fcn) ?> — <?= count($rows) ?> service(s)</p>
                </div>

                <?php
                $noPlus = 1;
                foreach ($rows as $r):
                    $st = (string) ($r['service_type'] ?? '');
                    $bs = $conformStatusLabel((string) ($r['conform_status'] ?? ''));
                    $svc = (string) ($r['service'] ?? '');
                    $fCountry = (string) ($r['from_country'] ?? '');
                    $fCity = (string) ($r['from_city'] ?? '');
                    $tCity = (string) ($r['to_city'] ?? '');
                    $fLoc = (string) ($r['from_location'] ?? '');
                    $tLoc = (string) ($r['to_location'] ?? '');
                    $fZ = $previewZone((string) ($r['from_zone'] ?? ''), $fLoc);
                    $tZ = $previewZone((string) ($r['to_zone'] ?? ''), $tLoc);
                    $svcD = $previewFmtDate((string) ($r['service_date'] ?? ''));
                    $pmob = (string) ($r['pax_mobile'] ?? '');
                    $put = (string) ($r['pickup_time'] ?? '');
                    $ftm = (string) ($r['flight_time'] ?? '');
                    $ad = (string) ($r['adults'] ?? '');
                    $ch = (string) ($r['children'] ?? '');
                    $fn = (string) ($r['flight_no'] ?? '');
                    $np = (string) ($r['no_of_pax'] ?? '');
                    $vehUnit = (string) ($r['no_of_vachile'] ?? $r['vehicle_type'] ?? '');
                    $sell = (string) ($r['selling_price'] ?? '');
                    $drv = (string) ($r['driver_name'] ?? '');
                    $sup = (string) ($r['supplier_name'] ?? '');
                    $rmk = (string) ($r['remarks'] ?? '');
                    $ln = (string) ($r['last_name'] ?? '');
                    $fnm = (string) ($r['first_name'] ?? '');
                    $guestLine = trim($ln . ' ' . $fnm);
                    ?>
                    <div class="file-preview__transport-wrap">
                        <h3 class="file-preview__transport-head">
                            <span class="file-preview__green">Transport service</span><span class="file-preview__blue"> / <?= h($st !== '' ? $st : '—') ?></span>
                            <span class="file-preview__status-lbl">&nbsp;&nbsp;Status:</span>
                            <span class="file-preview__status-val"><?= h($bs) ?></span>
                        </h3>
                        <table class="file-preview__detail-table" role="presentation">
                            <tr>
                                <td width="22%"><strong>Service name :</strong></td>
                                <td colspan="2"><?= h($svc) ?></td>
                                <td width="18%"><?= h($fCountry) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Pick up city :</strong></td>
                                <td class="file-preview__hl"><?= h($fCity) ?></td>
                                <td><strong>Drop off city :</strong></td>
                                <td class="file-preview__hl"><?= h($tCity) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Pick up location :</strong></td>
                                <td class="file-preview__hl"><?= h($fLoc) ?></td>
                                <td><strong>Drop off location :</strong></td>
                                <td class="file-preview__hl"><?= h($tLoc) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Pick up zone :</strong></td>
                                <td class="file-preview__hl"><?= h($fZ) ?></td>
                                <td><strong>Drop off zone :</strong></td>
                                <td class="file-preview__hl"><?= h($tZ) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Service date :</strong></td>
                                <td><?= h($svcD) ?></td>
                                <td><strong>Pax mobile no :</strong></td>
                                <td class="file-preview__hl"><?= h($pmob) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Pickup time :</strong></td>
                                <td colspan="2" class="file-preview__hl"><?= h($put) ?></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td><strong>No of adults :</strong></td>
                                <td><?= h($ad) ?></td>
                                <td><strong>Flight time :</strong></td>
                                <td class="file-preview__hl"><?= h($ftm) ?></td>
                            </tr>
                            <tr>
                                <td><strong>No of children :</strong></td>
                                <td><?= h($ch) ?></td>
                                <td><strong>Flight no :</strong></td>
                                <td><?= h($fn) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Total pax :</strong></td>
                                <td colspan="3"><?= h($np) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Total unit :</strong></td>
                                <td><?= h($vehUnit !== '' ? $vehUnit : '—') ?></td>
                                <td><strong>Price / unit :</strong></td>
                                <td><?= h($sell) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Driver name :</strong></td>
                                <td><?= h($drv) ?></td>
                                <td><strong>Supplier name :</strong></td>
                                <td><?= h($sup) ?></td>
                            </tr>
                        </table>
                        <div class="file-preview__guest-wrap">
                            <table class="file-preview__guest-table" role="grid">
                                <thead>
                                    <tr>
                                        <th style="width:4rem">No</th>
                                        <th>Guest list</th>
                                        <th style="width:8rem">Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><?= $noPlus ?></td>
                                        <td class="file-preview__guest-name"><?= h($guestLine) ?></td>
                                        <td class="file-preview__guest-price"><?= h($sell) ?> MYR</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <table class="file-preview__detail-table" role="presentation">
                            <tr>
                                <td width="22%"><strong>Remarks :</strong></td>
                                <td colspan="3"><?= h($rmk) ?></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>Currency MYR</td>
                                <td><strong>Total amount</strong></td>
                                <td class="file-preview__guest-price"><?= h($sell) ?></td>
                            </tr>
                        </table>
                    </div>
                    <?php
                    $noPlus++;
                endforeach;
                ?>

                <div class="file-preview__grand">
                    Grand total :
                    <span class="file-preview__grand-amt"><?= h(number_format($total, 2)) ?> MYR</span>
                </div>

                <div class="file-preview__footer-wrap">
                    <div class="file-preview__panel">
                        <div class="file-preview__panel-head">
                            <div class="file-preview__panel-head-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="file-preview__panel-title">File actions &amp; documents</p>
                                <p class="file-preview__panel-sub">Manage this booking and download PDFs</p>
                            </div>
                        </div>
                        <p class="file-preview__section-label">Quick actions</p>
                        <div class="file-preview__quick-links">
                            <a href="<?= h($addMoreHref) ?>" class="file-preview__quick-link">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                                Add transfer service
                            </a>
                            <a href="index.php?page=file&amp;new=1" class="file-preview__quick-link">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m-7 4h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                Start new file
                            </a>
                        </div>
                        <?php if ($isSupplierConfirmed): ?>
                            <p class="file-preview__section-label">Downloads</p>
                            <div class="file-preview__downloads">
                                <ul class="file-preview__doc-list">
                                    <?php foreach ($invoices as $inv):
                                        $invId = (string) ($inv['Invoices_id'] ?? '');
                                        $invType = (string) ($inv['invoice_type'] ?? '');
                                        $invParty = (string) ($inv['agent_supplier_name'] ?? '');
                                        if ($invType === 'Agent Invoice'): ?>
                                            <li class="file-preview__doc-item">
                                                <a href="index.php?page=invoice_pdf_converter&amp;file_count_no=<?= h(rawurlencode($fcn)) ?>" target="_blank" rel="noopener">
                                                    <?= $filePreviewPdfIcon('#2563eb') ?>
                                                    <span class="file-preview__doc-body">
                                                        <span class="file-preview__doc-meta-row">
                                                            <span class="file-preview__doc-badge file-preview__doc-badge--agent">Agent invoice</span>
                                                        </span>
                                                        <span class="file-preview__doc-title"><?= h($invId . ' — ' . $invParty) ?></span>
                                                        <span class="file-preview__doc-hint">Invoice PDF · opens in new tab</span>
                                                    </span>
                                                    <span class="file-preview__doc-action">
                                                        <?= $filePreviewExtIcon ?>
                                                        Open
                                                    </span>
                                                </a>
                                            </li>
                                        <?php elseif ($invType === 'Supplier Invoice'): ?>
                                            <li class="file-preview__doc-item">
                                                <a href="index.php?page=invoice_pdf_supplier_converter&amp;file_count_no=<?= h(rawurlencode($fcn . '|' . $invParty)) ?>" target="_blank" rel="noopener">
                                                    <?= $filePreviewPdfIcon('#ea580c') ?>
                                                    <span class="file-preview__doc-body">
                                                        <span class="file-preview__doc-meta-row">
                                                            <span class="file-preview__doc-badge file-preview__doc-badge--supplier">Supplier invoice</span>
                                                        </span>
                                                        <span class="file-preview__doc-title"><?= h($invId . ' — ' . $invParty) ?></span>
                                                        <span class="file-preview__doc-hint">Invoice PDF · opens in new tab</span>
                                                    </span>
                                                    <span class="file-preview__doc-action">
                                                        <?= $filePreviewExtIcon ?>
                                                        Open
                                                    </span>
                                                </a>
                                            </li>
                                        <?php endif;
                                    endforeach; ?>
                                    <li class="file-preview__doc-item">
                                        <a href="index.php?page=file_itinerary_pdf&amp;file_count_no=<?= h(rawurlencode($fcn)) ?>" target="_blank" rel="noopener">
                                            <?= $filePreviewPdfIcon('#16a34a') ?>
                                            <span class="file-preview__doc-body">
                                                <span class="file-preview__doc-meta-row">
                                                    <span class="file-preview__doc-badge file-preview__doc-badge--itinerary">Itinerary</span>
                                                </span>
                                                <span class="file-preview__doc-title">Guest itinerary</span>
                                                <span class="file-preview__doc-hint">Itinerary PDF · opens in new tab</span>
                                            </span>
                                            <span class="file-preview__doc-action">
                                                <?= $filePreviewExtIcon ?>
                                                Open
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <p class="file-preview__empty-hint">Invoices and itinerary will appear here after the supplier confirms this booking.</p>
                        <?php endif; ?>
                    </div>
                    <div class="file-preview__actions-col">
                        <?php if ($showConfirm): ?>
                            <form method="post" action="index.php?page=file_preview&amp;file_count_no=<?= h(rawurlencode($fcn)) ?>" class="m-0">
                                <input type="hidden" name="_token" value="<?= h($csrf) ?>">
                                <input type="hidden" name="file_confirm" value="1">
                                <input type="hidden" name="file_count_no" value="<?= h($fcn) ?>">
                                <button type="submit" class="file-preview__btn-go w-full">Confirm booking</button>
                            </form>
                        <?php endif; ?>
                        <a class="file-preview__btn-go file-preview__btn-go--secondary" href="index.php?page=file_send_email&amp;file_count_no=<?= h(rawurlencode($fcn)) ?>">Resend email</a>
                    </div>
                </div>

            <?php endif; ?>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
