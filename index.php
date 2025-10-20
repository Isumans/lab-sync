<?php

require_once 'C:\xampp\htdocs\lab_sync\app\controllers\TestCatalog_control.php';
require_once 'C:\xampp\htdocs\lab_sync\app\controllers\authController.php';
require_once 'C:\xampp\htdocs\lab_sync\app\controllers\administratorController.php';
require_once 'C:\xampp\htdocs\lab_sync\app\controllers\appointmentsController.php';
require_once 'C:\xampp\htdocs\lab_sync\app\controllers\patientController.php';
// require_once 'C:\xampp\htdocs\lab_sync\app\controllers\appointmentsController.php';
require_once 'C:\xampp\htdocs\lab_sync\config\db.php';
$controllerName = $_GET['controller'] ?? 'Auth'; // Default to 'Auth' controller
// $controllerName = 'TestCatalog'; 
// This should be set based 
// on your routing logic

if ($controllerName === 'dashboard') {
    $action = $_GET['action'] ?? 'index'; // or your desired default
    // include 'C:\xampp\htdocs\lab_sync\app\views\administrator\admin_dash.php';
    if($action === 'index'){
        include 'C:\xampp\htdocs\lab_sync\app\views\administrator\admin_dash.php';
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
        include 'C:\xampp\htdocs\lab_sync\app\views\administrator\admin_dash.php';
    }elseif($action ==='appointments'){
        include 'C:\xampp\htdocs\lab_sync\app\views\administrator\appointments.php';
    }elseif($action =='test_catalog'){
        $Testcontroller->index();
        // include 'C:\xampp\htdocs\lab_sync\app\views\receptionist\test_catalog.php';

    }elseif ($action === 'login_open') {
        include 'C:\xampp\htdocs\lab_sync\app\views\auth\dash_login.php';
    }elseif($action ==='createAppointment'){
        include 'C:\xampp\htdocs\lab_sync\app\views\receptionist\create_Appointment.php';
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
        include 'app\views\auth\dash_login.php';
        
    }
    // ...other auth actions...
}elseif($controllerName === 'administratorController'){
    $action = $_GET['action'] ?? 'settings'; // or your desired default
    if($action ==='settings'){
        include 'app\views\administrator\settings.php';
    }
}
elseif ($controllerName === 'appointmentsController') {
    $appointmentController = new appointmentsController();
    $action = $_GET['action'] ?? 'index'; // or your desired default
    if ($action === 'index') {
        include 'app\views\receptionist\appointments.php';
    }elseif($action ==='test_catalog'){
        include 'app\views\receptionist\test_catalog.php';
    }elseif($action==='createAppointment'){
        include 'app\views\receptionist\create_Appointment.php';
    }elseif($action==='storeAppointment'){
        $appointmentController->storeAppointment();
    }
    // ...other receptionist actions...
}elseif($controllerName === 'reportsController'){
    $action = $_GET['action'] ?? 'index'; // or your desired default
    if ($action === 'index') {
        include 'app\views\technicians\reports.php';

}
}elseif($controllerName === 'supplierController'){
    $action = $_GET['action'] ?? 'index'; // or your desired default
    if($action ==='index'){
        include 'app\views\administrator\suppliers.php';}
    }
elseif($controllerName === 'billingController'){
    $action = $_GET['action'] ?? 'index'; // or your desired default
    if($action ==='index'){
        include 'app\views\receptionist\billing.php';
    }elseif($action ==='Register_billing'){
        include 'app\views\receptionist\createBill.php';
    }
}elseif($controllerName === 'inventoryController'){
    $action = $_GET['action'] ?? 'index'; // or your desired default
    if($action ==='index'){
        include 'app\views\technicians\inventory.php';
    }elseif($action ==='add_inventory'){
        include 'app\views\technicians\add_inventory.php';
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
}
else {
        echo "404 Not Found";
    }

?>