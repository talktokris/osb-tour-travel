<?php
declare(strict_types=1);
if (!isset($mysqli)) require __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../includes/setup_vehicles_service.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_vehicle') {
    $token = (string) ($_POST['_token'] ?? '');
    $id = (int) ($_POST['vehicles_id'] ?? 0);
    if (!setup_vehicles_csrf_validate($token)) setup_vehicles_flash_set('error', 'Invalid request token.');
    elseif ($id <= 0) setup_vehicles_flash_set('error', 'Invalid vehicle.');
    elseif (setup_vehicles_delete($mysqli, $id)) setup_vehicles_flash_set('success', 'Vehicle deleted successfully.');
    else setup_vehicles_flash_set('error', 'Delete failed.');
    header('Location: index.php?page=setup_vehicles'); exit;
}

$rows = setup_vehicles_list($mysqli);
$flash = setup_vehicles_flash_get();
$csrf = setup_vehicles_csrf_token();
require __DIR__ . '/../../../includes/header.php';
require __DIR__ . '/../../../includes/nav.php';
?>
<div class="flex gap-6 w-full">
    <aside class="hidden lg:block w-72 shrink-0"><?php require __DIR__ . '/../sidebar.php'; ?></aside>
    <main class="flex-1 px-4">
        <div class="space-y-4">
            <?php $breadcrumbParentLabel='Setup'; $breadcrumbParentHref='index.php?page=setup'; $breadcrumbCurrent='Vehicles Setup'; require __DIR__ . '/../../../includes/breadcrumb.php'; ?>
            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body space-y-4">
                    <?php if (!empty($flash)): ?><div class="alert <?= $flash['type']==='success'?'alert-success':'alert-error' ?>"><span><?= h((string)$flash['message']) ?></span></div><?php endif; ?>
                    <div class="flex justify-center"><a href="index.php?page=setup_vehicle_create" class="btn btn-success btn-sm">Create Vehicles</a></div>
                    <div class="overflow-x-auto rounded-box border border-base-300">
                        <table class="table table-zebra table-sm">
                            <thead><tr><th>S.N</th><th>Vehicles Name</th><th>Type</th><th>Vehicles No</th><th>Max Occupancy</th><th>View</th><th>Edit</th><th>Delete</th></tr></thead>
                            <tbody>
                            <?php $sn=1; foreach($rows as $row): ?>
                                <tr>
                                    <td><?= $sn++ ?></td><td class="font-medium"><?= h((string)$row['vehicles_name']) ?></td><td><?= h((string)$row['vehicles_type']) ?></td><td><?= h((string)$row['vehicles_no']) ?></td><td><?= h((string)$row['vehicles_max_occupancy']) ?></td>
                                    <td><a class="btn btn-xs btn-info btn-outline" href="index.php?page=setup_vehicle_view&id=<?= (int)$row['vehicles_id'] ?>">View</a></td>
                                    <td><a class="btn btn-xs btn-outline" href="index.php?page=setup_vehicle_edit&id=<?= (int)$row['vehicles_id'] ?>">Edit</a></td>
                                    <td><button type="button" class="btn btn-xs btn-error btn-outline js-delete-vehicle" data-id="<?= (int)$row['vehicles_id'] ?>" data-name="<?= h((string)$row['vehicles_name']) ?>">Delete</button></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
<form id="delete-vehicle-form" method="post" action="index.php?page=setup_vehicles" class="hidden"><input type="hidden" name="_token" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="delete_vehicle"><input type="hidden" name="vehicles_id" id="delete-vehicle-id"></form>
<dialog id="delete-vehicle-modal" class="agent-delete-dialog"><div class="agent-delete-dialog__surface"><h3 class="font-bold text-lg mb-1">Delete vehicle?</h3><p class="agent-delete-dialog__message" id="delete-vehicle-msg">Are you sure? This cannot be undone.</p><div class="agent-delete-dialog__actions"><button type="button" class="btn btn-outline" id="delete-vehicle-no">No</button><button type="button" class="btn btn-error" id="delete-vehicle-yes">Yes</button></div></div></dialog>
<script>(function(){var m=document.getElementById('delete-vehicle-modal'),f=document.getElementById('delete-vehicle-form'),i=document.getElementById('delete-vehicle-id'),msg=document.getElementById('delete-vehicle-msg'),n=document.getElementById('delete-vehicle-no'),y=document.getElementById('delete-vehicle-yes');if(!m||!f||!i||!msg||!n||!y)return;document.querySelectorAll('.js-delete-vehicle').forEach(function(b){b.addEventListener('click',function(){i.value=this.getAttribute('data-id')||'';var name=this.getAttribute('data-name')||'';msg.textContent=name?'Are you sure you want to delete vehicle "'+name+'"? This cannot be undone.':'Are you sure you want to delete this vehicle? This cannot be undone.';m.showModal();});});n.addEventListener('click',function(){m.close();});y.addEventListener('click',function(){m.close();f.submit();});})();</script>
<?php require __DIR__ . '/../../../includes/footer.php'; ?>
