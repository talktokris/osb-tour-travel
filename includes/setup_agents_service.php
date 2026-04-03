<?php

declare(strict_types=1);

/**
 * Agent Setup CRUD + lookups (table `agent`, `country`, `city`).
 */

function setup_agents_upload_dir(): string
{
    return dirname(__DIR__) . '/uploads/agent_logos';
}

function setup_agents_upload_url_path(): string
{
    return 'uploads/agent_logos';
}

function setup_agents_flash_set(string $type, string $message): void
{
    $_SESSION['setup_agents_flash'] = ['type' => $type, 'message' => $message];
}

function setup_agents_flash_get(): ?array
{
    if (!isset($_SESSION['setup_agents_flash'])) {
        return null;
    }
    $flash = $_SESSION['setup_agents_flash'];
    unset($_SESSION['setup_agents_flash']);
    return is_array($flash) ? $flash : null;
}

function setup_agents_csrf_token(): string
{
    if (empty($_SESSION['setup_agents_csrf'])) {
        $_SESSION['setup_agents_csrf'] = bin2hex(random_bytes(16));
    }
    return (string) $_SESSION['setup_agents_csrf'];
}

function setup_agents_csrf_validate(string $token): bool
{
    $sessionToken = (string) ($_SESSION['setup_agents_csrf'] ?? '');
    return $sessionToken !== '' && hash_equals($sessionToken, $token);
}

function setup_agents_countries(mysqli $mysqli): array
{
    $rows = [];
    $result = $mysqli->query('SELECT country_name FROM country ORDER BY country_name');
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $n = trim((string) ($row['country_name'] ?? ''));
            if ($n !== '') {
                $rows[] = $n;
            }
        }
    }
    return array_values(array_unique($rows));
}

/** @return list<array{city_name:string,city_country_name:string}> */
function setup_agents_cities_all(mysqli $mysqli): array
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

function setup_agents_distinct_names(mysqli $mysqli): array
{
    $rows = [];
    $result = $mysqli->query('SELECT DISTINCT agent_name FROM agent WHERE agent_name <> \'\' ORDER BY agent_name');
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $n = trim((string) ($row['agent_name'] ?? ''));
            if ($n !== '') {
                $rows[] = $n;
            }
        }
    }
    return $rows;
}

function setup_agents_next_id(mysqli $mysqli): int
{
    $result = $mysqli->query('SELECT COALESCE(MAX(agent_id), 0) + 1 AS n FROM agent');
    if ($result && ($row = $result->fetch_assoc())) {
        return max(1, (int) ($row['n'] ?? 1));
    }
    return 1;
}

/** @return list<array<string, string>> */
function setup_agents_list(mysqli $mysqli, ?string $filterCountry, ?string $filterCity, ?string $filterAgentName): array
{
    $sql = 'SELECT agent_id, agent_name, agent_code, agent_address, agent_country, agent_city, agent_email, agent_contact_no, agent_mobile_no, agent_logo_name FROM agent WHERE 1=1';
    $types = '';
    $params = [];

    if ($filterCountry !== null && $filterCountry !== '') {
        $sql .= ' AND agent_country = ?';
        $types .= 's';
        $params[] = $filterCountry;
    }
    if ($filterCity !== null && $filterCity !== '') {
        $sql .= ' AND agent_city = ?';
        $types .= 's';
        $params[] = $filterCity;
    }
    if ($filterAgentName !== null && $filterAgentName !== '') {
        $sql .= ' AND agent_name = ?';
        $types .= 's';
        $params[] = $filterAgentName;
    }

    $sql .= ' ORDER BY agent_name';

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return [];
    }
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $list = [];
    while ($row = $result->fetch_assoc()) {
        $list[] = $row;
    }
    $stmt->close();
    return $list;
}

function setup_agents_find(mysqli $mysqli, int $agentId): ?array
{
    $stmt = $mysqli->prepare('SELECT agent_id, agent_name, agent_code, agent_address, agent_country, agent_city, agent_email, agent_contact_no, agent_mobile_no, agent_logo_name FROM agent WHERE agent_id = ? LIMIT 1');
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('i', $agentId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function setup_agents_validate(array $data, bool $isCreate): array
{
    $errors = [];
    if (mb_strlen(trim((string) ($data['agent_name'] ?? ''))) < 1) {
        $errors[] = 'Agent name is required.';
    }
    if (mb_strlen(trim((string) ($data['agent_email'] ?? ''))) < 3 || !filter_var(trim((string) ($data['agent_email'] ?? '')), FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }
    $req = ['agent_country', 'agent_city', 'agent_contact_no', 'agent_mobile_no'];
    foreach ($req as $k) {
        if (trim((string) ($data[$k] ?? '')) === '') {
            $errors[] = str_replace('_', ' ', $k) . ' is required.';
        }
    }
    return $errors;
}

function setup_agents_create(mysqli $mysqli, array $input): array
{
    $data = [
        'agent_name' => trim((string) ($input['agent_name'] ?? '')),
        'agent_code' => trim((string) ($input['agent_code'] ?? '')),
        'agent_address' => trim((string) ($input['agent_address'] ?? '')),
        'agent_country' => trim((string) ($input['agent_country'] ?? '')),
        'agent_city' => trim((string) ($input['agent_city'] ?? '')),
        'agent_email' => trim((string) ($input['agent_email'] ?? '')),
        'agent_contact_no' => trim((string) ($input['agent_contact_no'] ?? '')),
        'agent_mobile_no' => trim((string) ($input['agent_mobile_no'] ?? '')),
    ];
    $errors = setup_agents_validate($data, true);
    if ($errors) {
        return ['ok' => false, 'errors' => $errors];
    }

    $newId = setup_agents_next_id($mysqli);
    $logo = '';
    $sql = 'INSERT INTO agent (agent_id, agent_name, agent_code, agent_address, agent_country, agent_city, agent_email, agent_contact_no, agent_mobile_no, agent_logo_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return ['ok' => false, 'errors' => ['Failed to prepare insert.']];
    }
    $stmt->bind_param(
        'isssssssss',
        $newId,
        $data['agent_name'],
        $data['agent_code'],
        $data['agent_address'],
        $data['agent_country'],
        $data['agent_city'],
        $data['agent_email'],
        $data['agent_contact_no'],
        $data['agent_mobile_no'],
        $logo
    );
    try {
        $ok = $stmt->execute();
    } catch (Exception $e) {
        $stmt->close();
        return ['ok' => false, 'errors' => ['Create failed: ' . $e->getMessage()]];
    }
    $stmt->close();
    if (!$ok) {
        return ['ok' => false, 'errors' => ['Failed to create agent.']];
    }
    return ['ok' => true, 'id' => $newId];
}

function setup_agents_update(mysqli $mysqli, int $agentId, array $input): array
{
    $data = [
        'agent_name' => trim((string) ($input['agent_name'] ?? '')),
        'agent_code' => trim((string) ($input['agent_code'] ?? '')),
        'agent_address' => trim((string) ($input['agent_address'] ?? '')),
        'agent_country' => trim((string) ($input['agent_country'] ?? '')),
        'agent_city' => trim((string) ($input['agent_city'] ?? '')),
        'agent_email' => trim((string) ($input['agent_email'] ?? '')),
        'agent_contact_no' => trim((string) ($input['agent_contact_no'] ?? '')),
        'agent_mobile_no' => trim((string) ($input['agent_mobile_no'] ?? '')),
    ];
    $errors = setup_agents_validate($data, false);
    if ($errors) {
        return ['ok' => false, 'errors' => $errors];
    }

    $sql = 'UPDATE agent SET agent_name = ?, agent_code = ?, agent_address = ?, agent_country = ?, agent_city = ?, agent_email = ?, agent_contact_no = ?, agent_mobile_no = ? WHERE agent_id = ?';
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return ['ok' => false, 'errors' => ['Failed to prepare update.']];
    }
    $stmt->bind_param(
        'ssssssssi',
        $data['agent_name'],
        $data['agent_code'],
        $data['agent_address'],
        $data['agent_country'],
        $data['agent_city'],
        $data['agent_email'],
        $data['agent_contact_no'],
        $data['agent_mobile_no'],
        $agentId
    );
    $ok = $stmt->execute();
    $stmt->close();
    return $ok ? ['ok' => true] : ['ok' => false, 'errors' => ['Update failed.']];
}

function setup_agents_delete(mysqli $mysqli, int $agentId): bool
{
    $row = setup_agents_find($mysqli, $agentId);
    if (!$row) {
        return false;
    }
    $logo = trim((string) ($row['agent_logo_name'] ?? ''));
    if ($logo !== '') {
        $path = setup_agents_upload_dir() . '/' . basename($logo);
        if (is_file($path)) {
            @unlink($path);
        }
    }
    $stmt = $mysqli->prepare('DELETE FROM agent WHERE agent_id = ?');
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('i', $agentId);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

/** @return array{ok:bool, errors?:list<string>, filename?:string} */
function setup_agents_save_logo(mysqli $mysqli, int $agentId, array $file): array
{
    $agent = setup_agents_find($mysqli, $agentId);
    if (!$agent) {
        return ['ok' => false, 'errors' => ['Agent not found.']];
    }
    if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['ok' => false, 'errors' => ['No file uploaded.']];
    }
    $maxBytes = 2 * 1024 * 1024;
    if (($file['size'] ?? 0) > $maxBytes) {
        return ['ok' => false, 'errors' => ['File too large (max 2MB).']];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']) ?: '';
    $map = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];
    if (!isset($map[$mime])) {
        return ['ok' => false, 'errors' => ['Only JPEG, PNG, GIF, or WebP images are allowed.']];
    }
    $ext = $map[$mime];
    $dir = setup_agents_upload_dir();
    if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
        return ['ok' => false, 'errors' => ['Could not create upload directory.']];
    }
    if (!is_writable($dir)) {
        return ['ok' => false, 'errors' => ['Upload directory is not writable: ' . $dir]];
    }

    $filename = $agentId . 'logo.' . $ext;
    $dest = $dir . '/' . $filename;
    $oldLogo = trim((string) ($agent['agent_logo_name'] ?? ''));
    if ($oldLogo !== '' && $oldLogo !== $filename) {
        $oldPath = $dir . '/' . basename($oldLogo);
        if (is_file($oldPath)) {
            @unlink($oldPath);
        }
    }

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return ['ok' => false, 'errors' => ['Failed to save file to ' . basename($dest) . '. Please check upload permissions.']];
    }

    $stmt = $mysqli->prepare('UPDATE agent SET agent_logo_name = ? WHERE agent_id = ?');
    if (!$stmt) {
        return ['ok' => false, 'errors' => ['Failed to update database.']];
    }
    $stmt->bind_param('si', $filename, $agentId);
    $ok = $stmt->execute();
    $stmt->close();
    if (!$ok) {
        return ['ok' => false, 'errors' => ['Failed to save logo reference.']];
    }
    return ['ok' => true, 'filename' => $filename];
}
