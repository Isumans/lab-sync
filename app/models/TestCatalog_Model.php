<?php
class TestCatalog {
    private $db;
    private $lastError = '';

    public function __construct($db) { $this->db = $db; }

    public function getAllTests() {
        $sql = $this->buildTestsListSql('test_id DESC');
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getTestsForAppointment() {
        $sql = $this->buildTestsListSql('test_name ASC');
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getLatestTestsForAppointment($limit = 3) {
        $safeLimit = max(1, (int)$limit);
        $sql = $this->buildTestsListSql('test_id DESC', $safeLimit);
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function createTestWithRelations(array $payload) {
        if (!$this->tableExists('tests')) {
            $this->lastError = 'Tests table not found.';
            return false;
        }

        if (!$this->tableExists('test_units') || !$this->tableExists('test_reference_ranges')) {
            $this->lastError = 'Required related tables are missing (test_units or test_reference_ranges).';
            return false;
        }

        $units = $this->normalizeUnits($payload['units'] ?? []);
        if (count($units) === 0) {
            $this->lastError = 'At least one valid unit is required.';
            return false;
        }

        $this->db->begin_transaction();
        try {
            $testId = $this->insertTestRecord($payload);

            foreach ($units as $unitIndex => $unit) {
                $unitId = $this->insertUnitRecord($testId, $unit, $unitIndex);
                foreach ($unit['ranges'] as $rangeIndex => $range) {
                    $this->insertRangeRecord($unitId, $range, $rangeIndex);
                }
            }

            $hasPartnerData = ($payload['partner_lab_id'] ?? null) !== null
                || trim((string)($payload['external_test_code'] ?? '')) !== ''
                || ($payload['charge_cost'] ?? null) !== null;

            if ($hasPartnerData && $this->tableExists('test_partner_charges')) {
                $this->insertPartnerChargeRecord($testId, $payload);
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollback();
            $this->lastError = $e->getMessage();
            error_log('createTestWithRelations failed: ' . $e->getMessage());
            return false;
        }
    }

    public function addTest($name, $category, $price, $description) {
        $stmt = $this->db->prepare("INSERT INTO tests (test_name, category, price, description) VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
            $this->lastError = 'Failed to prepare addTest statement.';
            return false;
        }

        $stmt->bind_param("ssds", $name, $category, $price, $description);
        return $stmt->execute();
    }

    public function getTestById($id) {
        $stmt = $this->db->prepare("SELECT * FROM tests WHERE test_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_assoc() : null;
    }

    public function updateTest($id, $name, $category, $price) {
        $updates = [
            'test_name' => $name,
            'category' => $category,
            'department' => $category,
            'price' => $price,
        ];

        $setParts = [];
        $values = [];
        foreach ($updates as $column => $value) {
            if ($this->columnExists('tests', $column)) {
                $setParts[] = "{$column} = ?";
                $values[] = $value;
            }
        }

        if (empty($setParts)) {
            $this->lastError = 'No updatable columns found in tests table.';
            return false;
        }

        $sql = "UPDATE tests SET " . implode(', ', $setParts) . " WHERE test_id = ?";
        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            $this->lastError = 'Failed to prepare updateTest statement.';
            return false;
        }

        $values[] = (int)$id;
        $types = '';
        foreach ($values as $value) {
            $types .= $this->getParamType($value);
        }

        $params = [$types];
        foreach ($values as $index => $value) {
            $params[] = &$values[$index];
        }

        call_user_func_array([$stmt, 'bind_param'], $params);
        return $stmt->execute();
    }

    public function deleteTest($id) {
        $stmt = $this->db->prepare("DELETE FROM tests WHERE test_id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function getFullTestById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM tests WHERE test_id = ?");
        if (!$stmt) { return null; }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $test = $result ? $result->fetch_assoc() : null;
        if (!$test) { return null; }

        $stmt2 = $this->db->prepare(
            "SELECT * FROM test_units WHERE test_id = ? ORDER BY display_order ASC, unit_index ASC"
        );
        $units = [];
        if ($stmt2) {
            $stmt2->bind_param("i", $id);
            $stmt2->execute();
            $res2 = $stmt2->get_result();
            while ($unit = $res2->fetch_assoc()) {
                $unitId = (int)$unit['unit_id'];
                $stmt3 = $this->db->prepare(
                    "SELECT * FROM test_reference_ranges WHERE unit_id = ? ORDER BY display_order ASC, range_index ASC"
                );
                $ranges = [];
                if ($stmt3) {
                    $stmt3->bind_param("i", $unitId);
                    $stmt3->execute();
                    $res3 = $stmt3->get_result();
                    while ($range = $res3->fetch_assoc()) {
                        $ranges[] = $range;
                    }
                }
                $unit['ranges'] = $ranges;
                $units[] = $unit;
            }
        }

        return ['test' => $test, 'units' => $units];
    }

    public function getTestEditData(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM tests WHERE test_id = ?");
        if (!$stmt) { return null; }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        if (!$row) { return null; }

        $discountCol = $this->resolveFirstExistingColumn('tests', ['discount_percent', 'discount']);
        $discount = $discountCol !== null ? (float)($row[$discountCol] ?? 0) : 0.0;

        return [
            'test_id'        => (int)$row['test_id'],
            'test_name'      => $row['test_name'] ?? '',
            'department'     => $row['department'] ?? $row['category'] ?? '',
            'default_unit'   => $row['default_unit'] ?? '',
            'print_name'     => $row['print_name'] ?? '',
            'description'    => $row['description'] ?? '',
            'cost_price'     => (float)($row['cost_price'] ?? 0),
            'discount'       => $discount,
            'price'          => (float)($row['price'] ?? 0),
            'is_active'      => (int)($row['is_active'] ?? 1),
            'report_comments'=> $row['report_comments'] ?? '',
            'test_code'      => $row['test_code'] ?? '',
        ];
    }

    public function updateTestFull(int $id, array $fields): bool {
        $discountCol = $this->resolveFirstExistingColumn('tests', ['discount_percent', 'discount']);

        $candidates = [
            'test_name'       => $fields['test_name'],
            'department'      => $fields['department'],
            'category'        => $fields['department'],
            'default_unit'    => $fields['default_unit'],
            'print_name'      => $fields['print_name'],
            'description'     => $fields['description'],
            'cost_price'      => $fields['cost_price'],
            'price'           => $fields['price'],
            'is_active'       => $fields['is_active'],
            'report_comments' => $fields['report_comments'],
        ];
        if ($discountCol !== null) {
            $candidates[$discountCol] = $fields['discount'];
        }

        $setParts = [];
        $values   = [];
        foreach ($candidates as $column => $value) {
            if ($this->columnExists('tests', $column)) {
                $setParts[] = "{$column} = ?";
                $values[]   = $value;
            }
        }

        if (empty($setParts)) {
            $this->lastError = 'No updatable columns found in tests table.';
            return false;
        }

        $sql  = "UPDATE tests SET " . implode(', ', $setParts) . " WHERE test_id = ?";
        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            $this->lastError = 'Failed to prepare updateTestFull statement.';
            return false;
        }

        $values[] = $id;
        $types    = '';
        foreach ($values as $v) { $types .= $this->getParamType($v); }

        $params = [$types];
        foreach ($values as $index => $v) { $params[] = &$values[$index]; }
        call_user_func_array([$stmt, 'bind_param'], $params);
        return $stmt->execute();
    }

    public function getLastError() {
        return $this->lastError;
    }

    private function buildTestsListSql($orderBy, $limit = null) {
        $departmentExpr = $this->columnExists('tests', 'department')
            ? 'department'
            : ($this->columnExists('tests', 'category') ? 'category' : "''");

        $labIdExpr = $this->columnExists('tests', 'lab_id') ? 'lab_id' : "''";
        $descriptionExpr = $this->columnExists('tests', 'description') ? 'description' : "''";

        $discountCol = $this->resolveFirstExistingColumn('tests', ['discount_percent', 'discount']);
        if ($this->columnExists('tests', 'price')) {
            $priceExpr = 'price';
        } elseif ($this->columnExists('tests', 'cost_price') && $discountCol !== null) {
            $priceExpr = "(cost_price - (cost_price * COALESCE({$discountCol}, 0) / 100))";
        } elseif ($this->columnExists('tests', 'cost_price')) {
            $priceExpr = 'cost_price';
        } else {
            $priceExpr = '0';
        }

        $sql = "
            SELECT
                test_id,
                test_name,
                {$departmentExpr} AS department,
                {$labIdExpr} AS lab_id,
                {$priceExpr} AS price,
                {$descriptionExpr} AS description
            FROM tests
            ORDER BY {$orderBy}
        ";

        if ($limit !== null) {
            $sql .= " LIMIT " . max(1, (int)$limit);
        }

        return $sql;
    }

    private function insertTestRecord(array $payload) {
        $discountColumn = $this->resolveFirstExistingColumn('tests', ['discount_percent', 'discount']);
        $publicPrice = max(0, (float)$payload['cost_price'] - ((float)$payload['cost_price'] * (float)$payload['discount'] / 100));

        $testRow = [
            'department' => $payload['department'],
            'category' => $payload['department'],
            'test_name' => $payload['test_name'],
            'print_name' => $payload['print_name'],
            'default_unit' => $payload['default_unit'],
            'description' => $payload['description'],
            'cost_price' => (float)$payload['cost_price'],
            'price' => $publicPrice,
            'is_active' => (int)$payload['is_active'],
            'report_comments' => $payload['report_comments'],
        ];

        if ($discountColumn !== null) {
            $testRow[$discountColumn] = (float)$payload['discount'];
        }

        $testId = $this->insertRow('tests', $testRow, true);

        if ($this->columnExists('tests', 'test_code')) {
            $code = 'TC-' . $testId;
            $stmt = $this->db->prepare("UPDATE tests SET test_code = ? WHERE test_id = ?");
            $stmt->bind_param('si', $code, $testId);
            $stmt->execute();
        }

        return $testId;
    }

    private function insertUnitRecord($testId, array $unit, $unitIndex) {
        $row = [
            'test_id' => (int)$testId,
            'unit_index' => (int)$unitIndex,
            'display_order' => (int)$unitIndex,
            'value_name' => $unit['value_name'],
            'unit_name' => $unit['unit_name'],
            'is_default' => $unitIndex === 0 ? 1 : 0,
        ];

        return $this->insertRow('test_units', $row, true);
    }

    private function insertRangeRecord($unitId, array $range, $rangeIndex) {
        $rangeMinColumn = $this->resolveFirstExistingColumn('test_reference_ranges', ['range_min', 'ref_min', 'min']);
        $rangeMaxColumn = $this->resolveFirstExistingColumn('test_reference_ranges', ['range_max', 'ref_max', 'max']);
        $labelColumn = $this->resolveFirstExistingColumn('test_reference_ranges', ['range_label', 'label']);

        $gender = trim((string)($range['gender'] ?? ''));
        if ($gender === '') {
            $gender = 'ALL';
        }

        $row = [
            'unit_id' => (int)$unitId,
            'range_index' => (int)$rangeIndex,
            'display_order' => (int)$rangeIndex,
            'gender' => $gender,
            'age_min' => $range['age_min'],
            'age_max' => $range['age_max'],
        ];

        if ($rangeMinColumn !== null) {
            $row[$rangeMinColumn] = $range['min'];
        }
        if ($rangeMaxColumn !== null) {
            $row[$rangeMaxColumn] = $range['max'];
        }
        if ($labelColumn !== null) {
            $row[$labelColumn] = $range['label'];
        }

        $this->insertRow('test_reference_ranges', $row, false);
    }

    private function insertPartnerChargeRecord($testId, array $payload) {
        $chargeColumn = $this->resolveFirstExistingColumn('test_partner_charges', ['charge_cost', 'charge_amount']);

        $row = [
            'test_id' => (int)$testId,
            'partner_lab_id' => $payload['partner_lab_id'],
            'external_test_code' => $payload['external_test_code'],
            'charge_currency' => 'RS',
        ];

        if ($chargeColumn !== null) {
            $row[$chargeColumn] = $payload['charge_cost'];
        }

        $this->insertRow('test_partner_charges', $row, false);
    }

    private function normalizeUnits($units) {
        $normalized = [];
        foreach ($units as $unit) {
            if (!is_array($unit)) {
                continue;
            }

            $valueName = trim((string)($unit['value_name'] ?? ''));
            $unitName = trim((string)($unit['unit_name'] ?? ''));
            if ($valueName === '' || $unitName === '') {
                continue;
            }

            $ranges = [];
            $rawRanges = isset($unit['ranges']) && is_array($unit['ranges']) ? $unit['ranges'] : [];
            foreach ($rawRanges as $range) {
                if (!is_array($range)) {
                    continue;
                }

                $ranges[] = [
                    'gender' => trim((string)($range['gender'] ?? '')),
                    'age_min' => $this->toNullableNumber($range['age_min'] ?? null),
                    'age_max' => $this->toNullableNumber($range['age_max'] ?? null),
                    'min' => $this->toNullableNumber($range['min'] ?? null),
                    'max' => $this->toNullableNumber($range['max'] ?? null),
                    'label' => trim((string)($range['label'] ?? '')),
                ];
            }

            if (count($ranges) === 0) {
                continue;
            }

            $normalized[] = [
                'value_name' => $valueName,
                'unit_name' => $unitName,
                'ranges' => $ranges,
            ];
        }

        return $normalized;
    }

    private function toNullableNumber($value) {
        if ($value === null) {
            return null;
        }

        $value = trim((string)$value);
        if ($value === '' || !is_numeric($value)) {
            return null;
        }

        return (float)$value;
    }

    private function insertRow($tableName, array $data, $returnInsertId = false) {
        $columns = [];
        $values = [];

        foreach ($data as $column => $value) {
            if (!$this->columnExists($tableName, $column)) {
                continue;
            }

            if ($value === null) {
                continue;
            }

            $columns[] = $column;
            $values[] = $value;
        }

        if (count($columns) === 0) {
            throw new Exception("No valid columns available for insert into {$tableName}.");
        }

        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $sql = "INSERT INTO {$tableName} (" . implode(', ', $columns) . ") VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Prepare failed for {$tableName}: " . $this->db->error);
        }

        $types = '';
        foreach ($values as $value) {
            $types .= $this->getParamType($value);
        }

        $params = [$types];
        foreach ($values as $index => $value) {
            $params[] = &$values[$index];
        }

        call_user_func_array([$stmt, 'bind_param'], $params);
        if (!$stmt->execute()) {
            throw new Exception("Insert failed for {$tableName}: " . $stmt->error);
        }

        if ($returnInsertId) {
            return (int)$stmt->insert_id;
        }

        return true;
    }

    private function getParamType($value) {
        if (is_int($value)) {
            return 'i';
        }
        if (is_float($value)) {
            return 'd';
        }

        return 's';
    }

    private function tableExists($tableName) {
        $tableName = $this->db->real_escape_string($tableName);
        $result = $this->db->query("SHOW TABLES LIKE '{$tableName}'");
        return $result && $result->num_rows > 0;
    }

    private function columnExists($tableName, $columnName) {
        $tableName = $this->db->real_escape_string($tableName);
        $columnName = $this->db->real_escape_string($columnName);
        $result = $this->db->query("SHOW COLUMNS FROM {$tableName} LIKE '{$columnName}'");
        return $result && $result->num_rows > 0;
    }

    private function resolveFirstExistingColumn($tableName, array $candidates) {
        foreach ($candidates as $candidate) {
            if ($this->columnExists($tableName, $candidate)) {
                return $candidate;
            }
        }

        return null;
    }


}
?>