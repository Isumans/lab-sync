<?php

if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';
}

require_once MODEL_PATH . '/dashboardModel.php';
require_once 'C:\xampp\htdocs\lab_sync\config\db.php';

class dashboardController {
    private $db;
    private $model;

    public function __construct() {
        $this->db = connect();
        $this->model = new DashboardModel($this->db);
    }

    public function index(string $role = ''): void {
        if ($role === '') {
            $role = (string)($_SESSION['user_role'] ?? '');
        }

        switch ($role) {
            case 'admin':
                $this->adminDashboard();
                break;
            case 'receptionist':
                $this->receptionistDashboard();
                break;
            case 'technician':
                $this->technicianDashboard();
                break;
            default:
                header('Location: /lab_sync/index.php?controller=Auth&action=index');
                exit;
        }
    }

    private function adminDashboard(): void {
        $patientsThisWeek    = $this->model->countPatientsThisWeek();
        $appointmentsToday   = $this->model->countAppointmentsToday();
        $pendingReports      = $this->model->countPendingReports();
        $revenueThisMonth    = $this->model->getTotalRevenueThisMonth();
        $unpaidBills         = $this->model->countUnpaidBills();
        $staffCounts         = $this->model->getStaffCounts();
        $lowStockCount       = $this->model->countLowStockItems();
        $monthlyRevenue      = $this->model->getMonthlyRevenue(6);
        $appointmentStatus   = $this->model->getAppointmentStatusBreakdown();
        $paymentAging        = $this->model->getPaymentAgingData();
        $activeStaff         = $this->model->getActiveStaffList(4);
        $staffTotals         = $this->model->getStaffTotals();
        $lowStockItems       = $this->model->getLowStockItems(4);

        include VIEW_PATH . '/administrator/admin_dash.php';
    }

    private function receptionistDashboard(): void {
        $appointmentsToday     = $this->model->countAppointmentsToday();
        $pendingPrescriptions  = $this->model->countPendingPrescriptions();
        $unpaidBills           = $this->model->countUnpaidBills();
        $patientsToday         = $this->model->countPatientsRegisteredToday();
        $todaysAppointments    = $this->model->getTodaysAppointments(10);

        include VIEW_PATH . '/receptionist/receptionist_dash.php';
    }

    private function technicianDashboard(): void {
        $reportsPendingEntry   = $this->model->countReportsPendingEntry();
        $reportsAwaitingAuth   = $this->model->countReportsAwaitingAuthorization();
        $reportsCompletedToday = $this->model->countReportsCompletedToday();
        $lowStockCount         = $this->model->countLowStockItems();
        $lowStockItems         = $this->model->getLowStockItems(8);
        $pendingReportsList    = $this->model->getPendingReportsList(10);

        include VIEW_PATH . '/technicians/technician_dash.php';
    }
}
