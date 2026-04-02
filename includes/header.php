<?php
// Shared header for all logged-in pages (daisyUI + Tailwind)
?>
<!DOCTYPE html>
<html lang="en" data-theme="corporate">
<head>
    <meta charset="UTF-8">
    <title>OSB Tour System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/tailwind.css">
</head>
<body class="min-h-screen bg-base-200 flex flex-col">
<?php if (!empty($_SESSION['user_id'])): ?>
    <?php
    $activeAgent = $_COOKIE['agent_cookie'] ?? 'None';
    $loggedUser = $_SESSION['user_name'] ?? 'User';
    $currentPage = $_GET['page'] ?? 'home';
    ?>
    <!-- Fixed header similar to the Flowbite navbar layout -->
    <header class="navbar bg-base-100 text-base-content fixed top-0 left-0 right-0 z-20 border-b border-base-300 shadow-sm pl-10 py-4">
        <div class="w-full grid grid-cols-3 items-center">
            <!-- Logo (very left) -->
            <div class="flex items-center gap-3">
                <img src="images/within_earth.png" alt="OSB" class="h-8 w-auto" />
            </div>

            <!-- Desktop nav (menu centered) -->
            <div class="hidden md:flex items-center justify-center gap-1 flex-wrap">
                <a href="index.php?page=home" class="btn btn-sm rounded-btn <?= $currentPage === 'home' ? 'btn-primary' : 'btn-ghost' ?>">
                    Home
                </a>
                <a href="index.php?page=file" class="btn btn-sm rounded-btn <?= $currentPage === 'file' ? 'btn-primary' : 'btn-ghost' ?>">
                    File / Assg
                </a>
                <a href="index.php?page=search" class="btn btn-sm rounded-btn <?= $currentPage === 'search' ? 'btn-primary' : 'btn-ghost' ?>">
                    Search
                </a>
                <a href="index.php?page=report" class="btn btn-sm rounded-btn <?= $currentPage === 'report' ? 'btn-primary' : 'btn-ghost' ?>">
                    Report
                </a>
                <a href="index.php?page=driver" class="btn btn-sm rounded-btn <?= $currentPage === 'driver' ? 'btn-primary' : 'btn-ghost' ?>">
                    Driver
                </a>
                <a href="index.php?page=invoice" class="btn btn-sm rounded-btn <?= $currentPage === 'invoice' ? 'btn-primary' : 'btn-ghost' ?>">
                    Invoice
                </a>
                <a href="index.php?page=sms" class="btn btn-sm rounded-btn <?= $currentPage === 'sms' ? 'btn-primary' : 'btn-ghost' ?>">
                    SMS
                </a>
                <a href="index.php?page=setup" class="btn btn-sm rounded-btn <?= $currentPage === 'setup' ? 'btn-primary' : 'btn-ghost' ?>">
                    Setup
                </a>
                <a href="index.php?page=users" class="btn btn-sm rounded-btn <?= $currentPage === 'users' ? 'btn-primary' : 'btn-ghost' ?>">
                    Users
                </a>
                <a href="index.php?page=logout" class="btn btn-outline btn-primary btn-sm rounded-btn">
                    Logout
                </a>
            </div>

            <!-- Agent + user info on the right -->
            <div class="hidden md:flex items-center gap-8 text-sm justify-end">
                <div class="text-green-600 font-semibold">
                    Active Agent : <span class="text-green-700 font-bold"><?= h((string)$activeAgent) ?></span>
                </div>
                <div class="text-base-content/70">
                    Logged as : <span class="text-base-content font-semibold"><?= h((string)$loggedUser) ?></span>
                </div>
            </div>

            <!-- Mobile menu -->
            <div class="md:hidden flex items-center gap-2 justify-end col-span-3">
                <div class="dropdown dropdown-end">
                    <label tabindex="0" class="btn btn-ghost btn-square">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </label>
                    <ul tabindex="0" class="menu dropdown-content bg-base-100 text-base-content rounded-box mt-3 w-56 p-2 shadow border border-base-300">
                        <li class="p-2">
                            <div class="text-green-600 text-sm font-semibold">
                                Active Agent : <span class="text-green-700 font-bold"><?= h((string)$activeAgent) ?></span>
                            </div>
                            <div class="text-base-content/70 text-sm mt-1">
                                Logged as : <span class="font-semibold"><?= h((string)$loggedUser) ?></span>
                            </div>
                        </li>
                        <li><a href="index.php?page=home">Home</a></li>
                        <li><a href="index.php?page=file">File / Assg</a></li>
                        <li><a href="index.php?page=search">Search</a></li>
                        <li><a href="index.php?page=report">Report</a></li>
                        <li><a href="index.php?page=driver">Driver</a></li>
                        <li><a href="index.php?page=invoice">Invoice</a></li>
                        <li><a href="index.php?page=sms">SMS</a></li>
                        <li><a href="index.php?page=setup">Setup</a></li>
                        <li><a href="index.php?page=users">Users</a></li>
                        <li class="mt-1">
                            <a href="index.php?page=logout" class="btn btn-outline btn-primary btn-block">Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </header>
<?php endif; ?>
<!-- Offset for fixed header -->
<div class="w-full px-0 py-4 flex-1 pt-24">
