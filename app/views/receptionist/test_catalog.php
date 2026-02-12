<?php
$pageTitle = 'Test Catalog';
$extraStyles = '<link rel="stylesheet" href="/lab_sync/public/table.css"><link rel="stylesheet" href="/lab_sync/public/css/test_catalog.css">';
$role = $_GET['role'] ?? '';
// Start output buffering
ob_start();
?>
                
                <div class="Tmain-content">
                    <div class="test-catalog-header">
                        <h1>Test Catalog</h1>
                        <a href="/lab_sync/index.php?controller=TestCatalog&action=add_test&role=<?php echo urlencode($role); ?>" id="openAddTest" class="add-test-button">+ Add New Test</a>
                    </div>
                    <div>
                        <p class="MC-p">Dashboard > Test Catalog</p>
                    </div>
                    <div class="search-and-filter">
                        <input type="text" class="search-bar" placeholder="  Search tests...">
                    </div>
                    <div class="select-category">
                        <!-- <label for="category-filter">Filter by Category:</label> -->
                        <select class="category-filter" name="category-filter" placeholder="Category">
                            <option value="all">All</option>
                            <option value="blood">Blood Tests</option>
                            <option value="urine">Urine Tests</option>
                            <option value="imaging">Imaging</option>
                            <option value="molecular">Molecular Tests</option>
                        </select>
                    </div>
                    
                </div>
                <div class="tDiv">
                    <table class="test-catalog-table">
                    <thead>
                        <tr>
                            <th>Test ID</th>
                            <th>Test Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <!-- <th>Status</th> -->
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (is_array($packages) && count($packages) > 0): ?>
                            <?php foreach ($packages as $package): ?>
                                <tr class="test-row"
                                    data-id="<?php echo htmlspecialchars($package['test_id']); ?>"
                                    data-name="<?php echo htmlspecialchars($package['test_name']); ?>"
                                    data-category="<?php echo htmlspecialchars($package['category']); ?>"
                                    data-price="<?php echo htmlspecialchars($package['price']); ?>">
                                    <td><?php echo htmlspecialchars($package['test_id']); ?></td>
                                    <td><?php echo htmlspecialchars($package['test_name']); ?></td>
                                    <td><?php echo htmlspecialchars($package['category']); ?></td>
                                    <td><?php echo htmlspecialchars($package['price']); ?></td>
                                    <td>
                                        <button type="button" class="edit-btn" title="Edit"><img src="/lab_sync/public/assests/edit.png" alt="Edit"></button>
                                        <button type="button" class="delete-btn" title="Delete"><img src="/lab_sync/public/assests/delete.png" alt="Delete"></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5">No tests found or database error.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>

<!-- Edit Test Modal -->
<div id="editTestModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Test</h2>
            <button id="editTestModalClose" class="modal-close" aria-label="Close">&times;</button>
        </div>

        <form id="editTestForm" method="post" action="/lab_sync/index.php?controller=TestCatalog&action=edit_test&role=<?php echo urlencode($role); ?>">
            <input type="hidden" name="test_id" value="" />

            <div class="modal-body">
                <div class="form-row">
                    <label for="test_name">Test Name</label>
                    <input type="text" id="test_name" name="test_name" placeholder="Comprehensive Metabolic Panel" required />
                </div>

                <div class="form-row">
                    <label for="category">Category</label>
                    <div class="select-with-icon">
                        <select id="category" name="category">
                            <option value="Biochemistry">Biochemistry</option>
                            <option value="Hematology">Hematology</option>
                            <option value="Immunology">Immunology</option>
                            <option value="Microbiology">Microbiology</option>
                        </select>
                        <span class="cat-icon" aria-hidden="true"></span>
                    </div>
                </div>

                <div class="form-row">
                    <label for="price">Price</label>
                    <div class="input-group">
                        <span class="input-prefix">Rs</span>
                        <input type="number" step="0.01" id="price" name="price" placeholder="145.00" />
                    </div>
                </div>

            </div>

            <div class="modal-actions">
                <button type="button" id="cancelEditTest" class="btn-cancel">Cancel</button>
                <div style="display:flex; gap:8px;">
                    <button type="submit" name="edit" value="1" class="btn-save">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Page-specific JS -->
<script src="/lab_sync/public/js/test_catalog.js" defer></script>

<?php
$content = ob_get_clean();
require VIEW_PATH . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'main_layout.php';
?>