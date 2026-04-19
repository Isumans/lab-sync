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

    public function countAppointmentsToday(): int {
        return $this->scalar(
            "SELECT COUNT(*) FROM appointment WHERE DATE(appointment_date) = CURDATE()"
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
