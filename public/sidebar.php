<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$role = $_SESSION['user_role'] ?? '';

function showMenuItem($rolesAllowed, $content) {
    global $role;
    if ($role && in_array($role, $rolesAllowed)) {
        echo $content;
    }
}

$currentController = $_GET['controller'] ?? '';
$currentAction = $_GET['action'] ?? '';
?>

<aside class="sidebar">
    <ul>
        <?php
        // Dashboard
        showMenuItem(['admin', 'receptionist', 'technician'], "
            <li>
                <a href='index.php?controller=dashboard&action=index&role=" . $role . "'
                   class='" . (($currentController === 'dashboard' && $currentAction === 'index') ? 'active' : '') . "'>
                    <img class='sidebar-icon' src='/lab_sync/public/assests/dashboard.png' alt='Dashboard Icon'>Dashboard
                </a>
            </li>
        ");

        // Appointments
        showMenuItem(['admin', 'receptionist', 'technician'], "
            <li>
                <a href='index.php?controller=appointmentsController&action=index&role=" . $role . "'
                   class='" . (($currentController === 'appointmentsController' && $currentAction === 'index') ? 'active' : '') . "'>
                    <img class='sidebar-icon' src='/lab_sync/public/assests/appointment.png' alt='Appointments Icon'>Appointments
                </a>
            </li>
        ");

        // Patients
        showMenuItem(['admin', 'receptionist'], "
            <li>
                <a href='index.php?controller=patientController&action=index&role=" . $role . "'
                   class='" . (($currentController === 'patientController' && $currentAction === 'index') ? 'active' : '') . "'>
                    <img class='sidebar-icon' src='/lab_sync/public/assests/patients.png' alt='Patients Icon'>Patients
                </a>
            </li>
        ");

        // Test Catalog
        showMenuItem(['admin', 'technician'], "
            <li>
                <a href='index.php?controller=TestCatalog&action=index&role=" . $role . "'
                   class='" . (($currentController === 'TestCatalog' && $currentAction === 'index') ? 'active' : '') . "'>
                    <img class='sidebar-icon' src='/lab_sync/public/assests/test-catalog.png' alt='Test Catalog Icon'>Test Catalog
                </a>
            </li>
        ");

        // Reports
        showMenuItem(['admin', 'receptionist', 'technician'], "
            <li>
                <a href='index.php?controller=reportsController&action=index&role=" . $role . "'
                   class='" . (($currentController === 'reportsController' && $currentAction === 'index') ? 'active' : '') . "'>
                    <img class='sidebar-icon' src='/lab_sync/public/assests/results.png' alt='Reports Icon'>Reports
                </a>
            </li>
        ");

        // Inventory
        showMenuItem(['admin', 'technician'], "
            <li>
                <a href='index.php?controller=inventoryController&action=index&role=" . $role . "'
                   class='" . (($currentController === 'inventoryController' && $currentAction === 'index') ? 'active' : '') . "'>
                    <img class='sidebar-icon' src='/lab_sync/public/assests/inventory.png' alt='Inventory Icon'>Inventory
                </a>
            </li>
        ");

        // Billing
        showMenuItem(['admin', 'receptionist'], "
            <li>
                <a href='index.php?controller=billingController&action=index&role=" . $role . "'
                   class='" . (($currentController === 'billingController' && $currentAction === 'index') ? 'active' : '') . "'>
                    <img class='sidebar-icon' src='/lab_sync/public/assests/billing.png' alt='Billing Icon'>Billing
                </a>
            </li>
        ");

        // Suppliers
        showMenuItem(['admin'], "
            <li>
                <a href='index.php?controller=supplierController&action=index&role=" . $role . "'
                   class='" . (($currentController === 'supplierController' && $currentAction === 'index') ? 'active' : '') . "'>
                    <img class='sidebar-icon' src='/lab_sync/public/assests/supplier.png' alt='Supplier Icon'>Suppliers
                </a>
            </li>
        ");

        // Settings
        showMenuItem(['admin'], "
            <li>
                <a href='index.php?controller=administratorController&action=settings&role=" . $role . "'
                   class='" . (($currentController === 'administratorController' && $currentAction === 'settings') ? 'active' : '') . "'>
                    <img class='sidebar-icon' src='/lab_sync/public/assests/settings.png' alt='Settings Icon'>Settings
                </a>
            </li>
        ");

        // Logout
        showMenuItem(['admin', 'receptionist', 'technician'], "
            <li>
                <a href='index.php?controller=Auth&action=logout&role=" . $role . "'>
                    <img class='sidebar-icon' src='/lab_sync/public/assests/logout.png' alt='Logout Icon'>Logout
                </a>
            </li>
        ");
        ?>
    </ul>
</aside>
