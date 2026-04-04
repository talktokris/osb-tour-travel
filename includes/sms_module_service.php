<?php

declare(strict_types=1);

/**
 * SMS module: legacy parity (queue + iSMS test/credit + sms_list history).
 */

function sms_module_csrf_token(): string
{
    if (empty($_SESSION['sms_module_csrf'])) {
        $_SESSION['sms_module_csrf'] = bin2hex(random_bytes(16));
    }
    return (string) $_SESSION['sms_module_csrf'];
}

function sms_module_csrf_validate(string $token): bool
{
    $sessionToken = (string) ($_SESSION['sms_module_csrf'] ?? '');
    return $sessionToken !== '' && hash_equals($sessionToken, $token);
}

function sms_module_flash_set(string $type, string $message): void
{
    $_SESSION['sms_module_flash'] = ['type' => $type, 'message' => $message];
}

function sms_module_flash_get(): ?array
{
    if (!isset($_SESSION['sms_module_flash'])) {
        return null;
    }
    $flash = $_SESSION['sms_module_flash'];
    unset($_SESSION['sms_module_flash']);
    return is_array($flash) ? $flash : null;
}

/** Read env var: getenv, $_ENV, or simple .env file in app root */
function sms_module_env(string $key): string
{
    $v = getenv($key);
    if ($v !== false && $v !== '') {
        return $v;
    }
    if (isset($_ENV[$key]) && (string) $_ENV[$key] !== '') {
        return (string) $_ENV[$key];
    }
    static $dotenv = null;
    if ($dotenv === null) {
        $dotenv = [];
        $path = dirname(__DIR__) . '/.env';
        if (is_readable($path)) {
            foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#')) {
                    continue;
                }
                if (!str_contains($line, '=')) {
                    continue;
                }
                [$k, $val] = explode('=', $line, 2);
                $k = trim($k);
                $val = trim($val, " \t\"'");
                $dotenv[$k] = $val;
            }
        }
    }
    return (string) ($dotenv[$key] ?? '');
}

/**
 * @return array{username:string,password:string,sender_id:string}|null
 */
function sms_module_isms_config(): ?array
{
    $username = sms_module_env('ISMS_USERNAME');
    $password = sms_module_env('ISMS_PASSWORD');
    $senderId = sms_module_env('ISMS_SENDER_ID');
    if ($username === '' || $password === '') {
        return null;
    }
    return [
        'username' => $username,
        'password' => $password,
        'sender_id' => $senderId !== '' ? $senderId : '66300',
    ];
}

/** Accept `Y-m-d` (HTML date) or legacy `d-m-Y`. */
function sms_module_parse_service_date_input(string $raw): ?string
{
    $raw = trim($raw);
    if ($raw === '') {
        return null;
    }
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $raw, $m)) {
        $y = (int) $m[1];
        $mo = (int) $m[2];
        $d = (int) $m[3];
        if (checkdate($mo, $d, $y)) {
            return sprintf('%04d-%02d-%02d', $y, $mo, $d);
        }
        return null;
    }
    return sms_module_parse_from_date_dd_mm_yyyy($raw);
}

/** Legacy: dd-mm-yyyy → Y-m-d */
function sms_module_parse_from_date_dd_mm_yyyy(string $raw): ?string
{
    $raw = trim($raw);
    if ($raw === '') {
        return null;
    }
    $parts = explode('-', $raw);
    if (count($parts) !== 3) {
        return null;
    }
    $d = (int) $parts[0];
    $m = (int) $parts[1];
    $y = (int) $parts[2];
    if ($d < 1 || $d > 31 || $m < 1 || $m > 12 || $y < 1990 || $y > 2100) {
        return null;
    }
    if (!checkdate($m, $d, $y)) {
        return null;
    }
    return sprintf('%04d-%02d-%02d', $y, $m, $d);
}

function sms_module_curl_get(string $url, int $timeoutSec = 20): string
{
    if (!function_exists('curl_init')) {
        return 'cURL is not available.';
    }
    $ch = curl_init($url);
    if ($ch === false) {
        return '';
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeoutSec);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    $out = curl_exec($ch);
    curl_close($ch);
    return is_string($out) ? $out : '';
}

function sms_module_fetch_balance(): string
{
    $cfg = sms_module_isms_config();
    if ($cfg === null) {
        return '';
    }
    $u = rawurlencode($cfg['username']);
    $p = rawurlencode($cfg['password']);
    $url = "https://isms.com.my/isms_balance.php?un={$u}&pwd={$p}";
    $try = sms_module_curl_get($url);
    if ($try !== '') {
        return $try;
    }
    $urlHttp = "http://isms.com.my/isms_balance.php?un={$u}&pwd={$p}";
    return sms_module_curl_get($urlHttp);
}

/**
 * @return array{ok:bool, raw:string, error?:string}
 */
function sms_module_send_test_sms(string $destinationRaw, string $messageRaw, int $type): array
{
    $cfg = sms_module_isms_config();
    if ($cfg === null) {
        return ['ok' => false, 'raw' => '', 'error' => 'iSMS credentials are not configured (set ISMS_USERNAME and ISMS_PASSWORD in .env).'];
    }
    $dest = preg_replace('/\D+/', '', $destinationRaw) ?? '';
    if (strlen($dest) < 10) {
        return ['ok' => false, 'raw' => '', 'error' => 'Phone number incorrect.'];
    }
    if (trim($messageRaw) === '') {
        return ['ok' => false, 'raw' => '', 'error' => 'Message empty.'];
    }
    if ($type !== 1 && $type !== 2) {
        $type = 1;
    }
    $dstno = rawurlencode($dest);
    $msg = rawurlencode($messageRaw);
    $un = rawurlencode($cfg['username']);
    $pwd = rawurlencode($cfg['password']);
    $sendid = rawurlencode($cfg['sender_id']);
    $url = "https://www.isms.com.my/isms_send.php?un={$un}&pwd={$pwd}&dstno={$dstno}&msg={$msg}&type={$type}&sendid={$sendid}";
    $raw = sms_module_curl_get($url);
    return ['ok' => true, 'raw' => $raw];
}

/**
 * @return array{arebic:?array<string,string>,time:?array<string,string>,header:string,footer:string}
 */
function sms_module_load_templates(mysqli $mysqli): array
{
    $arebic = null;
    $stmt = $mysqli->prepare('SELECT * FROM arebic_lebels WHERE arebic_lebels_id = 1 LIMIT 1');
    if ($stmt) {
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        $arebic = is_array($row) ? $row : null;
    }
    $time = null;
    $stmt = $mysqli->prepare('SELECT * FROM time_format WHERE time_format_id = 1 LIMIT 1');
    if ($stmt) {
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        $time = is_array($row) ? $row : null;
    }
    $header = '';
    $footer = '';
    $r = $mysqli->query('SELECT sms_label_header, sms_label_footer FROM sms_label ORDER BY sms_label_id DESC LIMIT 1');
    if ($r) {
        $lr = $r->fetch_assoc();
        if (is_array($lr)) {
            $header = (string) ($lr['sms_label_header'] ?? '');
            $footer = (string) ($lr['sms_label_footer'] ?? '');
        }
    }
    return [
        'arebic' => $arebic,
        'time' => $time,
        'header' => $header,
        'footer' => $footer,
    ];
}

/**
 * @param array<string,mixed> $templates from sms_module_load_templates
 * @param array<string,mixed> $row file_entry row
 * @return array{name:string,mobile:string,message_preview:string,message_queue:string,service_date_sql:string}
 */
function sms_module_build_row_messages(mysqli $mysqli, array $templates, array $row): array
{
    $arebic = $templates['arebic'] ?? null;
    $time = $templates['time'] ?? null;
    $smsHeader = (string) ($templates['header'] ?? '');
    $smsFooter = (string) ($templates['footer'] ?? '');

    $Pick_Up_Point_fills = '';
    $Drop_Off_Point_fills = '';
    if (is_array($arebic)) {
        $Pick_Up_Point_fills = (string) ($arebic['Pick_Up_Point_fills'] ?? '');
        $Drop_Off_Point_fills = (string) ($arebic['Drop_Off_Point_fills'] ?? '');
    }

    $aa_fills = (string) ($time['aa_fills'] ?? '');
    $bb_fills = (string) ($time['bb_fills'] ?? '');
    $cc_fills = (string) ($time['cc_fills'] ?? '');
    $dd_fills = (string) ($time['dd_fills'] ?? '');
    $ee_fills = (string) ($time['ee_fills'] ?? '');

    $first = trim((string) ($row['first_name'] ?? ''));
    $last = trim((string) ($row['last_name'] ?? ''));
    $name = trim($last . ' ' . $first);
    $mobile = trim((string) ($row['pax_mobile'] ?? ''));

    $pickupSql = (string) ($row['pickup_time'] ?? '');
    $from_locationloop = (string) ($row['from_location'] ?? '');
    $to_locationloop = (string) ($row['to_location'] ?? '');
    $from_zoneloop = (string) ($row['from_zone'] ?? '');
    $to_zoneloop = (string) ($row['to_zone'] ?? '');
    $serviceloopEng = (string) ($row['service'] ?? '');
    $service_dateloopSQL = (string) ($row['service_date'] ?? '');

    $service_dateloop = $service_dateloopSQL !== '' ? date('d-M-Y', strtotime($service_dateloopSQL)) : '';

    $dateCon = strtotime($pickupSql);
    $arabic_timeloop = '';
    if ($dateCon !== false) {
        $date1from = strtotime('01:00:00');
        $date1to = strtotime('05:59:59');
        $date2from = strtotime('06:00:00');
        $date2to = strtotime('11:59:59');
        $date3from = strtotime('12:00:00');
        $date3to = strtotime('14:59:59');
        $date4from = strtotime('15:00:00');
        $date4to = strtotime('17:59:59');
        $date5from = strtotime('18:00:00');
        $date5to = strtotime('23:59:59');
        if ($dateCon >= $date1from && $dateCon <= $date1to) {
            $arabic_timeloop = $aa_fills;
        } elseif ($dateCon >= $date2from && $dateCon <= $date2to) {
            $arabic_timeloop = $bb_fills;
        } elseif ($dateCon >= $date3from && $dateCon <= $date3to) {
            $arabic_timeloop = $cc_fills;
        } elseif ($dateCon >= $date4from && $dateCon <= $date4to) {
            $arabic_timeloop = $dd_fills;
        } elseif ($dateCon >= $date5from && $dateCon <= $date5to) {
            $arabic_timeloop = $ee_fills;
        }
    }

    $service_name_arabic = '';
    $from_loop = '';
    $to_loop = '';

    if ($serviceloopEng !== '') {
        $sstmt = $mysqli->prepare('SELECT service_name_arabic, from_city FROM service WHERE service_name_english = ? ORDER BY service_id DESC LIMIT 1');
        if ($sstmt) {
            $sstmt->bind_param('s', $serviceloopEng);
            $sstmt->execute();
            $sres = $sstmt->get_result();
            $srow = $sres ? $sres->fetch_assoc() : null;
            $sstmt->close();
            if (is_array($srow)) {
                $service_name_arabic = (string) ($srow['service_name_arabic'] ?? '');
                $from_city_service_en = (string) ($srow['from_city'] ?? '');
                if ($from_city_service_en !== '') {
                    $cstmt = $mysqli->prepare('SELECT city_shotform FROM city WHERE city_name = ? ORDER BY city_id DESC LIMIT 1');
                    if ($cstmt) {
                        $cstmt->bind_param('s', $from_city_service_en);
                        $cstmt->execute();
                        $cres = $cstmt->get_result();
                        $crow = $cres ? $cres->fetch_assoc() : null;
                        $cstmt->close();
                        if (is_array($crow)) {
                            $from_loop = (string) ($crow['city_shotform'] ?? '');
                        }
                    }
                }
            }
        }
    }

    $locFromArb = '';
    if ($from_locationloop !== '') {
        $lst = $mysqli->prepare('SELECT location_name_arb FROM location WHERE location_name = ? ORDER BY location_id DESC LIMIT 1');
        if ($lst) {
            $lst->bind_param('s', $from_locationloop);
            $lst->execute();
            $lres = $lst->get_result();
            $lrow = $lres ? $lres->fetch_assoc() : null;
            $lst->close();
            if (is_array($lrow)) {
                $locFromArb = (string) ($lrow['location_name_arb'] ?? '');
            }
        }
    }
    $zoneFromArb = '';
    if ($from_zoneloop !== '') {
        $zst = $mysqli->prepare('SELECT zone_name_arabic FROM zone WHERE zone_name = ? ORDER BY zone_id DESC LIMIT 1');
        if ($zst) {
            $zst->bind_param('s', $from_zoneloop);
            $zst->execute();
            $zres = $zst->get_result();
            $zrow = $zres ? $zres->fetch_assoc() : null;
            $zst->close();
            if (is_array($zrow)) {
                $zoneFromArb = (string) ($zrow['zone_name_arabic'] ?? '');
            }
        }
    }
    $locToArb = '';
    if ($to_locationloop !== '') {
        $lst = $mysqli->prepare('SELECT location_name_arb FROM location WHERE location_name = ? ORDER BY location_id DESC LIMIT 1');
        if ($lst) {
            $lst->bind_param('s', $to_locationloop);
            $lst->execute();
            $lres = $lst->get_result();
            $lrow = $lres ? $lres->fetch_assoc() : null;
            $lst->close();
            if (is_array($lrow)) {
                $locToArb = (string) ($lrow['location_name_arb'] ?? '');
            }
        }
    }
    $zoneToArb = '';
    if ($to_zoneloop !== '') {
        $zst = $mysqli->prepare('SELECT zone_name_arabic FROM zone WHERE zone_name = ? ORDER BY zone_id DESC LIMIT 1');
        if ($zst) {
            $zst->bind_param('s', $to_zoneloop);
            $zst->execute();
            $zres = $zst->get_result();
            $zrow = $zres ? $zres->fetch_assoc() : null;
            $zst->close();
            if (is_array($zrow)) {
                $zoneToArb = (string) ($zrow['zone_name_arabic'] ?? '');
            }
        }
    }

    $from_loop_final = $zoneFromArb !== '' ? $zoneFromArb : $locFromArb;
    $to_loop_final = $zoneToArb !== '' ? $zoneToArb : $locToArb;

    $new_time = $pickupSql !== '' ? date('g:i', strtotime($pickupSql)) : '';

    // Legacy sms.php preview (no SMS header/footer)
    $message_preview = $service_dateloop . ' / ' . $new_time . $arabic_timeloop . '  ' . $service_name_arabic . ' / ' . $from_loop_final . ' : ' . $Pick_Up_Point_fills . ' / ' . $to_loop_final . ': ' . $Drop_Off_Point_fills;

    // Legacy sms_save.php queue body (literal \n in PHP becomes real newlines after decode)
    $queueRaw = $smsHeader . '\n' . $service_dateloop . ' ' . $new_time . ' ' . $arabic_timeloop . '\n ' . $service_name_arabic . '\n ' . $Drop_Off_Point_fills . ': ' . $to_loop_final . ' \n ' . $Pick_Up_Point_fills . ' : ' . $from_loop_final . '\n' . $smsFooter;
    $message_queue = str_replace('\\n', "\n", html_entity_decode($queueRaw, ENT_QUOTES, 'UTF-8'));

    return [
        'name' => $name,
        'mobile' => $mobile,
        'message_preview' => $message_preview,
        'message_queue' => $message_queue,
        'service_date_sql' => $service_dateloopSQL,
    ];
}

/** @return list<array<string,mixed>> */
function sms_module_file_entries_for_service_date(mysqli $mysqli, string $serviceYmd): array
{
    $stmt = $mysqli->prepare('SELECT * FROM file_entry WHERE service_type <> ? AND service_date = ? ORDER BY service_date DESC');
    if (!$stmt) {
        return [];
    }
    $arrival = 'Arrival';
    $stmt->bind_param('ss', $arrival, $serviceYmd);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

/**
 * @return array{queued:int, skipped:int, errors:list<string>}
 */
function sms_module_enqueue_service_date(mysqli $mysqli, string $serviceYmd): array
{
    $templates = sms_module_load_templates($mysqli);
    $rows = sms_module_file_entries_for_service_date($mysqli, $serviceYmd);
    $queued = 0;
    $skipped = 0;
    $errors = [];
    $ins = $mysqli->prepare('INSERT INTO sms_list (sms_no, sms_message, date, status) VALUES (?, ?, ?, ?)');
    if (!$ins) {
        return ['queued' => 0, 'skipped' => 0, 'errors' => ['Prepare failed: ' . $mysqli->error]];
    }
    $statusEmpty = '';
    foreach ($rows as $row) {
        $built = sms_module_build_row_messages($mysqli, $templates, $row);
        $mobile = preg_replace('/\D+/', '', $built['mobile']) ?? '';
        if (strlen($mobile) < 8) {
            ++$skipped;
            continue;
        }
        $svcDate = $built['service_date_sql'];
        if ($svcDate === '') {
            ++$skipped;
            continue;
        }
        $ts = strtotime($svcDate . ' 12:00:00');
        if ($ts === false) {
            ++$skipped;
            continue;
        }
        $nextDay = date('Y-m-d', strtotime('+1 day', $ts));
        $msg = $built['message_queue'];
        $ins->bind_param('ssss', $mobile, $msg, $nextDay, $statusEmpty);
        if ($ins->execute()) {
            ++$queued;
        } else {
            $errors[] = $mysqli->error;
        }
    }
    $ins->close();
    return ['queued' => $queued, 'skipped' => $skipped, 'errors' => $errors];
}

/** @return list<array<string,mixed>> */
function sms_module_history_rows(mysqli $mysqli, ?string $mobileExact): array
{
    if ($mobileExact !== null && trim($mobileExact) !== '') {
        $m = trim($mobileExact);
        $stmt = $mysqli->prepare('SELECT sms_id, sms_no, sms_message, date, status FROM sms_list WHERE sms_no = ? ORDER BY sms_id DESC');
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param('s', $m);
        $stmt->execute();
        $res = $stmt->get_result();
        $out = [];
        while ($row = $res->fetch_assoc()) {
            $out[] = $row;
        }
        $stmt->close();
        return $out;
    }
    $r = $mysqli->query('SELECT sms_id, sms_no, sms_message, date, status FROM sms_list ORDER BY sms_id DESC LIMIT 1000');
    if (!$r) {
        return [];
    }
    $out = [];
    while ($row = $r->fetch_assoc()) {
        $out[] = $row;
    }
    return $out;
}
