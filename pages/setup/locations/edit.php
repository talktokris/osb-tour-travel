<?php
declare(strict_types=1);
if(!isset($mysqli)) require __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../includes/setup_locations_service.php';
$id=(int)($_GET['id']??0);$row=$id>0?setup_locations_find($mysqli,$id):null;if(!$row){setup_locations_flash_set('error','Location not found.');header('Location: index.php?page=setup_locations');exit;}
$form=['location_name'=>(string)$row['location_name'],'location_name_arb'=>(string)$row['location_name_arb'],'location_city'=>(string)$row['location_city'],'location_address'=>(string)$row['location_address'],'location_phone'=>(string)$row['location_phone']];$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['action']??'')==='update_location'){if(!setup_locations_csrf_validate((string)($_POST['_token']??''))){$errors[]='Invalid request token.';}else{foreach(array_keys($form) as $k)$form[$k]=trim((string)($_POST[$k]??''));$r=setup_locations_update($mysqli,$id,$form);if(!empty($r['ok'])){setup_locations_flash_set('success','Location updated successfully.');header('Location: index.php?page=setup_location_view&id='.$id);exit;}$errors=$r['errors']??['Update failed.'];}}
$cities=setup_locations_cities_all($mysqli);$csrf=setup_locations_csrf_token();require __DIR__ . '/../../../includes/header.php';require __DIR__ . '/../../../includes/nav.php';
?>
<div class="flex gap-6 w-full"><aside class="hidden lg:block w-72 shrink-0"><?php require __DIR__ . '/../sidebar.php'; ?></aside><main class="flex-1 px-4"><div class="space-y-4">
<?php $breadcrumbParentLabel='Location Setup';$breadcrumbParentHref='index.php?page=setup_locations';$breadcrumbCurrent='Edit Location';require __DIR__ . '/../../../includes/breadcrumb.php'; ?>
<div class="flex flex-wrap gap-2"><a href="index.php?page=setup_locations" class="btn btn-sm btn-outline">Back to location list</a><a href="index.php?page=setup_location_view&id=<?= $id ?>" class="btn btn-sm btn-ghost">View</a></div>
<div class="card bg-base-100 shadow-xl border border-base-300"><div class="card-body space-y-4"><?php if($errors): ?><div class="alert alert-error"><span><?= h(implode(' ',$errors)) ?></span></div><?php endif; ?>
<form method="post" action="index.php?page=setup_location_edit&id=<?= $id ?>" class="space-y-3"><input type="hidden" name="_token" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="update_location">
<div class="max-w-4xl mx-auto border border-base-300 rounded-box overflow-hidden"><div class="px-4 py-2.5 bg-linear-to-r from-sky-700 to-cyan-600 text-white font-bold text-base">Edit Location</div><div class="divide-y divide-base-300">
<?php $rowClass='grid grid-cols-1 md:grid-cols-[190px_1fr] items-center gap-2 px-3 py-1.5'; $labelClass='font-semibold text-sm text-base-content/80'; $inputClass='input input-bordered input-sm text-sm w-full max-w-xl'; $selectClass='select select-bordered select-sm text-sm w-full max-w-xs'; ?>
<div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Location Name English :</label><input name="location_name" value="<?= h($form['location_name']) ?>" class="<?= $inputClass ?>" required></div>
<div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Location Name Arabic :</label><input name="location_name_arb" value="<?= h($form['location_name_arb']) ?>" class="<?= $inputClass ?>" required></div>
<div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">City :</label><select name="location_city" class="<?= $selectClass ?>" required><option value="">Select City</option><?php foreach($cities as $ct): ?><option value="<?= h($ct['city_name']) ?>" <?= $form['location_city']===$ct['city_name']?'selected':'' ?>><?= h($ct['city_name']) ?></option><?php endforeach; ?></select></div>
<div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Address :</label><input name="location_address" value="<?= h($form['location_address']) ?>" class="<?= $inputClass ?>"></div>
<div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Contact No :</label><input name="location_phone" value="<?= h($form['location_phone']) ?>" class="<?= $inputClass ?>"></div>
</div></div><div class="flex justify-center"><button class="btn btn-primary" type="submit">Update</button></div></form></div></div>
</div></main></div>
<?php require __DIR__ . '/../../../includes/footer.php'; ?>
