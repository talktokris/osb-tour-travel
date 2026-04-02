<?php
require __DIR__ . '/config.php';

// Simple routing based on ?page=
$page = $_GET['page'] ?? 'login';

if ($page === 'logout') {
    session_destroy();
    header('Location: index.php?page=login');
    exit;
}

// Handle login POST
if ($page === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } else {
        // Match legacy app: check user_login table with MD5 password
        $stmt = $mysqli->prepare('SELECT Userid AS user_id, Username AS user_name, password, Status FROM user_login WHERE Username = ? LIMIT 1');
        if (!$stmt) {
            $error = 'Login is temporarily unavailable. Please try again later.';
        } else {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            $hashedInput = md5($password);

            if ($user && strcasecmp($user['Status'], 'Active') === 0 && hash_equals($user['password'], $hashedInput)) {
                $_SESSION['user_id'] = (int) $user['user_id'];
                $_SESSION['user_name'] = $user['user_name'];
                header('Location: index.php?page=home');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        }
    }
}

// Require login for all pages except login
if ($page !== 'login' && empty($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

// Dispatch to page file
if ($page === 'login') {
    require __DIR__ . '/pages/login.php';
} elseif ($page === 'home') {
    require __DIR__ . '/pages/home.php';
} elseif ($page === 'agents') {
    require __DIR__ . '/pages/agents/list.php';
} elseif ($page === 'services') {
    require __DIR__ . '/pages/services.php';
} elseif ($page === 'bookings') {
    require __DIR__ . '/pages/bookings.php';
} else {
    require __DIR__ . '/pages/home.php';
}

