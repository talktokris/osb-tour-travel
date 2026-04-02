<?php
require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';
require __DIR__ . '/../../includes/users_service.php';

$currentPage = $_GET['page'] ?? 'users';
$actor = users_actor($mysqli);
users_require_access($actor);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_user') {
    $token = (string) ($_POST['_token'] ?? '');
    $userId = (int) ($_POST['user_id'] ?? 0);

    if (!users_csrf_validate($token)) {
        users_flash_set('error', 'Invalid request token.');
    } else {
        $target = users_find($mysqli, $userId, $actor);
        if (!$target) {
            users_flash_set('error', 'User not found or access denied.');
        } elseif ((int) $target['Userid'] === (int) $actor['id']) {
            users_flash_set('error', 'You cannot delete your own account.');
        } elseif (users_delete($mysqli, $userId)) {
            users_flash_set('success', 'User deleted successfully.');
        } else {
            users_flash_set('error', 'Delete failed.');
        }
    }

    header('Location: index.php?page=users');
    exit;
}

$users = users_list($mysqli, $actor);
$flash = users_flash_get();
$csrf = users_csrf_token();
?>

<div class="flex gap-6 w-full">
    <aside class="hidden lg:block w-72 shrink-0">
        <?php require __DIR__ . '/sidebar.php'; ?>
    </aside>

    <main class="flex-1 px-4">
        <div class="space-y-4">
            <?php $breadcrumbCurrent = 'Users / User List'; require __DIR__ . '/../../includes/breadcrumb.php'; ?>

            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body space-y-4">
                    <?php if (!empty($flash)): ?>
                        <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-error' ?>">
                            <span><?= h((string) $flash['message']) ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="overflow-x-auto rounded-box border border-base-300">
                        <table class="table table-zebra">
                            <thead>
                                <tr>
                                    <th>S.N</th>
                                    <th>Users Name</th>
                                    <th>Gender</th>
                                    <th>Role</th>
                                    <th>Department</th>
                                    <th>Title</th>
                                    <th>Edit</th>
                                    <th>Delete</th>
                                    <th>View</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $row): ?>
                                    <tr>
                                        <td><?= h((string) $row['Userid']) ?></td>
                                        <td><?= h((string) $row['Username']) ?></td>
                                        <td><?= h((string) $row['gender']) ?></td>
                                        <td><?= h((string) $row['Role']) ?></td>
                                        <td><?= h((string) $row['department']) ?></td>
                                        <td><?= h((string) $row['position']) ?></td>
                                        <td>
                                            <a class="btn btn-xs btn-outline" href="index.php?page=users_edit&id=<?= (int) $row['Userid'] ?>">Edit</a>
                                        </td>
                                        <td>
                                            <form method="post" action="index.php?page=users" onsubmit="return confirm('Delete this user?');">
                                                <input type="hidden" name="_token" value="<?= h($csrf) ?>">
                                                <input type="hidden" name="action" value="delete_user">
                                                <input type="hidden" name="user_id" value="<?= (int) $row['Userid'] ?>">
                                                <button type="submit" class="btn btn-xs btn-error btn-outline">Delete</button>
                                            </form>
                                        </td>
                                        <td>
                                            <a class="btn btn-xs btn-info btn-outline" href="index.php?page=users_view&id=<?= (int) $row['Userid'] ?>">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

