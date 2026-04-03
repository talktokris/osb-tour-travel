<?php

declare(strict_types=1);

if (!isset($mysqli)) {
    require __DIR__ . '/../../../config.php';
}
require_once __DIR__ . '/../../../includes/setup_agents_service.php';

$agentId = (int) ($_GET['id'] ?? $_POST['agent_id'] ?? 0);
$agent = $agentId > 0 ? setup_agents_find($mysqli, $agentId) : null;
if (!$agent) {
    setup_agents_flash_set('error', 'Agent not found.');
    header('Location: index.php?page=setup_agents');
    exit;
}

$form = [
    'agent_name' => (string) ($agent['agent_name'] ?? ''),
    'agent_code' => (string) ($agent['agent_code'] ?? ''),
    'agent_address' => (string) ($agent['agent_address'] ?? ''),
    'agent_country' => (string) ($agent['agent_country'] ?? ''),
    'agent_city' => (string) ($agent['agent_city'] ?? ''),
    'agent_email' => (string) ($agent['agent_email'] ?? ''),
    'agent_contact_no' => (string) ($agent['agent_contact_no'] ?? ''),
    'agent_mobile_no' => (string) ($agent['agent_mobile_no'] ?? ''),
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? 'update');
    if ($action === 'update_agent') {
        if (!setup_agents_csrf_validate((string) ($_POST['_token'] ?? ''))) {
            $errors[] = 'Invalid request token.';
        } else {
            foreach (array_keys($form) as $key) {
                $form[$key] = trim((string) ($_POST[$key] ?? ''));
            }
            $result = setup_agents_update($mysqli, $agentId, $form);
            if (!empty($result['ok'])) {
                setup_agents_flash_set('success', 'Agent updated successfully.');
                header('Location: index.php?page=setup_agent_view&id=' . $agentId);
                exit;
            }
            $errors = $result['errors'] ?? ['Update failed.'];
        }
    } elseif ($action === 'upload_logo') {
        if (!setup_agents_csrf_validate((string) ($_POST['_token_logo'] ?? ''))) {
            setup_agents_flash_set('error', 'Invalid request token.');
        } else {
            $res = setup_agents_save_logo($mysqli, $agentId, $_FILES['logo'] ?? []);
            if (!empty($res['ok'])) {
                setup_agents_flash_set('success', 'Logo updated successfully.');
            } else {
                setup_agents_flash_set('error', implode(' ', $res['errors'] ?? ['Upload failed.']));
            }
        }
        header('Location: index.php?page=setup_agent_edit&id=' . $agentId);
        exit;
    }
}

$countries = setup_agents_countries($mysqli);
$citiesAll = setup_agents_cities_all($mysqli);
$csrf = setup_agents_csrf_token();

$logoName = trim((string) ($agent['agent_logo_name'] ?? ''));
$logoUrl = '';
if ($logoName !== '') {
    $logoPath = setup_agents_upload_dir() . '/' . basename($logoName);
    if (is_file($logoPath)) {
        $logoUrl = setup_agents_upload_url_path() . '/' . rawurlencode(basename($logoName));
    }
}

require __DIR__ . '/../../../includes/header.php';
require __DIR__ . '/../../../includes/nav.php';
?>

<div class="flex gap-6 w-full">
    <aside class="hidden lg:block w-72 shrink-0">
        <?php require __DIR__ . '/../sidebar.php'; ?>
    </aside>

    <main class="flex-1 px-4">
        <div class="space-y-4">
            <?php
            $breadcrumbParentLabel = 'Agent Setup';
            $breadcrumbParentHref = 'index.php?page=setup_agents';
            $breadcrumbCurrent = 'Edit Agent';
            require __DIR__ . '/../../../includes/breadcrumb.php';
            ?>
            <div class="flex flex-wrap gap-2">
                <a href="index.php?page=setup_agents" class="btn btn-sm btn-outline gap-1.5">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                    Back to agent list
                </a>
                <a href="index.php?page=setup_agent_view&id=<?= $agentId ?>" class="btn btn-sm btn-ghost">View</a>
            </div>

            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body space-y-4">
                    <?php if ($errors): ?>
                        <div class="alert alert-error"><span><?= h(implode(' ', $errors)) ?></span></div>
                    <?php endif; ?>

                    <div class="max-w-4xl mx-auto border border-base-300 rounded-box overflow-hidden p-4 space-y-3">
                        <div class="font-semibold text-sm">Logo</div>
                        <div class="flex flex-wrap items-start gap-4">
                            <?php if ($logoUrl !== ''): ?>
                                <img src="<?= h($logoUrl) ?>" alt="Agent logo" class="max-h-24 rounded border border-base-300 bg-base-200 object-contain">
                            <?php else: ?>
                                <div class="w-48 h-24 flex items-center justify-center rounded border border-dashed border-base-300 bg-base-200/40">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5 text-base-content/40" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V7z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 12.5l1.5-1.5 3 3 2-2 2 2"/>
                                    </svg>
                                </div>
                            <?php endif; ?>

                            <div class="flex flex-col gap-2 justify-start">
                                <div class="text-[10px] text-base-content/50 break-all max-w-[220px]">
                                    <?= $logoName !== '' ? h($logoName) : 'No logo uploaded yet' ?>
                                </div>
                                <a href="index.php?page=setup_agent_view&id=<?= $agentId ?>" class="btn btn-sm btn-primary w-fit">
                                    Change logo
                                </a>
                            </div>
                        </div>
                    </div>

                    <form method="post" action="index.php?page=setup_agent_edit&id=<?= $agentId ?>" class="space-y-3">
                        <input type="hidden" name="_token" value="<?= h($csrf) ?>">
                        <input type="hidden" name="action" value="update_agent">
                        <input type="hidden" name="agent_id" value="<?= $agentId ?>">
                        <div class="max-w-4xl mx-auto border border-base-300 rounded-box overflow-hidden">
                            <div class="px-4 py-2.5 bg-linear-to-r from-sky-700 to-cyan-600 text-white font-bold text-base">Edit Agent</div>
                            <div class="divide-y divide-base-300">
                                <?php
                                $rowClass = 'grid grid-cols-1 md:grid-cols-[190px_1fr] items-center gap-2 px-3 py-1.5';
                                $labelClass = 'font-semibold text-sm text-base-content/80';
                                $inputClass = 'input input-bordered input-sm text-sm w-full max-w-xl';
                                $selectClass = 'select select-bordered select-sm text-sm w-full max-w-xs';
                                ?>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Agent Name :</label><input name="agent_name" value="<?= h($form['agent_name']) ?>" class="<?= $inputClass ?>" required></div>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Agent Code :</label><input name="agent_code" value="<?= h($form['agent_code']) ?>" class="<?= $inputClass ?>"></div>
                                <div class="<?= $rowClass ?> md:items-start"><label class="<?= $labelClass ?> pt-1">Address :</label><textarea name="agent_address" class="textarea textarea-bordered textarea-sm text-sm w-full max-w-xl" rows="3"><?= h($form['agent_address']) ?></textarea></div>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Country :</label>
                                    <select name="agent_country" id="edit-agent-country" class="<?= $selectClass ?>" required>
                                        <option value="">Select country</option>
                                        <?php foreach ($countries as $c): ?>
                                            <option value="<?= h($c) ?>" <?= $form['agent_country'] === $c ? 'selected' : '' ?>><?= h($c) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">City :</label>
                                    <select name="agent_city" id="edit-agent-city" class="<?= $selectClass ?>" required>
                                        <option value="">Select city</option>
                                        <?php foreach ($citiesAll as $ct): ?>
                                            <option value="<?= h($ct['city_name']) ?>" data-country="<?= h($ct['city_country_name']) ?>" <?= $form['agent_city'] === $ct['city_name'] ? 'selected' : '' ?>><?= h($ct['city_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Email :</label><input type="email" name="agent_email" value="<?= h($form['agent_email']) ?>" class="<?= $inputClass ?>" required></div>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Contact No :</label><input name="agent_contact_no" value="<?= h($form['agent_contact_no']) ?>" class="<?= $inputClass ?>" required></div>
                                <div class="<?= $rowClass ?>"><label class="<?= $labelClass ?>">Mobile No :</label><input name="agent_mobile_no" value="<?= h($form['agent_mobile_no']) ?>" class="<?= $inputClass ?>" required></div>
                            </div>
                        </div>
                        <div class="flex justify-center"><button class="btn btn-primary" type="submit">Update</button></div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
(function () {
    var co = document.getElementById('edit-agent-country');
    var ci = document.getElementById('edit-agent-city');
    if (!co || !ci) return;
    function sync() {
        var c = co.value || '';
        Array.prototype.forEach.call(ci.options, function (opt, i) {
            if (i === 0) return;
            opt.hidden = c !== '' && (opt.getAttribute('data-country') || '') !== c;
        });
        var sel = ci.options[ci.selectedIndex];
        if (sel && sel.hidden) ci.selectedIndex = 0;
    }
    co.addEventListener('change', sync);
    sync();
})();
</script>

<?php require __DIR__ . '/../../../includes/footer.php'; ?>
