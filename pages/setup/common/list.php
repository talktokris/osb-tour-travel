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
<?php $sn=1; $arabicCols = $m['list_arabic_columns'] ?? []; foreach($rows as $row): ?><tr><td><?= $sn++ ?></td><?php foreach(array_keys($m['list_columns']) as $col): $cell = (string)($row[$col]??''); $isAr = in_array($col, $arabicCols, true); if ($isAr && function_exists('normalize_arabic_text')) { $cell = normalize_arabic_text($cell); } ?><td<?= $isAr ? ' class="max-w-xs whitespace-normal text-right" dir="rtl" lang="ar"' : '' ?>><?= h($cell) ?></td><?php endforeach; ?><td><a class="btn btn-xs btn-info btn-outline" href="index.php?page=<?= h($routes['view']) ?>&id=<?= (int)$row[$pk] ?>">View</a></td><td><a class="btn btn-xs btn-outline" href="index.php?page=<?= h($routes['edit']) ?>&id=<?= (int)$row[$pk] ?>">Edit</a></td><?php if($setupModule==='drivers'): ?><td><a class="btn btn-xs btn-warning btn-outline" href="index.php?page=setup_driver_password_form&id=<?= (int)$row[$pk] ?>">Change Password</a></td><?php endif; ?><?php if(!$hideDelete): ?><td><button type="button" class="btn btn-xs btn-error btn-outline js-del" data-id="<?= (int)$row[$pk] ?>" data-name="<?= h((string)($row[array_key_first($m['list_columns'])]??'')) ?>">Delete</button></td><?php endif; ?></tr><?php endforeach; ?>
</tbody></table></div></div></div></div></main></div>
<?php if (!$hideDelete): ?>
<form id="f-del" method="post" action="index.php?page=<?= h($routes['list']) ?>" class="hidden" aria-hidden="true"><input type="hidden" name="_token" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="delete_row"><input type="hidden" name="id" id="f-del-id"></form>
<dialog id="delete-setup-list-modal" class="agent-delete-dialog" aria-labelledby="delete-setup-list-title" aria-describedby="delete-setup-list-msg">
    <div class="agent-delete-dialog__surface">
        <h3 class="font-bold text-lg mb-1" id="delete-setup-list-title">Delete <?= h($m['single']) ?>?</h3>
        <p class="agent-delete-dialog__message" id="delete-setup-list-msg">Are you sure? This cannot be undone.</p>
        <div class="agent-delete-dialog__actions">
            <button type="button" class="btn btn-outline" id="delete-setup-list-no">No</button>
            <button type="button" class="btn btn-error" id="delete-setup-list-yes">Yes</button>
        </div>
    </div>
</dialog>
<script>
(function () {
    var modal = document.getElementById('delete-setup-list-modal');
    var form = document.getElementById('f-del');
    var idInput = document.getElementById('f-del-id');
    var msg = document.getElementById('delete-setup-list-msg');
    var btnNo = document.getElementById('delete-setup-list-no');
    var btnYes = document.getElementById('delete-setup-list-yes');
    var entityLabel = <?= json_encode($m['single'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    if (!modal || !form || !idInput || !msg || !btnNo || !btnYes) return;

    document.querySelectorAll('.js-del').forEach(function (btn) {
        btn.addEventListener('click', function () {
            idInput.value = this.getAttribute('data-id') || '';
            var name = this.getAttribute('data-name') || '';
            msg.textContent = name
                ? 'Are you sure you want to delete ' + entityLabel + ' "' + name + '"? This cannot be undone.'
                : 'Are you sure you want to delete this ' + entityLabel + '? This cannot be undone.';
            modal.showModal();
        });
    });
    btnNo.addEventListener('click', function () { modal.close(); });
    btnYes.addEventListener('click', function () { modal.close(); form.submit(); });
})();
</script>
<?php endif; ?>
<?php require __DIR__ . '/../../../includes/footer.php'; ?>
