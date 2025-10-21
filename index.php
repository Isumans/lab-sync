<?php
require_once __DIR__ . '/config/paths.php';

require_once CONTROLLER_PATH . '/TestCatalog_control.php';
require_once CONTROLLER_PATH . '/authController.php';
require_once CONTROLLER_PATH . '/administratorController.php';
require_once CONTROLLER_PATH . '/appointmentsController.php';
require_once CONTROLLER_PATH . '/patientController.php';
require_once CONTROLLER_PATH . '/homeController.php';
require_once CONTROLLER_PATH . '/inventoryController.php';
// require_once 'C:\xampp\htdocs\lab_sync\app\controllers\appointmentsController.php';
require_once 'C:\xampp\htdocs\lab_sync\config\db.php';
$controllerName = $_GET['controller'] ?? 'home'; // Default to 'home' controller
// $controllerName = 'TestCatalog'; 
// This should be set based 
// on your routing logic

if ($controllerName === 'dashboard') {
    $action = $_GET['action'] ?? 'index'; // or your desired default
    // include 'C:\xampp\htdocs\lab_sync\app\views\administrator\admin_dash.php';
    if($action === 'index'){
        include VIEW_PATH . '/administrator/admin_dash.php';
    }
}

if ($controllerName === 'TestCatalog') {
    $Testcontroller = new TestCatalogController();
    $action = $_GET['action'] ?? 'login_open'; // or your desired default
    if ($action === 'index') {
        $Testcontroller->index();
    } elseif ($action === 'add_test') {
        $Testcontroller->add_test();
    } elseif ($action === 'store') {
        $Testcontroller->store();
    }elseif($action ==='dashboard'){
        include VIEW_PATH . '/administrator/admin_dash.php';
    }elseif($action ==='appointments'){
        include VIEW_PATH . '/administrator/appointments.php';
    }elseif($action =='test_catalog'){
        $Testcontroller->index();
        // include 'C:\xampp\htdocs\lab_sync\app\views\receptionist\test_catalog.php';

    }elseif ($action === 'login_open') {
        include VIEW_PATH . '/auth/dash_login.php';
    }elseif($action ==='createAppointment'){
        include VIEW_PATH . '/receptionist/create_Appointment.php';
    }elseif($action ==='edit_test'){
        $Testcontroller->edit_test();
    }
    
    else {
        echo "404 Not Found";
    }
}
 elseif ($controllerName === 'Auth') {
    $authController = new AuthController();
    $action = $_GET['action'] ?? 'index'; // or your desired default
    if ($action === 'login') {
        $authController->login();
    }elseif ($action === 'index') {
        include VIEW_PATH . '/auth/dash_login.php';
        
    }elseif ($action === 'patient_signup') {
        include VIEW_PATH . '/auth/patient_signup.php';
    }
    // ...other auth actions...
}elseif($controllerName === 'administratorController'){
    $adminController = new administratorController();
    $action = $_GET['action'] ?? 'settings'; // or your desired default
    if($action ==='settings'){
        $adminController->settings();
    }elseif($action==='add_user'){
        include VIEW_PATH . '/administrator/add_user.php';
    }elseif($action==='create_user'){
        // $username = $_POST['username'];
        // $password = $_POST['password'];
        // $role = $_POST['role'];
        $adminController->createUser();
    }elseif($action==='manageUser'){
        // $username = $_POST['username'];
        // $password = $_POST['password'];
        // $role = $_POST['role'];
        $adminController->manageUser();
    }
}
elseif ($controllerName === 'appointmentsController') {
    $appointmentController = new appointmentsController();
    $action = $_GET['action'] ?? 'index'; // or your desired default
    if ($action === 'index') {
        include VIEW_PATH . '/receptionist/appointments.php';
    }elseif($action ==='test_catalog'){
        include VIEW_PATH . '/receptionist/test_catalog.php';
    }elseif($action==='createAppointment'){
        include VIEW_PATH . '/receptionist/create_Appointment.php';
    }elseif($action==='storeAppointment'){
        $appointmentController->storeAppointment();
    }
    // ...other receptionist actions...
}elseif($controllerName === 'reportsController'){
    $action = $_GET['action'] ?? 'index'; // or your desired default
    if ($action === 'index') {
        include VIEW_PATH . '/technicians/reports.php';

}
}elseif($controllerName === 'supplierController'){
    $action = $_GET['action'] ?? 'index'; // or your desired default
    if($action ==='index'){
        include VIEW_PATH . '/administrator/suppliers.php';
    }
}elseif($controllerName === 'billingController'){
    $action = $_GET['action'] ?? 'index'; // or your desired default
    if($action ==='index'){
        include VIEW_PATH . '/receptionist/billing.php';
    }elseif($action ==='Register_billing'){
        include VIEW_PATH . '/receptionist/createBill.php';
    }
}elseif($controllerName === 'inventoryController'){
    $inventoryController = new inventoryController();
    $action = $_GET['action'] ?? 'index'; // or your desired default
    if($action ==='index'){
        $inventoryController->index();
    }elseif($action ==='add_inventory'){
        include VIEW_PATH . '/technicians/addInventory.php';
    }elseif($action ==='store'){
        $inventoryController->store();
        // Logic to store inventory item
    }elseif($action ==='edit_item'){
        $inventoryController->edit_item();
    }
}elseif($controllerName==='patientController'){
    $patientsController = new patientController();
    $action = $_GET['action'] ?? 'index'; // or your desired default
    if($action==='index'){
        $patientsController->index();
    }elseif($action==='register_patient'){
        $patientsController->register_patient();
    }elseif($action==='register'){
        $patientsController->register();
    }elseif($action==='edit_patient'){
        $patientsController->edit_patient();
    }
}elseif($controllerName==='home'){
    $homeController = new homeController(); 
    $action = $_GET['action'] ?? 'index'; // or your desired default
    if($action==='index'){
        include VIEW_PATH . '/patient/patientIndex.php';
    }elseif($action==='explore'){
        include VIEW_PATH . '/patient/explore.php';
    }elseif($action==='dashboard'){
        include VIEW_PATH . '/patient/dashboard.php';
    }elseif($action==='book_test'){
        include VIEW_PATH . '/patient/book.php';
}elseif($action==="signup"){
        $homeController->signup();
}
}
else {
        echo "404 Not Found";
    }

?>