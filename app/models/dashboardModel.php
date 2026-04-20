<?php

class DashboardModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // ── Shared helpers ──────────────────────────────────────────────────────

    private function tableExists(string $table): bool {
        $result = $this->db->query("SHOW TABLES LIKE '" . $this->db->real_escape_string($table) . "'");
        return $result && $result->num_rows > 0;
    }

    private function scalar(string $sql, string $types = '', array $params = []): int {
        if ($types === '') {
            $result = $this->db->query($sql);
            if (!$result) return 0;
            $row = $result->fetch_row();
            return $row ? intval($row[0]) : 0;
        }
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return 0;
        $stmt->bind_param($types, ...$params);
        if (!$stmt->execute()) return 0;
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_row() : null;
        return $row ? intval($row[0]) : 0;
    }

    // ── Admin metrics ────────────────────────────────────────────────────────

    public function countPatientsThisWeek(): int {
        return $this->scalar(
            "SELECT COUNT(*) FROM patients WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );
    }

    public function getPatientGrowthPercentage(): int {
        $thisWeek = $this->scalar(
            "SELECT COUNT(*) FROM patients WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );
        $previousWeek = $this->scalar(
            "SELECT COUNT(*) FROM patients
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)
               AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );

        if ($previousWeek <= 0) {
            return $thisWeek > 0 ? 100 : 0;
        }

        return intval(round((($thisWeek - $previousWeek) / $previousWeek) * 100));
    }

    public function countAppointmentsToday(): int {
        return $this->scalar(
            "SELECT COUNT(*) FROM appointment WHERE DATE(appointment_date) = CURDATE()"
        );
    }

    public function countAppointmentsYesterday(): int {
        return $this->scalar(
            "SELECT COUNT(*) FROM appointment WHERE DATE(appointment_date) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)"
        );
    }

    public function countPendingReports(): int {
        if (!$this->tableExists('appointment_tests')) {
            return $this->scalar(
                "SELECT COUNT(*) FROM appointment WHERE status NOT IN ('Completed','Cancelled')"
            );
        }
        return $this->scalar(
            "SELECT COUNT(*) FROM appointment_tests WHERE status IN ('PENDING','IN_PROGRESS','COMPLETED')"
        );
    }

    public function getTotalRevenueThisMonth(): float {
        if (!$this->tableExists('bills')) return 0.0;
        $result = $this->db->query(
            "SELECT COALESCE(SUM(total_amount),0) FROM bills
             WHERE status = 'PAID'
               AND MONTH(bill_date) = MONTH(CURDATE())
               AND YEAR(bill_date) = YEAR(CURDATE())"
        );
        if (!$result) return 0.0;
        $row = $result->fetch_row();
        return $row ? floatval($row[0]) : 0.0;
    }

    public function countUnpaidBills(): int {
        if (!$this->tableExists('bills')) return 0;
        return $this->scalar(
            "SELECT COUNT(*) FROM bills WHERE status IN ('PENDING','PARTIALLY_PAID')"
        );
    }

    public function getUnpaidBillsOutstandingAmount(): float {
        if (!$this->tableExists('bills')) return 0.0;

        $result = $this->db->query(
            "SELECT COALESCE(SUM(
                CASE
                    WHEN balance_due > 0 THEN balance_due
                    ELSE total_amount
                END
            ), 0) AS total_outstanding
            FROM bills
            WHERE status IN ('PENDING','PARTIALLY_PAID')"
        );

        if (!$result) return 0.0;
        $row = $result->fetch_assoc();
        return $row ? floatval($row['total_outstanding'] ?? 0) : 0.0;
    }

    public function getStaffCounts(): array {
        $result = $this->db->query(
            "SELECT role, COUNT(*) AS cnt FROM users
             WHERE role IN ('admin','receptionist','technician')
             GROUP BY role"
        );
        $counts = ['admin' => 0, 'receptionist' => 0, 'technician' => 0];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $counts[$row['role']] = intval($row['cnt']);
            }
        }
        return $counts;
    }

    public function countLowStockItems(): int {
        return $this->scalar(
            "SELECT COUNT(*) FROM inventory WHERE quantity <= reorder_level"
        );
    }

    public function getMonthlyRevenue(int $months = 6): array {
        if (!$this->tableExists('bills')) {
            return ['labels' => [], 'values' => []];
        }
        $result = $this->db->query(
            "SELECT DATE_FORMAT(bill_date, '%b %Y') AS label,
                    COALESCE(SUM(total_amount), 0) AS total
             FROM bills
             WHERE status = 'PAID'
               AND bill_date >= DATE_SUB(CURDATE(), INTERVAL {$months} MONTH)
             GROUP BY YEAR(bill_date), MONTH(bill_date)
             ORDER BY YEAR(bill_date), MONTH(bill_date)"
        );
        $labels = [];
        $values = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $labels[] = $row['label'];
                $values[] = round(floatval($row['total']), 2);
            }
        }
        return ['labels' => $labels, 'values' => $values];
    }

    public function getAppointmentStatusBreakdown(): array {
        $result = $this->db->query(
            "SELECT status, COUNT(*) AS cnt FROM appointment GROUP BY status"
        );
        $counts = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $counts[$row['status']] = intval($row['cnt']);
            }
        }
        return $counts;
    }

    public function getTestStatusBreakdown(): array {
        $counts = [
            'pending' => 0,
            'in_progress' => 0,
            'completed' => 0,
            'authenticated' => 0,
        ];

        if (!$this->tableExists('appointment_tests')) {
            $result = $this->db->query(
                "SELECT UPPER(COALESCE(status, '')) AS st, COUNT(*) AS cnt
                 FROM appointment
                 GROUP BY UPPER(COALESCE(status, ''))"
            );
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $status = $row['st'] ?? '';
                    $cnt = intval($row['cnt'] ?? 0);
                    if (in_array($status, ['PENDING', 'SCHEDULED'], true)) {
                        $counts['pending'] += $cnt;
                    } elseif (in_array($status, ['IN_PROGRESS', 'IN PROGRESS'], true)) {
                        $counts['in_progress'] += $cnt;
                    } elseif ($status === 'COMPLETED') {
                        $counts['completed'] += $cnt;
                    } elseif (in_array($status, ['AUTHENTICATED', 'AUTHORIZED', 'PRINTED'], true)) {
                        $counts['authenticated'] += $cnt;
                    }
                }
            }
            return $counts;
        }

        $result = $this->db->query(
            "SELECT UPPER(COALESCE(status, '')) AS st, COUNT(*) AS cnt
             FROM appointment_tests
             GROUP BY UPPER(COALESCE(status, ''))"
        );

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $status = $row['st'] ?? '';
                $cnt = intval($row['cnt'] ?? 0);

                if ($status === 'PENDING') {
                    $counts['pending'] += $cnt;
                } elseif ($status === 'IN_PROGRESS') {
                    $counts['in_progress'] += $cnt;
                } elseif ($status === 'COMPLETED') {
                    $counts['completed'] += $cnt;
                } elseif (in_array($status, ['AUTHENTICATED', 'AUTHORIZED', 'PRINTED'], true)) {
                    $counts['authenticated'] += $cnt;
                }
            }
        }

        return $counts;
    }

    public function getPaymentAgingData(): array {
        if (!$this->tableExists('bills')) {
            return ['d0_7' => 0, 'd8_30' => 0, 'd30plus' => 0, 'total_outstanding' => 0];
        }
        $result = $this->db->query(
            "SELECT
               COALESCE(SUM(CASE WHEN DATEDIFF(CURDATE(), due_date) BETWEEN 0 AND 7 THEN balance_due ELSE 0 END), 0) AS d0_7,
               COALESCE(SUM(CASE WHEN DATEDIFF(CURDATE(), due_date) BETWEEN 8 AND 30 THEN balance_due ELSE 0 END), 0) AS d8_30,
               COALESCE(SUM(CASE WHEN DATEDIFF(CURDATE(), due_date) > 30 THEN balance_due ELSE 0 END), 0) AS d30plus,
               COALESCE(SUM(balance_due), 0) AS total_outstanding
             FROM bills
             WHERE status IN ('PENDING','PARTIALLY_PAID')"
        );
        if (!$result) return ['d0_7' => 0, 'd8_30' => 0, 'd30plus' => 0, 'total_outstanding' => 0];
        $row = $result->fetch_assoc();
        return $row ? [
            'd0_7'             => floatval($row['d0_7']),
            'd8_30'            => floatval($row['d8_30']),
            'd30plus'          => floatval($row['d30plus']),
            'total_outstanding'=> floatval($row['total_outstanding']),
        ] : ['d0_7' => 0, 'd8_30' => 0, 'd30plus' => 0, 'total_outstanding' => 0];
    }

    public function getActiveStaffList(int $limit = 4): array {
        $limit = max(1, min(20, $limit));
        $result = $this->db->query(
            "SELECT user_id, username, role, status FROM users
             WHERE role IN ('admin','receptionist','technician')
             ORDER BY status ASC, role ASC
             LIMIT {$limit}"
        );
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getStaffTotals(): array {
        $result = $this->db->query(
            "SELECT status, COUNT(*) AS cnt FROM users
             WHERE role IN ('admin','receptionist','technician')
             GROUP BY status"
        );
        $totals = ['active' => 0, 'inactive' => 0];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $totals[$row['status']] = intval($row['cnt']);
            }
        }
        return $totals;
    }

    // ── Receptionist metrics ─────────────────────────────────────────────────

    public function countPendingPrescriptions(): int {
        if (!$this->tableExists('prescription_requests')) return 0;
        return $this->scalar(
            "SELECT COUNT(*) FROM prescription_requests WHERE status = 'Pending'"
        );
    }

    public function countPatientsRegisteredToday(): int {
        return $this->scalar(
            "SELECT COUNT(*) FROM patients WHERE DATE(created_at) = CURDATE()"
        );
    }

    public function getTodaysAppointments(int $limit = 10): array {
        $limit = max(1, min(50, $limit));
        $result = $this->db->query(
            "SELECT a.appointment_id, a.appointment_time, a.status, a.method,
                    p.patient_name, t.test_name
             FROM appointment a
             LEFT JOIN patients p ON p.patient_id = a.patient_id
             LEFT JOIN tests t ON t.test_id = a.test_id
             WHERE DATE(a.appointment_date) = CURDATE()
             ORDER BY a.appointment_time ASC
             LIMIT {$limit}"
        );
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getTodayAppointmentDensity(): array {
        $density = [
            'MOR' => 0,
            'LUN' => 0,
            'AFT' => 0,
            'EVE' => 0,
        ];

        $result = $this->db->query(
            "SELECT
                SUM(CASE WHEN TIME(a.appointment_time) < '12:00:00' THEN 1 ELSE 0 END) AS mor,
                SUM(CASE WHEN TIME(a.appointment_time) >= '12:00:00' AND TIME(a.appointment_time) < '14:00:00' THEN 1 ELSE 0 END) AS lun,
                SUM(CASE WHEN TIME(a.appointment_time) >= '14:00:00' AND TIME(a.appointment_time) < '17:00:00' THEN 1 ELSE 0 END) AS aft,
                SUM(CASE WHEN TIME(a.appointment_time) >= '17:00:00' THEN 1 ELSE 0 END) AS eve
            FROM appointment a
            WHERE DATE(a.appointment_date) = CURDATE()"
        );

        if ($result) {
            $row = $result->fetch_assoc();
            if ($row) {
                $density['MOR'] = intval($row['mor'] ?? 0);
                $density['LUN'] = intval($row['lun'] ?? 0);
                $density['AFT'] = intval($row['aft'] ?? 0);
                $density['EVE'] = intval($row['eve'] ?? 0);
            }
        }

        return $density;
    }

    public function getTodayAppointmentStatusSnapshot(): array {
        $counts = [
            'confirmed' => 0,
            'completed' => 0,
            'in_progress' => 0,
            'cancelled' => 0,
        ];

        $result = $this->db->query(
            "SELECT UPPER(COALESCE(status, '')) AS st, COUNT(*) AS cnt
             FROM appointment
             WHERE DATE(appointment_date) = CURDATE()
             GROUP BY UPPER(COALESCE(status, ''))"
        );

        if (!$result) {
            return $counts;
        }

        while ($row = $result->fetch_assoc()) {
            $status = $row['st'] ?? '';
            $cnt = intval($row['cnt'] ?? 0);

            if (in_array($status, ['CONFIRMED', 'SCHEDULED'], true)) {
                $counts['confirmed'] += $cnt;
                continue;
            }

            if ($status === 'COMPLETED') {
                $counts['completed'] += $cnt;
                continue;
            }

            if (in_array($status, ['IN_PROGRESS', 'IN PROGRESS'], true)) {
                $counts['in_progress'] += $cnt;
                continue;
            }

            if ($status === 'CANCELLED') {
                $counts['cancelled'] += $cnt;
            }
        }

        return $counts;
    }

    public function getTodayAppointmentTypeSplit(): array {
        $types = [
            'physical' => 0,
            'online_scheduled' => 0,
            'online_home_visit' => 0,
        ];

        $homeCollectionColumn = $this->resolveColumn('appointment', ['home_collection']);
        $homeCollectionExpr = $homeCollectionColumn !== null
            ? "COALESCE(a.{$homeCollectionColumn}, 0)"
            : '0';

        $result = $this->db->query(
            "SELECT
                SUM(CASE
                    WHEN LOWER(COALESCE(a.method, '')) = 'physical' THEN 1
                    ELSE 0
                END) AS physical_count,
                SUM(CASE
                    WHEN LOWER(COALESCE(a.method, '')) = 'online' AND {$homeCollectionExpr} = 1 THEN 1
                    ELSE 0
                END) AS online_home_count,
                SUM(CASE
                    WHEN LOWER(COALESCE(a.method, '')) = 'online' AND {$homeCollectionExpr} = 0 THEN 1
                    WHEN LOWER(COALESCE(a.method, '')) = 'call' THEN 1
                    ELSE 0
                END) AS online_scheduled_count
             FROM appointment a
             WHERE DATE(a.appointment_date) = CURDATE()"
        );

        if ($result) {
            $row = $result->fetch_assoc();
            if ($row) {
                $types['physical'] = intval($row['physical_count'] ?? 0);
                $types['online_home_visit'] = intval($row['online_home_count'] ?? 0);
                $types['online_scheduled'] = intval($row['online_scheduled_count'] ?? 0);
            }
        }

        return $types;
    }

    public function getTopOrderedTestsToday(int $limit = 4): array {
        $limit = max(1, min(10, $limit));

        if ($this->tableExists('appointment_items')) {
            $quantityColumn = $this->resolveColumn('appointment_items', ['quantity']);
            $quantityExpr = $quantityColumn !== null
                ? "COALESCE(ai.{$quantityColumn}, 1)"
                : '1';

            $result = $this->db->query(
                "SELECT
                    ai.test_id,
                    COALESCE(t.test_name, CONCAT('Test #', ai.test_id)) AS test_name,
                    SUM({$quantityExpr}) AS total_orders
                 FROM appointment_items ai
                 INNER JOIN appointment a ON a.appointment_id = ai.appointment_id
                 LEFT JOIN tests t ON t.test_id = ai.test_id
                 WHERE DATE(a.appointment_date) = CURDATE()
                 GROUP BY ai.test_id, COALESCE(t.test_name, CONCAT('Test #', ai.test_id))
                 ORDER BY total_orders DESC, test_name ASC
                 LIMIT {$limit}"
            );

            if ($result) {
                return $result->fetch_all(MYSQLI_ASSOC);
            }
        }

        if ($this->tableExists('appointment_tests')) {
            $result = $this->db->query(
                "SELECT
                    at2.test_id,
                    COALESCE(t.test_name, CONCAT('Test #', at2.test_id)) AS test_name,
                    COUNT(*) AS total_orders
                 FROM appointment_tests at2
                 INNER JOIN appointment a ON a.appointment_id = at2.appointment_id
                 LEFT JOIN tests t ON t.test_id = at2.test_id
                 WHERE DATE(a.appointment_date) = CURDATE()
                 GROUP BY at2.test_id, COALESCE(t.test_name, CONCAT('Test #', at2.test_id))
                 ORDER BY total_orders DESC, test_name ASC
                 LIMIT {$limit}"
            );

            if ($result) {
                return $result->fetch_all(MYSQLI_ASSOC);
            }
        }

        $result = $this->db->query(
            "SELECT
                a.test_id,
                COALESCE(t.test_name, CONCAT('Test #', a.test_id)) AS test_name,
                COUNT(*) AS total_orders
             FROM appointment a
             LEFT JOIN tests t ON t.test_id = a.test_id
             WHERE DATE(a.appointment_date) = CURDATE()
             GROUP BY a.test_id, COALESCE(t.test_name, CONCAT('Test #', a.test_id))
             ORDER BY total_orders DESC, test_name ASC
             LIMIT {$limit}"
        );

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // ── Technician metrics ───────────────────────────────────────────────────

    public function countReportsPendingEntry(): int {
        if (!$this->tableExists('appointment_tests')) {
            return $this->scalar(
                "SELECT COUNT(*) FROM appointment WHERE status NOT IN ('Completed','Cancelled','Pending')"
            );
        }
        return $this->scalar(
            "SELECT COUNT(*) FROM appointment_tests WHERE status IN ('PENDING','IN_PROGRESS')"
        );
    }

    public function countReportsAwaitingAuthorization(): int {
        if (!$this->tableExists('appointment_tests')) return 0;
        return $this->scalar(
            "SELECT COUNT(*) FROM appointment_tests WHERE status = 'COMPLETED'"
        );
    }

    public function countReportsCompletedToday(): int {
        if (!$this->tableExists('appointment_tests')) return 0;
        $authorizedAtCol = $this->resolveColumn('appointment_tests', ['authorized_at', 'completed_at']);
        if ($authorizedAtCol === null) {
            return $this->scalar(
                "SELECT COUNT(*) FROM appointment_tests WHERE status IN ('AUTHORIZED','PRINTED')"
            );
        }
        return $this->scalar(
            "SELECT COUNT(*) FROM appointment_tests
             WHERE status IN ('AUTHORIZED','PRINTED')
               AND DATE({$authorizedAtCol}) = CURDATE()"
        );
    }

    public function getPendingEntryDayComparison(): array {
        $totals = [
            'today' => 0,
            'yesterday' => 0,
            'delta' => 0,
        ];

        if ($this->tableExists('appointment_tests')) {
            $result = $this->db->query(
                "SELECT
                    SUM(CASE WHEN DATE(a.appointment_date) = CURDATE() THEN 1 ELSE 0 END) AS today_count,
                    SUM(CASE WHEN DATE(a.appointment_date) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) THEN 1 ELSE 0 END) AS yesterday_count
                 FROM appointment_tests at2
                 INNER JOIN appointment a ON a.appointment_id = at2.appointment_id
                 WHERE at2.status IN ('PENDING', 'IN_PROGRESS')"
            );

            if ($result) {
                $row = $result->fetch_assoc();
                $totals['today'] = intval($row['today_count'] ?? 0);
                $totals['yesterday'] = intval($row['yesterday_count'] ?? 0);
            }
        } else {
            $result = $this->db->query(
                "SELECT
                    SUM(CASE WHEN DATE(a.appointment_date) = CURDATE() THEN 1 ELSE 0 END) AS today_count,
                    SUM(CASE WHEN DATE(a.appointment_date) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) THEN 1 ELSE 0 END) AS yesterday_count
                 FROM appointment a
                 WHERE UPPER(COALESCE(a.status, '')) NOT IN ('COMPLETED', 'CANCELLED')"
            );

            if ($result) {
                $row = $result->fetch_assoc();
                $totals['today'] = intval($row['today_count'] ?? 0);
                $totals['yesterday'] = intval($row['yesterday_count'] ?? 0);
            }
        }

        $totals['delta'] = $totals['today'] - $totals['yesterday'];
        return $totals;
    }

    public function getTechnicianWorkflowBreakdown(): array {
        $breakdown = [
            'data_entry' => 0,
            'review' => 0,
            'authorized' => 0,
            'total_active' => 0,
        ];

        if (!$this->tableExists('appointment_tests')) {
            $result = $this->db->query(
                "SELECT UPPER(COALESCE(a.status, '')) AS st, COUNT(*) AS cnt
                 FROM appointment a
                 GROUP BY UPPER(COALESCE(a.status, ''))"
            );

            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $status = $row['st'] ?? '';
                    $count = intval($row['cnt'] ?? 0);

                    if (in_array($status, ['PENDING', 'SCHEDULED', 'IN_PROGRESS', 'IN PROGRESS'], true)) {
                        $breakdown['data_entry'] += $count;
                        continue;
                    }

                    if ($status === 'COMPLETED') {
                        $breakdown['review'] += $count;
                        continue;
                    }

                    if (in_array($status, ['AUTHORIZED', 'AUTHENTICATED', 'PRINTED'], true)) {
                        $breakdown['authorized'] += $count;
                    }
                }
            }

            $breakdown['total_active'] = $breakdown['data_entry'] + $breakdown['review'] + $breakdown['authorized'];
            return $breakdown;
        }

        $result = $this->db->query(
            "SELECT UPPER(COALESCE(status, '')) AS st, COUNT(*) AS cnt
             FROM appointment_tests
             GROUP BY UPPER(COALESCE(status, ''))"
        );

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $status = $row['st'] ?? '';
                $count = intval($row['cnt'] ?? 0);

                if (in_array($status, ['PENDING', 'IN_PROGRESS'], true)) {
                    $breakdown['data_entry'] += $count;
                    continue;
                }

                if ($status === 'COMPLETED') {
                    $breakdown['review'] += $count;
                    continue;
                }

                if (in_array($status, ['AUTHORIZED', 'AUTHENTICATED', 'PRINTED'], true)) {
                    $breakdown['authorized'] += $count;
                }
            }
        }

        $breakdown['total_active'] = $breakdown['data_entry'] + $breakdown['review'] + $breakdown['authorized'];
        return $breakdown;
    }

    public function getTechnicianTestVolumeByCategory(int $limit = 4): array {
        $limit = max(1, min(10, $limit));
        $categoryColumn = $this->resolveColumn('tests', ['department', 'category']);
        $categoryExpr = $categoryColumn !== null
            ? "COALESCE(NULLIF(TRIM(t.{$categoryColumn}), ''), 'Uncategorized')"
            : "'Uncategorized'";

        if ($this->tableExists('appointment_tests')) {
            $result = $this->db->query(
                "SELECT
                    {$categoryExpr} AS category_name,
                    COUNT(*) AS total_volume
                 FROM appointment_tests at2
                 INNER JOIN appointment a ON a.appointment_id = at2.appointment_id
                 LEFT JOIN tests t ON t.test_id = at2.test_id
                 WHERE DATE(a.appointment_date) = CURDATE()
                   AND UPPER(COALESCE(at2.status, '')) IN ('COMPLETED', 'AUTHORIZED', 'AUTHENTICATED', 'PRINTED')
                 GROUP BY {$categoryExpr}
                 ORDER BY total_volume DESC, category_name ASC
                 LIMIT {$limit}"
            );

            if ($result) {
                return $result->fetch_all(MYSQLI_ASSOC);
            }
        }

        $result = $this->db->query(
            "SELECT
                {$categoryExpr} AS category_name,
                COUNT(*) AS total_volume
             FROM appointment a
             LEFT JOIN tests t ON t.test_id = a.test_id
             WHERE DATE(a.appointment_date) = CURDATE()
               AND UPPER(COALESCE(a.status, '')) IN ('COMPLETED', 'AUTHORIZED', 'AUTHENTICATED', 'PRINTED')
             GROUP BY {$categoryExpr}
             ORDER BY total_volume DESC, category_name ASC
             LIMIT {$limit}"
        );

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getCriticalInventoryLevels(int $limit = 4): array {
        $limit = max(1, min(12, $limit));
        $rows = [];

        $result = $this->db->query(
            "SELECT i.item_name, i.quantity, i.reorder_level
             FROM inventory i
             WHERE i.reorder_level > 0
             ORDER BY (i.quantity / i.reorder_level) ASC, i.item_name ASC
             LIMIT {$limit}"
        );

        if (!$result) {
            return $rows;
        }

        while ($row = $result->fetch_assoc()) {
            $quantity = intval($row['quantity'] ?? 0);
            $reorderLevel = max(1, intval($row['reorder_level'] ?? 0));
            $ratio = (int) round(($quantity / $reorderLevel) * 100);
            $ratio = max(0, min(140, $ratio));

            $severity = 'healthy';
            if ($ratio <= 35) {
                $severity = 'critical';
            } elseif ($ratio <= 80) {
                $severity = 'warning';
            }

            $rows[] = [
                'item_name' => $row['item_name'] ?? 'Inventory item',
                'quantity' => $quantity,
                'reorder_level' => $reorderLevel,
                'ratio_percent' => $ratio,
                'severity' => $severity,
            ];
        }

        return $rows;
    }

    public function getTechnicianCompletedTarget(): int {
        $target = intval(getenv('TECHNICIAN_COMPLETED_TARGET') ?: 100);
        return $target > 0 ? $target : 100;
    }

    public function getLowStockItems(int $limit = 8): array {
        $limit = max(1, min(20, $limit));
        $result = $this->db->query(
            "SELECT i.item_name, i.quantity, i.reorder_level, s.supplier_name
             FROM inventory i
             LEFT JOIN suppliers s ON s.supplier_id = i.supplier_id
             WHERE i.quantity <= i.reorder_level
             ORDER BY (i.reorder_level - i.quantity) DESC
             LIMIT {$limit}"
        );
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getPendingReportsList(int $limit = 10): array {
        $limit = max(1, min(50, $limit));
        if (!$this->tableExists('appointment_tests')) {
            $result = $this->db->query(
                "SELECT a.appointment_id, a.appointment_date, a.status,
                        p.patient_name, t.test_name
                 FROM appointment a
                 LEFT JOIN patients p ON p.patient_id = a.patient_id
                 LEFT JOIN tests t ON t.test_id = a.test_id
                 WHERE a.status NOT IN ('Completed','Cancelled')
                 ORDER BY a.appointment_date ASC, a.appointment_time ASC
                 LIMIT {$limit}"
            );
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        }
        $result = $this->db->query(
            "SELECT at2.appointment_id, at2.test_id, at2.status AS report_status,
                    a.appointment_date,
                    p.patient_name, t.test_name
             FROM appointment_tests at2
             JOIN appointment a ON a.appointment_id = at2.appointment_id
             LEFT JOIN patients p ON p.patient_id = a.patient_id
             LEFT JOIN tests t ON t.test_id = at2.test_id
             WHERE at2.status IN ('PENDING','IN_PROGRESS','COMPLETED')
             ORDER BY a.appointment_date ASC
             LIMIT {$limit}"
        );
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function resolveColumn(string $table, array $candidates): ?string {
        foreach ($candidates as $col) {
            $escaped = $this->db->real_escape_string($col);
            $res = $this->db->query("SHOW COLUMNS FROM `{$table}` LIKE '{$escaped}'");
            if ($res && $res->num_rows > 0) return $col;
        }
        return null;
    }
}
