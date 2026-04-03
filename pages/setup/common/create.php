<?php
declare(strict_types=1);
if (!isset($mysqli)) require __DIR__ . '/../../../config.php';
require_once __DIR__ . '/config.php';
$moduleMap = setup_common_module_map();
$setupModule = (string) ($_GET['m'] ?? '');
if (!isset($moduleMap[$setupModule])) { header('Location: index.php?page=setup'); exit; }
$m = $moduleMap[$setupModule]; require_once $m['service']; $f = $m['functions']; $routes = $m['routes'];
$form = []; foreach ($m['fields'] as $fld) { $form[$fld['key']] = ''; } $errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') { if (!$f['csrf_validate']((string) ($_POST['_token'] ?? ''))) $errors[] = 'Invalid request token.'; else { foreach ($m['fields'] as $fld) { $k = $fld['key']; $form[$k] = trim((string) ($_POST[$k] ?? '')); } $res = $f['create']($mysqli, $form); if (!empty($res['ok'])) { $id = (int) ($res['id'] ?? 0); $f['flash_set']('success', $m['single'] . ' created successfully.'); header('Location: index.php?page=' . $routes['view'] . '&id=' . $id); exit; } $errors = $res['errors'] ?? ['Create failed.']; } }
$arabicNorm = static function (string $v, array $fld): string {
    if (empty($fld['arabic']) || !function_exists('normalize_arabic_text')) {
        return $v;
    }
    return normalize_arabic_text($v);
};
$csrf = $f['csrf_token'](); $countries = isset($f['countries']) ? $f['countries']($mysqli) : []; $locations = isset($f['locations']) ? $f['locations']($mysqli) : [];
require __DIR__ . '/../../../includes/header.php'; require __DIR__ . '/../../../includes/nav.php';
?>
<div class="flex gap-6 w-full"><aside class="hidden lg:block w-72 shrink-0"><?php require __DIR__ . '/../sidebar.php'; ?></aside><main class="flex-1 px-4"><div class="space-y-4">
<?php $breadcrumbParentLabel=$m['label'];$breadcrumbParentHref='index.php?page='.$routes['list'];$breadcrumbCurrent='Create '.$m['single'];require __DIR__ . '/../../../includes/breadcrumb.php'; ?>
<div class="flex"><a href="index.php?page=<?= h($routes['list']) ?>" class="btn btn-sm btn-outline">Back to list</a></div>
<div class="card bg-base-100 shadow-xl border border-base-300"><div class="card-body space-y-4">
<?php if($errors): ?><div class="alert alert-error"><span><?= h(implode(' ', $errors)) ?></span></div><?php endif; ?>
<form method="post" action="index.php?page=<?= h($routes['create']) ?>" class="space-y-3"><input type="hidden" name="_token" value="<?= h($csrf) ?>">
<div class="max-w-4xl mx-auto border border-base-300 rounded-box overflow-hidden"><div class="px-4 py-2.5 bg-linear-to-r from-sky-700 to-cyan-600 text-white font-bold text-base">Create <?= h($m['single']) ?></div><div class="divide-y divide-base-300">
<?php $rowClass='grid grid-cols-1 md:grid-cols-[220px_1fr] items-center gap-2 px-3 py-1.5'; $labelClass='font-semibold text-sm text-base-content/80'; $inputClass='input input-bordered input-sm text-sm w-full max-w-xl'; foreach($m['fields'] as $fld): $k=$fld['key']; if(!empty($fld['edit_only'])) continue; ?>
<div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>"><?= h($fld['label']) ?> :</label>
<?php if(($fld['type']??'')==='textarea'): ?><textarea name="<?= h($k) ?>" class="textarea textarea-bordered textarea-sm text-sm w-full max-w-xl<?= !empty($fld['arabic']) ? ' input-arabic' : '' ?>" rows="3"<?= !empty($fld['arabic']) ? ' dir="rtl" lang="ar"' : '' ?>><?= h($arabicNorm($form[$k], $fld)) ?></textarea>
<?php elseif(($fld['type']??'')==='password'): ?><input type="password" name="<?= h($k) ?>" class="<?= $inputClass ?>">
<?php elseif(($fld['type']??'')==='select_countries'): ?><select name="<?= h($k) ?>" class="select select-bordered select-sm w-full max-w-xs"><option value="">Select Country</option><?php foreach($countries as $c): ?><option value="<?= h($c) ?>" <?= $form[$k]===$c?'selected':'' ?>><?= h($c) ?></option><?php endforeach; ?></select>
<?php elseif(($fld['type']??'')==='select_locations'): ?><select name="<?= h($k) ?>" class="select select-bordered select-sm w-full max-w-xs"><option value="">Select Location</option><?php foreach($locations as $loc): ?><option value="<?= h($loc) ?>" <?= $form[$k]===$loc?'selected':'' ?>><?= h($loc) ?></option><?php endforeach; ?></select>
<?php else: ?><input name="<?= h($k) ?>" value="<?= h($arabicNorm($form[$k], $fld)) ?>" class="<?= $inputClass ?><?= !empty($fld['arabic']) ? ' input-arabic' : '' ?>"<?= !empty($fld['arabic']) ? ' dir="rtl" lang="ar"' : '' ?>><?php endif; ?>
</div><?php endforeach; ?></div></div><div class="flex justify-center"><button class="btn btn-primary" type="submit">Submit</button></div></form>
</div></div></div></main></div>
<?php require __DIR__ . '/../../../includes/footer.php'; ?>

