<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/invoice_module_service.php';

$im = $mode ?? invoice_module_normalize_mode((string) ($_GET['mode'] ?? 'outstanding_agent'));

$link = static function (string $m, string $label) use ($im): void {
    $liClass = 'w-full' . ($im === $m ? ' active' : '');
    $href = 'index.php?page=invoice&mode=' . rawurlencode($m);
    echo '<li class="' . h($liClass) . '"><a href="' . h($href) . '">' . h($label) . '</a></li>';
};
?>
<aside class="module-sidebar">
    <div class="module-sidebar__head">Invoice Menu</div>
    <ul class="menu">
        <?php
        $link('outstanding_agent', 'Outstanding Agent');
        $link('outstanding_supplier', 'Outstanding Supplier');
        $link('paid_agent', 'Paid Invoice Agent');
        $link('paid_supplier', 'Paid Invoice Supplier');
        $link('statement_agent', 'Statement by Agent');
        $link('statement_supplier', 'Statement by Supplier');
        ?>
    </ul>
</aside>

