<?php
$usersAction = $usersAction ?? 'list';
$menuItems = [
    'create' => ['label' => 'Create User', 'href' => 'index.php?page=users_create'],
    'role' => ['label' => 'Change Role', 'href' => 'index.php?page=users_role_list'],
    'password' => ['label' => 'Change Password', 'href' => 'index.php?page=users_password_list'],
    'list' => ['label' => 'User List', 'href' => 'index.php?page=users'],
];
?>
<div class="grid grid-cols-2 md:grid-cols-4 gap-2 md:gap-3">
    <?php foreach ($menuItems as $key => $item): ?>
        <a href="<?= h($item['href']) ?>"
           class="btn btn-sm <?= $usersAction === $key ? 'btn-primary' : 'btn-outline btn-primary' ?>">
            <?= h($item['label']) ?>
        </a>
    <?php endforeach; ?>
</div>

