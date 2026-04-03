<?php
declare(strict_types=1);

function setup_services_flash_set(string $type,string $message):void{$_SESSION['setup_services_flash']=['type'=>$type,'message'=>$message];}
function setup_services_flash_get():?array{if(!isset($_SESSION['setup_services_flash']))return null;$f=$_SESSION['setup_services_flash'];unset($_SESSION['setup_services_flash']);return is_array($f)?$f:null;}
function setup_services_csrf_token():string{if(empty($_SESSION['setup_services_csrf']))$_SESSION['setup_services_csrf']=bin2hex(random_bytes(16));return (string)$_SESSION['setup_services_csrf'];}
function setup_services_csrf_validate(string $token):bool{$s=(string)($_SESSION['setup_services_csrf']??'');return $s!==''&&hash_equals($s,$token);}

function setup_services_next_id(mysqli $mysqli): int {$r=$mysqli->query('SELECT COALESCE(MAX(service_id),0)+1 AS n FROM service');if($r&&($row=$r->fetch_assoc()))return max(1,(int)($row['n']??1));return 1;}

function setup_services_types(mysqli $mysqli): array { $rows=[];$r=$mysqli->query('SELECT service_type_name FROM service_type ORDER BY service_type_name');if($r)while($row=$r->fetch_assoc()){$n=trim((string)($row['service_type_name']??''));if($n!=='')$rows[]=$n;}return array_values(array_unique($rows)); }
function setup_services_vehicle_types(mysqli $mysqli): array { $rows=[];$r=$mysqli->query('SELECT vehicle_type_name FROM vehicle_type ORDER BY vehicle_type_name');if($r)while($row=$r->fetch_assoc()){$n=trim((string)($row['vehicle_type_name']??''));if($n!=='')$rows[]=$n;}return array_values(array_unique($rows)); }
/** @return list<array<string,string>> */ function setup_services_locations(mysqli $mysqli): array { $r=$mysqli->query('SELECT location_name, location_country, location_city, location_address FROM location ORDER BY location_name'); if(!$r)return []; $rows=[]; while($row=$r->fetch_assoc()) $rows[]=$row; return $rows; }
function setup_services_location_map(mysqli $mysqli): array { $map=[]; foreach(setup_services_locations($mysqli) as $loc){$map[(string)$loc['location_name']]=$loc;} return $map; }
function setup_services_countries(mysqli $mysqli): array { $rows=[];$r=$mysqli->query('SELECT country_name FROM country ORDER BY country_name');if($r)while($row=$r->fetch_assoc()){$n=trim((string)($row['country_name']??''));if($n!=='')$rows[]=$n;}return array_values(array_unique($rows));}
/** @return list<array{city_name:string,city_country_name:string}> */ function setup_services_cities_all(mysqli $mysqli): array { $rows=[];$r=$mysqli->query('SELECT city_name, city_country_name FROM city ORDER BY city_country_name, city_name');if($r)while($row=$r->fetch_assoc())$rows[]=['city_name'=>trim((string)($row['city_name']??'')),'city_country_name'=>trim((string)($row['city_country_name']??''))];return $rows;}

/** @return list<array<string,string>> */
function setup_services_list(mysqli $mysqli, ?string $country, ?string $city, ?string $type): array
{
    $sql='SELECT service_id, service_type, from_country, from_locaion, from_city, to_country, to_locaion, to_city, service_name_english, service_name_arabic, buying_price, selling_price, service_categories, vehicle_type, sic_adult_price, sic_children_price FROM service WHERE 1=1';
    $types=''; $params=[];
    if($country!==null&&$country!==''){ $sql.=' AND from_country = ?'; $types.='s'; $params[]=$country; }
    if($city!==null&&$city!==''){ $sql.=' AND from_city = ?'; $types.='s'; $params[]=$city; }
    if($type!==null&&$type!==''){ $sql.=' AND service_type = ?'; $types.='s'; $params[]=$type; }
    $sql.=' ORDER BY service_id DESC';
    $stmt=$mysqli->prepare($sql); if(!$stmt)return [];
    if($types!=='')$stmt->bind_param($types,...$params);
    $stmt->execute(); $r=$stmt->get_result(); $rows=[]; while($row=$r->fetch_assoc())$rows[]=$row; $stmt->close(); return $rows;
}

function setup_services_find(mysqli $mysqli, int $id): ?array
{
    $stmt=$mysqli->prepare('SELECT service_id, service_type, from_country, from_locaion, from_city, from_address, to_country, to_locaion, to_city, to_address, service_name_english, service_name_arabic, buying_price, selling_price, service_categories, vehicle_type, sic_adult_price, sic_children_price FROM service WHERE service_id = ? LIMIT 1');
    if(!$stmt)return null; $stmt->bind_param('i',$id); $stmt->execute(); $row=$stmt->get_result()->fetch_assoc(); $stmt->close(); return $row?:null;
}

function setup_services_validate(array $data): array
{
    $errors=[];
    if(trim((string)($data['service_type']??''))==='')$errors[]='Service type is required.';
    if(trim((string)($data['from_locaion']??''))==='')$errors[]='From location is required.';
    if(trim((string)($data['to_locaion']??''))==='')$errors[]='To location is required.';
    if(trim((string)($data['service_name_english']??''))==='')$errors[]='Service name english is required.';
    if(trim((string)($data['service_name_arabic']??''))==='')$errors[]='Service name arabic is required.';
    $cat=trim((string)($data['service_categories']??''));
    if($cat==='')$errors[]='Service category is required.';
    if(trim((string)($data['vehicle_type']??''))==='')$errors[]='Vehicle type is required.';
    if($cat==='Private'){
        if(trim((string)($data['buying_price']??''))==='')$errors[]='Buying price is required for Private.';
        if(trim((string)($data['selling_price']??''))==='')$errors[]='Selling price is required for Private.';
    } elseif($cat==='SIC'){
        if(trim((string)($data['sic_adult_price']??''))==='')$errors[]='SIC adult price is required for SIC.';
        if(trim((string)($data['sic_children_price']??''))==='')$errors[]='SIC children price is required for SIC.';
    }
    return $errors;
}

function setup_services_enrich_locations(mysqli $mysqli, array $data): array
{
    $map=setup_services_location_map($mysqli);
    $from=$map[$data['from_locaion']]??null; $to=$map[$data['to_locaion']]??null;
    if(!$from || !$to) return ['ok'=>false,'errors'=>['Invalid from/to location selection.']];
    $data['from_country']=(string)($from['location_country']??'');
    $data['from_city']=(string)($from['location_city']??'');
    $data['from_address']=(string)($from['location_address']??'');
    $data['to_country']=(string)($to['location_country']??'');
    $data['to_city']=(string)($to['location_city']??'');
    $data['to_address']=(string)($to['location_address']??'');
    return ['ok'=>true,'data'=>$data];
}

function setup_services_create(mysqli $mysqli, array $input): array
{
    $data=['service_type'=>trim((string)($input['service_type']??'')),'from_locaion'=>trim((string)($input['from_locaion']??'')),'to_locaion'=>trim((string)($input['to_locaion']??'')),'service_name_english'=>trim((string)($input['service_name_english']??'')),'service_name_arabic'=>trim((string)($input['service_name_arabic']??'')),'service_categories'=>trim((string)($input['service_categories']??'')),'vehicle_type'=>trim((string)($input['vehicle_type']??'')),'buying_price'=>trim((string)($input['buying_price']??'')),'selling_price'=>trim((string)($input['selling_price']??'')),'sic_adult_price'=>trim((string)($input['sic_adult_price']??'')),'sic_children_price'=>trim((string)($input['sic_children_price']??''))];
    $errors=setup_services_validate($data); if($errors)return ['ok'=>false,'errors'=>$errors];
    $enriched=setup_services_enrich_locations($mysqli,$data); if(empty($enriched['ok']))return ['ok'=>false,'errors'=>$enriched['errors']??['Invalid location data.']]; $data=$enriched['data'];
    $id=setup_services_next_id($mysqli);
    $stmt=$mysqli->prepare('INSERT INTO service (service_id, service_type, from_country, from_locaion, from_city, from_address, to_country, to_locaion, to_city, to_address, service_name_english, service_name_arabic, buying_price, selling_price, service_categories, vehicle_type, sic_adult_price, sic_children_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    if(!$stmt)return ['ok'=>false,'errors'=>['Failed to prepare insert.']];
    $stmt->bind_param('isssssssssssssssss',$id,$data['service_type'],$data['from_country'],$data['from_locaion'],$data['from_city'],$data['from_address'],$data['to_country'],$data['to_locaion'],$data['to_city'],$data['to_address'],$data['service_name_english'],$data['service_name_arabic'],$data['buying_price'],$data['selling_price'],$data['service_categories'],$data['vehicle_type'],$data['sic_adult_price'],$data['sic_children_price']);
    $ok=$stmt->execute();$stmt->close();return $ok?['ok'=>true,'id'=>$id]:['ok'=>false,'errors'=>['Failed to create service.']];
}

function setup_services_update(mysqli $mysqli, int $id, array $input): array
{
    $data=['service_type'=>trim((string)($input['service_type']??'')),'from_locaion'=>trim((string)($input['from_locaion']??'')),'to_locaion'=>trim((string)($input['to_locaion']??'')),'service_name_english'=>trim((string)($input['service_name_english']??'')),'service_name_arabic'=>trim((string)($input['service_name_arabic']??'')),'service_categories'=>trim((string)($input['service_categories']??'')),'vehicle_type'=>trim((string)($input['vehicle_type']??'')),'buying_price'=>trim((string)($input['buying_price']??'')),'selling_price'=>trim((string)($input['selling_price']??'')),'sic_adult_price'=>trim((string)($input['sic_adult_price']??'')),'sic_children_price'=>trim((string)($input['sic_children_price']??''))];
    $errors=setup_services_validate($data); if($errors)return ['ok'=>false,'errors'=>$errors];
    $enriched=setup_services_enrich_locations($mysqli,$data); if(empty($enriched['ok']))return ['ok'=>false,'errors'=>$enriched['errors']??['Invalid location data.']]; $data=$enriched['data'];
    $stmt=$mysqli->prepare('UPDATE service SET service_type=?, from_country=?, from_locaion=?, from_city=?, from_address=?, to_country=?, to_locaion=?, to_city=?, to_address=?, service_name_english=?, service_name_arabic=?, buying_price=?, selling_price=?, service_categories=?, vehicle_type=?, sic_adult_price=?, sic_children_price=? WHERE service_id=?');
    if(!$stmt)return ['ok'=>false,'errors'=>['Failed to prepare update.']];
    $stmt->bind_param('sssssssssssssssssi',$data['service_type'],$data['from_country'],$data['from_locaion'],$data['from_city'],$data['from_address'],$data['to_country'],$data['to_locaion'],$data['to_city'],$data['to_address'],$data['service_name_english'],$data['service_name_arabic'],$data['buying_price'],$data['selling_price'],$data['service_categories'],$data['vehicle_type'],$data['sic_adult_price'],$data['sic_children_price'],$id);
    $ok=$stmt->execute();$stmt->close();return $ok?['ok'=>true]:['ok'=>false,'errors'=>['Failed to update service.']];
}

function setup_services_delete(mysqli $mysqli, int $id): bool
{
    $stmt=$mysqli->prepare('DELETE FROM service WHERE service_id = ?');
    if(!$stmt)return false; $stmt->bind_param('i',$id); $ok=$stmt->execute(); $stmt->close(); return $ok;
}
