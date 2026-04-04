<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/home_dashboard_service.php';

header('Content-Type: application/json; charset=utf-8');

$type = (string) ($_GET['type'] ?? '');
$q = (string) ($_GET['q'] ?? '');

echo json_encode(home_dashboard_autocomplete_json($mysqli, $type, $q), JSON_UNESCAPED_UNICODE);
exit;
