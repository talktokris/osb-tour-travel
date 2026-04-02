<?php
require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';
require __DIR__ . '/../../includes/users_service.php';

$currentPage = $_GET['page'] ?? 'users';
$actor = users_actor($mysqli);
users_require_access($actor);

$flash = users_flash_get();
$userId = (int) ($_GET['id'] ?? 0);
if ($userId <= 0 && !empty($_GET['entry']) && $_GET['entry'] === 'new') {
    $rows = users_list($mysqli, $actor);
    if (!empty($rows)) {
        $last = end($rows);
        $userId = (int) ($last['Userid'] ?? 0);
    }
}

$user = $userId > 0 ? users_find($mysqli, $userId, $actor) : null;
if (!$user) {
    users_flash_set('error', 'User not found.');
    header('Location: index.php?page=users');
    exit;
}
?>

<div class="flex gap-6 w-full">
    <aside class="hidden lg:block w-72 shrink-0">
        <?php require __DIR__ . '/sidebar.php'; ?>
    </aside>

    <main class="flex-1 px-4">
        <div class="space-y-4">
            <?php $breadcrumbCurrent = 'Users / View'; require __DIR__ . '/../../includes/breadcrumb.php'; ?>
            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body space-y-4">
                    <?php if (!empty($flash)): ?>
                        <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-error' ?>">
                            <span><?= h((string) $flash['message']) ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="max-w-4xl mx-auto border border-base-300 rounded-box overflow-hidden">
                        <div class="px-4 py-2.5 bg-linear-to-r from-sky-700 to-cyan-600 text-white font-bold text-base">View User Information</div>
                        <div class="divide-y divide-base-300">
                            <?php
                            $rowClass = 'grid grid-cols-1 md:grid-cols-[190px_1fr] items-center gap-2 px-3 py-1.5';
                            $labelClass = 'font-semibold text-sm text-base-content/80';
                            $valueClass = 'text-sm text-base-content';
                            ?>
                            <div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">Name :</div><div class="<?= $valueClass ?>"><?= h((string) $user['Name']) ?></div></div>
                            <div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">Username :</div><div class="<?= $valueClass ?>"><?= h((string) $user['Username']) ?></div></div>
                            <div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">Contact No. :</div><div class="<?= $valueClass ?>"><?= h((string) $user['contact_nomber']) ?></div></div>
                            <div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">IC/Passport No. :</div><div class="<?= $valueClass ?>"><?= h((string) $user['ic_passport']) ?></div></div>
                            <div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">Email Address :</div><div class="<?= $valueClass ?>"><?= h((string) $user['Email']) ?></div></div>
                            <div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">Date of Birth :</div><div class="<?= $valueClass ?>"><?= h((string) $user['date_birth']) ?></div></div>
                            <div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">Active Status :</div><div class="<?= $valueClass ?>"><?= h((string) $user['Status']) ?></div></div>
                            <div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">Gender :</div><div class="<?= $valueClass ?>"><?= h((string) $user['gender']) ?></div></div>
                            <div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">Role :</div><div class="<?= $valueClass ?>"><?= h((string) $user['Role']) ?></div></div>
                            <div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">Title :</div><div class="<?= $valueClass ?>"><?= h((string) $user['position']) ?></div></div>
                            <div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">Department :</div><div class="<?= $valueClass ?>"><?= h((string) $user['department']) ?></div></div>
                            <div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">Outgoing Server :</div><div class="<?= $valueClass ?>"><?= h((string) $user['outgoing_server']) ?></div></div>
                            <div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">Port No. :</div><div class="<?= $valueClass ?>"><?= h((string) $user['outgoing_port_no']) ?></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

