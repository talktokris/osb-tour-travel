<?php

declare(strict_types=1);

/**
 * Company details for PDF headers (from .env).
 */
function pdf_company_bootstrap_env(): void
{
    static $loaded = false;
    if ($loaded) {
        return;
    }
    $loaded = true;

    $envPath = dirname(__DIR__) . '/.env';
    if (!is_file($envPath) || !is_readable($envPath)) {
        return;
    }

    $envLines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!is_array($envLines)) {
        return;
    }

    foreach ($envLines as $envLine) {
        $envLine = trim((string) $envLine);
        if ($envLine === '' || str_starts_with($envLine, '#') || !str_contains($envLine, '=')) {
            continue;
        }
        [$envKey, $envValue] = explode('=', $envLine, 2);
        $envKey = trim($envKey);
        $envValue = trim($envValue, " \t\n\r\0\x0B\"'");
        if ($envKey !== '' && getenv($envKey) === false) {
            putenv($envKey . '=' . $envValue);
            $_ENV[$envKey] = $envValue;
        }
    }
}

function pdf_company_env(string $key, string $default = ''): string
{
    pdf_company_bootstrap_env();
    $value = getenv($key);
    if ($value === false || $value === null || $value === '') {
        return $default;
    }

    return (string) $value;
}

/** @return array{name: string, address: string, email: string, phone1: string, phone2: string, website: string} */
function pdf_company_config(): array
{
    return [
        'name' => pdf_company_env('COMPANY_NAME', 'OSB Global Services Sdn Bhd'),
        'address' => pdf_company_env(
            'COMPANY_ADDRESS',
            "Suite B-09-04, Block B, Megan Avenue 2\nJalan Yap Kwan Seng, 50450 Kuala Lumpur, Malaysia"
        ),
        'email' => pdf_company_env('COMPANY_EMAIL', 'sales@ossbtrf.com'),
        'phone1' => pdf_company_env('COMPANY_PHONE_1', '+603 2166 3969'),
        'phone2' => pdf_company_env('COMPANY_PHONE_2', '+603 2166 0418'),
        'website' => pdf_company_env('COMPANY_WEBSITE', 'malaysia.onlinewe.net'),
    ];
}

/** @return list<string> */
function pdf_company_address_lines(string $address): array
{
    $address = str_replace(['\\n', "\r\n", "\r"], ["\n", "\n", "\n"], $address);
    $lines = array_values(array_filter(array_map('trim', explode("\n", $address)), static fn (string $l): bool => $l !== ''));

    return $lines !== [] ? $lines : [''];
}

function pdf_company_h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Invoice-style HTML table (legacy TCPDF writeHTML). */
function pdf_company_header_html_invoice(): string
{
    $c = pdf_company_config();
    $rows = '<tr><td><h3>' . pdf_company_h($c['name']) . '</h3></td></tr>';
    foreach (pdf_company_address_lines($c['address']) as $line) {
        $rows .= '<tr><td>' . pdf_company_h($line) . '</td></tr>';
    }
    $rows .= '<tr><td>Tel : ' . pdf_company_h($c['phone1']) . ', Fax : ' . pdf_company_h($c['phone2']) . '</td></tr>';
    $rows .= '<tr><td>E-Mail : ' . pdf_company_h($c['email']) . ' Website : ' . pdf_company_h($c['website']) . '</td></tr>';

    return '<table>' . $rows . '</table>';
}

/** Itinerary-style plain text block (TCPDF MultiCell). */
function pdf_company_header_multiline_itinerary(): string
{
    $c = pdf_company_config();
    $lines = pdf_company_address_lines($c['address']);
    $lines[] = 'Tel: ' . $c['phone1'] . ' | Fax: ' . $c['phone2'];
    $lines[] = 'E-Mail: ' . $c['email'] . ' | Website: ' . $c['website'];

    return implode("\n", $lines);
}
