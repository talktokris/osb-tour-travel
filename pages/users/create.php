<?php
require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';
require __DIR__ . '/../../includes/users_service.php';

$currentPage = $_GET['page'] ?? 'users';
$actor = users_actor($mysqli);
users_require_access($actor);

$defaults = [
    'Name' => '',
    'Username' => '',
    'ic_passport' => '',
    'password' => '',
    'conpassword' => '',
    'contact_nomber' => '',
    'Email' => '',
    'email_password' => '',
    'outgoing_server' => '',
    'outgoing_port_no' => '',
    'date_birth' => '',
    'Status' => '',
    'gender' => '',
    'Role' => '',
    'position' => '',
    'department' => '',
    'employee_id' => '',
];

$form = $defaults;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!users_csrf_validate((string) ($_POST['_token'] ?? ''))) {
        $errors[] = 'Invalid request token.';
    } else {
        foreach ($defaults as $key => $_) {
            $form[$key] = trim((string) ($_POST[$key] ?? ''));
        }
        if (!empty($actor['is_admin'])) {
            $form['department'] = (string) ($actor['department'] ?? '');
        }
        $result = users_create($mysqli, $form);
        if (!empty($result['ok'])) {
            users_flash_set('success', 'User created successfully.');
            $newId = (int) ($result['id'] ?? 0);
            header('Location: index.php?page=users_view&id=' . $newId);
            exit;
        }
        $errors = $result['errors'] ?? ['Create failed.'];
    }
}

$activeOptions = users_lookup_values($mysqli, 'active_status', 'active_status');
$genderOptions = users_lookup_values($mysqli, 'gender', 'gender_name');
$roleOptions = users_lookup_values($mysqli, 'roles', 'role_name');
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
            <?php $breadcrumbCurrent = 'Users / Create User'; require __DIR__ . '/../../includes/breadcrumb.php'; ?>
            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body space-y-4">
                    <?php $usersAction = 'create'; require __DIR__ . '/menu-top.php'; ?>

                    <?php if ($errors): ?>
                        <div class="alert alert-error">
                            <span><?= h(implode(' ', $errors)) ?></span>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="index.php?page=users_create" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <input type="hidden" name="_token" value="<?= h($csrf) ?>">
                        <label class="form-control"><span class="label-text">Full Name</span><input name="Name" value="<?= h($form['Name']) ?>" class="input input-bordered" required></label>
                        <label class="form-control"><span class="label-text">User Name</span><input name="Username" value="<?= h($form['Username']) ?>" class="input input-bordered" required></label>
                        <label class="form-control"><span class="label-text">IC/Passport</span><input name="ic_passport" value="<?= h($form['ic_passport']) ?>" class="input input-bordered"></label>
                        <label class="form-control"><span class="label-text">Contact No.</span><input name="contact_nomber" value="<?= h($form['contact_nomber']) ?>" class="input input-bordered"></label>
                        <label class="form-control"><span class="label-text">Email Address</span><input name="Email" value="<?= h($form['Email']) ?>" class="input input-bordered"></label>
                        <label class="form-control"><span class="label-text">Date of Birth</span><input type="date" name="date_birth" value="<?= h($form['date_birth']) ?>" class="input input-bordered"></label>
                        <label class="form-control"><span class="label-text">Password</span><input type="password" name="password" class="input input-bordered" required></label>
                        <label class="form-control"><span class="label-text">Confirm Password</span><input type="password" name="conpassword" class="input input-bordered" required></label>
                        <label class="form-control"><span class="label-text">Email Password</span><input type="password" name="email_password" class="input input-bordered"></label>
                        <label class="form-control"><span class="label-text">Outgoing Server</span><input name="outgoing_server" value="<?= h($form['outgoing_server']) ?>" class="input input-bordered"></label>
                        <label class="form-control"><span class="label-text">Port No.</span><input name="outgoing_port_no" value="<?= h($form['outgoing_port_no']) ?>" class="input input-bordered"></label>
                        <label class="form-control"><span class="label-text">Employee ID</span><input name="employee_id" value="<?= h($form['employee_id']) ?>" class="input input-bordered"></label>

                        <label class="form-control">
                            <span class="label-text">Active Status</span>
                            <select name="Status" class="select select-bordered">
                                <option value="">Select Status</option>
                                <?php foreach ($activeOptions as $opt): ?><option value="<?= h($opt) ?>" <?= $form['Status'] === $opt ? 'selected' : '' ?>><?= h($opt) ?></option><?php endforeach; ?>
                            </select>
                        </label>
                        <label class="form-control">
                            <span class="label-text">Gender</span>
                            <select name="gender" class="select select-bordered">
                                <option value="">Select Gender</option>
                                <?php foreach ($genderOptions as $opt): ?><option value="<?= h($opt) ?>" <?= $form['gender'] === $opt ? 'selected' : '' ?>><?= h($opt) ?></option><?php endforeach; ?>
                            </select>
                        </label>
                        <label class="form-control">
                            <span class="label-text">Role</span>
                            <select name="Role" class="select select-bordered">
                                <option value="">Select Role</option>
                                <?php foreach ($roleOptions as $opt): ?><option value="<?= h($opt) ?>" <?= $form['Role'] === $opt ? 'selected' : '' ?>><?= h($opt) ?></option><?php endforeach; ?>
                            </select>
                        </label>
                        <label class="form-control">
                            <span class="label-text">Title</span>
                            <select name="position" class="select select-bordered">
                                <option value="">Select Title</option>
                                <?php foreach ($positionOptions as $opt): ?><option value="<?= h($opt) ?>" <?= $form['position'] === $opt ? 'selected' : '' ?>><?= h($opt) ?></option><?php endforeach; ?>
                            </select>
                        </label>
                        <label class="form-control md:col-span-2">
                            <span class="label-text">Department</span>
                            <select name="department" class="select select-bordered">
                                <option value="">Select Department</option>
                                <?php foreach ($departmentOptions as $opt): ?><option value="<?= h($opt) ?>" <?= $form['department'] === $opt ? 'selected' : '' ?>><?= h($opt) ?></option><?php endforeach; ?>
                            </select>
                        </label>

                        <div class="md:col-span-2 flex justify-end">
                            <button class="btn btn-primary" type="submit">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

