<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect to login page
    header('Location: /lab_sync/index.php?controller=Auth&action=index&role=' . urlencode($_GET['role'] ?? ''));
    exit();
}

$workflowScriptPath = __DIR__ . '/../../../public/js/testCatalogWorkflow.js';
$workflowScriptVersion = file_exists($workflowScriptPath) ? (string)filemtime($workflowScriptPath) : '1';
?>

<html>
    <head>
        <title>Test Catalog - Add Test</title>
        <link rel="stylesheet" href="/lab_sync/public/styles.css">
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
                    <?php
                        $pageTitle = 'Add New Test';
                        $pageBreadcrumbHtml = '<a href="javascript:history.back()" style="color: var(--primary-color); text-decoration: none;">Test-Catalog-></a>Add-New-Test';
                        $pageActionHtml = '';
                        require __DIR__ . '/../../../public/partials/page-header.php';
                    ?>
                    <?php if (isset($_SESSION['flash'])): ?>
                        <?php
                            $flash = $_SESSION['flash'];
                            $flashType = $flash['type'] ?? 'info';
                            $flashMessage = $flash['message'] ?? '';
                            $flashBg = '#e9f7ff';
                            $flashColor = '#145374';
                            $flashBorder = '#b8e2f2';
                            if ($flashType === 'success') {
                                $flashBg = '#eaf9f1';
                                $flashColor = '#1f7a44';
                                $flashBorder = '#b8e5c8';
                            } elseif ($flashType === 'error') {
                                $flashBg = '#fdecec';
                                $flashColor = '#9b1c1c';
                                $flashBorder = '#f4b7b7';
                            }
                            unset($_SESSION['flash']);
                        ?>
                        <div style="margin: 14px 0; padding: 12px 14px; border-radius: 8px; border: 1px solid <?php echo htmlspecialchars($flashBorder); ?>; background: <?php echo htmlspecialchars($flashBg); ?>; color: <?php echo htmlspecialchars($flashColor); ?>; font-size: 14px;">
                            <?php echo htmlspecialchars($flashMessage); ?>
                        </div>
                    <?php endif; ?>

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

                    <!-- Multi-Step Form -->
                    <form id="test-catalog-form" class="workflow-form" action="/lab_sync/index.php?controller=TestCatalog&action=store&role=<?php echo urlencode($role); ?>" method="POST">
                        
                        <!-- STEP 1: TEST INFORMATION -->
                        <div class="workflow-step active" id="step-1">
                            <div class="step-content">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="department">DEPARTMENT</label>
                                        <select id="department" name="department" required>
                                            <option value="">Select Department</option>
                                            <option value="Biochemistry">Biochemistry</option>
                                            <option value="Hematology">Hematology</option>
                                            <option value="Microbiology">Microbiology</option>
                                            <option value="Immunology">Immunology</option>
                                            <option value="Radiology">Radiology</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="test-code">TEST CODE</label>
                                        <input type="text" id="test-code" name="test_code" placeholder="e.g., T-001" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="lab-id">LAB # / LIB ID</label>
                                        <input type="text" id="lab-id" name="lab_id" placeholder="e.g., L-4829">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="test-name">TEST NAME</label>
                                        <input type="text" id="test-name" name="test_name" placeholder="e.g., Serum Creatinine" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="default-unit">DEFAULT UNIT</label>
                                        <input type="text" id="default-unit" name="default_unit" placeholder="e.g., mg/dL" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="print-name">PRINT NAME</label>
                                        <input type="text" id="print-name" name="print_name" placeholder="e.g., Creatinine" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group full-width">
                                        <label for="description">DESCRIPTION</label>
                                        <textarea id="description" name="description" placeholder="Enter test clinical indications or technical notes..." rows="4"></textarea>
                                    </div>
                                </div>
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
                                                    <label>VALUE NAME</label>
                                                    <input type="text" name="unit_names[]" placeholder="FBS" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>UNIT NAME</label>
                                                    <input type="text" name="conversion_factors[]" placeholder="e.g., mg/dL" required>
                                                </div>
                                                <button type="button" class="btn-remove-unit" onclick="removeUnit(this)">×</button>
                                            </div>
                                            <div class="reference-ranges-section">
                                            <div class="section-subtitle">+ RANGE PARAMETERS</div>
                                            <div id="reference-ranges-container">
                                                <div class="range-table">
                                                    <div class="range-header">
                                                        <div>GENDER</div>
                                                        <div>AGE RANGE</div>
                                                        <div>REF. RANGE (MIN-MAX)</div>
                                                        <div>RANGE LABEL</div>
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
                                                            <input type="text" name="range_label[]" placeholder="Range Label">
                                                        </div>
                                                        <button type="button" class="btn-remove-range" onclick="removeRange(this)">×</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" class="btn-add-range" onclick="addNewRange(this)">+ Add Reference Range</button>
                                        </div>

                                        </div>



                                        <div class="empty-state" id="empty-ranges">
                                            <p>Dynamic unit blocks will appear here as you add them.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="step-actions">
                                <button type="button" class="btn-prev" onclick="prevStep(2)">< Previous Step</button>
                                <button type="button" class="btn-save" onclick="saveProgress(2)">Save Progress</button>
                                <button type="button" class="btn-next" onclick="nextStep(2)">Next Step ></button>
                            </div>
                        </div>

                        <!-- STEP 3: CHARGES & COMMENTS -->
                        <div class="workflow-step" id="step-3">
                            <div class="step-content">
                                <h2 class="step-title">Finalize Test Configuration</h2>
                                <p class="step-subtitle">Specify external hospital billing details and interpretative reporting standards.</p>

                                <!-- External Hospital Charges -->
                                <div class="section-box">
                                    <div class="section-title">
                                        <span class="icon"></span> External Hospital Charges
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="external-test-code">EXTERNAL TEST CODE</label>
                                            <input type="text" id="external-test-code" name="external_test_code" placeholder="e.g., EXT-LIB-001">
                                        </div>
                                        <div class="form-group">
                                            <label for="partner-hospital">PARTNER HOSPITAL</label>
                                            <select id="partner-hospital" name="partner_hospital">
                                                <option value="">Select Hospital</option>
                                                <?php if (!empty($partnerLabs) && is_array($partnerLabs)): ?>
                                                    <?php foreach ($partnerLabs as $lab): ?>
                                                        <option value="<?php echo htmlspecialchars($lab['id'] ?? ''); ?>">
                                                            <?php echo htmlspecialchars($lab['lab_name'] ?? ''); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="charge-cost">CHARGE COST (RS)</label>
                                            <div class="input-wrapper">
                                                <span class="currency">₨</span>
                                                <input type="number" id="charge-cost" name="charge_cost" placeholder="0.00" step="0.01">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Report Comments & Interpretation -->
                                <div class="section-box">
                                    <div class="section-title">
                                        <span class="icon"></span> Report Comments & Interpretation
                                    </div>

                                    <div class="form-group">
                                        <label for="report-comments">COMMENTS</label>
                                        <div class="editor-toolbar">
                                            <button type="button" class="toolbar-btn" title="Bold"><b>B</b></button>
                                            <button type="button" class="toolbar-btn" title="Italic"><i>I</i></button>
                                            <button type="button" class="toolbar-btn" title="Underline"><u>U</u></button>
                                            <button type="button" class="toolbar-btn" title="Bullet List">•</button>
                                            <button type="button" class="toolbar-btn" title="Numbered List">1.</button>
                                            <button type="button" class="toolbar-btn" title="Link">🔗</button>
                                        </div>
                                        <textarea id="report-comments" name="report_comments" placeholder="Standard interpretative comments for the patient report..." rows="6"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="step-actions">
                                <button type="button" class="btn-prev" onclick="prevStep(3)">< Previous Step</button>
                                <button type="button" class="btn-save" onclick="saveProgress(3)">Save Draft</button>
                                <button type="submit" class="btn-finish">✓ Finalize & Add Test</button>
                            </div>
                        </div>

                    </form>
                </div>
            </main>
        </div>

        <script src="/lab_sync/public/js/testCatalogWorkflow.js?v=<?php echo urlencode($workflowScriptVersion); ?>"></script>
    </body>
</html>