<?php

declare(strict_types=1);

function file_itinerary_bootstrap_tcpdf_config(): void
{
    if (defined('PDF_PAGE_FORMAT')) {
        return;
    }
    define('K_TCPDF_EXTERNAL_CONFIG', true);
    define('K_PATH_IMAGES', dirname(__DIR__) . '/legacy/file/tcpdf/examples/images/');
    define('K_PATH_FONTS', dirname(__DIR__) . '/vendor/tecnickcom/tcpdf/fonts/');
    define('K_PATH_CACHE', sys_get_temp_dir() . '/');
    define('K_BLANK_IMAGE', '_blank.png');
    define('PDF_PAGE_FORMAT', 'A4');
    define('PDF_PAGE_ORIENTATION', 'P');
    define('PDF_CREATOR', 'TCPDF');
    define('PDF_AUTHOR', 'TCPDF');
    define('PDF_HEADER_TITLE', '');
    define('PDF_HEADER_STRING', '');
    define('PDF_UNIT', 'mm');
    define('PDF_MARGIN_HEADER', 5);
    define('PDF_MARGIN_FOOTER', 10);
    define('PDF_MARGIN_TOP', 12);
    define('PDF_MARGIN_BOTTOM', 18);
    define('PDF_MARGIN_LEFT', 12);
    define('PDF_MARGIN_RIGHT', 12);
    define('PDF_FONT_NAME_MAIN', 'helvetica');
    define('PDF_FONT_SIZE_MAIN', 10);
    define('PDF_FONT_NAME_DATA', 'helvetica');
    define('PDF_FONT_SIZE_DATA', 8);
    define('PDF_FONT_MONOSPACED', 'courier');
    define('PDF_IMAGE_SCALE_RATIO', 1.25);
    define('HEAD_MAGNIFICATION', 1.1);
    define('K_CELL_HEIGHT_RATIO', 1.25);
    define('K_TITLE_MAGNIFICATION', 1.3);
    define('K_SMALL_RATIO', 2 / 3);
    define('K_THAI_TOPCHARS', true);
    define('K_TCPDF_CALLS_IN_HTML', true);
    define('K_TCPDF_THROW_EXCEPTION_ERROR', false);
}

/**
 * Recover Arabic text from legacy double-UTF-8 / mojibake storage.
 */
function file_itinerary_ar(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }
    if (function_exists('normalize_arabic_text')) {
        $value = normalize_arabic_text($value);
    }
    if (preg_match('/[ØÙÃÂ]/u', $value)) {
        return '';
    }
    if (preg_match('/[\x{0600}-\x{06FF}]/u', $value)) {
        return $value;
    }

    return '';
}

/** Standard itinerary labels (legacy / image reference). */
function file_itinerary_label_defaults(): array
{
    return [
        'ITINERARY_fills' => 'جدول الرحلة',
        'Client_Name_fills' => 'اسم العميل',
        'Ref_No_fills' => 'الملف رقم',
        'Transfers_fills' => 'المواصلات',
        'city_one_fills' => 'الوقت',
        'city_two_fills' => 'المدينه',
        'city_three_fills' => 'الخدمات',
        'city_four_fills' => 'تاريخ',
        'Drop_Off_Point_fills' => 'الوجهة',
        'Pick_Up_Point_fills' => 'من',
    ];
}

function file_itinerary_label_is_valid(string $value): bool
{
    $value = trim($value);
    if ($value === '' || !preg_match('/[\x{0600}-\x{06FF}]/u', $value)) {
        return false;
    }
    if (preg_match('/[ØÙÃÂ?�]/u', $value)) {
        return false;
    }
    return (bool) preg_match(
        '/(?:ال|اسم|رقم|ملف|وقت|تاريخ|خدم|مواص|عميل|وجه|مدين|من|جدول|رح|صباح|عصر|مساء|فجر|ظهر)/u',
        $value
    );
}

function file_itinerary_resolve_label(array $labels, string $key): string
{
    $defaults = file_itinerary_label_defaults();
    $default = $defaults[$key] ?? '';
    $raw = trim((string) ($labels[$key] ?? ''));
    if ($raw !== '' && file_itinerary_label_is_valid($raw)) {
        return $raw;
    }

    return $default;
}

function file_itinerary_is_arabic(string $value): bool
{
    return (bool) preg_match('/[\x{0600}-\x{06FF}]/u', $value);
}

function file_itinerary_ar_broken(string $value): bool
{
    return $value === '' || str_contains($value, '?') || str_contains($value, '�');
}

/** Wrap text for TCPDF HTML (legacy uses dejavusans for Arabic). */
function file_itinerary_ar_html(string $value): string
{
    $text = file_itinerary_ar($value);
    if ($text === '') {
        return '';
    }

    $escaped = file_itinerary_h($text);
    if (file_itinerary_is_arabic($text)) {
        return $escaped;
    }

    return '<span dir="ltr">' . $escaped . '</span>';
}

function file_itinerary_latin_html(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }

    return '<span dir="ltr">' . file_itinerary_h($value) . '</span>';
}

function file_itinerary_write_html(TCPDF $pdf, string $html, int $fontPt = 10): void
{
    $pdf->SetFont('dejavusans', '', $fontPt);
    $pdf->writeHTML($html, true, false, true, false, '');
}

function file_itinerary_h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** @return list<string> */
function file_itinerary_logo_candidates(?int $agentId, string $agentLogoName): array
{
    $root = dirname(__DIR__);
    $candidates = [
        $root . '/legacy/file/tcpdf/examples/images/within_earth.jpg',
        $root . '/legacy/invoice/tcpdf/examples/images/within_earth.jpg',
    ];
    if ($agentId > 0 && $agentLogoName !== '') {
        $candidates[] = $root . '/setup/agent_logo/' . $agentId . '/' . $agentLogoName;
        $candidates[] = dirname($root) . '/old_app_travel/login/super/setup/agent_logo/' . $agentId . '/' . $agentLogoName;
    }

    return $candidates;
}

function file_itinerary_resolve_logo(?int $agentId, string $agentLogoName): ?string
{
    foreach (file_itinerary_logo_candidates($agentId, $agentLogoName) as $path) {
        if (is_file($path)) {
            return $path;
        }
    }

    return null;
}

/** @return array<string, string> */
function file_itinerary_time_label_defaults(): array
{
    return [
        'aa_fills' => 'فجرا',
        'bb_fills' => 'صباحا',
        'cc_fills' => 'ظهرا',
        'dd_fills' => 'عصرا',
        'ee_fills' => 'مساءا',
    ];
}

/** @param array<string, mixed> $timeRow */
function file_itinerary_arabic_time_label(string $pickupTimeSql, array $timeRow): string
{
    $ts = strtotime($pickupTimeSql);
    if ($ts === false) {
        return '';
    }
    $ranges = [
        ['01:00:00', '05:59:00', 'aa_fills'],
        ['06:00:00', '11:59:00', 'bb_fills'],
        ['12:00:00', '14:59:00', 'cc_fills'],
        ['15:00:00', '17:59:00', 'dd_fills'],
        ['18:00:00', '23:59:00', 'ee_fills'],
    ];
    $defaults = file_itinerary_time_label_defaults();
    $timeOnly = date('H:i:s', $ts);
    $t = strtotime($timeOnly);
    foreach ($ranges as [$from, $to, $key]) {
        if ($t >= strtotime($from) && $t <= strtotime($to)) {
            $label = file_itinerary_ar((string) ($timeRow[$key] ?? ''));
            if (file_itinerary_ar_broken($label)) {
                return $defaults[$key] ?? $label;
            }

            return $label;
        }
    }

    return '';
}

function file_itinerary_load_tcpdf(): void
{
    file_itinerary_bootstrap_tcpdf_config();
    $autoload = dirname(__DIR__) . '/vendor/autoload.php';
    if (is_file($autoload)) {
        require_once $autoload;
    }
    $tcpdf = dirname(__DIR__) . '/vendor/tecnickcom/tcpdf/tcpdf.php';
    if (is_file($tcpdf)) {
        require_once $tcpdf;
    }
}

function file_itinerary_pdf_render(mysqli $mysqli, string $fileCountNo): void
{
    if ($fileCountNo === '') {
        http_response_code(400);
        echo 'Missing file_count_no';
        return;
    }

    if (is_file(__DIR__ . '/setup_itinerary_labels_service.php')) {
        require_once __DIR__ . '/setup_itinerary_labels_service.php';
        if (function_exists('setup_itinerary_labels_ensure_utf8mb4')) {
            setup_itinerary_labels_ensure_utf8mb4($mysqli);
        }
    }

    $stmt = $mysqli->prepare('SELECT * FROM file_entry WHERE file_count_no = ? ORDER BY file_id DESC LIMIT 1');
    if (!$stmt) {
        http_response_code(500);
        echo 'Database error';
        return;
    }
    $stmt->bind_param('s', $fileCountNo);
    $stmt->execute();
    $headerEntry = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$headerEntry) {
        http_response_code(404);
        echo 'File not found';
        return;
    }

    $agentName = (string) ($headerEntry['agent_name'] ?? '');
    $fileNo = (string) ($headerEntry['file_no'] ?? '');
    $title = (string) ($headerEntry['title'] ?? '');
    $lastName = (string) ($headerEntry['last_name'] ?? '');
    $firstName = (string) ($headerEntry['first_name'] ?? '');
    $clientName = trim($title . ' ' . $lastName . ' ' . $firstName);

    $agent = [];
    $agentId = 0;
    $agentLogo = '';
    if ($agentName !== '') {
        $aStmt = $mysqli->prepare('SELECT * FROM agent WHERE agent_name = ? LIMIT 1');
        if ($aStmt) {
            $aStmt->bind_param('s', $agentName);
            $aStmt->execute();
            $agent = $aStmt->get_result()->fetch_assoc() ?: [];
            $aStmt->close();
            $agentId = (int) ($agent['agent_id'] ?? 0);
            $agentLogo = (string) ($agent['agent_logo_name'] ?? '');
        }
    }

    $labels = [];
    $lRes = $mysqli->query('SELECT * FROM arebic_lebels WHERE arebic_lebels_id = 1 LIMIT 1');
    if ($lRes) {
        $labels = $lRes->fetch_assoc() ?: [];
        $lRes->close();
    }
    foreach ($labels as $k => $v) {
        if (is_string($v)) {
            $labels[$k] = file_itinerary_ar($v);
        }
    }

    $timeRow = [];
    $tRes = $mysqli->query('SELECT * FROM time_format WHERE time_format_id = 1 LIMIT 1');
    if ($tRes) {
        $timeRow = $tRes->fetch_assoc() ?: [];
        $tRes->close();
        foreach ($timeRow as $k => $v) {
            if (is_string($v)) {
                $timeRow[$k] = file_itinerary_ar($v);
            }
        }
    }

    $entries = [];
    $eStmt = $mysqli->prepare('SELECT * FROM file_entry WHERE file_count_no = ? ORDER BY service_date ASC, pickup_time ASC');
    if ($eStmt) {
        $eStmt->bind_param('s', $fileCountNo);
        $eStmt->execute();
        $res = $eStmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $entries[] = $row;
        }
        $eStmt->close();
    }

    require_once __DIR__ . '/pdf_company_config.php';
    $company = pdf_company_config();

    file_itinerary_load_tcpdf();

    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($company['name']);
    $pdf->SetTitle('Itinerary ' . $fileNo);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(12, 12, 12);
    $pdf->SetAutoPageBreak(true, 18);
    $pdf->setRTL(false);
    $pdf->SetFont('dejavusans', '', 9);
    $pdf->AddPage();

    $logoPath = file_itinerary_resolve_logo($agentId, $agentLogo);
    $yStart = 12;
    if ($logoPath !== null) {
        $pdf->Image($logoPath, 12, $yStart, 42, 0, '', '', '', false, 300);
    }

    $pdf->SetXY(58, $yStart);
    $pdf->SetFont('dejavusans', 'B', 11);
    $pdf->Cell(0, 6, $company['name'], 0, 1, 'L');
    $pdf->SetX(58);
    $pdf->SetFont('dejavusans', '', 8);
    $pdf->MultiCell(0, 4, pdf_company_header_multiline_itinerary(), 0, 'L');

    $pdf->Ln(6);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->Line(12, $pdf->GetY(), 198, $pdf->GetY());
    $pdf->Ln(6);

    $itineraryArLabel = file_itinerary_resolve_label($labels, 'ITINERARY_fills');
    $pdf->SetFont('dejavusans', 'B', 14);
    $pdf->Cell(0, 8, 'ITINERARY', 0, 1, 'C');
    $pdf->SetFont('dejavusans', 'B', 14);
    $pdf->Cell(0, 8, '( ' . $itineraryArLabel . ' )', 0, 1, 'C');

    $clientLabel = file_itinerary_resolve_label($labels, 'Client_Name_fills');
    $refLabel = file_itinerary_resolve_label($labels, 'Ref_No_fills');

    $clientHtml = '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%;">'
        . '<tr>'
        . '<td colspan="2" rowspan="2" align="center" style="font-size:11pt;">'
        . '<b>' . file_itinerary_latin_html($clientName) . ' : '
        . file_itinerary_ar_html($clientLabel) . '</b></td>'
        . '<td align="center">' . file_itinerary_ar_html($refLabel) . '</td>'
        . '</tr>'
        . '<tr><td align="center"><b>' . file_itinerary_latin_html($fileNo) . '</b></td></tr>'
        . '</table>';

    file_itinerary_write_html($pdf, $clientHtml, 10);

    $transfersLabel = file_itinerary_resolve_label($labels, 'Transfers_fills');
    $colTime = file_itinerary_resolve_label($labels, 'city_one_fills');
    $colCity = file_itinerary_resolve_label($labels, 'city_two_fills');
    $colService = file_itinerary_resolve_label($labels, 'city_three_fills');
    $colDate = file_itinerary_resolve_label($labels, 'city_four_fills');
    $dropLabel = file_itinerary_resolve_label($labels, 'Drop_Off_Point_fills');
    $pickLabel = file_itinerary_resolve_label($labels, 'Pick_Up_Point_fills');

    $table = '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse:collapse;width:100%;">'
        . '<tr><td colspan="4" align="center" style="font-size:14pt;font-weight:bold;">'
        . file_itinerary_ar_html($transfersLabel) . '</td></tr>'
        . '<tr>'
        . '<td width="14%" align="center"><b>' . file_itinerary_ar_html($colTime) . '</b></td>'
        . '<td width="16%" align="center"><b>' . file_itinerary_ar_html($colCity) . '</b></td>'
        . '<td width="54%" align="center"><b>' . file_itinerary_ar_html($colService) . '</b></td>'
        . '<td width="16%" align="center"><b>' . file_itinerary_ar_html($colDate) . '</b></td>'
        . '</tr>';

    foreach ($entries as $row) {
        $pickupSql = (string) ($row['pickup_time'] ?? '');
        $timeDisplay = $pickupSql !== '' ? date('g:i', strtotime($pickupSql)) : '';
        $arabicTime = file_itinerary_arabic_time_label($pickupSql, $timeRow);
        $serviceDate = (string) ($row['service_date'] ?? '');
        $serviceDateDisp = $serviceDate !== '' ? date('d-M-Y', strtotime($serviceDate)) : '';

        $serviceId = (int) ($row['service_id'] ?? 0);
        $serviceAr = '';
        $fromCityAr = '';
        $fromLoop = '';
        $toLoop = '';

        if ($serviceId > 0) {
            $sStmt = $mysqli->prepare('SELECT * FROM service WHERE service_id = ? LIMIT 1');
            if ($sStmt) {
                $sStmt->bind_param('i', $serviceId);
                $sStmt->execute();
                $svc = $sStmt->get_result()->fetch_assoc();
                $sStmt->close();
                if ($svc) {
                    $serviceAr = file_itinerary_ar((string) ($svc['service_name_arabic'] ?? ''));
                    $fromCityEn = (string) ($svc['from_city'] ?? '');
                    if ($fromCityEn !== '') {
                        $cStmt = $mysqli->prepare('SELECT city_shotform FROM city WHERE city_name = ? ORDER BY city_id DESC LIMIT 1');
                        if ($cStmt) {
                            $cStmt->bind_param('s', $fromCityEn);
                            $cStmt->execute();
                            $cRow = $cStmt->get_result()->fetch_assoc();
                            $cStmt->close();
                            if ($cRow) {
                                $fromCityAr = file_itinerary_ar((string) ($cRow['city_shotform'] ?? ''));
                            }
                        }
                    }
                }
            }
        }

        $fromLoc = (string) ($row['from_location'] ?? '');
        $fromZone = (string) ($row['from_zone'] ?? '');
        $toLoc = (string) ($row['to_location'] ?? '');
        $toZone = (string) ($row['to_zone'] ?? '');

        if ($fromZone !== '') {
            $zStmt = $mysqli->prepare('SELECT zone_name_arabic FROM zone WHERE zone_name = ? ORDER BY zone_id DESC LIMIT 1');
            if ($zStmt) {
                $zStmt->bind_param('s', $fromZone);
                $zStmt->execute();
                $z = $zStmt->get_result()->fetch_assoc();
                $zStmt->close();
                $fromLoop = file_itinerary_ar((string) ($z['zone_name_arabic'] ?? $fromZone));
            }
        }
        if ($fromLoop === '' && $fromLoc !== '') {
            $lStmt = $mysqli->prepare('SELECT location_name_arb FROM location WHERE location_name = ? ORDER BY location_id DESC LIMIT 1');
            if ($lStmt) {
                $lStmt->bind_param('s', $fromLoc);
                $lStmt->execute();
                $l = $lStmt->get_result()->fetch_assoc();
                $lStmt->close();
                $fromLoop = file_itinerary_ar((string) ($l['location_name_arb'] ?? $fromLoc));
            }
        }

        if ($toZone !== '') {
            $zStmt = $mysqli->prepare('SELECT zone_name_arabic FROM zone WHERE zone_name = ? ORDER BY zone_id DESC LIMIT 1');
            if ($zStmt) {
                $zStmt->bind_param('s', $toZone);
                $zStmt->execute();
                $z = $zStmt->get_result()->fetch_assoc();
                $zStmt->close();
                $toLoop = file_itinerary_ar((string) ($z['zone_name_arabic'] ?? $toZone));
            }
        }
        if ($toLoop === '' && $toLoc !== '') {
            $lStmt = $mysqli->prepare('SELECT location_name_arb FROM location WHERE location_name = ? ORDER BY location_id DESC LIMIT 1');
            if ($lStmt) {
                $lStmt->bind_param('s', $toLoc);
                $lStmt->execute();
                $l = $lStmt->get_result()->fetch_assoc();
                $lStmt->close();
                $toLoop = file_itinerary_ar((string) ($l['location_name_arb'] ?? $toLoc));
            }
        }

        $serviceArHtml = $serviceAr !== ''
            ? file_itinerary_ar_html($serviceAr)
            : file_itinerary_latin_html((string) ($row['service'] ?? ''));
        $timeCell = '<table><tr><td align="right">' . file_itinerary_ar_html($arabicTime)
            . '</td><td align="center">' . file_itinerary_latin_html($timeDisplay) . '</td></tr></table>';

        $table .= '<tr>'
            . '<td align="center" style="font-size:10pt;">' . $timeCell . '</td>'
            . '<td align="center" style="font-size:10pt;">' . file_itinerary_ar_html($fromCityAr) . '</td>'
            . '<td align="center" style="font-size:10pt;">'
            . '<table><tr><td colspan="2" align="center">' . $serviceArHtml . '</td></tr><tr><td>'
            . '<table><tr><td width="100" align="right">' . file_itinerary_ar_html($toLoop)
            . '</td><td width="5">:</td><td width="30">' . file_itinerary_ar_html($dropLabel) . '</td></tr></table>'
            . '</td><td>'
            . '<table><tr><td width="100" align="right">' . file_itinerary_ar_html($fromLoop)
            . '</td><td width="5">:</td><td width="30">' . file_itinerary_ar_html($pickLabel) . '</td></tr></table>'
            . '</td></tr></table></td>'
            . '<td align="center" style="font-size:10pt;">' . file_itinerary_latin_html($serviceDateDisp) . '</td>'
            . '</tr>';
    }

    $table .= '</table>';
    file_itinerary_write_html($pdf, $table, 10);

    $footerImg = dirname(__DIR__) . '/legacy/file/tcpdf/examples/images/itinerary_footer.jpg';
    if (is_file($footerImg)) {
        $pdf->AddPage();
        $pdf->Image($footerImg, 12, 20, 186, 0, '', '', '', false, 300);
    }

    $pdf->Output('itinerary-' . preg_replace('/[^a-zA-Z0-9_-]/', '', $fileNo) . '.pdf', 'I');
}
