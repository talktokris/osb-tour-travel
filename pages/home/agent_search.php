<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/home_dashboard_service.php';
require_once __DIR__ . '/../../includes/setup_agents_service.php';

$currentPage = 'home_agent_search';

require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';

$searchWord = '';
$agents = [];
$formError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string) ($_POST['_token'] ?? '');
    if (!home_dashboard_csrf_validate($token)) {
        $formError = 'Invalid request token.';
        $searchWord = trim((string) ($_POST['search_word'] ?? ''));
    } else {
        $searchWord = trim((string) ($_POST['search_word'] ?? ''));
        $agents = home_dashboard_search_agents($mysqli, $searchWord);
    }
}

$csrf = home_dashboard_csrf_token();
$flash = home_dashboard_flash_get();

$agentLogoPlaceholderSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="160" height="96" viewBox="0 0 160 96" role="img" aria-label="No logo"><rect width="160" height="96" fill="#f1f5f9" rx="6"/><rect x="28" y="22" width="104" height="52" fill="#e2e8f0" rx="4"/><circle cx="64" cy="46" r="10" fill="#cbd5e1"/><path fill="#cbd5e1" d="M44 66h72v8H44z"/><text x="80" y="86" text-anchor="middle" fill="#94a3b8" font-size="9" font-family="system-ui,Segoe UI,sans-serif">No logo</text></svg>';
$agentLogoPlaceholderDataUri = 'data:image/svg+xml,' . rawurlencode($agentLogoPlaceholderSvg);
?>
<style>
    .home-agent-input-join {
        display: flex;
        align-items: center;
        width: 100%;
        min-height: 2.75rem;
        height: 2.75rem;
        padding: 0 0.75rem;
        gap: 0.5rem;
        border: 1px solid color-mix(in oklab, var(--color-base-content, #64748b) 20%, transparent);
        border-radius: var(--rounded-btn, 0.5rem);
        background: var(--color-base-100, #fff);
        box-sizing: border-box;
    }
    .home-agent-input-join:focus-within {
        border-color: var(--color-primary, #2563eb);
        outline: 2px solid color-mix(in oklab, var(--color-primary, #2563eb) 35%, transparent);
        outline-offset: 1px;
    }
    .home-agent-input-join input {
        flex: 1 1 0%;
        min-width: 0;
        height: 100%;
        border: 0;
        background: transparent;
        font-size: 0.875rem;
        line-height: 1.25rem;
        outline: none;
    }
    .home-agent-input-join svg {
        flex-shrink: 0;
        width: 1rem;
        height: 1rem;
        opacity: 0.45;
    }
    .home-agent-suggest-wrap {
        position: relative;
        width: 100%;
    }
    .home-agent-suggest {
        position: absolute;
        left: 0;
        right: 0;
        top: 100%;
        margin-top: 2px;
        z-index: 50;
        background: #ffffff;
        border: 1px solid #c5ccd6;
        border-radius: 0.375rem;
        box-shadow: 0 6px 20px rgba(15, 23, 42, 0.12);
        max-height: 240px;
        overflow-y: auto;
        box-sizing: border-box;
    }
    .home-agent-suggest[hidden] {
        display: none !important;
    }
    .home-agent-suggest__item {
        display: block;
        width: 100%;
        text-align: left;
        padding: 0.45rem 0.65rem;
        font-size: 0.8125rem;
        line-height: 1.35;
        color: #1e293b;
        background: #ffffff;
        border: 0;
        border-bottom: 1px solid #eef2f6;
        cursor: pointer;
    }
    .home-agent-suggest__item:last-child {
        border-bottom: 0;
    }
    .home-agent-suggest__item:hover,
    .home-agent-suggest__item:focus-visible {
        background: #f1f5f9;
        outline: none;
    }
</style>

<main class="w-full max-w-none pb-6">
        <div class="space-y-4">
            <?php $breadcrumbCurrent = 'Agent search'; require __DIR__ . '/../../includes/breadcrumb.php'; ?>

            <?php if ($flash): ?>
                <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-error' ?> shadow-sm">
                    <span><?= h((string) $flash['message']) ?></span>
                </div>
            <?php endif; ?>
            <?php if ($formError !== ''): ?>
                <div class="alert alert-error shadow-sm"><span><?= h($formError) ?></span></div>
            <?php endif; ?>

            <div class="border border-[#c5ccd6] bg-base-100 rounded-sm p-4 mb-4">
                <div class="space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h2 class="text-lg font-bold" style="color:#009900;font-family:Arial,Helvetica,sans-serif;">Agent Search</h2>
                        <a href="index.php?page=home" class="btn btn-ghost btn-sm">Back to home</a>
                    </div>
                    <form method="post" action="index.php?page=home_agent_search" class="flex flex-wrap items-end gap-3">
                        <input type="hidden" name="_token" value="<?= h($csrf) ?>">
                        <div class="form-control flex-1 min-w-[220px]">
                            <div class="home-agent-suggest-wrap">
                                <div class="home-agent-input-join" role="group" aria-label="Search agents">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                    <input type="text" name="search_word" id="home-agent-search-input" placeholder="Code or name"
                                           value="<?= h($searchWord) ?>" maxlength="100" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="home-agent-search-suggest">
                                </div>
                                <div id="home-agent-search-suggest" class="home-agent-suggest" role="listbox" hidden></div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-sm px-6 text-white font-semibold border-0" style="background:linear-gradient(180deg,#5cb85c,#449d44);">Search</button>
                    </form>
                </div>
            </div>

            <div class="card bg-base-100 shadow border border-base-300">
                <div class="card-body space-y-4">

                    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && $searchWord !== ''): ?>
                        <?php if ($agents === []): ?>
                            <p class="text-base-content/70">No result found.</p>
                        <?php else: ?>
                            <div class="overflow-x-auto rounded-box border border-base-200">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Agent</th>
                                            <th>Details</th>
                                            <th>Login as</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($agents as $i => $row): ?>
                                            <?php
                                            $name = trim((string) ($row['agent_name'] ?? ''));
                                            $logo = trim((string) ($row['agent_logo_name'] ?? ''));
                                            $logoUrl = $logo !== '' && is_file(setup_agents_upload_dir() . '/' . basename($logo))
                                                ? setup_agents_upload_url_path() . '/' . rawurlencode(basename($logo))
                                                : '';
                                            $logoSrc = $logoUrl !== '' ? $logoUrl : $agentLogoPlaceholderDataUri;
                                            ?>
                                            <tr class="<?= $i % 2 === 0 ? 'bg-base-200/40' : '' ?>">
                                                <td><?= $i + 1 ?></td>
                                                <td>
                                                    <div class="font-medium"><?= h($name) ?></div>
                                                    <img src="<?= h($logoSrc) ?>"
                                                         alt=""
                                                         width="160"
                                                         height="96"
                                                         class="mt-2 block max-w-[160px] max-h-24 w-auto h-auto rounded border border-base-300 bg-base-200/50 object-contain object-center"
                                                         loading="lazy"
                                                         decoding="async"
                                                         <?php if ($logoUrl !== ''): ?>data-fallback="<?= h($agentLogoPlaceholderDataUri) ?>" onerror="this.onerror=null;if(this.dataset.fallback)this.src=this.dataset.fallback;"<?php endif; ?>>
                                                </td>
                                                <td class="text-sm max-w-md">
                                                    <div><span class="text-base-content/60">Address:</span> <?= h((string) ($row['agent_address'] ?? '')) ?></div>
                                                    <div><span class="text-base-content/60">Country:</span> <?= h((string) ($row['agent_country'] ?? '')) ?></div>
                                                    <div><span class="text-base-content/60">City:</span> <?= h((string) ($row['agent_city'] ?? '')) ?></div>
                                                    <div><span class="text-base-content/60">Email:</span> <?= h((string) ($row['agent_email'] ?? '')) ?></div>
                                                    <div><span class="text-base-content/60">Phone:</span> <?= h((string) ($row['agent_contact_no'] ?? '')) ?></div>
                                                </td>
                                                <td>
                                                    <a href="index.php?page=agent_login&amp;login_id=<?= h(rawurlencode($name)) ?>" class="btn btn-sm btn-outline">Login as agent</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-base-content/60 text-sm m-0">Use the search box above to find agents by name or code.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
</main>

<script>
(function () {
    var input = document.getElementById('home-agent-search-input');
    var panel = document.getElementById('home-agent-search-suggest');
    var wrap = input && input.closest('.home-agent-suggest-wrap');
    if (!input || !panel || !wrap) return;
    var t;
    var onDocDown;
    function hide() {
        input.setAttribute('aria-expanded', 'false');
        panel.hidden = true;
        panel.innerHTML = '';
        if (onDocDown) {
            document.removeEventListener('mousedown', onDocDown);
            onDocDown = null;
        }
    }
    function render(items) {
        panel.innerHTML = '';
        if (!items.length) {
            hide();
            return;
        }
        items.forEach(function (s) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'home-agent-suggest__item';
            btn.setAttribute('role', 'option');
            btn.textContent = s;
            btn.addEventListener('click', function () {
                input.value = s;
                hide();
                input.focus();
            });
            panel.appendChild(btn);
        });
        panel.hidden = false;
        input.setAttribute('aria-expanded', 'true');
        if (!onDocDown) {
            onDocDown = function (e) {
                if (!wrap.contains(e.target)) hide();
            };
            document.addEventListener('mousedown', onDocDown);
        }
    }
    input.addEventListener('input', function () {
        clearTimeout(t);
        var q = input.value.trim();
        if (q.length < 1) {
            hide();
            return;
        }
        t = setTimeout(function () {
            fetch('index.php?page=home_autocomplete&type=agent&q=' + encodeURIComponent(q))
                .then(function (r) { return r.json(); })
                .then(function (arr) {
                    if (!Array.isArray(arr)) return;
                    render(arr.slice(0, 25));
                })
                .catch(function () { hide(); });
        }, 200);
    });
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') hide();
        if (e.key === 'Enter' && !panel.hidden && panel.firstElementChild) {
            e.preventDefault();
            panel.firstElementChild.click();
        }
    });
})();
</script>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
