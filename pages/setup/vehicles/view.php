<?php
declare(strict_types=1);
if (!isset($mysqli)) require __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../includes/setup_vehicles_service.php';
$id=(int)($_GET['id']??0);$row=$id>0?setup_vehicles_find($mysqli,$id):null;if(!$row){setup_vehicles_flash_set('error','Vehicle not found.');header('Location: index.php?page=setup_vehicles');exit;}
$flash=setup_vehicles_flash_get();require __DIR__ . '/../../../includes/header.php';require __DIR__ . '/../../../includes/nav.php';
?>
<div class="flex gap-6 w-full"><aside class="hidden lg:block w-72 shrink-0"><?php require __DIR__ . '/../sidebar.php'; ?></aside><main class="flex-1 px-4"><div class="space-y-4">
<?php $breadcrumbParentLabel='Vehicles Setup';$breadcrumbParentHref='index.php?page=setup_vehicles';$breadcrumbCurrent='View Vehicles';require __DIR__ . '/../../../includes/breadcrumb.php'; ?>
<div class="flex flex-wrap items-center gap-2"><a href="index.php?page=setup_vehicles" class="btn btn-sm btn-outline">Back to vehicles list</a><a href="index.php?page=setup_vehicle_edit&id=<?= $id ?>" class="btn btn-sm btn-success">Edit</a></div>
<div class="card bg-base-100 shadow-xl border border-base-300"><div class="card-body space-y-4"><?php if(!empty($flash)): ?><div class="alert <?= $flash['type']==='success'?'alert-success':'alert-error' ?>"><span><?= h((string)$flash['message']) ?></span></div><?php endif; ?>
<div class="max-w-4xl mx-auto border border-base-300 rounded-box overflow-hidden"><div class="px-4 py-2.5 bg-linear-to-r from-sky-700 to-cyan-600 text-white font-bold text-base">View Vehicles</div><div class="divide-y divide-base-300">
<?php $rowClass='grid grid-cols-1 md:grid-cols-[190px_1fr] items-center gap-2 px-3 py-1.5'; $labelClass='font-semibold text-sm text-base-content/80'; $valueClass='text-sm text-base-content'; ?>
<div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">Vehicles Name :</div><div class="<?= $valueClass ?>"><?= h((string)$row['vehicles_name']) ?></div></div>
<div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">Type :</div><div class="<?= $valueClass ?>"><?= h((string)$row['vehicles_type']) ?></div></div>
<div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">Vehicles No :</div><div class="<?= $valueClass ?>"><?= h((string)$row['vehicles_no']) ?></div></div>
<div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">Max Occupancy :</div><div class="<?= $valueClass ?>"><?= h((string)$row['vehicles_max_occupancy']) ?></div></div>
</div></div></div></div>
</div></main></div>
<?php require __DIR__ . '/../../../includes/footer.php'; ?>
