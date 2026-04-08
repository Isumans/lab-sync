<?php
$pageTitle = 'Patient Billing';
$extraStyles = '<link rel="stylesheet" href="/lab_sync/public/css/create_bill.css">';
$role = $_GET['role'] ?? '';
// Start output buffering
ob_start();
?>

<div class="create-bill-page">
    <!-- Header with Title and Buttons -->
    <div class="billing-header">
        <div>
            <h1>Patient Billing</h1>
            <div class="invoice-status">
                <span class="invoice-label">Invoice Number:</span>
                <span class="invoice-number">#INV-2023-001</span>
                <span class="status-badge pending">● Payment Pending</span>
            </div>
        </div>
        <div class="header-actions">
            <button type="button" class="btn-print">🖨️ Print Preview</button>
            <button type="button" class="btn-finalize">✓ Finalize Billing</button>
        </div>
    </div>

    <form id="createBillForm" action="/lab_sync/index.php?controller=billingController&action=store_bill" method="POST">
        
        <div class="billing-container">
            <!-- Left Column: Patient Info and Tests -->
            <div class="billing-left">
                
                <!-- SECTION 1: Patient Demographics -->
                <div class="card">
                    <h3 class="card-title">PATIENT INFORMATION</h3>
                    <div class="patient-demographics">
                        <div class="demo-row">
                            <div class="demo-item">
                                <label>FULL NAME</label>
                                <input type="text" id="patient_name" name="patient_name" placeholder="Enter patient name" value="Kamal Perera">
                            </div>
                            <div class="demo-item">
                                <label>AGE / GENDER</label>
                                <input type="text" id="patient_age_gender" name="patient_age_gender" placeholder="45 Years / Male" value="45 Years / Male">
                            </div>
                        </div>
                        <div class="demo-row">
                            <div class="demo-item">
                                <label>CONTACT NUMBER</label>
                                <input type="text" id="patient_phone" name="patient_phone" placeholder="Phone number" value="+94 77 123 4567">
                            </div>
                            <div class="demo-item">
                                <label>REFERRING DOCTOR</label>
                                <select id="referring_doctor" name="referring_doctor">
                                    <option value="">Select Doctor</option>
                                    <option value="Dr. N. Fernando" selected>Dr. N. Fernando</option>
                                    <option value="Dr. Smith">Dr. Smith</option>
                                    <option value="Dr. Johnson">Dr. Johnson</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: Tests & Services -->
                <div class="card">
                    <div class="card-title-with-btn">
                        <h3 class="card-title">TESTS & SERVICES</h3>
                        <button type="button" id="addTestBtn" class="btn-add-test-link">+ Add Test</button>
                    </div>
                    <table class="tests-table">
                        <thead>
                            <tr>
                                <th>TEST DESCRIPTION</th>
                                <th>CATEGORY</th>
                                <th>PRICE (LKR)</th>
                                <th>QTY</th>
                                <th>SUBTOTAL</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="testsBody">
                            <tr>
                                <td>
                                    <select name="tests[0][test_name]" class="test-select">
                                        <option value="">Select Test</option>
                                        <option value="Full Blood Count (FBC)" data-price="1250" data-category="Hematology" selected>Full Blood Count (FBC)</option>
                                        <option value="Lipid Profile" data-price="3400" data-category="Biochemistry">Lipid Profile</option>
                                        <option value="HbA1c (Glycated Hemoglobin)" data-price="2100" data-category="Diabetes">HbA1c (Glycated Hemoglobin)</option>
                                        <option value="Serum Creatinine" data-price="950" data-category="Renal Function">Serum Creatinine</option>
                                    </select>
                                </td>
                                <td><span class="test-category">Hematology</span></td>
                                <td><span class="test-price">1,250.00</span></td>
                                <td><input type="number" name="tests[0][quantity]" value="1" min="1" class="test-qty quantity-input"></td>
                                <td><span class="test-subtotal">1,250.00</span></td>
                                <td><button type="button" class="remove-test-btn" title="Remove">×</button></td>
                            </tr>
                            <tr>
                                <td>
                                    <select name="tests[1][test_name]" class="test-select">
                                        <option value="">Select Test</option>
                                        <option value="Full Blood Count (FBC)" data-price="1250" data-category="Hematology">Full Blood Count (FBC)</option>
                                        <option value="Lipid Profile" data-price="3400" data-category="Biochemistry" selected>Lipid Profile</option>
                                        <option value="HbA1c (Glycated Hemoglobin)" data-price="2100" data-category="Diabetes">HbA1c (Glycated Hemoglobin)</option>
                                        <option value="Serum Creatinine" data-price="950" data-category="Renal Function">Serum Creatinine</option>
                                    </select>
                                </td>
                                <td><span class="test-category">Biochemistry</span></td>
                                <td><span class="test-price">3,400.00</span></td>
                                <td><input type="number" name="tests[1][quantity]" value="1" min="1" class="test-qty quantity-input"></td>
                                <td><span class="test-subtotal">3,400.00</span></td>
                                <td><button type="button" class="remove-test-btn" title="Remove">×</button></td>
                            </tr>
                            <tr>
                                <td>
                                    <select name="tests[2][test_name]" class="test-select">
                                        <option value="">Select Test</option>
                                        <option value="Full Blood Count (FBC)" data-price="1250" data-category="Hematology">Full Blood Count (FBC)</option>
                                        <option value="Lipid Profile" data-price="3400" data-category="Biochemistry">Lipid Profile</option>
                                        <option value="HbA1c (Glycated Hemoglobin)" data-price="2100" data-category="Diabetes" selected>HbA1c (Glycated Hemoglobin)</option>
                                        <option value="Serum Creatinine" data-price="950" data-category="Renal Function">Serum Creatinine</option>
                                    </select>
                                </td>
                                <td><span class="test-category">Diabetes</span></td>
                                <td><span class="test-price">2,100.00</span></td>
                                <td><input type="number" name="tests[2][quantity]" value="1" min="1" class="test-qty quantity-input"></td>
                                <td><span class="test-subtotal">2,100.00</span></td>
                                <td><button type="button" class="remove-test-btn" title="Remove">×</button></td>
                            </tr>
                            <tr>
                                <td>
                                    <select name="tests[3][test_name]" class="test-select">
                                        <option value="">Select Test</option>
                                        <option value="Full Blood Count (FBC)" data-price="1250" data-category="Hematology">Full Blood Count (FBC)</option>
                                        <option value="Lipid Profile" data-price="3400" data-category="Biochemistry">Lipid Profile</option>
                                        <option value="HbA1c (Glycated Hemoglobin)" data-price="2100" data-category="Diabetes">HbA1c (Glycated Hemoglobin)</option>
                                        <option value="Serum Creatinine" data-price="950" data-category="Renal Function" selected>Serum Creatinine</option>
                                    </select>
                                </td>
                                <td><span class="test-category">Renal Function</span></td>
                                <td><span class="test-price">950.00</span></td>
                                <td><input type="number" name="tests[3][quantity]" value="1" min="1" class="test-qty quantity-input"></td>
                                <td><span class="test-subtotal">950.00</span></td>
                                <td><button type="button" class="remove-test-btn" title="Remove">×</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- SECTION 3: Billing Notes -->
                <div class="card">
                    <h3 class="card-title">BILLING NOTES</h3>
                    <textarea id="billing_notes" name="billing_notes" placeholder="Add any special instructions or billing notes..." class="notes-textarea"></textarea>
                </div>

            </div>

            <!-- Right Column: Financial Summary -->
            <div class="billing-right">
                <div class="financial-summary-card">
                    <h3 class="financial-title">FINANCIAL SUMMARY</h3>
                    
                    <div class="summary-item">
                        <span class="summary-label">Subtotal</span>
                        <span class="summary-value" id="subtotal">Rs. 7,700.00</span>
                    </div>

                    <div class="summary-item">
                        <span class="summary-label">Laboratory Tax (2%)</span>
                        <span class="summary-value" id="tax">Rs. 154.00</span>
                    </div>

                    <div class="summary-item">
                        <span class="summary-label">Service Fee</span>
                        <input type="number" id="service_fee" name="service_fee" value="500" class="summary-input">
                        <span class="summary-value">Rs. <span id="service-fee-display">500.00</span></span>
                    </div>

                    <div class="summary-item">
                        <span class="summary-label">
                            Discount 
                            <span class="discount-code">(Promo: WINTER)</span>
                        </span>
                        <div class="discount-input-group">
                            <input type="text" id="discount_code" name="discount_code" placeholder="Enter code" value="WINTER">
                            <span class="summary-value discount-amount" id="discount">- Rs. 770.00</span>
                        </div>
                    </div>

                    <div class="summary-divider"></div>

                    <div class="summary-item total-row">
                        <span class="total-label">TOTAL PAYABLE</span>
                        <div class="total-amount-group">
                            <span class="total-value" id="totalAmount">Rs. 7,584.00</span>
                            <span class="currency-label">LKR CURRENCY</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Page-specific JS -->
<script src="/lab_sync/public/js/createbill.js" defer></script>

<?php
$content = ob_get_clean();
require VIEW_PATH . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'main_layout.php';
?>