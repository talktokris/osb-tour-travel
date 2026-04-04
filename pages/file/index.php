<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/file_module_service.php';

$currentPage = 'file';

if (!file_module_has_agent()) {
    file_module_render_agent_required();
}

if (isset($_GET['new'])) {
    file_module_state();
    file_module_set_file_count_no(null);
}

require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';

$csrf = file_module_csrf_token();
$flash = file_module_flash_get();
$state = file_module_state();
$c = $state['criteria'];
$results = null;
$searchError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_do_search'])) {
    $token = (string) ($_POST['_token'] ?? '');
    if (!file_module_csrf_validate($token)) {
        $searchError = 'Invalid request token.';
    } else {
        $post = [
            'from_country' => (string) ($_POST['from_country'] ?? ''),
            'from_city' => (string) ($_POST['from_city'] ?? ''),
            'from_location' => (string) ($_POST['from_location'] ?? ''),
            'from_zone' => (string) ($_POST['from_zone'] ?? ''),
            'to_country' => (string) ($_POST['to_country'] ?? ''),
            'to_city' => (string) ($_POST['to_city'] ?? ''),
            'to_location' => (string) ($_POST['to_location'] ?? ''),
            'to_zone' => (string) ($_POST['to_zone'] ?? ''),
            'service_name' => (string) ($_POST['service_name'] ?? ''),
            'vehicle_type' => (string) ($_POST['vehicle_type'] ?? ''),
            'no_of_vachile' => (string) ($_POST['no_of_vachile'] ?? '1'),
            'service_cat' => (string) ($_POST['service_cat'] ?? 'Private'),
            'service_date' => (string) ($_POST['service_date'] ?? ''),
            'adults' => (string) ($_POST['adults'] ?? ''),
            'children' => (string) ($_POST['children'] ?? ''),
            'no_of_pax' => (string) ($_POST['no_of_pax'] ?? ''),
        ];
        if ($post['to_country'] === '') {
            $post['to_country'] = $post['from_country'];
        }
        if ($post['from_country'] === '' || $post['from_city'] === '' || $post['from_location'] === ''
            || $post['to_city'] === '' || $post['to_location'] === '' || $post['service_date'] === ''
            || $post['no_of_pax'] === '') {
            $searchError = 'Please complete country, pick-up, drop-off, service date, and pax.';
        } elseif (!in_array($post['service_cat'], ['Private', 'SIC'], true)) {
            $searchError = 'Choose Private or SIC.';
        } else {
            file_module_save_criteria($post);
            $c = file_module_state()['criteria'];
            $results = file_module_search_services($mysqli, $c);
        }
    }
} else {
    $c = $state['criteria'];
}

$countries = file_module_countries($mysqli);
$vehicleTypes = file_module_vehicle_types($mysqli);
$apiBase = 'index.php?page=file_api';
?>

<div class="flex gap-6 w-full pb-6">
    <aside class="hidden lg:block w-72 shrink-0"><?php require __DIR__ . '/sidebar.php'; ?></aside>
    <main class="flex-1 min-w-0">
        <div class="space-y-4">
            <?php $breadcrumbCurrent = 'Transfer search'; require __DIR__ . '/../../includes/breadcrumb.php'; ?>

            <?php if ($flash): ?>
                <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-info' ?>"><span><?= h($flash['message']) ?></span></div>
            <?php endif; ?>
            <?php if ($searchError !== ''): ?>
                <div class="alert alert-warning"><span><?= h($searchError) ?></span></div>
            <?php endif; ?>

            <div class="rounded-sm border border-base-300 bg-[#ffffee] p-4 sm:p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-success mb-3">Transfer Search</h2>
                <form method="post" action="index.php?page=file" id="file-search-form" class="space-y-3 max-w-4xl">
                    <input type="hidden" name="_token" value="<?= h($csrf) ?>">
                    <input type="hidden" name="file_do_search" value="1">

                    <label class="form-control w-full max-w-md">
                        <span class="label-text">Country</span>
                        <select name="from_country" id="fa-from-country" class="select select-bordered select-sm w-full bg-white" required>
                            <option value="">Select country</option>
                            <?php foreach ($countries as $cn): ?>
                                <option value="<?= h($cn) ?>" <?= $c['from_country'] === $cn ? 'selected' : '' ?>><?= h($cn) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <input type="hidden" name="to_country" id="fa-to-country" value="<?= h($c['to_country'] !== '' ? $c['to_country'] : $c['from_country']) ?>">

                    <div class="flex flex-wrap gap-3 items-end">
                        <label class="form-control min-w-[10rem]">
                            <span class="label-text">Pick up — City</span>
                            <select name="from_city" id="fa-from-city" class="select select-bordered select-sm w-full bg-white" required></select>
                        </label>
                        <label class="form-control min-w-[10rem]">
                            <span class="label-text">Location</span>
                            <select name="from_location" id="fa-from-location" class="select select-bordered select-sm w-full bg-white" required></select>
                        </label>
                        <label class="form-control min-w-[10rem]">
                            <span class="label-text">Zone</span>
                            <select name="from_zone" id="fa-from-zone" class="select select-bordered select-sm w-full bg-white"><option value="">Select zone</option></select>
                        </label>
                    </div>

                    <div class="flex flex-wrap gap-3 items-end">
                        <label class="form-control min-w-[10rem]">
                            <span class="label-text">Drop off — City</span>
                            <select name="to_city" id="fa-to-city" class="select select-bordered select-sm w-full bg-white" required></select>
                        </label>
                        <label class="form-control min-w-[10rem]">
                            <span class="label-text">Location</span>
                            <select name="to_location" id="fa-to-location" class="select select-bordered select-sm w-full bg-white" required></select>
                        </label>
                        <label class="form-control min-w-[10rem]">
                            <span class="label-text">Zone</span>
                            <select name="to_zone" id="fa-to-zone" class="select select-bordered select-sm w-full bg-white"><option value="">Select zone</option></select>
                        </label>
                    </div>

                    <label class="form-control w-full max-w-xl">
                        <span class="label-text">Services</span>
                        <select name="service_name" id="fa-service" class="select select-bordered select-sm w-full bg-white">
                            <option value="">All matching services</option>
                        </select>
                    </label>

                    <div class="flex flex-wrap gap-3 items-end">
                        <label class="form-control min-w-[12rem]">
                            <span class="label-text">Vehicle type</span>
                            <select name="vehicle_type" class="select select-bordered select-sm w-full bg-white">
                                <option value="">Any</option>
                                <?php foreach ($vehicleTypes as $vt): ?>
                                    <option value="<?= h($vt) ?>" <?= $c['vehicle_type'] === $vt ? 'selected' : '' ?>><?= h($vt) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label class="form-control w-24">
                            <span class="label-text">No of unit</span>
                            <select name="no_of_vachile" class="select select-bordered select-sm w-full bg-white">
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <option value="<?= $i ?>" <?= $c['no_of_vachile'] === (string) $i ? 'selected' : '' ?>><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </label>
                        <div class="flex gap-4 items-center pt-6">
                            <label class="label cursor-pointer gap-2"><input type="radio" name="service_cat" value="Private" class="radio radio-sm" <?= ($c['service_cat'] ?? '') === 'SIC' ? '' : 'checked' ?>> Private</label>
                            <label class="label cursor-pointer gap-2"><input type="radio" name="service_cat" value="SIC" class="radio radio-sm" <?= ($c['service_cat'] ?? '') === 'SIC' ? 'checked' : '' ?>> SIC</label>
                        </div>
                    </div>

                    <div class="rounded-sm border border-warning/40 bg-base-100/80 p-3 space-y-2 max-w-xl">
                        <label class="form-control">
                            <span class="label-text">Service date</span>
                            <input type="text" name="service_date" class="input input-bordered input-sm w-full bg-white" placeholder="dd-mm-yyyy" value="<?= h($c['service_date']) ?>" required>
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <label class="form-control w-28"><span class="label-text">Adults</span><input type="text" name="adults" id="fa-adults" class="input input-bordered input-sm bg-white" value="<?= h($c['adults']) ?>"></label>
                            <label class="form-control w-28"><span class="label-text">Children</span><input type="text" name="children" id="fa-children" class="input input-bordered input-sm bg-white" value="<?= h($c['children']) ?>"></label>
                            <label class="form-control w-28"><span class="label-text">Pax</span><input type="text" name="no_of_pax" id="fa-pax" class="input input-bordered input-sm bg-white" value="<?= h($c['no_of_pax']) ?>"></label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-sm btn-success text-white px-6">Search</button>
                </form>
            </div>

            <?php if (is_array($results)): ?>
                <div class="rounded-sm border border-base-300 bg-[#ffccdf]/40 p-4 overflow-x-auto">
                    <h3 class="font-semibold text-success mb-2">Results</h3>
                    <?php if ($results === []): ?>
                        <p class="text-success font-medium">No result found</p>
                    <?php else: ?>
                        <?php
                        $adults = (int) $c['adults'];
                        $children = (int) $c['children'];
                        ?>
                        <table class="table table-sm table-zebra bg-base-100">
                            <thead><tr><th>No.</th><th>Service</th><th>Vehicle</th><th>Price</th><th>Max pax</th><th></th></tr></thead>
                            <tbody>
                            <?php $n = 1; foreach ($results as $row): ?>
                                <?php
                                $pr = file_module_compute_prices($row, $adults, $children);
                                $sid = (int) ($row['service_id'] ?? 0);
                                ?>
                                <tr>
                                    <td><?= $n++ ?></td>
                                    <td><?= h((string) ($row['service_name_english'] ?? '')) ?></td>
                                    <td><?= h((string) ($row['vehicle_type'] ?? '')) ?></td>
                                    <td class="font-semibold"><?= h($pr['selling']) ?></td>
                                    <td><?= h($c['no_of_pax']) ?></td>
                                    <td><a class="btn btn-xs btn-success text-white" href="index.php?page=file_book&amp;service_id=<?= $sid ?>">Book now</a></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
(function () {
    var api = <?= json_encode($apiBase, JSON_THROW_ON_ERROR) ?>;
    var saved = <?= json_encode([
        'from_city' => $c['from_city'],
        'from_location' => $c['from_location'],
        'from_zone' => $c['from_zone'],
        'to_city' => $c['to_city'],
        'to_location' => $c['to_location'],
        'to_zone' => $c['to_zone'],
        'service_name' => $c['service_name'],
    ], JSON_THROW_ON_ERROR) ?>;

    function fillSelect(sel, items, cur, emptyLabel) {
        sel.innerHTML = '';
        var o = document.createElement('option');
        o.value = '';
        o.textContent = emptyLabel;
        sel.appendChild(o);
        items.forEach(function (t) {
            var op = document.createElement('option');
            op.value = t;
            op.textContent = t;
            if (cur && cur === t) op.selected = true;
            sel.appendChild(op);
        });
    }

    function get(url) {
        return fetch(url).then(function (r) { return r.json(); });
    }

    var fc = document.getElementById('fa-from-country');
    var fcity = document.getElementById('fa-from-city');
    var floc = document.getElementById('fa-from-location');
    var fzone = document.getElementById('fa-from-zone');
    var tcity = document.getElementById('fa-to-city');
    var tloc = document.getElementById('fa-to-location');
    var tzone = document.getElementById('fa-to-zone');
    var svc = document.getElementById('fa-service');
    var toHidden = document.getElementById('fa-to-country');

    function refreshServices() {
        var fromL = floc.value;
        var toL = tloc.value;
        svc.innerHTML = '<option value=\"\">All matching services</option>';
        if (!fromL || !toL) return;
        get(api + '&action=services_between&q=' + encodeURIComponent(fromL) + '&to=' + encodeURIComponent(toL)).then(function (d) {
            if (!d.items) return;
            d.items.forEach(function (t) {
                var op = document.createElement('option');
                op.value = t;
                op.textContent = t;
                if (saved.service_name === t) op.selected = true;
                svc.appendChild(op);
            });
        });
    }

    fc.addEventListener('change', function () {
        toHidden.value = fc.value;
        get(api + '&action=cities&q=' + encodeURIComponent(fc.value)).then(function (d) {
            fillSelect(fcity, d.items || [], saved.from_city, 'Select city');
            floc.innerHTML = '<option value=\"\">Select location</option>';
            fzone.innerHTML = '<option value=\"\">Select zone</option>';
        });
    });

    fcity.addEventListener('change', function () {
        get(api + '&action=locations&q=' + encodeURIComponent(fcity.value)).then(function (d) {
            fillSelect(floc, d.items || [], saved.from_location, 'Select location');
            fzone.innerHTML = '<option value=\"\">Select zone</option>';
        });
    });

    floc.addEventListener('change', function () {
        get(api + '&action=zones&q=' + encodeURIComponent(floc.value)).then(function (d) {
            fillSelect(fzone, d.items || [], saved.from_zone, 'Select zone');
            refreshServices();
        });
    });

    get(api + '&action=cities_all').then(function (d) {
        fillSelect(tcity, d.items || [], saved.to_city, 'Select city');
        tloc.innerHTML = '<option value=\"\">Select location</option>';
        tzone.innerHTML = '<option value=\"\">Select zone</option>';
    });

    tcity.addEventListener('change', function () {
        get(api + '&action=locations&q=' + encodeURIComponent(tcity.value)).then(function (d) {
            fillSelect(tloc, d.items || [], saved.to_location, 'Select location');
            tzone.innerHTML = '<option value=\"\">Select zone</option>';
        });
    });

    tloc.addEventListener('change', function () {
        get(api + '&action=zones&q=' + encodeURIComponent(tloc.value)).then(function (d) {
            fillSelect(tzone, d.items || [], saved.to_zone, 'Select zone');
            refreshServices();
        });
    });

    fzone.addEventListener('change', refreshServices);
    tzone.addEventListener('change', refreshServices);

    if (fc.value) {
        fc.dispatchEvent(new Event('change'));
        setTimeout(function () {
            if (saved.from_city) {
                fcity.value = saved.from_city;
                fcity.dispatchEvent(new Event('change'));
            }
        }, 200);
        setTimeout(function () {
            if (saved.from_location) {
                floc.value = saved.from_location;
                floc.dispatchEvent(new Event('change'));
            }
        }, 450);
    }
    setTimeout(function () {
        if (saved.to_city) {
            tcity.value = saved.to_city;
            tcity.dispatchEvent(new Event('change'));
        }
    }, 300);
    setTimeout(function () {
        if (saved.to_location) {
            tloc.value = saved.to_location;
            tloc.dispatchEvent(new Event('change'));
        }
    }, 600);

    function sumPax() {
        var a = parseInt(document.getElementById('fa-adults').value, 10) || 0;
        var c = parseInt(document.getElementById('fa-children').value, 10) || 0;
        document.getElementById('fa-pax').value = String(a + c);
    }
    document.getElementById('fa-adults').addEventListener('keyup', sumPax);
    document.getElementById('fa-children').addEventListener('keyup', sumPax);
})();
</script>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
