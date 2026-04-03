<?php
declare(strict_types=1);
if(!isset($mysqli)) require __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../includes/setup_services_service.php';
$id=(int)($_GET['id']??0);$row=$id>0?setup_services_find($mysqli,$id):null;if(!$row){setup_services_flash_set('error','Service not found.');header('Location: index.php?page=setup_services');exit;}
$flash=setup_services_flash_get();require __DIR__ . '/../../../includes/header.php';require __DIR__ . '/../../../includes/nav.php';
?>
<div class="flex gap-6 w-full"><aside class="hidden lg:block w-72 shrink-0"><?php require __DIR__ . '/../sidebar.php'; ?></aside><main class="flex-1 px-4"><div class="space-y-4">
<?php $breadcrumbParentLabel='Service Setup';$breadcrumbParentHref='index.php?page=setup_services';$breadcrumbCurrent='View Service';require __DIR__ . '/../../../includes/breadcrumb.php'; ?>
<div class="flex flex-wrap items-center gap-2"><a href="index.php?page=setup_services" class="btn btn-sm btn-outline">Back to service list</a><a href="index.php?page=setup_service_edit&id=<?= $id ?>" class="btn btn-sm btn-success">Edit</a></div>
<div class="card bg-base-100 shadow-xl border border-base-300"><div class="card-body space-y-4"><?php if(!empty($flash)): ?><div class="alert <?= $flash['type']==='success'?'alert-success':'alert-error' ?>"><span><?= h((string)$flash['message']) ?></span></div><?php endif; ?>
<div class="max-w-5xl mx-auto border border-base-300 rounded-box overflow-hidden"><div class="px-4 py-2.5 bg-linear-to-r from-sky-700 to-cyan-600 text-white font-bold text-base">View Service</div><div class="divide-y divide-base-300">
<?php $rowClass='grid grid-cols-1 md:grid-cols-[190px_1fr] items-center gap-2 px-3 py-1.5'; $labelClass='font-semibold text-sm text-base-content/80'; $valueClass='text-sm text-base-content'; ?>
<div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">Service Type :</div><div class="<?= $valueClass ?>"><?= h((string)$row['service_type']) ?></div></div>
<div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">From Country :</div><div class="<?= $valueClass ?>"><?= h((string)$row['from_country']) ?></div></div>
<div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">From Location :</div><div class="<?= $valueClass ?>"><?= h((string)$row['from_locaion']) ?></div></div>
<div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">From City :</div><div class="<?= $valueClass ?>"><?= h((string)$row['from_city']) ?></div></div>
<div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">To Country :</div><div class="<?= $valueClass ?>"><?= h((string)$row['to_country']) ?></div></div>
<div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">To Location :</div><div class="<?= $valueClass ?>"><?= h((string)$row['to_locaion']) ?></div></div>
<div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">To City :</div><div class="<?= $valueClass ?>"><?= h((string)$row['to_city']) ?></div></div>
<div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">Service Name Eng :</div><div class="<?= $valueClass ?>"><?= h((string)$row['service_name_english']) ?></div></div>
<div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">Service Name Arb :</div><div class="<?= $valueClass ?> text-right" dir="rtl" lang="ar"><?= h(normalize_arabic_text((string)$row['service_name_arabic'])) ?></div></div>
<div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">Service Categories :</div><div class="<?= $valueClass ?>"><?= h((string)$row['service_categories']) ?></div></div>
<div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">Vehicle Type :</div><div class="<?= $valueClass ?>"><?= h((string)$row['vehicle_type']) ?></div></div>
<div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">Buying / Selling :</div><div class="<?= $valueClass ?>"><?= h((string)$row['buying_price']) ?> / <?= h((string)$row['selling_price']) ?></div></div>
<div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">SIC Adult / Children :</div><div class="<?= $valueClass ?>"><?= h((string)$row['sic_adult_price']) ?> / <?= h((string)$row['sic_children_price']) ?></div></div>
</div></div></div></div>
</div></main></div>
<?php require __DIR__ . '/../../../includes/footer.php'; ?>
