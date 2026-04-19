<?php
require_once MODEL_PATH . '/patientModel.php';
require_once MODEL_PATH . '/appointmentModel.php';
require_once MODEL_PATH . '/inventoryModel.php';

class navbarSearchController {

    public function search(): void {
        header('Content-Type: application/json; charset=UTF-8');

        $role = strtolower(trim((string) ($_SESSION['user_role'] ?? '')));
        if (!in_array($role, ['admin', 'receptionist', 'technician'], true)) {
            http_response_code(401);
            echo json_encode([]);
            return;
        }

        $query = trim((string) ($_GET['q'] ?? ''));
        if (mb_strlen($query) < 3) {
            echo json_encode([]);
            return;
        }

        $results = [];

        if ($role === 'admin' || $role === 'receptionist') {
            $patientModel = new patientModel(connect());
            $patients = $patientModel->searchPatients('patient_name', $query);
            foreach ((array) $patients as $p) {
                $results[] = [
                    'type'     => 'patient',
                    'label'    => (string) ($p['name'] ?? ''),
                    'subtitle' => (string) ($p['email'] ?? ''),
                    'url'      => '/lab_sync/index.php?controller=patientController&action=index',
                ];
            }
        }

        if ($role === 'admin' || $role === 'technician') {
            $appointmentModel = new AppointmentModel(connect());
            $tests = $appointmentModel->searchTestsCatalog($query, 8);
            foreach ((array) $tests as $t) {
                $results[] = [
                    'type'     => 'test',
                    'label'    => (string) ($t['test_name'] ?? ''),
                    'subtitle' => (string) ($t['category'] ?? ''),
                    'url'      => '/lab_sync/index.php?controller=TestCatalog&action=index',
                ];
            }

            $inventoryModel = new inventoryModel();
            $items = $inventoryModel->searchInventoryItems($query, 8);
            foreach ((array) $items as $i) {
                $results[] = [
                    'type'     => 'inventory',
                    'label'    => (string) ($i['item_name'] ?? ''),
                    'subtitle' => 'Inventory item',
                    'url'      => '/lab_sync/index.php?controller=inventoryController&action=index',
                ];
            }
        }

        if ($role === 'admin') {
            $inventoryModel = $inventoryModel ?? new inventoryModel();
            $suppliers = $inventoryModel->searchSuppliers($query, 8);
            foreach ((array) $suppliers as $s) {
                $results[] = [
                    'type'     => 'supplier',
                    'label'    => (string) ($s['supplier_name'] ?? ''),
                    'subtitle' => (string) ($s['email'] ?? $s['contact_no'] ?? ''),
                    'url'      => '/lab_sync/index.php?controller=inventoryController&action=index',
                ];
            }
        }

        echo json_encode(array_values($results));
    }
}
