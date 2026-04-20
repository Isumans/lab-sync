<?php
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';
}

require_once MODEL_PATH . '/TestCatalog_Model.php';
require_once 'C:\xampp\htdocs\lab_sync\config\db.php';

// class TestCatalogController {
//     public function index() {
//         connect();
//         global $conn; 
//         $model = new TestCatalog($conn);
//         $packages = $model->getAllTests();
//         extract(['packages' => $packages]);
//         include 'C:\xampp\htdocs\lab_sync\app\views\receptionist\test_catalog.php';
//     }
// }
class TestCatalogController {

    private $db;

     public function __construct() {
        
        // Verify authentication
        if (!isset($_SESSION['user_id'])) {
            header('Location: /lab_sync/index.php?controller=Auth&action=login');
            exit;
        }
        $this->db = connect();
    }
    public function index($role) {
        $role=$_GET['role'] ?? '';
        $model = new TestCatalog($this->db);
        $packages = $model->getAllTests();
        if($packages === false) {
            echo "Error fetching tests.";
            return;
        }else{
            // extract(['packages' => $packages]);
            $action = 'index';
            include VIEW_PATH . '/receptionist/test_catalog.php';
        }
    }

    public function getTestsForAppointment() {
        header('Content-Type: application/json');

        $model = new TestCatalog($this->db);
        $tests = $model->getTestsForAppointment();

        echo json_encode([
            'success' => true,
            'data' => $tests,
        ]);
    }

    public function getLatestTestsForAppointment() {
        header('Content-Type: application/json');

        $model = new TestCatalog($this->db);
        $tests = $model->getLatestTestsForAppointment(3);

        echo json_encode([
            'success' => true,
            'data' => $tests,
        ]);
    }
        
    public function add_test($role) {
        $role=$_GET['role'] ?? '';
        $partnerLabs = $this->getPartnerLabs();
        include VIEW_PATH . '/receptionist/add_test.php';
    }

    public function store($role) {
        $role=$_GET['role'] ?? '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $costPrice = trim((string)($_POST['cost_price'] ?? ''));
                $discount = trim((string)($_POST['discount'] ?? ''));
                $partnerHospital = trim((string)($_POST['partner_hospital'] ?? ''));
                $chargeCost = trim((string)($_POST['charge_cost'] ?? ''));

                $units = isset($_POST['units']) && is_array($_POST['units'])
                    ? $_POST['units']
                    : $this->buildUnitsFromLegacyPost($_POST);

                $payload = [
                    'department' => trim((string)($_POST['department'] ?? '')),
                    'test_name' => trim((string)($_POST['test_name'] ?? '')),
                    'default_unit' => trim((string)($_POST['default_unit'] ?? '')),
                    'print_name' => trim((string)($_POST['print_name'] ?? '')),
                    'description' => trim((string)($_POST['description'] ?? '')),
                    'cost_price' => ($costPrice !== '' && is_numeric($costPrice)) ? (float)$costPrice : null,
                    'discount' => ($discount !== '' && is_numeric($discount)) ? (float)$discount : 0.0,
                    'is_active' => isset($_POST['is_active']) ? 1 : 0,
                    'external_test_code' => trim((string)($_POST['external_test_code'] ?? '')),
                    'partner_lab_id' => ($partnerHospital !== '' && ctype_digit($partnerHospital)) ? (int)$partnerHospital : null,
                    'charge_cost' => ($chargeCost !== '' && is_numeric($chargeCost)) ? (float)$chargeCost : null,
                    'report_comments' => trim((string)($_POST['report_comments'] ?? '')),
                    'units' => $units,
                ];

                $errors = [];
                if ($payload['department'] === '') $errors[] = 'Department is required.';
                if ($payload['test_name'] === '') $errors[] = 'Test name is required.';
                if ($payload['default_unit'] === '') $errors[] = 'Default unit is required.';
                if ($payload['print_name'] === '') $errors[] = 'Print name is required.';
                if ($payload['cost_price'] === null) $errors[] = 'Cost price is required and must be numeric.';
                if (!is_array($payload['units']) || count($payload['units']) === 0) {
                    $errors[] = 'At least one unit is required.';
                }

                foreach ($payload['units'] as $unitIndex => $unit) {
                    $valueName = trim((string)($unit['value_name'] ?? ''));
                    $unitName = trim((string)($unit['unit_name'] ?? ''));
                    if ($valueName === '' || $unitName === '') {
                        $errors[] = 'Each unit must include value name and unit name.';
                        break;
                    }

                    $ranges = isset($unit['ranges']) && is_array($unit['ranges']) ? $unit['ranges'] : [];
                    if (count($ranges) === 0) {
                        $errors[] = 'Each unit must have at least one reference range.';
                        break;
                    }

                    foreach ($ranges as $rangeIndex => $range) {
                        $min = trim((string)($range['min'] ?? ''));
                        $max = trim((string)($range['max'] ?? ''));
                        if ($min !== '' && !is_numeric($min)) {
                            $errors[] = 'Reference range minimum must be numeric.';
                            break 2;
                        }
                        if ($max !== '' && !is_numeric($max)) {
                            $errors[] = 'Reference range maximum must be numeric.';
                            break 2;
                        }
                    }
                }

                if ($partnerHospital !== '' && !ctype_digit($partnerHospital)) {
                    $errors[] = 'Invalid partner hospital selection.';
                }

                if ($chargeCost !== '' && !is_numeric($chargeCost)) {
                    $errors[] = 'Charge cost must be numeric.';
                }

                if (count($errors) > 0) {
                    $_SESSION['flash'] = ['type' => 'error', 'message' => implode(' ', $errors)];
                    header("Location: /lab_sync/index.php?controller=TestCatalog&action=add_test&role=" . urlencode($role));
                    exit;
                }

                $model = new TestCatalog($this->db);
                if ($model->existsTestName($payload['test_name'])) {
                    $_SESSION['flash'] = ['type' => 'error', 'message' => 'A test with this name already exists. Please use a different test name.'];
                    header("Location: /lab_sync/index.php?controller=TestCatalog&action=add_test&role=" . urlencode($role));
                    exit;
                }

                $success = $model->createTestWithRelations($payload);
                if ($success) {
                    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Test added successfully.'];
                    header("Location: /lab_sync/index.php?controller=TestCatalog&action=index&role=" . urlencode($role));
                    exit;
                }

                $errorMessage = $model->getLastError() ?: 'Error adding test.';
                $_SESSION['flash'] = ['type' => 'error', 'message' => $errorMessage];
                header("Location: /lab_sync/index.php?controller=TestCatalog&action=add_test&role=" . urlencode($role));
                exit;
            } catch (Throwable $e) {
                error_log('TestCatalog store failed: ' . $e->getMessage());
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Unexpected error while saving test. Please try again.'];
                header("Location: /lab_sync/index.php?controller=TestCatalog&action=add_test&role=" . urlencode($role));
                exit;
            }
        }
    }

    private function buildUnitsFromLegacyPost(array $post) {
        $unitNames = isset($post['unit_names']) && is_array($post['unit_names']) ? $post['unit_names'] : [];
        $conversionFactors = isset($post['conversion_factors']) && is_array($post['conversion_factors']) ? $post['conversion_factors'] : [];

        if (count($unitNames) === 0 && count($conversionFactors) === 0) {
            return [];
        }

        $genders = isset($post['range_gender']) && is_array($post['range_gender']) ? $post['range_gender'] : [];
        $ageMins = isset($post['range_age_min']) && is_array($post['range_age_min']) ? $post['range_age_min'] : [];
        $ageMaxs = isset($post['range_age_max']) && is_array($post['range_age_max']) ? $post['range_age_max'] : [];
        $mins = isset($post['range_min']) && is_array($post['range_min']) ? $post['range_min'] : [];
        $maxs = isset($post['range_max']) && is_array($post['range_max']) ? $post['range_max'] : [];
        $labels = isset($post['range_label']) && is_array($post['range_label']) ? $post['range_label'] : [];
        $criticalValues = isset($post['critical_value']) && is_array($post['critical_value']) ? $post['critical_value'] : [];

        $rangeCount = max(count($genders), count($ageMins), count($ageMaxs), count($mins), count($maxs), count($labels), count($criticalValues));
        $ranges = [];

        for ($i = 0; $i < $rangeCount; $i++) {
            $gender = trim((string)($genders[$i] ?? ''));
            $ageMin = trim((string)($ageMins[$i] ?? ''));
            $ageMax = trim((string)($ageMaxs[$i] ?? ''));
            $min = trim((string)($mins[$i] ?? ''));
            $max = trim((string)($maxs[$i] ?? ''));
            $label = trim((string)($labels[$i] ?? ($criticalValues[$i] ?? '')));

            if ($gender === '' && $ageMin === '' && $ageMax === '' && $min === '' && $max === '' && $label === '') {
                continue;
            }

            $ranges[] = [
                'gender' => $gender,
                'age_min' => $ageMin,
                'age_max' => $ageMax,
                'min' => $min,
                'max' => $max,
                'label' => $label,
            ];
        }

        $units = [];
        $unitCount = max(count($unitNames), count($conversionFactors));
        for ($i = 0; $i < $unitCount; $i++) {
            $valueName = trim((string)($unitNames[$i] ?? ''));
            $unitName = trim((string)($conversionFactors[$i] ?? ''));

            if ($valueName === '' && $unitName === '') {
                continue;
            }

            $units[] = [
                'value_name' => $valueName,
                'unit_name' => $unitName,
                'ranges' => $ranges,
            ];
        }

        return $units;
    }

    private function getPartnerLabs() {
        $partnerLabs = [];
        $sql = "SELECT id, lab_name FROM partner_labs ORDER BY lab_name ASC";
        $result = $this->db->query($sql);
        if ($result) {
            $partnerLabs = $result->fetch_all(MYSQLI_ASSOC);
        }

        return $partnerLabs;
    }
    public function getTestDetails() {
        if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
            http_response_code(400);
            exit;
        }
        $testId = (int)($_GET['test_id'] ?? 0);
        if ($testId <= 0) {
            http_response_code(400);
            echo '<div class="test-catalog-view-error"><h3>Invalid test ID.</h3></div>';
            exit;
        }
        $model = new TestCatalog($this->db);
        $data  = $model->getFullTestById($testId);
        if ($data === null) {
            http_response_code(404);
            echo '<div class="test-catalog-view-error"><h3>Test not found.</h3></div>';
            exit;
        }
        $test  = $data['test'];
        $units = $data['units'];
        include VIEW_PATH . '/receptionist/get_test_details.php';
        exit;
    }

    public function getTestEditData() {
        header('Content-Type: application/json');
        if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Bad request.']);
            exit;
        }
        $testId = (int)($_GET['test_id'] ?? 0);
        if ($testId <= 0) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid test ID.']);
            exit;
        }
        $model = new TestCatalog($this->db);
        $data  = $model->getTestEditData($testId);
        if ($data === null) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Test not found.']);
            exit;
        }
        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    public function updateTestAjax() {
        header('Content-Type: application/json');
        if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Bad request.']);
            exit;
        }
        if (!$this->validateCsrfHeader()) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'CSRF validation failed.']);
            exit;
        }
        $body   = json_decode(file_get_contents('php://input'), true);
        $testId = (int)($body['test_id'] ?? 0);
        if ($testId <= 0) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid test ID.']);
            exit;
        }
        $testName = trim((string)($body['test_name'] ?? ''));
        if ($testName === '') {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Test name is required.']);
            exit;
        }
        $costPrice = max(0.0, (float)($body['cost_price'] ?? 0));
        $discount  = min(100.0, max(0.0, (float)($body['discount'] ?? 0)));
        $price     = $costPrice - ($costPrice * $discount / 100);

        $fields = [
            'test_name'       => $testName,
            'department'      => trim((string)($body['department'] ?? '')),
            'default_unit'    => trim((string)($body['default_unit'] ?? '')),
            'print_name'      => trim((string)($body['print_name'] ?? '')),
            'description'     => trim((string)($body['description'] ?? '')),
            'cost_price'      => $costPrice,
            'discount'        => $discount,
            'price'           => $price,
            'is_active'       => (int)(bool)($body['is_active'] ?? 1),
            'report_comments' => trim((string)($body['report_comments'] ?? '')),
        ];

        $model   = new TestCatalog($this->db);
        $success = $model->updateTestFull($testId, $fields);
        if ($success) {
            echo json_encode(['status' => 'success', 'message' => 'Test updated successfully.']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $model->getLastError() ?: 'Failed to update test.']);
        }
        exit;
    }

    public function deleteTestAjax() {
        header('Content-Type: application/json');
        if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Bad request.']);
            exit;
        }
        if (!$this->validateCsrfHeader()) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'CSRF validation failed.']);
            exit;
        }
        $body   = json_decode(file_get_contents('php://input'), true);
        $testId = (int)($body['test_id'] ?? 0);
        if ($testId <= 0) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid test ID.']);
            exit;
        }
        $model   = new TestCatalog($this->db);
        $success = $model->deleteTest($testId);
        if ($success) {
            echo json_encode(['status' => 'success', 'message' => 'Test deleted successfully.']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete test.']);
        }
        exit;
    }

    private function validateCsrfHeader(): bool {
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        if ($sessionToken === '') { return false; }
        $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return hash_equals($sessionToken, $headerToken);
    }

    public function edit_test($role) {
        $role = $_GET['role'] ?? '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $testId = trim($_POST['test_id'] ?? '');
            $testName = trim($_POST['test_name'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $price = trim($_POST['price'] ?? '');

            $conn1 = connect();
            $model2 = new TestCatalog($conn1);
            if (isset($_POST['edit'])) {
                $errors = [];
                if ($testId === '' || !ctype_digit((string)$testId)) $errors[] = 'Invalid test id.';
                if ($testName === '') $errors[] = 'Test name is required.';
                if ($price === '' || !is_numeric($price)) $errors[] = 'Price is required and must be numeric.';
                if (count($errors) > 0) {
                    $_SESSION['flash'] = ['type' => 'error', 'message' => implode(' ', $errors)];
                    header("Location: /lab_sync/index.php?controller=TestCatalog&action=index&role=" . urlencode($role));
                    exit;
                }

                $success = $model2->updateTest((int)$testId, $testName, $category, (float)$price);
                if ($success) {
                    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Test updated successfully.'];
                    header("Location: /lab_sync/index.php?controller=TestCatalog&action=index&role=" . urlencode($role));
                    exit;
                } else {
                    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Error updating test.'];
                    header("Location: /lab_sync/index.php?controller=TestCatalog&action=index&role=" . urlencode($role));
                    exit;
                }
            } elseif (isset($_POST['delete'])) {
                if ($testId === '' || !ctype_digit((string)$testId)) {
                    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid test id for deletion.'];
                    header("Location: /lab_sync/index.php?controller=TestCatalog&action=index&role=" . urlencode($role));
                    exit;
                }
                $success = $model2->deleteTest((int)$testId);
                if ($success) {
                    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Test deleted successfully.'];
                    header("Location: /lab_sync/index.php?controller=TestCatalog&action=index&role=" . urlencode($role));
                    exit;

                } else {
                    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Error deleting test.'];
                    header("Location: /lab_sync/index.php?controller=TestCatalog&action=index&role=" . urlencode($role));
                    exit;
                }
            }
        }
    }
}