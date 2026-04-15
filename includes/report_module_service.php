<?php

declare(strict_types=1);

require_once __DIR__ . '/file_module_service.php';

/** @var list<string> */
const REPORT_MODULE_MODES = [
    'agent',
    'supplier',
    'vehicle_type',
    'private_sic',
    'driver_name',
    'vehicle_no',
    'city',
    'tour_arrival',
    'statement_agent',
    'statement_supplier',
];

function report_module_normalize_mode(string $mode): string
{
    $mode = strtolower(trim($mode));

    return in_array($mode, REPORT_MODULE_MODES, true) ? $mode : 'agent';
}

/**
 * Legacy transfer pick-up / drop display (matches report_agent.php).
 *
 * @param array<string, mixed> $row
 * @return array{0: string, 1: string}
 */
function report_module_legacy_pickup_drop(array $row): array
{
    $lo_from = (string) ($row['from_location'] ?? '');
    $zo_from = (string) ($row['from_zone'] ?? '');
    $zo_to = (string) ($row['to_zone'] ?? '');

    $from_location = $lo_from;
    $to_location = $zo_to === '' ? $zo_from : $zo_to;

    return [$from_location, $to_location];
}

/**
 * @return list<string>
 */
function report_module_service_type_names(mysqli $mysqli): array
{
    $out = [];
    $res = $mysqli->query('SELECT service_type_name FROM service_type ORDER BY service_type_name ASC');
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $n = trim((string) ($r['service_type_name'] ?? ''));
            if ($n !== '') {
                $out[] = $n;
            }
        }
        $res->free();
    }

    return $out;
}

/**
 * Tour categories for Private/SIC report (tour_type.tour_name).
 *
 * @return list<string>
 */
function report_module_tour_type_names(mysqli $mysqli): array
{
    $out = [];
    $res = $mysqli->query('SELECT tour_name FROM tour_type ORDER BY tour_name ASC');
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $n = trim((string) ($r['tour_name'] ?? ''));
            if ($n !== '') {
                $out[] = $n;
            }
        }
        $res->free();
    }

    return $out;
}

/**
 * @return list<string>
 */
function report_module_agent_names(mysqli $mysqli): array
{
    $out = [];
    $res = $mysqli->query('SELECT agent_name FROM agent ORDER BY agent_name ASC');
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $n = trim((string) ($r['agent_name'] ?? ''));
            if ($n !== '') {
                $out[] = $n;
            }
        }
        $res->free();
    }

    return $out;
}

/**
 * @return list<string>
 */
function report_module_supplier_names(mysqli $mysqli): array
{
    $out = [];
    $res = $mysqli->query('SELECT supplier_name FROM supplier ORDER BY supplier_name ASC');
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $n = trim((string) ($r['supplier_name'] ?? ''));
            if ($n !== '') {
                $out[] = $n;
            }
        }
        $res->free();
    }

    return $out;
}

/**
 * @return list<string>
 */
function report_module_driver_names(mysqli $mysqli): array
{
    $out = [];
    $res = $mysqli->query('SELECT DISTINCT driver_name FROM file_entry WHERE driver_name IS NOT NULL AND TRIM(driver_name) <> \'\' ORDER BY driver_name ASC');
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $n = trim((string) ($r['driver_name'] ?? ''));
            if ($n !== '') {
                $out[] = $n;
            }
        }
        $res->free();
    }

    return $out;
}

/**
 * @return list<string>
 */
function report_module_vehicle_numbers(mysqli $mysqli): array
{
    $out = [];
    $res = $mysqli->query('SELECT DISTINCT vehicle_no FROM file_entry WHERE vehicle_no IS NOT NULL AND TRIM(vehicle_no) <> \'\' ORDER BY vehicle_no ASC');
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $n = trim((string) ($r['vehicle_no'] ?? ''));
            if ($n !== '') {
                $out[] = $n;
            }
        }
        $res->free();
    }

    return $out;
}

/**
 * @return list<string>
 */
function report_module_city_names(mysqli $mysqli): array
{
    $out = [];
    $res = $mysqli->query('SELECT city_name FROM city ORDER BY city_name ASC');
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $n = trim((string) ($r['city_name'] ?? ''));
            if ($n !== '') {
                $out[] = $n;
            }
        }
        $res->free();
    }

    return $out;
}

/**
 * @return list<string>
 */
function report_module_vehicle_type_names(mysqli $mysqli): array
{
    $out = [];
    $res = $mysqli->query('SELECT DISTINCT vehicle_type FROM file_entry WHERE vehicle_type IS NOT NULL AND TRIM(vehicle_type) <> \'\' ORDER BY vehicle_type ASC');
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $n = trim((string) ($r['vehicle_type'] ?? ''));
            if ($n !== '') {
                $out[] = $n;
            }
        }
        $res->free();
    }

    return $out;
}

/**
 * @param array<string, mixed> $row
 * @return array<string, string>
 */
function report_module_format_transfer_row(array $row): array
{
    [$pick, $drop] = report_module_legacy_pickup_drop($row);
    $sd = (string) ($row['service_date'] ?? '');
    $serviceDate = $sd !== '' ? date('d-m-Y', strtotime($sd)) : '';

    return [
        'supplier_name' => (string) ($row['supplier_name'] ?? ''),
        'agent_name' => (string) ($row['agent_name'] ?? ''),
        'file_no' => (string) ($row['file_no'] ?? ''),
        'client_name' => (string) ($row['first_name'] ?? '') . (string) ($row['last_name'] ?? ''),
        'service_date' => $serviceDate,
        'service' => (string) ($row['service'] ?? ''),
        'flight_no' => (string) ($row['flight_no'] ?? ''),
        'flight_time' => (string) ($row['flight_time'] ?? ''),
        'pickup_time' => (string) ($row['pickup_time'] ?? ''),
        'pick_up' => $pick,
        'drop_off' => $drop,
        'vehicle_type' => (string) ($row['vehicle_type'] ?? ''),
        'driver_name' => (string) ($row['driver_name'] ?? ''),
        'pax_mobile' => (string) ($row['pax_mobile'] ?? ''),
        'service_cat' => (string) ($row['service_cat'] ?? ''),
    ];
}

/**
 * Count rows for a transfer section (legacy: no date filter on count).
 */
function report_module_transfer_count(
    mysqli $mysqli,
    string $mode,
    string $sectionServiceType,
    string $dimValue,
    ?string $supplierOpt
): int {
    $dimValue = trim($dimValue);
    $sectionServiceType = trim($sectionServiceType);
    if ($dimValue === '' || $sectionServiceType === '') {
        return 0;
    }

    $sql = 'SELECT COUNT(*) AS c FROM file_entry WHERE service_type = ? AND ';
    $types = 'ss';
    $params = [$sectionServiceType, $dimValue];

    switch ($mode) {
        case 'agent':
            $sql = 'SELECT COUNT(*) AS c FROM file_entry WHERE agent_name = ? AND service_type = ?';
            $types = 'ss';
            $params = [$dimValue, $sectionServiceType];
            break;
        case 'supplier':
            $sql = 'SELECT COUNT(*) AS c FROM file_entry WHERE supplier_name = ? AND service_type = ?';
            $types = 'ss';
            $params = [$dimValue, $sectionServiceType];
            break;
        case 'vehicle_type':
            $sql = 'SELECT COUNT(*) AS c FROM file_entry WHERE vehicle_type = ? AND service_type = ?';
            $types = 'ss';
            $params = [$dimValue, $sectionServiceType];
            break;
        case 'private_sic':
            $sql = 'SELECT COUNT(*) AS c FROM file_entry WHERE service_cat = ? AND service_type = ?';
            $types = 'ss';
            $params = [$dimValue, $sectionServiceType];
            break;
        case 'driver_name':
            $sql = 'SELECT COUNT(*) AS c FROM file_entry WHERE driver_name = ? AND service_type = ?';
            $types = 'ss';
            $params = [$dimValue, $sectionServiceType];
            break;
        case 'vehicle_no':
            $sql = 'SELECT COUNT(*) AS c FROM file_entry WHERE vehicle_no = ? AND service_type = ?';
            $types = 'ss';
            $params = [$dimValue, $sectionServiceType];
            break;
        case 'city':
            $sql = 'SELECT COUNT(*) AS c FROM file_entry WHERE from_city = ? AND service_type = ?';
            $types = 'ss';
            $params = [$dimValue, $sectionServiceType];
            break;
        case 'tour_arrival':
            if ($sectionServiceType !== $dimValue) {
                return 0;
            }
            $sql = 'SELECT COUNT(*) AS c FROM file_entry WHERE service_type = ?';
            $types = 's';
            $params = [$dimValue];
            $supplierOpt = trim((string) $supplierOpt);
            if ($supplierOpt !== '') {
                $sql .= ' AND supplier_name = ?';
                $types .= 's';
                $params[] = $supplierOpt;
            }
            break;
        default:
            return 0;
    }

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return 0;
    }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    return (int) ($row['c'] ?? 0);
}

/**
 * @return list<array<string, string>>
 */
function report_module_transfer_rows_for_section(
    mysqli $mysqli,
    string $mode,
    string $sectionServiceType,
    string $dimValue,
    string $fromYmd,
    string $toYmd,
    ?string $supplierOpt
): array {
    $dimValue = trim($dimValue);
    $sectionServiceType = trim($sectionServiceType);
    if ($dimValue === '' || $sectionServiceType === '') {
        return [];
    }

    $sql = '';
    $types = '';
    $params = [];

    switch ($mode) {
        case 'agent':
            $sql = 'SELECT * FROM file_entry WHERE agent_name = ? AND service_type = ? AND service_date BETWEEN ? AND ? ORDER BY service_date DESC';
            $types = 'ssss';
            $params = [$dimValue, $sectionServiceType, $fromYmd, $toYmd];
            break;
        case 'supplier':
            $sql = 'SELECT * FROM file_entry WHERE supplier_name = ? AND service_type = ? AND service_date BETWEEN ? AND ? ORDER BY service_date DESC';
            $types = 'ssss';
            $params = [$dimValue, $sectionServiceType, $fromYmd, $toYmd];
            break;
        case 'vehicle_type':
            $sql = 'SELECT * FROM file_entry WHERE vehicle_type = ? AND service_type = ? AND service_date BETWEEN ? AND ? ORDER BY service_date DESC';
            $types = 'ssss';
            $params = [$dimValue, $sectionServiceType, $fromYmd, $toYmd];
            break;
        case 'private_sic':
            $sql = 'SELECT * FROM file_entry WHERE service_cat = ? AND service_type = ? AND service_date BETWEEN ? AND ? ORDER BY service_date DESC';
            $types = 'ssss';
            $params = [$dimValue, $sectionServiceType, $fromYmd, $toYmd];
            break;
        case 'driver_name':
            $sql = 'SELECT * FROM file_entry WHERE driver_name = ? AND service_type = ? AND service_date BETWEEN ? AND ? ORDER BY service_date DESC';
            $types = 'ssss';
            $params = [$dimValue, $sectionServiceType, $fromYmd, $toYmd];
            break;
        case 'vehicle_no':
            $sql = 'SELECT * FROM file_entry WHERE vehicle_no = ? AND service_type = ? AND service_date BETWEEN ? AND ? ORDER BY service_date DESC';
            $types = 'ssss';
            $params = [$dimValue, $sectionServiceType, $fromYmd, $toYmd];
            break;
        case 'city':
            $sql = 'SELECT * FROM file_entry WHERE from_city = ? AND service_type = ? AND service_date BETWEEN ? AND ? ORDER BY service_date DESC';
            $types = 'ssss';
            $params = [$dimValue, $sectionServiceType, $fromYmd, $toYmd];
            break;
        case 'tour_arrival':
            if ($sectionServiceType !== $dimValue) {
                return [];
            }
            $sql = 'SELECT * FROM file_entry WHERE service_type = ? AND service_date BETWEEN ? AND ?';
            $types = 'sss';
            $params = [$dimValue, $fromYmd, $toYmd];
            $supplierOpt = trim((string) $supplierOpt);
            if ($supplierOpt !== '') {
                $sql .= ' AND supplier_name = ?';
                $types .= 's';
                $params[] = $supplierOpt;
            }
            $sql .= ' ORDER BY service_date DESC';
            break;
        default:
            return [];
    }

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    $out = [];
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $out[] = report_module_format_transfer_row($r);
        }
    }
    $stmt->close();

    return $out;
}

/**
 * Simpler path: skip re-query for title; use section key $st (matches loop service type label in legacy tables).
 * The legacy overwrites with header row — same value if data consistent.
 */
function report_module_build_transfer_sections_simple(
    mysqli $mysqli,
    string $mode,
    string $dimValue,
    string $fromYmd,
    string $toYmd,
    ?string $supplierOpt = null
): array {
    $sections = [];
    foreach (report_module_service_type_names($mysqli) as $st) {
        $cnt = report_module_transfer_count($mysqli, $mode, $st, $dimValue, $supplierOpt);
        if ($cnt < 1) {
            continue;
        }
        $rows = report_module_transfer_rows_for_section($mysqli, $mode, $st, $dimValue, $fromYmd, $toYmd, $supplierOpt);
        if ($rows === []) {
            continue;
        }
        $sections[] = ['title' => $st, 'rows' => $rows];
    }

    return $sections;
}

/**
 * @param array<string, string|int|float> $post trimmed POST
 * @return array{ok: bool, error?: string, sections?: list<array{title: string, rows: list<array<string, string>>}>}
 */
function report_module_run_transfer(mysqli $mysqli, string $mode, array $post): array
{
    $dim = trim((string) ($post['search_word'] ?? ''));
    if ($dim === '') {
        return ['ok' => false, 'error' => 'Please select a value and try again.'];
    }

    $fromDmy = trim((string) ($post['from_date'] ?? ''));
    $toDmy = trim((string) ($post['to_date'] ?? ''));
    $fromYmd = file_module_parse_service_date($fromDmy);
    $toYmd = file_module_parse_service_date($toDmy);
    if ($fromYmd === null || $toYmd === null) {
        return ['ok' => false, 'error' => 'Enter valid From and To dates (dd-mm-yyyy).'];
    }

    $supplierOpt = $mode === 'tour_arrival' ? trim((string) ($post['search_supplier'] ?? '')) : null;

    $sections = report_module_build_transfer_sections_simple($mysqli, $mode, $dim, $fromYmd, $toYmd, $supplierOpt);

    return ['ok' => true, 'sections' => $sections];
}

/**
 * @return array{ok: bool, error?: string, agent_name?: string, agent_address?: string, rows?: list<array<string, string>>, totals?: array{total: string, paid: string, balance: string}}
 */
function report_module_run_statement_agent(mysqli $mysqli, array $post): array
{
    $agent = trim((string) ($post['search_word'] ?? ''));
    if ($agent === '') {
        return ['ok' => false, 'error' => 'Please select an agent.'];
    }

    $fromDmy = trim((string) ($post['from_date'] ?? ''));
    $toDmy = trim((string) ($post['to_date'] ?? ''));
    $fromYmd = $fromDmy !== '' ? file_module_parse_service_date($fromDmy) : null;
    $toYmd = $toDmy !== '' ? file_module_parse_service_date($toDmy) : null;
    if (($fromDmy !== '' && $fromYmd === null) || ($toDmy !== '' && $toYmd === null)) {
        return ['ok' => false, 'error' => 'Dates must be dd-mm-yyyy when provided.'];
    }

    $addr = '';
    $stA = $mysqli->prepare('SELECT agent_address FROM agent WHERE agent_name = ? ORDER BY agent_id DESC LIMIT 1');
    if ($stA) {
        $stA->bind_param('s', $agent);
        $stA->execute();
        $ra = $stA->get_result();
        $ar = $ra ? $ra->fetch_assoc() : null;
        $stA->close();
        $addr = trim((string) ($ar['agent_address'] ?? ''));
    }

    $sql = 'SELECT * FROM file_entry WHERE agent_name = ? AND conform_status = ?';
    $types = 'ss';
    $params = [$agent, 'Confirmed'];
    if ($fromYmd !== null && $toYmd !== null) {
        $sql .= ' AND service_date BETWEEN ? AND ?';
        $types .= 'ss';
        $params[] = $fromYmd;
        $params[] = $toYmd;
    }
    $sql .= ' ORDER BY service_date DESC';

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return ['ok' => false, 'error' => 'Could not load statement.'];
    }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    $fileRows = [];
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $fileRows[] = $r;
        }
    }
    $stmt->close();

    if ($fileRows === []) {
        return ['ok' => false, 'error' => 'No results found.'];
    }

    $invStmt = $mysqli->prepare('SELECT * FROM invoices WHERE file_count_no = ? AND agent_supplier_name = ? ORDER BY Invoices_id DESC LIMIT 1');

    $outRows = [];
    $rowIndex = 0;
    $paidAmountCompare = 0.0;

    foreach ($fileRows as $rows) {
        ++$rowIndex;
        $fileCountNo = (string) ($rows['file_count_no'] ?? '');
        $Invoices_id = '';
        $invoice_create_date = '';
        $paid_totatl_dispay = '';
        $balance_amount_display = '';
        $paid_status_display = '';

        if ($invStmt) {
            $invStmt->bind_param('ss', $fileCountNo, $agent);
            $invStmt->execute();
            $invRes = $invStmt->get_result();
            $rowysHeaderinv = $invRes ? $invRes->fetch_assoc() : null;
            if ($rowysHeaderinv) {
                $Invoices_id = (string) ($rowysHeaderinv['Invoices_id'] ?? '');
                $icd = (string) ($rowysHeaderinv['invoice_create_date'] ?? '');
                $invoice_create_date = $icd !== '' ? date('d-m-Y', strtotime($icd)) : '';
                $paid_amount = (float) ($rowysHeaderinv['paid_amount'] ?? 0);
                $paid_status = (string) ($rowysHeaderinv['paid_status'] ?? '');

                $unit_prices_sell = (float) ($rows['selling_price'] ?? 0);

                if ($rowIndex === 1) {
                    $paidAmountCompare = $paid_amount;
                }
                if ($paidAmountCompare >= $unit_prices_sell) {
                    $paid_totatl_dispay = (string) $unit_prices_sell;
                    $paidAmountCompare -= $unit_prices_sell;
                    $balance_amount_display = '';
                    $paid_status_display = ' Paid';
                } elseif ($paidAmountCompare < 1) {
                    $paid_totatl_dispay = '';
                    $paidAmountCompare -= $unit_prices_sell;
                    $balance_amount_display = '';
                } elseif ($paidAmountCompare > 1 && $paidAmountCompare < $unit_prices_sell) {
                    $paid_totatl_dispay = (string) $paidAmountCompare;
                    $balance_amount_display = (string) ($unit_prices_sell - $paidAmountCompare);
                    $paid_status_display = 'Partial Paid';
                } else {
                    $balance_amount_display = (string) $unit_prices_sell;
                    $paid_status_display = '';
                }
                if ($paid_status === '') {
                    $balance_amount_display = (string) $unit_prices_sell;
                }

                [$fromLoc, $toLoc] = report_module_legacy_pickup_drop($rows);
                $sd = (string) ($rows['service_date'] ?? '');
                $serviceDate = $sd !== '' ? date('d-m-Y', strtotime($sd)) : '';
                $qty = (string) ($rows['no_of_pax'] ?? '');
                $type = (string) ($rows['service_cat'] ?? '');
                $unit_prices = (float) ($rows['selling_price'] ?? 0);
                $q = (int) $rows['no_of_pax'];
                if ($type === 'SIC' && $q > 0) {
                    $unit_price = (string) ($unit_prices / $q);
                } else {
                    $unit_price = (string) $unit_prices;
                }

                $guest = trim((string) ($rows['first_name'] ?? '') . ' ' . (string) ($rows['last_name'] ?? ''));
                $desc = $fromLoc . ' To ' . $toLoc;

                $outRows[] = [
                    'invoices_id' => $Invoices_id,
                    'invoice_create_date' => $invoice_create_date,
                    'service_date' => $serviceDate,
                    'guest' => $guest,
                    'description' => $desc,
                    'qty' => $qty,
                    'type' => $type,
                    'unit_price' => $unit_price,
                    'selling_price' => (string) ($rows['selling_price'] ?? ''),
                    'item_amount' => (string) $unit_prices_sell,
                    'paid' => $paid_totatl_dispay,
                    'balance' => $balance_amount_display,
                    'acc_status' => $paid_status_display,
                    'status' => (string) ($rows['conform_status'] ?? ''),
                    'user_create' => (string) ($rows['user_enter_by'] ?? ''),
                ];
            }
        }
    }
    if ($invStmt) {
        $invStmt->close();
    }

    if ($outRows === []) {
        return ['ok' => false, 'error' => 'No invoice-linked rows for this agent.'];
    }

    $totals = ['total' => '0.00', 'paid' => '0.00', 'balance' => '0.00'];
    if ($fromYmd !== null && $toYmd !== null) {
        $stT = $mysqli->prepare('SELECT SUM(total_price) AS s FROM invoices WHERE agent_supplier_name = ? AND invoice_create_date BETWEEN ? AND ?');
        if ($stT) {
            $stT->bind_param('sss', $agent, $fromYmd, $toYmd);
            $stT->execute();
            $rt = $stT->get_result();
            $trow = $rt ? $rt->fetch_assoc() : null;
            $stT->close();
            $totals['total'] = number_format((float) ($trow['s'] ?? 0), 2);
        }
        $stP = $mysqli->prepare('SELECT SUM(paid_amount) AS s FROM invoices WHERE agent_supplier_name = ? AND invoice_create_date BETWEEN ? AND ?');
        if ($stP) {
            $stP->bind_param('sss', $agent, $fromYmd, $toYmd);
            $stP->execute();
            $rp = $stP->get_result();
            $prow = $rp ? $rp->fetch_assoc() : null;
            $stP->close();
            $totals['paid'] = number_format((float) ($prow['s'] ?? 0), 2);
        }
        $sumTot = (float) str_replace(',', '', $totals['total']);
        $sumPaid = (float) str_replace(',', '', $totals['paid']);
        $totals['balance'] = number_format($sumTot - $sumPaid, 2);
    }

    return [
        'ok' => true,
        'agent_name' => $agent,
        'agent_address' => $addr,
        'rows' => $outRows,
        'totals' => $totals,
    ];
}

/**
 * @return array{ok: bool, error?: string, supplier_name?: string, rows?: list<array<string, string>>, totals?: array{total: string, paid: string, balance: string}}
 */
function report_module_run_statement_supplier(mysqli $mysqli, array $post): array
{
    $supplier = trim((string) ($post['search_word'] ?? ''));
    if ($supplier === '') {
        return ['ok' => false, 'error' => 'Please select a supplier.'];
    }

    $fromDmy = trim((string) ($post['from_date'] ?? ''));
    $toDmy = trim((string) ($post['to_date'] ?? ''));
    $fromYmd = $fromDmy !== '' ? file_module_parse_service_date($fromDmy) : null;
    $toYmd = $toDmy !== '' ? file_module_parse_service_date($toDmy) : null;
    if (($fromDmy !== '' && $fromYmd === null) || ($toDmy !== '' && $toYmd === null)) {
        return ['ok' => false, 'error' => 'Dates must be dd-mm-yyyy when provided.'];
    }

    $cntStmt = $mysqli->prepare('SELECT COUNT(*) AS c FROM file_entry WHERE supplier_name = ?');
    if (!$cntStmt) {
        return ['ok' => false, 'error' => 'Could not load statement.'];
    }
    $cntStmt->bind_param('s', $supplier);
    $cntStmt->execute();
    $cr = $cntStmt->get_result();
    $crow = $cr ? $cr->fetch_assoc() : null;
    $cntStmt->close();
    if ((int) ($crow['c'] ?? 0) < 1) {
        return ['ok' => false, 'error' => 'No results found.'];
    }

    if ($fromYmd !== null && $toYmd !== null) {
        $sql = 'SELECT * FROM file_entry WHERE supplier_name = ? AND date BETWEEN ? AND ? ORDER BY date DESC';
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            return ['ok' => false, 'error' => 'Could not load statement.'];
        }
        $stmt->bind_param('sss', $supplier, $fromYmd, $toYmd);
    } else {
        $stmt = $mysqli->prepare('SELECT * FROM file_entry WHERE supplier_name = ? ORDER BY date DESC');
        if (!$stmt) {
            return ['ok' => false, 'error' => 'Could not load statement.'];
        }
        $stmt->bind_param('s', $supplier);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $fileRows = [];
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $fileRows[] = $r;
        }
    }
    $stmt->close();

    $invStmt = $mysqli->prepare('SELECT * FROM invoices WHERE file_count_no = ? AND agent_supplier_name = ? ORDER BY Invoices_id DESC LIMIT 1');

    $outRows = [];
    $rowIndex = 0;
    $paidAmountCompare = 0.0;
    $num = 1;

    foreach ($fileRows as $rows) {
        ++$rowIndex;
        $fileCountNo = (string) ($rows['file_count_no'] ?? '');
        $file_no = (string) ($rows['file_no'] ?? '');
        $ref_no = (string) ($rows['ref_no'] ?? '');
        $agent_name = (string) ($rows['agent_name'] ?? '');
        $sd = (string) ($rows['service_date'] ?? '');
        $service_date = $sd !== '' ? date('d-m-Y', strtotime($sd)) : '';

        $invoice_create_date = '';
        $invoice_no = '';
        $paid_totatl_dispay = '';
        $balance_amount_display = '';
        $paid_status_display = '';

        if ($invStmt) {
            $invStmt->bind_param('ss', $fileCountNo, $supplier);
            $invStmt->execute();
            $invRes = $invStmt->get_result();
            $rowysHeaderinv = $invRes ? $invRes->fetch_assoc() : null;
            if ($rowysHeaderinv) {
                $icd = (string) ($rowysHeaderinv['invoice_create_date'] ?? '');
                $invoice_create_date = $icd !== '' ? date('d-m-Y', strtotime($icd)) : '';
                $invoice_no = (string) ($rowysHeaderinv['invoice_no'] ?? '');
                $paid_amount = (float) ($rowysHeaderinv['paid_amount'] ?? 0);
                $paid_status = (string) ($rowysHeaderinv['paid_status'] ?? '');

                $unit_prices_sell = (float) ($rows['buying_price'] ?? 0);

                if ($rowIndex === 1) {
                    $paidAmountCompare = $paid_amount;
                }
                if ($paidAmountCompare >= $unit_prices_sell) {
                    $paid_totatl_dispay = (string) $unit_prices_sell;
                    $paidAmountCompare -= $unit_prices_sell;
                    $balance_amount_display = '';
                    $paid_status_display = ' Paid';
                } elseif ($paidAmountCompare < 1) {
                    $paid_totatl_dispay = '';
                    $paidAmountCompare -= $unit_prices_sell;
                    $balance_amount_display = '';
                    $paid_status_display = 'Partial Paid';
                } elseif ($paidAmountCompare > 1 && $paidAmountCompare < $unit_prices_sell) {
                    $paid_totatl_dispay = (string) $paidAmountCompare;
                    $balance_amount_display = (string) ($unit_prices_sell - $paidAmountCompare);
                    $paid_status_display = 'Partial Paid';
                } else {
                    $balance_amount_display = (string) $unit_prices_sell;
                    $paid_status_display = '';
                }
                if ($paid_status === '') {
                    $balance_amount_display = (string) $unit_prices_sell;
                }

                $outRows[] = [
                    'no' => (string) $num,
                    'doc_id' => $file_no,
                    'issued_date' => $invoice_create_date,
                    'ref_no' => $ref_no,
                    'agent' => $agent_name,
                    'service_date' => $service_date,
                    'inv_no' => $invoice_no,
                    'net_total' => (string) $unit_prices_sell,
                    'paid_amt' => $paid_totatl_dispay,
                    'balance' => $balance_amount_display,
                ];
                ++$num;
            }
        }
    }
    if ($invStmt) {
        $invStmt->close();
    }

    if ($outRows === []) {
        return ['ok' => false, 'error' => 'No invoice-linked rows for this supplier in range.'];
    }

    $totals = ['total' => '0.00', 'paid' => '0.00', 'balance' => '0.00'];
    if ($fromYmd !== null && $toYmd !== null) {
        $stT = $mysqli->prepare('SELECT SUM(total_price) AS s FROM invoices WHERE agent_supplier_name = ? AND invoice_create_date BETWEEN ? AND ?');
        if ($stT) {
            $stT->bind_param('sss', $supplier, $fromYmd, $toYmd);
            $stT->execute();
            $rt = $stT->get_result();
            $trow = $rt ? $rt->fetch_assoc() : null;
            $stT->close();
            $totals['total'] = number_format((float) ($trow['s'] ?? 0), 2);
        }
        $stP = $mysqli->prepare('SELECT SUM(paid_amount) AS s FROM invoices WHERE agent_supplier_name = ? AND invoice_create_date BETWEEN ? AND ?');
        if ($stP) {
            $stP->bind_param('sss', $supplier, $fromYmd, $toYmd);
            $stP->execute();
            $rp = $stP->get_result();
            $prow = $rp ? $rp->fetch_assoc() : null;
            $stP->close();
            $totals['paid'] = number_format((float) ($prow['s'] ?? 0), 2);
        }
        $sumTot = (float) str_replace(',', '', $totals['total']);
        $sumPaid = (float) str_replace(',', '', $totals['paid']);
        $totals['balance'] = number_format($sumTot - $sumPaid, 2);
    }

    return [
        'ok' => true,
        'supplier_name' => $supplier,
        'rows' => $outRows,
        'totals' => $totals,
    ];
}

/**
 * @param array<string, string|int|float> $post
 * @return array{ok: bool, error?: string, kind: string, transfer?: array, statement_agent?: array, statement_supplier?: array}
 */
function report_module_run(mysqli $mysqli, string $mode, array $post): array
{
    $mode = report_module_normalize_mode($mode);

    if (in_array($mode, ['statement_agent', 'statement_supplier'], true)) {
        if ($mode === 'statement_agent') {
            $r = report_module_run_statement_agent($mysqli, $post);

            return $r['ok'] ? ['ok' => true, 'kind' => 'statement_agent', 'statement_agent' => $r] : ['ok' => false, 'kind' => 'statement_agent', 'error' => $r['error'] ?? 'Error'];
        }
        $r = report_module_run_statement_supplier($mysqli, $post);

        return $r['ok'] ? ['ok' => true, 'kind' => 'statement_supplier', 'statement_supplier' => $r] : ['ok' => false, 'kind' => 'statement_supplier', 'error' => $r['error'] ?? 'Error'];
    }

    $r = report_module_run_transfer($mysqli, $mode, $post);

    return $r['ok']
        ? ['ok' => true, 'kind' => 'transfer', 'transfer' => $r]
        : ['ok' => false, 'kind' => 'transfer', 'error' => $r['error'] ?? 'Error'];
}

function report_module_export_filename(string $mode): string
{
    $mode = report_module_normalize_mode($mode);
    $stamp = date('Ymd_His');

    return 'report_' . $mode . '_' . $stamp . '.xlsx';
}

/**
 * @param array<string, mixed> $reportOutcome
 * @return list<list<string>>
 */
function report_module_export_rows(array $reportOutcome): array
{
    $kind = (string) ($reportOutcome['kind'] ?? '');
    if ($kind === 'transfer') {
        /** @var array{sections?: list<array{title: string, rows: list<array<string, string>>}>} $transfer */
        $transfer = (array) ($reportOutcome['transfer'] ?? []);
        $sections = (array) ($transfer['sections'] ?? []);
        $rows = [];
        $first = true;
        foreach ($sections as $sec) {
            $title = (string) ($sec['title'] ?? '');
            $items = (array) ($sec['rows'] ?? []);
            if ($first) {
                $rows[] = ['Report :'];
                $first = false;
            }
            $rows[] = [$title . ' Transfer:'];
            $rows[] = [
                'Supplier Name', 'Agent Name', 'File No.', 'Client Name', 'Service Date', 'Service Name',
                'Flight No.', 'Flight Time', 'Pick Up Time', 'Pick Up', 'Drop Off', 'Vehicle Type',
                'Driver Name', 'PAX SIM NO', 'Tour type',
            ];
            foreach ($items as $r) {
                $rows[] = [
                    (string) ($r['supplier_name'] ?? ''),
                    (string) ($r['agent_name'] ?? ''),
                    (string) ($r['file_no'] ?? ''),
                    (string) ($r['client_name'] ?? ''),
                    (string) ($r['service_date'] ?? ''),
                    (string) ($r['service'] ?? ''),
                    (string) ($r['flight_no'] ?? ''),
                    (string) ($r['flight_time'] ?? ''),
                    (string) ($r['pickup_time'] ?? ''),
                    (string) ($r['pick_up'] ?? ''),
                    (string) ($r['drop_off'] ?? ''),
                    (string) ($r['vehicle_type'] ?? ''),
                    (string) ($r['driver_name'] ?? ''),
                    (string) ($r['pax_mobile'] ?? ''),
                    (string) ($r['service_cat'] ?? ''),
                ];
            }
            $rows[] = [];
        }

        return $rows;
    }

    if ($kind === 'statement_agent') {
        /** @var array{agent_name?: string, agent_address?: string, rows?: list<array<string, string>>, totals?: array<string, string>} $stmt */
        $stmt = (array) ($reportOutcome['statement_agent'] ?? []);
        $items = (array) ($stmt['rows'] ?? []);
        $totals = (array) ($stmt['totals'] ?? []);
        $rows = [
            ['STATEMENT REPORT'],
            ['Agent :', (string) ($stmt['agent_name'] ?? '')],
            ['Address :', (string) ($stmt['agent_address'] ?? '')],
            ['Currency :', 'Ringgit Malaysia', 'Type', 'INVOICE'],
            [],
            [
                'No.', 'Invoice No.', 'Issue Date', 'Due Date', 'Service Date', 'Guest Name', 'Description', 'Qty',
                'Type', 'Price', 'Item Amount', 'Total Invoice', 'Paid', 'Balance', 'Acc. Status', 'Status', 'UserCreate',
            ],
        ];
        $num = 1;
        foreach ($items as $r) {
            $rows[] = [
                (string) $num++,
                (string) ($r['invoices_id'] ?? ''),
                (string) ($r['invoice_create_date'] ?? ''),
                '',
                (string) ($r['service_date'] ?? ''),
                (string) ($r['guest'] ?? ''),
                (string) ($r['description'] ?? ''),
                (string) ($r['qty'] ?? ''),
                (string) ($r['type'] ?? ''),
                (string) ($r['unit_price'] ?? ''),
                (string) ($r['selling_price'] ?? ''),
                (string) ($r['item_amount'] ?? ''),
                (string) ($r['paid'] ?? ''),
                (string) ($r['balance'] ?? ''),
                (string) ($r['acc_status'] ?? ''),
                (string) ($r['status'] ?? ''),
                (string) ($r['user_create'] ?? ''),
            ];
        }
        $rows[] = [
            'Total', '', '', '', '', '', '', '', '', '', '',
            (string) ($totals['total'] ?? ''),
            (string) ($totals['paid'] ?? ''),
            (string) ($totals['balance'] ?? ''),
            '', '', '',
        ];

        return $rows;
    }

    if ($kind === 'statement_supplier') {
        /** @var array{supplier_name?: string, rows?: list<array<string, string>>, totals?: array<string, string>} $stmt */
        $stmt = (array) ($reportOutcome['statement_supplier'] ?? []);
        $items = (array) ($stmt['rows'] ?? []);
        $totals = (array) ($stmt['totals'] ?? []);
        $rows = [
            ['CREDITOR INVOICE COSTING REPORT'],
            [],
            ['Transfer', (string) ($stmt['supplier_name'] ?? ''), 'Invoice Status', 'ACTIVE'],
            ['Account Status'],
            ['Source Type'],
            ['Currency', 'Ringgit Malaysia'],
            [],
            ['No.', 'Doc ID.', 'Issued Date', 'O/S Ref.', 'Agent', 'Service Date', 'Inv # No.', 'Net Total', 'Paid Amt.', 'Balance'],
        ];
        foreach ($items as $r) {
            $rows[] = [
                (string) ($r['no'] ?? ''),
                (string) ($r['doc_id'] ?? ''),
                (string) ($r['issued_date'] ?? ''),
                (string) ($r['ref_no'] ?? ''),
                (string) ($r['agent'] ?? ''),
                (string) ($r['service_date'] ?? ''),
                (string) ($r['inv_no'] ?? ''),
                (string) ($r['net_total'] ?? ''),
                (string) ($r['paid_amt'] ?? ''),
                (string) ($r['balance'] ?? ''),
            ];
        }
        $rows[] = [
            'Total', '', '', '', '', '', '',
            (string) ($totals['total'] ?? ''),
            (string) ($totals['paid'] ?? ''),
            (string) ($totals['balance'] ?? ''),
        ];

        return $rows;
    }

    return [];
}
