<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/home_dashboard_service.php';

$currentPage = 'home_browse';

require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';
$pattern = home_dashboard_resolve_supplier_like_pattern();
$letterRaw = isset($_GET['letter']) ? (string) $_GET['letter'] : '';

$suppliers = $pattern !== null
    ? home_dashboard_suppliers_matching_name_pattern($mysqli, $pattern)
    : [];

$strip = home_dashboard_az_letter_strip();
?>

<main class="w-full max-w-none pb-6">
        <div class="space-y-4">
            <?php $breadcrumbCurrent = 'Supplier directory'; require __DIR__ . '/../../includes/breadcrumb.php'; ?>

            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h2 class="card-title text-lg">Suppliers A–Z</h2>
                        <a href="index.php?page=home" class="link link-primary text-sm">Main home (pending by file)</a>
                    </div>

                    <p class="text-sm text-base-content/70">Pick a letter to list suppliers whose names match the pattern, then open pending bookings for that supplier.</p>

                    <div class="flex flex-wrap gap-x-1 gap-y-2 items-center text-sm font-semibold">
                        <?php foreach ($strip as $item): ?>
                            <?php
                            $cnt = home_dashboard_count_pending_supplier_like($mysqli, $item['pattern']);
                            $isActive = $pattern !== null && $item['pattern'] === $pattern;
                            $cls = $cnt >= 1 ? 'text-error' : 'text-base-content/30';
                            $href = 'index.php?' . http_build_query([
                                'page' => 'home_browse',
                                'letter' => home_dashboard_letter_query_value($item['label'], $item['pattern']),
                            ]);
                            ?>
                            <a href="<?= h($href) ?>"
                               class="<?= h($cls) ?> no-underline hover:underline px-1 <?= $isActive ? 'underline decoration-2' : '' ?>"><?= h($item['label']) ?></a>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($pattern === null): ?>
                        <p class="text-base-content/70 text-sm">Select a letter above.</p>
                    <?php elseif ($suppliers === []): ?>
                        <p class="text-base-content/70">No suppliers match this filter.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto rounded-box border border-base-200">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Supplier</th>
                                        <th>Country</th>
                                        <th>City</th>
                                        <th>Pending</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($suppliers as $row): ?>
                                        <?php
                                        $sname = trim((string) ($row['supplier_name'] ?? ''));
                                        if ($sname === '') {
                                            continue;
                                        }
                                        $pc = home_dashboard_count_pending_for_supplier_exact($mysqli, $sname);
                                        ?>
                                        <tr>
                                            <td>
                                                <a class="link link-primary font-medium" href="index.php?page=home_supplier_bookings&amp;supplier=<?= h(rawurlencode($sname)) ?>"><?= h($sname) ?></a>
                                            </td>
                                            <td><?= h((string) ($row['supplier_country'] ?? '')) ?></td>
                                            <td><?= h((string) ($row['supplier_city'] ?? '')) ?></td>
                                            <td><?= (int) $pc ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
</main>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
