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
    header('Location: index.php?page=users');
    exit;
}

$form = [
    'Name' => (string) ($user['Name'] ?? ''),
    'Username' => (string) ($user['Username'] ?? ''),
    'ic_passport' => (string) ($user['ic_passport'] ?? ''),
    'contact_nomber' => (string) ($user['contact_nomber'] ?? ''),
    'Email' => (string) ($user['Email'] ?? ''),
    'email_password' => (string) ($user['email_password'] ?? ''),
    'outgoing_server' => (string) ($user['outgoing_server'] ?? ''),
    'outgoing_port_no' => (string) ($user['outgoing_port_no'] ?? ''),
    'date_birth' => (string) ($user['date_birth'] ?? ''),
    'Status' => (string) ($user['Status'] ?? ''),
    'gender' => (string) ($user['gender'] ?? ''),
    'position' => (string) ($user['position'] ?? ''),
    'department' => (string) ($user['department'] ?? ''),
    'employee_id' => (string) ($user['employee_id'] ?? ''),
    'Role' => (string) ($user['Role'] ?? ''),
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!users_csrf_validate((string) ($_POST['_token'] ?? ''))) {
        $errors[] = 'Invalid request token.';
    } else {
        foreach ($form as $key => $_) {
            $form[$key] = trim((string) ($_POST[$key] ?? ''));
        }
        if (!empty($actor['is_admin'])) {
            $form['department'] = (string) ($actor['department'] ?? '');
        }
        $result = users_update($mysqli, $userId, $form);
        if (!empty($result['ok'])) {
            users_flash_set('success', 'User updated successfully.');
            header('Location: index.php?page=users_view&id=' . $userId);
            exit;
        }
        $errors = $result['errors'] ?? ['Update failed.'];
    }
}

$activeOptions = users_lookup_values($mysqli, 'active_status', 'active_status');
$genderOptions = users_lookup_values($mysqli, 'gender', 'gender_name');
$positionOptions = users_lookup_values($mysqli, 'position', 'position_name');
$departmentOptions = users_lookup_values($mysqli, 'department', 'department_name');
if (!empty($actor['is_admin'])) {
    $departmentOptions = [(string) ($actor['department'] ?? '')];
    $form['department'] = (string) ($actor['department'] ?? '');
}
$csrf = users_csrf_token();
?>

<div class="flex gap-6 w-full">
    <aside class="hidden lg:block w-72 shrink-0">
        <?php require __DIR__ . '/sidebar.php'; ?>
    </aside>

    <main class="flex-1 px-4">
        <div class="space-y-4">
            <?php $breadcrumbCurrent = 'Users / Edit User'; require __DIR__ . '/../../includes/breadcrumb.php'; ?>
            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body space-y-4">
                    <?php $usersAction = 'list'; require __DIR__ . '/menu-top.php'; ?>

                    <?php if ($errors): ?>
                        <div class="alert alert-error">
                            <span><?= h(implode(' ', $errors)) ?></span>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="index.php?page=users_edit&id=<?= $userId ?>" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <input type="hidden" name="_token" value="<?= h($csrf) ?>">
                        <input type="hidden" name="id" value="<?= $userId ?>">
                        <label class="form-control"><span class="label-text">Full Name</span><input name="Name" value="<?= h($form['Name']) ?>" class="input input-bordered" required></label>
                        <label class="form-control"><span class="label-text">User Name</span><input name="Username" value="<?= h($form['Username']) ?>" class="input input-bordered" required></label>
                        <label class="form-control"><span class="label-text">IC/Passport</span><input name="ic_passport" value="<?= h($form['ic_passport']) ?>" class="input input-bordered"></label>
                        <label class="form-control"><span class="label-text">Contact No.</span><input name="contact_nomber" value="<?= h($form['contact_nomber']) ?>" class="input input-bordered"></label>
                        <label class="form-control"><span class="label-text">Email Address</span><input name="Email" value="<?= h($form['Email']) ?>" class="input input-bordered"></label>
                        <label class="form-control"><span class="label-text">Date of Birth</span><input type="date" name="date_birth" value="<?= h($form['date_birth']) ?>" class="input input-bordered"></label>
                        <label class="form-control"><span class="label-text">Email Password</span><input type="password" name="email_password" value="<?= h($form['email_password']) ?>" class="input input-bordered"></label>
                        <label class="form-control"><span class="label-text">Outgoing Server</span><input name="outgoing_server" value="<?= h($form['outgoing_server']) ?>" class="input input-bordered"></label>
                        <label class="form-control"><span class="label-text">Port No.</span><input name="outgoing_port_no" value="<?= h($form['outgoing_port_no']) ?>" class="input input-bordered"></label>
                        <label class="form-control"><span class="label-text">Employee ID</span><input name="employee_id" value="<?= h($form['employee_id']) ?>" class="input input-bordered"></label>

                        <label class="form-control">
                            <span class="label-text">Active Status</span>
                            <select name="Status" class="select select-bordered">
                                <?php foreach ($activeOptions as $opt): ?><option value="<?= h($opt) ?>" <?= $form['Status'] === $opt ? 'selected' : '' ?>><?= h($opt) ?></option><?php endforeach; ?>
                            </select>
                        </label>
                        <label class="form-control">
                            <span class="label-text">Gender</span>
                            <select name="gender" class="select select-bordered">
                                <?php foreach ($genderOptions as $opt): ?><option value="<?= h($opt) ?>" <?= $form['gender'] === $opt ? 'selected' : '' ?>><?= h($opt) ?></option><?php endforeach; ?>
                            </select>
                        </label>
                        <label class="form-control">
                            <span class="label-text">Title</span>
                            <select name="position" class="select select-bordered">
                                <?php foreach ($positionOptions as $opt): ?><option value="<?= h($opt) ?>" <?= $form['position'] === $opt ? 'selected' : '' ?>><?= h($opt) ?></option><?php endforeach; ?>
                            </select>
                        </label>
                        <label class="form-control md:col-span-2">
                            <span class="label-text">Department</span>
                            <select name="department" class="select select-bordered">
                                <?php foreach ($departmentOptions as $opt): ?><option value="<?= h($opt) ?>" <?= $form['department'] === $opt ? 'selected' : '' ?>><?= h($opt) ?></option><?php endforeach; ?>
                            </select>
                        </label>

                        <div class="md:col-span-2 flex justify-end">
                            <button class="btn btn-primary" type="submit">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

