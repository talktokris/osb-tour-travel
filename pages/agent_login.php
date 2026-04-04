<?php

declare(strict_types=1);

$loginId = isset($_GET['login_id']) ? trim((string) $_GET['login_id']) : '';

$cookieOpts = [
    'expires' => time() + 86400,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
];

if ($loginId === 'logout') {
    setcookie('agent_cookie', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    header('Location: index.php?page=home');
    exit;
}

if ($loginId === '') {
    header('Location: index.php?page=home');
    exit;
}

setcookie('agent_cookie', $loginId, $cookieOpts);
header('Location: index.php?page=file');
exit;
