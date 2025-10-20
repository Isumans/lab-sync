<?php
require_once 'C:\xampp\htdocs\lab_sync\app\models\homeModel.php';
require_once 'C:\xampp\htdocs\lab_sync\config\db.php';

class HomeController {
    private $model;

    public function __construct() {
        $this->model = new HomeModel();
    }

    public function index() {
        $data = $this->model->getData();
        include 'C:\xampp\htdocs\lab_sync\app\views\patient\home.php';
    }
     public function explore(){
        include 'C:\xampp\htdocs\lab_sync\app\views\patient\explore.php';
     }
        public function dash(){
            include 'C:\xampp\htdocs\lab_sync\app\views\patient\dashboard.php';
        }
}
?>