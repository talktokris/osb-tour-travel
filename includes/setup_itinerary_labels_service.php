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

function setup_itinerary_labels_get(mysqli $mysqli, int $id = 1): ?array
{
    $stmt = $mysqli->prepare('SELECT * FROM arebic_lebels WHERE arebic_lebels_id = ? LIMIT 1');
    if (!$stmt) return null;
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function setup_itinerary_labels_validate(array $data): array
{
    $errors = [];
    foreach (setup_itinerary_labels_field_map() as $k => $label) {
        $min = $k === 'Arrival_fills' ? 1 : 2;
        if (mb_strlen(trim((string) ($data[$k] ?? ''))) < $min) $errors[] = $label . ' is required.';
    }
    return $errors;
}

function setup_itinerary_labels_update(mysqli $mysqli, int $id, array $input): array
{
    $data = [];
    foreach (setup_itinerary_labels_field_map() as $k => $_label) $data[$k] = trim((string) ($input[$k] ?? ''));
    $errors = setup_itinerary_labels_validate($data);
    if ($errors) return ['ok' => false, 'errors' => $errors];

    $sql = 'UPDATE arebic_lebels SET ITINERARY_fills=?, Client_Name_fills=?, Ref_No_fills=?, Arrival_fills=?, Departure_fills=?, Transfers_fills=?, Drop_Off_Point_fills=?, Service_Name_fills=?, Pick_Up_Point_fills=?, Tours_fills=?, city_one_fills=?, city_two_fills=?, city_three_fills=?, city_four_fills=?, city_five_fills=? WHERE arebic_lebels_id=?';
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) return ['ok' => false, 'errors' => ['Failed to prepare update.']];
    $stmt->bind_param(
        'sssssssssssssssi',
        $data['ITINERARY_fills'],
        $data['Client_Name_fills'],
        $data['Ref_No_fills'],
        $data['Arrival_fills'],
        $data['Departure_fills'],
        $data['Transfers_fills'],
        $data['Drop_Off_Point_fills'],
        $data['Service_Name_fills'],
        $data['Pick_Up_Point_fills'],
        $data['Tours_fills'],
        $data['city_one_fills'],
        $data['city_two_fills'],
        $data['city_three_fills'],
        $data['city_four_fills'],
        $data['city_five_fills'],
        $id
    );
    $ok = $stmt->execute();
    $stmt->close();
    return $ok ? ['ok' => true] : ['ok' => false, 'errors' => ['Failed to update itinerary labels.']];
}

