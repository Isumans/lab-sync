<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect to login page
    header('Location: /lab_sync/index.php?controller=Auth&action=index&role=' . urlencode($_GET['role'] ?? ''));
    exit();
}
?>

<html>
    <head>
        <title>Test Catalog - Add Test</title>
        <!-- <link rel="stylesheet" href="/lab_sync/public/styles.css"> -->
        <link rel="stylesheet" href="/lab_sync/public/testCatalogWorkflow.css">
    </head>
    <body>
        <!-- Navigation Bar -->
        <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
        <div class="container">
            <!-- Sidebar -->
            <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>

            <!-- Main Body Section -->
            <main class="main-content">
                <div class="add-test-container">
                    <h1>Add New Test</h1>
                    <p class="MC-p" ><a href="javascript:history.back()" style="color: var(--primary-color); text-decoration: none;">Test-Catalog-></a>Add-New-Test</p>

                    <!-- Progress Indicator -->
                    <div class="workflow-progress">
                        <div class="progress-step active" data-step="1">
                            <div class="progress-number">1</div>
                            <div class="progress-label">STEP 1: TEST INFORMATION</div>
                        </div>
                        <div class="progress-line"></div>
                        <div class="progress-step" data-step="2">
                            <div class="progress-number">2</div>
                            <div class="progress-label">STEP 2: PRICING & UNITS</div>
                        </div>
                        <div class="progress-line"></div>
                        <div class="progress-step" data-step="3">
                            <div class="progress-number">3</div>
                            <div class="progress-label">STEP 3: CHARGES & COMMENTS</div>
                        </div>
                    </div>

                <form id="addTestForm" action="/lab_sync/index.php?controller=TestCatalog&action=store&role=<?php echo urlencode($role); ?>" method="POST">

                    <!-- SECTION 1: Test Information -->
                    <div class="card">
                        <h4>Test Information</h4>
                        <div class="form-grid">
                            <div class="col">
                                <label for="department">Department</label>
                                <select id="department" name="department">
                                    <option value="">Select Department</option>
                                    <option value="hematology">Hematology</option>
                                    <option value="biochemistry">Biochemistry</option>
                                    <option value="microbiology">Microbiology</option>
                                </select>

                                <label for="test_name">Test Name</label>
                                <input type="text" id="test_name" name="test_name" required>

                                <label for="print_name">Print Name</label>
                                <input type="text" id="print_name" name="print_name">

                                <label for="code">Code</label>
                                <input type="text" id="code" name="code">

                                <label for="cost">Cost (Rs.)</label>
                                <input type="number" id="cost" name="cost" step="0.01">
                            </div>

                            <div class="step-actions">
                                <button type="button" class="btn-discard" onclick="discardDraft()">Discard Draft</button>
                                <button type="button" class="btn-save" onclick="saveProgress(1)">Save Progress</button>
                                <button type="button" class="btn-next" onclick="nextStep(1)">Next Step ></button>
                            </div>
                        </div>

                        <!-- STEP 2: PRICING & UNITS -->
                        <div class="workflow-step" id="step-2">
                            <div class="step-content">
                                <div class="step-grid">
                                    <!-- Left Column: Pricing & Billing -->
                                    <div class="pricing-section">
                                        <div class="section-title">
                                            <span class="icon">💰</span> Pricing & Billing
                                        </div>

                                        <div class="form-group">
                                            <label for="cost-price">COST PRICE (USD)</label>
                                            <div class="input-wrapper">
                                                <span class="currency">$</span>
                                                <input type="number" id="cost-price" name="cost_price" placeholder="0.00" step="0.01" required>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="discount">DISCOUNT %</label>
                                            <div class="input-wrapper">
                                                <input type="number" id="discount" name="discount" placeholder="0" min="0" max="100">
                                                <span class="suffix">%</span>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="print-order">PRINT ORDER</label>
                                            <input type="number" id="print-order" name="print_order" placeholder="0" min="0">
                                        </div>

                                        <div class="form-group">
                                            <label for="decimals">DECIMALS</label>
                                            <select id="decimals" name="decimals">
                                                <option value="2">2 Decimal Places</option>
                                                <option value="3">3 Decimal Places</option>
                                                <option value="4">4 Decimal Places</option>
                                            </select>
                                        </div>

                                        <div class="checkbox-group">
                                            <label>
                                                <input type="checkbox" id="is-active" name="is_active" value="1">
                                                Is Active
                                            </label>
                                        </div>

                                        <div class="validation-note">
                                            <input type="checkbox" id="validation-required" name="validation_required" value="1">
                                            <label for="validation-required">Validation Required</label>
                                        </div>
                                    </div>

                                    <!-- Right Column: Units & Reference Ranges -->
                                    <div class="units-section">
                                        <div class="section-title">
                                            Units & Reference Ranges
                                            <button type="button" class="btn-add-unit" onclick="addNewUnit()">+ Add New Unit</button>
                                        </div>

                                        <div id="units-container">
                                            <div class="unit-row">
                                                <div class="form-group">
                                                    <label>UNIT NAME</label>
                                                    <input type="text" name="unit_names[]" placeholder="e.g., mg/dL" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>CONVERSION FACTOR</label>
                                                    <input type="number" name="conversion_factors[]" placeholder="Optional" step="0.01">
                                                </div>
                                                <button type="button" class="btn-remove-unit" onclick="removeUnit(this)">×</button>
                                            </div>
                                        </div>

                                        <div class="reference-ranges-section">
                                            <div class="section-subtitle">+ RANGE PARAMETERS</div>
                                            <div id="reference-ranges-container">
                                                <div class="range-table">
                                                    <div class="range-header">
                                                        <div>GENDER</div>
                                                        <div>AGE RANGE</div>
                                                        <div>REF. RANGE (MIN-MAX)</div>
                                                        <div>CRITICAL RANGE</div>
                                                        <div></div>
                                                    </div>
                                                    <div class="range-row">
                                                        <div class="range-cell">
                                                            <select name="range_gender[]">
                                                                <option value="">All</option>
                                                                <option value="M">Male</option>
                                                                <option value="F">Female</option>
                                                            </select>
                                                        </div>
                                                        <div class="range-cell">
                                                            <div class="age-inputs">
                                                                <input type="number" name="range_age_min[]" placeholder="0">
                                                                <span>-</span>
                                                                <input type="number" name="range_age_max[]" placeholder="99">
                                                            </div>
                                                        </div>
                                                        <div class="range-cell">
                                                            <div class="ref-inputs">
                                                                <input type="number" name="range_min[]" placeholder="70" step="0.01">
                                                                <span>-</span>
                                                                <input type="number" name="range_max[]" placeholder="110" step="0.01">
                                                            </div>
                                                        </div>
                                                        <div class="range-cell">
                                                            <input type="number" name="critical_range[]" placeholder="50" step="0.01">
                                                        </div>
                                                        <button type="button" class="btn-remove-range" onclick="removeRange(this)">×</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" class="btn-add-range" onclick="addNewRange()">+ Add Reference Range</button>
                                        </div>

                    <!-- SECTION 3: External Charges -->
                    <div class="card">
                        <h4>External Charges</h4>
                        <table class="external-table">
                            <thead>
                                <tr>
                                    <th>Test Code</th>
                                    <th>Hospital</th>
                                    <th>Cost</th>
                                    <th>Remove</th>
                                </tr>
                            </thead>
                            <tbody id="externalBody">
                                <tr>
                                    <td><input type="text" name="external[0][test_code]"></td>
                                    <td>
                                        <select name="external[0][hospital]"><option value="">Select Hospital</option><option value="HospA">Hospital A</option><option value="HospB">Hospital B</option></select>
                                    </td>
                                    <td><input type="number" name="external[0][cost]"></td>
                                    <td><button type="button" class="btn delete-external">Remove</button></td>
                                </tr>
                            </tbody>
                        </table>
                        <div style="margin-top:12px;"><button type="button" id="addExternalBtn" class="btn">Add Entry</button></div>
                    </div>

                    <!-- SECTION 4: Report Comments -->
                    <div class="card">
                        <h4>Report Comments</h4>
                        <div class="toolbar" style="margin-bottom:8px;">
                            <button type="button" data-cmd="bold"><b>B</b></button>
                            <button type="button" data-cmd="italic"><i>I</i></button>
                            <button type="button" data-cmd="underline"><u>U</u></button>
                            <button type="button" data-cmd="insertUnorderedList">• List</button>
                            <button type="button" data-cmd="insertOrderedList">1. List</button>
                        </div>
                        <div id="reportEditor" class="report-editor" contenteditable="true" data-placeholder="Enter default report comments here..."></div>
                        <textarea name="report_comments" id="report_comments" style="display:none;"></textarea>
                    </div>

                    <!-- SECTION 5: Flags & Status -->
                    <div class="card">
                        <h4>Flags & Status</h4>
                        <label for="flags">Flags (comma-separated: L, M, H)</label>
                        <input type="text" id="flags" name="flags" placeholder="L,M,H">

                        <div style="margin-top:12px;">
                            <label><input type="checkbox" name="is_active" value="1" checked> Is Active</label>
                            <label style="margin-left:12px;"><input type="checkbox" name="requires_validation" value="1"> Requires Validation</label>
                        </div>

                        <div class="actions">
                            <a href="/lab_sync/index.php?controller=TestCatalog&action=test_catalog&role=<?php echo urlencode($role); ?>" class="btn">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Test</button>
                        </div>
                    </div>

                </form>
            </main>
        </div>

        <!-- Page-specific JS loaded separately -->
        <script src="/lab_sync/public/js/add_test_page.js" defer></script>
    </body>
</html>