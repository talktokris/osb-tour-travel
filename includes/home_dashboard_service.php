<?php

declare(strict_types=1);

function home_dashboard_flash_set(string $type, string $message): void
{
    $_SESSION['home_dashboard_flash'] = ['type' => $type, 'message' => $message];
}

function home_dashboard_flash_get(): ?array
{
    if (!isset($_SESSION['home_dashboard_flash'])) {
        return null;
    }
    $f = $_SESSION['home_dashboard_flash'];
    unset($_SESSION['home_dashboard_flash']);
    return is_array($f) ? $f : null;
}

function home_dashboard_csrf_token(): string
{
    if (empty($_SESSION['home_dashboard_csrf'])) {
        $_SESSION['home_dashboard_csrf'] = bin2hex(random_bytes(16));
    }
    return (string) $_SESSION['home_dashboard_csrf'];
}

function home_dashboard_csrf_validate(string $token): bool
{
    $s = (string) ($_SESSION['home_dashboard_csrf'] ?? '');
    return $s !== '' && hash_equals($s, $token);
}

/** @return array{label:string,pattern:string}|null */
function home_dashboard_parse_letter_param(string $raw): ?array
{
    $raw = trim($raw);
    if ($raw === '') {
        return null;
    }
    $parts = explode('-', $raw, 2);
    if (count($parts) < 2) {
        return null;
    }
    return ['label' => $parts[0], 'pattern' => rawurldecode($parts[1])];
}

/**
 * Resolve A–Z filter: prefer `az` (All|A..Z), else `letter` (legacy Label-pattern).
 */
function home_dashboard_resolve_supplier_like_pattern(): ?string
{
    if (isset($_GET['az']) && is_string($_GET['az'])) {
        $az = trim($_GET['az']);
        if ($az === 'All') {
            return '%%';
        }
        if (preg_match('/^[A-Z]$/', $az)) {
            return $az . '%';
        }
    }
    $letter = isset($_GET['letter']) ? (string) $_GET['letter'] : '';
    $parsed = home_dashboard_parse_letter_param($letter);
    return $parsed['pattern'] ?? null;
}

/** @return list<string> */
function home_dashboard_countries(mysqli $mysqli): array
{
    $rows = [];
    $r = $mysqli->query('SELECT country_name FROM country ORDER BY country_name');
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $n = trim((string) ($row['country_name'] ?? ''));
            if ($n !== '') {
                $rows[] = $n;
            }
        }
    }
    return $rows;
}

/** @return list<string> */
function home_dashboard_cities(mysqli $mysqli): array
{
    $rows = [];
    $r = $mysqli->query('SELECT city_name FROM city ORDER BY city_name');
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $n = trim((string) ($row['city_name'] ?? ''));
            if ($n !== '') {
                $rows[] = $n;
            }
        }
    }
    return $rows;
}

function home_dashboard_count_pending_supplier_like(mysqli $mysqli, string $pattern): int
{
    $sql = 'SELECT COUNT(*) AS c FROM file_entry WHERE conform_status IS NULL AND supplier_name LIKE ?';
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return 0;
    }
    $stmt->bind_param('s', $pattern);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['c'] ?? 0);
}

/** For cancel-report A–Z strip (legacy: conform_status != 'Confirmed') */
function home_dashboard_count_nonconfirmed_supplier_like(mysqli $mysqli, string $pattern): int
{
    $sql = 'SELECT COUNT(*) AS c FROM file_entry WHERE supplier_name LIKE ? AND (conform_status IS NULL OR conform_status <> ?)';
    $confirmed = 'Confirmed';
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return 0;
    }
    $stmt->bind_param('ss', $pattern, $confirmed);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['c'] ?? 0);
}

/** @return list<array<string,mixed>> */
function home_dashboard_file_entries_pending_by_supplier_like(mysqli $mysqli, string $pattern): array
{
    $sql = 'SELECT file_id, file_no, supplier_name, agent_name, first_name, last_name, from_location, to_location, service, service_date, conform_status
            FROM file_entry
            WHERE conform_status IS NULL AND supplier_name LIKE ?
            ORDER BY service_date DESC';
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param('s', $pattern);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

/**
 * @return list<array{supplier_name:string,supplier_country:string,supplier_city:string,pending_count:int}>
 */
function home_dashboard_suppliers_pending_summary(mysqli $mysqli): array
{
    $out = [];
    $r = $mysqli->query('SELECT supplier_id, supplier_name, supplier_country, supplier_city FROM supplier ORDER BY supplier_name');
    if (!$r) {
        return [];
    }
    while ($row = $r->fetch_assoc()) {
        $name = trim((string) ($row['supplier_name'] ?? ''));
        if ($name === '') {
            continue;
        }
        $cnt = home_dashboard_count_pending_for_supplier_exact($mysqli, $name);
        if ($cnt < 1) {
            continue;
        }
        $out[] = [
            'supplier_name' => $name,
            'supplier_country' => (string) ($row['supplier_country'] ?? ''),
            'supplier_city' => (string) ($row['supplier_city'] ?? ''),
            'pending_count' => $cnt,
        ];
    }
    return $out;
}

function home_dashboard_count_pending_for_supplier_exact(mysqli $mysqli, string $supplierName): int
{
    $sql = 'SELECT COUNT(*) AS c FROM file_entry WHERE conform_status IS NULL AND supplier_name = ?';
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return 0;
    }
    $stmt->bind_param('s', $supplierName);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['c'] ?? 0);
}

/**
 * Booking search (legacy booking_search.php intent; fixed file_no bind).
 *
 * @return list<array<string,mixed>>
 */
function home_dashboard_search_pending_bookings(
    mysqli $mysqli,
    string $searchPax,
    string $searchFileNo,
    string $country,
    string $city
): array {
    $sql = 'SELECT file_id, file_no, supplier_name, agent_name, first_name, last_name, from_location, to_location, service, service_date
            FROM file_entry
            WHERE conform_status IS NULL AND (user_enter_by IS NOT NULL AND user_enter_by <> \'\')';
    $types = '';
    $params = [];

    if ($searchPax !== '') {
        $sql .= ' AND first_name = ?';
        $types .= 's';
        $params[] = $searchPax;
    }
    if ($searchFileNo !== '') {
        $sql .= ' AND file_no = ?';
        $types .= 's';
        $params[] = $searchFileNo;
    }
    if ($country !== '') {
        $sql .= ' AND from_country = ?';
        $types .= 's';
        $params[] = $country;
    }
    if ($city !== '') {
        $sql .= ' AND from_city = ?';
        $types .= 's';
        $params[] = $city;
    }
    $sql .= ' ORDER BY service_date DESC';

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return [];
    }
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

/** @return list<array<string,mixed>> */
function home_dashboard_suppliers_matching_name_pattern(mysqli $mysqli, string $likePattern): array
{
    $sql = 'SELECT supplier_id, supplier_name, supplier_country, supplier_city FROM supplier WHERE supplier_name LIKE ? ORDER BY supplier_name';
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param('s', $likePattern);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

/** @return list<array<string,mixed>> */
function home_dashboard_file_entries_pending_for_supplier(mysqli $mysqli, string $supplierName): array
{
    $sql = 'SELECT file_id, file_no, supplier_name, agent_name, first_name, last_name, from_location, to_location, service, service_date
            FROM file_entry
            WHERE conform_status IS NULL AND supplier_name = ?
            ORDER BY service_date DESC';
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param('s', $supplierName);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

/** @return list<array<string,mixed>> */
function home_dashboard_cancelled_bookings(mysqli $mysqli, int $limit = 50): array
{
    $sql = 'SELECT file_no, supplier_name, agent_name, first_name, last_name, from_location, to_location, service, service_date
            FROM file_entry
            WHERE conform_status = ?
            ORDER BY service_date DESC
            LIMIT ' . (int) $limit;
    $cancel = 'Cancel';
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param('s', $cancel);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

function home_dashboard_set_conform_status(mysqli $mysqli, int $fileId, string $status): bool
{
    if ($status !== 'Confirmed' && $status !== 'Cancel') {
        return false;
    }
    $stmt = $mysqli->prepare('UPDATE file_entry SET conform_status = ? WHERE file_id = ? LIMIT 1');
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('si', $status, $fileId);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

/** @return list<array<string,mixed>> */
function home_dashboard_search_agents(mysqli $mysqli, string $searchWord): array
{
    $fragment = $searchWord;
    if (strpos($searchWord, ' - ') !== false) {
        $parts = explode(' - ', $searchWord, 2);
        $fragment = trim($parts[1] ?? $parts[0] ?? '');
    }
    if ($fragment === '') {
        return [];
    }
    $like = '%' . $fragment . '%';
    $sql = 'SELECT agent_id, agent_code, agent_name, agent_address, agent_country, agent_city, agent_email, agent_contact_no, agent_logo_name
            FROM agent WHERE agent_name LIKE ? ORDER BY agent_name';
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param('s', $like);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

/** @return list<string> "CODE - Name" */
function home_dashboard_agent_autocomplete_labels(mysqli $mysqli, int $limit = 500): array
{
    $labels = [];
    $sql = 'SELECT agent_code, agent_name FROM agent ORDER BY agent_name LIMIT ' . (int) $limit;
    $r = $mysqli->query($sql);
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $code = trim((string) ($row['agent_code'] ?? ''));
            $name = trim((string) ($row['agent_name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $labels[] = ($code !== '' ? $code . ' - ' : '') . $name;
        }
    }
    return $labels;
}

/** @return list<string> */
function home_dashboard_autocomplete_first_names(mysqli $mysqli, int $limit = 400): array
{
    $out = [];
    $sql = 'SELECT DISTINCT first_name FROM file_entry WHERE first_name IS NOT NULL AND TRIM(first_name) <> \'\' ORDER BY first_name LIMIT ' . (int) $limit;
    $r = $mysqli->query($sql);
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $out[] = (string) ($row['first_name'] ?? '');
        }
    }
    return $out;
}

/** @return list<string> */
function home_dashboard_autocomplete_file_nos(mysqli $mysqli, int $limit = 400): array
{
    $out = [];
    $sql = 'SELECT DISTINCT file_no FROM file_entry WHERE file_no IS NOT NULL AND TRIM(file_no) <> \'\' ORDER BY file_no LIMIT ' . (int) $limit;
    $r = $mysqli->query($sql);
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $out[] = (string) ($row['file_no'] ?? '');
        }
    }
    return $out;
}

/** Build `letter` query value like legacy: Label-encodedPattern */
function home_dashboard_letter_query_value(string $label, string $pattern): string
{
    return $label . '-' . rawurlencode($pattern);
}

/** @return list<array{label:string,pattern:string}> */
function home_dashboard_az_letter_strip(): array
{
    $out = [['label' => 'All', 'pattern' => '%%']];
    foreach (range('A', 'Z') as $L) {
        $out[] = ['label' => $L, 'pattern' => $L . '%'];
    }
    return $out;
}

/**
 * Redirect URL after confirm/cancel (preserve letter / az filters, or supplier drill-down).
 *
 * @param array{letter?:string,az?:string,to?:string,supplier?:string} $return
 */
function home_dashboard_build_home_url(array $return = []): string
{
    $to = trim((string) ($return['to'] ?? 'home'));
    if ($to === 'supplier') {
        $supplier = trim((string) ($return['supplier'] ?? ''));
        if ($supplier !== '') {
            return 'index.php?' . http_build_query(['page' => 'home_supplier_bookings', 'supplier' => $supplier]);
        }
    }
    $q = ['page' => 'home'];
    $letter = trim((string) ($return['letter'] ?? ''));
    $az = trim((string) ($return['az'] ?? ''));
    if ($az !== '') {
        $q['az'] = $az;
    }
    if ($letter !== '') {
        $q['letter'] = $letter;
    }
    return 'index.php?' . http_build_query($q);
}

/** @return list<string> */
function home_dashboard_autocomplete_json(mysqli $mysqli, string $type, string $q, int $limit = 20): array
{
    $q = trim($q);
    if ($q === '' || $limit < 1) {
        return [];
    }
    $like = '%' . $q . '%';
    if ($type === 'agent') {
        $sql = 'SELECT agent_code, agent_name FROM agent WHERE agent_name LIKE ? OR agent_code LIKE ? ORDER BY agent_name LIMIT ' . (int) $limit;
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param('ss', $like, $like);
        $stmt->execute();
        $res = $stmt->get_result();
        $out = [];
        while ($row = $res->fetch_assoc()) {
            $code = trim((string) ($row['agent_code'] ?? ''));
            $name = trim((string) ($row['agent_name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $out[] = ($code !== '' ? $code . ' - ' : '') . $name;
        }
        $stmt->close();
        return $out;
    }
    if ($type === 'pax') {
        $sql = 'SELECT DISTINCT first_name FROM file_entry WHERE first_name LIKE ? ORDER BY first_name LIMIT ' . (int) $limit;
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param('s', $like);
        $stmt->execute();
        $res = $stmt->get_result();
        $out = [];
        while ($row = $res->fetch_assoc()) {
            $out[] = (string) ($row['first_name'] ?? '');
        }
        $stmt->close();
        return $out;
    }
    if ($type === 'file_no') {
        $sql = 'SELECT DISTINCT file_no FROM file_entry WHERE file_no LIKE ? ORDER BY file_no LIMIT ' . (int) $limit;
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param('s', $like);
        $stmt->execute();
        $res = $stmt->get_result();
        $out = [];
        while ($row = $res->fetch_assoc()) {
            $out[] = (string) ($row['file_no'] ?? '');
        }
        $stmt->close();
        return $out;
    }
    return [];
}
