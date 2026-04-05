<?php

declare(strict_types=1);

/**
 * Driver module: job lists and assignment (legacy parity, scoped by user_enter_by).
 */

require_once __DIR__ . '/file_module_service.php';

/** Legacy DB value for “driver/vehicle assigned” (typo preserved). */
const DRIVER_MODULE_ASSIGNED_FLAG = 'Asinged';

const DRIVER_MODULE_ROW_LIMIT = 500;

const DRIVER_MODULE_RECENT_LIMIT = 1000;

/** @return 'search'|'pending'|'completed'|'recent' */
function driver_module_normalize_sub(string $sub): string
{
    $sub = strtolower(trim($sub));
    $allowed = ['search', 'pending', 'completed', 'recent'];
    return in_array($sub, $allowed, true) ? $sub : 'search';
}

/** @return list<string> */
function driver_module_completed_statuses(mysqli $mysqli): array
{
    $rows = [];
    $r = $mysqli->query('SELECT job_complited FROM completed_job ORDER BY job_complited');
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $v = trim((string) ($row['job_complited'] ?? ''));
            if ($v !== '') {
                $rows[] = $v;
            }
        }
    }
    return $rows;
}

/** @return list<string> */
function driver_module_vehicle_numbers(mysqli $mysqli): array
{
    $rows = [];
    $r = $mysqli->query('SELECT vehicles_no FROM vehicles WHERE vehicles_no <> \'\' ORDER BY vehicles_no');
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $n = trim((string) ($row['vehicles_no'] ?? ''));
            if ($n !== '') {
                $rows[] = $n;
            }
        }
    }
    return $rows;
}

/**
 * @param array<string, string> $filters trimmed POST-style keys: search_driver, search_ref, search_file, search_agent, search_supplier, search_pax, status, select_date (d-m-Y)
 * @return list<array<string, mixed>>
 */
function driver_module_search_rows(mysqli $mysqli, array $filters, string $userEnterBy): array
{
    if ($userEnterBy === '') {
        return [];
    }

    $where = ['fe.user_enter_by = ?'];
    $params = [$userEnterBy];
    $types = 's';

    $addEq = static function (string $col, string $val) use (&$where, &$params, &$types): void {
        if ($val === '') {
            return;
        }
        $where[] = 'fe.' . $col . ' = ?';
        $params[] = $val;
        $types .= 's';
    };

    $addEq('driver_name', $filters['search_driver'] ?? '');
    $addEq('ref_no', $filters['search_ref'] ?? '');
    $addEq('file_no', $filters['search_file'] ?? '');
    $addEq('agent_name', $filters['search_agent'] ?? '');
    $addEq('supplier_name', $filters['search_supplier'] ?? '');
    $addEq('first_name', $filters['search_pax'] ?? '');

    $status = trim((string) ($filters['status'] ?? ''));
    if ($status !== '') {
        $where[] = 'fe.job_complited = ?';
        $params[] = $status;
        $types .= 's';
    }

    $dateDmy = trim((string) ($filters['select_date'] ?? ''));
    if ($dateDmy !== '') {
        $ymd = file_module_parse_service_date($dateDmy);
        if ($ymd !== null) {
            $where[] = 'fe.service_date = ?';
            $params[] = $ymd;
            $types .= 's';
        }
    }

    $sql = 'SELECT fe.file_id, fe.ref_no, fe.file_no, fe.agent_name, fe.supplier_name, fe.last_name, fe.first_name, '
        . 'fe.service_date, fe.vehicle_no, fe.driver_name, fe.job_complited '
        . 'FROM file_entry fe WHERE ' . implode(' AND ', $where)
        . ' ORDER BY fe.service_date ASC LIMIT ' . (int) DRIVER_MODULE_ROW_LIMIT;

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    $out = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $out[] = $row;
        }
    }
    $stmt->close();

    return $out;
}

/**
 * @param array<string, string> $filters search_supplier, select_date (d-m-Y)
 * @return list<array<string, mixed>>
 */
function driver_module_pending_rows(mysqli $mysqli, array $filters, string $userEnterBy): array
{
    if ($userEnterBy === '') {
        return [];
    }

    $where = [
        'fe.user_enter_by = ?',
        '(fe.job_compliments_assigned IS NULL OR fe.job_compliments_assigned <> ?)',
    ];
    $params = [$userEnterBy, DRIVER_MODULE_ASSIGNED_FLAG];
    $types = 'ss';

    $sup = trim((string) ($filters['search_supplier'] ?? ''));
    if ($sup !== '') {
        $where[] = 'fe.supplier_name = ?';
        $params[] = $sup;
        $types .= 's';
    }

    $dateDmy = trim((string) ($filters['select_date'] ?? ''));
    if ($dateDmy !== '') {
        $ymd = file_module_parse_service_date($dateDmy);
        if ($ymd !== null) {
            $where[] = 'fe.service_date = ?';
            $params[] = $ymd;
            $types .= 's';
        }
    }

    $sql = 'SELECT fe.file_id, fe.ref_no, fe.file_no, fe.service, fe.agent_name, fe.supplier_name, fe.last_name, fe.first_name, '
        . 'fe.service_date, fe.vehicle_no, fe.driver_name '
        . 'FROM file_entry fe WHERE ' . implode(' AND ', $where)
        . ' ORDER BY fe.service_date ASC LIMIT ' . (int) DRIVER_MODULE_ROW_LIMIT;

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    $out = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $out[] = $row;
        }
    }
    $stmt->close();

    return $out;
}

/**
 * Completed jobs: legacy completed_job.php used `WHERE job_complited != ''` with no user_enter_by filter.
 * file_entry.user_enter_by often stores reservation/staff codes (e.g. res5), not user_login.Username (Kris),
 * so scoping by session username hid all historical completed rows.
 *
 * @return list<array<string, mixed>>
 */
function driver_module_completed_rows(mysqli $mysqli): array
{
    $sql = 'SELECT fe.ref_no, fe.file_no, fe.last_name, fe.first_name, fe.from_city, fe.to_city, fe.service_date, '
        . 'fe.driver_name, fe.vehicle_no, fe.job_complited '
        . 'FROM file_entry fe WHERE TRIM(COALESCE(fe.job_complited, \'\')) <> \'\' '
        . 'ORDER BY fe.date DESC LIMIT ' . (int) DRIVER_MODULE_ROW_LIMIT;

    $res = $mysqli->query($sql);
    $out = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $out[] = $row;
        }
    }

    return $out;
}

/** @return list<array<string, mixed>> */
function driver_module_recent_rows(mysqli $mysqli, string $userEnterBy): array
{
    if ($userEnterBy === '') {
        return [];
    }

    $sql = 'SELECT fe.file_id, fe.ref_no, fe.file_no, fe.service, fe.agent_name, fe.supplier_name, fe.last_name, fe.first_name, '
        . 'fe.service_date, fe.vehicle_no, fe.driver_name '
        . 'FROM file_entry fe WHERE fe.user_enter_by = ? AND fe.job_compliments_assigned = ? '
        . 'ORDER BY fe.service_date DESC LIMIT ' . (int) DRIVER_MODULE_RECENT_LIMIT;

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return [];
    }
    $flag = DRIVER_MODULE_ASSIGNED_FLAG;
    $stmt->bind_param('ss', $userEnterBy, $flag);
    $stmt->execute();
    $res = $stmt->get_result();
    $out = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $out[] = $row;
        }
    }
    $stmt->close();

    return $out;
}

/** @return array<string, mixed>|null */
function driver_module_get_file_for_assign(mysqli $mysqli, int $fileId, string $userEnterBy): ?array
{
    if ($fileId <= 0 || $userEnterBy === '') {
        return null;
    }
    $stmt = $mysqli->prepare(
        'SELECT * FROM file_entry WHERE file_id = ? AND user_enter_by = ? LIMIT 1'
    );
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('is', $fileId, $userEnterBy);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    return $row ?: null;
}

/**
 * @return array{ok:bool, error?:string}
 */
function driver_module_assign_post(mysqli $mysqli, int $fileId, string $userEnterBy, string $vehicleNo, string $driverName): array
{
    $vehicleNo = trim($vehicleNo);
    $driverName = trim($driverName);
    if (strlen($vehicleNo) < 2) {
        return ['ok' => false, 'error' => 'Please select a vehicle number.'];
    }
    if (strlen($driverName) < 1) {
        return ['ok' => false, 'error' => 'Please select a driver name.'];
    }
    if ($fileId <= 0 || $userEnterBy === '') {
        return ['ok' => false, 'error' => 'Invalid request.'];
    }

    $mobile = file_module_driver_mobile($mysqli, $driverName);
    $flag = DRIVER_MODULE_ASSIGNED_FLAG;
    $newJob = 'Yes';

    $stmt = $mysqli->prepare(
        'UPDATE file_entry SET vehicle_no = ?, driver_name = ?, job_compliments_assigned = ?, new_job = ?, driver_mobile = ? '
        . 'WHERE file_id = ? AND user_enter_by = ? LIMIT 1'
    );
    if (!$stmt) {
        return ['ok' => false, 'error' => 'Could not save assignment.'];
    }
    $stmt->bind_param('sssssis', $vehicleNo, $driverName, $flag, $newJob, $mobile, $fileId, $userEnterBy);
    $ok = $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if (!$ok || $affected < 1) {
        return ['ok' => false, 'error' => 'No row updated (check file id or access).'];
    }

    return ['ok' => true];
}

/** @return array{pickup:string,drop:string} */
function driver_module_pickup_drop(array $row): array
{
    $pickupFrom = trim((string) ($row['pickup_from'] ?? ''));
    $dropOff = trim((string) ($row['drop_off'] ?? ''));
    $fromZone = trim((string) ($row['from_zone'] ?? ''));
    $fromLoc = trim((string) ($row['from_location'] ?? ''));
    $toZone = trim((string) ($row['to_zone'] ?? ''));
    $toLoc = trim((string) ($row['to_location'] ?? ''));

    $pickup = $pickupFrom !== '' ? $pickupFrom : ($fromZone !== '' ? $fromZone : $fromLoc);
    $drop = $dropOff !== '' ? $dropOff : ($toZone !== '' ? $toZone : $toLoc);

    return ['pickup' => $pickup, 'drop' => $drop];
}

function driver_module_format_hm(string $timeSql): string
{
    $timeSql = trim($timeSql);
    if ($timeSql === '' || $timeSql === '00:00:00') {
        return '';
    }
    $p = explode(':', $timeSql);
    if (count($p) >= 2) {
        return $p[0] . ':' . $p[1];
    }
    return $timeSql;
}

/** @return array<string, mixed>|null */
function driver_module_get_driver_by_name(mysqli $mysqli, string $driverName): ?array
{
    $driverName = trim($driverName);
    if ($driverName === '') {
        return null;
    }
    $stmt = $mysqli->prepare(
        'SELECT driver_id, driver_name, Username, device_id FROM driver WHERE driver_name = ? LIMIT 1'
    );
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('s', $driverName);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    if ($row) {
        return $row;
    }
    // Try trim match in DB
    $stmt = $mysqli->prepare(
        'SELECT driver_id, driver_name, Username, device_id FROM driver WHERE TRIM(driver_name) = ? LIMIT 1'
    );
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('s', $driverName);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    return $row ?: null;
}

/**
 * Server-side GET to legacy-style push endpoint (message + regId).
 *
 * @return array{ok:bool, error?:string}
 */
function driver_module_send_push_notification(string $baseUrl, string $regId, string $message): array
{
    $baseUrl = trim($baseUrl);
    $regId = trim($regId);
    if ($baseUrl === '' || $regId === '') {
        return ['ok' => false, 'error' => 'Push URL or device id is not configured.'];
    }
    if (!function_exists('curl_init')) {
        return ['ok' => false, 'error' => 'cURL is not available.'];
    }
    $q = http_build_query(['regId' => $regId, 'message' => $message], '', '&', PHP_QUERY_RFC3986);
    $url = $baseUrl . (str_contains($baseUrl, '?') ? '&' : '?') . $q;
    $ch = curl_init($url);
    if ($ch === false) {
        return ['ok' => false, 'error' => 'Could not start request.'];
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_USERAGENT, 'OSBTourDriverModule/1.0');
    $body = curl_exec($ch);
    $err = curl_error($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($body === false && $err !== '') {
        return ['ok' => false, 'error' => $err];
    }
    if ($code >= 400) {
        return ['ok' => false, 'error' => 'HTTP ' . $code];
    }

    return ['ok' => true];
}
