<?php
// Sidebar for the Bookings menu (logged-in pages).
// Expects $currentPage to be available.
?>
<aside class="bg-base-100 border-r border-base-300 w-72 min-h-full ml-6 rounded-2xl">
    <div class="p-4 border-b border-base-300">
        <div class="text-sm font-semibold text-base-content/70">Navigation</div>
    </div>

    <ul class="menu menu-compact p-2">
        <li class="<?= ($currentPage ?? 'home') === 'home' ? 'active' : '' ?>">
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

