<?php
declare(strict_types=1);
if (!isset($mysqli)) require __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../includes/setup_vehicles_service.php';
$defaults=['vehicles_name'=>'','vehicles_type'=>'','vehicles_no'=>'','vehicles_max_occupancy'=>''];$form=$defaults;$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){if(!setup_vehicles_csrf_validate((string)($_POST['_token']??''))){$errors[]='Invalid request token.';}else{foreach($defaults as $k=>$_)$form[$k]=trim((string)($_POST[$k]??''));$r=setup_vehicles_create($mysqli,$form);if(!empty($r['ok'])){setup_vehicles_flash_set('success','Vehicle created successfully.');header('Location: index.php?page=setup_vehicle_view&id='.(int)($r['id']??0));exit;}$errors=$r['errors']??['Create failed.'];}}
$types=setup_vehicles_types($mysqli);$csrf=setup_vehicles_csrf_token();require __DIR__ . '/../../../includes/header.php';require __DIR__ . '/../../../includes/nav.php';
?>
<div class="flex gap-6 w-full"><aside class="hidden lg:block w-72 shrink-0"><?php require __DIR__ . '/../sidebar.php'; ?></aside><main class="flex-1 px-4"><div class="space-y-4">
<?php $breadcrumbParentLabel='Vehicles Setup';$breadcrumbParentHref='index.php?page=setup_vehicles';$breadcrumbCurrent='Create Vehicles';require __DIR__ . '/../../../includes/breadcrumb.php'; ?>
<div class="flex flex-wrap gap-2"><a href="index.php?page=setup_vehicles" class="btn btn-sm btn-outline">Back to vehicles list</a></div>
<div class="card bg-base-100 shadow-xl border border-base-300"><div class="card-body space-y-4"><?php if($errors): ?><div class="alert alert-error"><span><?= h(implode(' ',$errors)) ?></span></div><?php endif; ?>
<form method="post" action="index.php?page=setup_vehicle_create" class="space-y-3"><input type="hidden" name="_token" value="<?= h($csrf) ?>">
<div class="max-w-4xl mx-auto border border-base-300 rounded-box overflow-hidden"><div class="px-4 py-2.5 bg-linear-to-r from-sky-700 to-cyan-600 text-white font-bold text-base">Create Vehicles</div><div class="divide-y divide-base-300">
<?php $rowClass='grid grid-cols-1 md:grid-cols-[190px_1fr] items-center gap-2 px-3 py-1.5'; $labelClass='font-semibold text-sm text-base-content/80'; $inputClass='input input-bordered input-sm text-sm w-full max-w-xl'; $selectClass='select select-bordered select-sm text-sm w-full max-w-xs'; ?>
<div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Vehicles Name :</label><input name="vehicles_name" value="<?= h($form['vehicles_name']) ?>" class="<?= $inputClass ?>" required></div>
<div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Type :</label><select name="vehicles_type" class="<?= $selectClass ?>" required><option value="">Select Vehicle Type</option><?php foreach($types as $t): ?><option value="<?= h($t) ?>" <?= $form['vehicles_type']===$t?'selected':'' ?>><?= h($t) ?></option><?php endforeach; ?></select></div>
<div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Vehicles No :</label><input name="vehicles_no" value="<?= h($form['vehicles_no']) ?>" class="<?= $inputClass ?>" required></div>
<div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Max Occupancy :</label><input name="vehicles_max_occupancy" value="<?= h($form['vehicles_max_occupancy']) ?>" class="<?= $inputClass ?>" required></div>
</div></div><div class="flex justify-center"><button class="btn btn-primary" type="submit">Submit</button></div></form></div></div>
</div></main></div>
<?php require __DIR__ . '/../../../includes/footer.php'; ?>
