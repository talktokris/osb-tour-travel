<?php

declare(strict_types=1);

require_once __DIR__ . '/file_module_service.php';
require_once __DIR__ . '/report_module_service.php';

/** @var list<string> */
const INVOICE_MODULE_MODES = [
    'outstanding_agent',
    'outstanding_supplier',
    'paid_agent',
    'paid_supplier',
    'statement_agent',
    'statement_supplier',
    'pay_single',
    'pay_multiple',
];

function invoice_module_normalize_mode(string $mode): string
{
    $mode = strtolower(trim($mode));

    return in_array($mode, INVOICE_MODULE_MODES, true) ? $mode : 'outstanding_agent';
}

function invoice_module_parse_ymd_date(string $v): ?string
{
    $v = trim($v);
    if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $v, $m)) {
        return null;
    }
    $y = (int) $m[1];
    $mo = (int) $m[2];
    $d = (int) $m[3];

    return checkdate($mo, $d, $y) ? $v : null;
}

function invoice_module_dmy_from_ymd(string $ymd): string
{
    $p = explode('-', $ymd);

    return count($p) === 3 ? ($p[2] . '-' . $p[1] . '-' . $p[0]) : $ymd;
}

/**
 * @return list<string>
 */
function invoice_module_country_names(mysqli $mysqli): array
{
    $out = [];
    $res = $mysqli->query('SELECT country_name FROM country ORDER BY country_name ASC');
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $v = trim((string) ($r['country_name'] ?? ''));
            if ($v !== '') {
                $out[] = $v;
            }
        }
        $res->free();
    }

    return $out;
}

/**
 * @return list<string>
 */
function invoice_module_city_names(mysqli $mysqli, string $country = ''): array
{
    $country = trim($country);
    $out = [];
    if ($country === '') {
        $res = $mysqli->query('SELECT city_name FROM city ORDER BY city_name ASC');
    } else {
        $stmt = $mysqli->prepare('SELECT city_name FROM city WHERE city_country_name = ? ORDER BY city_name ASC');
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param('s', $country);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
    }
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $v = trim((string) ($r['city_name'] ?? ''));
            if ($v !== '') {
                $out[] = $v;
            }
        }
        $res->free();
    }

    return $out;
}

/**
 * @return list<string>
 */
function invoice_module_agent_names(mysqli $mysqli, string $country = '', string $city = ''): array
{
    $country = trim($country);
    $city = trim($city);
    $sql = 'SELECT DISTINCT agent_name FROM agent';
    $where = [];
    $types = '';
    $params = [];
    if ($country !== '') {
        $where[] = 'agent_country = ?';
        $types .= 's';
        $params[] = $country;
    }
    if ($city !== '') {
        $where[] = 'agent_city = ?';
        $types .= 's';
        $params[] = $city;
    }
    if ($where !== []) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY agent_name ASC';

    $out = [];
    if ($params === []) {
        $res = $mysqli->query($sql);
    } else {
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
    }
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $v = trim((string) ($r['agent_name'] ?? ''));
            if ($v !== '') {
                $out[] = $v;
            }
        }
        $res->free();
    }

    return $out;
}

/**
 * @return list<string>
 */
function invoice_module_supplier_names(mysqli $mysqli, string $country = '', string $city = ''): array
{
    $country = trim($country);
    $city = trim($city);
    $sql = 'SELECT DISTINCT supplier_name FROM supplier';
    $where = [];
    $types = '';
    $params = [];
    if ($country !== '') {
        $where[] = 'supplier_country = ?';
        $types .= 's';
        $params[] = $country;
    }
    if ($city !== '') {
        $where[] = 'supplier_city = ?';
        $types .= 's';
        $params[] = $city;
    }
    if ($where !== []) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY supplier_name ASC';

    $out = [];
    if ($params === []) {
        $res = $mysqli->query($sql);
    } else {
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
    }
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $v = trim((string) ($r['supplier_name'] ?? ''));
            if ($v !== '') {
                $out[] = $v;
            }
        }
        $res->free();
    }

    return $out;
}

/**
 * @return list<string>
 */
function invoice_module_invoice_refs(mysqli $mysqli): array
{
    $out = [];
    $res = $mysqli->query('SELECT DISTINCT Invoices_id FROM invoices ORDER BY Invoices_id DESC');
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $v = trim((string) ($r['Invoices_id'] ?? ''));
            if ($v !== '') {
                $out[] = $v;
            }
        }
        $res->free();
    }

    return $out;
}

function invoice_module_num(string $v): float
{
    $v = trim($v);
    if ($v === '') {
        return 0.0;
    }

    return (float) preg_replace('/[^0-9.\-]/', '', $v);
}

function invoice_module_due_amount(array $inv): float
{
    $bal = invoice_module_num((string) ($inv['balance_amount'] ?? ''));
    if ($bal > 0) {
        return $bal;
    }

    return max(0.0, invoice_module_num((string) ($inv['total_price'] ?? '')));
}

/**
 * @param array<string, mixed> $row
 * @return array<string, string>
 */
function invoice_module_format_invoice_row(array $row): array
{
    $total = invoice_module_num((string) ($row['total_price'] ?? ''));
    $bal = invoice_module_num((string) ($row['balance_amount'] ?? ''));
    $paid = invoice_module_num((string) ($row['paid_amount'] ?? ''));
    $balShow = $bal > 0 ? $bal : max(0.0, $total - $paid);

    return [
        'invoices_id' => (string) ($row['Invoices_id'] ?? ''),
        'file_count_no' => (string) ($row['file_count_no'] ?? ''),
        'file_no' => (string) ($row['file_no'] ?? ''),
        'ref_no' => (string) ($row['ref_no'] ?? ''),
        'invoice_type' => (string) ($row['invoice_type'] ?? ''),
        'agent_supplier_name' => (string) ($row['agent_supplier_name'] ?? ''),
        'invoice_create_date' => (string) ($row['invoice_create_date'] ?? ''),
        'paid_date' => (string) ($row['paid_date'] ?? ''),
        'cheque_no' => (string) ($row['cheque_no'] ?? ''),
        'total_price' => number_format($total, 2, '.', ''),
        'paid_amount' => number_format($paid, 2, '.', ''),
        'balance_amount' => number_format($balShow, 2, '.', ''),
        'paid_status' => (string) ($row['paid_status'] ?? ''),
    ];
}

/**
 * @return array{sql: string, types: string, params: list<string>}
 */
function invoice_module_build_invoice_query(array $post, bool $isPaidView): array
{
    $searchAgent = trim((string) ($post['search_agent'] ?? ''));
    $searchSupplier = trim((string) ($post['search_supplier'] ?? ''));
    $searchRef = trim((string) ($post['search_ref'] ?? ''));
    $from = trim((string) ($post['from_date'] ?? ''));
    $to = trim((string) ($post['to_date'] ?? ''));

    $fromYmd = invoice_module_parse_ymd_date($from);
    $toYmd = invoice_module_parse_ymd_date($to);
    if ($fromYmd === null || $toYmd === null) {
        $fromYmd = date('Y-m-01');
        $toYmd = date('Y-m-d');
    }

    $dateColumn = $isPaidView ? 'paid_date' : 'invoice_create_date';
    $where = [];
    $types = '';
    $params = [];
    if ($isPaidView) {
        $where[] = "paid_status <> ''";
    } else {
        $where[] = "paid_status <> 'Paid'";
    }

    if ($searchAgent !== '') {
        $where[] = 'agent_supplier_name = ?';
        $types .= 's';
        $params[] = $searchAgent;
    } elseif ($searchSupplier !== '') {
        $where[] = 'agent_supplier_name = ?';
        $types .= 's';
        $params[] = $searchSupplier;
    } elseif ($searchRef !== '') {
        $where[] = 'Invoices_id = ?';
        $types .= 's';
        $params[] = $searchRef;
    }

    $where[] = $dateColumn . ' BETWEEN ? AND ?';
    $types .= 'ss';
    $params[] = $fromYmd;
    $params[] = $toYmd;

    $orderBy = $isPaidView ? 'paid_date DESC' : 'invoice_create_date DESC';
    $sql = 'SELECT * FROM invoices WHERE ' . implode(' AND ', $where) . ' ORDER BY ' . $orderBy;

    return ['sql' => $sql, 'types' => $types, 'params' => $params];
}

/**
 * @param array<string, string|int|float> $post
 * @return array{ok: bool, error?: string, rows?: list<array<string, string>>, context?: array<string, string>}
 */
function invoice_module_run_invoice_list(mysqli $mysqli, array $post, bool $isPaidView): array
{
    $q = invoice_module_build_invoice_query($post, $isPaidView);
    $stmt = $mysqli->prepare($q['sql']);
    if (!$stmt) {
        return ['ok' => false, 'error' => 'Could not load invoice list.'];
    }
    $stmt->bind_param($q['types'], ...$q['params']);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $rows[] = invoice_module_format_invoice_row($r);
        }
    }
    $stmt->close();

    return [
        'ok' => true,
        'rows' => $rows,
        'context' => [
            'search_agent' => trim((string) ($post['search_agent'] ?? '')),
            'search_supplier' => trim((string) ($post['search_supplier'] ?? '')),
            'search_ref' => trim((string) ($post['search_ref'] ?? '')),
            'from_date' => trim((string) ($post['from_date'] ?? '')),
            'to_date' => trim((string) ($post['to_date'] ?? '')),
        ],
    ];
}

/**
 * @return list<array<string, string>>
 */
function invoice_module_payment_rows(mysqli $mysqli, string $invoiceNo): array
{
    $out = [];
    $stmt = $mysqli->prepare('SELECT * FROM payment WHERE invoice_no = ? ORDER BY payment_id ASC');
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param('s', $invoiceNo);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $out[] = [
                'paid_date' => (string) ($r['paid_date'] ?? ''),
                'cheque_no' => (string) ($r['cheque_no'] ?? ''),
                'paid_amount' => (string) ($r['paid_amount'] ?? ''),
            ];
        }
    }
    $stmt->close();

    return $out;
}

/**
 * @return array<string, mixed>|null
 */
function invoice_module_invoice_by_id(mysqli $mysqli, string $invoiceId): ?array
{
    $stmt = $mysqli->prepare('SELECT * FROM invoices WHERE Invoices_id = ? LIMIT 1');
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('s', $invoiceId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    return $row ?: null;
}

/**
 * @param array<string, string|int|float> $post
 * @return array{ok: bool, error?: string}
 */
function invoice_module_apply_single_payment(mysqli $mysqli, array $post): array
{
    $id = trim((string) ($post['invoice_id'] ?? ''));
    $paidDate = trim((string) ($post['paid_date'] ?? ''));
    $chequeNo = trim((string) ($post['cheque_no'] ?? ''));
    $payingAmt = invoice_module_num((string) ($post['paying_amt'] ?? '0'));

    if ($id === '' || invoice_module_parse_ymd_date($paidDate) === null || $payingAmt <= 0) {
        return ['ok' => false, 'error' => 'Invoice, paid date and amount are required.'];
    }

    $inv = invoice_module_invoice_by_id($mysqli, $id);
    if ($inv === null) {
        return ['ok' => false, 'error' => 'Invoice not found.'];
    }

    $invoiceAmt = max(0.0, invoice_module_num((string) ($inv['total_price'] ?? '')));
    $newPaid = min($invoiceAmt, $payingAmt);
    $newBal = max(0.0, $invoiceAmt - $newPaid);
    $status = ($newPaid >= $invoiceAmt) ? 'Paid' : 'Partial Paid';

    $invoiceNo = (string) ($inv['invoice_no'] ?? '');
    $agentSupplier = (string) ($inv['agent_supplier_name'] ?? '');
    $fileCountNo = (string) ($inv['file_count_no'] ?? '');

    $up = $mysqli->prepare('UPDATE invoices SET paid_date = ?, cheque_no = ?, paid_amount = ?, balance_amount = ?, paid_status = ? WHERE Invoices_id = ?');
    if (!$up) {
        return ['ok' => false, 'error' => 'Could not update invoice.'];
    }
    $paidStr = number_format($newPaid, 2, '.', '');
    $balStr = number_format($newBal, 2, '.', '');
    $up->bind_param('ssssss', $paidDate, $chequeNo, $paidStr, $balStr, $status, $id);
    $okUp = $up->execute();
    $up->close();
    if (!$okUp) {
        return ['ok' => false, 'error' => 'Could not save payment.'];
    }

    $ins = $mysqli->prepare('INSERT INTO payment (file_count_no, invoice_no, paid_date, cheque_no, paid_amount, agent_supplier_name) VALUES (?, ?, ?, ?, ?, ?)');
    if ($ins) {
        $ins->bind_param('ssssss', $fileCountNo, $invoiceNo, $paidDate, $chequeNo, $paidStr, $agentSupplier);
        $ins->execute();
        $ins->close();
    }

    return ['ok' => true];
}

/**
 * @param array<string, string|int|float> $post
 * @return array{ok: bool, error?: string}
 */
function invoice_module_apply_multi_payment(mysqli $mysqli, array $post): array
{
    $paidDate = trim((string) ($post['paid_date'] ?? ''));
    $chequeNo = trim((string) ($post['cheque_no'] ?? ''));
    $remaining = invoice_module_num((string) ($post['paying_amt'] ?? '0'));
    $rawIds = trim((string) ($post['selected_invoice_ids'] ?? ''));
    if (invoice_module_parse_ymd_date($paidDate) === null || $remaining <= 0 || $rawIds === '') {
        return ['ok' => false, 'error' => 'Date, amount and selected invoices are required.'];
    }

    $ids = array_values(array_filter(array_map('trim', explode('|', $rawIds)), static fn(string $v): bool => $v !== ''));
    if ($ids === []) {
        return ['ok' => false, 'error' => 'No invoices selected.'];
    }

    $up = $mysqli->prepare('UPDATE invoices SET paid_date = ?, cheque_no = ?, paid_amount = ?, balance_amount = ?, paid_status = ? WHERE Invoices_id = ?');
    $ins = $mysqli->prepare('INSERT INTO payment (file_count_no, invoice_no, paid_date, cheque_no, paid_amount, agent_supplier_name) VALUES (?, ?, ?, ?, ?, ?)');
    if (!$up) {
        return ['ok' => false, 'error' => 'Could not prepare payment update.'];
    }

    foreach ($ids as $id) {
        if ($remaining <= 0) {
            break;
        }
        $inv = invoice_module_invoice_by_id($mysqli, $id);
        if ($inv === null) {
            continue;
        }
        $total = invoice_module_num((string) ($inv['total_price'] ?? ''));
        $alreadyPaid = invoice_module_num((string) ($inv['paid_amount'] ?? ''));
        $due = max(0.0, $total - $alreadyPaid);
        if ($due <= 0.0) {
            continue;
        }
        $alloc = min($due, $remaining);
        if ($alloc <= 0.0) {
            continue;
        }

        $newPaid = min($total, $alreadyPaid + $alloc);
        $newBal = max(0.0, $total - $newPaid);
        $status = ($newBal <= 0.0) ? 'Paid' : 'Partial Paid';
        $paidStr = number_format($newPaid, 2, '.', '');
        $balStr = number_format($newBal, 2, '.', '');
        $up->bind_param('ssssss', $paidDate, $chequeNo, $paidStr, $balStr, $status, $id);
        $up->execute();

        if ($ins) {
            $allocStr = number_format($alloc, 2, '.', '');
            $fileCountNo = (string) ($inv['file_count_no'] ?? '');
            $invoiceNo = (string) ($inv['invoice_no'] ?? '');
            $agentSupplier = (string) ($inv['agent_supplier_name'] ?? '');
            $ins->bind_param('ssssss', $fileCountNo, $invoiceNo, $paidDate, $chequeNo, $allocStr, $agentSupplier);
            $ins->execute();
        }
        $remaining -= $alloc;
    }
    $up->close();
    if ($ins) {
        $ins->close();
    }

    return ['ok' => true];
}

/**
 * @param array<string, string|int|float> $post
 * @return array{ok: bool, error?: string, statement_agent?: array, statement_supplier?: array}
 */
function invoice_module_run_statement(mysqli $mysqli, string $mode, array $post): array
{
    $searchWord = trim((string) ($post['search_word'] ?? ''));
    $fromYmd = trim((string) ($post['from_date'] ?? ''));
    $toYmd = trim((string) ($post['to_date'] ?? ''));
    $fromDmy = $fromYmd !== '' ? invoice_module_dmy_from_ymd($fromYmd) : '';
    $toDmy = $toYmd !== '' ? invoice_module_dmy_from_ymd($toYmd) : '';

    $payload = [
        'search_word' => $searchWord,
        'from_date' => $fromDmy,
        'to_date' => $toDmy,
    ];
    if ($mode === 'statement_agent') {
        $r = report_module_run_statement_agent($mysqli, $payload);

        return $r['ok'] ? ['ok' => true, 'statement_agent' => $r] : ['ok' => false, 'error' => (string) ($r['error'] ?? 'No result found.')];
    }
    $r = report_module_run_statement_supplier($mysqli, $payload);

    return $r['ok'] ? ['ok' => true, 'statement_supplier' => $r] : ['ok' => false, 'error' => (string) ($r['error'] ?? 'No result found.')];
}

