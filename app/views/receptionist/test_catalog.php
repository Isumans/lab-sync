<?php
$pageTitle = 'Test Catalog';
$extraStyles = '<link rel="stylesheet" href="/lab_sync/public/table.css">';
$role = $_GET['role'] ?? '';
// Start output buffering
ob_start();
?>
                
                <div class="Tmain-content">
                    <div class="test-catalog-header">
                        <h1>Test Catalog</h1>
                        <button class="add-test-button">
                            <a href="/lab_sync/index.php?controller=TestCatalog&action=add_test&role=<?php echo $role; ?>">+ Add New Test</a>
                        </button>
                    </div>
                    <div>
                        <p class="MC-p">Test-Catalog-></p>
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
                
                <div>

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
        });
        </script>
*** End Patch                
                </table>

                </div>
                
                <div>

                </div>
<?php
$content = ob_get_clean();
require VIEW_PATH . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'main_layout.php';
?>