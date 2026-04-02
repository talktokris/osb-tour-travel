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
                    <?php $usersAction = 'list'; require __DIR__ . '/menu-top.php'; ?>

                    <?php if (!empty($flash)): ?>
                        <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-error' ?>">
                            <span><?= h((string) $flash['message']) ?></span>
                        </div>
                    <?php endif; ?>

                    <h3 class="card-title text-lg text-success">View User Information</h3>
                    <div class="overflow-x-auto rounded-box border border-base-300">
                        <table class="table">
                            <tbody>
                                <tr><th>Name</th><td><?= h((string) $user['Name']) ?></td></tr>
                                <tr><th>Username</th><td><?= h((string) $user['Username']) ?></td></tr>
                                <tr><th>Contact No.</th><td><?= h((string) $user['contact_nomber']) ?></td></tr>
                                <tr><th>IC/Passport No.</th><td><?= h((string) $user['ic_passport']) ?></td></tr>
                                <tr><th>Email Address</th><td><?= h((string) $user['Email']) ?></td></tr>
                                <tr><th>Date of Birth</th><td><?= h((string) $user['date_birth']) ?></td></tr>
                                <tr><th>Active Status</th><td><?= h((string) $user['Status']) ?></td></tr>
                                <tr><th>Gender</th><td><?= h((string) $user['gender']) ?></td></tr>
                                <tr><th>Role</th><td><?= h((string) $user['Role']) ?></td></tr>
                                <tr><th>Title</th><td><?= h((string) $user['position']) ?></td></tr>
                                <tr><th>Department</th><td><?= h((string) $user['department']) ?></td></tr>
                                <tr><th>Outgoing Server</th><td><?= h((string) $user['outgoing_server']) ?></td></tr>
                                <tr><th>Port No.</th><td><?= h((string) $user['outgoing_port_no']) ?></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

