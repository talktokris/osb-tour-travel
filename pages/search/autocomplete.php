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

switch ($field) {
    case 'agent':
        $stmt = $mysqli->prepare('SELECT DISTINCT agent_name FROM agent WHERE agent_name LIKE ? ORDER BY agent_name LIMIT ' . (int) $limit);
        if ($stmt) {
            $stmt->bind_param('s', $like);
            if ($stmt->execute()) {
                $r = $stmt->get_result();
                if ($r) {
                    $pushDistinct($r, 'agent_name');
                }
            }
            $stmt->close();
        }
        if ($items === [] && $user !== '') {
            $stmt = $mysqli->prepare('SELECT DISTINCT agent_name FROM file_entry WHERE user_enter_by = ? AND agent_name LIKE ? ORDER BY agent_name LIMIT ' . (int) $limit);
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
        }
        break;

    case 'supplier':
        $stmt = $mysqli->prepare('SELECT DISTINCT supplier_name FROM supplier WHERE supplier_name LIKE ? ORDER BY supplier_name LIMIT ' . (int) $limit);
        if ($stmt) {
            $stmt->bind_param('s', $like);
            if ($stmt->execute()) {
                $r = $stmt->get_result();
                if ($r) {
                    $pushDistinct($r, 'supplier_name');
                }
            }
            $stmt->close();
        }
        break;

    case 'driver':
        if ($user === '') {
            break;
        }
        $stmt = $mysqli->prepare('SELECT DISTINCT driver_name FROM file_entry WHERE user_enter_by = ? AND driver_name LIKE ? ORDER BY driver_name LIMIT ' . (int) $limit);
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

    case 'vehicle_no':
        $stmt = $mysqli->prepare('SELECT DISTINCT vehicles_no FROM vehicles WHERE vehicles_no LIKE ? ORDER BY vehicles_no LIMIT ' . (int) $limit);
        if ($stmt) {
            $stmt->bind_param('s', $like);
            if ($stmt->execute()) {
                $r = $stmt->get_result();
                if ($r) {
                    $pushDistinct($r, 'vehicles_no');
                }
            }
            $stmt->close();
        }
        break;

    case 'file_no':
        if ($user === '') {
            break;
        }
        $stmt = $mysqli->prepare('SELECT DISTINCT file_no FROM file_entry WHERE user_enter_by = ? AND file_no LIKE ? ORDER BY file_no LIMIT ' . (int) $limit);
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

    case 'ref_no':
        if ($user === '') {
            break;
        }
        $stmt = $mysqli->prepare('SELECT DISTINCT ref_no FROM file_entry WHERE user_enter_by = ? AND ref_no LIKE ? ORDER BY ref_no LIMIT ' . (int) $limit);
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

    case 'city':
        $stmt = $mysqli->prepare('SELECT DISTINCT from_city FROM service WHERE from_city LIKE ? ORDER BY from_city LIMIT ' . (int) $limit);
        if ($stmt) {
            $stmt->bind_param('s', $like);
            if ($stmt->execute()) {
                $r = $stmt->get_result();
                if ($r) {
                    $pushDistinct($r, 'from_city');
                }
            }
            $stmt->close();
        }
        break;

    case 'pax':
        if ($user === '') {
            break;
        }
        $stmt = $mysqli->prepare('SELECT DISTINCT last_name FROM file_entry WHERE user_enter_by = ? AND last_name LIKE ? ORDER BY last_name LIMIT ' . (int) $limit);
        if ($stmt) {
            $stmt->bind_param('ss', $user, $like);
            if ($stmt->execute()) {
                $r = $stmt->get_result();
                if ($r) {
                    $pushDistinct($r, 'last_name');
                }
            }
            $stmt->close();
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['ok' => false, 'items' => []]);
        exit;
}

echo json_encode(['ok' => true, 'items' => array_values($items)]);
exit;
