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

                    <form method="post" action="index.php?page=users_password_form&id=<?= $userId ?>" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <input type="hidden" name="_token" value="<?= h($csrf) ?>">
                        <input type="hidden" name="id" value="<?= $userId ?>">
                        <label class="form-control"><span class="label-text">Full Name</span><input value="<?= h((string) $user['Name']) ?>" class="input input-bordered bg-base-200" readonly></label>
                        <label class="form-control"><span class="label-text">User Name</span><input value="<?= h((string) $user['Username']) ?>" class="input input-bordered bg-base-200" readonly></label>
                        <label class="form-control"><span class="label-text">Password</span><input type="password" name="new_password" class="input input-bordered"></label>
                        <label class="form-control"><span class="label-text">Confirm Password</span><input type="password" name="con_password" class="input input-bordered"></label>
                        <div class="md:col-span-2 flex justify-end"><button class="btn btn-primary" type="submit">Change Password</button></div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

