<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /lab_sync/index.php?controller=Auth&action=index&role=' . urlencode($_GET['role'] ?? ''));
    exit();
}
?>

<!doctype html>
<html>
    <head>
        <title>Add New Laboratory Test</title>
        <link rel="stylesheet" href="/lab_sync/public/styles.css">
        <link rel="stylesheet" href="/lab_sync/public/table.css">
        <link rel="stylesheet" href="/lab_sync/public/css/add_test.css">
    </head>
    <body>
        <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
        <div class="container add-test-page">
            <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>
            <main class="main-content">
                <div class="d-header">
                    <h1>Add New Laboratory Test</h1>
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

                            <div class="col">
                                <label for="discounts">Discount % (comma-separated)</label>
                                <input type="text" id="discounts" name="discounts" placeholder="e.g. 5,10,20">

                                <label for="print_order">Print Order</label>
                                <input type="number" id="print_order" name="print_order">

                                <label for="lis_id">LIS ID</label>
                                <input type="text" id="lis_id" name="lis_id">

                                <label for="decimals">Decimals</label>
                                <input type="number" id="decimals" name="decimals" min="0">

                                <label for="unit">Unit</label>
                                <input type="text" id="unit" name="unit" placeholder="e.g. mg/dL">
                            </div>
                        </div>

                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="full"></textarea>
                    </div>

                    <!-- SECTION 2: Reference Ranges -->
                    <div class="card">
                        <h4>Reference Ranges</h4>
                        <table class="range-table">
                            <thead>
                                <tr>
                                    <th>Gender</th>
                                    <th>From Age</th>
                                    <th>To Age</th>
                                    <th>Reference Range</th>
                                    <th>Critical Range</th>
                                    <th>Remove</th>
                                </tr>
                            </thead>
                            <tbody id="rangesBody">
                                <tr>
                                    <td>
                                        <select name="ranges[0][gender]">
                                            <option value="Both">Both</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                    </td>
                                    <td><input type="number" name="ranges[0][from_age]"></td>
                                    <td><input type="number" name="ranges[0][to_age]"></td>
                                    <td><input type="text" name="ranges[0][range]"></td>
                                    <td><input type="text" name="ranges[0][critical]"></td>
                                    <td><button type="button" class="btn delete-range">Remove</button></td>
                                </tr>
                            </tbody>
                        </table>
                        <div><button type="button" id="addRangeBtn" class="btn">Add Range</button></div>
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