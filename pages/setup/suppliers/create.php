<?php
declare(strict_types=1);
if (!isset($mysqli)) { require __DIR__ . '/../../../config.php'; }
require_once __DIR__ . '/../../../includes/setup_suppliers_service.php';

$defaults = ['supplier_name' => '', 'supplier_address' => '', 'supplier_country' => '', 'supplier_city' => '', 'supplier_email' => '', 'supplier_contact_no' => ''];
$form = $defaults;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!setup_suppliers_csrf_validate((string) ($_POST['_token'] ?? ''))) {
        $errors[] = 'Invalid request token.';
    } else {
        foreach ($defaults as $k => $_) { $form[$k] = trim((string) ($_POST[$k] ?? '')); }
        $result = setup_suppliers_create($mysqli, $form);
        if (!empty($result['ok'])) {
            $newId = (int) ($result['id'] ?? 0);
            setup_suppliers_flash_set('success', 'Supplier created successfully.');
            header('Location: index.php?page=setup_supplier_view&id=' . $newId);
            exit;
        }
        $errors = $result['errors'] ?? ['Create failed.'];
    }
}

$countries = setup_suppliers_countries($mysqli);
$citiesAll = setup_suppliers_cities_all($mysqli);
$csrf = setup_suppliers_csrf_token();
require __DIR__ . '/../../../includes/header.php';
require __DIR__ . '/../../../includes/nav.php';
?>
<div class="flex gap-6 w-full">
    <aside class="hidden lg:block w-72 shrink-0"><?php require __DIR__ . '/../sidebar.php'; ?></aside>
    <main class="flex-1 px-4">
        <div class="space-y-4">
            <?php $breadcrumbParentLabel = 'Supplier Setup'; $breadcrumbParentHref = 'index.php?page=setup_suppliers'; $breadcrumbCurrent = 'Create Supplier'; require __DIR__ . '/../../../includes/breadcrumb.php'; ?>
            <div class="flex flex-wrap gap-2"><a href="index.php?page=setup_suppliers" class="btn btn-sm btn-outline">Back to supplier list</a></div>
            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body space-y-4">
                    <?php if ($errors): ?><div class="alert alert-error"><span><?= h(implode(' ', $errors)) ?></span></div><?php endif; ?>
                    <form method="post" action="index.php?page=setup_supplier_create" class="space-y-3">
                        <input type="hidden" name="_token" value="<?= h($csrf) ?>">
                        <div class="max-w-4xl mx-auto border border-base-300 rounded-box overflow-hidden">
                            <div class="px-4 py-2.5 bg-linear-to-r from-sky-700 to-cyan-600 text-white font-bold text-base">Create Supplier</div>
                            <div class="divide-y divide-base-300">
                                <?php $rowClass='grid grid-cols-1 md:grid-cols-[190px_1fr] items-center gap-2 px-3 py-1.5'; $labelClass='font-semibold text-sm text-base-content/80'; $inputClass='input input-bordered input-sm text-sm w-full max-w-xl'; $selectClass='select select-bordered select-sm text-sm w-full max-w-xs'; ?>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Supplier Name :</label><input name="supplier_name" value="<?= h($form['supplier_name']) ?>" class="<?= $inputClass ?>" required></div>
                                <div class="<?= $rowClass ?> md:items-start"><label class="<?= $labelClass ?> pt-1">Address :</label><textarea name="supplier_address" class="textarea textarea-bordered textarea-sm text-sm w-full max-w-xl" rows="3"><?= h($form['supplier_address']) ?></textarea></div>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Country :</label><select name="supplier_country" id="supplier-country" class="<?= $selectClass ?>" required><option value="">Select country</option><?php foreach ($countries as $c): ?><option value="<?= h($c) ?>" <?= $form['supplier_country']===$c?'selected':'' ?>><?= h($c) ?></option><?php endforeach; ?></select></div>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">City :</label><select name="supplier_city" id="supplier-city" class="<?= $selectClass ?>" required><option value="">Select city</option><?php foreach ($citiesAll as $ct): ?><option value="<?= h($ct['city_name']) ?>" data-country="<?= h($ct['city_country_name']) ?>" <?= $form['supplier_city']===$ct['city_name']?'selected':'' ?>><?= h($ct['city_name']) ?></option><?php endforeach; ?></select></div>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Email :</label><input type="email" name="supplier_email" value="<?= h($form['supplier_email']) ?>" class="<?= $inputClass ?>" required></div>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Contact No :</label><input name="supplier_contact_no" value="<?= h($form['supplier_contact_no']) ?>" class="<?= $inputClass ?>" required></div>
                            </div>
                        </div>
                        <div class="flex justify-center"><button class="btn btn-primary" type="submit">Submit</button></div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>
<script>
(function(){var co=document.getElementById('supplier-country');var ci=document.getElementById('supplier-city');if(!co||!ci)return;function sync(){var c=co.value||'';Array.prototype.forEach.call(ci.options,function(opt,i){if(i===0)return;opt.hidden=c!==''&&(opt.getAttribute('data-country')||'')!==c;});var sel=ci.options[ci.selectedIndex];if(sel&&sel.hidden)ci.selectedIndex=0;}co.addEventListener('change',sync);sync();})();
</script>
<?php require __DIR__ . '/../../../includes/footer.php'; ?>
