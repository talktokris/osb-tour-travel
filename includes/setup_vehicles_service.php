<?php
declare(strict_types=1);

function setup_vehicles_flash_set(string $type, string $message): void { $_SESSION['setup_vehicles_flash'] = ['type' => $type, 'message' => $message]; }
function setup_vehicles_flash_get(): ?array { if (!isset($_SESSION['setup_vehicles_flash'])) return null; $f = $_SESSION['setup_vehicles_flash']; unset($_SESSION['setup_vehicles_flash']); return is_array($f) ? $f : null; }
function setup_vehicles_csrf_token(): string { if (empty($_SESSION['setup_vehicles_csrf'])) $_SESSION['setup_vehicles_csrf'] = bin2hex(random_bytes(16)); return (string) $_SESSION['setup_vehicles_csrf']; }
function setup_vehicles_csrf_validate(string $token): bool { $s = (string) ($_SESSION['setup_vehicles_csrf'] ?? ''); return $s !== '' && hash_equals($s, $token); }

function setup_vehicles_next_id(mysqli $mysqli): int { $r = $mysqli->query('SELECT COALESCE(MAX(vehicles_id), 0) + 1 AS n FROM vehicles'); if ($r && ($row = $r->fetch_assoc())) return max(1, (int) ($row['n'] ?? 1)); return 1; }

function setup_vehicles_types(mysqli $mysqli): array
{
    $rows = [];
    $result = $mysqli->query('SELECT vehicle_type_name FROM vehicle_type ORDER BY vehicle_type_name');
    if ($result) while ($row = $result->fetch_assoc()) { $n = trim((string) ($row['vehicle_type_name'] ?? '')); if ($n !== '') $rows[] = $n; }
    return array_values(array_unique($rows));
}

/** @return list<array<string,string>> */
function setup_vehicles_list(mysqli $mysqli): array
{
    $result = $mysqli->query('SELECT vehicles_id, vehicles_name, vehicles_type, vehicles_no, vehicles_max_occupancy FROM vehicles ORDER BY vehicles_name');
    if (!$result) return [];
    $rows = [];
    while ($row = $result->fetch_assoc()) $rows[] = $row;
    return $rows;
}

function setup_vehicles_find(mysqli $mysqli, int $id): ?array
{
    $stmt = $mysqli->prepare('SELECT vehicles_id, vehicles_name, vehicles_type, vehicles_no, vehicles_max_occupancy FROM vehicles WHERE vehicles_id = ? LIMIT 1');
    if (!$stmt) return null;
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function setup_vehicles_validate(array $data): array
{
    $errors = [];
    if (trim((string) ($data['vehicles_name'] ?? '')) === '') $errors[] = 'Vehicle name is required.';
    if (trim((string) ($data['vehicles_type'] ?? '')) === '') $errors[] = 'Vehicle type is required.';
    if (trim((string) ($data['vehicles_no'] ?? '')) === '') $errors[] = 'Vehicle number is required.';
    if (trim((string) ($data['vehicles_max_occupancy'] ?? '')) === '') $errors[] = 'Max occupancy is required.';
    return $errors;
}

function setup_vehicles_create(mysqli $mysqli, array $input): array
{
    $data = ['vehicles_name'=>trim((string)($input['vehicles_name']??'')), 'vehicles_type'=>trim((string)($input['vehicles_type']??'')), 'vehicles_no'=>trim((string)($input['vehicles_no']??'')), 'vehicles_max_occupancy'=>trim((string)($input['vehicles_max_occupancy']??''))];
    $errors = setup_vehicles_validate($data); if ($errors) return ['ok'=>false,'errors'=>$errors];
    $id = setup_vehicles_next_id($mysqli);
    $stmt = $mysqli->prepare('INSERT INTO vehicles (vehicles_id, vehicles_name, vehicles_type, vehicles_no, vehicles_max_occupancy) VALUES (?, ?, ?, ?, ?)');
    if (!$stmt) return ['ok'=>false,'errors'=>['Failed to prepare insert.']];
    $stmt->bind_param('issss', $id, $data['vehicles_name'], $data['vehicles_type'], $data['vehicles_no'], $data['vehicles_max_occupancy']);
    $ok = $stmt->execute(); $stmt->close();
    return $ok ? ['ok'=>true,'id'=>$id] : ['ok'=>false,'errors'=>['Failed to create vehicle.']];
}

function setup_vehicles_update(mysqli $mysqli, int $id, array $input): array
{
    $data = ['vehicles_name'=>trim((string)($input['vehicles_name']??'')), 'vehicles_type'=>trim((string)($input['vehicles_type']??'')), 'vehicles_no'=>trim((string)($input['vehicles_no']??'')), 'vehicles_max_occupancy'=>trim((string)($input['vehicles_max_occupancy']??''))];
    $errors = setup_vehicles_validate($data); if ($errors) return ['ok'=>false,'errors'=>$errors];
    $stmt = $mysqli->prepare('UPDATE vehicles SET vehicles_name = ?, vehicles_type = ?, vehicles_no = ?, vehicles_max_occupancy = ? WHERE vehicles_id = ?');
    if (!$stmt) return ['ok'=>false,'errors'=>['Failed to prepare update.']];
    $stmt->bind_param('ssssi', $data['vehicles_name'], $data['vehicles_type'], $data['vehicles_no'], $data['vehicles_max_occupancy'], $id);
    $ok = $stmt->execute(); $stmt->close();
    return $ok ? ['ok'=>true] : ['ok'=>false,'errors'=>['Failed to update vehicle.']];
}

function setup_vehicles_delete(mysqli $mysqli, int $id): bool
{
    $stmt = $mysqli->prepare('DELETE FROM vehicles WHERE vehicles_id = ?');
    if (!$stmt) return false;
    $stmt->bind_param('i', $id);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}
