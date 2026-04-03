<?php
declare(strict_types=1);

function setup_common_module_map(): array
{
    return [
        'zones' => [
            'label' => 'Zone Setup', 'single' => 'Zone',
            'service' => __DIR__ . '/../../../includes/setup_zones_service.php',
            'routes' => ['list' => 'setup_zones', 'create' => 'setup_zone_create', 'view' => 'setup_zone_view', 'edit' => 'setup_zone_edit'],
            'functions' => ['list' => 'setup_zones_list', 'find' => 'setup_zones_find', 'create' => 'setup_zones_create', 'update' => 'setup_zones_update', 'delete' => 'setup_zones_delete', 'flash_get' => 'setup_zones_flash_get', 'flash_set' => 'setup_zones_flash_set', 'csrf_token' => 'setup_zones_csrf_token', 'csrf_validate' => 'setup_zones_csrf_validate', 'locations' => 'setup_zones_locations'],
            'fields' => [['key' => 'location_name', 'label' => 'Location', 'type' => 'select_locations'], ['key' => 'zone_name', 'label' => 'Zone Name', 'type' => 'text'], ['key' => 'zone_name_arabic', 'label' => 'Zone Name Arabic', 'type' => 'text']],
            'list_columns' => ['location_name' => 'Location', 'zone_name' => 'Zone', 'zone_name_arabic' => 'Zone Arabic'],
            'primary_key' => 'zone_id',
        ],
        'countries' => [
            'label' => 'Country Setup', 'single' => 'Country',
            'service' => __DIR__ . '/../../../includes/setup_countries_service.php',
            'routes' => ['list' => 'setup_countries', 'create' => 'setup_country_create', 'view' => 'setup_country_view', 'edit' => 'setup_country_edit'],
            'functions' => ['list' => 'setup_countries_list', 'find' => 'setup_countries_find', 'create' => 'setup_countries_create', 'update' => 'setup_countries_update', 'delete' => 'setup_countries_delete', 'flash_get' => 'setup_countries_flash_get', 'flash_set' => 'setup_countries_flash_set', 'csrf_token' => 'setup_countries_csrf_token', 'csrf_validate' => 'setup_countries_csrf_validate'],
            'fields' => [['key' => 'country_name', 'label' => 'Country Name English', 'type' => 'text'], ['key' => 'country_shotform', 'label' => 'Country Name Arabic/Short', 'type' => 'text']],
            'list_columns' => ['country_name' => 'Country Name English', 'country_shotform' => 'Country Name Arabic'],
            'primary_key' => 'country_id',
        ],
        'cities' => [
            'label' => 'City Setup', 'single' => 'City',
            'service' => __DIR__ . '/../../../includes/setup_cities_service.php',
            'routes' => ['list' => 'setup_cities', 'create' => 'setup_city_create', 'view' => 'setup_city_view', 'edit' => 'setup_city_edit'],
            'functions' => ['list' => 'setup_cities_list', 'find' => 'setup_cities_find', 'create' => 'setup_cities_create', 'update' => 'setup_cities_update', 'delete' => 'setup_cities_delete', 'flash_get' => 'setup_cities_flash_get', 'flash_set' => 'setup_cities_flash_set', 'csrf_token' => 'setup_cities_csrf_token', 'csrf_validate' => 'setup_cities_csrf_validate', 'countries' => 'setup_cities_countries'],
            'fields' => [
                ['key' => 'city_country_name', 'label' => 'Country', 'type' => 'select_countries'],
                ['key' => 'city_name', 'label' => 'City Name English', 'type' => 'text'],
                ['key' => 'city_shotform', 'label' => 'City Name Arabic', 'type' => 'text', 'arabic' => true],
            ],
            'list_columns' => ['city_name' => 'City Name English', 'city_shotform' => 'City Name Arabic', 'city_country_name' => 'Country'],
            'list_arabic_columns' => ['city_shotform'],
            'primary_key' => 'city_id',
        ],
        'designations' => [
            'label' => 'Designation Setup', 'single' => 'Designation',
            'service' => __DIR__ . '/../../../includes/setup_designations_service.php',
            'routes' => ['list' => 'setup_designations', 'create' => 'setup_designation_create', 'view' => 'setup_designation_view', 'edit' => 'setup_designation_edit'],
            'functions' => ['list' => 'setup_designations_list', 'find' => 'setup_designations_find', 'create' => 'setup_designations_create', 'update' => 'setup_designations_update', 'delete' => 'setup_designations_delete', 'flash_get' => 'setup_designations_flash_get', 'flash_set' => 'setup_designations_flash_set', 'csrf_token' => 'setup_designations_csrf_token', 'csrf_validate' => 'setup_designations_csrf_validate'],
            'fields' => [['key' => 'position_name', 'label' => 'Designation Name', 'type' => 'text'], ['key' => 'position_short_name', 'label' => 'Designation Short Name', 'type' => 'text']],
            'list_columns' => ['position_name' => 'Designation Name', 'position_short_name' => 'Designation Short Name'],
            'primary_key' => 'position_id',
        ],
        'departments' => [
            'label' => 'Department Setup', 'single' => 'Department',
            'service' => __DIR__ . '/../../../includes/setup_departments_service.php',
            'routes' => ['list' => 'setup_departments', 'create' => 'setup_department_create', 'view' => 'setup_department_view', 'edit' => 'setup_department_edit'],
            'functions' => ['list' => 'setup_departments_list', 'find' => 'setup_departments_find', 'create' => 'setup_departments_create', 'update' => 'setup_departments_update', 'delete' => 'setup_departments_delete', 'flash_get' => 'setup_departments_flash_get', 'flash_set' => 'setup_departments_flash_set', 'csrf_token' => 'setup_departments_csrf_token', 'csrf_validate' => 'setup_departments_csrf_validate'],
            'fields' => [['key' => 'department_name', 'label' => 'Department Name', 'type' => 'text'], ['key' => 'department_name_short', 'label' => 'Department Short Name', 'type' => 'text']],
            'list_columns' => ['department_name' => 'Department Name', 'department_name_short' => 'Department Short Name'],
            'primary_key' => 'department_id',
        ],
        'drivers' => [
            'label' => 'Driver Setup', 'single' => 'Driver',
            'service' => __DIR__ . '/../../../includes/setup_drivers_service.php',
            'routes' => ['list' => 'setup_drivers', 'create' => 'setup_driver_create', 'view' => 'setup_driver_view', 'edit' => 'setup_driver_edit'],
            'functions' => ['list' => 'setup_drivers_list', 'find' => 'setup_drivers_find', 'create' => 'setup_drivers_create', 'update' => 'setup_drivers_update', 'delete' => 'setup_drivers_delete', 'flash_get' => 'setup_drivers_flash_get', 'flash_set' => 'setup_drivers_flash_set', 'csrf_token' => 'setup_drivers_csrf_token', 'csrf_validate' => 'setup_drivers_csrf_validate'],
            'fields' => [['key' => 'driver_name', 'label' => 'Name', 'type' => 'text'], ['key' => 'Username', 'label' => 'User Name', 'type' => 'text', 'create_only' => true], ['key' => 'password', 'label' => 'Password', 'type' => 'password', 'create_only' => true], ['key' => 'con_password', 'label' => 'Confirm Password', 'type' => 'password', 'create_only' => true], ['key' => 'driver_contact_no', 'label' => 'HP No', 'type' => 'text'], ['key' => 'driver_address', 'label' => 'Address', 'type' => 'textarea'], ['key' => 'driver_email', 'label' => 'Email', 'type' => 'text']],
            'list_columns' => ['driver_name' => 'Name', 'Username' => 'Username', 'driver_contact_no' => 'HP/No', 'driver_address' => 'Address', 'driver_email' => 'Email'],
            'primary_key' => 'driver_id',
        ],
        'vehicle_types' => [
            'label' => 'Vehicles-Type Setup', 'single' => 'Vehicles-Type',
            'service' => __DIR__ . '/../../../includes/setup_vehicle_types_service.php',
            'routes' => ['list' => 'setup_vehicle_types', 'create' => 'setup_vehicle_type_create', 'view' => 'setup_vehicle_type_view', 'edit' => 'setup_vehicle_type_edit'],
            'functions' => ['list' => 'setup_vehicle_types_list', 'find' => 'setup_vehicle_types_find', 'create' => 'setup_vehicle_types_create', 'update' => 'setup_vehicle_types_update', 'delete' => 'setup_vehicle_types_delete', 'flash_get' => 'setup_vehicle_types_flash_get', 'flash_set' => 'setup_vehicle_types_flash_set', 'csrf_token' => 'setup_vehicle_types_csrf_token', 'csrf_validate' => 'setup_vehicle_types_csrf_validate'],
            'fields' => [['key' => 'vehicle_type_name', 'label' => 'Vehicle Name', 'type' => 'text']],
            'list_columns' => ['vehicle_type_name' => 'Vehicle Name'],
            'primary_key' => 'vehicle_type_id',
        ],
        'sms_labels' => [
            'label' => 'SMS Label Setup', 'single' => 'SMS Label',
            'list_hide_create' => true,
            'list_hide_delete' => true,
            'service' => __DIR__ . '/../../../includes/setup_sms_labels_service.php',
            'routes' => ['list' => 'setup_sms_labels', 'view' => 'setup_sms_label_view', 'edit' => 'setup_sms_label_edit'],
            'functions' => ['list' => 'setup_sms_labels_list', 'find' => 'setup_sms_labels_find', 'update' => 'setup_sms_labels_update', 'flash_get' => 'setup_sms_labels_flash_get', 'flash_set' => 'setup_sms_labels_flash_set', 'csrf_token' => 'setup_sms_labels_csrf_token', 'csrf_validate' => 'setup_sms_labels_csrf_validate'],
            'fields' => [
                ['key' => 'sms_label_header', 'label' => 'SMS Header Label', 'type' => 'text', 'arabic' => true],
                ['key' => 'sms_label_footer', 'label' => 'SMS Footer Label', 'type' => 'text', 'arabic' => true],
            ],
            'list_columns' => ['sms_label_header' => 'SMS Header Label', 'sms_label_footer' => 'SMS Footer Label'],
            'list_arabic_columns' => ['sms_label_header', 'sms_label_footer'],
            'primary_key' => 'sms_label_id',
        ],
    ];
}

