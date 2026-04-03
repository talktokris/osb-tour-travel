<?php
require __DIR__ . '/config.php';

// Simple routing based on ?page=
$page = $_GET['page'] ?? 'login';

if ($page === 'logout') {
    session_destroy();
    header('Location: index.php?page=login');
    exit;
}

// Handle login POST
if ($page === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } else {
        // Match legacy app: check user_login table with MD5 password
        $stmt = $mysqli->prepare('SELECT Userid AS user_id, Username AS user_name, password, Status FROM user_login WHERE Username = ? LIMIT 1');
        if (!$stmt) {
            $error = 'Login is temporarily unavailable. Please try again later.';
        } else {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            $hashedInput = md5($password);

            if ($user && strcasecmp($user['Status'], 'Active') === 0 && hash_equals($user['password'], $hashedInput)) {
                $_SESSION['user_id'] = (int) $user['user_id'];
                $_SESSION['user_name'] = $user['user_name'];
                header('Location: index.php?page=home');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        }
    }
}

// Require login for all pages except login
if ($page !== 'login' && empty($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

// Dispatch to page file
if ($page === 'login') {
    require __DIR__ . '/pages/login.php';
} elseif ($page === 'home') {
    require __DIR__ . '/pages/home/index.php';
} elseif ($page === 'agents') {
    require __DIR__ . '/pages/agents/list.php';
} elseif ($page === 'services') {
    require __DIR__ . '/pages/services/index.php';
} elseif ($page === 'bookings') {
    require __DIR__ . '/pages/bookings/index.php';
} elseif ($page === 'file') {
    require __DIR__ . '/pages/file/index.php';
} elseif ($page === 'search') {
    require __DIR__ . '/pages/search/index.php';
} elseif ($page === 'report') {
    require __DIR__ . '/pages/report/index.php';
} elseif ($page === 'driver') {
    require __DIR__ . '/pages/driver/index.php';
} elseif ($page === 'invoice') {
    require __DIR__ . '/pages/invoice/index.php';
} elseif ($page === 'sms') {
    require __DIR__ . '/pages/sms/index.php';
} elseif ($page === 'setup') {
    require __DIR__ . '/pages/setup/index.php';
} elseif ($page === 'setup_agents') {
    require __DIR__ . '/pages/setup/agents/list.php';
} elseif ($page === 'setup_agent_create') {
    require __DIR__ . '/pages/setup/agents/create.php';
} elseif ($page === 'setup_agent_view') {
    require __DIR__ . '/pages/setup/agents/view.php';
} elseif ($page === 'setup_agent_edit') {
    require __DIR__ . '/pages/setup/agents/edit.php';
} elseif ($page === 'setup_suppliers') {
    require __DIR__ . '/pages/setup/suppliers/list.php';
} elseif ($page === 'setup_supplier_create') {
    require __DIR__ . '/pages/setup/suppliers/create.php';
} elseif ($page === 'setup_supplier_view') {
    require __DIR__ . '/pages/setup/suppliers/view.php';
} elseif ($page === 'setup_supplier_edit') {
    require __DIR__ . '/pages/setup/suppliers/edit.php';
} elseif ($page === 'setup_vehicles') {
    require __DIR__ . '/pages/setup/vehicles/list.php';
} elseif ($page === 'setup_vehicle_create') {
    require __DIR__ . '/pages/setup/vehicles/create.php';
} elseif ($page === 'setup_vehicle_view') {
    require __DIR__ . '/pages/setup/vehicles/view.php';
} elseif ($page === 'setup_vehicle_edit') {
    require __DIR__ . '/pages/setup/vehicles/edit.php';
} elseif ($page === 'setup_locations') {
    require __DIR__ . '/pages/setup/locations/list.php';
} elseif ($page === 'setup_location_create') {
    require __DIR__ . '/pages/setup/locations/create.php';
} elseif ($page === 'setup_location_view') {
    require __DIR__ . '/pages/setup/locations/view.php';
} elseif ($page === 'setup_location_edit') {
    require __DIR__ . '/pages/setup/locations/edit.php';
} elseif ($page === 'setup_services') {
    require __DIR__ . '/pages/setup/services/list.php';
} elseif ($page === 'setup_service_create') {
    require __DIR__ . '/pages/setup/services/create.php';
} elseif ($page === 'setup_service_view') {
    require __DIR__ . '/pages/setup/services/view.php';
} elseif ($page === 'setup_service_edit') {
    require __DIR__ . '/pages/setup/services/edit.php';
} elseif ($page === 'setup_zones') {
    $_GET['m'] = 'zones';
    require __DIR__ . '/pages/setup/common/list.php';
} elseif ($page === 'setup_zone_create') {
    $_GET['m'] = 'zones';
    require __DIR__ . '/pages/setup/common/create.php';
} elseif ($page === 'setup_zone_view') {
    $_GET['m'] = 'zones';
    require __DIR__ . '/pages/setup/common/view.php';
} elseif ($page === 'setup_zone_edit') {
    $_GET['m'] = 'zones';
    require __DIR__ . '/pages/setup/common/edit.php';
} elseif ($page === 'setup_countries') {
    $_GET['m'] = 'countries';
    require __DIR__ . '/pages/setup/common/list.php';
} elseif ($page === 'setup_country_create') {
    $_GET['m'] = 'countries';
    require __DIR__ . '/pages/setup/common/create.php';
} elseif ($page === 'setup_country_view') {
    $_GET['m'] = 'countries';
    require __DIR__ . '/pages/setup/common/view.php';
} elseif ($page === 'setup_country_edit') {
    $_GET['m'] = 'countries';
    require __DIR__ . '/pages/setup/common/edit.php';
} elseif ($page === 'setup_cities') {
    $_GET['m'] = 'cities';
    require __DIR__ . '/pages/setup/common/list.php';
} elseif ($page === 'setup_city_create') {
    $_GET['m'] = 'cities';
    require __DIR__ . '/pages/setup/common/create.php';
} elseif ($page === 'setup_city_view') {
    $_GET['m'] = 'cities';
    require __DIR__ . '/pages/setup/common/view.php';
} elseif ($page === 'setup_city_edit') {
    $_GET['m'] = 'cities';
    require __DIR__ . '/pages/setup/common/edit.php';
} elseif ($page === 'setup_designations') {
    $_GET['m'] = 'designations';
    require __DIR__ . '/pages/setup/common/list.php';
} elseif ($page === 'setup_designation_create') {
    $_GET['m'] = 'designations';
    require __DIR__ . '/pages/setup/common/create.php';
} elseif ($page === 'setup_designation_view') {
    $_GET['m'] = 'designations';
    require __DIR__ . '/pages/setup/common/view.php';
} elseif ($page === 'setup_designation_edit') {
    $_GET['m'] = 'designations';
    require __DIR__ . '/pages/setup/common/edit.php';
} elseif ($page === 'setup_departments') {
    $_GET['m'] = 'departments';
    require __DIR__ . '/pages/setup/common/list.php';
} elseif ($page === 'setup_department_create') {
    $_GET['m'] = 'departments';
    require __DIR__ . '/pages/setup/common/create.php';
} elseif ($page === 'setup_department_view') {
    $_GET['m'] = 'departments';
    require __DIR__ . '/pages/setup/common/view.php';
} elseif ($page === 'setup_department_edit') {
    $_GET['m'] = 'departments';
    require __DIR__ . '/pages/setup/common/edit.php';
} elseif ($page === 'setup_drivers') {
    $_GET['m'] = 'drivers';
    require __DIR__ . '/pages/setup/common/list.php';
} elseif ($page === 'setup_driver_create') {
    $_GET['m'] = 'drivers';
    require __DIR__ . '/pages/setup/common/create.php';
} elseif ($page === 'setup_driver_view') {
    $_GET['m'] = 'drivers';
    require __DIR__ . '/pages/setup/common/view.php';
} elseif ($page === 'setup_driver_edit') {
    $_GET['m'] = 'drivers';
    require __DIR__ . '/pages/setup/common/edit.php';
} elseif ($page === 'setup_driver_password_list') {
    require __DIR__ . '/pages/setup/drivers/change-password-list.php';
} elseif ($page === 'setup_driver_password_form') {
    require __DIR__ . '/pages/setup/drivers/change-password-form.php';
} elseif ($page === 'setup_vehicle_types') {
    $_GET['m'] = 'vehicle_types';
    require __DIR__ . '/pages/setup/common/list.php';
} elseif ($page === 'setup_vehicle_type_create') {
    $_GET['m'] = 'vehicle_types';
    require __DIR__ . '/pages/setup/common/create.php';
} elseif ($page === 'setup_vehicle_type_view') {
    $_GET['m'] = 'vehicle_types';
    require __DIR__ . '/pages/setup/common/view.php';
} elseif ($page === 'setup_vehicle_type_edit') {
    $_GET['m'] = 'vehicle_types';
    require __DIR__ . '/pages/setup/common/edit.php';
} elseif ($page === 'setup_sms_labels') {
    $_GET['m'] = 'sms_labels';
    require __DIR__ . '/pages/setup/common/list.php';
} elseif ($page === 'setup_sms_label_create') {
    $_GET['m'] = 'sms_labels';
    require __DIR__ . '/pages/setup/common/create.php';
} elseif ($page === 'setup_sms_label_view') {
    $_GET['m'] = 'sms_labels';
    require __DIR__ . '/pages/setup/common/view.php';
} elseif ($page === 'setup_sms_label_edit') {
    $_GET['m'] = 'sms_labels';
    require __DIR__ . '/pages/setup/common/edit.php';
} elseif ($page === 'setup_itinerary_labels') {
    require __DIR__ . '/pages/setup/itinerary-labels/list.php';
} elseif ($page === 'setup_itinerary_label_edit') {
    require __DIR__ . '/pages/setup/itinerary-labels/edit.php';
} elseif ($page === 'users') {
    require __DIR__ . '/pages/users/list.php';
} elseif ($page === 'users_create') {
    require __DIR__ . '/pages/users/create.php';
} elseif ($page === 'users_edit') {
    require __DIR__ . '/pages/users/edit.php';
} elseif ($page === 'users_view') {
    require __DIR__ . '/pages/users/view.php';
} elseif ($page === 'users_role_list') {
    require __DIR__ . '/pages/users/change-role-list.php';
} elseif ($page === 'users_role_form') {
    require __DIR__ . '/pages/users/change-role-form.php';
} elseif ($page === 'users_password_list') {
    require __DIR__ . '/pages/users/change-password-list.php';
} elseif ($page === 'users_password_form') {
    require __DIR__ . '/pages/users/change-password-form.php';
} else {
    require __DIR__ . '/pages/home/index.php';
}

