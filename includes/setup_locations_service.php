<?php
declare(strict_types=1);

function setup_locations_flash_set(string $type, string $message): void { $_SESSION['setup_locations_flash'] = ['type' => $type, 'message' => $message]; }
function setup_locations_flash_get(): ?array { if (!isset($_SESSION['setup_locations_flash'])) return null; $f = $_SESSION['setup_locations_flash']; unset($_SESSION['setup_locations_flash']); return is_array($f) ? $f : null; }
function setup_locations_csrf_token(): string { if (empty($_SESSION['setup_locations_csrf'])) $_SESSION['setup_locations_csrf'] = bin2hex(random_bytes(16)); return (string) $_SESSION['setup_locations_csrf']; }
function setup_locations_csrf_validate(string $token): bool { $s = (string) ($_SESSION['setup_locations_csrf'] ?? ''); return $s !== '' && hash_equals($s, $token); }

const SETUP_LOCATIONS_MAX_VARCHAR = 150;
const SETUP_LOCATIONS_MAX_PHONE = 50;
const SETUP_LOCATIONS_MAX_ADDRESS = 65535;

function setup_locations_clip(string $value, int $maxChars): string
{
    if ($maxChars <= 0) {
        return $value;
    }
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($value, 'UTF-8') <= $maxChars) {
            return $value;
        }
        return mb_substr($value, 0, $maxChars, 'UTF-8');
    }
    return strlen($value) <= $maxChars ? $value : substr($value, 0, $maxChars);
}

function setup_locations_utf8_len(string $value): int
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($value, 'UTF-8');
    }
    return strlen($value);
}

function setup_locations_ensure_utf8mb4(mysqli $mysqli): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;
    $sql = "SELECT TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'location' LIMIT 1";
    $res = $mysqli->query($sql);
    if (!$res) {
        return;
    }
    $row = $res->fetch_assoc();
    $res->close();
    $coll = strtolower((string) ($row['TABLE_COLLATION'] ?? ''));
    if ($coll !== '' && strpos($coll, 'utf8mb4') !== false) {
        return;
    }
    @$mysqli->query('ALTER TABLE location CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
}

/** @param array<string,mixed> $row */
function setup_locations_normalize_row(array $row): array
{
    if (function_exists('normalize_arabic_text') && isset($row['location_name_arb']) && is_string($row['location_name_arb'])) {
        $row['location_name_arb'] = normalize_arabic_text($row['location_name_arb']);
    }
    return $row;
}

function setup_locations_next_id(mysqli $mysqli): int
{
    $r = $mysqli->query('SELECT COALESCE(MAX(location_id),0)+1 AS n FROM location');
    if ($r && ($row = $r->fetch_assoc())) {
        return max(1, (int) ($row['n'] ?? 1));
    }
    return 1;
}

/** @return list<array{city_name:string,city_country_name:string}> */
function setup_locations_cities_all(mysqli $mysqli): array
{
    $rows = [];
    $result = $mysqli->query('SELECT city_name, city_country_name FROM city ORDER BY city_name');
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = [
                'city_name' => trim((string) ($row['city_name'] ?? '')),
                'city_country_name' => trim((string) ($row['city_country_name'] ?? '')),
            ];
        }
    }
    return $rows;
}

function setup_locations_country_by_city(mysqli $mysqli, string $city): string
{
    $stmt = $mysqli->prepare('SELECT city_country_name FROM city WHERE city_name = ? LIMIT 1');
    if (!$stmt) {
        return '';
    }
    $stmt->bind_param('s', $city);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return trim((string) ($row['city_country_name'] ?? ''));
}

/** @return list<array<string,string>> */
function setup_locations_list(mysqli $mysqli): array
{
    setup_locations_ensure_utf8mb4($mysqli);
    $result = $mysqli->query('SELECT location_id, location_name, location_name_arb, location_country, location_city, location_address, location_phone FROM location ORDER BY location_name');
    if (!$result) {
        return [];
    }
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = setup_locations_normalize_row($row);
    }
    return $rows;
}

function setup_locations_find(mysqli $mysqli, int $id): ?array
{
    setup_locations_ensure_utf8mb4($mysqli);
    $stmt = $mysqli->prepare('SELECT location_id, location_name, location_name_arb, location_country, location_city, location_address, location_phone FROM location WHERE location_id = ? LIMIT 1');
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) {
        return null;
    }
    return setup_locations_normalize_row($row);
}

function setup_locations_validate(array $data): array
{
    $errors = [];
    if (setup_locations_utf8_len(trim((string) ($data['location_name'] ?? ''))) < 1) {
        $errors[] = 'Location name english is required.';
    }
    if (setup_locations_utf8_len(trim((string) ($data['location_name_arb'] ?? ''))) < 1) {
        $errors[] = 'Location name arabic is required.';
    }
    if (setup_locations_utf8_len(trim((string) ($data['location_city'] ?? ''))) < 1) {
        $errors[] = 'City is required.';
    }
    return $errors;
}

function setup_locations_create(mysqli $mysqli, array $input): array
{
    setup_locations_ensure_utf8mb4($mysqli);

    $data = [
        'location_name' => setup_locations_clip(trim((string) ($input['location_name'] ?? '')), SETUP_LOCATIONS_MAX_VARCHAR),
        'location_name_arb' => setup_locations_clip(trim((string) ($input['location_name_arb'] ?? '')), SETUP_LOCATIONS_MAX_VARCHAR),
        'location_city' => setup_locations_clip(trim((string) ($input['location_city'] ?? '')), SETUP_LOCATIONS_MAX_VARCHAR),
        'location_address' => setup_locations_clip(trim((string) ($input['location_address'] ?? '')), SETUP_LOCATIONS_MAX_ADDRESS),
        'location_phone' => setup_locations_clip(trim((string) ($input['location_phone'] ?? '')), SETUP_LOCATIONS_MAX_PHONE),
    ];
    $errors = setup_locations_validate($data);
    if ($errors) {
        return ['ok' => false, 'errors' => $errors];
    }
    $data['location_country'] = setup_locations_country_by_city($mysqli, $data['location_city']);
    if ($data['location_country'] === '') {
        return ['ok' => false, 'errors' => ['Could not determine country for selected city.']];
    }
    $data['location_country'] = setup_locations_clip($data['location_country'], SETUP_LOCATIONS_MAX_VARCHAR);

    $id = setup_locations_next_id($mysqli);
    $stmt = $mysqli->prepare('INSERT INTO location (location_id, location_name, location_name_arb, location_country, location_city, location_address, location_phone) VALUES (?, ?, ?, ?, ?, ?, ?)');
    if (!$stmt) {
        return ['ok' => false, 'errors' => ['Failed to prepare insert. ' . $mysqli->error]];
    }

    $ln = $data['location_name'];
    $la = $data['location_name_arb'];
    $lc = $data['location_country'];
    $lci = $data['location_city'];
    $lad = $data['location_address'];
    $lp = $data['location_phone'];
    $stmt->bind_param('issssss', $id, $ln, $la, $lc, $lci, $lad, $lp);
    $ok = $stmt->execute();
    $execErr = $stmt->error;
    $stmt->close();
    if ($ok) {
        return ['ok' => true, 'id' => $id];
    }
    $msg = trim($execErr !== '' ? $execErr : $mysqli->error);
    return ['ok' => false, 'errors' => [$msg !== '' ? 'Save failed: ' . $msg : 'Failed to create location.']];
}

function setup_locations_update(mysqli $mysqli, int $id, array $input): array
{
    setup_locations_ensure_utf8mb4($mysqli);

    $data = [
        'location_name' => setup_locations_clip(trim((string) ($input['location_name'] ?? '')), SETUP_LOCATIONS_MAX_VARCHAR),
        'location_name_arb' => setup_locations_clip(trim((string) ($input['location_name_arb'] ?? '')), SETUP_LOCATIONS_MAX_VARCHAR),
        'location_city' => setup_locations_clip(trim((string) ($input['location_city'] ?? '')), SETUP_LOCATIONS_MAX_VARCHAR),
        'location_address' => setup_locations_clip(trim((string) ($input['location_address'] ?? '')), SETUP_LOCATIONS_MAX_ADDRESS),
        'location_phone' => setup_locations_clip(trim((string) ($input['location_phone'] ?? '')), SETUP_LOCATIONS_MAX_PHONE),
    ];
    $errors = setup_locations_validate($data);
    if ($errors) {
        return ['ok' => false, 'errors' => $errors];
    }
    $data['location_country'] = setup_locations_country_by_city($mysqli, $data['location_city']);
    if ($data['location_country'] === '') {
        return ['ok' => false, 'errors' => ['Could not determine country for selected city.']];
    }
    $data['location_country'] = setup_locations_clip($data['location_country'], SETUP_LOCATIONS_MAX_VARCHAR);

    $stmt = $mysqli->prepare('UPDATE location SET location_name = ?, location_name_arb = ?, location_country = ?, location_city = ?, location_address = ?, location_phone = ? WHERE location_id = ?');
    if (!$stmt) {
        return ['ok' => false, 'errors' => ['Failed to prepare update. ' . $mysqli->error]];
    }

    $ln = $data['location_name'];
    $la = $data['location_name_arb'];
    $lc = $data['location_country'];
    $lci = $data['location_city'];
    $lad = $data['location_address'];
    $lp = $data['location_phone'];
    $stmt->bind_param('ssssssi', $ln, $la, $lc, $lci, $lad, $lp, $id);
    $ok = $stmt->execute();
    $execErr = $stmt->error;
    $stmt->close();
    if ($ok) {
        return ['ok' => true];
    }
    $msg = trim($execErr !== '' ? $execErr : $mysqli->error);
    return ['ok' => false, 'errors' => [$msg !== '' ? 'Save failed: ' . $msg : 'Failed to update location.']];
}

function setup_locations_delete(mysqli $mysqli, int $id): bool
{
    setup_locations_ensure_utf8mb4($mysqli);
    $stmt = $mysqli->prepare('DELETE FROM location WHERE location_id = ?');
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('i', $id);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}
