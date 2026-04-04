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

<style>
/* Legacy-style compact transfer search (cream panels, teal border, label column + colon) */
.file-ts-form { max-width: 52rem; }
.file-ts-panel {
    border: 1px solid #1a6b5c;
    background: #fffce8;
    padding: 6px 10px 8px;
    margin-bottom: 8px;
}
.file-ts-panel h2 {
    color: #00a651;
    font-size: 0.95rem;
    font-weight: 700;
    margin: 0 0 6px;
    line-height: 1.2;
    letter-spacing: 0.01em;
}
.file-ts-row {
    display: grid;
    grid-template-columns: minmax(10.5rem, max-content) minmax(0, 1fr);
    column-gap: 8px;
    row-gap: 2px;
    align-items: center;
    margin-bottom: 4px;
}
.file-ts-row:last-child { margin-bottom: 0; }
.file-ts-lbl {
    font-size: 11px;
    line-height: 1.25;
    color: #222;
    align-self: center;
    white-space: nowrap;
    padding-right: 4px;
}
.file-ts-ctl .select,
.file-ts-ctl .input {
    min-height: 1.85rem;
    height: 1.85rem;
    font-size: 12px;
    padding-top: 0.15rem;
    padding-bottom: 0.15rem;
}
/* City | Location | Zone (zone column wider for long hotel names) */
.file-ts-pair {
    display: grid;
    grid-template-columns: minmax(0, 0.88fr) minmax(0, 1fr) minmax(0, 1.35fr);
    gap: 5px;
    width: 100%;
}
.file-ts-veh {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px 14px;
}
.file-ts-veh .select { width: auto; min-width: 7.5rem; }
.file-ts-veh .file-ts-units { width: 3.25rem; min-width: 3.25rem; }
.file-ts-radios {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 12px;
}
.file-ts-radios label {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    cursor: pointer;
    margin: 0;
    white-space: nowrap;
}
.file-ts-radios input { width: 14px; height: 14px; }
.file-ts-search.btn {
    margin-top: 2px;
    box-shadow: 0 1px 2px rgba(0,0,0,.12);
}
.file-ts-pax .input { max-width: 5rem; }
/* Flatpickr: match compact form height */
.file-ts-date-wrap { max-width: 11rem; }
.file-ts-date-wrap .flatpickr-input { cursor: pointer; }
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

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

            <form method="post" action="index.php?page=file" id="file-search-form" class="file-ts-form">
                <input type="hidden" name="_token" value="<?= h($csrf) ?>">
                <input type="hidden" name="file_do_search" value="1">
                <input type="hidden" name="to_country" id="fa-to-country" value="<?= h($c['to_country'] !== '' ? $c['to_country'] : $c['from_country']) ?>">

                <div class="file-ts-panel">
                    <h2>Transfer Search</h2>

                    <div class="file-ts-row">
                        <div class="file-ts-lbl">Country :</div>
                        <div class="file-ts-ctl">
                            <select name="from_country" id="fa-from-country" class="select select-bordered w-full bg-white" required>
                                <option value="">Select Country</option>
                                <?php foreach ($countries as $cn): ?>
                                    <option value="<?= h($cn) ?>" <?= $c['from_country'] === $cn ? 'selected' : '' ?>><?= h($cn) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="file-ts-row">
                        <div class="file-ts-lbl">Pick Up :</div>
                        <div class="file-ts-ctl file-ts-pair">
                            <select name="from_city" id="fa-from-city" class="select select-bordered w-full bg-white" required></select>
                            <select name="from_location" id="fa-from-location" class="select select-bordered w-full bg-white" required></select>
                            <select name="from_zone" id="fa-from-zone" class="select select-bordered w-full bg-white"><option value="">Select Zone</option></select>
                        </div>
                    </div>

                    <div class="file-ts-row">
                        <div class="file-ts-lbl">Drop Off :</div>
                        <div class="file-ts-ctl file-ts-pair">
                            <select name="to_city" id="fa-to-city" class="select select-bordered w-full bg-white" required></select>
                            <select name="to_location" id="fa-to-location" class="select select-bordered w-full bg-white" required></select>
                            <select name="to_zone" id="fa-to-zone" class="select select-bordered w-full bg-white"><option value="">Select Zone</option></select>
                        </div>
                    </div>

                    <div class="file-ts-row">
                        <div class="file-ts-lbl">Services :</div>
                        <div class="file-ts-ctl">
                            <select name="service_name" id="fa-service" class="select select-bordered w-full bg-white">
                                <option value="">Select Services</option>
                            </select>
                        </div>
                    </div>

                    <div class="file-ts-row">
                        <div class="file-ts-lbl">Vehicle Type :</div>
                        <div class="file-ts-ctl">
                            <select name="vehicle_type" class="select select-bordered w-full bg-white max-w-xs">
                                <option value="">Select Vehicle Type</option>
                                <?php foreach ($vehicleTypes as $vt): ?>
                                    <option value="<?= h($vt) ?>" <?= $c['vehicle_type'] === $vt ? 'selected' : '' ?>><?= h($vt) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="file-ts-row">
                        <div class="file-ts-lbl">No of Unit :</div>
                        <div class="file-ts-ctl file-ts-veh">
                            <select name="no_of_vachile" class="select select-bordered bg-white file-ts-units">
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <option value="<?= $i ?>" <?= $c['no_of_vachile'] === (string) $i ? 'selected' : '' ?>><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                            <div class="file-ts-radios">
                                <label><input type="radio" name="service_cat" value="Private" <?= ($c['service_cat'] ?? '') === 'SIC' ? '' : 'checked' ?>> Private</label>
                                <label><input type="radio" name="service_cat" value="SIC" <?= ($c['service_cat'] ?? '') === 'SIC' ? 'checked' : '' ?>> SIC</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="file-ts-panel">
                    <div class="file-ts-row">
                        <div class="file-ts-lbl">Service Date :</div>
                        <div class="file-ts-ctl file-ts-date-wrap">
                            <input type="text" name="service_date" id="fa-service-date" class="input input-bordered w-full bg-white" placeholder="dd-mm-yyyy" value="<?= h($c['service_date']) ?>" required autocomplete="off">
                        </div>
                    </div>
                    <div class="file-ts-row file-ts-pax">
                        <div class="file-ts-lbl">Number of Adults :</div>
                        <div class="file-ts-ctl"><input type="text" name="adults" id="fa-adults" class="input input-bordered bg-white" value="<?= h($c['adults']) ?>"></div>
                    </div>
                    <div class="file-ts-row file-ts-pax">
                        <div class="file-ts-lbl">Number of Children :</div>
                        <div class="file-ts-ctl"><input type="text" name="children" id="fa-children" class="input input-bordered bg-white" value="<?= h($c['children']) ?>"></div>
                    </div>
                    <div class="file-ts-row file-ts-pax">
                        <div class="file-ts-lbl">Number of Pax :</div>
                        <div class="file-ts-ctl"><input type="text" name="no_of_pax" id="fa-pax" class="input input-bordered bg-white" value="<?= h($c['no_of_pax']) ?>"></div>
                    </div>
                </div>

                <button type="submit" class="btn btn-sm btn-success text-white px-6 file-ts-search">Search</button>
            </form>

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

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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
        svc.innerHTML = '<option value=\"\">Select Services</option>';
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
        var country = fc.value;
        if (!country) {
            fillSelect(fcity, [], '', 'Select City');
            fillSelect(tcity, [], '', 'Select City');
            floc.innerHTML = '<option value=\"\">Select Location</option>';
            fzone.innerHTML = '<option value=\"\">Select Zone</option>';
            tloc.innerHTML = '<option value=\"\">Select Location</option>';
            tzone.innerHTML = '<option value=\"\">Select Zone</option>';
            refreshServices();
            return;
        }
        get(api + '&action=cities&q=' + encodeURIComponent(country)).then(function (d) {
            var items = d.items || [];
            fillSelect(fcity, items, saved.from_city, 'Select City');
            floc.innerHTML = '<option value=\"\">Select Location</option>';
            fzone.innerHTML = '<option value=\"\">Select Zone</option>';
            fillSelect(tcity, items, saved.to_city, 'Select City');
            tloc.innerHTML = '<option value=\"\">Select Location</option>';
            tzone.innerHTML = '<option value=\"\">Select Zone</option>';
        });
    });

    fcity.addEventListener('change', function () {
        get(api + '&action=locations&q=' + encodeURIComponent(fcity.value)).then(function (d) {
            fillSelect(floc, d.items || [], saved.from_location, 'Select Location');
            fzone.innerHTML = '<option value=\"\">Select Zone</option>';
        });
    });

    floc.addEventListener('change', function () {
        if (!floc.value) {
            fzone.innerHTML = '<option value=\"\">Select Zone</option>';
            refreshServices();
            return;
        }
        get(api + '&action=zones&q=' + encodeURIComponent(floc.value)).then(function (d) {
            fillSelect(fzone, d.items || [], saved.from_zone, 'Select Zone');
            refreshServices();
        });
    });

    tcity.addEventListener('change', function () {
        get(api + '&action=locations&q=' + encodeURIComponent(tcity.value)).then(function (d) {
            fillSelect(tloc, d.items || [], saved.to_location, 'Select Location');
            tzone.innerHTML = '<option value=\"\">Select Zone</option>';
        });
    });

    tloc.addEventListener('change', function () {
        if (!tloc.value) {
            tzone.innerHTML = '<option value=\"\">Select Zone</option>';
            refreshServices();
            return;
        }
        get(api + '&action=zones&q=' + encodeURIComponent(tloc.value)).then(function (d) {
            fillSelect(tzone, d.items || [], saved.to_zone, 'Select Zone');
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

    if (typeof flatpickr === 'function') {
        flatpickr('#fa-service-date', { dateFormat: 'd-m-Y', allowInput: true, clickOpens: true });
    }
})();
</script>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
