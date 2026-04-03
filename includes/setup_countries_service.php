<?php
declare(strict_types=1);

function setup_countries_flash_set(string $type, string $message): void { $_SESSION['setup_countries_flash'] = ['type' => $type, 'message' => $message]; }
function setup_countries_flash_get(): ?array { if (!isset($_SESSION['setup_countries_flash'])) return null; $f = $_SESSION['setup_countries_flash']; unset($_SESSION['setup_countries_flash']); return is_array($f) ? $f : null; }
function setup_countries_csrf_token(): string { if (empty($_SESSION['setup_countries_csrf'])) $_SESSION['setup_countries_csrf'] = bin2hex(random_bytes(16)); return (string) $_SESSION['setup_countries_csrf']; }
function setup_countries_csrf_validate(string $token): bool { $s = (string) ($_SESSION['setup_countries_csrf'] ?? ''); return $s !== '' && hash_equals($s, $token); }

const SETUP_COUNTRIES_MAX_NAME = 150;
const SETUP_COUNTRIES_MAX_SHORT = 200;

function setup_countries_clip(string $value, int $maxChars): string
{
    if ($maxChars <= 0) return $value;
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($value, 'UTF-8') <= $maxChars) return $value;
        return mb_substr($value, 0, $maxChars, 'UTF-8');
    }
    return strlen($value) <= $maxChars ? $value : substr($value, 0, $maxChars);
}

function setup_countries_utf8_len(string $value): int
{
    if (function_exists('mb_strlen')) return mb_strlen($value, 'UTF-8');
    return strlen($value);
}

function setup_countries_ensure_utf8mb4(mysqli $mysqli): void
{
    static $checked = false;
    if ($checked) return;
    $checked = true;
    $sql = "SELECT TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'country' LIMIT 1";
    $res = $mysqli->query($sql);
    if (!$res) return;
    $row = $res->fetch_assoc();
    $res->close();
    $coll = strtolower((string) ($row['TABLE_COLLATION'] ?? ''));
    if ($coll !== '' && strpos($coll, 'utf8mb4') !== false) return;
    @$mysqli->query('ALTER TABLE country CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
}

/** @param array<string,mixed> $row */
function setup_countries_normalize_row(array $row): array
{
    if (function_exists('normalize_arabic_text') && isset($row['country_shotform']) && is_string($row['country_shotform'])) {
        $row['country_shotform'] = normalize_arabic_text($row['country_shotform']);
    }
    return $row;
}

function setup_countries_next_id(mysqli $mysqli): int
{
    $r = $mysqli->query('SELECT COALESCE(MAX(country_id),0)+1 AS n FROM country');
    if ($r && ($row = $r->fetch_assoc())) return max(1, (int) ($row['n'] ?? 1));
    return 1;
}

function setup_countries_list(mysqli $mysqli): array
{
    setup_countries_ensure_utf8mb4($mysqli);
    $r = $mysqli->query('SELECT country_id, country_name, country_shotform FROM country ORDER BY country_name');
    $rows = [];
    if ($r) while ($row = $r->fetch_assoc()) $rows[] = setup_countries_normalize_row($row);
    return $rows;
}

function setup_countries_find(mysqli $mysqli, int $id): ?array
{
    setup_countries_ensure_utf8mb4($mysqli);
    $s = $mysqli->prepare('SELECT country_id, country_name, country_shotform FROM country WHERE country_id = ? LIMIT 1');
    if (!$s) return null;
    $s->bind_param('i', $id);
    $s->execute();
    $row = $s->get_result()->fetch_assoc();
    $s->close();
    if (!$row) return null;
    return setup_countries_normalize_row($row);
}

function setup_countries_validate(array $data): array
{
    $e = [];
    if (setup_countries_utf8_len(trim((string) ($data['country_name'] ?? ''))) < 2) $e[] = 'Country name is required.';
    return $e;
}

function setup_countries_name_exists(mysqli $mysqli, string $name, int $excludeId = 0): bool
{
    $sql = 'SELECT country_id FROM country WHERE country_name = ?';
    if ($excludeId > 0) $sql .= ' AND country_id <> ?';
    $sql .= ' LIMIT 1';
    $s = $mysqli->prepare($sql);
    if (!$s) return false;
    if ($excludeId > 0) $s->bind_param('si', $name, $excludeId); else $s->bind_param('s', $name);
    $s->execute();
    $exists = (bool) $s->get_result()->fetch_assoc();
    $s->close();
    return $exists;
}

function setup_countries_create(mysqli $mysqli, array $input): array
{
    setup_countries_ensure_utf8mb4($mysqli);
    $d = [
        'country_name' => setup_countries_clip(trim((string) ($input['country_name'] ?? '')), SETUP_COUNTRIES_MAX_NAME),
        'country_shotform' => setup_countries_clip(trim((string) ($input['country_shotform'] ?? '')), SETUP_COUNTRIES_MAX_SHORT),
    ];
    $e = setup_countries_validate($d);
    if ($e) return ['ok' => false, 'errors' => $e];
    if (setup_countries_name_exists($mysqli, $d['country_name'])) return ['ok' => false, 'errors' => ['Country already exists.']];
    $id = setup_countries_next_id($mysqli);
    $s = $mysqli->prepare('INSERT INTO country (country_id, country_name, country_shotform) VALUES (?, ?, ?)');
    if (!$s) return ['ok' => false, 'errors' => ['Failed to prepare insert. ' . $mysqli->error]];
    $name = $d['country_name'];
    $short = $d['country_shotform'];
    $s->bind_param('iss', $id, $name, $short);
    $ok = $s->execute();
    $execErr = $s->error;
    $s->close();
    if ($ok) return ['ok' => true, 'id' => $id];
    $msg = trim($execErr !== '' ? $execErr : $mysqli->error);
    return ['ok' => false, 'errors' => [$msg !== '' ? 'Save failed: ' . $msg : 'Failed to create country.']];
}

function setup_countries_update(mysqli $mysqli, int $id, array $input): array
{
    setup_countries_ensure_utf8mb4($mysqli);
    $d = [
        'country_name' => setup_countries_clip(trim((string) ($input['country_name'] ?? '')), SETUP_COUNTRIES_MAX_NAME),
        'country_shotform' => setup_countries_clip(trim((string) ($input['country_shotform'] ?? '')), SETUP_COUNTRIES_MAX_SHORT),
    ];
    $e = setup_countries_validate($d);
    if ($e) return ['ok' => false, 'errors' => $e];
    if (setup_countries_name_exists($mysqli, $d['country_name'], $id)) return ['ok' => false, 'errors' => ['Country already exists.']];
    $s = $mysqli->prepare('UPDATE country SET country_name = ?, country_shotform = ? WHERE country_id = ?');
    if (!$s) return ['ok' => false, 'errors' => ['Failed to prepare update. ' . $mysqli->error]];
    $name = $d['country_name'];
    $short = $d['country_shotform'];
    $s->bind_param('ssi', $name, $short, $id);
    $ok = $s->execute();
    $execErr = $s->error;
    $s->close();
    if ($ok) return ['ok' => true];
    $msg = trim($execErr !== '' ? $execErr : $mysqli->error);
    return ['ok' => false, 'errors' => [$msg !== '' ? 'Save failed: ' . $msg : 'Failed to update country.']];
}

function setup_countries_delete(mysqli $mysqli, int $id): bool
{
    setup_countries_ensure_utf8mb4($mysqli);
    $s = $mysqli->prepare('DELETE FROM country WHERE country_id = ?');
    if (!$s) return false;
    $s->bind_param('i', $id);
    $ok = $s->execute();
    $s->close();
    return $ok;
}

