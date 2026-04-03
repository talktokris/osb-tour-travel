<?php
declare(strict_types=1);

function setup_locations_flash_set(string $type, string $message): void { $_SESSION['setup_locations_flash'] = ['type' => $type, 'message' => $message]; }
function setup_locations_flash_get(): ?array { if (!isset($_SESSION['setup_locations_flash'])) return null; $f = $_SESSION['setup_locations_flash']; unset($_SESSION['setup_locations_flash']); return is_array($f) ? $f : null; }
function setup_locations_csrf_token(): string { if (empty($_SESSION['setup_locations_csrf'])) $_SESSION['setup_locations_csrf'] = bin2hex(random_bytes(16)); return (string) $_SESSION['setup_locations_csrf']; }
function setup_locations_csrf_validate(string $token): bool { $s=(string)($_SESSION['setup_locations_csrf']??''); return $s!=='' && hash_equals($s,$token); }

function setup_locations_next_id(mysqli $mysqli): int { $r=$mysqli->query('SELECT COALESCE(MAX(location_id),0)+1 AS n FROM location'); if($r&&($row=$r->fetch_assoc())) return max(1,(int)($row['n']??1)); return 1; }

/** @return list<array{city_name:string,city_country_name:string}> */
function setup_locations_cities_all(mysqli $mysqli): array
{
    $rows=[];$result=$mysqli->query('SELECT city_name, city_country_name FROM city ORDER BY city_name');
    if($result) while($row=$result->fetch_assoc()) $rows[]=['city_name'=>trim((string)($row['city_name']??'')),'city_country_name'=>trim((string)($row['city_country_name']??''))];
    return $rows;
}

function setup_locations_country_by_city(mysqli $mysqli, string $city): string
{
    $stmt=$mysqli->prepare('SELECT city_country_name FROM city WHERE city_name = ? LIMIT 1');
    if(!$stmt) return '';
    $stmt->bind_param('s',$city);$stmt->execute();$row=$stmt->get_result()->fetch_assoc();$stmt->close();
    return trim((string)($row['city_country_name']??''));
}

/** @return list<array<string,string>> */
function setup_locations_list(mysqli $mysqli): array
{
    $result=$mysqli->query('SELECT location_id, location_name, location_name_arb, location_country, location_city, location_address, location_phone FROM location ORDER BY location_name');
    if(!$result) return [];
    $rows=[]; while($row=$result->fetch_assoc()) $rows[]=$row; return $rows;
}

function setup_locations_find(mysqli $mysqli, int $id): ?array
{
    $stmt=$mysqli->prepare('SELECT location_id, location_name, location_name_arb, location_country, location_city, location_address, location_phone FROM location WHERE location_id = ? LIMIT 1');
    if(!$stmt) return null;
    $stmt->bind_param('i',$id);$stmt->execute();$row=$stmt->get_result()->fetch_assoc();$stmt->close(); return $row?:null;
}

function setup_locations_validate(array $data): array
{
    $errors=[];
    if(trim((string)($data['location_name']??''))==='') $errors[]='Location name english is required.';
    if(trim((string)($data['location_name_arb']??''))==='') $errors[]='Location name arabic is required.';
    if(trim((string)($data['location_city']??''))==='') $errors[]='City is required.';
    return $errors;
}

function setup_locations_create(mysqli $mysqli, array $input): array
{
    $data=['location_name'=>trim((string)($input['location_name']??'')),'location_name_arb'=>trim((string)($input['location_name_arb']??'')),'location_city'=>trim((string)($input['location_city']??'')),'location_address'=>trim((string)($input['location_address']??'')),'location_phone'=>trim((string)($input['location_phone']??''))];
    $errors=setup_locations_validate($data); if($errors) return ['ok'=>false,'errors'=>$errors];
    $data['location_country']=setup_locations_country_by_city($mysqli,$data['location_city']);
    if($data['location_country']==='') return ['ok'=>false,'errors'=>['Could not determine country for selected city.']];
    $id=setup_locations_next_id($mysqli);
    $stmt=$mysqli->prepare('INSERT INTO location (location_id, location_name, location_name_arb, location_country, location_city, location_address, location_phone) VALUES (?, ?, ?, ?, ?, ?, ?)');
    if(!$stmt) return ['ok'=>false,'errors'=>['Failed to prepare insert.']];
    $stmt->bind_param('issssss',$id,$data['location_name'],$data['location_name_arb'],$data['location_country'],$data['location_city'],$data['location_address'],$data['location_phone']);
    $ok=$stmt->execute();$stmt->close(); return $ok?['ok'=>true,'id'=>$id]:['ok'=>false,'errors'=>['Failed to create location.']];
}

function setup_locations_update(mysqli $mysqli, int $id, array $input): array
{
    $data=['location_name'=>trim((string)($input['location_name']??'')),'location_name_arb'=>trim((string)($input['location_name_arb']??'')),'location_city'=>trim((string)($input['location_city']??'')),'location_address'=>trim((string)($input['location_address']??'')),'location_phone'=>trim((string)($input['location_phone']??''))];
    $errors=setup_locations_validate($data); if($errors) return ['ok'=>false,'errors'=>$errors];
    $data['location_country']=setup_locations_country_by_city($mysqli,$data['location_city']);
    if($data['location_country']==='') return ['ok'=>false,'errors'=>['Could not determine country for selected city.']];
    $stmt=$mysqli->prepare('UPDATE location SET location_name = ?, location_name_arb = ?, location_country = ?, location_city = ?, location_address = ?, location_phone = ? WHERE location_id = ?');
    if(!$stmt) return ['ok'=>false,'errors'=>['Failed to prepare update.']];
    $stmt->bind_param('ssssssi',$data['location_name'],$data['location_name_arb'],$data['location_country'],$data['location_city'],$data['location_address'],$data['location_phone'],$id);
    $ok=$stmt->execute();$stmt->close(); return $ok?['ok'=>true]:['ok'=>false,'errors'=>['Failed to update location.']];
}

function setup_locations_delete(mysqli $mysqli, int $id): bool
{
    $stmt=$mysqli->prepare('DELETE FROM location WHERE location_id = ?');
    if(!$stmt) return false;
    $stmt->bind_param('i',$id);$ok=$stmt->execute();$stmt->close(); return $ok;
}
