<?php
class supplierModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAllSuppliersWithItems($searchBy = '', $searchQuery = '') {
        $sql = "SELECT s.supplier_id, s.supplier_name, s.contact_no, s.email,
                       GROUP_CONCAT(si.item_name ORDER BY si.item_name SEPARATOR ', ') AS supplying_items,
                       COUNT(si.supplier_item_id) AS item_count
                FROM suppliers s
                LEFT JOIN supplier_items si ON si.supplier_id = s.supplier_id";

        $searchBy = trim((string) $searchBy);
        $searchQuery = trim((string) $searchQuery);

        $types = '';
        $params = [];

        if ($searchQuery !== '') {
            if ($searchBy === 'supplier_id') {
                if (!ctype_digit($searchQuery)) {
                    return [];
                }
                $sql .= " WHERE s.supplier_id = ?";
                $types = 'i';
                $params[] = (int) $searchQuery;
            } else {
                $sql .= " WHERE s.email LIKE ?";
                $types = 's';
                $params[] = '%' . $searchQuery . '%';
            }
        }

        $sql .= " GROUP BY s.supplier_id, s.supplier_name, s.contact_no, s.email
              ORDER BY s.supplier_id ASC";

        if ($types === '') {
            $result = $this->db->query($sql);
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();

        return $rows;
    }

    public function emailExists($email, $excludeSupplierId = null) {
        if ($excludeSupplierId !== null) {
            $stmt = $this->db->prepare("SELECT supplier_id FROM suppliers WHERE email = ? AND supplier_id <> ? LIMIT 1");
            $stmt->bind_param('si', $email, $excludeSupplierId);
        } else {
            $stmt = $this->db->prepare("SELECT supplier_id FROM suppliers WHERE email = ? LIMIT 1");
            $stmt->bind_param('s', $email);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result && $result->num_rows > 0;
        $stmt->close();

        return $exists;
    }

    public function createSupplierWithItems($name, $email, $contact, $items) {
        $items = $this->normalizeItems($items);

        $this->db->begin_transaction();
        try {
            $supplierStmt = $this->db->prepare("INSERT INTO suppliers (supplier_name, contact_no, email) VALUES (?, ?, ?)");
            $supplierStmt->bind_param('sss', $name, $contact, $email);

            if (!$supplierStmt->execute()) {
                throw new Exception('Failed to create supplier.');
            }

            $supplierId = $this->db->insert_id;
            $supplierStmt->close();

            $this->insertSupplierItems($supplierId, $items);
            $this->db->commit();

            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    public function updateSupplierWithItems($supplierId, $name, $email, $contact, $items) {
        $items = $this->normalizeItems($items);

        $this->db->begin_transaction();
        try {
            $updateStmt = $this->db->prepare("UPDATE suppliers SET supplier_name = ?, contact_no = ?, email = ? WHERE supplier_id = ?");
            $updateStmt->bind_param('sssi', $name, $contact, $email, $supplierId);

            if (!$updateStmt->execute()) {
                throw new Exception('Failed to update supplier.');
            }
            $updateStmt->close();

            $deleteItemsStmt = $this->db->prepare("DELETE FROM supplier_items WHERE supplier_id = ?");
            $deleteItemsStmt->bind_param('i', $supplierId);

            if (!$deleteItemsStmt->execute()) {
                throw new Exception('Failed to refresh supplier items.');
            }
            $deleteItemsStmt->close();

            $this->insertSupplierItems($supplierId, $items);
            $this->db->commit();

            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    public function deleteSupplier($supplierId) {
        $stmt = $this->db->prepare("DELETE FROM suppliers WHERE supplier_id = ?");
        $stmt->bind_param('i', $supplierId);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    private function insertSupplierItems($supplierId, $items) {
        if (empty($items)) {
            return;
        }

        $itemStmt = $this->db->prepare("INSERT INTO supplier_items (supplier_id, item_name) VALUES (?, ?)");

        foreach ($items as $itemName) {
            $itemStmt->bind_param('is', $supplierId, $itemName);
            if (!$itemStmt->execute()) {
                throw new Exception('Failed to insert supplier item.');
            }
        }

        $itemStmt->close();
    }

    private function normalizeItems($items) {
        $cleanItems = [];

        foreach ((array) $items as $item) {
            $value = trim((string) $item);
            if ($value === '') {
                continue;
            }
            $cleanItems[] = $value;
        }

        return array_values(array_unique($cleanItems));
    }
}
