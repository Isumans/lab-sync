<?php
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';  // ✅ correct
}

require_once MODEL_PATH . '/homeModel.php';
require_once 'C:\xampp\htdocs\lab_sync\config\db.php';

class HomeController {
    private $model;

    public function __construct() {
        $this->model = new HomeModel();
    }

    public function index() {
        $data = $this->model->getData();
        include VIEW_PATH . '/patient/home.php';
    }
     public function explore(){
        include VIEW_PATH . '/patient/explore.php';
     }
        public function dash(){
            include VIEW_PATH . '/patient/dashboard.php';
        }
}
?>