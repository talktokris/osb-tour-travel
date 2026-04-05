<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/report_module_service.php';

$sm = $mode ?? report_module_normalize_mode((string) ($_GET['mode'] ?? 'agent'));

$link = static function (string $m, string $label) use ($sm): void {
    $liClass = 'w-full' . ($sm === $m ? ' active' : '');
    $href = 'index.php?page=report&mode=' . rawurlencode($m);
    echo '<li class="' . h($liClass) . '"><a href="' . h($href) . '">' . h($label) . '</a></li>';
};
?>
<aside class="module-sidebar">
    <div class="module-sidebar__head">Report Menu</div>
    <ul class="menu">
        <?php
        $link('agent', 'Report by Agent');
        $link('supplier', 'Report by Supplier');
        $link('vehicle_type', 'Report by Vehicle Type');
        $link('private_sic', 'Report by Private/SIC');
        $link('driver_name', 'Report by Driver Name');
        $link('vehicle_no', 'Report by Vehicle No.');
        $link('city', 'Report by City');
        $link('tour_arrival', 'Report by Tour Type');
        $link('statement_agent', 'Statement by Agent');
        $link('statement_supplier', 'Statement by Supplier');
        ?>
    </ul>
</aside>
