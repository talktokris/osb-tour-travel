<?php
declare(strict_types=1);

function setup_sms_labels_flash_set(string $type, string $message): void { $_SESSION['setup_sms_labels_flash'] = ['type' => $type, 'message' => $message]; }
function setup_sms_labels_flash_get(): ?array { if (!isset($_SESSION['setup_sms_labels_flash'])) return null; $f = $_SESSION['setup_sms_labels_flash']; unset($_SESSION['setup_sms_labels_flash']); return is_array($f) ? $f : null; }
function setup_sms_labels_csrf_token(): string { if (empty($_SESSION['setup_sms_labels_csrf'])) $_SESSION['setup_sms_labels_csrf'] = bin2hex(random_bytes(16)); return (string) $_SESSION['setup_sms_labels_csrf']; }
function setup_sms_labels_csrf_validate(string $token): bool { $s = (string) ($_SESSION['setup_sms_labels_csrf'] ?? ''); return $s !== '' && hash_equals($s, $token); }

/** varchar(200) per schema — UTF-8 safe clip */
const SETUP_SMS_LABELS_MAX_FIELD = 200;

function setup_sms_labels_clip(string $value, int $maxChars = SETUP_SMS_LABELS_MAX_FIELD): string
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
 * Legacy DB may use latin1; Arabic UTF-8 fails on UPDATE. Convert table once per request if needed.
 */
function setup_sms_labels_ensure_utf8mb4(mysqli $mysqli): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;
    $sql = "SELECT TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sms_label' LIMIT 1";
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
    @$mysqli->query('ALTER TABLE sms_label CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
}

/** @param array<string,mixed> $row */
function setup_sms_labels_normalize_row(array $row): array
{
    if (!function_exists('normalize_arabic_text')) {
        return $row;
    }
    foreach (['sms_label_header', 'sms_label_footer'] as $k) {
        if (isset($row[$k]) && is_string($row[$k])) {
            $row[$k] = normalize_arabic_text($row[$k]);
        }
    }
    return $row;
}

function setup_sms_labels_list(mysqli $mysqli): array
{
    setup_sms_labels_ensure_utf8mb4($mysqli);
    $r = $mysqli->query('SELECT sms_label_id, sms_label_header, sms_label_footer FROM sms_label ORDER BY sms_label_id DESC');
    $rows = [];
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $rows[] = setup_sms_labels_normalize_row($row);
        }
    }
    return $rows;
}

function setup_sms_labels_find(mysqli $mysqli, int $id): ?array
{
    setup_sms_labels_ensure_utf8mb4($mysqli);
    $s = $mysqli->prepare('SELECT sms_label_id, sms_label_header, sms_label_footer FROM sms_label WHERE sms_label_id = ? LIMIT 1');
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
    return setup_sms_labels_normalize_row($row);
}

function setup_sms_labels_utf8_len(string $value): int
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($value, 'UTF-8');
    }
    return strlen($value);
}

function setup_sms_labels_validate(array $d): array
{
    $e = [];
    if (setup_sms_labels_utf8_len(trim((string) ($d['sms_label_header'] ?? ''))) < 2) {
        $e[] = 'SMS header label is required.';
    }
    return $e;
}

function setup_sms_labels_header_exists(mysqli $mysqli, string $header, int $excludeId = 0): bool
{
    $sql = 'SELECT sms_label_id FROM sms_label WHERE sms_label_header = ?';
    if ($excludeId > 0) {
        $sql .= ' AND sms_label_id <> ?';
    }
    $sql .= ' LIMIT 1';
    $s = $mysqli->prepare($sql);
    if (!$s) {
        return false;
    }
    if ($excludeId > 0) {
        $s->bind_param('si', $header, $excludeId);
    } else {
        $s->bind_param('s', $header);
    }
    $s->execute();
    $exists = (bool) $s->get_result()->fetch_assoc();
    $s->close();
    return $exists;
}

function setup_sms_labels_update(mysqli $mysqli, int $id, array $input): array
{
    setup_sms_labels_ensure_utf8mb4($mysqli);

    $d = [
        'sms_label_header' => setup_sms_labels_clip(trim((string) ($input['sms_label_header'] ?? ''))),
        'sms_label_footer' => setup_sms_labels_clip(trim((string) ($input['sms_label_footer'] ?? ''))),
    ];
    $e = setup_sms_labels_validate($d);
    if ($e) {
        return ['ok' => false, 'errors' => $e];
    }
    if (setup_sms_labels_header_exists($mysqli, $d['sms_label_header'], $id)) {
        return ['ok' => false, 'errors' => ['Header already exists.']];
    }

    $s = $mysqli->prepare('UPDATE sms_label SET sms_label_header = ?, sms_label_footer = ? WHERE sms_label_id = ?');
    if (!$s) {
        return ['ok' => false, 'errors' => ['Failed to prepare update. ' . $mysqli->error]];
    }

    $h = $d['sms_label_header'];
    $f = $d['sms_label_footer'];
    $s->bind_param('ssi', $h, $f, $id);
    $ok = $s->execute();
    $execErr = $s->error;
    $s->close();
    if ($ok) {
        return ['ok' => true];
    }
    $msg = trim($execErr !== '' ? $execErr : $mysqli->error);
    return ['ok' => false, 'errors' => [$msg !== '' ? 'Save failed: ' . $msg : 'Failed to update SMS label.']];
}
