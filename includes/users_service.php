<?php

function users_actor(mysqli $mysqli): array
{
    $userId = (int) ($_SESSION['user_id'] ?? 0);
    if ($userId <= 0) {
        return ['id' => 0, 'name' => '', 'role' => '', 'department' => '', 'is_super' => false, 'is_admin' => false];
    }

    $stmt = $mysqli->prepare('SELECT Userid, Name, Role, department FROM user_login WHERE Userid = ? LIMIT 1');
    if (!$stmt) {
        return ['id' => 0, 'name' => '', 'role' => '', 'department' => '', 'is_super' => false, 'is_admin' => false];
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        return ['id' => 0, 'name' => '', 'role' => '', 'department' => '', 'is_super' => false, 'is_admin' => false];
    }

    $role = trim((string) ($row['Role'] ?? ''));
    $department = trim((string) ($row['department'] ?? ''));

    return [
        'id' => (int) $row['Userid'],
        'name' => (string) ($row['Name'] ?? ''),
        'role' => $role,
        'department' => $department,
        'is_super' => strcasecmp($role, 'Super User') === 0,
        'is_admin' => strcasecmp($role, 'Admin User') === 0,
    ];
}

function users_can_access(array $actor): bool
{
    return !empty($actor['is_super']) || !empty($actor['is_admin']);
}

function users_require_access(array $actor): void
{
    if (!users_can_access($actor)) {
        header('Location: index.php?page=home');
        exit;
    }
}

function users_flash_set(string $type, string $message): void
{
    $_SESSION['users_flash'] = ['type' => $type, 'message' => $message];
}

function users_flash_get(): ?array
{
    if (!isset($_SESSION['users_flash'])) {
        return null;
    }
    $flash = $_SESSION['users_flash'];
    unset($_SESSION['users_flash']);
    return is_array($flash) ? $flash : null;
}

function users_csrf_token(): string
{
    if (empty($_SESSION['users_csrf'])) {
        $_SESSION['users_csrf'] = bin2hex(random_bytes(16));
    }
    return (string) $_SESSION['users_csrf'];
}

function users_csrf_validate(string $token): bool
{
    $sessionToken = (string) ($_SESSION['users_csrf'] ?? '');
    return $sessionToken !== '' && hash_equals($sessionToken, $token);
}

function users_lookup_values(mysqli $mysqli, string $table, string $column): array
{
    $sql = "SELECT {$column} AS value FROM {$table} ORDER BY {$column}";
    $result = $mysqli->query($sql);
    if ($result === false) {
        return [];
    }

    $values = [];
    while ($row = $result->fetch_assoc()) {
        $value = trim((string) ($row['value'] ?? ''));
        if ($value !== '') {
            $values[] = $value;
        }
    }
    return array_values(array_unique($values));
}

function users_user_in_scope(array $actor, array $user): bool
{
    if (!empty($actor['is_super'])) {
        return true;
    }
    if (!empty($actor['is_admin'])) {
        return strcasecmp((string) ($actor['department'] ?? ''), (string) ($user['department'] ?? '')) === 0;
    }
    return false;
}

function users_list(mysqli $mysqli, array $actor): array
{
    if (!users_can_access($actor)) {
        return [];
    }

    if (!empty($actor['is_super'])) {
        $result = $mysqli->query('SELECT Userid, Name, Username, gender, Role, department, position, Status, date_birth, Email, employee_id, contact_nomber, ic_passport, outgoing_server, outgoing_port_no, email_password FROM user_login ORDER BY Userid');
        if ($result === false) {
            return [];
        }
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    $stmt = $mysqli->prepare('SELECT Userid, Name, Username, gender, Role, department, position, Status, date_birth, Email, employee_id, contact_nomber, ic_passport, outgoing_server, outgoing_port_no, email_password FROM user_login WHERE department = ? ORDER BY Userid');
    if (!$stmt) {
        return [];
    }
    $dept = (string) ($actor['department'] ?? '');
    $stmt->bind_param('s', $dept);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

function users_find(mysqli $mysqli, int $userId, array $actor): ?array
{
    $stmt = $mysqli->prepare('SELECT Userid, Name, Username, gender, Role, department, position, Status, date_birth, Email, employee_id, contact_nomber, ic_passport, outgoing_server, outgoing_port_no, email_password FROM user_login WHERE Userid = ? LIMIT 1');
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        return null;
    }

    return users_user_in_scope($actor, $row) ? $row : null;
}

function users_validate_common(array $data, bool $isCreate): array
{
    $errors = [];

    if (mb_strlen(trim((string) ($data['Name'] ?? ''))) < 2) {
        $errors[] = 'Full Name must be at least 2 characters.';
    }
    if (mb_strlen(trim((string) ($data['Username'] ?? ''))) < 2) {
        $errors[] = 'User Name must be at least 2 characters.';
    }

    $required = ['Status', 'gender', 'Role', 'position', 'department'];
    foreach ($required as $key) {
        if (trim((string) ($data[$key] ?? '')) === '') {
            $errors[] = "{$key} is required.";
        }
    }

    if ($isCreate) {
        $pass = (string) ($data['password'] ?? '');
        $confirm = (string) ($data['conpassword'] ?? '');
        if (mb_strlen($pass) < 2) {
            $errors[] = 'Password must be at least 2 characters.';
        }
        if ($pass !== $confirm) {
            $errors[] = 'Confirm password does not match.';
        }
    }

    return $errors;
}

function users_username_exists(mysqli $mysqli, string $username, ?int $ignoreId = null): bool
{
    if ($ignoreId !== null) {
        $stmt = $mysqli->prepare('SELECT Userid FROM user_login WHERE Username = ? AND Userid <> ? LIMIT 1');
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('si', $username, $ignoreId);
    } else {
        $stmt = $mysqli->prepare('SELECT Userid FROM user_login WHERE Username = ? LIMIT 1');
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('s', $username);
    }

    $stmt->execute();
    $exists = $stmt->get_result()->fetch_assoc() !== null;
    $stmt->close();
    return $exists;
}

function users_create(mysqli $mysqli, array $input): array
{
    $errors = users_validate_common($input, true);

    $username = trim((string) ($input['Username'] ?? ''));
    if ($username !== '' && users_username_exists($mysqli, $username)) {
        $errors[] = 'This username already exists.';
    }

    if ($errors) {
        return ['ok' => false, 'errors' => $errors];
    }

    $sql = 'INSERT INTO user_login (Name, Username, password, Status, gender, Role, date_birth, position, Email, employee_id, contact_nomber, ic_passport, department, outgoing_server, outgoing_port_no, email_password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return ['ok' => false, 'errors' => ['Failed to prepare create statement.']];
    }

    $pass = md5((string) $input['password']);
    $values = [
        trim((string) ($input['Name'] ?? '')),
        $username,
        $pass,
        trim((string) ($input['Status'] ?? '')),
        trim((string) ($input['gender'] ?? '')),
        trim((string) ($input['Role'] ?? '')),
        trim((string) ($input['date_birth'] ?? '')),
        trim((string) ($input['position'] ?? '')),
        trim((string) ($input['Email'] ?? '')),
        trim((string) ($input['employee_id'] ?? '')),
        trim((string) ($input['contact_nomber'] ?? '')),
        trim((string) ($input['ic_passport'] ?? '')),
        trim((string) ($input['department'] ?? '')),
        trim((string) ($input['outgoing_server'] ?? '')),
        trim((string) ($input['outgoing_port_no'] ?? '')),
        trim((string) ($input['email_password'] ?? '')),
    ];
    $stmt->bind_param('ssssssssssssssss', ...$values);
    $ok = $stmt->execute();
    $newId = (int) $stmt->insert_id;
    $stmt->close();

    if (!$ok) {
        return ['ok' => false, 'errors' => ['Failed to create user.']];
    }

    return ['ok' => true, 'id' => $newId];
}

function users_update(mysqli $mysqli, int $userId, array $input): array
{
    $errors = users_validate_common($input, false);
    $username = trim((string) ($input['Username'] ?? ''));
    if ($username !== '' && users_username_exists($mysqli, $username, $userId)) {
        $errors[] = 'This username already exists.';
    }
    if ($errors) {
        return ['ok' => false, 'errors' => $errors];
    }

    $sql = 'UPDATE user_login SET Name = ?, Username = ?, Email = ?, position = ?, Status = ?, gender = ?, date_birth = ?, employee_id = ?, contact_nomber = ?, ic_passport = ?, department = ?, outgoing_server = ?, outgoing_port_no = ?, email_password = ? WHERE Userid = ?';
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return ['ok' => false, 'errors' => ['Failed to prepare update statement.']];
    }

    $values = [
        trim((string) ($input['Name'] ?? '')),
        $username,
        trim((string) ($input['Email'] ?? '')),
        trim((string) ($input['position'] ?? '')),
        trim((string) ($input['Status'] ?? '')),
        trim((string) ($input['gender'] ?? '')),
        trim((string) ($input['date_birth'] ?? '')),
        trim((string) ($input['employee_id'] ?? '')),
        trim((string) ($input['contact_nomber'] ?? '')),
        trim((string) ($input['ic_passport'] ?? '')),
        trim((string) ($input['department'] ?? '')),
        trim((string) ($input['outgoing_server'] ?? '')),
        trim((string) ($input['outgoing_port_no'] ?? '')),
        trim((string) ($input['email_password'] ?? '')),
        $userId,
    ];
    $stmt->bind_param('ssssssssssssssi', ...$values);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok ? ['ok' => true] : ['ok' => false, 'errors' => ['Failed to update user.']];
}

function users_update_role(mysqli $mysqli, int $userId, string $role): bool
{
    $stmt = $mysqli->prepare('UPDATE user_login SET Role = ? WHERE Userid = ?');
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('si', $role, $userId);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function users_update_password(mysqli $mysqli, int $userId, string $password, string $confirm): array
{
    if ($password === '') {
        return ['ok' => false, 'errors' => ['Please type the password.']];
    }
    if ($password !== $confirm) {
        return ['ok' => false, 'errors' => ['Password does not match confirm password.']];
    }

    $hash = md5($password);
    $stmt = $mysqli->prepare('UPDATE user_login SET password = ? WHERE Userid = ?');
    if (!$stmt) {
        return ['ok' => false, 'errors' => ['Failed to prepare password update statement.']];
    }
    $stmt->bind_param('si', $hash, $userId);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok ? ['ok' => true] : ['ok' => false, 'errors' => ['Failed to update password.']];
}

function users_delete(mysqli $mysqli, int $userId): bool
{
    $stmt = $mysqli->prepare('DELETE FROM user_login WHERE Userid = ?');
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('i', $userId);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

