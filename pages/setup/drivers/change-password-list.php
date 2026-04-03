<?php
declare(strict_types=1);
if (!isset($mysqli)) require __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../includes/setup_drivers_service.php';
$rows = setup_drivers_list($mysqli); $flash = setup_drivers_flash_get();
require __DIR__ . '/../../../includes/header.php'; require __DIR__ . '/../../../includes/nav.php';
?>
<div class="flex gap-6 w-full"><aside class="hidden lg:block w-72 shrink-0"><?php require __DIR__ . '/../sidebar.php'; ?></aside><main class="flex-1 px-4"><div class="space-y-4">
<?php $breadcrumbParentLabel='Driver Setup';$breadcrumbParentHref='index.php?page=setup_drivers';$breadcrumbCurrent='Change Password';require __DIR__ . '/../../../includes/breadcrumb.php'; ?>
<div class="card bg-base-100 shadow-xl border border-base-300"><div class="card-body space-y-4">
<?php if(!empty($flash)): ?><div class="alert <?= $flash['type']==='success'?'alert-success':'alert-error' ?>"><span><?= h((string)$flash['message']) ?></span></div><?php endif; ?>
<div class="overflow-x-auto rounded-box border border-base-300"><table class="table table-zebra table-sm"><thead><tr><th>S.N</th><th>Driver Name</th><th>Users Name</th><th>Change Password</th></tr></thead><tbody><?php $sn=1; foreach($rows as $r): ?><tr><td><?= $sn++ ?></td><td><?= h((string)$r['driver_name']) ?></td><td><?= h((string)$r['Username']) ?></td><td><a class="btn btn-xs btn-warning btn-outline" href="index.php?page=setup_driver_password_form&id=<?= (int)$r['driver_id'] ?>">Change Password</a></td></tr><?php endforeach; ?></tbody></table></div>
</div></div></div></main></div>
<?php require __DIR__ . '/../../../includes/footer.php'; ?>

