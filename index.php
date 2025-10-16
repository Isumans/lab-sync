<?php

require_once 'C:\xampp\htdocs\lab_sync\app\controllers\TestCatalog_control.php';
require_once 'C:\xampp\htdocs\lab_sync\app\controllers\authController.php';
require_once 'C:\xampp\htdocs\lab_sync\app\controllers\administratorController.php';
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
    $action = $_GET['action'] ?? 'index'; // or your desired default
    if ($action === 'index') {
        include 'app\views\receptionist\appointments.php';
    }elseif($action ==='test_catalog'){
        include 'app\views\receptionist\test_catalog.php';
    }
    // ...other receptionist actions...
}elseif($controllerName === 'patients'){
    $action = $_GET['action'] ?? 'index'; // or your desired default
    if($action ==='index'){
        include 'app\views\patients\patients.php';

}
}
 else {
    echo "404 Not Found";
}


?>