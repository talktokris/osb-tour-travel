<?php
if (!isset($mysqli)) {
    require __DIR__ . '/../../config.php';
}
require_once __DIR__ . '/../../includes/users_service.php';

$currentPage = $_GET['page'] ?? 'users';
$actor = users_actor($mysqli);
users_require_access($actor);

$userId = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$user = $userId > 0 ? users_find($mysqli, $userId, $actor) : null;
if (!$user) {
    users_flash_set('error', 'User not found.');
    header('Location: index.php?page=users_role_list');
    exit;
}

$errors = [];
$selectedRole = (string) ($user['Role'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string) ($_POST['_token'] ?? '');
    $selectedRole = trim((string) ($_POST['Role'] ?? ''));
    if (!users_csrf_validate($token)) {
        $errors[] = 'Invalid request token.';
    } elseif ($selectedRole === '') {
        $errors[] = 'Please select the user role.';
    } elseif (users_update_role($mysqli, $userId, $selectedRole)) {
        users_flash_set('success', 'Role updated successfully.');
        header('Location: index.php?page=users_view&id=' . $userId);
        exit;
    } else {
        $errors[] = 'Failed to update role.';
    }
}

$roleOptions = users_lookup_values($mysqli, 'roles', 'role_name');
$csrf = users_csrf_token();

require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';
?>

<div class="flex gap-6 w-full">
    <aside class="hidden lg:block w-72 shrink-0">
        <?php require __DIR__ . '/sidebar.php'; ?>
    </aside>

    <main class="flex-1 px-4">
        <div class="space-y-4">
            <?php $breadcrumbCurrent = 'Users / Change Role'; require __DIR__ . '/../../includes/breadcrumb.php'; ?>
            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body space-y-4">
                    <?php if ($errors): ?>
                        <div class="alert alert-error"><span><?= h(implode(' ', $errors)) ?></span></div>
                    <?php endif; ?>

                    <form method="post" action="index.php?page=users_role_form&id=<?= $userId ?>" class="space-y-3">
                        <input type="hidden" name="_token" value="<?= h($csrf) ?>">
                        <input type="hidden" name="id" value="<?= $userId ?>">
                        <div class="max-w-4xl mx-auto border border-base-300 rounded-box overflow-hidden">
                            <div class="px-4 py-2.5 bg-linear-to-r from-sky-700 to-cyan-600 text-white font-bold text-base">Change Role</div>
                            <div class="divide-y divide-base-300">
                                <?php
                                $rowClass = 'grid grid-cols-1 md:grid-cols-[190px_1fr] items-center gap-2 px-3 py-1.5';
                                $labelClass = 'font-semibold text-sm text-base-content/80';
                                $inputClass = 'input input-bordered input-sm text-sm w-full max-w-xl';
                                ?>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Full Name :</label><input value="<?= h((string) $user['Name']) ?>" class="<?= $inputClass ?> bg-base-200" readonly></div>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">User Name :</label><input value="<?= h((string) $user['Username']) ?>" class="<?= $inputClass ?> bg-base-200" readonly></div>
                                <div class="<?= $rowClass ?>">
                                    <label class="<?= $labelClass ?>">Role :</label>
                                    <select name="Role" class="select select-bordered select-sm text-sm w-full max-w-xs">
                                        <option value="">Select Role</option>
                                        <?php foreach ($roleOptions as $opt): ?><option value="<?= h($opt) ?>" <?= $selectedRole === $opt ? 'selected' : '' ?>><?= h($opt) ?></option><?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-center"><button class="btn btn-primary" type="submit">Change Role</button></div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

