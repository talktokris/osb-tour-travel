<?php

declare(strict_types=1);

function setup_suppliers_flash_set(string $type, string $message): void
{
    $_SESSION['setup_suppliers_flash'] = ['type' => $type, 'message' => $message];
}

function setup_suppliers_flash_get(): ?array
{
    if (!isset($_SESSION['setup_suppliers_flash'])) {
        return null;
    }
    $flash = $_SESSION['setup_suppliers_flash'];
    unset($_SESSION['setup_suppliers_flash']);
    return is_array($flash) ? $flash : null;
}

function setup_suppliers_csrf_token(): string
{
    if (empty($_SESSION['setup_suppliers_csrf'])) {
        $_SESSION['setup_suppliers_csrf'] = bin2hex(random_bytes(16));
    }
    return (string) $_SESSION['setup_suppliers_csrf'];
}

function setup_suppliers_csrf_validate(string $token): bool
{
    $sessionToken = (string) ($_SESSION['setup_suppliers_csrf'] ?? '');
    return $sessionToken !== '' && hash_equals($sessionToken, $token);
}

function setup_suppliers_countries(mysqli $mysqli): array
{
    $rows = [];
    $result = $mysqli->query('SELECT country_name FROM country ORDER BY country_name');
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $name = trim((string) ($row['country_name'] ?? ''));
            if ($name !== '') {
                $rows[] = $name;
            }
        }
    }
    return array_values(array_unique($rows));
}

/** @return list<array{city_name:string,city_country_name:string}> */
function setup_suppliers_cities_all(mysqli $mysqli): array
{
    $rows = [];
    $result = $mysqli->query('SELECT city_name, city_country_name FROM city ORDER BY city_country_name, city_name');
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

function setup_suppliers_distinct_names(mysqli $mysqli): array
{
    $rows = [];
    $result = $mysqli->query('SELECT DISTINCT supplier_name FROM supplier WHERE supplier_name <> \'\' ORDER BY supplier_name');
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $name = trim((string) ($row['supplier_name'] ?? ''));
            if ($name !== '') {
                $rows[] = $name;
            }
        }
    }
    return $rows;
}

function setup_suppliers_next_id(mysqli $mysqli): int
{
    $result = $mysqli->query('SELECT COALESCE(MAX(supplier_id), 0) + 1 AS n FROM supplier');
    if ($result && ($row = $result->fetch_assoc())) {
        return max(1, (int) ($row['n'] ?? 1));
    }
    return 1;
}

/** @return list<array<string, string>> */
function setup_suppliers_list(mysqli $mysqli, ?string $country, ?string $city, ?string $name): array
{
    $sql = 'SELECT supplier_id, supplier_name, supplier_address, supplier_country, supplier_city, supplier_email, supplier_contact_no FROM supplier WHERE 1=1';
    $types = '';
    $params = [];

    if ($country !== null && $country !== '') {
        $sql .= ' AND supplier_country = ?';
        $types .= 's';
        $params[] = $country;
    }
    if ($city !== null && $city !== '') {
        $sql .= ' AND supplier_city = ?';
        $types .= 's';
        $params[] = $city;
    }
    if ($name !== null && $name !== '') {
        $sql .= ' AND supplier_name = ?';
        $types .= 's';
        $params[] = $name;
    }
    $sql .= ' ORDER BY supplier_name';

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return [];
    }
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

function setup_suppliers_find(mysqli $mysqli, int $supplierId): ?array
{
    $stmt = $mysqli->prepare('SELECT supplier_id, supplier_name, supplier_address, supplier_country, supplier_city, supplier_email, supplier_contact_no FROM supplier WHERE supplier_id = ? LIMIT 1');
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('i', $supplierId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function setup_suppliers_validate(array $data): array
{
    $errors = [];
    if (trim((string) ($data['supplier_name'] ?? '')) === '') {
        $errors[] = 'Supplier name is required.';
    }
    if (trim((string) ($data['supplier_country'] ?? '')) === '') {
        $errors[] = 'Country is required.';
    }
    if (trim((string) ($data['supplier_city'] ?? '')) === '') {
        $errors[] = 'City is required.';
    }
    $email = trim((string) ($data['supplier_email'] ?? ''));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }
    if (trim((string) ($data['supplier_contact_no'] ?? '')) === '') {
        $errors[] = 'Contact number is required.';
    }
    return $errors;
}

function setup_suppliers_create(mysqli $mysqli, array $input): array
{
    $data = [
        'supplier_name' => trim((string) ($input['supplier_name'] ?? '')),
        'supplier_address' => trim((string) ($input['supplier_address'] ?? '')),
        'supplier_country' => trim((string) ($input['supplier_country'] ?? '')),
        'supplier_city' => trim((string) ($input['supplier_city'] ?? '')),
        'supplier_email' => trim((string) ($input['supplier_email'] ?? '')),
        'supplier_contact_no' => trim((string) ($input['supplier_contact_no'] ?? '')),
    ];
    $errors = setup_suppliers_validate($data);
    if ($errors) {
        return ['ok' => false, 'errors' => $errors];
    }

    $newId = setup_suppliers_next_id($mysqli);
    $stmt = $mysqli->prepare('INSERT INTO supplier (supplier_id, supplier_name, supplier_address, supplier_country, supplier_city, supplier_email, supplier_contact_no) VALUES (?, ?, ?, ?, ?, ?, ?)');
    if (!$stmt) {
        return ['ok' => false, 'errors' => ['Failed to prepare insert.']];
    }
    $stmt->bind_param(
        'issssss',
        $newId,
        $data['supplier_name'],
        $data['supplier_address'],
        $data['supplier_country'],
        $data['supplier_city'],
        $data['supplier_email'],
        $data['supplier_contact_no']
    );
    $ok = $stmt->execute();
    $stmt->close();
    return $ok ? ['ok' => true, 'id' => $newId] : ['ok' => false, 'errors' => ['Failed to create supplier.']];
}

function setup_suppliers_update(mysqli $mysqli, int $supplierId, array $input): array
{
    $data = [
        'supplier_name' => trim((string) ($input['supplier_name'] ?? '')),
        'supplier_address' => trim((string) ($input['supplier_address'] ?? '')),
        'supplier_country' => trim((string) ($input['supplier_country'] ?? '')),
        'supplier_city' => trim((string) ($input['supplier_city'] ?? '')),
        'supplier_email' => trim((string) ($input['supplier_email'] ?? '')),
        'supplier_contact_no' => trim((string) ($input['supplier_contact_no'] ?? '')),
    ];
    $errors = setup_suppliers_validate($data);
    if ($errors) {
        return ['ok' => false, 'errors' => $errors];
    }

    $stmt = $mysqli->prepare('UPDATE supplier SET supplier_name = ?, supplier_address = ?, supplier_country = ?, supplier_city = ?, supplier_email = ?, supplier_contact_no = ? WHERE supplier_id = ?');
    if (!$stmt) {
        return ['ok' => false, 'errors' => ['Failed to prepare update.']];
    }
    $stmt->bind_param(
        'ssssssi',
        $data['supplier_name'],
        $data['supplier_address'],
        $data['supplier_country'],
        $data['supplier_city'],
        $data['supplier_email'],
        $data['supplier_contact_no'],
        $supplierId
    );
    $ok = $stmt->execute();
    $stmt->close();
    return $ok ? ['ok' => true] : ['ok' => false, 'errors' => ['Failed to update supplier.']];
}

function setup_suppliers_delete(mysqli $mysqli, int $supplierId): bool
{
    $stmt = $mysqli->prepare('DELETE FROM supplier WHERE supplier_id = ?');
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('i', $supplierId);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}
