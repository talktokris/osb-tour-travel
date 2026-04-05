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
$total = 0.0;
foreach ($rows as $r) {
    $total += (float) ($r['selling_price'] ?? 0);
}
$head = $rows[0] ?? [];
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

$bookingStatusSummary = static function (array $row): string {
    $b = (string) ($row['book_status'] ?? '');
    if ($b === 'On Request') {
        return 'On Request';
    }

    return 'Pending';
};

$statusSummary = $head !== [] ? $bookingStatusSummary($head) : '—';

$showConfirm = false;
foreach ($rows as $r) {
    if ((string) ($r['book_status'] ?? '') !== 'On Request') {
        $showConfirm = true;
        break;
    }
}

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
.file-preview__links {
    display: flex;
    flex-wrap: wrap;
    gap: 12px 20px;
    align-items: center;
    margin: 8px 0 10px;
}
.file-preview__link-add {
    font-size: 15px;
    color: #009900;
    font-weight: 600;
    text-decoration: underline;
}
.file-preview__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
    justify-content: flex-end;
    margin-top: 6px;
}
.file-preview__btn-go {
    display: inline-block;
    padding: 6px 16px;
    font-size: 13px;
    font-weight: 600;
    color: #fff !important;
    background: #009900;
    border: 1px solid #007700;
    border-radius: 3px;
    text-decoration: none;
    cursor: pointer;
    font-family: inherit;
}
.file-preview__btn-go:hover {
    background: #00b300;
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
                    $bs = (string) ($r['book_status'] ?? '');
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

                <div class="file-preview__links">
                    <a href="index.php?page=file" class="file-preview__link-add">Add more transfer service</a>
                    <a href="index.php?page=file&amp;new=1" class="link link-primary text-sm">Start new file</a>
                </div>

                <form method="post" action="index.php?page=file_preview&amp;file_count_no=<?= h(rawurlencode($fcn)) ?>" class="file-preview__actions">
                    <input type="hidden" name="_token" value="<?= h($csrf) ?>">
                    <input type="hidden" name="file_confirm" value="1">
                    <input type="hidden" name="file_count_no" value="<?= h($fcn) ?>">
                    <?php if ($showConfirm): ?>
                        <button type="submit" class="file-preview__btn-go">Confirm</button>
                    <?php endif; ?>
                    <a class="file-preview__btn-go" href="index.php?page=file_send_email&amp;file_count_no=<?= h(rawurlencode($fcn)) ?>">Resend email</a>
                </form>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
