<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/home_dashboard_service.php';

require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';

$currentPage = 'home_supplier_bookings';
$supplier = trim((string) ($_GET['supplier'] ?? ''));
$flash = home_dashboard_flash_get();
$csrf = home_dashboard_csrf_token();

$rows = $supplier !== '' ? home_dashboard_file_entries_pending_for_supplier($mysqli, $supplier) : [];
?>

<main class="w-full max-w-none pb-6">
        <div class="space-y-4">
            <?php $breadcrumbCurrent = 'Supplier bookings'; require __DIR__ . '/../../includes/breadcrumb.php'; ?>

            <?php if ($flash): ?>
                <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-error' ?> shadow-sm">
                    <span><?= h((string) $flash['message']) ?></span>
                </div>
            <?php endif; ?>

            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h2 class="card-title text-lg">Pending bookings<?= $supplier !== '' ? ' — ' . h($supplier) : '' ?></h2>
                        <a href="index.php?page=home_browse" class="btn btn-sm btn-outline">Supplier A–Z</a>
                    </div>

                    <?php if ($supplier === ''): ?>
                        <p class="text-base-content/70">No supplier selected.</p>
                    <?php elseif ($rows === []): ?>
                        <p class="text-base-content/70">No pending bookings for this supplier.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto rounded-box border border-base-200">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>File no</th>
                                        <th>Guest</th>
                                        <th>Agent</th>
                                        <th>Pickup</th>
                                        <th>Drop-off</th>
                                        <th>Service</th>
                                        <th>Date</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rows as $row): ?>
                                        <?php
                                        $fid = (int) ($row['file_id'] ?? 0);
                                        $guest = trim((string) ($row['first_name'] ?? '') . ' ' . (string) ($row['last_name'] ?? ''));
                                        ?>
                                        <tr>
                                            <td><?= h((string) ($row['file_no'] ?? '')) ?></td>
                                            <td><?= h(trim($guest)) ?></td>
                                            <td><?= h((string) ($row['agent_name'] ?? '')) ?></td>
                                            <td class="max-w-[140px] truncate" title="<?= h((string) ($row['from_location'] ?? '')) ?>"><?= h((string) ($row['from_location'] ?? '')) ?></td>
                                            <td class="max-w-[140px] truncate" title="<?= h((string) ($row['to_location'] ?? '')) ?>"><?= h((string) ($row['to_location'] ?? '')) ?></td>
                                            <td><?= h((string) ($row['service'] ?? '')) ?></td>
                                            <td><?= h((string) ($row['service_date'] ?? '')) ?></td>
                                            <td class="text-end whitespace-nowrap">
                                                <button type="button" class="btn btn-xs btn-success js-home-confirm" data-file-id="<?= $fid ?>">Confirm</button>
                                                <button type="button" class="btn btn-xs btn-error js-home-cancel-open" data-file-id="<?= $fid ?>">Cancel</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <form id="home-supplier-action-form" method="post" action="index.php?page=home_booking_action" class="hidden">
                            <input type="hidden" name="_token" value="<?= h($csrf) ?>">
                            <input type="hidden" name="file_id" id="home-supplier-action-file-id" value="">
                            <input type="hidden" name="action" id="home-supplier-action-action" value="">
                            <input type="hidden" name="return_to" value="supplier">
                            <input type="hidden" name="return_supplier" value="<?= h($supplier) ?>">
                        </form>

                        <dialog id="home-supplier-cancel-dialog" class="agent-delete-dialog">
                            <div class="agent-delete-dialog__surface">
                                <h3 class="font-bold text-lg mb-1">Cancel booking?</h3>
                                <p class="agent-delete-dialog__message">This marks the booking as cancelled (status only). Continue?</p>
                                <div class="agent-delete-dialog__actions">
                                    <button type="button" class="btn btn-outline" id="home-supplier-cancel-no">No</button>
                                    <button type="button" class="btn btn-error" id="home-supplier-cancel-yes">Yes, cancel</button>
                                </div>
                            </div>
                        </dialog>

                        <script>
                        (function () {
                            var form = document.getElementById('home-supplier-action-form');
                            var fid = document.getElementById('home-supplier-action-file-id');
                            var act = document.getElementById('home-supplier-action-action');
                            var dlg = document.getElementById('home-supplier-cancel-dialog');
                            var noBtn = document.getElementById('home-supplier-cancel-no');
                            var yesBtn = document.getElementById('home-supplier-cancel-yes');
                            if (!form || !fid || !act || !dlg || !noBtn || !yesBtn) return;
                            document.querySelectorAll('.js-home-confirm').forEach(function (btn) {
                                btn.addEventListener('click', function () {
                                    fid.value = this.getAttribute('data-file-id') || '';
                                    act.value = 'confirm';
                                    form.submit();
                                });
                            });
                            document.querySelectorAll('.js-home-cancel-open').forEach(function (btn) {
                                btn.addEventListener('click', function () {
                                    fid.value = this.getAttribute('data-file-id') || '';
                                    act.value = 'cancel';
                                    dlg.showModal();
                                });
                            });
                            noBtn.addEventListener('click', function () { dlg.close(); });
                            yesBtn.addEventListener('click', function () { dlg.close(); form.submit(); });
                        })();
                        </script>
                    <?php endif; ?>
                </div>
            </div>
        </div>
</main>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
