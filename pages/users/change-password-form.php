<?php
require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';
require __DIR__ . '/../../includes/users_service.php';

$currentPage = $_GET['page'] ?? 'users';
$actor = users_actor($mysqli);
users_require_access($actor);

$userId = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$user = $userId > 0 ? users_find($mysqli, $userId, $actor) : null;
if (!$user) {
    users_flash_set('error', 'User not found.');
    header('Location: index.php?page=users_password_list');
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string) ($_POST['_token'] ?? '');
    $pass = (string) ($_POST['new_password'] ?? '');
    $confirm = (string) ($_POST['con_password'] ?? '');

    if (!users_csrf_validate($token)) {
        $errors[] = 'Invalid request token.';
    } else {
        $result = users_update_password($mysqli, $userId, $pass, $confirm);
        if (!empty($result['ok'])) {
            users_flash_set('success', 'Password updated successfully.');
            header('Location: index.php?page=users_view&id=' . $userId);
            exit;
        }
        $errors = $result['errors'] ?? ['Failed to change password.'];
    }
}

$csrf = users_csrf_token();
?>

<div class="flex gap-6 w-full">
    <aside class="hidden lg:block w-72 shrink-0">
        <?php require __DIR__ . '/sidebar.php'; ?>
    </aside>

    <main class="flex-1 px-4">
        <div class="space-y-4">
            <?php $breadcrumbCurrent = 'Users / Change Password'; require __DIR__ . '/../../includes/breadcrumb.php'; ?>
            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body space-y-4">
                    <?php if ($errors): ?>
                        <div class="alert alert-error"><span><?= h(implode(' ', $errors)) ?></span></div>
                    <?php endif; ?>

                    <form method="post" action="index.php?page=users_password_form&id=<?= $userId ?>" class="space-y-3">
                        <input type="hidden" name="_token" value="<?= h($csrf) ?>">
                        <input type="hidden" name="id" value="<?= $userId ?>">
                        <div class="max-w-4xl mx-auto border border-base-300 rounded-box overflow-hidden">
                            <div class="px-4 py-2.5 bg-linear-to-r from-sky-700 to-cyan-600 text-white font-bold text-base">Change Password</div>
                            <div class="divide-y divide-base-300">
                                <?php
                                $rowClass = 'grid grid-cols-1 md:grid-cols-[190px_1fr] items-center gap-2 px-3 py-1.5';
                                $labelClass = 'font-semibold text-sm text-base-content/80';
                                $inputClass = 'input input-bordered input-sm text-sm w-full max-w-xl';
                                ?>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Full Name :</label><input value="<?= h((string) $user['Name']) ?>" class="<?= $inputClass ?> bg-base-200" readonly></div>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">User Name :</label><input value="<?= h((string) $user['Username']) ?>" class="<?= $inputClass ?> bg-base-200" readonly></div>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Password :</label><input type="password" name="new_password" class="<?= $inputClass ?>"></div>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Confirm Password :</label><input type="password" name="con_password" class="<?= $inputClass ?>"></div>
                            </div>
                        </div>
                        <div class="flex justify-center"><button class="btn btn-primary" type="submit">Change Password</button></div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

