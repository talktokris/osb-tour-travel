<?php
// Sidebar for Users module.
$activePage = $_GET['page'] ?? 'users';
?>
<aside class="module-sidebar">
    <div class="module-sidebar__head">Users Menu</div>
    <div class="px-3 pt-2 pb-1 text-[10px] uppercase tracking-wider text-slate-500 font-medium">Access</div>
    <ul class="menu">
        <li class="<?= $activePage === 'users_create' ? 'active' : '' ?>"><a href="index.php?page=users_create">Create User</a></li>
        <li class="<?= in_array($activePage, ['users_role_list', 'users_role_form'], true) ? 'active' : '' ?>"><a href="index.php?page=users_role_list">Change Role</a></li>
        <li class="<?= in_array($activePage, ['users_password_list', 'users_password_form'], true) ? 'active' : '' ?>"><a href="index.php?page=users_password_list">Change Password</a></li>
        <li class="<?= in_array($activePage, ['users', 'users_view', 'users_edit'], true) ? 'active' : '' ?>"><a href="index.php?page=users">User List</a></li>
    </ul>
</aside>

