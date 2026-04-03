<?php
// POST + redirects must run before any HTML (header.php sends output; otherwise Location fails and page exits blank).
if (!isset($mysqli)) {
    require __DIR__ . '/../../config.php';
}
require_once __DIR__ . '/../../includes/users_service.php';

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
            $createdName = trim((string) ($form['Username'] !== '' ? $form['Username'] : $form['Name']));
            $message = $createdName !== ''
                ? 'User "' . $createdName . '" created successfully.'
                : 'User created successfully.';
            users_flash_set('success', $message);
            header('Location: index.php?page=users');
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

require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';
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
                    <?php if ($errors): ?>
                        <div class="alert alert-error">
                            <span><?= h(implode(' ', $errors)) ?></span>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="index.php?page=users_create" class="space-y-3" autocomplete="off">
                        <input type="hidden" name="_token" value="<?= h($csrf) ?>">
                        <div class="max-w-4xl mx-auto border border-base-300 rounded-box overflow-hidden">
                            <div class="px-4 py-2.5 bg-linear-to-r from-sky-700 to-cyan-600 text-white font-bold text-base">Create User</div>
                            <div class="divide-y divide-base-300">
                                <?php
                                $rowClass = 'grid grid-cols-1 md:grid-cols-[190px_1fr] items-center gap-2 px-3 py-1.5';
                                $labelClass = 'font-semibold text-sm text-base-content/80';
                                $inputClass = 'input input-bordered input-sm text-sm w-full max-w-xl';
                                $selectClass = 'select select-bordered select-sm text-sm w-full max-w-xs';
                                ?>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Full Name :</label><input name="Name" value="<?= h($form['Name']) ?>" class="<?= $inputClass ?>" required></div>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">User Name :</label><input name="Username" value="<?= h($form['Username']) ?>" class="<?= $inputClass ?>" autocomplete="off" required></div>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">IC/Passport :</label><input name="ic_passport" value="<?= h($form['ic_passport']) ?>" class="<?= $inputClass ?>"></div>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Password :</label><input type="password" name="password" class="<?= $inputClass ?>" autocomplete="new-password" required></div>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Confirm Password :</label><input type="password" name="conpassword" class="<?= $inputClass ?>" autocomplete="new-password" required></div>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Contact No. :</label><input name="contact_nomber" value="<?= h($form['contact_nomber']) ?>" class="<?= $inputClass ?>"></div>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Email Address :</label><input name="Email" value="<?= h($form['Email']) ?>" class="<?= $inputClass ?>"></div>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Email Password :</label><input type="password" name="email_password" class="<?= $inputClass ?>"></div>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Outgoing Server :</label><input name="outgoing_server" value="<?= h($form['outgoing_server']) ?>" class="<?= $inputClass ?>"></div>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Port No :</label><input name="outgoing_port_no" value="<?= h($form['outgoing_port_no']) ?>" class="input input-bordered input-sm text-sm w-full max-w-48"></div>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Date of Birth :</label><input type="date" name="date_birth" value="<?= h($form['date_birth']) ?>" class="input input-bordered input-sm text-sm w-full max-w-xs"></div>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Active Status :</label><select name="Status" class="<?= $selectClass ?>"><option value="">Select Status</option><?php foreach ($activeOptions as $opt): ?><option value="<?= h($opt) ?>" <?= $form['Status'] === $opt ? 'selected' : '' ?>><?= h($opt) ?></option><?php endforeach; ?></select></div>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Gender :</label><select name="gender" class="<?= $selectClass ?>"><option value="">Select Gender</option><?php foreach ($genderOptions as $opt): ?><option value="<?= h($opt) ?>" <?= $form['gender'] === $opt ? 'selected' : '' ?>><?= h($opt) ?></option><?php endforeach; ?></select></div>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Role :</label><select name="Role" class="<?= $selectClass ?>"><option value="">Select Role</option><?php foreach ($roleOptions as $opt): ?><option value="<?= h($opt) ?>" <?= $form['Role'] === $opt ? 'selected' : '' ?>><?= h($opt) ?></option><?php endforeach; ?></select></div>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Title :</label><select name="position" class="<?= $selectClass ?>"><option value="">Select Title</option><?php foreach ($positionOptions as $opt): ?><option value="<?= h($opt) ?>" <?= $form['position'] === $opt ? 'selected' : '' ?>><?= h($opt) ?></option><?php endforeach; ?></select></div>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Department :</label><select name="department" class="<?= $selectClass ?>"><option value="">Select Department</option><?php foreach ($departmentOptions as $opt): ?><option value="<?= h($opt) ?>" <?= $form['department'] === $opt ? 'selected' : '' ?>><?= h($opt) ?></option><?php endforeach; ?></select></div>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Employee ID :</label><input name="employee_id" value="<?= h($form['employee_id']) ?>" class="input input-bordered input-sm text-sm w-full max-w-xs"></div>
                            </div>
                        </div>

                        <div class="flex justify-center">
                            <button class="btn btn-primary" type="submit">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

