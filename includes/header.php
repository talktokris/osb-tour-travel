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
    <header style="position:fixed;top:0;left:0;right:0;z-index:20;background:#ffffff;border-bottom:1px solid #d7dee7;box-shadow:0 6px 20px rgba(15,23,42,0.10);">
        <div style="max-width:1400px;margin:0 auto;padding:10px 20px;display:flex;align-items:center;justify-content:space-between;gap:12px;">
            <div style="flex-shrink:0;display:flex;align-items:center;">
                <img src="images/within_earth.png" alt="OSB" style="height:38px;width:auto;display:block;" />
            </div>

            <div style="display:flex;align-items:center;gap:10px;white-space:nowrap;font-size:12px;">
                <div style="padding:4px 10px;border-radius:999px;background:#ecfdf5;color:#047857;font-weight:600;border:1px solid #a7f3d0;">
                    Active Agent : <span class="font-bold"><?= h((string)$activeAgent) ?></span>
                </div>
                <div style="padding:4px 10px;border-radius:999px;background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;">
                    Logged as : <span style="font-weight:600;color:#0f172a;"><?= h((string)$loggedUser) ?></span>
                </div>
            </div>
        </div>

        <div style="background:linear-gradient(90deg,#0c4a8a 0%,#0b77bb 55%,#0a94c8 100%);border-top:1px solid rgba(255,255,255,0.30);box-shadow:inset 0 1px 0 rgba(255,255,255,0.18);">
            <nav style="max-width:1400px;margin:0 auto;padding:8px 16px;display:flex;align-items:center;justify-content:center;gap:4px;white-space:nowrap;overflow-x:auto;">
                <a href="index.php?page=home" style="height:34px;padding:0 12px;display:inline-flex;align-items:center;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;<?= $currentPage === 'home' ? 'background:#ffffff;color:#0c4a8a;box-shadow:0 4px 10px rgba(15,23,42,0.20);' : 'color:#ffffff;' ?>">Home</a>
                <a href="index.php?page=file" style="height:34px;padding:0 12px;display:inline-flex;align-items:center;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;<?= $currentPage === 'file' ? 'background:#ffffff;color:#0c4a8a;box-shadow:0 4px 10px rgba(15,23,42,0.20);' : 'color:#ffffff;' ?>">File / Assg</a>
                <a href="index.php?page=search" style="height:34px;padding:0 12px;display:inline-flex;align-items:center;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;<?= $currentPage === 'search' ? 'background:#ffffff;color:#0c4a8a;box-shadow:0 4px 10px rgba(15,23,42,0.20);' : 'color:#ffffff;' ?>">Search</a>
                <a href="index.php?page=report" style="height:34px;padding:0 12px;display:inline-flex;align-items:center;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;<?= $currentPage === 'report' ? 'background:#ffffff;color:#0c4a8a;box-shadow:0 4px 10px rgba(15,23,42,0.20);' : 'color:#ffffff;' ?>">Report</a>
                <a href="index.php?page=driver" style="height:34px;padding:0 12px;display:inline-flex;align-items:center;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;<?= $currentPage === 'driver' ? 'background:#ffffff;color:#0c4a8a;box-shadow:0 4px 10px rgba(15,23,42,0.20);' : 'color:#ffffff;' ?>">Driver</a>
                <a href="index.php?page=invoice" style="height:34px;padding:0 12px;display:inline-flex;align-items:center;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;<?= $currentPage === 'invoice' ? 'background:#ffffff;color:#0c4a8a;box-shadow:0 4px 10px rgba(15,23,42,0.20);' : 'color:#ffffff;' ?>">Invoice</a>
                <a href="index.php?page=sms" style="height:34px;padding:0 12px;display:inline-flex;align-items:center;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;<?= $currentPage === 'sms' ? 'background:#ffffff;color:#0c4a8a;box-shadow:0 4px 10px rgba(15,23,42,0.20);' : 'color:#ffffff;' ?>">SMS</a>
                <a href="index.php?page=setup" style="height:34px;padding:0 12px;display:inline-flex;align-items:center;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;<?= $currentPage === 'setup' ? 'background:#ffffff;color:#0c4a8a;box-shadow:0 4px 10px rgba(15,23,42,0.20);' : 'color:#ffffff;' ?>">Setup</a>
                <a href="index.php?page=users" style="height:34px;padding:0 12px;display:inline-flex;align-items:center;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;<?= $currentPage === 'users' ? 'background:#ffffff;color:#0c4a8a;box-shadow:0 4px 10px rgba(15,23,42,0.20);' : 'color:#ffffff;' ?>">Users</a>
                <a href="index.php?page=logout" style="height:34px;padding:0 12px;display:inline-flex;align-items:center;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;color:#ffffff;border:1px solid rgba(255,255,255,0.75);">Logout</a>
            </nav>
        </div>
    </header>
<?php endif; ?>
<!-- Offset for fixed header -->
<div class="w-full px-0 py-4 flex-1 pt-28">
