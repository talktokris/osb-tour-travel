<?php

declare(strict_types=1);

require_once __DIR__ . '/file_module_service.php';

const SEARCH_MODULE_ROW_LIMIT = 500;
const SEARCH_MODULE_NESTED_FILE_LIMIT = 200;

/** @return list<string> */
function search_module_valid_modes(): array
{
    return [
        'agent', 'supplier', 'file_no', 'pax', 'vehicle_type', 'tour_type', 'driver', 'vehicle_no',
        'service_date', 'city', 'arrival', 'combined', 'departure', 'overland', 'tours',
    ];
}

function search_module_normalize_mode(string $m): string
{
    $m = strtolower(trim($m));

    return in_array($m, search_module_valid_modes(), true) ? $m : 'agent';
}

function search_module_username(): string
{
    return trim((string) ($_SESSION['user_name'] ?? ''));
}

/** @param array<string, mixed> $r */
function search_module_service_route(array $r): string
{
    $loFrom = trim((string) ($r['from_location'] ?? ''));
    $loTo = trim((string) ($r['to_location'] ?? ''));
    $zoFrom = trim((string) ($r['from_zone'] ?? ''));
    $zoTo = trim((string) ($r['to_zone'] ?? ''));
    $fromLoc = $loFrom;
    $toLoc = $zoTo !== '' ? $zoTo : $loTo;

    return $fromLoc . ' To ' . $toLoc;
}

/** @param array<string, mixed> $r */
function search_module_row_service_date_dmy(array $r): string
{
    $sd = trim((string) ($r['service_date'] ?? ''));
    if ($sd === '' || $sd === '0000-00-00') {
        return '';
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $sd)) {
        return file_module_format_date_ymd_to_dmy($sd);
    }

    return $sd;
}

/**
 * @param list<string|int|float> $params
 * @return list<array<string, mixed>>
 */
function search_module_stmt_fetch_all(mysqli $mysqli, string $sql, string $types, array $params): array
{
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return [];
    }
    if ($types !== '') {
        $bp = [$types];
        foreach (array_keys($params) as $k) {
            $bp[] = &$params[$k];
        }
        call_user_func_array([$stmt, 'bind_param'], $bp);
    }
    if (!$stmt->execute()) {
        $stmt->close();

        return [];
    }
    $res = $stmt->get_result();
    $rows = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
    }
    $stmt->close();

    return $rows;
}

/** @return list<string> */
function search_module_vehicle_type_names(mysqli $mysqli): array
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

    return array_values(array_unique($rows));
}

/** @return list<string> */
function search_module_tour_category_names(mysqli $mysqli): array
{
    $rows = [];
    $r = $mysqli->query('SELECT tour_name FROM tour_type ORDER BY tour_name');
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $n = trim((string) ($row['tour_name'] ?? ''));
            if ($n !== '') {
                $rows[] = $n;
            }
        }
    }
    if ($rows !== []) {
        return array_values(array_unique($rows));
    }
    $r2 = $mysqli->query("SELECT DISTINCT service_cat FROM file_entry WHERE TRIM(service_cat) <> '' ORDER BY service_cat");
    if ($r2) {
        while ($row = $r2->fetch_assoc()) {
            $n = trim((string) ($row['service_cat'] ?? ''));
            if ($n !== '') {
                $rows[] = $n;
            }
        }
    }

    return array_values(array_unique($rows));
}

/** @return list<string> */
function search_module_service_type_names(mysqli $mysqli): array
{
    $rows = [];
    $r = $mysqli->query('SELECT service_type_name FROM service_type ORDER BY service_type_name');
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $n = trim((string) ($row['service_type_name'] ?? ''));
            if ($n !== '') {
                $rows[] = $n;
            }
        }
    }

    return array_values(array_unique($rows));
}

/**
 * @param array<string, string> $post
 * @return array{variant:string, rows:list<array<string,mixed>>, groups:?list<array{file_count_no:string,header:array<string,mixed>,lines:list<array<string,mixed>>}>, error:?string}
 */
function search_module_run(mysqli $mysqli, string $mode, array $post): array
{
    $base = [
        'variant' => 'wide',
        'rows' => [],
        'groups' => null,
        'error' => null,
    ];
    $user = search_module_username();
    if ($user === '') {
        $base['error'] = 'You must be logged in to search.';

        return $base;
    }

    return match ($mode) {
        'agent' => search_module_run_agent($mysqli, $user, $post),
        'supplier' => search_module_run_supplier($mysqli, $user, $post),
        'file_no' => search_module_run_file_no($mysqli, $user, $post),
        'pax' => search_module_run_pax($mysqli, $user, $post),
        'vehicle_type' => search_module_run_vehicle_type($mysqli, $user, $post),
        'tour_type' => search_module_run_tour_type($mysqli, $user, $post),
        'driver' => search_module_run_driver($mysqli, $user, $post),
        'vehicle_no' => search_module_run_vehicle_no($mysqli, $user, $post),
        'service_date' => search_module_run_service_date($mysqli, $user, $post),
        'city' => search_module_run_city($mysqli, $user, $post),
        'arrival' => search_module_run_arrival($mysqli, $user, $post),
        'combined' => search_module_run_combined($mysqli, $user, $post),
        'departure', 'overland' => search_module_run_nested_agent($mysqli, $user, $post),
        'tours' => search_module_run_tours($mysqli, $user, $post),
        default => search_module_run_agent($mysqli, $user, $post),
    };
}

/**
 * @param array<string, string> $post
 * @return array{variant:string, rows:list<array<string,mixed>>, groups:null, error:?string}
 */
function search_module_run_agent(mysqli $mysqli, string $user, array $post): array
{
    $term = trim($post['search_word'] ?? '');
    if ($term === '') {
        return ['variant' => 'wide', 'rows' => [], 'groups' => null, 'error' => null];
    }
    $like = '%' . $term . '%';
    $sql = 'SELECT fe.* FROM file_entry fe WHERE fe.agent_name LIKE ? AND fe.user_enter_by = ? ORDER BY fe.service_date DESC LIMIT ' . (int) SEARCH_MODULE_ROW_LIMIT;
    $rows = search_module_stmt_fetch_all($mysqli, $sql, 'ss', [$like, $user]);

    return ['variant' => 'wide', 'rows' => $rows, 'groups' => null, 'error' => null];
}

/**
 * @param array<string, string> $post
 * @return array{variant:string, rows:list<array<string,mixed>>, groups:null, error:?string}
 */
function search_module_run_supplier(mysqli $mysqli, string $user, array $post): array
{
    $term = trim($post['search_word'] ?? '');
    if ($term === '') {
        return ['variant' => 'wide', 'rows' => [], 'groups' => null, 'error' => null];
    }
    $like = '%' . $term . '%';
    $sql = 'SELECT fe.* FROM file_entry fe WHERE fe.supplier_name LIKE ? AND fe.user_enter_by = ? ORDER BY fe.service_date DESC LIMIT ' . (int) SEARCH_MODULE_ROW_LIMIT;
    $rows = search_module_stmt_fetch_all($mysqli, $sql, 'ss', [$like, $user]);

    return ['variant' => 'wide', 'rows' => $rows, 'groups' => null, 'error' => null];
}

/**
 * @param array<string, string> $post
 * @return array{variant:string, rows:list<array<string,mixed>>, groups:null, error:?string}
 */
function search_module_run_file_no(mysqli $mysqli, string $user, array $post): array
{
    $term = trim($post['search_word'] ?? '');
    if ($term === '') {
        return ['variant' => 'wide', 'rows' => [], 'groups' => null, 'error' => null];
    }
    $like = '%' . $term . '%';
    $sql = 'SELECT fe.* FROM file_entry fe WHERE fe.file_no LIKE ? AND fe.user_enter_by = ? ORDER BY fe.service_date DESC LIMIT ' . (int) SEARCH_MODULE_ROW_LIMIT;
    $rows = search_module_stmt_fetch_all($mysqli, $sql, 'ss', [$like, $user]);

    return ['variant' => 'wide', 'rows' => $rows, 'groups' => null, 'error' => null];
}

/**
 * @param array<string, string> $post
 * @return array{variant:string, rows:list<array<string,mixed>>, groups:null, error:?string}
 */
function search_module_run_pax(mysqli $mysqli, string $user, array $post): array
{
    $term = trim($post['search_word'] ?? '');
    if ($term === '') {
        return ['variant' => 'wide', 'rows' => [], 'groups' => null, 'error' => null];
    }
    $like = '%' . $term . '%';
    $sql = 'SELECT fe.* FROM file_entry fe WHERE (fe.first_name LIKE ? OR fe.last_name LIKE ?) AND fe.user_enter_by = ? ORDER BY fe.service_date DESC LIMIT ' . (int) SEARCH_MODULE_ROW_LIMIT;
    $rows = search_module_stmt_fetch_all($mysqli, $sql, 'sss', [$like, $like, $user]);

    return ['variant' => 'wide', 'rows' => $rows, 'groups' => null, 'error' => null];
}

/**
 * @param array<string, string> $post
 * @return array{variant:string, rows:list<array<string,mixed>>, groups:null, error:?string}
 */
function search_module_run_vehicle_type(mysqli $mysqli, string $user, array $post): array
{
    $term = trim($post['search_word'] ?? '');
    $from = file_module_parse_service_date(trim($post['from_date'] ?? ''));
    $to = file_module_parse_service_date(trim($post['to_date'] ?? ''));
    if ($term === '' || $from === null || $to === null) {
        return [
            'variant' => 'vehicle_type_last',
            'rows' => [],
            'groups' => null,
            'error' => 'Vehicle type, from date, and to date (dd-mm-yyyy) are required.',
        ];
    }
    $sql = 'SELECT fe.* FROM file_entry fe WHERE fe.vehicle_type = ? AND fe.service_date BETWEEN ? AND ? AND fe.user_enter_by = ? ORDER BY fe.service_date DESC LIMIT ' . (int) SEARCH_MODULE_ROW_LIMIT;
    $rows = search_module_stmt_fetch_all($mysqli, $sql, 'ssss', [$term, $from, $to, $user]);

    return ['variant' => 'vehicle_type_last', 'rows' => $rows, 'groups' => null, 'error' => null];
}

/**
 * @param array<string, string> $post
 * @return array{variant:string, rows:list<array<string,mixed>>, groups:null, error:?string}
 */
function search_module_run_tour_type(mysqli $mysqli, string $user, array $post): array
{
    $term = trim($post['search_word'] ?? '');
    $from = file_module_parse_service_date(trim($post['from_date'] ?? ''));
    $to = file_module_parse_service_date(trim($post['to_date'] ?? ''));
    if ($term === '' || $from === null || $to === null) {
        return [
            'variant' => 'wide',
            'rows' => [],
            'groups' => null,
            'error' => 'Tour type, from date, and to date (dd-mm-yyyy) are required.',
        ];
    }
    $sql = 'SELECT fe.* FROM file_entry fe WHERE fe.service_cat = ? AND fe.service_date BETWEEN ? AND ? AND fe.user_enter_by = ? ORDER BY fe.service_date DESC LIMIT ' . (int) SEARCH_MODULE_ROW_LIMIT;
    $rows = search_module_stmt_fetch_all($mysqli, $sql, 'ssss', [$term, $from, $to, $user]);

    return ['variant' => 'wide', 'rows' => $rows, 'groups' => null, 'error' => null];
}

/**
 * @param array<string, string> $post
 * @return array{variant:string, rows:list<array<string,mixed>>, groups:null, error:?string}
 */
function search_module_run_driver(mysqli $mysqli, string $user, array $post): array
{
    $term = trim($post['search_word'] ?? '');
    $from = file_module_parse_service_date(trim($post['from_date'] ?? ''));
    $to = file_module_parse_service_date(trim($post['to_date'] ?? ''));
    if ($term === '' || $from === null || $to === null) {
        return [
            'variant' => 'wide',
            'rows' => [],
            'groups' => null,
            'error' => 'Driver name, from date, and to date (dd-mm-yyyy) are required.',
        ];
    }
    $like = '%' . $term . '%';
    $sql = 'SELECT fe.* FROM file_entry fe WHERE fe.driver_name LIKE ? AND fe.service_date BETWEEN ? AND ? AND fe.user_enter_by = ? ORDER BY fe.service_date DESC LIMIT ' . (int) SEARCH_MODULE_ROW_LIMIT;
    $rows = search_module_stmt_fetch_all($mysqli, $sql, 'ssss', [$like, $from, $to, $user]);

    return ['variant' => 'wide', 'rows' => $rows, 'groups' => null, 'error' => null];
}

/**
 * @param array<string, string> $post
 * @return array{variant:string, rows:list<array<string,mixed>>, groups:null, error:?string}
 */
function search_module_run_vehicle_no(mysqli $mysqli, string $user, array $post): array
{
    $term = trim($post['search_word'] ?? '');
    $from = file_module_parse_service_date(trim($post['from_date'] ?? ''));
    $to = file_module_parse_service_date(trim($post['to_date'] ?? ''));
    if ($term === '' || $from === null || $to === null) {
        return [
            'variant' => 'wide',
            'rows' => [],
            'groups' => null,
            'error' => 'Vehicle number, from date, and to date (dd-mm-yyyy) are required.',
        ];
    }
    $like = '%' . $term . '%';
    $sql = 'SELECT fe.* FROM file_entry fe WHERE fe.vehicle_no LIKE ? AND fe.service_date BETWEEN ? AND ? AND fe.user_enter_by = ? ORDER BY fe.service_date DESC LIMIT ' . (int) SEARCH_MODULE_ROW_LIMIT;
    $rows = search_module_stmt_fetch_all($mysqli, $sql, 'ssss', [$like, $from, $to, $user]);

    return ['variant' => 'wide', 'rows' => $rows, 'groups' => null, 'error' => null];
}

/**
 * @param array<string, string> $post
 * @return array{variant:string, rows:list<array<string,mixed>>, groups:null, error:?string}
 */
function search_module_run_service_date(mysqli $mysqli, string $user, array $post): array
{
    $from = file_module_parse_service_date(trim($post['from_date'] ?? ''));
    $to = file_module_parse_service_date(trim($post['to_date'] ?? ''));
    if ($from === null || $to === null) {
        return [
            'variant' => 'wide',
            'rows' => [],
            'groups' => null,
            'error' => 'From date and to date (dd-mm-yyyy) are required.',
        ];
    }
    $sql = 'SELECT fe.* FROM file_entry fe WHERE fe.service_date <> \'\' AND fe.service_date <> \'0000-00-00\' AND fe.service_date BETWEEN ? AND ? AND fe.user_enter_by = ? ORDER BY fe.service_date DESC LIMIT ' . (int) SEARCH_MODULE_ROW_LIMIT;
    $rows = search_module_stmt_fetch_all($mysqli, $sql, 'sss', [$from, $to, $user]);

    return ['variant' => 'wide', 'rows' => $rows, 'groups' => null, 'error' => null];
}

/**
 * @param array<string, string> $post
 * @return array{variant:string, rows:list<array<string,mixed>>, groups:null, error:?string}
 */
function search_module_run_city(mysqli $mysqli, string $user, array $post): array
{
    $term = trim($post['search_word'] ?? '');
    if ($term === '') {
        return ['variant' => 'wide', 'rows' => [], 'groups' => null, 'error' => null];
    }
    $like = '%' . $term . '%';
    $sql = 'SELECT fe.* FROM file_entry fe WHERE fe.to_city LIKE ? AND fe.user_enter_by = ? ORDER BY fe.service_date DESC LIMIT ' . (int) SEARCH_MODULE_ROW_LIMIT;
    $rows = search_module_stmt_fetch_all($mysqli, $sql, 'ss', [$like, $user]);

    return ['variant' => 'wide', 'rows' => $rows, 'groups' => null, 'error' => null];
}

/**
 * @param array<string, string> $post
 * @return array{variant:string, rows:list<array<string,mixed>>, groups:null, error:?string}
 */
function search_module_run_arrival(mysqli $mysqli, string $user, array $post): array
{
    $term = trim($post['search_word'] ?? '');
    $from = file_module_parse_service_date(trim($post['from_date'] ?? ''));
    $to = file_module_parse_service_date(trim($post['to_date'] ?? ''));
    if ($term === '' || $from === null || $to === null) {
        return [
            'variant' => 'wide',
            'rows' => [],
            'groups' => null,
            'error' => 'Service type, from date, and to date (dd-mm-yyyy) are required.',
        ];
    }
    $like = '%' . $term . '%';
    $sql = 'SELECT fe.* FROM file_entry fe WHERE fe.service_type LIKE ? AND fe.service_date BETWEEN ? AND ? AND fe.user_enter_by = ? ORDER BY fe.service_date DESC LIMIT ' . (int) SEARCH_MODULE_ROW_LIMIT;
    $rows = search_module_stmt_fetch_all($mysqli, $sql, 'ssss', [$like, $from, $to, $user]);

    return ['variant' => 'wide', 'rows' => $rows, 'groups' => null, 'error' => null];
}

/**
 * @param array<string, string> $post
 * @return array{variant:string, rows:list<array<string,mixed>>, groups:null, error:?string}
 */
function search_module_run_tours(mysqli $mysqli, string $user, array $post): array
{
    $term = trim($post['search_word'] ?? '');
    if ($term === '') {
        return ['variant' => 'tours', 'rows' => [], 'groups' => null, 'error' => null];
    }
    $sql = 'SELECT fe.* FROM file_entry fe WHERE fe.service_cat = ? AND fe.user_enter_by = ? ORDER BY fe.service_date DESC LIMIT ' . (int) SEARCH_MODULE_ROW_LIMIT;
    $rows = search_module_stmt_fetch_all($mysqli, $sql, 'ss', [$term, $user]);

    return ['variant' => 'tours', 'rows' => $rows, 'groups' => null, 'error' => null];
}

/**
 * @param array<string, string> $post
 * @return array{variant:string, rows:list<array<string,mixed>>, groups:null, error:?string}
 */
function search_module_run_combined(mysqli $mysqli, string $user, array $post): array
{
    $parts = ['fe.user_enter_by = ?'];
    $types = 's';
    $params = [$user];

    $agent = trim($post['search_agent'] ?? '');
    if ($agent !== '') {
        $parts[] = 'fe.agent_name = ?';
        $types .= 's';
        $params[] = $agent;
    }
    $supplier = trim($post['search_supplier'] ?? '');
    if ($supplier !== '') {
        $parts[] = 'fe.supplier_name = ?';
        $types .= 's';
        $params[] = $supplier;
    }
    $ref = trim($post['search_ref'] ?? '');
    if ($ref !== '') {
        $parts[] = 'fe.ref_no = ?';
        $types .= 's';
        $params[] = $ref;
    }
    $fileNo = trim($post['search_file_no'] ?? '');
    if ($fileNo !== '') {
        $parts[] = 'fe.file_no = ?';
        $types .= 's';
        $params[] = $fileNo;
    }
    $pax = trim($post['search_pax'] ?? '');
    if ($pax !== '') {
        $parts[] = 'fe.last_name = ?';
        $types .= 's';
        $params[] = $pax;
    }
    $vtype = trim($post['vehicle_search_word'] ?? '');
    if ($vtype !== '') {
        $parts[] = 'fe.vehicle_type = ?';
        $types .= 's';
        $params[] = $vtype;
    }
    $tour = trim($post['tour_search_word'] ?? '');
    if ($tour !== '') {
        $parts[] = 'fe.service_cat = ?';
        $types .= 's';
        $params[] = $tour;
    }
    $driver = trim($post['search_driver'] ?? '');
    if ($driver !== '') {
        $parts[] = 'fe.driver_name = ?';
        $types .= 's';
        $params[] = $driver;
    }
    $vehNo = trim($post['search_vehicles'] ?? '');
    if ($vehNo !== '') {
        $parts[] = 'fe.vehicle_no = ?';
        $types .= 's';
        $params[] = $vehNo;
    }
    $selDate = file_module_parse_service_date(trim($post['select_date'] ?? ''));
    if ($selDate !== null) {
        $parts[] = 'fe.service_date = ?';
        $types .= 's';
        $params[] = $selDate;
    }
    $city = trim($post['search_city'] ?? '');
    if ($city !== '') {
        $parts[] = 'fe.from_city = ?';
        $types .= 's';
        $params[] = $city;
    }
    $svcType = trim($post['search_word'] ?? '');
    if ($svcType !== '') {
        $parts[] = 'fe.service_type = ?';
        $types .= 's';
        $params[] = $svcType;
    }

    $from = file_module_parse_service_date(trim($post['from_date'] ?? ''));
    $to = file_module_parse_service_date(trim($post['to_date'] ?? ''));
    if ($from !== null && $to !== null) {
        $parts[] = 'fe.service_date BETWEEN ? AND ?';
        $types .= 'ss';
        $params[] = $from;
        $params[] = $to;
    }

    $where = implode(' AND ', $parts);
    $sql = "SELECT fe.* FROM file_entry fe WHERE {$where} ORDER BY fe.file_count_no DESC, fe.service_date DESC LIMIT " . (int) SEARCH_MODULE_ROW_LIMIT;
    $rows = search_module_stmt_fetch_all($mysqli, $sql, $types, $params);

    return ['variant' => 'combined', 'rows' => $rows, 'groups' => null, 'error' => null];
}

/**
 * @param array<string, string> $post
 * @return array{variant:string, rows:list<array<string,mixed>>, groups:list<array{file_count_no:string,header:array<string,mixed>,lines:list<array<string,mixed>>}>, error:?string}
 */
function search_module_run_nested_agent(mysqli $mysqli, string $user, array $post): array
{
    $agent = trim($post['search_word'] ?? '');
    if ($agent === '') {
        return ['variant' => 'nested', 'rows' => [], 'groups' => [], 'error' => null];
    }
    $sql = 'SELECT fe.file_count_no FROM file_entry fe WHERE fe.agent_name = ? AND fe.user_enter_by = ? GROUP BY fe.file_count_no ORDER BY MAX(fe.file_id) DESC LIMIT ' . (int) SEARCH_MODULE_NESTED_FILE_LIMIT;
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return ['variant' => 'nested', 'rows' => [], 'groups' => [], 'error' => 'Search failed.'];
    }
    $stmt->bind_param('ss', $agent, $user);
    $stmt->execute();
    $res = $stmt->get_result();
    $counts = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $fcn = trim((string) ($row['file_count_no'] ?? ''));
            if ($fcn !== '') {
                $counts[] = $fcn;
            }
        }
    }
    $stmt->close();

    $groups = [];
    foreach ($counts as $fcn) {
        $lines = search_module_stmt_fetch_all(
            $mysqli,
            'SELECT fe.* FROM file_entry fe WHERE fe.file_count_no = ? AND fe.user_enter_by = ? ORDER BY fe.file_id ASC',
            'ss',
            [$fcn, $user]
        );
        if ($lines === []) {
            continue;
        }
        $headerStmt = $mysqli->prepare('SELECT fe.* FROM file_entry fe WHERE fe.file_count_no = ? AND fe.user_enter_by = ? ORDER BY fe.file_id DESC LIMIT 1');
        $header = $lines[0];
        if ($headerStmt) {
            $headerStmt->bind_param('ss', $fcn, $user);
            if ($headerStmt->execute()) {
                $hr = $headerStmt->get_result();
                if ($hr && ($hrow = $hr->fetch_assoc())) {
                    $header = $hrow;
                }
            }
            $headerStmt->close();
        }
        $groups[] = [
            'file_count_no' => $fcn,
            'header' => $header,
            'lines' => $lines,
        ];
    }

    return ['variant' => 'nested', 'rows' => [], 'groups' => $groups, 'error' => null];
}

function search_module_safe_redirect(string $candidate, string $fallback): string
{
    $candidate = trim($candidate);
    if ($candidate === '') {
        return $fallback;
    }
    if (preg_match('#^[a-z]+:#i', $candidate)) {
        return $fallback;
    }
    if (str_starts_with($candidate, '//')) {
        return $fallback;
    }
    if (str_starts_with($candidate, 'index.php')) {
        return $candidate;
    }
    if (str_starts_with($candidate, '?')) {
        return 'index.php' . $candidate;
    }

    return $fallback;
}

function search_module_delete_entry(mysqli $mysqli, string $user, string $fileId): bool
{
    if ($user === '' || $fileId === '') {
        return false;
    }
    $stmt = $mysqli->prepare('DELETE FROM file_entry WHERE file_id = ? AND user_enter_by = ? LIMIT 1');
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('ss', $fileId, $user);
    $ok = $stmt->execute() && $stmt->affected_rows > 0;
    $stmt->close();

    return $ok;
}
