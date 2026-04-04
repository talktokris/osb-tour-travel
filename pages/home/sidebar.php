<?php
// Sidebar for the Home menu (logged-in pages).
// Expects $currentPage to be available.
?>
<aside class="module-sidebar">
    <div class="module-sidebar__head">Navigation</div>
    <div class="px-3 py-2 text-[11px] uppercase tracking-wide text-slate-500 font-semibold">Main Menu</div>
    
    <ul class="menu">
        <?php
        $cp = $currentPage ?? 'home';
        $homeSidebar = $cp === 'home' || strncmp($cp, 'home_', 5) === 0;
        ?>
        <li class="<?= $homeSidebar ? 'active' : '' ?>">
            <a href="index.php?page=home">Home</a>
        </li>
        <li class="<?= ($currentPage ?? 'home') === 'agents' ? 'active' : '' ?>">
            <a href="index.php?page=agents">Agents</a>
        </li>
        <li class="<?= ($currentPage ?? 'home') === 'services' ? 'active' : '' ?>">
            <a href="index.php?page=services">Services</a>
        </li>
        <li class="<?= ($currentPage ?? 'home') === 'bookings' ? 'active' : '' ?>">
            <a href="index.php?page=bookings">Bookings</a>
        </li>
    </ul>
</aside>

