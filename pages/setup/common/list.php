<?php
declare(strict_types=1);
if (!isset($mysqli)) require __DIR__ . '/../../../config.php';
require_once __DIR__ . '/config.php';
$moduleMap = setup_common_module_map();
$setupModule = (string) ($_GET['m'] ?? '');
if (!isset($moduleMap[$setupModule])) { header('Location: index.php?page=setup'); exit; }
$m = $moduleMap[$setupModule]; require_once $m['service'];
$f = $m['functions']; $routes = $m['routes']; $pk = $m['primary_key'];
$hideCreate = !empty($m['list_hide_create']);
$hideDelete = !empty($m['list_hide_delete']);
if (!$hideDelete && $_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['action'] ?? '') === 'delete_row') {
    if (!$f['csrf_validate']((string) ($_POST['_token'] ?? ''))) $f['flash_set']('error', 'Invalid request token.');
    else { $id = (int) ($_POST['id'] ?? 0); if ($id <= 0) $f['flash_set']('error', 'Invalid record.'); elseif ($f['delete']($mysqli, $id)) $f['flash_set']('success', $m['single'] . ' deleted successfully.'); else $f['flash_set']('error', 'Delete failed.'); }
    header('Location: index.php?page=' . $routes['list']); exit;
}
$rows = $f['list']($mysqli); $flash = $f['flash_get'](); $csrf = $f['csrf_token']();
require __DIR__ . '/../../../includes/header.php'; require __DIR__ . '/../../../includes/nav.php';
?>
<div class="flex gap-6 w-full"><aside class="hidden lg:block w-72 shrink-0"><?php require __DIR__ . '/../sidebar.php'; ?></aside><main class="flex-1 px-4"><div class="space-y-4">
<?php $breadcrumbParentLabel='Setup';$breadcrumbParentHref='index.php?page=setup';$breadcrumbCurrent=$m['label'];require __DIR__ . '/../../../includes/breadcrumb.php'; ?>
<div class="card bg-base-100 shadow-xl border border-base-300"><div class="card-body space-y-4">
<?php if(!empty($flash)): ?><div class="alert <?= $flash['type']==='success'?'alert-success':'alert-error' ?>"><span><?= h((string)$flash['message']) ?></span></div><?php endif; ?>
<?php if($setupModule==='drivers'): ?><div class="flex gap-2 justify-center"><a href="index.php?page=<?= h($routes['create']) ?>" class="btn btn-success btn-sm">Create <?= h($m['single']) ?></a><a href="index.php?page=setup_driver_password_list" class="btn btn-warning btn-sm">Change Password</a></div><?php elseif(!$hideCreate): ?><div class="flex justify-center"><a href="index.php?page=<?= h($routes['create']) ?>" class="btn btn-success btn-sm">Create <?= h($m['single']) ?></a></div><?php endif; ?>
<div class="overflow-x-auto rounded-box border border-base-300"><table class="table table-zebra table-sm"><thead><tr><th>S.N</th><?php foreach($m['list_columns'] as $lbl): ?><th><?= h($lbl) ?></th><?php endforeach; ?><th>View</th><th>Edit</th><?php if($setupModule==='drivers'): ?><th>Password</th><?php endif; ?><?php if(!$hideDelete): ?><th>Delete</th><?php endif; ?></tr></thead><tbody>
<?php $sn=1; foreach($rows as $row): ?><tr><td><?= $sn++ ?></td><?php foreach(array_keys($m['list_columns']) as $col): ?><td><?= h((string)($row[$col]??'')) ?></td><?php endforeach; ?><td><a class="btn btn-xs btn-info btn-outline" href="index.php?page=<?= h($routes['view']) ?>&id=<?= (int)$row[$pk] ?>">View</a></td><td><a class="btn btn-xs btn-outline" href="index.php?page=<?= h($routes['edit']) ?>&id=<?= (int)$row[$pk] ?>">Edit</a></td><?php if($setupModule==='drivers'): ?><td><a class="btn btn-xs btn-warning btn-outline" href="index.php?page=setup_driver_password_form&id=<?= (int)$row[$pk] ?>">Change Password</a></td><?php endif; ?><?php if(!$hideDelete): ?><td><button type="button" class="btn btn-xs btn-error btn-outline js-del" data-id="<?= (int)$row[$pk] ?>" data-name="<?= h((string)($row[array_key_first($m['list_columns'])]??'')) ?>">Delete</button></td><?php endif; ?></tr><?php endforeach; ?>
</tbody></table></div></div></div></div></main></div>
<?php if (!$hideDelete): ?>
<form id="f-del" method="post" action="index.php?page=<?= h($routes['list']) ?>" class="hidden"><input type="hidden" name="_token" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="delete_row"><input type="hidden" name="id" id="f-del-id"></form>
<script>(function(){var f=document.getElementById('f-del'),id=document.getElementById('f-del-id');document.querySelectorAll('.js-del').forEach(function(b){b.addEventListener('click',function(){id.value=this.getAttribute('data-id')||'';if(confirm('Delete this record?'))f.submit();});});})();</script>
<?php endif; ?>
<?php require __DIR__ . '/../../../includes/footer.php'; ?>
