<?php
declare(strict_types=1);

function setup_itinerary_labels_flash_set(string $type, string $message): void { $_SESSION['setup_itinerary_labels_flash'] = ['type' => $type, 'message' => $message]; }
function setup_itinerary_labels_flash_get(): ?array { if (!isset($_SESSION['setup_itinerary_labels_flash'])) return null; $f = $_SESSION['setup_itinerary_labels_flash']; unset($_SESSION['setup_itinerary_labels_flash']); return is_array($f) ? $f : null; }
function setup_itinerary_labels_csrf_token(): string { if (empty($_SESSION['setup_itinerary_labels_csrf'])) $_SESSION['setup_itinerary_labels_csrf'] = bin2hex(random_bytes(16)); return (string) $_SESSION['setup_itinerary_labels_csrf']; }
function setup_itinerary_labels_csrf_validate(string $token): bool { $s = (string) ($_SESSION['setup_itinerary_labels_csrf'] ?? ''); return $s !== '' && hash_equals($s, $token); }

function setup_itinerary_labels_field_map(): array
{
    return [
        'ITINERARY_fills' => 'Itinerary',
        'Client_Name_fills' => 'Client Name',
        'Ref_No_fills' => 'Ref No',
        'Arrival_fills' => 'Arrival',
        'Departure_fills' => 'Departure',
        'Transfers_fills' => 'Transfers',
        'Drop_Off_Point_fills' => 'Drop Off Point',
        'Service_Name_fills' => 'Service Name',
        'Pick_Up_Point_fills' => 'Pick Up Point',
        'Tours_fills' => 'Tours',
        'city_one_fills' => 'Pick up Time',
        'city_two_fills' => 'City',
        'city_three_fills' => 'Service Name',
        'city_four_fills' => 'Date',
        'city_five_fills' => 'Five',
    ];
}

/** Matches `arebic_lebels` varchar/text limits in schema (UTF-8 safe clip). */
function setup_itinerary_labels_field_max_lengths(): array
{
    return [
        'ITINERARY_fills' => 80,
        'Client_Name_fills' => 80,
        'Ref_No_fills' => 80,
        'Arrival_fills' => 80,
        'Departure_fills' => 80,
        'Transfers_fills' => 80,
        'Drop_Off_Point_fills' => 80,
        'Service_Name_fills' => 80,
        'Pick_Up_Point_fills' => 80,
        'Tours_fills' => 80,
        'city_one_fills' => 100,
        'city_two_fills' => 100,
        'city_three_fills' => 100,
        'city_four_fills' => 100,
        'city_five_fills' => 65535,
    ];
}

function setup_itinerary_labels_clip(string $value, int $maxChars): string
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
 * Legacy DB used latin1; Arabic UTF-8 fails on INSERT/UPDATE. Convert table once per request if needed.
 */
function setup_itinerary_labels_ensure_utf8mb4(mysqli $mysqli): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;
    $sql = "SELECT TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'arebic_lebels' LIMIT 1";
    $res = $mysqli->query($sql);
    if (!$res) {
        return;
    }
    $row = $res->fetch_assoc();
    $res->close();
    $coll = strtolower((string) ($row['TABLE_COLLATION'] ?? ''));
    if ($coll !== '' && str_contains($coll, 'utf8mb4')) {
        return;
    }
    @$mysqli->query('ALTER TABLE arebic_lebels CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
}

function setup_itinerary_labels_get(mysqli $mysqli, int $id = 1): ?array
{
    setup_itinerary_labels_ensure_utf8mb4($mysqli);
    $stmt = $mysqli->prepare('SELECT * FROM arebic_lebels WHERE arebic_lebels_id = ? LIMIT 1');
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row && function_exists('normalize_arabic_text')) {
        foreach (array_keys(setup_itinerary_labels_field_map()) as $k) {
            if (isset($row[$k]) && is_string($row[$k])) {
                $row[$k] = normalize_arabic_text($row[$k]);
            }
        }
    }
    return $row ?: null;
}

function setup_itinerary_labels_utf8_len(string $value): int
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($value, 'UTF-8');
    }
    return strlen($value);
}

function setup_itinerary_labels_validate(array $data): array
{
    $errors = [];
    foreach (setup_itinerary_labels_field_map() as $k => $label) {
        $min = $k === 'Arrival_fills' ? 1 : 2;
        if (setup_itinerary_labels_utf8_len(trim((string) ($data[$k] ?? ''))) < $min) {
            $errors[] = $label . ' is required.';
        }
    }
    return $errors;
}

function setup_itinerary_labels_update(mysqli $mysqli, int $id, array $input): array
{
    setup_itinerary_labels_ensure_utf8mb4($mysqli);

    $data = [];
    $maxLens = setup_itinerary_labels_field_max_lengths();
    foreach (setup_itinerary_labels_field_map() as $k => $_label) {
        $raw = trim((string) ($input[$k] ?? ''));
        $data[$k] = setup_itinerary_labels_clip($raw, (int) ($maxLens[$k] ?? 0));
    }

    $errors = setup_itinerary_labels_validate($data);
    if ($errors) {
        return ['ok' => false, 'errors' => $errors];
    }

    $sql = 'UPDATE arebic_lebels SET ITINERARY_fills=?, Client_Name_fills=?, Ref_No_fills=?, Arrival_fills=?, Departure_fills=?, Transfers_fills=?, Drop_Off_Point_fills=?, Service_Name_fills=?, Pick_Up_Point_fills=?, Tours_fills=?, city_one_fills=?, city_two_fills=?, city_three_fills=?, city_four_fills=?, city_five_fills=? WHERE arebic_lebels_id=?';
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return ['ok' => false, 'errors' => ['Failed to prepare update. ' . $mysqli->error]];
    }

    $p1 = $data['ITINERARY_fills'];
    $p2 = $data['Client_Name_fills'];
    $p3 = $data['Ref_No_fills'];
    $p4 = $data['Arrival_fills'];
    $p5 = $data['Departure_fills'];
    $p6 = $data['Transfers_fills'];
    $p7 = $data['Drop_Off_Point_fills'];
    $p8 = $data['Service_Name_fills'];
    $p9 = $data['Pick_Up_Point_fills'];
    $p10 = $data['Tours_fills'];
    $p11 = $data['city_one_fills'];
    $p12 = $data['city_two_fills'];
    $p13 = $data['city_three_fills'];
    $p14 = $data['city_four_fills'];
    $p15 = $data['city_five_fills'];

    $stmt->bind_param(
        str_repeat('s', 15) . 'i',
        $p1,
        $p2,
        $p3,
        $p4,
        $p5,
        $p6,
        $p7,
        $p8,
        $p9,
        $p10,
        $p11,
        $p12,
        $p13,
        $p14,
        $p15,
        $id
    );

    $ok = $stmt->execute();
    $execErr = $stmt->error;
    $stmt->close();
    if ($ok) {
        return ['ok' => true];
    }
    $msg = trim($execErr !== '' ? $execErr : $mysqli->error);
    return ['ok' => false, 'errors' => [$msg !== '' ? 'Save failed: ' . $msg : 'Failed to update itinerary labels.']];
}
