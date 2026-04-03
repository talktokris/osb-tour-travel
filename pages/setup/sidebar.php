<?php
// Sidebar for Setup module.
$setupPage = $_GET['page'] ?? 'setup';
$agentPages = ['setup_agents', 'setup_agent_create', 'setup_agent_view', 'setup_agent_edit'];
$isAgent = in_array($setupPage, $agentPages, true);
?>
<aside class="module-sidebar">
    <div class="module-sidebar__head">Setup Menu</div>
    <div class="px-3 pt-2 pb-1 text-[10px] uppercase tracking-wider text-slate-500 font-medium">Master Data</div>
    <ul class="menu">
        <li class="<?= $isAgent ? 'active' : '' ?>"><a href="index.php?page=setup_agents">Agent Setup</a></li>
        <li><a href="#">Supplier Setup</a></li>
        <li><a href="#">Vehicles Setup</a></li>
        <li><a href="#">Service Setup</a></li>
        <li><a href="#">Location Setup</a></li>
        <li><a href="#">Zone Setup</a></li>
        <li><a href="#">Country Setup</a></li>
        <li><a href="#">City Setup</a></li>
        <li><a href="#">Designation Setup</a></li>
        <li><a href="#">Department Setup</a></li>
        <li><a href="#">Driver Setup</a></li>
        <li><a href="#">Vehicle Type Setup</a></li>
        <li><a href="#">Itinerary Label</a></li>
        <li><a href="#">SMS Label</a></li>
    </ul>
</aside>
