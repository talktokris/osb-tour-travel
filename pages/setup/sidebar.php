<?php
// Sidebar for Setup module.
$setupPage = $_GET['page'] ?? 'setup';
$agentPages = ['setup_agents', 'setup_agent_create', 'setup_agent_view', 'setup_agent_edit'];
$supplierPages = ['setup_suppliers', 'setup_supplier_create', 'setup_supplier_view', 'setup_supplier_edit'];
$vehiclePages = ['setup_vehicles', 'setup_vehicle_create', 'setup_vehicle_view', 'setup_vehicle_edit'];
$servicePages = ['setup_services', 'setup_service_create', 'setup_service_view', 'setup_service_edit'];
$locationPages = ['setup_locations', 'setup_location_create', 'setup_location_view', 'setup_location_edit'];
$isAgent = in_array($setupPage, $agentPages, true);
$isSupplier = in_array($setupPage, $supplierPages, true);
$isVehicle = in_array($setupPage, $vehiclePages, true);
$isService = in_array($setupPage, $servicePages, true);
$isLocation = in_array($setupPage, $locationPages, true);
?>
<aside class="module-sidebar">
    <div class="module-sidebar__head">Setup Menu</div>
    <div class="px-3 pt-2 pb-1 text-[10px] uppercase tracking-wider text-slate-500 font-medium">Master Data</div>
    <ul class="menu">
        <li class="<?= $isAgent ? 'active' : '' ?>"><a href="index.php?page=setup_agents">Agent Setup</a></li>
        <li class="<?= $isSupplier ? 'active' : '' ?>"><a href="index.php?page=setup_suppliers">Supplier Setup</a></li>
        <li class="<?= $isVehicle ? 'active' : '' ?>"><a href="index.php?page=setup_vehicles">Vehicles Setup</a></li>
        <li class="<?= $isService ? 'active' : '' ?>"><a href="index.php?page=setup_services">Service Setup</a></li>
        <li class="<?= $isLocation ? 'active' : '' ?>"><a href="index.php?page=setup_locations">Location Setup</a></li>
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
