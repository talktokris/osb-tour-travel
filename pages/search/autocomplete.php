<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
/** @var mysqli $mysqli */

if (empty($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'items' => []]);
    exit;
}

$field = trim((string) ($_GET['field'] ?? ''));
$q = trim((string) ($_GET['q'] ?? ''));
$like = $q === '' ? '%' : ('%' . $q . '%');
$limit = 60;
$items = [];

$pushDistinct = static function (mysqli_result $res, string $col) use (&$items): void {
    while ($row = $res->fetch_assoc()) {
        $v = trim((string) ($row[$col] ?? ''));
        if ($v !== '' && !in_array($v, $items, true)) {
            $items[] = $v;
        }
    }
};

$fetchFileEntryDistinct = static function (string $column) use ($mysqli, $like, $limit, $pushDistinct): void {
    $sql = 'SELECT DISTINCT ' . $column . ' FROM file_entry WHERE ' . $column . ' LIKE ? ORDER BY ' . $column . ' LIMIT ' . (int) $limit;
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return;
    }
    $stmt->bind_param('s', $like);
    if ($stmt->execute()) {
        $r = $stmt->get_result();
        if ($r) {
            $pushDistinct($r, $column);
        }
    }
    $stmt->close();
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
        if ($items === []) {
            $fetchFileEntryDistinct('agent_name');
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
        $fetchFileEntryDistinct('driver_name');
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
        $fetchFileEntryDistinct('file_no');
        break;

    case 'ref_no':
        $fetchFileEntryDistinct('ref_no');
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
        $fetchFileEntryDistinct('last_name');
        break;

    default:
        http_response_code(400);
        echo json_encode(['ok' => false, 'items' => []]);
        exit;
}

echo json_encode(['ok' => true, 'items' => array_values($items)]);
exit;
