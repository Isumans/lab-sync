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
        $patientGrowthPct    = $this->model->getPatientGrowthPercentage();
        $appointmentsToday   = $this->model->countAppointmentsToday();
        $pendingReports      = $this->model->countPendingReports();
        $revenueThisMonth    = $this->model->getTotalRevenueThisMonth();
        $unpaidBills         = $this->model->countUnpaidBills();
        $staffCounts         = $this->model->getStaffCounts();
        $monthlyRevenue      = $this->model->getMonthlyRevenue(6);
        $testStatus          = $this->model->getTestStatusBreakdown();
        $staffTotals         = $this->model->getStaffTotals();
        $lowStockItems       = $this->model->getLowStockItems(4);

        $revenueLabels = $monthlyRevenue['labels'] ?? [];
        $revenueValues = $monthlyRevenue['values'] ?? [];
        if (!is_array($revenueLabels)) {
            $revenueLabels = [];
        }
        if (!is_array($revenueValues)) {
            $revenueValues = [];
        }

        $retailRevenueValues = array_fill(0, count($revenueValues), 0);
        $staffActiveTotal = intval($staffTotals['active'] ?? 0);
        $staffOffTotal = intval($staffTotals['inactive'] ?? 0);
        $staffPendingTotal = 0;

        $adminChartPayload = [
            'revenue' => [
                'labels' => $revenueLabels,
                'service' => array_map('floatval', $revenueValues),
                'retail' => $retailRevenueValues,
            ],
            'status' => [
                'labels' => ['Pending', 'In Progress', 'Completed', 'Authenticated'],
                'values' => [
                    intval($testStatus['pending'] ?? 0),
                    intval($testStatus['in_progress'] ?? 0),
                    intval($testStatus['completed'] ?? 0),
                    intval($testStatus['authenticated'] ?? 0),
                ],
                'colors' => ['#f4b400', '#3DBDEC', '#2ed573', '#8b5cf6'],
            ],
        ];

        include VIEW_PATH . '/administrator/admin_dash.php';
    }

    private function receptionistDashboard(): void {
        $appointmentsToday     = $this->model->countAppointmentsToday();
        $appointmentsYesterday = $this->model->countAppointmentsYesterday();
        $pendingPrescriptions  = $this->model->countPendingPrescriptions();
        $unpaidBills           = $this->model->countUnpaidBills();
        $unpaidBillsAmount     = $this->model->getUnpaidBillsOutstandingAmount();
        $patientsToday         = $this->model->countPatientsRegisteredToday();
        $appointmentDensityRows = $this->model->getTodayOnlineSlotDensity();
        $statusSnapshot        = $this->model->getTodayAppointmentStatusSnapshot();
        $appointmentTypes      = $this->model->getTodayAppointmentTypeSplit();
        $topOrderedTests       = $this->model->getTopOrderedTestsToday(4);

        $appointmentChangePct = 0;
        if ($appointmentsYesterday > 0) {
            $appointmentChangePct = intval(round((($appointmentsToday - $appointmentsYesterday) / $appointmentsYesterday) * 100));
        } elseif ($appointmentsToday > 0) {
            $appointmentChangePct = 100;
        }

        $pendingBadgeLabel = $pendingPrescriptions >= 5 ? 'Priority' : 'Normal';

        if ($unpaidBillsAmount >= 2000) {
            $unpaidBadgeLabel = 'Critical';
        } elseif ($unpaidBillsAmount > 0) {
            $unpaidBadgeLabel = 'Watch';
        } else {
            $unpaidBadgeLabel = 'Clear';
        }

        $registeredBadgeLabel = $patientsToday > 0 ? 'New' : 'Today';

        $statusTotal = array_sum($statusSnapshot);
        $appointmentTypeTotal = array_sum($appointmentTypes);

        $topOrderedTestsTotal = array_sum(array_map(function ($row) {
            return intval($row['total_orders'] ?? 0);
        }, $topOrderedTests));

        include VIEW_PATH . '/receptionist/receptionist_dash.php';
    }

    private function technicianDashboard(): void {
        $reportsPendingEntry   = $this->model->countReportsPendingEntry();
        $reportsAwaitingAuth   = $this->model->countReportsAwaitingAuthorization();
        $reportsCompletedToday = $this->model->countReportsCompletedToday();
        $lowStockCount         = $this->model->countLowStockItems();
        $pendingEntryCompare   = $this->model->getPendingEntryDayComparison();
        $workflowBreakdown     = $this->model->getTechnicianWorkflowBreakdown();
        $testVolumeCategories  = $this->model->getTechnicianTestVolumeByCategory(4);
        $criticalInventory     = $this->model->getCriticalInventoryLevels(4);
        $completedTarget       = $this->model->getTechnicianCompletedTarget();

        $technicianChartPayload = [
            'workflow' => [
                'labels' => ['Data Entry', 'Review', 'Authorized'],
                'values' => [
                    intval($workflowBreakdown['data_entry'] ?? 0),
                    intval($workflowBreakdown['review'] ?? 0),
                    intval($workflowBreakdown['authorized'] ?? 0),
                ],
                'colors' => ['#20c18a', '#f4bc2a', '#39b8e4'],
            ],
        ];

        include VIEW_PATH . '/technicians/technician_dash.php';
    }
}
