<?php
declare(strict_types=1);

function setup_zones_flash_set(string $type, string $message): void { $_SESSION['setup_zones_flash'] = ['type' => $type, 'message' => $message]; }
function setup_zones_flash_get(): ?array { if (!isset($_SESSION['setup_zones_flash'])) return null; $f = $_SESSION['setup_zones_flash']; unset($_SESSION['setup_zones_flash']); return is_array($f) ? $f : null; }
function setup_zones_csrf_token(): string { if (empty($_SESSION['setup_zones_csrf'])) $_SESSION['setup_zones_csrf'] = bin2hex(random_bytes(16)); return (string) $_SESSION['setup_zones_csrf']; }
function setup_zones_csrf_validate(string $token): bool { $s = (string) ($_SESSION['setup_zones_csrf'] ?? ''); return $s !== '' && hash_equals($s, $token); }

const SETUP_ZONES_MAX_LOCATION = 150;
const SETUP_ZONES_MAX_ZONE = 150;
const SETUP_ZONES_MAX_ARABIC = 200;
const SETUP_ZONES_MAX_LOCATION_ID = 150;

function setup_zones_clip(string $value, int $maxChars): string
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

function setup_zones_utf8_len(string $value): int
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($value, 'UTF-8');
    }
    return strlen($value);
}

function setup_zones_ensure_utf8mb4(mysqli $mysqli): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;
    $sql = "SELECT TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'zone' LIMIT 1";
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
    @$mysqli->query('ALTER TABLE zone CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
}

/** @param array<string,mixed> $row */
function setup_zones_normalize_row(array $row): array
{
    if (function_exists('normalize_arabic_text') && isset($row['zone_name_arabic']) && is_string($row['zone_name_arabic'])) {
        $row['zone_name_arabic'] = normalize_arabic_text($row['zone_name_arabic']);
    }
    return $row;
}

function setup_zones_next_id(mysqli $mysqli): int
{
    $r = $mysqli->query('SELECT COALESCE(MAX(zone_id),0)+1 AS n FROM zone');
    if ($r && ($row = $r->fetch_assoc())) {
        return max(1, (int) ($row['n'] ?? 1));
    }
    return 1;
}

function setup_zones_locations(mysqli $mysqli): array
{
    $r = $mysqli->query('SELECT location_name FROM location ORDER BY location_name');
    $rows = [];
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $n = trim((string) ($row['location_name'] ?? ''));
            if ($n !== '') {
                $rows[] = $n;
            }
        }
    }
    return $rows;
}

function setup_zones_list(mysqli $mysqli, ?string $location = null): array
{
    setup_zones_ensure_utf8mb4($mysqli);
    $sql = 'SELECT zone_id, location_name, zone_name, zone_name_arabic FROM zone';
    $types = '';
    $params = [];
    if ($location !== null && $location !== '') {
        $sql .= ' WHERE location_name = ?';
        $types = 's';
        $params[] = $location;
    }
    $sql .= ' ORDER BY zone_name';
    $s = $mysqli->prepare($sql);
    if (!$s) {
        return [];
    }
    if ($types !== '') {
        $s->bind_param($types, ...$params);
    }
    $s->execute();
    $r = $s->get_result();
    $rows = [];
    while ($row = $r->fetch_assoc()) {
        $rows[] = setup_zones_normalize_row($row);
    }
    $s->close();
    return $rows;
}

function setup_zones_find(mysqli $mysqli, int $id): ?array
{
    setup_zones_ensure_utf8mb4($mysqli);
    $s = $mysqli->prepare('SELECT zone_id, location_name, zone_name, zone_name_arabic FROM zone WHERE zone_id = ? LIMIT 1');
    if (!$s) {
        return null;
    }
    $s->bind_param('i', $id);
    $s->execute();
    $row = $s->get_result()->fetch_assoc();
    $s->close();
    if (!$row) {
        return null;
    }
    return setup_zones_normalize_row($row);
}

function setup_zones_validate(array $d): array
{
    $e = [];
    if (setup_zones_utf8_len(trim((string) ($d['location_name'] ?? ''))) < 2) {
        $e[] = 'Location is required.';
    }
    if (setup_zones_utf8_len(trim((string) ($d['zone_name'] ?? ''))) < 2) {
        $e[] = 'Zone name is required.';
    }
    if (setup_zones_utf8_len(trim((string) ($d['zone_name_arabic'] ?? ''))) < 2) {
        $e[] = 'Zone Arabic name is required.';
    }
    return $e;
}

function setup_zones_create(mysqli $mysqli, array $input): array
{
    setup_zones_ensure_utf8mb4($mysqli);

    $d = [
        'location_name' => setup_zones_clip(trim((string) ($input['location_name'] ?? '')), SETUP_ZONES_MAX_LOCATION),
        'zone_name' => setup_zones_clip(trim((string) ($input['zone_name'] ?? '')), SETUP_ZONES_MAX_ZONE),
        'zone_name_arabic' => setup_zones_clip(trim((string) ($input['zone_name_arabic'] ?? '')), SETUP_ZONES_MAX_ARABIC),
    ];
    $e = setup_zones_validate($d);
    if ($e) {
        return ['ok' => false, 'errors' => $e];
    }

    $id = setup_zones_next_id($mysqli);
    $locId = setup_zones_clip('0', SETUP_ZONES_MAX_LOCATION_ID);
    $s = $mysqli->prepare('INSERT INTO zone (zone_id, location_id, location_name, zone_name_arabic, zone_name) VALUES (?, ?, ?, ?, ?)');
    if (!$s) {
        return ['ok' => false, 'errors' => ['Failed to prepare insert. ' . $mysqli->error]];
    }

    $ln = $d['location_name'];
    $za = $d['zone_name_arabic'];
    $zn = $d['zone_name'];
    $s->bind_param('issss', $id, $locId, $ln, $za, $zn);
    $ok = $s->execute();
    $execErr = $s->error;
    $s->close();
    if ($ok) {
        return ['ok' => true, 'id' => $id];
    }
    $msg = trim($execErr !== '' ? $execErr : $mysqli->error);
    return ['ok' => false, 'errors' => [$msg !== '' ? 'Save failed: ' . $msg : 'Failed to create zone.']];
}

function setup_zones_update(mysqli $mysqli, int $id, array $input): array
{
    setup_zones_ensure_utf8mb4($mysqli);

    $d = [
        'location_name' => setup_zones_clip(trim((string) ($input['location_name'] ?? '')), SETUP_ZONES_MAX_LOCATION),
        'zone_name' => setup_zones_clip(trim((string) ($input['zone_name'] ?? '')), SETUP_ZONES_MAX_ZONE),
        'zone_name_arabic' => setup_zones_clip(trim((string) ($input['zone_name_arabic'] ?? '')), SETUP_ZONES_MAX_ARABIC),
    ];
    $e = setup_zones_validate($d);
    if ($e) {
        return ['ok' => false, 'errors' => $e];
    }

    $s = $mysqli->prepare('UPDATE zone SET location_name = ?, zone_name = ?, zone_name_arabic = ? WHERE zone_id = ?');
    if (!$s) {
        return ['ok' => false, 'errors' => ['Failed to prepare update. ' . $mysqli->error]];
    }

    $ln = $d['location_name'];
    $zn = $d['zone_name'];
    $za = $d['zone_name_arabic'];
    $s->bind_param('sssi', $ln, $zn, $za, $id);
    $ok = $s->execute();
    $execErr = $s->error;
    $s->close();
    if ($ok) {
        return ['ok' => true];
    }
    $msg = trim($execErr !== '' ? $execErr : $mysqli->error);
    return ['ok' => false, 'errors' => [$msg !== '' ? 'Save failed: ' . $msg : 'Failed to update zone.']];
}

function setup_zones_delete(mysqli $mysqli, int $id): bool
{
    setup_zones_ensure_utf8mb4($mysqli);
    $s = $mysqli->prepare('DELETE FROM zone WHERE zone_id = ?');
    if (!$s) {
        return false;
    }
    $s->bind_param('i', $id);
    $ok = $s->execute();
    $s->close();
    return $ok;
}
