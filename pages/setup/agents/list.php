<?php

declare(strict_types=1);

if (!isset($mysqli)) {
    require __DIR__ . '/../../../config.php';
}
require_once __DIR__ . '/../../../includes/setup_agents_service.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_agent') {
    $token = (string) ($_POST['_token'] ?? '');
    $agentId = (int) ($_POST['agent_id'] ?? 0);

    if (!setup_agents_csrf_validate($token)) {
        setup_agents_flash_set('error', 'Invalid request token.');
    } elseif ($agentId <= 0) {
        setup_agents_flash_set('error', 'Invalid agent.');
    } elseif (setup_agents_delete($mysqli, $agentId)) {
        setup_agents_flash_set('success', 'Agent deleted successfully.');
    } else {
        setup_agents_flash_set('error', 'Delete failed.');
    }

    header('Location: index.php?page=setup_agents');
    exit;
}

$fCountry = isset($_GET['f_country']) ? trim((string) $_GET['f_country']) : '';
$fCity = isset($_GET['f_city']) ? trim((string) $_GET['f_city']) : '';
$fAgentName = isset($_GET['f_agent']) ? trim((string) $_GET['f_agent']) : '';

$agents = setup_agents_list(
    $mysqli,
    $fCountry !== '' ? $fCountry : null,
    $fCity !== '' ? $fCity : null,
    $fAgentName !== '' ? $fAgentName : null
);
$countries = setup_agents_countries($mysqli);
$agentNames = setup_agents_distinct_names($mysqli);
$citiesAll = setup_agents_cities_all($mysqli);

$flash = setup_agents_flash_get();
$csrf = setup_agents_csrf_token();

require __DIR__ . '/../../../includes/header.php';
require __DIR__ . '/../../../includes/nav.php';
?>

<div class="flex gap-6 w-full">
    <aside class="hidden lg:block w-72 shrink-0">
        <?php require __DIR__ . '/../sidebar.php'; ?>
    </aside>

    <main class="flex-1 px-4">
        <div class="space-y-4">
            <?php $breadcrumbParentLabel = 'Setup'; $breadcrumbParentHref = 'index.php?page=setup'; $breadcrumbCurrent = 'Agent Setup'; require __DIR__ . '/../../../includes/breadcrumb.php'; ?>

            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body space-y-4">
                    <?php if (!empty($flash)): ?>
                        <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-error' ?>">
                            <span><?= h((string) $flash['message']) ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="max-w-5xl mx-auto border border-base-300 rounded-box overflow-hidden">
                        <div class="px-4 py-2.5 bg-linear-to-r from-sky-700 to-cyan-600 text-white font-bold text-base">Agent Setup</div>
                        <form method="get" action="index.php" class="p-4 space-y-3 border-b border-base-300 bg-base-100">
                            <input type="hidden" name="page" value="setup_agents">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <label class="form-control w-full">
                                    <span class="label-text text-sm">Search country</span>
                                    <select name="f_country" class="select select-bordered select-sm w-full" id="agent-filter-country">
                                        <option value="">All</option>
                                        <?php foreach ($countries as $c): ?>
                                            <option value="<?= h($c) ?>" <?= $fCountry === $c ? 'selected' : '' ?>><?= h($c) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label class="form-control w-full">
                                    <span class="label-text text-sm">Select city</span>
                                    <select name="f_city" class="select select-bordered select-sm w-full" id="agent-filter-city">
                                        <option value="">All</option>
                                        <?php foreach ($citiesAll as $ct): ?>
                                            <option value="<?= h($ct['city_name']) ?>" data-country="<?= h($ct['city_country_name']) ?>" <?= $fCity === $ct['city_name'] ? 'selected' : '' ?>><?= h($ct['city_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label class="form-control w-full">
                                    <span class="label-text text-sm">Agent name</span>
                                    <select name="f_agent" class="select select-bordered select-sm w-full">
                                        <option value="">All</option>
                                        <?php foreach ($agentNames as $n): ?>
                                            <option value="<?= h($n) ?>" <?= $fAgentName === $n ? 'selected' : '' ?>><?= h($n) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">Search</button>
                                <a href="index.php?page=setup_agents" class="btn btn-ghost btn-sm">Reset</a>
                            </div>
                        </form>
                    </div>

                    <div class="flex justify-center">
                        <a href="index.php?page=setup_agent_create" class="btn btn-success btn-sm">Create New Agent</a>
                    </div>

                    <div class="overflow-x-auto rounded-box border border-base-300">
                        <table class="table table-zebra table-sm">
                            <thead>
                                <tr>
                                    <th>S.N</th>
                                    <th>Agent Name</th>
                                    <th>Logo</th>
                                    <th>Code</th>
                                    <th>Country</th>
                                    <th>City</th>
                                    <th>Contact</th>
                                    <th>View</th>
                                    <th>Edit</th>
                                    <th>Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $sn = 1; foreach ($agents as $row): ?>
                                    <tr>
                                        <td><?= $sn++ ?></td>
                                        <td class="font-medium">
                                            <div><?= h((string) $row['agent_name']) ?></div>
                                            <?php
                                            $logoName = trim((string) ($row['agent_logo_name'] ?? ''));
                                            ?>
                                            <div class="text-[10px] text-base-content/50 mt-0.5 break-all max-w-[160px]">
                                                <?= $logoName !== '' ? h($logoName) : 'No logo' ?>
                                            </div>
                                        </td>
                                        <td class="w-24">
                                            <?php
                                            $logoNameCell = trim((string) ($row['agent_logo_name'] ?? ''));
                                            $logoPath = $logoNameCell !== '' ? setup_agents_upload_dir() . '/' . basename($logoNameCell) : '';
                                            if ($logoNameCell !== '' && $logoPath !== '' && is_file($logoPath)) {
                                                $logoUrl = setup_agents_upload_url_path() . '/' . rawurlencode(basename($logoNameCell));
                                                ?>
                                                <img
                                                    src="<?= h($logoUrl) ?>"
                                                    alt="Agent logo"
                                                    class="max-h-12 max-w-24 rounded border border-base-300 bg-base-200 object-contain"
                                                >
                                            <?php } else { ?>
                                                <div class="w-full h-12 flex items-center justify-center rounded border border-dashed border-base-300 bg-base-200/40">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 text-base-content/40" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V7z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 12.5l1.5-1.5 3 3 2-2 2 2" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 9h.01" />
                                                    </svg>
                                                </div>
                                            <?php } ?>
                                        </td>
                                        <td><?= h((string) $row['agent_code']) ?></td>
                                        <td><?= h((string) $row['agent_country']) ?></td>
                                        <td><?= h((string) $row['agent_city']) ?></td>
                                        <td class="text-sm max-w-xs whitespace-normal">
                                            <div><?= h((string) $row['agent_email']) ?></div>
                                            <div class="text-base-content/70"><?= h((string) $row['agent_contact_no']) ?> / <?= h((string) $row['agent_mobile_no']) ?></div>
                                            <div class="text-base-content/70"><?= h((string) $row['agent_address']) ?></div>
                                        </td>
                                        <td>
                                            <a class="btn btn-xs btn-info btn-outline" href="index.php?page=setup_agent_view&id=<?= (int) $row['agent_id'] ?>">View</a>
                                        </td>
                                        <td>
                                            <a class="btn btn-xs btn-outline" href="index.php?page=setup_agent_edit&id=<?= (int) $row['agent_id'] ?>">Edit</a>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-xs btn-error btn-outline js-delete-agent" data-agent-id="<?= (int) $row['agent_id'] ?>" data-agent-name="<?= h((string) $row['agent_name']) ?>">Delete</button>
                                        </td>
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

<form id="delete-agent-form" method="post" action="index.php?page=setup_agents" class="hidden" aria-hidden="true">
    <input type="hidden" name="_token" value="<?= h($csrf) ?>">
    <input type="hidden" name="action" value="delete_agent">
    <input type="hidden" name="agent_id" id="delete-agent-form-id" value="">
</form>

<style>
    #delete-agent-modal.agent-delete-dialog {
        margin: 0;
        max-width: none;
        max-height: none;
        width: 100%;
        height: 100%;
        padding: 1.25rem;
        border: none;
        background: transparent;
        display: none;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
    }
    #delete-agent-modal.agent-delete-dialog[open] { display: flex; }
    #delete-agent-modal.agent-delete-dialog::backdrop {
        background: rgba(15, 23, 42, 0.55);
        backdrop-filter: blur(4px);
    }
    #delete-agent-modal .agent-delete-dialog__surface {
        width: 100%;
        max-width: 26rem;
        background: var(--color-base-100, #ffffff);
        color: var(--color-base-content, #1e293b);
        border-radius: 1rem;
        border: 1px solid color-mix(in oklab, var(--color-base-content, #64748b) 12%, transparent);
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.35);
        padding: 1.5rem 1.5rem 1.25rem;
    }
    #delete-agent-modal .agent-delete-dialog__message {
        margin: 0 0 1.35rem;
        font-size: 0.875rem;
        line-height: 1.55;
        color: color-mix(in oklab, var(--color-base-content, #64748b) 78%, transparent);
    }
    #delete-agent-modal .agent-delete-dialog__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        justify-content: flex-end;
    }
</style>

<dialog id="delete-agent-modal" class="agent-delete-dialog">
    <div class="agent-delete-dialog__surface">
        <h3 class="font-bold text-lg mb-1">Delete agent?</h3>
        <p class="agent-delete-dialog__message" id="delete-agent-modal-message">Are you sure? This cannot be undone.</p>
        <div class="agent-delete-dialog__actions">
            <button type="button" class="btn btn-outline" id="delete-agent-modal-no">No</button>
            <button type="button" class="btn btn-error" id="delete-agent-modal-yes">Yes</button>
        </div>
    </div>
</dialog>

<script>
(function () {
    var countrySel = document.getElementById('agent-filter-country');
    var citySel = document.getElementById('agent-filter-city');
    if (countrySel && citySel) {
        function filterCities() {
            var c = countrySel.value || '';
            Array.prototype.forEach.call(citySel.options, function (opt, i) {
                if (i === 0) return;
                var cc = opt.getAttribute('data-country') || '';
                opt.hidden = c !== '' && cc !== c;
            });
            if (c !== '') {
                var sel = citySel.options[citySel.selectedIndex];
                if (sel && sel.hidden) citySel.value = '';
            }
        }
        countrySel.addEventListener('change', filterCities);
        filterCities();
    }

    var modal = document.getElementById('delete-agent-modal');
    var form = document.getElementById('delete-agent-form');
    var idInput = document.getElementById('delete-agent-form-id');
    var msg = document.getElementById('delete-agent-modal-message');
    var btnNo = document.getElementById('delete-agent-modal-no');
    var btnYes = document.getElementById('delete-agent-modal-yes');
    if (!modal || !form || !idInput || !msg || !btnNo || !btnYes) return;

    document.querySelectorAll('.js-delete-agent').forEach(function (btn) {
        btn.addEventListener('click', function () {
            idInput.value = this.getAttribute('data-agent-id') || '';
            var name = this.getAttribute('data-agent-name') || '';
            msg.textContent = name
                ? 'Are you sure you want to delete agent "' + name + '"? This cannot be undone.'
                : 'Are you sure you want to delete this agent? This cannot be undone.';
            modal.showModal();
        });
    });
    btnNo.addEventListener('click', function () { modal.close(); });
    btnYes.addEventListener('click', function () { modal.close(); form.submit(); });
})();
</script>

<?php require __DIR__ . '/../../../includes/footer.php'; ?>
