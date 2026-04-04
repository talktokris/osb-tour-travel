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
/* Service date: text field + calendar icon inside (same pattern as SMS / Date of Birth affordance) */
.file-ts-dob-wrap {
    position: relative;
    max-width: 11.5rem;
    width: 100%;
}
.file-ts-dob-wrap .file-ts-dob-input {
    width: 100%;
    height: 1.85rem;
    min-height: 1.85rem;
    padding-left: 0.5rem;
    padding-right: 2.15rem;
    font-size: 12px;
    line-height: 1.2;
    border: 1px solid #94a3b8;
    border-radius: 0.25rem;
    background: #fff;
    box-sizing: border-box;
    cursor: pointer;
}
.file-ts-dob-wrap .file-ts-dob-input:hover {
    border-color: #64748b;
}
.file-ts-dob-wrap .file-ts-dob-input:focus {
    outline: 2px solid color-mix(in oklab, #00a651 40%, transparent);
    outline-offset: 1px;
    border-color: #1a6b5c;
}
.file-ts-dob-wrap .file-ts-dob-input::placeholder {
    color: #94a3b8;
}
.file-ts-dob-cal-btn {
    position: absolute;
    right: 2px;
    top: 50%;
    transform: translateY(-50%);
    width: 1.85rem;
    height: 1.5rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin: 0;
    padding: 0;
    border: 0;
    border-radius: 0.2rem;
    background: transparent;
    cursor: pointer;
    color: #334155;
}
.file-ts-dob-cal-btn:hover {
    background: rgba(26, 107, 92, 0.1);
    color: #1a6b5c;
}
.file-ts-dob-cal-btn:focus-visible {
    outline: 2px solid #00a651;
    outline-offset: 1px;
}
/* Flatpickr calendar — OSB teal / green, softer shadow */
.flatpickr-calendar {
    border-radius: 10px;
    border: 1px solid #1a6b5c;
    box-shadow: 0 14px 44px rgba(15, 23, 42, 0.14), 0 4px 14px rgba(26, 107, 92, 0.1);
    font-family: inherit;
}
.flatpickr-months {
    border-radius: 10px 10px 0 0;
    overflow: hidden;
}
.flatpickr-months .flatpickr-month {
    background: #1a6b5c !important;
    color: #fff !important;
    fill: #fff !important;
}
.flatpickr-current-month .flatpickr-monthDropdown-months {
    background: rgba(255, 255, 255, 0.95) !important;
    color: #14532d !important;
    font-weight: 600;
    border-radius: 4px;
    border: 1px solid rgba(255, 255, 255, 0.4);
}
.flatpickr-current-month input.cur-year {
    background: rgba(255, 255, 255, 0.15) !important;
    color: #fff !important;
    font-weight: 600;
    border-radius: 4px;
}
.flatpickr-months .flatpickr-prev-month svg,
.flatpickr-months .flatpickr-next-month svg {
    fill: #fff;
}
.flatpickr-months .flatpickr-prev-month:hover svg,
.flatpickr-months .flatpickr-next-month:hover svg {
    fill: #bbf7d0;
}
.flatpickr-weekdays {
    background: #f0fdf4;
    border-bottom: 1px solid #d1fae5;
}
span.flatpickr-weekday {
    color: #166534;
    font-weight: 600;
    font-size: 0.72rem;
}
.flatpickr-day {
    border-radius: 6px;
    font-weight: 500;
}
.flatpickr-day.selected,
.flatpickr-day.startRange,
.flatpickr-day.endRange {
    background: #00a651 !important;
    border-color: #00a651 !important;
    color: #fff !important;
    box-shadow: 0 2px 6px rgba(0, 166, 81, 0.35);
}
.flatpickr-day.today {
    border-color: #0d9488;
    color: #0f766e;
    font-weight: 700;
}
.flatpickr-day:hover:not(.selected):not(.flatpickr-disabled) {
    background: #ecfdf5;
    border-color: #6ee7b7;
    color: #14532d;
}
.flatpickr-day.prevMonthDay,
.flatpickr-day.nextMonthDay {
    color: #cbd5e1;
}
select.file-ts-zone-empty {
    color: #64748b;
    background-color: #f8fafc;
}
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<div class="flex gap-6 w-full pb-6">
    <aside class="hidden lg:block w-72 shrink-0"><?php require __DIR__ . '/sidebar.php'; ?></aside>
    <main class="flex-1 min-w-0">
        <div class="space-y-4">
            <?php $breadcrumbCurrent = 'Transfer search'; require __DIR__ . '/../../includes/breadcrumb.php'; ?>

            <?php if ($flash): ?>
                <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : ($flash['type'] === 'warning' ? 'alert-warning' : 'alert-info') ?>"><span><?= h($flash['message']) ?></span></div>
            <?php endif; ?>

            <form method="post" action="index.php?page=file_results" id="file-search-form" class="file-ts-form">
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
                            <select name="from_zone" id="fa-from-zone" class="select select-bordered w-full bg-white file-ts-zone-sel"><option value="">Select Zone</option></select>
                        </div>
                    </div>

                    <div class="file-ts-row">
                        <div class="file-ts-lbl">Drop Off :</div>
                        <div class="file-ts-ctl file-ts-pair">
                            <select name="to_city" id="fa-to-city" class="select select-bordered w-full bg-white" required></select>
                            <select name="to_location" id="fa-to-location" class="select select-bordered w-full bg-white" required></select>
                            <select name="to_zone" id="fa-to-zone" class="select select-bordered w-full bg-white file-ts-zone-sel"><option value="">Select Zone</option></select>
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
                        <div class="file-ts-ctl">
                            <div class="file-ts-dob-wrap">
                                <input type="text" name="service_date" id="fa-service-date" class="file-ts-dob-input" placeholder="dd-mm-yyyy" value="<?= h($c['service_date']) ?>" required autocomplete="off" inputmode="numeric">
                                <button type="button" class="file-ts-dob-cal-btn" id="fa-service-date-cal" title="Open calendar" aria-label="Open calendar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                </button>
                            </div>
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

    function resetZoneSelect(sel) {
        sel.classList.remove('file-ts-zone-empty');
        sel.title = '';
        sel.innerHTML = '<option value=\"\">Select Zone</option>';
    }

    /** After a location is chosen: populate zones or show empty-state (still submits zone as empty). */
    function fillZoneSelect(sel, items, cur, emptyLabel) {
        if (items.length === 0) {
            sel.classList.add('file-ts-zone-empty');
            sel.innerHTML = '';
            var o = document.createElement('option');
            o.value = '';
            o.textContent = '(No zones for this location)';
            sel.appendChild(o);
            sel.title = 'No zone records for this location.';
            return;
        }
        sel.classList.remove('file-ts-zone-empty');
        sel.title = '';
        fillSelect(sel, items, cur, emptyLabel);
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

    /** Restore pick-up or drop-off chain: city → locations → zones (strict order). */
    function hydrateSide(locSel, zoneSel, savedCity, savedLoc, savedZone) {
        if (!savedCity) {
            locSel.innerHTML = '<option value=\"\">Select Location</option>';
            resetZoneSelect(zoneSel);
            return Promise.resolve();
        }
        return get(api + '&action=locations&q=' + encodeURIComponent(savedCity)).then(function (d) {
            fillSelect(locSel, d.items || [], savedLoc, 'Select Location');
            if (!savedLoc) {
                resetZoneSelect(zoneSel);
                return Promise.resolve();
            }
            return get(api + '&action=zones&q=' + encodeURIComponent(savedLoc)).then(function (zd) {
                fillZoneSelect(zoneSel, zd.items || [], savedZone, 'Select Zone');
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
            tloc.innerHTML = '<option value=\"\">Select Location</option>';
            resetZoneSelect(fzone);
            resetZoneSelect(tzone);
            refreshServices();
            return;
        }
        get(api + '&action=cities&q=' + encodeURIComponent(country)).then(function (d) {
            var items = d.items || [];
            fillSelect(fcity, items, '', 'Select City');
            fillSelect(tcity, items, '', 'Select City');
            floc.innerHTML = '<option value=\"\">Select Location</option>';
            tloc.innerHTML = '<option value=\"\">Select Location</option>';
            resetZoneSelect(fzone);
            resetZoneSelect(tzone);
            refreshServices();
        });
    });

    fcity.addEventListener('change', function () {
        if (!fcity.value) {
            floc.innerHTML = '<option value=\"\">Select Location</option>';
            resetZoneSelect(fzone);
            refreshServices();
            return;
        }
        get(api + '&action=locations&q=' + encodeURIComponent(fcity.value)).then(function (d) {
            fillSelect(floc, d.items || [], '', 'Select Location');
            resetZoneSelect(fzone);
            refreshServices();
        });
    });

    floc.addEventListener('change', function () {
        if (!floc.value) {
            resetZoneSelect(fzone);
            refreshServices();
            return;
        }
        get(api + '&action=zones&q=' + encodeURIComponent(floc.value)).then(function (d) {
            fillZoneSelect(fzone, d.items || [], '', 'Select Zone');
            refreshServices();
        });
    });

    tcity.addEventListener('change', function () {
        if (!tcity.value) {
            tloc.innerHTML = '<option value=\"\">Select Location</option>';
            resetZoneSelect(tzone);
            refreshServices();
            return;
        }
        get(api + '&action=locations&q=' + encodeURIComponent(tcity.value)).then(function (d) {
            fillSelect(tloc, d.items || [], '', 'Select Location');
            resetZoneSelect(tzone);
            refreshServices();
        });
    });

    tloc.addEventListener('change', function () {
        if (!tloc.value) {
            resetZoneSelect(tzone);
            refreshServices();
            return;
        }
        get(api + '&action=zones&q=' + encodeURIComponent(tloc.value)).then(function (d) {
            fillZoneSelect(tzone, d.items || [], '', 'Select Zone');
            refreshServices();
        });
    });

    fzone.addEventListener('change', refreshServices);
    tzone.addEventListener('change', refreshServices);

    if (fc.value) {
        toHidden.value = fc.value;
        get(api + '&action=cities&q=' + encodeURIComponent(fc.value)).then(function (d) {
            var items = d.items || [];
            fillSelect(fcity, items, saved.from_city, 'Select City');
            fillSelect(tcity, items, saved.to_city, 'Select City');
            return Promise.all([
                hydrateSide(floc, fzone, saved.from_city, saved.from_location, saved.from_zone),
                hydrateSide(tloc, tzone, saved.to_city, saved.to_location, saved.to_zone)
            ]);
        }).then(function () {
            refreshServices();
        });
    }

    function sumPax() {
        var a = parseInt(document.getElementById('fa-adults').value, 10) || 0;
        var c = parseInt(document.getElementById('fa-children').value, 10) || 0;
        document.getElementById('fa-pax').value = String(a + c);
    }
    document.getElementById('fa-adults').addEventListener('keyup', sumPax);
    document.getElementById('fa-children').addEventListener('keyup', sumPax);

    var dateInp = document.getElementById('fa-service-date');
    var dateCalBtn = document.getElementById('fa-service-date-cal');
    if (typeof flatpickr === 'function' && dateInp) {
        var fpSvc = flatpickr(dateInp, {
            dateFormat: 'd-m-Y',
            allowInput: true,
            clickOpens: true,
            animate: true
        });
        if (dateCalBtn && fpSvc) {
            dateCalBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                fpSvc.open();
            });
        }
    }
})();
</script>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
