<?php
require_once __DIR__ . '/app/bootstrap.php'; // <--- keep this
// require_once __DIR__ . '/config/paths.php'; // <--- remove this line

// Check if user is logged in - redirect to login if not (except for auth actions)
$controllerName = $_GET['controller'] ?? 'home';
$action = $_GET['action'] ?? 'index';

// Allow these pages without login
$allowedWithoutLogin = [
    'Auth' => ['login', 'index', 'patient_signup', 'logout'],
    'home' => ['about', 'how'],
];

$isAllowed = isset($allowedWithoutLogin[$controllerName]) && 
             in_array($action, $allowedWithoutLogin[$controllerName]);

if (!$isAllowed && !isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/index.php?controller=Auth&action=index&loginRequired=true');
    exit();
}

require_once CONTROLLER_PATH . '/TestCatalog_Control.php';
require_once CONTROLLER_PATH . '/authController.php';
require_once CONTROLLER_PATH . '/administratorController.php';
require_once CONTROLLER_PATH . '/appointmentsController.php';
require_once CONTROLLER_PATH . '/patientController.php';
require_once CONTROLLER_PATH . '/homeController.php';
require_once CONTROLLER_PATH . '/inventoryController.php';
require_once CONTROLLER_PATH . '/profileController.php';
require_once CONTROLLER_PATH . '/blogController.php';
require_once CONTROLLER_PATH . '/partnerLabController.php';
require_once CONTROLLER_PATH . '/supplierController.php';
// require_once 'C:\xampp\htdocs\lab_sync\app\controllers\appointmentsController.php';
require_once __DIR__ . '/config/db.php';
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

elseif ($controllerName === 'TestCatalog') {
    $Testcontroller = new TestCatalogController();
    $action = $_GET['action'] ?? 'login_open'; // or your desired default
    $role= $_GET['role'] ?? '';
    if ($action === 'index') {
        $Testcontroller->index($role);
    } elseif ($action === 'getTestsForAppointment') {
        $Testcontroller->getTestsForAppointment();
    } elseif ($action === 'getLatestTestsForAppointment') {
        $Testcontroller->getLatestTestsForAppointment();
    } elseif ($action === 'add_test') {
        $Testcontroller->add_test($role);
    } elseif ($action === 'store') {
        $Testcontroller->store($role);
    }elseif($action ==='dashboard'){
        include VIEW_PATH . '/administrator/admin_dash.php';
    }elseif($action ==='appointments'){
        include VIEW_PATH . '/administrator/appointments.php';
    }elseif($action =='test_catalog'){
        $Testcontroller->index($role);
        // include 'C:\xampp\htdocs\lab_sync\app\views\receptionist\test_catalog.php';

    }elseif ($action === 'login_open') {
        include VIEW_PATH . '/auth/dash_login.php';
    }elseif($action ==='createAppointment'){
        include VIEW_PATH . '/receptionist/create_Appointment.php';
    }elseif($action ==='edit_test'){
        $Testcontroller->edit_test($role);
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
    }elseif ($action === 'logout') {
        $authController->logout();
    }
    // ...other auth actions...
}elseif($controllerName === 'administratorController'){
    $adminController = new administratorController();
    $action = $_GET['action'] ?? 'settings'; // or your desired default
    $role = $_GET['role'] ?? '';
    if($action ==='settings'){
        $adminController->settings($role);
    }elseif($action==='add_user'){
        include VIEW_PATH . '/administrator/add_user.php';
    }elseif($action==='create_user'){
        // $username = $_POST['username'];
        // $password = $_POST['password'];
        // $role = $_POST['role'];
        $adminController->createUser($role);
    }elseif($action==='manageUser'){
        // $username = $_POST['username'];
        // $password = $_POST['password'];
        // $role = $_POST['role'];
        $adminController->manageUser($role);
    }elseif($action==='usersByRole'){
        $adminController->usersByRole($role);
    }elseif($action==='getLabConfigurationSection'){
        $adminController->getLabConfigurationSection();
    }elseif($action==='getGeneralSettingsSection'){
        $adminController->getGeneralSettingsSection();
    }elseif($action==='saveLabConfiguration'){
        $adminController->saveLabConfiguration();
    }elseif($action==='saveGeneralSettings'){
        $adminController->saveGeneralSettings();
    }
}
elseif ($controllerName === 'appointmentsController') {
    $appointmentController = new appointmentsController();
    $action = $_GET['action'] ?? 'index';
    $role = $_GET['role'] ?? '';
    if ($action === 'index') {
        $appointmentController->index($role);
    } elseif ($action === 'test_catalog') {
        include VIEW_PATH . '/receptionist/test_catalog.php';
    } elseif ($action === 'createAppointment') {
        $appointmentController->createAppointment($role);
    }elseif($action==='prescriptionQueue'){
        $appointmentController->prescriptionQueue();
    }elseif($action==='prescriptionDecisionReport'){
        $appointmentController->prescriptionDecisionReport();
    }elseif($action==='prescriptionRequestDetails'){
        $appointmentController->prescriptionRequestDetails();
    }elseif($action==='processPrescriptionDecision'){
        $appointmentController->processPrescriptionDecision();
    }elseif($action==='storeAppointment'){
        $appointmentController->storeAppointment($role);
    } elseif ($action === 'searchPatients') {
        $appointmentController->searchPatients();
    } elseif ($action === 'filterAppointments') {
        $appointmentController->filterAppointments();
    } elseif ($action === 'getAppointmentDetails') {
        $appointmentController->getAppointmentDetails();
    } elseif ($action === 'getAppointmentEditData') {
        $appointmentController->getAppointmentEditData();
    } elseif ($action === 'searchTests') {
        $appointmentController->searchTests();
    } elseif ($action === 'updateAppointment') {
        $appointmentController->updateAppointment();
    } elseif ($action === 'updateTestStatus') {
        $appointmentController->updateTestStatus();
    }elseif ($action === 'deleteAppointment') {
        $appointmentController->deleteAppointment();
    }
}elseif($controllerName === 'reportsController'){
    $action = $_GET['action'] ?? 'index'; // or your desired default
    if ($action === 'index') {
        include VIEW_PATH . '/technicians/reports.php';

}
}elseif($controllerName === 'supplierController'){
    $supplierController = new supplierController();
    $action = $_GET['action'] ?? 'index';
    $role = $_GET['role'] ?? '';

    if($action === 'index'){
        $supplierController->index($role);
    }elseif($action === 'Register_supplier' || $action === 'register'){
        $supplierController->register($role);
    }elseif($action === 'store'){
        $supplierController->store($role);
    }elseif($action === 'update'){
        $supplierController->update($role);
    }elseif($action === 'delete'){
        $supplierController->delete($role);
    }else{
        echo "404 Not Found";
    }
}elseif($controllerName === 'billingController'){
    $action = $_GET['action'] ?? 'index'; // or your desired default
    if($action ==='index'){
        include VIEW_PATH . '/receptionist/billing.php';
    }elseif($action ==='Register_billing'){
        include VIEW_PATH . '/receptionist/createBill.php';
    }elseif($action ==='create_bill'){
        include VIEW_PATH . '/receptionist/createBill.php';
    }
}elseif($controllerName === 'inventoryController'){
    $inventoryController = new inventoryController();
    $role = $_GET['role'] ?? '';
    $action = $_GET['action'] ?? 'index'; // or your desired default
    if($action ==='index'){
        $inventoryController->index($role);
    }elseif($action ==='add_inventory'){
        $inventoryController->add_inventory();
    }elseif($action ==='store'){
        $inventoryController->store();
        // Logic to store inventory item
    }elseif($action ==='edit_item'){
        $inventoryController->edit_item();
    }
}elseif($controllerName==='patientController'){
    $patientsController = new patientController();
    $action = $_GET['action'] ?? 'index'; 
    $role = $_GET['role'] ?? '';
    if($action==='index'){
        $patientsController->index($role);
    }elseif($action==='register_patient'){
        $patientsController->register_patient($role);
    }elseif($action==='register'){
        $patientsController->register($role);
    }elseif($action==='edit_patient'){
        $patientsController->edit_patient($role);
    }
}elseif($controllerName==='home'){
    $homeController = new homeController(); 
    $action = $_GET['action'] ?? 'index'; // or your desired default
    if($action==='index'){
        include VIEW_PATH . '/patient/patientIndex.php';
    }elseif($action==='appointment_options'){
    $homeController->appointmentOptions();
    }elseif($action==='explore'){
        $homeController->getTests();
    }elseif($action==='dashboard'){
        $homeController->getAppointment();
    }elseif($action==='book_test'){
        include VIEW_PATH . '/patient/book.php';
}elseif($action==="signup"){
        $homeController->signup();
}elseif($action==="how"){
        include VIEW_PATH . '/patient/how.php';
}elseif($action==="profile"){
        include VIEW_PATH . '/patient/profile.php';
}elseif($action==="book"){
        $homeController->bookTest();
}elseif($action==="bookAppointment"){
        $homeController->bookAppointment();
}elseif($action==="get_help"){
    $homeController->getHelp();
}elseif($action==="submit_prescription_help"){
    $homeController->submitPrescriptionHelp();
}elseif($action==="edit_appointment"){
        $homeController->edit_appointment();
}elseif($action==="about"){
        include VIEW_PATH .'/patient/about.php';
}
}elseif($controllerName==='profile'){
    $action = $_GET['action'] ?? 'view'; // or your desired default
    $profileController = new ProfileController();
    if($action==='view'){
        $profileController->viewProfile();
    }elseif($action==='update'){
        $profileController->updateProfile();
    }elseif($action==='changePassword'){
        $profileController->changePassword();
    }
}elseif($controllerName==='blog'){
    $blogController = new blogController();
    $action = $_GET['action'] ?? 'index';
    $role = $_GET['role'] ?? '';
    
    if($action==='index'){
        $blogController->index($role);
    }elseif($action==='view'){
        $blogController->view($role);
    }elseif($action==='manage'){
        $blogController->manage($role);
    }elseif($action==='create'){
        $blogController->create($role);
    }elseif($action==='store'){
        $blogController->store($role);
    }elseif($action==='edit'){
        $blogController->edit($role);
    }elseif($action==='update'){
        $blogController->update($role);
    }elseif($action==='publish'){
        $blogController->publish($role);
    }elseif($action==='unpublish'){
        $blogController->unpublish($role);
    }elseif($action==='archive'){
        $blogController->archive($role);
    }
}elseif($controllerName==='partnerLabController'){
    $partnerLabController = new partnerLabController();
    $action = $_GET['action'] ?? 'index'; 
    $role = $_GET['role'] ?? '';
    // $partnerLabController = new partnerLabController();
    if($action === 'index'){
        $partnerLabController->index();
    }elseif($action === 'RegisterLab'){
        $partnerLabController->index($role);
    }elseif($action === 'storeLab'){
        $partnerLabController->storeLab();
    }elseif($action === 'getPartnerLabsSection'){
        $partnerLabController->getPartnerLabsSection();
    }
}else {
        echo "404 Not Found";
    }

?>