<?php

declare(strict_types=1);

/**
 * File / Assignment: legacy workflow with mysqli + session state (replaces cookies).
 */

function file_module_csrf_token(): string
{
    if (empty($_SESSION['file_module_csrf'])) {
        $_SESSION['file_module_csrf'] = bin2hex(random_bytes(16));
    }
    return (string) $_SESSION['file_module_csrf'];
}

function file_module_csrf_validate(string $token): bool
{
    $t = (string) ($_SESSION['file_module_csrf'] ?? '');
    return $t !== '' && hash_equals($t, $token);
}

function file_module_flash_set(string $type, string $message): void
{
    $_SESSION['file_module_flash'] = ['type' => $type, 'message' => $message];
}

/** @return array{type:string,message:string}|null */
function file_module_flash_get(): ?array
{
    if (!isset($_SESSION['file_module_flash'])) {
        return null;
    }
    $f = $_SESSION['file_module_flash'];
    unset($_SESSION['file_module_flash']);
    return is_array($f) ? $f : null;
}

function file_module_agent_name(): string
{
    $v = trim((string) ($_COOKIE['agent_cookie'] ?? ''));
    return $v;
}

function file_module_has_agent(): bool
{
    return file_module_agent_name() !== '';
}

/** @return never */
function file_module_render_agent_required(): void
{
    require __DIR__ . '/../pages/file/agent_required.php';
    exit;
}

function file_module_session_uid(): int
{
    return (int) ($_SESSION['user_id'] ?? 0);
}

/** @return array<string, mixed> */
function file_module_state(): array
{
    $uid = file_module_session_uid();
    if ($uid <= 0) {
        return [];
    }
    if (empty($_SESSION['file_mod']) || (int) ($_SESSION['file_mod']['uid'] ?? 0) !== $uid) {
        $_SESSION['file_mod'] = [
            'uid' => $uid,
            'criteria' => file_module_default_criteria(),
            'file_count_no' => null,
            'file_no' => '',
            'guest' => [
                'title' => 'Mr',
                'last_name' => '',
                'first_name' => '',
                'pax_mobile' => '',
                'ref_no' => '',
            ],
        ];
    }
    return $_SESSION['file_mod'];
}

/** @return array<string, string> */
function file_module_default_criteria(): array
{
    return [
        'from_country' => '',
        'from_city' => '',
        'from_location' => '',
        'from_zone' => '',
        'to_country' => '',
        'to_city' => '',
        'to_location' => '',
        'to_zone' => '',
        'service_name' => '',
        'vehicle_type' => '',
        'no_of_vachile' => '1',
        'service_cat' => 'Private',
        'service_date' => '',
        'adults' => '2',
        'children' => '0',
        'no_of_pax' => '2',
    ];
}

/** @param array<string, string> $c */
function file_module_save_criteria(array $c): void
{
    $st = &$_SESSION['file_mod'];
    $def = file_module_default_criteria();
    foreach ($def as $k => $_) {
        if (array_key_exists($k, $c)) {
            $st['criteria'][$k] = trim((string) $c[$k]);
        }
    }
}

function file_module_set_file_count_no(?string $no): void
{
    $_SESSION['file_mod']['file_count_no'] = $no === null || $no === '' ? null : $no;
}

function file_module_set_file_no(string $no): void
{
    $_SESSION['file_mod']['file_no'] = trim($no);
}

/** @param array<string, string> $g */
function file_module_save_guest(array $g): void
{
    $st = &$_SESSION['file_mod']['guest'];
    foreach (['title', 'last_name', 'first_name', 'pax_mobile', 'ref_no'] as $k) {
        if (isset($g[$k])) {
            $st[$k] = trim((string) $g[$k]);
        }
    }
}

/** @return list<string> */
function file_module_countries(mysqli $mysqli): array
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
    return array_values(array_unique($rows));
}

/** @return list<string> */
function file_module_cities_for_country(mysqli $mysqli, string $country): array
{
    if ($country === '') {
        return [];
    }
    $stmt = $mysqli->prepare('SELECT city_name FROM city WHERE city_country_name = ? ORDER BY city_name');
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param('s', $country);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $n = trim((string) ($row['city_name'] ?? ''));
        if ($n !== '') {
            $rows[] = $n;
        }
    }
    $stmt->close();
    return $rows;
}

/** @return list<string> */
function file_module_locations_for_city(mysqli $mysqli, string $city): array
{
    if ($city === '') {
        return [];
    }
    $stmt = $mysqli->prepare('SELECT location_name FROM location WHERE location_city = ? ORDER BY location_name');
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param('s', $city);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $n = trim((string) ($row['location_name'] ?? ''));
        if ($n !== '') {
            $rows[] = $n;
        }
    }
    $stmt->close();
    return $rows;
}

/**
 * Zones for a location (legacy: zone.location_name = selected location).
 * Tries exact string, trim, and collapsed whitespace variants for DB/name drift.
 *
 * @return list<string>
 */
function file_module_zones_for_location(mysqli $mysqli, string $location): array
{
    if ($location === '') {
        return [];
    }
    $variants = [];
    $variants[] = $location;
    $t = trim($location);
    if ($t !== '' && $t !== $location) {
        $variants[] = $t;
    }
    $collapsed = $t === '' ? '' : (string) preg_replace('/\s+/u', ' ', $t);
    if ($collapsed !== '' && !in_array($collapsed, $variants, true)) {
        $variants[] = $collapsed;
    }
    $seen = [];
    foreach ($variants as $v) {
        if ($v === '' || isset($seen[$v])) {
            continue;
        }
        $seen[$v] = true;
        $rows = file_module_zones_for_location_exact($mysqli, $v);
        if ($rows !== []) {
            return $rows;
        }
    }
    return [];
}

/** @return list<string> */
function file_module_zones_for_location_exact(mysqli $mysqli, string $location): array
{
    $stmt = $mysqli->prepare('SELECT zone_name FROM zone WHERE location_name = ? ORDER BY zone_name');
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param('s', $location);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $n = trim((string) ($row['zone_name'] ?? ''));
        if ($n !== '') {
            $rows[] = $n;
        }
    }
    $stmt->close();
    return $rows;
}

/** Service names for from_locaion + to_locaion (legacy typo columns). @return list<string> */
function file_module_service_names_between(mysqli $mysqli, string $fromLoc, string $toLoc): array
{
    if ($fromLoc === '' || $toLoc === '') {
        return [];
    }
    $stmt = $mysqli->prepare(
        'SELECT DISTINCT service_name_english FROM service WHERE from_locaion = ? AND to_locaion = ? ORDER BY service_name_english'
    );
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param('ss', $fromLoc, $toLoc);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $n = trim((string) ($row['service_name_english'] ?? ''));
        if ($n !== '') {
            $rows[] = $n;
        }
    }
    $stmt->close();
    return $rows;
}

/** @return list<string> */
function file_module_vehicle_types(mysqli $mysqli): array
{
    $rows = [];
    $r = $mysqli->query('SELECT vehicle_type_name FROM vehicle_type ORDER BY vehicle_type_name');
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $n = trim((string) ($row['vehicle_type_name'] ?? ''));
            if ($n !== '') {
                $rows[] = $n;
            }
        }
    }
    return $rows;
}

/**
 * Legacy filters: service_categories, from_locaion, to_locaion, optional vehicle + service name.
 *
 * @param array<string, string> $criteria
 * @return list<array<string, mixed>>
 */
function file_module_search_services(mysqli $mysqli, array $criteria): array
{
    $from = $criteria['from_location'] ?? '';
    $to = $criteria['to_location'] ?? '';
    $cat = $criteria['service_cat'] ?? 'Private';
    $veh = trim((string) ($criteria['vehicle_type'] ?? ''));
    $svc = trim((string) ($criteria['service_name'] ?? ''));

    if ($from === '' || $to === '') {
        return [];
    }

    $sql = 'SELECT * FROM service WHERE service_categories = ? AND from_locaion = ? AND to_locaion = ?';
    $types = 'sss';
    $params = [$cat, $from, $to];

    if ($veh !== '') {
        $sql .= ' AND vehicle_type = ?';
        $types .= 's';
        $params[] = $veh;
    }
    if ($svc !== '') {
        $sql .= ' AND service_name_english = ?';
        $types .= 's';
        $params[] = $svc;
    }
    $sql .= ' ORDER BY service_id ASC';

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

/** @return array{selling:string,buying:string} */
function file_module_compute_prices(array $serviceRow, int $adults, int $children): array
{
    $cat = (string) ($serviceRow['service_categories'] ?? '');
    $sell = (string) ($serviceRow['selling_price'] ?? '0');
    $buy = (string) ($serviceRow['buying_price'] ?? '0');
    $sellF = (float) $sell;
    $buyF = (float) $buy;

    if ($cat === 'SIC') {
        $cSell = (float) ($serviceRow['sic_children_price'] ?? 0);
        $cBuy = (float) ($serviceRow['sic_adult_price'] ?? 0);
        $totalSell = $adults * $sellF + $children * $cSell;
        $totalBuy = $adults * $buyF + $children * $cBuy;
        return ['selling' => (string) $totalSell, 'buying' => (string) $totalBuy];
    }
    return ['selling' => $sell, 'buying' => $buy];
}

function file_module_next_file_count_no(mysqli $mysqli): string
{
    $r = $mysqli->query('SELECT file_count_no FROM file_entry');
    $max = 0;
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $raw = (string) ($row['file_count_no'] ?? '');
            $n = (int) preg_replace('/\D/', '', $raw);
            if ($n > $max) {
                $max = $n;
            }
        }
    }
    return (string) ($max + 1);
}

function file_module_next_file_id(mysqli $mysqli): int
{
    $r = $mysqli->query('SELECT COALESCE(MAX(file_id), 0) + 1 AS n FROM file_entry');
    if ($r && ($row = $r->fetch_assoc())) {
        return max(1, (int) ($row['n'] ?? 1));
    }
    return 1;
}

/** @return array<string, mixed>|null */
function file_module_service_by_id(mysqli $mysqli, int $serviceId): ?array
{
    $stmt = $mysqli->prepare('SELECT * FROM service WHERE service_id = ? LIMIT 1');
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('i', $serviceId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function file_module_user_can_access_file_count(mysqli $mysqli, string $fileCountNo, string $username): bool
{
    if ($fileCountNo === '' || $username === '') {
        return false;
    }
    $stmt = $mysqli->prepare('SELECT 1 FROM file_entry WHERE file_count_no = ? AND user_enter_by = ? LIMIT 1');
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('ss', $fileCountNo, $username);
    $stmt->execute();
    $ok = $stmt->get_result()->fetch_row() !== null;
    $stmt->close();
    return $ok;
}

/** @return list<array<string, mixed>> */
function file_module_entries_for_count(mysqli $mysqli, string $fileCountNo): array
{
    $stmt = $mysqli->prepare('SELECT * FROM file_entry WHERE file_count_no = ? ORDER BY file_id ASC');
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param('s', $fileCountNo);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

function file_module_set_all_on_request(mysqli $mysqli, string $fileCountNo): bool
{
    $stmt = $mysqli->prepare("UPDATE file_entry SET book_status = 'On Request' WHERE file_count_no = ?");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('s', $fileCountNo);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

/** @return list<string> */
function file_module_supplier_names(mysqli $mysqli): array
{
    $rows = [];
    $r = $mysqli->query('SELECT DISTINCT supplier_name FROM supplier WHERE supplier_name <> \'\' ORDER BY supplier_name');
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $n = trim((string) ($row['supplier_name'] ?? ''));
            if ($n !== '') {
                $rows[] = $n;
            }
        }
    }
    return $rows;
}

/** @return list<array{driver_name:string,driver_contact_no:string}> */
function file_module_drivers(mysqli $mysqli): array
{
    $rows = [];
    $r = $mysqli->query('SELECT driver_name, driver_contact_no FROM driver ORDER BY driver_name');
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $rows[] = [
                'driver_name' => trim((string) ($row['driver_name'] ?? '')),
                'driver_contact_no' => trim((string) ($row['driver_contact_no'] ?? '')),
            ];
        }
    }
    return $rows;
}

function file_module_driver_mobile(mysqli $mysqli, string $driverName): string
{
    if ($driverName === '') {
        return '';
    }
    $stmt = $mysqli->prepare('SELECT driver_contact_no FROM driver WHERE driver_name = ? LIMIT 1');
    if (!$stmt) {
        return '';
    }
    $stmt->bind_param('s', $driverName);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    return trim((string) ($row['driver_contact_no'] ?? ''));
}

/** dd-mm-yyyy -> Y-m-d */
function file_module_parse_service_date(string $dmy): ?string
{
    $dmy = trim($dmy);
    if (!preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $dmy, $m)) {
        return null;
    }
    $d = (int) $m[1];
    $mo = (int) $m[2];
    $y = (int) $m[3];
    if (!checkdate($mo, $d, $y)) {
        return null;
    }
    return sprintf('%04d-%02d-%02d', $y, $mo, $d);
}

function file_module_format_date_ymd_to_dmy(string $ymd): string
{
    $p = explode('-', $ymd);
    if (count($p) !== 3) {
        return $ymd;
    }
    return $p[2] . '-' . $p[1] . '-' . $p[0];
}

/** HH:MM from hour/min int strings */
function file_module_time_hm(string $hr, string $min): string
{
    $h = max(0, min(23, (int) $hr));
    $m = max(0, min(59, (int) $min));
    return sprintf('%02d:%02d:00', $h, $m);
}

/**
 * Insert one file_entry row. Uses dynamic placeholders — one 's' per column (PHP 8.1+ bind_param).
 *
 * @param array<string, string|null> $in
 */
function file_module_insert_file_entry(mysqli $mysqli, array $in): bool
{
    $id = file_module_next_file_id($mysqli);
    $row = [
        'file_id' => (string) $id,
        'agent_name' => (string) ($in['agent_name'] ?? ''),
        'from_location' => (string) ($in['from_location'] ?? ''),
        'from_country' => (string) ($in['from_country'] ?? ''),
        'from_city' => (string) ($in['from_city'] ?? ''),
        'from_zone' => (string) ($in['from_zone'] ?? ''),
        'to_location' => (string) ($in['to_location'] ?? ''),
        'to_country' => (string) ($in['to_country'] ?? ''),
        'to_city' => (string) ($in['to_city'] ?? ''),
        'to_zone' => (string) ($in['to_zone'] ?? ''),
        'service' => (string) ($in['service'] ?? ''),
        'service_id' => (string) ($in['service_id'] ?? ''),
        'service_type' => (string) ($in['service_type'] ?? ''),
        'service_cat' => (string) ($in['service_cat'] ?? ''),
        'vehicle_type' => (string) ($in['vehicle_type'] ?? ''),
        'service_date' => (string) ($in['service_date'] ?? ''),
        'adults' => (string) ($in['adults'] ?? ''),
        'children' => (string) ($in['children'] ?? ''),
        'no_of_pax' => (string) ($in['no_of_pax'] ?? ''),
        'title' => (string) ($in['title'] ?? ''),
        'last_name' => (string) ($in['last_name'] ?? ''),
        'first_name' => (string) ($in['first_name'] ?? ''),
        'pax_mobile' => (string) ($in['pax_mobile'] ?? ''),
        'ref_no' => (string) ($in['ref_no'] ?? ''),
        'flight_time' => (string) ($in['flight_time'] ?? '00:00:00'),
        'flight_no' => (string) ($in['flight_no'] ?? ''),
        'pickup_time' => (string) ($in['pickup_time'] ?? '00:00:00'),
        'pickup_from' => (string) ($in['pickup_from'] ?? ''),
        'drop_off' => (string) ($in['drop_off'] ?? ''),
        'supplier_name' => (string) ($in['supplier_name'] ?? ''),
        'driver_name' => (string) ($in['driver_name'] ?? ''),
        'driver_mobile' => (string) ($in['driver_mobile'] ?? ''),
        'remarks' => (string) ($in['remarks'] ?? ''),
        'book_status' => (string) ($in['book_status'] ?? 'Pending'),
        'file_no' => (string) ($in['file_no'] ?? ''),
        'invoice_no' => (string) ($in['invoice_no'] ?? ''),
        'selling_price' => (string) ($in['selling_price'] ?? ''),
        'buying_price' => (string) ($in['buying_price'] ?? ''),
        'file_count_no' => (string) ($in['file_count_no'] ?? ''),
        'user_enter_by' => (string) ($in['user_enter_by'] ?? ''),
        'date' => (string) ($in['date'] ?? date('Y-m-d')),
        'ip' => (string) ($in['ip'] ?? ''),
        'job_complited_date' => '0000-00-00',
    ];

    $cols = array_keys($row);
    $quoted = '`' . implode('`,`', $cols) . '`';
    $ph = implode(',', array_fill(0, count($cols), '?'));
    $sql = "INSERT INTO file_entry ({$quoted}) VALUES ({$ph})";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return false;
    }
    $types = str_repeat('s', count($cols));
    $stmt->bind_param($types, ...array_values($row));
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}
