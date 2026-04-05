<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'items' => []]);
    exit;
}

$field = trim((string) ($_GET['field'] ?? ''));
$q = trim((string) ($_GET['q'] ?? ''));
$like = $q === '' ? '%' : ('%' . $q . '%');
$limit = 60;
$user = trim((string) ($_SESSION['user_name'] ?? ''));
$items = [];

$pushDistinct = static function (mysqli_result $res, string $col) use (&$items): void {
    while ($row = $res->fetch_assoc()) {
        $v = trim((string) ($row[$col] ?? ''));
        if ($v !== '' && !in_array($v, $items, true)) {
            $items[] = $v;
        }
    }
};

if ($user === '') {
    echo json_encode(['ok' => true, 'items' => []]);
    exit;
}

$lim = (int) $limit;

switch ($field) {
    case 'd_driver':
        $stmt = $mysqli->prepare(
            'SELECT DISTINCT driver_name FROM file_entry WHERE user_enter_by = ? AND driver_name LIKE ? ORDER BY driver_name LIMIT ' . $lim
        );
        if ($stmt) {
            $stmt->bind_param('ss', $user, $like);
            if ($stmt->execute()) {
                $r = $stmt->get_result();
                if ($r) {
                    $pushDistinct($r, 'driver_name');
                }
            }
            $stmt->close();
        }
        break;

    case 'd_ref':
        $stmt = $mysqli->prepare(
            'SELECT DISTINCT ref_no FROM file_entry WHERE user_enter_by = ? AND ref_no LIKE ? ORDER BY ref_no LIMIT ' . $lim
        );
        if ($stmt) {
            $stmt->bind_param('ss', $user, $like);
            if ($stmt->execute()) {
                $r = $stmt->get_result();
                if ($r) {
                    $pushDistinct($r, 'ref_no');
                }
            }
            $stmt->close();
        }
        break;

    case 'd_file':
        $stmt = $mysqli->prepare(
            'SELECT DISTINCT file_no FROM file_entry WHERE user_enter_by = ? AND file_no LIKE ? ORDER BY file_no LIMIT ' . $lim
        );
        if ($stmt) {
            $stmt->bind_param('ss', $user, $like);
            if ($stmt->execute()) {
                $r = $stmt->get_result();
                if ($r) {
                    $pushDistinct($r, 'file_no');
                }
            }
            $stmt->close();
        }
        break;

    case 'd_agent':
        $stmt = $mysqli->prepare(
            'SELECT DISTINCT agent_name FROM file_entry WHERE user_enter_by = ? AND agent_name LIKE ? ORDER BY agent_name LIMIT ' . $lim
        );
        if ($stmt) {
            $stmt->bind_param('ss', $user, $like);
            if ($stmt->execute()) {
                $r = $stmt->get_result();
                if ($r) {
                    $pushDistinct($r, 'agent_name');
                }
            }
            $stmt->close();
        }
        break;

    case 'd_supplier':
        $stmt = $mysqli->prepare(
            'SELECT DISTINCT supplier_name FROM file_entry WHERE user_enter_by = ? AND supplier_name LIKE ? ORDER BY supplier_name LIMIT ' . $lim
        );
        if ($stmt) {
            $stmt->bind_param('ss', $user, $like);
            if ($stmt->execute()) {
                $r = $stmt->get_result();
                if ($r) {
                    $pushDistinct($r, 'supplier_name');
                }
            }
            $stmt->close();
        }
        break;

    case 'd_pax':
        $stmt = $mysqli->prepare(
            'SELECT DISTINCT first_name FROM file_entry WHERE user_enter_by = ? AND first_name LIKE ? ORDER BY first_name LIMIT ' . $lim
        );
        if ($stmt) {
            $stmt->bind_param('ss', $user, $like);
            if ($stmt->execute()) {
                $r = $stmt->get_result();
                if ($r) {
                    $pushDistinct($r, 'first_name');
                }
            }
            $stmt->close();
        }
        break;

    default:
        break;
}

echo json_encode(['ok' => true, 'items' => $items]);
