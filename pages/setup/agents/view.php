<?php

declare(strict_types=1);

if (!isset($mysqli)) {
    require __DIR__ . '/../../../config.php';
}
require_once __DIR__ . '/../../../includes/setup_agents_service.php';

$agentId = (int) ($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload_logo') {
    $postId = (int) ($_POST['agent_id'] ?? 0);
    if ($postId !== $agentId || $agentId <= 0) {
        setup_agents_flash_set('error', 'Invalid agent.');
        header('Location: index.php?page=setup_agents');
        exit;
    }
    if (!setup_agents_csrf_validate((string) ($_POST['_token'] ?? ''))) {
        setup_agents_flash_set('error', 'Invalid request token.');
    } else {
        $res = setup_agents_save_logo($mysqli, $agentId, $_FILES['logo'] ?? []);
        if (!empty($res['ok'])) {
            setup_agents_flash_set('success', 'Logo updated successfully.');
        } else {
            setup_agents_flash_set('error', implode(' ', $res['errors'] ?? ['Upload failed.']));
        }
    }
    header('Location: index.php?page=setup_agent_view&id=' . $agentId);
    exit;
}

$agent = $agentId > 0 ? setup_agents_find($mysqli, $agentId) : null;
if (!$agent) {
    setup_agents_flash_set('error', 'Agent not found.');
    header('Location: index.php?page=setup_agents');
    exit;
}

$flash = setup_agents_flash_get();
$csrf = setup_agents_csrf_token();
$logoName = trim((string) ($agent['agent_logo_name'] ?? ''));
$logoUrl = '';
if ($logoName !== '') {
    $logoPath = setup_agents_upload_dir() . '/' . basename($logoName);
    if (is_file($logoPath)) {
        $logoUrl = setup_agents_upload_url_path() . '/' . rawurlencode(basename($logoName));
    } else {
        $logoUrl = '';
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
            $breadcrumbCurrent = 'View Agent';
            require __DIR__ . '/../../../includes/breadcrumb.php';
            ?>
            <div class="flex flex-wrap items-center gap-2">
                <a href="index.php?page=setup_agents" class="btn btn-sm btn-outline gap-1.5">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                    Back to agent list
                </a>
                <a href="index.php?page=setup_agent_edit&id=<?= $agentId ?>" class="btn btn-sm btn-success">Edit</a>
            </div>

            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body space-y-4">
                    <?php if (!empty($flash)): ?>
                        <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-error' ?>">
                            <span><?= h((string) $flash['message']) ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="max-w-4xl mx-auto border border-base-300 rounded-box overflow-hidden">
                        <div class="px-4 py-2.5 bg-linear-to-r from-sky-700 to-cyan-600 text-white font-bold text-base">View Agent</div>
                        <div class="p-4 border-b border-base-300 space-y-3">
                            <div class="font-semibold text-sm">Logo</div>
                            <?php if ($logoUrl !== ''): ?>
                                <div class="flex items-start gap-4 flex-wrap">
                                    <img src="<?= h($logoUrl) ?>" alt="Agent logo" class="max-h-24 rounded border border-base-300 bg-base-200 object-contain">
                                </div>
                            <?php else: ?>
                                <div class="max-w-xs">
                                    <div class="w-[220px] h-24 flex items-center justify-center rounded-lg border border-dashed border-base-300 bg-base-200/40">
                                        <div class="text-xs text-base-content/60 text-center px-2">
                                            No logo uploaded yet
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <form method="post" action="index.php?page=setup_agent_view&id=<?= $agentId ?>" enctype="multipart/form-data" class="flex flex-wrap items-end gap-2">
                                <input type="hidden" name="_token" value="<?= h($csrf) ?>">
                                <input type="hidden" name="action" value="upload_logo">
                                <input type="hidden" name="agent_id" value="<?= $agentId ?>">
                                <input type="file" name="logo" accept="image/jpeg,image/png,image/gif,image/webp" class="file-input file-input-bordered file-input-sm w-full max-w-md">
                                <button type="submit" class="btn btn-success btn-sm">Save logo</button>
                            </form>
                        </div>
                        <div class="divide-y divide-base-300">
                            <?php
                            $rowClass = 'grid grid-cols-1 md:grid-cols-[190px_1fr] items-center gap-2 px-3 py-1.5';
                            $labelClass = 'font-semibold text-sm text-base-content/80';
                            $valueClass = 'text-sm text-base-content';
                            ?>
                            <div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">Agent Name :</div><div class="<?= $valueClass ?>"><?= h((string) $agent['agent_name']) ?></div></div>
                            <div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">Agent Code :</div><div class="<?= $valueClass ?>"><?= h((string) $agent['agent_code']) ?></div></div>
                            <div class="<?= $rowClass ?> md:items-start"><div class="<?= $labelClass ?> pt-1">Address :</div><div class="<?= $valueClass ?> whitespace-pre-wrap"><?= h((string) $agent['agent_address']) ?></div></div>
                            <div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">Country :</div><div class="<?= $valueClass ?>"><?= h((string) $agent['agent_country']) ?></div></div>
                            <div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">City :</div><div class="<?= $valueClass ?>"><?= h((string) $agent['agent_city']) ?></div></div>
                            <div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">Email :</div><div class="<?= $valueClass ?>"><?= h((string) $agent['agent_email']) ?></div></div>
                            <div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">Contact No :</div><div class="<?= $valueClass ?>"><?= h((string) $agent['agent_contact_no']) ?></div></div>
                            <div class="<?= $rowClass ?>"><div class="<?= $labelClass ?>">Mobile No :</div><div class="<?= $valueClass ?>"><?= h((string) $agent['agent_mobile_no']) ?></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../../../includes/footer.php'; ?>
