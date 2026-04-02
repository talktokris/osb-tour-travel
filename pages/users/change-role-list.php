<?php
require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';
require __DIR__ . '/../../includes/users_service.php';

$currentPage = $_GET['page'] ?? 'users';
$actor = users_actor($mysqli);
users_require_access($actor);

$users = users_list($mysqli, $actor);
$flash = users_flash_get();
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
                                    <th>Name</th>
                                    <th>Users Name</th>
                                    <th>Role</th>
                                    <th>Change Role</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $row): ?>
                                    <tr>
                                        <td><?= h((string) $row['Userid']) ?></td>
                                        <td><?= h((string) $row['Name']) ?></td>
                                        <td><?= h((string) $row['Username']) ?></td>
                                        <td><?= h((string) $row['Role']) ?></td>
                                        <td><a class="btn btn-xs btn-primary btn-outline" href="index.php?page=users_role_form&id=<?= (int) $row['Userid'] ?>">Change Role</a></td>
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

