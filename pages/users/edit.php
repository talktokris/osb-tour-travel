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
                    <?php if ($errors): ?>
                        <div class="alert alert-error">
                            <span><?= h(implode(' ', $errors)) ?></span>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="index.php?page=users_edit&id=<?= $userId ?>" class="space-y-4">
                        <input type="hidden" name="_token" value="<?= h($csrf) ?>">
                        <input type="hidden" name="id" value="<?= $userId ?>">
                        <div class="max-w-5xl mx-auto border border-base-300 rounded-box overflow-hidden">
                            <div class="px-4 py-3 bg-linear-to-r from-sky-700 to-cyan-600 text-white font-bold text-lg">Edit User</div>
                            <div class="divide-y divide-base-300">
                                <?php
                                $rowClass = 'grid grid-cols-1 md:grid-cols-[220px_1fr] items-center gap-3 px-4 py-2';
                                $inputClass = 'input input-bordered w-full max-w-xl';
                                $selectClass = 'select select-bordered w-full max-w-xs';
                                ?>
                                <div class="<?= $rowClass ?>"><label class="font-semibold text-base-content/80">Full Name :</label><input name="Name" value="<?= h($form['Name']) ?>" class="<?= $inputClass ?>" required></div>
                                <div class="<?= $rowClass ?>"><label class="font-semibold text-base-content/80">User Name :</label><input name="Username" value="<?= h($form['Username']) ?>" class="<?= $inputClass ?>" required></div>
                                <div class="<?= $rowClass ?>"><label class="font-semibold text-base-content/80">IC/Passport :</label><input name="ic_passport" value="<?= h($form['ic_passport']) ?>" class="<?= $inputClass ?>"></div>
                                <div class="<?= $rowClass ?>"><label class="font-semibold text-base-content/80">Contact No. :</label><input name="contact_nomber" value="<?= h($form['contact_nomber']) ?>" class="<?= $inputClass ?>"></div>
                                <div class="<?= $rowClass ?>"><label class="font-semibold text-base-content/80">Email Address :</label><input name="Email" value="<?= h($form['Email']) ?>" class="<?= $inputClass ?>"></div>
                                <div class="<?= $rowClass ?>"><label class="font-semibold text-base-content/80">Email Password :</label><input type="password" name="email_password" value="<?= h($form['email_password']) ?>" class="<?= $inputClass ?>"></div>
                                <div class="<?= $rowClass ?>"><label class="font-semibold text-base-content/80">Outgoing Server :</label><input name="outgoing_server" value="<?= h($form['outgoing_server']) ?>" class="<?= $inputClass ?>"></div>
                                <div class="<?= $rowClass ?>"><label class="font-semibold text-base-content/80">Port No :</label><input name="outgoing_port_no" value="<?= h($form['outgoing_port_no']) ?>" class="input input-bordered w-full max-w-48"></div>
                                <div class="<?= $rowClass ?>"><label class="font-semibold text-base-content/80">Date of Birth :</label><input type="date" name="date_birth" value="<?= h($form['date_birth']) ?>" class="input input-bordered w-full max-w-xs"></div>
                                <div class="<?= $rowClass ?>"><label class="font-semibold text-base-content/80">Active Status :</label><select name="Status" class="<?= $selectClass ?>"><?php foreach ($activeOptions as $opt): ?><option value="<?= h($opt) ?>" <?= $form['Status'] === $opt ? 'selected' : '' ?>><?= h($opt) ?></option><?php endforeach; ?></select></div>
                                <div class="<?= $rowClass ?>"><label class="font-semibold text-base-content/80">Gender :</label><select name="gender" class="<?= $selectClass ?>"><?php foreach ($genderOptions as $opt): ?><option value="<?= h($opt) ?>" <?= $form['gender'] === $opt ? 'selected' : '' ?>><?= h($opt) ?></option><?php endforeach; ?></select></div>
                                <div class="<?= $rowClass ?>"><label class="font-semibold text-base-content/80">Title :</label><select name="position" class="<?= $selectClass ?>"><?php foreach ($positionOptions as $opt): ?><option value="<?= h($opt) ?>" <?= $form['position'] === $opt ? 'selected' : '' ?>><?= h($opt) ?></option><?php endforeach; ?></select></div>
                                <div class="<?= $rowClass ?>"><label class="font-semibold text-base-content/80">Department :</label><select name="department" class="<?= $selectClass ?>"><?php foreach ($departmentOptions as $opt): ?><option value="<?= h($opt) ?>" <?= $form['department'] === $opt ? 'selected' : '' ?>><?= h($opt) ?></option><?php endforeach; ?></select></div>
                                <div class="<?= $rowClass ?>"><label class="font-semibold text-base-content/80">Employee ID :</label><input name="employee_id" value="<?= h($form['employee_id']) ?>" class="input input-bordered w-full max-w-xs"></div>
                            </div>
                        </div>

                        <div class="flex justify-center">
                            <button class="btn btn-primary" type="submit">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

