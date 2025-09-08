<?php

require_once 'C:\xampp\htdocs\lab_sync\app\controllers\TestCatalog_control.php';
require_once 'C:\xampp\htdocs\lab_sync\config\db.php';

$controllerName = 'TestCatalog'; // This should be set based on your routing logic
// $action = 'index'; 
// This should be set based on your routing logic

if ($controllerName === 'TestCatalog') {
    $Testcontroller = new TestCatalogController();
    if($_GET['action']){
        $action = $_GET['action'];
    } else {
        $action = 'index'; // default action
    }
    if ($action === 'index') {
        $Testcontroller->index();
    } elseif ($action === 'add_test') {
        $Testcontroller->add_test();
    } elseif ($action === 'store') {
        $Testcontroller->store();
    } else {
        echo "404 Not Found";
    }
}


?>