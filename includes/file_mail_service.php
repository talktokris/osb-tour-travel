<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;

/** @return array{Email:string,outgoing_server:string,outgoing_port_no:string,email_password:string,Name:string}|null */
function file_mail_user_smtp(mysqli $mysqli, string $username): ?array
{
    $stmt = $mysqli->prepare(
        'SELECT Email, outgoing_server, outgoing_port_no, email_password, Name FROM user_login WHERE Username = ? LIMIT 1'
    );
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) {
        return null;
    }
    return [
        'Email' => trim((string) ($row['Email'] ?? '')),
        'outgoing_server' => trim((string) ($row['outgoing_server'] ?? '')),
        'outgoing_port_no' => trim((string) ($row['outgoing_port_no'] ?? '')),
        'email_password' => (string) ($row['email_password'] ?? ''),
        'Name' => trim((string) ($row['Name'] ?? '')),
    ];
}

function file_mail_supplier_email(mysqli $mysqli, string $supplierName): string
{
    if ($supplierName === '') {
        return '';
    }
    $stmt = $mysqli->prepare(
        'SELECT supplier_email FROM supplier WHERE supplier_name = ? ORDER BY supplier_id DESC LIMIT 1'
    );
    if (!$stmt) {
        return '';
    }
    $stmt->bind_param('s', $supplierName);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return trim((string) ($row['supplier_email'] ?? ''));
}

/** @return list<array<string, mixed>> */
function file_mail_rows_for_supplier(mysqli $mysqli, string $fileCountNo, string $supplierName): array
{
    $stmt = $mysqli->prepare(
        'SELECT * FROM file_entry WHERE file_count_no = ? AND supplier_name = ? ORDER BY file_id ASC'
    );
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param('ss', $fileCountNo, $supplierName);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $rows[] = $r;
    }
    $stmt->close();
    return $rows;
}

/** @return list<string> */
function file_mail_distinct_suppliers(mysqli $mysqli, string $fileCountNo): array
{
    $stmt = $mysqli->prepare(
        'SELECT DISTINCT supplier_name FROM file_entry WHERE file_count_no = ? AND supplier_name <> \'\' ORDER BY supplier_name'
    );
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param('s', $fileCountNo);
    $stmt->execute();
    $res = $stmt->get_result();
    $out = [];
    while ($row = $res->fetch_assoc()) {
        $out[] = (string) $row['supplier_name'];
    }
    $stmt->close();
    return $out;
}

function file_mail_build_html(mysqli $mysqli, string $fileCountNo, string $supplierName, string $userDisplay): string
{
    $rows = file_mail_rows_for_supplier($mysqli, $fileCountNo, $supplierName);
    if ($rows === []) {
        return '<p>No rows for supplier.</p>';
    }
    $head = $rows[0];
    $name = h(trim((string) $head['last_name'] . ' ' . (string) $head['first_name']));
    $fileNo = h((string) ($head['file_no'] ?? ''));
    $paxMobile = h((string) ($head['pax_mobile'] ?? ''));
    $sup = h($supplierName);

    $blocks = '<table cellpadding="5" cellspacing="0" width="700" style="font-family:Arial;border:1px solid #000">';
    $blocks .= '<tr><td colspan="2" align="center"><h2>Transfer Booking Request</h2></td></tr>';
    $blocks .= '<tr><td><strong>Pax Name :</strong></td><td>' . $name . '</td></tr>';
    $blocks .= '<tr><td><strong>Supplier Name:</strong></td><td>' . $sup . '</td></tr>';
    $blocks .= '<tr><td><strong>File No :</strong></td><td>' . $fileNo . '</td></tr>';
    $blocks .= '<tr><td><strong>Pax Mobile Ref:</strong></td><td>' . $paxMobile . '</td></tr></table>';

    $num = 1;
    foreach ($rows as $row) {
        $svc = h((string) ($row['service'] ?? ''));
        $fromZ = (string) ($row['from_zone'] ?? '');
        $fromL = (string) ($row['from_location'] ?? '');
        $pick = $fromZ !== '' ? $fromZ : $fromL;
        $toZ = (string) ($row['to_zone'] ?? '');
        $toL = (string) ($row['to_location'] ?? '');
        $drop = $toZ !== '' ? $toZ : $toL;
        $sd = h((string) ($row['service_date'] ?? ''));
        $vt = h((string) ($row['vehicle_type'] ?? ''));
        $np = h((string) ($row['no_of_pax'] ?? ''));
        $fn = h((string) ($row['flight_no'] ?? ''));
        $ft = (string) ($row['flight_time'] ?? '');
        $ftp = $ft !== '' ? h(substr($ft, 0, 5)) : '';
        $rm = h((string) ($row['remarks'] ?? ''));

        $blocks .= '<br/><br/><table cellpadding="0" cellspacing="0" width="700" style="font-family:Arial;border:1px solid #000">';
        $blocks .= '<tr><td colspan="4"><h2>Service ' . $num . '</h2></td></tr>';
        $blocks .= '<tr><td><strong>Service Name :</strong></td><td colspan="3">' . $svc . '</td></tr>';
        $blocks .= '<tr><td><strong>Pick up from:</strong></td><td>' . h($pick) . '</td>';
        $blocks .= '<td><strong>Drop off :</strong></td><td>' . h($drop) . '</td></tr>';
        $blocks .= '<tr><td><strong>Service Date :</strong></td><td colspan="3">' . $sd . '</td></tr>';
        $blocks .= '<tr><td><strong>Vehicle Type:</strong></td><td>' . $vt . '</td>';
        $blocks .= '<td><strong>No of Pax:</strong></td><td>' . $np . '</td></tr>';
        $blocks .= '<tr><td><strong>Flight No:</strong></td><td>' . $fn . '</td>';
        $blocks .= '<td><strong>Flight Time:</strong></td><td>' . $ftp . '</td></tr>';
        $blocks .= '<tr><td><strong>Remark:</strong></td><td colspan="4">' . $rm . '</td></tr></table>';
        $num++;
    }

    $sig = h($userDisplay);
    $wrap = '<p>Dear Supplier,</p>';
    $wrap .= '<p>Please book and confirm the Services listed below and acknowledge the confirmation A.S.A.P.</p>';
    $wrap .= $blocks;
    $wrap .= '<p>Many Thanks &amp; Best Regards</p><p><strong>' . $sig . '</strong></p>';
    $wrap .= '<p><strong>Within Earth Holidays Sdn Bhd</strong><br/>Tel : 603 21663969, Fax 603 2166 0418</p>';
    return $wrap;
}

/**
 * @return array{ok:bool, messages:list<string>, supplier_ok:int, supplier_total:int, user_ok:int, user_total:int}
 */
function file_mail_send_all(mysqli $mysqli, string $fileCountNo, string $username): array
{
    $autoload = dirname(__DIR__) . '/vendor/autoload.php';
    if (!is_readable($autoload)) {
        return ['ok' => false, 'messages' => ['Composer autoload missing. Run composer install.'], 'supplier_ok' => 0, 'supplier_total' => 0, 'user_ok' => 0, 'user_total' => 0];
    }
    require_once $autoload;

    $cfg = file_mail_user_smtp($mysqli, $username);
    if ($cfg === null || $cfg['Email'] === '' || $cfg['outgoing_server'] === '') {
        return ['ok' => false, 'messages' => ['User email / SMTP not configured in user_login.'], 'supplier_ok' => 0, 'supplier_total' => 0, 'user_ok' => 0, 'user_total' => 0];
    }

    $suppliers = file_mail_distinct_suppliers($mysqli, $fileCountNo);
    $messages = [];
    $sOk = 0;
    $uOk = 0;
    $userDisplay = $cfg['Name'] !== '' ? $cfg['Name'] : $username;

    foreach ($suppliers as $sup) {
        $toSup = file_mail_supplier_email($mysqli, $sup);
        $html = file_mail_build_html($mysqli, $fileCountNo, $sup, $userDisplay);
        $head = file_mail_rows_for_supplier($mysqli, $fileCountNo, $sup)[0] ?? [];
        $fn = (string) ($head['first_name'] ?? '');
        $ln = (string) ($head['last_name'] ?? '');
        $fno = (string) ($head['file_no'] ?? '');
        $sub = trim($fn . ' ' . $ln . '-' . $fno);

        if ($toSup === '') {
            $messages[] = 'No email for supplier: ' . $sup;
            continue;
        }

        $send = static function (string $addr) use ($cfg, $html, $sub): bool {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->SMTPDebug = 0;
                $mail->Host = $cfg['outgoing_server'];
                $mail->SMTPAuth = true;
                $mail->Port = (int) $cfg['outgoing_port_no'] ?: 587;
                $mail->Username = $cfg['Email'];
                $mail->Password = $cfg['email_password'];
                $mail->CharSet = 'UTF-8';
                $mail->setFrom($cfg['Email'], 'Within Earth');
                $mail->addAddress($addr);
                $mail->Subject = $sub !== '' ? $sub : 'Transfer booking request';
                $mail->isHTML(true);
                $mail->Body = $html;
                $mail->AltBody = strip_tags($html);
                $mail->send();
                return true;
            } catch (\Throwable $e) {
                return false;
            }
        };

        if ($send($toSup)) {
            $sOk++;
            $messages[] = 'Supplier email sent: ' . $toSup;
        } else {
            $messages[] = 'Supplier send failed: ' . $toSup;
        }

        if ($send($cfg['Email'])) {
            $uOk++;
            $messages[] = 'User copy sent: ' . $cfg['Email'];
        } else {
            $messages[] = 'User copy failed: ' . $cfg['Email'];
        }
    }

    $total = count($suppliers);
    $ok = $total > 0 && $sOk === $total && $uOk === $total;
    return [
        'ok' => $ok,
        'messages' => $messages,
        'supplier_ok' => $sOk,
        'supplier_total' => $total,
        'user_ok' => $uOk,
        'user_total' => $total,
    ];
}
