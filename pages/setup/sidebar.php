<?php
// Sidebar for Setup module.
$setupPage = $_GET['page'] ?? 'setup';
$agentPages = ['setup_agents', 'setup_agent_create', 'setup_agent_view', 'setup_agent_edit'];
$supplierPages = ['setup_suppliers', 'setup_supplier_create', 'setup_supplier_view', 'setup_supplier_edit'];
$vehiclePages = ['setup_vehicles', 'setup_vehicle_create', 'setup_vehicle_view', 'setup_vehicle_edit'];
$servicePages = ['setup_services', 'setup_service_create', 'setup_service_view', 'setup_service_edit'];
$locationPages = ['setup_locations', 'setup_location_create', 'setup_location_view', 'setup_location_edit'];
$zonePages = ['setup_zones', 'setup_zone_create', 'setup_zone_view', 'setup_zone_edit'];
$countryPages = ['setup_countries', 'setup_country_create', 'setup_country_view', 'setup_country_edit'];
$cityPages = ['setup_cities', 'setup_city_create', 'setup_city_view', 'setup_city_edit'];
$designationPages = ['setup_designations', 'setup_designation_create', 'setup_designation_view', 'setup_designation_edit'];
$departmentPages = ['setup_departments', 'setup_department_create', 'setup_department_view', 'setup_department_edit'];
$driverPages = ['setup_drivers', 'setup_driver_create', 'setup_driver_view', 'setup_driver_edit', 'setup_driver_password_list', 'setup_driver_password_form'];
$vehicleTypePages = ['setup_vehicle_types', 'setup_vehicle_type_create', 'setup_vehicle_type_view', 'setup_vehicle_type_edit'];
$smsLabelPages = ['setup_sms_labels', 'setup_sms_label_view', 'setup_sms_label_edit'];
$itineraryLabelPages = ['setup_itinerary_labels', 'setup_itinerary_label_edit'];
$isAgent = in_array($setupPage, $agentPages, true);
$isSupplier = in_array($setupPage, $supplierPages, true);
$isVehicle = in_array($setupPage, $vehiclePages, true);
$isService = in_array($setupPage, $servicePages, true);
$isLocation = in_array($setupPage, $locationPages, true);
$isZone = in_array($setupPage, $zonePages, true);
$isCountry = in_array($setupPage, $countryPages, true);
$isCity = in_array($setupPage, $cityPages, true);
$isDesignation = in_array($setupPage, $designationPages, true);
$isDepartment = in_array($setupPage, $departmentPages, true);
$isDriver = in_array($setupPage, $driverPages, true);
$isVehicleType = in_array($setupPage, $vehicleTypePages, true);
$isSmsLabel = in_array($setupPage, $smsLabelPages, true);
$isItineraryLabel = in_array($setupPage, $itineraryLabelPages, true);
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
        <li class="<?= $isZone ? 'active' : '' ?>"><a href="index.php?page=setup_zones">Zone Setup</a></li>
        <li class="<?= $isCountry ? 'active' : '' ?>"><a href="index.php?page=setup_countries">Country Setup</a></li>
        <li class="<?= $isCity ? 'active' : '' ?>"><a href="index.php?page=setup_cities">City Setup</a></li>
        <li class="<?= $isDesignation ? 'active' : '' ?>"><a href="index.php?page=setup_designations">Designation Setup</a></li>
        <li class="<?= $isDepartment ? 'active' : '' ?>"><a href="index.php?page=setup_departments">Department Setup</a></li>
        <li class="<?= $isDriver ? 'active' : '' ?>"><a href="index.php?page=setup_drivers">Driver Setup</a></li>
        <li class="<?= $isVehicleType ? 'active' : '' ?>"><a href="index.php?page=setup_vehicle_types">Vehicle Type Setup</a></li>
        <li class="<?= $isItineraryLabel ? 'active' : '' ?>"><a href="index.php?page=setup_itinerary_labels">Itinerary Label</a></li>
        <li class="<?= $isSmsLabel ? 'active' : '' ?>"><a href="index.php?page=setup_sms_labels">SMS Label</a></li>
    </ul>
</aside>
