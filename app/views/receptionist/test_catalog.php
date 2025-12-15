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
                            <th>Test Name</th>
                            <th>Code</th>
                            <th>Category</th>
                            <th>Price</th>
                            <!-- <th>Status</th> -->
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (is_array($packages)): ?>
                            <?php foreach ($packages as $package): ?>
                                <form method="post" action="/lab_sync/index.php?controller=TestCatalog&action=edit_test&role=<?php echo urlencode($role); ?>" class="editForm">
                                
                                    <tr>
                                    
                                        <td><input id="test_name" class="form1" name="test_name" type="text" value="<?php echo htmlspecialchars($package['test_name']); ?>"></td>
                                        <td><input id="test_id" class="form1" name="test_id" type="text" value="<?php echo htmlspecialchars($package['test_id']); ?>"></td>
                                        <td><input id="category" class="form1" name="category" type="text" value="<?php echo htmlspecialchars($package['category']); ?>"></td>
                                        <td><input id="price" class="form1" name="price" type="text" value="<?php echo htmlspecialchars($package['price']); ?>"></td>
                                        <td>
                                            <button id="edit" type="submit" name="edit" class="edit-button" onclick="showAlertAndSubmit (event,'edit')"><img src="/lab_sync/public/assests/edit.png" alt="Edit"></button>
                                            <button id="delete" type="submit" name="delete" class="delete-button" onclick="showAlertAndSubmit(event,'delete')"><img src="/lab_sync/public/assests/delete.png" alt="Delete"></button>
                                        </td>
                                    </tr>
                                </form>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5">No tests found or database error.</td></tr>
                        <?php endif; ?>
                        
                    </tbody>
                
                </table>

                </div>
                
                <div>

                </div>
<?php
$content = ob_get_clean();
require VIEW_PATH . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'main_layout.php';
?>