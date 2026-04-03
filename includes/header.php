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
    <style>
        input[type="text"],
        input[type="search"],
        input[type="email"],
        input[type="password"],
        textarea {
            font-family: "Noto Naskh Arabic", "Tahoma", "Arial", sans-serif;
        }
        /* Explicit RTL for Arabic-only fields (typing flows right-to-left) */
        .input-arabic,
        textarea.input-arabic {
            direction: rtl;
            text-align: right;
            unicode-bidi: plaintext;
        }
        .module-sidebar {
            width: 18rem;
            min-height: 100%;
            margin-left: 1.5rem;
            border: 1px solid #dbe2ea;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 16px 34px rgba(15, 23, 42, 0.10);
            overflow: hidden;
        }
        .module-sidebar__head {
            padding: 0.85rem 1rem;
            color: #fff;
            font-size: 0.6875rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            background: linear-gradient(90deg, #0c4a8a 0%, #0b77bb 60%, #0a94c8 100%);
            border-bottom: 1px solid rgba(255, 255, 255, 0.25);
        }
        .module-sidebar .menu {
            padding: 0.65rem 0 0.85rem;
            gap: 0.55rem;
            background: transparent;
            width: 100% !important;
            min-width: 100% !important;
            max-width: 100% !important;
            display: flex !important;
            flex-direction: column;
            box-sizing: border-box;
        }
        .module-sidebar .menu li {
            margin: 0;
            width: 100% !important;
            display: flex !important;
            padding: 0 0.55rem;
            box-sizing: border-box;
        }
        .module-sidebar ul.menu > li > a,
        .module-sidebar .menu li > a,
        .module-sidebar .menu :where(li > a) {
            min-height: 2.35rem;
            border-radius: 10px;
            font-size: 0.8125rem;
            font-weight: 500;
            color: #475569;
            display: flex !important;
            align-items: center;
            justify-content: flex-start;
            gap: 0.6rem;
            transition: all 0.18s ease;
            padding: 0.4rem 0.5rem 0.4rem 0.65rem;
            border: 1px solid #dbe6f3;
            background: linear-gradient(180deg, #f8fbff 0%, #edf3fb 100%);
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.8);
            width: 100% !important;
            min-width: 100% !important;
            max-width: 100% !important;
            text-align: left;
            box-sizing: border-box;
            flex: 1 1 auto !important;
        }
        .module-sidebar .menu li > a::before {
            content: "";
            width: 0.875rem;
            height: 0.875rem;
            flex: 0 0 0.875rem;
            border-radius: 999px;
            background-color: #64748b;
            mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M9 5l7 7-7 7' /%3E%3C/svg%3E");
            mask-size: contain;
            mask-repeat: no-repeat;
            mask-position: center;
            transition: all 0.18s ease;
        }
        .module-sidebar .menu li > a::after {
            content: none;
        }
        .module-sidebar .menu li > a:hover {
            background: linear-gradient(180deg, #f0f6ff 0%, #e3eeff 100%);
            border-color: #bad2f8;
            color: #0c4a8a;
            transform: translateX(3px);
            box-shadow: 0 5px 12px rgba(59, 130, 246, 0.18), inset 0 1px 0 rgba(255, 255, 255, 0.9);
        }
        .module-sidebar .menu li > a:hover::before {
            background-color: #0b77bb;
            transform: translateX(1px);
        }
        .module-sidebar .menu li.active > a,
        .module-sidebar .menu li > a.active {
            background: linear-gradient(180deg, #dfeeff 0%, #d3e6ff 100%);
            color: #0c4a8a;
            font-weight: 600;
            border-color: #9ec5fb;
            box-shadow: 0 6px 14px rgba(59, 130, 246, 0.22), inset 0 1px 0 rgba(255, 255, 255, 0.92);
        }
        .module-sidebar .menu li.active > a::before,
        .module-sidebar .menu li > a.active::before {
            background-color: #0c4a8a;
        }
        /* Shared delete confirmation (Setup lists, agents, suppliers, etc.) */
        dialog.agent-delete-dialog {
            margin: 0;
            max-width: none;
            max-height: none;
            width: 100%;
            height: 100%;
            padding: 1.25rem;
            border: none;
            background: transparent;
            display: none;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
        }
        dialog.agent-delete-dialog[open] {
            display: flex;
        }
        dialog.agent-delete-dialog::backdrop {
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(4px);
        }
        dialog.agent-delete-dialog .agent-delete-dialog__surface {
            width: 100%;
            max-width: 26rem;
            background: var(--color-base-100, #ffffff);
            color: var(--color-base-content, #1e293b);
            border-radius: 1rem;
            border: 1px solid color-mix(in oklab, var(--color-base-content, #64748b) 12%, transparent);
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.35);
            padding: 1.5rem 1.5rem 1.25rem;
        }
        dialog.agent-delete-dialog .agent-delete-dialog__message {
            margin: 0 0 1.35rem;
            font-size: 0.875rem;
            line-height: 1.55;
            color: color-mix(in oklab, var(--color-base-content, #64748b) 78%, transparent);
        }
        dialog.agent-delete-dialog .agent-delete-dialog__actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: flex-end;
        }
    </style>
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
                <a href="index.php?page=setup" style="height:34px;padding:0 12px;display:inline-flex;align-items:center;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;<?= in_array($currentPage, ['setup', 'setup_agents', 'setup_agent_create', 'setup_agent_view', 'setup_agent_edit', 'setup_suppliers', 'setup_supplier_create', 'setup_supplier_view', 'setup_supplier_edit', 'setup_vehicles', 'setup_vehicle_create', 'setup_vehicle_view', 'setup_vehicle_edit', 'setup_services', 'setup_service_create', 'setup_service_view', 'setup_service_edit', 'setup_locations', 'setup_location_create', 'setup_location_view', 'setup_location_edit'], true) ? 'background:#ffffff;color:#0c4a8a;box-shadow:0 4px 10px rgba(15,23,42,0.20);' : 'color:#ffffff;' ?>">Setup</a>
                <a href="index.php?page=users" style="height:34px;padding:0 12px;display:inline-flex;align-items:center;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;<?= $currentPage === 'users' ? 'background:#ffffff;color:#0c4a8a;box-shadow:0 4px 10px rgba(15,23,42,0.20);' : 'color:#ffffff;' ?>">Users</a>
                <a href="index.php?page=logout" style="height:34px;padding:0 12px;display:inline-flex;align-items:center;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;color:#ffffff;border:1px solid rgba(255,255,255,0.75);">Logout</a>
            </nav>
        </div>
    </header>
<?php endif; ?>
<!-- Offset for fixed header -->
<div class="w-full px-0 py-4 flex-1 pt-28">
