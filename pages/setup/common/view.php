<?php
declare(strict_types=1);
if (!isset($mysqli)) require __DIR__ . '/../../../config.php';
require_once __DIR__ . '/config.php';
$moduleMap = setup_common_module_map();
$setupModule = (string) ($_GET['m'] ?? '');
if (!isset($moduleMap[$setupModule])) { header('Location: index.php?page=setup'); exit; }
$m = $moduleMap[$setupModule]; require_once $m['service']; $f = $m['functions']; $routes = $m['routes']; $id = (int) ($_GET['id'] ?? 0);
$row = $id > 0 ? $f['find']($mysqli, $id) : null; if (!$row) { $f['flash_set']('error', $m['single'] . ' not found.'); header('Location: index.php?page=' . $routes['list']); exit; }
$flash = $f['flash_get']();
require __DIR__ . '/../../../includes/header.php'; require __DIR__ . '/../../../includes/nav.php';
?>
<div class="flex gap-6 w-full"><aside class="hidden lg:block w-72 shrink-0"><?php require __DIR__ . '/../sidebar.php'; ?></aside><main class="flex-1 px-4"><div class="space-y-4">
<?php $breadcrumbParentLabel=$m['label'];$breadcrumbParentHref='index.php?page='.$routes['list'];$breadcrumbCurrent='View '.$m['single'];require __DIR__ . '/../../../includes/breadcrumb.php'; ?>
<div class="flex gap-2"><a href="index.php?page=<?= h($routes['list']) ?>" class="btn btn-sm btn-outline">Back to list</a><a href="index.php?page=<?= h($routes['edit']) ?>&id=<?= $id ?>" class="btn btn-sm btn-success">Edit</a></div>
<div class="card bg-base-100 shadow-xl border border-base-300"><div class="card-body space-y-4">
<?php if(!empty($flash)): ?><div class="alert <?= $flash['type']==='success'?'alert-success':'alert-error' ?>"><span><?= h((string)$flash['message']) ?></span></div><?php endif; ?>
<div class="max-w-4xl mx-auto border border-base-300 rounded-box overflow-hidden"><div class="px-4 py-2.5 bg-linear-to-r from-sky-700 to-cyan-600 text-white font-bold text-base">View <?= h($m['single']) ?></div><div class="divide-y divide-base-300">
<?php $rowClass='grid grid-cols-1 md:grid-cols-[220px_1fr] items-center gap-2 px-3 py-1.5'; $labelClass='font-semibold text-sm text-base-content/80'; foreach($m['fields'] as $fld): if(!empty($fld['create_only'])) continue; $k=$fld['key']; ?><div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>"><?= h($fld['label']) ?> :</div><div class="text-sm"><?= h((string)($row[$k]??'')) ?></div></div><?php endforeach; ?>
</div></div></div></div></main></div>
<?php require __DIR__ . '/../../../includes/footer.php'; ?>

