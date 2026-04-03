<?php
declare(strict_types=1);
if (!isset($mysqli)) require __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../includes/setup_drivers_service.php';
$id = (int) ($_GET['id'] ?? 0); $driver = $id > 0 ? setup_drivers_find($mysqli, $id) : null;
if (!$driver) { setup_drivers_flash_set('error', 'Driver not found.'); header('Location: index.php?page=setup_driver_password_list'); exit; }
$errors = []; if ($_SERVER['REQUEST_METHOD'] === 'POST') { if (!setup_drivers_csrf_validate((string) ($_POST['_token'] ?? ''))) $errors[] = 'Invalid request token.'; else { $p = trim((string) ($_POST['new_password'] ?? '')); $cp = trim((string) ($_POST['con_password'] ?? '')); if ($p === '' || $cp === '') $errors[] = 'Both fields are required.'; elseif ($p !== $cp) $errors[] = 'Confirm password does not match.'; else { $res = setup_drivers_update_password($mysqli, $id, $p); if (!empty($res['ok'])) { setup_drivers_flash_set('success', 'Driver password updated successfully.'); header('Location: index.php?page=setup_driver_password_list'); exit; } $errors = $res['errors'] ?? ['Password update failed.']; } } }
$csrf = setup_drivers_csrf_token();
require __DIR__ . '/../../../includes/header.php'; require __DIR__ . '/../../../includes/nav.php';
?>
<div class="flex gap-6 w-full"><aside class="hidden lg:block w-72 shrink-0"><?php require __DIR__ . '/../sidebar.php'; ?></aside><main class="flex-1 px-4"><div class="space-y-4">
<?php $breadcrumbParentLabel='Change Password';$breadcrumbParentHref='index.php?page=setup_driver_password_list';$breadcrumbCurrent='Driver Password';require __DIR__ . '/../../../includes/breadcrumb.php'; ?>
<div class="card bg-base-100 shadow-xl border border-base-300"><div class="card-body space-y-4">
<?php if($errors): ?><div class="alert alert-error"><span><?= h(implode(' ', $errors)) ?></span></div><?php endif; ?>
<form method="post" class="space-y-3"><input type="hidden" name="_token" value="<?= h($csrf) ?>">
<div class="max-w-2xl mx-auto border border-base-300 rounded-box overflow-hidden"><div class="px-4 py-2.5 bg-linear-to-r from-sky-700 to-cyan-600 text-white font-bold text-base">Change Password</div><div class="divide-y divide-base-300">
<div class="grid grid-cols-1 md:grid-cols-[160px_1fr] items-center gap-2 px-3 py-1.5"><label class="font-semibold text-sm">Full Name :</label><input class="input input-bordered input-sm" value="<?= h((string)$driver['driver_name']) ?>" readonly></div>
<div class="grid grid-cols-1 md:grid-cols-[160px_1fr] items-center gap-2 px-3 py-1.5"><label class="font-semibold text-sm">User Name :</label><input class="input input-bordered input-sm" value="<?= h((string)$driver['Username']) ?>" readonly></div>
<div class="grid grid-cols-1 md:grid-cols-[160px_1fr] items-center gap-2 px-3 py-1.5"><label class="font-semibold text-sm">Password :</label><input type="password" name="new_password" class="input input-bordered input-sm"></div>
<div class="grid grid-cols-1 md:grid-cols-[160px_1fr] items-center gap-2 px-3 py-1.5"><label class="font-semibold text-sm">Confirm Password :</label><input type="password" name="con_password" class="input input-bordered input-sm"></div>
</div></div><div class="flex justify-center"><button type="submit" class="btn btn-primary">Change Password</button></div></form>
</div></div></div></main></div>
<?php require __DIR__ . '/../../../includes/footer.php'; ?>

