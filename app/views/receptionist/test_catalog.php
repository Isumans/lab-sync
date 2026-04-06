<?php
$pageTitle = 'Test Catalog';
$extraStyles = '<link rel="stylesheet" href="/lab_sync/public/testCatalogTable.css">';
$role = $_GET['role'] ?? '';
// Start output buffering
ob_start();
?>
                
                <div class="Tmain-content">
                    <div class="test-catalog-header">
                        <h1>Test Catalog</h1>
                        <button type="button" class="add-test-button"><a href="/lab_sync/index.php?controller=TestCatalog&action=add_test">+ Add New Test</a></button>
                    </div>
                    <div>
                        <p class="MC-p">Test-Catalog-></p>
                    </div>
                    
                    <!-- Search and Filter Section -->
                    <div class="catalog-controls">
                        <div class="search-section">
                            <input type="text" id="test-search" class="search-bar" placeholder="Search by name, ID, or LIB...">
                            <span class="search-icon">🔍</span>
                        </div>
                        <div class="filter-section">
                            <select id="department-filter" class="department-filter">
                                <option value="">All Departments</option>
                                <option value="Hematology">Hematology</option>
                                <option value="Biochemistry">Biochemistry</option>
                                <option value="Immunology">Immunology</option>
                                <option value="Microbiology">Microbiology</option>
                                <option value="Radiology">Radiology</option>
                            </select>
                            <span class="result-count">Showing <span id="result-count">0</span> of <span id="total-count">0</span> results</span>
                        </div>
                    </div>
                    <div class="test-catalog-wrapper">
                    <table class="test-catalog-table">
                        <thead>
                            <tr>
                                <th class="col-id">TEST ID</th>
                                <th class="col-name">TEST NAME</th>
                                <th class="col-dept">DEPARTMENT</th>
                                <th class="col-lib">LAB # / LIB ID</th>
                                <th class="col-price">PRICE</th>
                                <th class="col-actions">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (is_array($packages) && count($packages) > 0): ?>
                                <?php foreach ($packages as $package): ?>
                                    <tr class="test-row"
                                        data-id="<?php echo htmlspecialchars($package['test_id'] ?? ''); ?>"
                                        data-name="<?php echo htmlspecialchars($package['test_name'] ?? ''); ?>"
                                        data-description="<?php echo htmlspecialchars($package['description'] ?? ''); ?>"
                                        data-department="<?php echo htmlspecialchars($package['department'] ?? $package['category'] ?? ''); ?>"
                                        data-lib-id="<?php echo htmlspecialchars($package['lab_id'] ?? ''); ?>"
                                        data-price="<?php echo htmlspecialchars($package['price'] ?? ''); ?>">
                                        
                                        <td class="test-id-cell">
                                            <span class="test-id-badge"><?php echo htmlspecialchars($package['test_id'] ?? 'N/A'); ?></span>
                                        </td>
                                        
                                        <td class="test-name-cell">
                                            <div class="test-name-title"><?php echo htmlspecialchars($package['test_name'] ?? ''); ?></div>
                                            <div class="test-description"><?php echo htmlspecialchars(substr($package['description'] ?? '', 0, 60)); ?></div>
                                        </td>
                                        
                                        <td class="department-cell">
                                            <span class="dept-badge dept-<?php echo strtolower(str_replace(' ', '-', $package['department'] ?? $package['category'] ?? '')); ?>">
                                                <?php echo htmlspecialchars($package['department'] ?? $package['category'] ?? 'Other'); ?>
                                            </span>
                                        </td>
                                        
                                        <td class="lib-id-cell">
                                            <span class="lib-id"><?php echo htmlspecialchars($package['lab_id'] ?? 'N/A'); ?></span>
                                        </td>
                                        
                                        <td class="price-cell">
                                            <span class="price-amount">$<?php echo number_format($package['price'] ?? 0, 2); ?></span>
                                        </td>
                                        
                                        <td class="actions-cell">
                                            <button type="button" class="action-btn-view" title="View Details" onclick="viewTest(this)">
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                    <path d="M1 8C1 8 3.5 2 8 2C12.5 2 15 8 15 8C15 8 12.5 14 8 14C3.5 14 1 8 1 8Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M8 5C6.34315 5 5 6.34315 5 8C5 9.65685 6.34315 11 8 11C9.65685 11 11 9.65685 11 8C11 6.34315 9.65685 5 8 5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </button>
                                            <button type="button" class="action-btn-edit" title="Edit">
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                    <path d="M3 13.5H13M2 11L11.5 1.5C11.8 1.2 12.3 1.2 12.6 1.5L14.5 3.4C14.8 3.7 14.8 4.2 14.5 4.5L5 14H2V11Z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </button>
                                            <button type="button" class="action-btn-delete" title="Delete">
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                    <path d="M2 4H14M6.5 7V11M9.5 7V11M3 4L4 13C4 13.5 4.5 14 5 14H11C11.5 14 12 13.5 12 13L13 4M5.5 4V2.5C5.5 2.2 5.7 2 6 2H10C10.3 2 10.5 2.2 10.5 2.5V4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr class="empty-state">
                                    <td colspan="6" style="text-align: center; padding: 40px; color: #999;">
                                        <div style="font-size: 48px; margin-bottom: 10px;">📋</div>
                                        <div>No tests found in the catalog</div>
                                        <a href="/lab_sync/index.php?controller=TestCatalog&action=add_test" style="color: #1bc47d; text-decoration: none; margin-top: 10px; display: inline-block;">+ Add your first test</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
                        <span class="input-prefix">$</span>
                        <input type="number" step="0.01" id="price" name="price" placeholder="145.00" />
                    </div>
                </div>
                
                <div>

            <div class="modal-actions">
                <button type="button" id="cancelEditTest" class="btn btn-cancel">Cancel</button>
                <div style="display:flex; gap:8px;">
                    <button type="submit" name="edit" value="1" class="btn btn-save">Save Changes</button>
                </div>

        <!-- Edit Test Modal -->
        <style>
        #editTestModal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background: rgba(0,0,0,0.4); }
        #editTestModal .modal-content { background: #fff; margin: 6% auto; padding: 20px; border-radius: 6px; width: 92%; max-width: 600px; }
        #editTestModal .close { float: right; font-size: 24px; font-weight: bold; cursor: pointer; }
        #editTestForm .form-row { margin-bottom: 10px; }
        #editTestForm label { display: block; font-weight: 600; margin-bottom: 4px; }
        #editTestForm input[type=text], #editTestForm input[type=number] { width: 100%; padding: 8px; box-sizing: border-box; }
        </style>

        <div id="editTestModal">
            <div class="modal-content">
                <span id="editTestModalClose" class="close">&times;</span>
                <h3>Edit Test</h3>
                <form id="editTestForm" method="post" action="/lab_sync/index.php?controller=TestCatalog&action=edit_test&role=<?php echo urlencode($role); ?>">
                    <input type="hidden" name="test_id" value="" />
                    <div class="form-row">
                        <label for="test_name">Test Name</label>
                        <input type="text" id="test_name" name="test_name" required />
                    </div>
                    <div class="form-row">
                        <label for="category">Category</label>
                        <input type="text" id="category" name="category" />
                    </div>
                    <div class="form-row">
                        <label for="price">Price</label>
                        <input type="text" id="price" name="price" />
                    </div>
                    <div class="actions">
                        <button type="button" id="cancelEditTest">Cancel</button>
                        <button type="submit" name="edit" value="1">Save changes</button>
                        <button type="submit" name="delete" value="1" style="margin-left:8px; background:#c33; color:#fff;">Delete</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add Test Modal (embedded form from add_test.php, resized for modal) -->
        <style>
        #addTestModal { display: none; position: fixed; z-index: 1100; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background: rgba(0,0,0,0.45); }
        #addTestModal .modal-content { background: #fff; margin: 4% auto; padding: 20px; border-radius: 8px; width: 92%; max-width: 700px; box-shadow: 0 6px 24px rgba(0,0,0,0.2); }
        #addTestModal .close { float: right; font-size: 24px; font-weight: bold; cursor: pointer; }
        #addTestModal .Tmain-content.formStyle { padding: 0; margin: 0; }
        #addTestModal label { display:block; margin-top:8px; font-weight:600; }
        #addTestModal input, #addTestModal select, #addTestModal textarea { width:100%; padding:8px; box-sizing:border-box; margin-top:4px; }
        #addTestModal button[type=submit] { margin-top:12px; }
        </style>

        <div id="addTestModal">
            <div class="modal-content">
                <span id="addTestModalClose" class="close">&times;</span>
                <h3>Add New Test</h3>
                <form class="Tmain-content formStyle" action="/lab_sync/index.php?controller=TestCatalog&action=store&role=<?php echo urlencode($role); ?>" method="POST">
                    <label for="test-name">Test Name:</label>
                    <input type="text" id="test-name" name="test-name" required>
                    <label for="test-category">Category:</label>
                    <select id="test-category" name="test-category" required>
                        <option value="">Select Category</option>
                        <option value="blood">Blood Tests</option>
                        <option value="urine">Urine Tests</option>
                        <option value="imaging">Imaging</option>
                        <option value="molecular">Molecular Tests</option>
                    </select>
                    <label for="test-description">Description:</label>
                    <textarea id="test-description" name="test-description" required></textarea>

                    <label for="test-price">Price:</label>
                    <input type="number" id="test-price" name="test-price" required>

                    <label for="test-status">Status:</label>
                    <select id="test-status" name="test-status" required>
                        <option value="">Select Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <div style="display:flex; gap:8px; margin-top:12px;">
                        <button type="button" id="cancelAddTest">Cancel</button>
                        <button type="submit">Add Test</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('editTestModal');
            const form = document.getElementById('editTestForm');
            const closeBtn = document.getElementById('editTestModalClose');
            const cancelBtn = document.getElementById('cancelEditTest');

            function openModalWithData(data) {
                form.elements['test_id'].value = data.id || '';
                form.elements['test_name'].value = data.name || '';
                form.elements['category'].value = data.category || '';
                form.elements['price'].value = data.price || '';
                modal.style.display = 'block';
            }

            document.querySelectorAll('.edit-btn').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    const tr = e.currentTarget.closest('tr');
                    if (!tr) return;
                    const data = {
                        id: tr.dataset.id,
                        name: tr.dataset.name,
                        category: tr.dataset.category,
                        price: tr.dataset.price
                    };
                    openModalWithData(data);
                });
            });

            closeBtn.addEventListener('click', function () { modal.style.display = 'none'; });
            cancelBtn.addEventListener('click', function () { modal.style.display = 'none'; });
            window.addEventListener('click', function (e) { if (e.target === modal) modal.style.display = 'none'; });

            // Row delete button: confirm and submit a small form
            document.querySelectorAll('.delete-btn').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    const tr = e.currentTarget.closest('tr');
                    if (!tr) return;
                    const id = tr.dataset.id;
                    if (!id) return;
                    if (!confirm('Delete this test? This action cannot be undone.')) return;
                    const f = document.createElement('form');
                    f.method = 'post';
                    f.action = '/lab_sync/index.php?controller=TestCatalog&action=edit_test&role=<?php echo urlencode($role); ?>';
                    const inp = document.createElement('input'); inp.type = 'hidden'; inp.name = 'test_id'; inp.value = id; f.appendChild(inp);
                    const del = document.createElement('input'); del.type = 'hidden'; del.name = 'delete'; del.value = '1'; f.appendChild(del);
                    document.body.appendChild(f);
                    f.submit();
                });
            });

            // Add Test Modal handlers
            const addModal = document.getElementById('addTestModal');
            const openAddBtn = document.getElementById('openAddTest');
            const addClose = document.getElementById('addTestModalClose');
            const cancelAdd = document.getElementById('cancelAddTest');

            if (openAddBtn) {
                openAddBtn.addEventListener('click', function () {
                    addModal.style.display = 'block';
                });
            }
            if (addClose) addClose.addEventListener('click', function () { addModal.style.display = 'none'; });
            if (cancelAdd) cancelAdd.addEventListener('click', function () { addModal.style.display = 'none'; });
            window.addEventListener('click', function (e) { if (e.target === addModal) addModal.style.display = 'none'; });
        });
        </script>
        
                </table>

                </div>
                
                <script src="/lab_sync/public/js/testCatalogTable.js"></script>              
<?php
$content = ob_get_clean();
require VIEW_PATH . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'main_layout.php';
?>