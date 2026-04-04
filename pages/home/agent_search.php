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
?>

<main class="w-full max-w-[1000px] mx-auto px-3 sm:px-4 pb-6">
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
                            <div class="relative">
                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                </span>
                                <input type="text" name="search_word" id="home-agent-search-input" placeholder="Code or name"
                                       class="input input-bordered w-full h-11 pl-9" value="<?= h($searchWord) ?>" maxlength="100" autocomplete="off" list="home-agent-search-datalist">
                                <datalist id="home-agent-search-datalist"></datalist>
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
                                            ?>
                                            <tr class="<?= $i % 2 === 0 ? 'bg-base-200/40' : '' ?>">
                                                <td><?= $i + 1 ?></td>
                                                <td>
                                                    <div class="font-medium"><?= h($name) ?></div>
                                                    <?php if ($logoUrl !== ''): ?>
                                                        <img src="<?= h($logoUrl) ?>" alt="" class="mt-2 max-h-16 rounded border border-base-300 object-contain">
                                                    <?php endif; ?>
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
    var list = document.getElementById('home-agent-search-datalist');
    if (!input || !list) return;
    var t;
    input.addEventListener('input', function () {
        clearTimeout(t);
        var q = input.value.trim();
        if (q.length < 1) {
            list.innerHTML = '';
            return;
        }
        t = setTimeout(function () {
            fetch('index.php?page=home_autocomplete&type=agent&q=' + encodeURIComponent(q))
                .then(function (r) { return r.json(); })
                .then(function (arr) {
                    if (!Array.isArray(arr)) return;
                    list.innerHTML = '';
                    arr.slice(0, 25).forEach(function (s) {
                        var o = document.createElement('option');
                        o.value = s;
                        list.appendChild(o);
                    });
                })
                .catch(function () {});
        }, 200);
    });
})();
</script>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
