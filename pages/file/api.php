<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/file_module_service.php';

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['ok' => false, 'error' => 'auth']);
    exit;
}

if (!file_module_has_agent()) {
    echo json_encode(['ok' => false, 'error' => 'agent']);
    exit;
}

$action = (string) ($_GET['action'] ?? '');
$q = trim((string) ($_GET['q'] ?? ''));

/** @return never */
function file_api_out(mixed $data): void
{
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

switch ($action) {
    case 'countries':
        file_api_out(['ok' => true, 'items' => file_module_countries($mysqli)]);
    case 'cities':
        file_api_out(['ok' => true, 'items' => file_module_cities_for_country($mysqli, $q)]);
    case 'cities_all':
        $all = [];
        $r = $mysqli->query('SELECT city_name FROM city ORDER BY city_country_name, city_name');
        if ($r) {
            while ($row = $r->fetch_assoc()) {
                $n = trim((string) ($row['city_name'] ?? ''));
                if ($n !== '') {
                    $all[] = $n;
                }
            }
        }
        file_api_out(['ok' => true, 'items' => array_values(array_unique($all))]);
    case 'locations':
        file_api_out(['ok' => true, 'items' => file_module_locations_for_city($mysqli, $q)]);
    case 'zones':
        file_api_out(['ok' => true, 'items' => file_module_zones_for_location($mysqli, $q)]);
    case 'services_between':
        $to = trim((string) ($_GET['to'] ?? ''));
        file_api_out(['ok' => true, 'items' => file_module_service_names_between($mysqli, $q, $to)]);
    case 'vehicle_types':
        file_api_out(['ok' => true, 'items' => file_module_vehicle_types($mysqli)]);
    default:
        file_api_out(['ok' => false, 'error' => 'action']);
}
