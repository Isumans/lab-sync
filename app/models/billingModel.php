<?php
class BillingModel {
    private $conn;
    private $testsTable = 'tests';
    private $billsTable = 'bills';
    private $billItemsTable = 'bill_items';
    private $doctorsTable = 'doctors';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Get all tests from database
     */
    public function getAllTests() {
        $query = "SELECT test_id, test_name, category, price FROM " . $this->testsTable . " WHERE is_active = 1 ORDER BY test_name ASC";
        $result = mysqli_query($this->conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $tests = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $tests[] = $row;
            }
            return $tests;
        }
        return [];
    }

    /**
     * Get all doctors from database
     */
    public function getAllDoctors() {
        $query = "SELECT doctor_id, doctor_name FROM " . $this->doctorsTable . " WHERE status = 'active' ORDER BY doctor_name ASC";
        $result = mysqli_query($this->conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $doctors = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $doctors[] = $row;
            }
            return $doctors;
        }
        return [];
    }

    /**
     * Generate unique invoice number
     */
    public function generateInvoiceNumber() {
        $prefix = "INV-" . date('Y');
        $query = "SELECT COUNT(*) as count FROM " . $this->billsTable . " WHERE invoice_number LIKE '" . $prefix . "%'";
        $result = mysqli_query($this->conn, $query);
        $row = mysqli_fetch_assoc($result);
        $count = $row['count'] + 1;
        return $prefix . "-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new bill with items
     */
    public function createBill($data) {
        $invoiceNumber = $this->generateInvoiceNumber();
        $patientName = mysqli_real_escape_string($this->conn, $data['patient_name']);
        $patientPhone = mysqli_real_escape_string($this->conn, $data['patient_phone']);
        $referringDoctor = mysqli_real_escape_string($this->conn, $data['referring_doctor']);
        $billingNotes = mysqli_real_escape_string($this->conn, $data['billing_notes']);
        $serviceFee = floatval($data['service_fee']);
        $discountCode = mysqli_real_escape_string($this->conn, $data['discount_code']);
        
        // Calculate totals
        $subtotal = 0;
        $tests = $data['tests'] ?? [];
        
        foreach ($tests as $test) {
            if (!empty($test['test_name'])) {
                // Get price from test name or use submitted price
                $testQuery = "SELECT price FROM " . $this->testsTable . " WHERE test_name = '" . mysqli_real_escape_string($this->conn, $test['test_name']) . "'";
                $testResult = mysqli_query($this->conn, $testQuery);
                
                if ($testResult && mysqli_num_rows($testResult) > 0) {
                    $testRow = mysqli_fetch_assoc($testResult);
                    $price = floatval($testRow['price']);
                    $qty = intval($test['quantity'] ?? 1);
                    $subtotal += $price * $qty;
                }
            }
        }
        
        $tax = $subtotal * 0.02; // 2% tax
        $discount = $subtotal * 0.1; // 10% for promo code
        $totalAmount = $subtotal + $tax + $serviceFee - $discount;
        
        // Insert bill
        $billQuery = "INSERT INTO " . $this->billsTable . " 
                      (invoice_number, patient_name, patient_phone, referring_doctor, billing_notes, subtotal, tax, service_fee, discount, total_amount, status, created_at)
                      VALUES 
                      ('$invoiceNumber', '$patientName', '$patientPhone', '$referringDoctor', '$billingNotes', $subtotal, $tax, $serviceFee, $discount, $totalAmount, 'pending', NOW())";
        
        if (mysqli_query($this->conn, $billQuery)) {
            $billId = mysqli_insert_id($this->conn);
            
            // Insert bill items
            foreach ($tests as $index => $test) {
                if (!empty($test['test_name'])) {
                    $testName = mysqli_real_escape_string($this->conn, $test['test_name']);
                    $qty = intval($test['quantity'] ?? 1);
                    
                    // Get test price and category
                    $testQuery = "SELECT price, category FROM " . $this->testsTable . " WHERE test_name = '$testName'";
                    $testResult = mysqli_query($this->conn, $testQuery);
                    
                    if ($testResult && mysqli_num_rows($testResult) > 0) {
                        $testRow = mysqli_fetch_assoc($testResult);
                        $price = floatval($testRow['price']);
                        $category = $testRow['category'];
                        $itemSubtotal = $price * $qty;
                        
                        $itemQuery = "INSERT INTO " . $this->billItemsTable . " 
                                     (bill_id, test_name, category, price, quantity, subtotal)
                                     VALUES 
                                     ($billId, '$testName', '$category', $price, $qty, $itemSubtotal)";
                        
                        mysqli_query($this->conn, $itemQuery);
                    }
                }
            }
            
            return true;
        }
        
        return false;
    }

    /**
     * Get all bills
     */
    public function getAllBills() {
        $query = "SELECT * FROM " . $this->billsTable . " ORDER BY created_at DESC";
        $result = mysqli_query($this->conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $bills = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $bills[] = $row;
            }
            return $bills;
        }
        return [];
    }

    /**
     * Get bill by ID
     */
    public function getBillById($billId) {
        $query = "SELECT * FROM " . $this->billsTable . " WHERE bill_id = " . intval($billId);
        $result = mysqli_query($this->conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
        return null;
    }

    /**
     * Get bill items
     */
    public function getBillItems($billId) {
        $query = "SELECT * FROM " . $this->billItemsTable . " WHERE bill_id = " . intval($billId);
        $result = mysqli_query($this->conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $items = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $items[] = $row;
            }
            return $items;
        }
        return [];
    }
}
?>
