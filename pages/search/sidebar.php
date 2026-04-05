<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/search_module_service.php';

$sm = $mode ?? search_module_normalize_mode((string) ($_GET['mode'] ?? 'agent'));

$link = static function (string $m, string $label) use ($sm): void {
    $active = $sm === $m ? ' !bg-sky-100 !border-sky-400 !text-sky-900 font-semibold' : '';
    $href = 'index.php?page=search&mode=' . rawurlencode($m);
    echo '<li class="w-full"><a href="' . h($href) . '" class="' . $active . '">' . h($label) . '</a></li>';
};
?>
<aside class="module-sidebar">
    <div class="module-sidebar__head">Search Menu</div>
    <ul class="menu">
        <?php
        $link('agent', 'Search by Agent');
        $link('supplier', 'Search by Supplier');
        $link('file_no', 'Search by File Number');
        $link('pax', 'Search by Pax Name');
        $link('vehicle_type', 'Search by Vehicle Type');
        $link('tour_type', 'Search by Tour Type');
        $link('driver', 'Search by Driver Name');
        $link('vehicle_no', 'Search by Vehicle No.');
        $link('service_date', 'Search by Service Date');
        $link('city', 'Search by City services');
        $link('arrival', 'Arrival, Dep, Tours, Over…');
        $link('combined', 'Search by (all fields)');
        $link('departure', 'Grouped — Departure');
        $link('overland', 'Grouped — Overland');
        $link('tours', 'Search by tour category');
        ?>
    </ul>
</aside>
