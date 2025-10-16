<?php
// Get current controller and action from URL or variables set in your router
$currentController = $_GET['controller'] ?? '';
$currentAction = $_GET['action'] ?? '';
?>
<aside class="sidebar">
    <ul>
        <li>
            <a href="index.php?controller=dashboard&action=index"
               class="<?php echo ($currentController === 'dashboard' && $currentAction === 'index') ? 'active' : ''; ?>">
                <img class="sidebar-icon" src="/lab_sync/public/assests/dashboard.png" alt="Dashboard Icon">Dashboard
            </a>
        </li>
        <li>
            <a href="index.php?controller=appointmentsController&action=index"
               class="<?php echo ($currentController === 'appointmentsController' && $currentAction === 'index') ? 'active' : ''; ?>">
                <img class="sidebar-icon" src="/lab_sync/public/assests/appointment.png" alt="Appointments Icon">Appointments
            </a>
        </li>
        <li>
            <a href="index.php?controller=patients&action=index"
               class="<?php echo ($currentController === 'patients' && $currentAction === 'index') ? 'active' : ''; ?>">
                <img class="sidebar-icon" src="/lab_sync/public/assests/patients.png" alt="Patients Icon">Patients
            </a>
        </li>
        <li>
            <a href="index.php?controller=TestCatalog&action=index"
               class="<?php echo ($currentController === 'TestCatalog' && $currentAction === 'index') ? 'active' : ''; ?>">
                <img class="sidebar-icon" src="/lab_sync/public/assests/test-catalog.png" alt="Test Catalog Icon">Test-Catalog
            </a>
        </li>
        <li>
            <a href="index.php?controller=reportsController&action=index"
               class="<?php echo ($currentController === 'reportsController' && $currentAction === 'index') ? 'active' : ''; ?>">
                <img class="sidebar-icon" src="/lab_sync/public/assests/results.png" alt="Reports Icon">Reports
            </a>
        </li>
        <li>
            <a href="index.php?controller=inventoryController&action=index"
               class="<?php echo ($currentController === 'inventoryController' && $currentAction === 'index') ? 'active' : ''; ?>">
                <img class="sidebar-icon" src="/lab_sync/public/assests/inventory.png" alt="Inventory Icon">Inventory
            </a>
        </li>
        <li>
            <a href="index.php?controller=billingController&action=index"
               class="<?php echo ($currentController === 'billingController' && $currentAction === 'index') ? 'active' : ''; ?>">
                <img class="sidebar-icon" src="/lab_sync/public/assests/billing.png" alt="Billing Icon">Billing
            </a>
        </li>
        <li>
            <a href="index.php?controller=supplierController&action=index"
               class="<?php echo ($currentController === 'supplierController' && $currentAction === 'index') ? 'active' : ''; ?>">
                <img class="sidebar-icon" src="/lab_sync/public/assests/supplier.png" alt="Suppliers Icon">Suppliers
            </a>
        </li>
        <li>
            <a href="index.php?controller=administratorController&action=settings"
               class="<?php echo ($currentController === 'administratorController' && $currentAction === 'settings') ? 'active' : ''; ?>">
                <img class="sidebar-icon" src="/lab_sync/public/assests/settings.png" alt="Employees Icon">Settings
            </a>
        </li>
        <li>
            <a href="index.php?controller=authController&action=logout"
               class="<?php echo ($currentController === 'authController' && $currentAction === 'logout') ? 'active' : ''; ?>">
                <img class="sidebar-icon" src="/lab_sync/public/assests/logout.png" alt="Logout Icon">Logout
            </a>
        </li>
    </ul>
</aside>