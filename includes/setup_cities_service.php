<?php
declare(strict_types=1);

function setup_cities_flash_set(string $type, string $message): void { $_SESSION['setup_cities_flash'] = ['type' => $type, 'message' => $message]; }
function setup_cities_flash_get(): ?array { if (!isset($_SESSION['setup_cities_flash'])) return null; $f = $_SESSION['setup_cities_flash']; unset($_SESSION['setup_cities_flash']); return is_array($f) ? $f : null; }
function setup_cities_csrf_token(): string { if (empty($_SESSION['setup_cities_csrf'])) $_SESSION['setup_cities_csrf'] = bin2hex(random_bytes(16)); return (string) $_SESSION['setup_cities_csrf']; }
function setup_cities_csrf_validate(string $token): bool { $s = (string) ($_SESSION['setup_cities_csrf'] ?? ''); return $s !== '' && hash_equals($s, $token); }

/** varchar limits per schema */
const SETUP_CITIES_MAX_NAME = 150;
const SETUP_CITIES_MAX_SHOT = 200;
const SETUP_CITIES_MAX_COUNTRY = 200;

function setup_cities_clip(string $value, int $maxChars): string
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

/**
 * Legacy DB may use latin1; Arabic UTF-8 fails on INSERT/UPDATE. Convert table once per request if needed.
 */
function setup_cities_ensure_utf8mb4(mysqli $mysqli): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;
    $sql = "SELECT TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'city' LIMIT 1";
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
    @$mysqli->query('ALTER TABLE city CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
}

/** @param array<string,mixed> $row */
function setup_cities_normalize_row(array $row): array
{
    if (!function_exists('normalize_arabic_text')) {
        return $row;
    }
    if (isset($row['city_shotform']) && is_string($row['city_shotform'])) {
        $row['city_shotform'] = normalize_arabic_text($row['city_shotform']);
    }
    return $row;
}

function setup_cities_next_id(mysqli $mysqli): int
{
    $r = $mysqli->query('SELECT COALESCE(MAX(city_id),0)+1 AS n FROM city');
    if ($r && ($row = $r->fetch_assoc())) {
        return max(1, (int) ($row['n'] ?? 1));
    }
    return 1;
}

function setup_cities_countries(mysqli $mysqli): array
{
    $r = $mysqli->query('SELECT country_name FROM country ORDER BY country_name');
    $rows = [];
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

function setup_cities_list(mysqli $mysqli): array
{
    setup_cities_ensure_utf8mb4($mysqli);
    $r = $mysqli->query('SELECT city_id, city_name, city_shotform, city_country_name FROM city ORDER BY city_name');
    $rows = [];
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $rows[] = setup_cities_normalize_row($row);
        }
    }
    return $rows;
}

function setup_cities_find(mysqli $mysqli, int $id): ?array
{
    setup_cities_ensure_utf8mb4($mysqli);
    $s = $mysqli->prepare('SELECT city_id, city_name, city_shotform, city_country_name FROM city WHERE city_id = ? LIMIT 1');
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
    return setup_cities_normalize_row($row);
}

function setup_cities_utf8_len(string $value): int
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($value, 'UTF-8');
    }
    return strlen($value);
}

function setup_cities_validate(array $d): array
{
    $e = [];
    if (setup_cities_utf8_len(trim((string) ($d['city_name'] ?? ''))) < 2) {
        $e[] = 'City name is required.';
    }
    if (setup_cities_utf8_len(trim((string) ($d['city_country_name'] ?? ''))) < 2) {
        $e[] = 'Country is required.';
    }
    return $e;
}

function setup_cities_create(mysqli $mysqli, array $input): array
{
    setup_cities_ensure_utf8mb4($mysqli);

    $d = [
        'city_name' => setup_cities_clip(trim((string) ($input['city_name'] ?? '')), SETUP_CITIES_MAX_NAME),
        'city_shotform' => setup_cities_clip(trim((string) ($input['city_shotform'] ?? '')), SETUP_CITIES_MAX_SHOT),
        'city_country_name' => setup_cities_clip(trim((string) ($input['city_country_name'] ?? '')), SETUP_CITIES_MAX_COUNTRY),
    ];
    $e = setup_cities_validate($d);
    if ($e) {
        return ['ok' => false, 'errors' => $e];
    }

    $id = setup_cities_next_id($mysqli);
    $s = $mysqli->prepare('INSERT INTO city (city_id, city_name, city_shotform, city_country_name) VALUES (?, ?, ?, ?)');
    if (!$s) {
        return ['ok' => false, 'errors' => ['Failed to prepare insert. ' . $mysqli->error]];
    }

    $n = $d['city_name'];
    $sf = $d['city_shotform'];
    $cn = $d['city_country_name'];
    $s->bind_param('isss', $id, $n, $sf, $cn);
    $ok = $s->execute();
    $execErr = $s->error;
    $s->close();
    if ($ok) {
        return ['ok' => true, 'id' => $id];
    }
    $msg = trim($execErr !== '' ? $execErr : $mysqli->error);
    return ['ok' => false, 'errors' => [$msg !== '' ? 'Save failed: ' . $msg : 'Failed to create city.']];
}

function setup_cities_update(mysqli $mysqli, int $id, array $input): array
{
    setup_cities_ensure_utf8mb4($mysqli);

    $d = [
        'city_name' => setup_cities_clip(trim((string) ($input['city_name'] ?? '')), SETUP_CITIES_MAX_NAME),
        'city_shotform' => setup_cities_clip(trim((string) ($input['city_shotform'] ?? '')), SETUP_CITIES_MAX_SHOT),
        'city_country_name' => setup_cities_clip(trim((string) ($input['city_country_name'] ?? '')), SETUP_CITIES_MAX_COUNTRY),
    ];
    $e = setup_cities_validate($d);
    if ($e) {
        return ['ok' => false, 'errors' => $e];
    }

    $s = $mysqli->prepare('UPDATE city SET city_name = ?, city_shotform = ?, city_country_name = ? WHERE city_id = ?');
    if (!$s) {
        return ['ok' => false, 'errors' => ['Failed to prepare update. ' . $mysqli->error]];
    }

    $n = $d['city_name'];
    $sf = $d['city_shotform'];
    $cn = $d['city_country_name'];
    $s->bind_param('sssi', $n, $sf, $cn, $id);
    $ok = $s->execute();
    $execErr = $s->error;
    $s->close();
    if ($ok) {
        return ['ok' => true];
    }
    $msg = trim($execErr !== '' ? $execErr : $mysqli->error);
    return ['ok' => false, 'errors' => [$msg !== '' ? 'Save failed: ' . $msg : 'Failed to update city.']];
}

function setup_cities_delete(mysqli $mysqli, int $id): bool
{
    setup_cities_ensure_utf8mb4($mysqli);
    $s = $mysqli->prepare('DELETE FROM city WHERE city_id = ?');
    if (!$s) {
        return false;
    }
    $s->bind_param('i', $id);
    $ok = $s->execute();
    $s->close();
    return $ok;
}
