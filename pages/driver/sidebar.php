<?php

declare(strict_types=1);

/** @var string $driverSub */
$ds = isset($driverSub) ? (string) $driverSub : 'search';
$link = static function (string $sub, string $label) use ($ds): void {
    $liClass = 'w-full' . ($ds !== '' && $ds === $sub ? ' active' : '');
    $href = 'index.php?page=driver&sub=' . rawurlencode($sub);
    echo '<li class="' . h($liClass) . '"><a href="' . h($href) . '">' . h($label) . '</a></li>';
};
?>
<aside class="module-sidebar">
    <div class="module-sidebar__head">Driver Menu</div>
    <div class="px-3 py-2 text-[11px] uppercase tracking-wide text-slate-500 font-semibold">Jobs</div>
    <ul class="menu">
        <?php
        $link('search', 'Search Job');
        $link('pending', 'Pending Job');
        $link('completed', 'Completed Job');
        $link('recent', 'Recent Assigned Job');
        ?>
    </ul>
</aside>
