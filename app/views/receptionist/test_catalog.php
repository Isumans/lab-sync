<?php
$pageTitle = 'Test Catalog';
$extraStyles = '<link rel="stylesheet" href="/lab_sync/public/teamStyles.css">'
    . '<link rel="stylesheet" href="/lab_sync/public/reportsDashboard.css">'
    . '<link rel="stylesheet" href="/lab_sync/public/testCatalogTable.css">'
    . '<link rel="stylesheet" href="/lab_sync/public/testCatalogViewModal.css">'
    . '<link rel="stylesheet" href="/lab_sync/public/testCatalogEditModal.css">'
    . '<link rel="stylesheet" href="/lab_sync/public/testCatalogDeleteModal.css">'
    .'<link rel="stylesheet" href="/lab_sync/public/reportsDashboard.css">';
$role = $_GET['role'] ?? '';
// Start output buffering
ob_start();
?>
                
    
                <div class="reports-dashboard">
                    <?php
                        $pageBreadcrumbText = 'Test-Catalog->';
                        $pageActionHtml = '<a class="add-user-button" href="/lab_sync/index.php?controller=TestCatalog&action=add_test">+ Add New Test</a>';
                        require __DIR__ . '/../../../public/partials/page-header.php';
                    ?>
                    
                    <section class="rd-filter-card" aria-label="Search and filters">
                        <div class="rd-filter-grid tc-filter-grid">
                            <div class="rd-filter-field rd-filter-field-search">
                                <label for="tcSearch">Search Records</label>
                                <input type="text" id="tcSearch" placeholder="Search by Test Name, Test ID, or LIB ID...">
                            </div>

                            <div class="rd-filter-field">
                                <label for="tcDepartment">Department</label>
                                <select id="tcDepartment">
                                    <option value="all">All Departments</option>
                                    <option value="hematology">Hematology</option>
                                    <option value="biochemistry">Biochemistry</option>
                                    <option value="immunology">Immunology</option>
                                    <option value="microbiology">Microbiology</option>
                                    <option value="radiology">Radiology</option>
                                </select>
                            </div>

                            <div class="rd-filter-field">
                                <label for="tcSortBy">Sort By</label>
                                <select id="tcSortBy">
                                    <option value="test_name">Test Name</option>
                                    <option value="test_id">Test ID</option>
                                    <option value="department">Department</option>
                                    <option value="price">Price</option>
                                </select>
                            </div>
                        </div>

                        <div class="rd-filter-bottom-row">
                            <div></div>
                            <div class="rd-sort-direction-wrap">
                                <select id="tcSortDir" class="rd-sort-direction" aria-label="Sort direction">
                                    <option value="asc">A to Z</option>
                                    <option value="desc">Z to A</option>
                                </select>
                                <button type="button" class="rd-clear-btn" id="tcClearBtn">Clear All Filters</button>
                            </div>
                        </div>
                    </section>

                    <section class="rd-table-card" aria-label="Test catalog table">
                    <div class="rd-table-wrap">
                    <table class="rd-table test-catalog-table">
                        <thead>
                            <tr>
                                <th class="rd-sortable is-active is-asc" data-sort="test_id" data-direction="asc">Test ID</th>
                                <th class="rd-sortable" data-sort="test_name" data-direction="asc">Test Name</th>
                                <th class="rd-sortable" data-sort="department" data-direction="asc">Department</th>
                                <th class="rd-sortable rd-th-right" data-sort="price" data-direction="asc">Price</th>
                                <th class="rd-th-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tcTableBody">
                            <?php if (is_array($packages) && count($packages) > 0): ?>
                                <?php foreach ($packages as $package): ?>
                                    <tr class="test-row"
                                        data-id="<?php echo htmlspecialchars($package['test_id'] ?? ''); ?>"
                                        data-name="<?php echo htmlspecialchars($package['test_name'] ?? ''); ?>"
                                        data-description="<?php echo htmlspecialchars($package['description'] ?? ''); ?>"
                                        data-department="<?php echo htmlspecialchars($package['department'] ?? $package['category'] ?? ''); ?>"
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
                                        
                                        <td class="price-cell">
                                            <span class="price-amount">LKR <?php echo number_format($package['price'] ?? 0, 2); ?></span>
                                        </td>
                                        
                                        <td class="actions-cell rd-th-right">
                                            <button type="button"
                                                class="action-btn-view js-view-test-btn"
                                                title="View Details"
                                                data-test-id="<?php echo htmlspecialchars($package['test_id'] ?? ''); ?>">
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                    <path d="M1 8C1 8 3.5 2 8 2C12.5 2 15 8 15 8C15 8 12.5 14 8 14C3.5 14 1 8 1 8Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M8 5C6.34315 5 5 6.34315 5 8C5 9.65685 6.34315 11 8 11C9.65685 11 11 9.65685 11 8C11 6.34315 9.65685 5 8 5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </button>
                                            <button type="button"
                                                class="action-btn-edit js-edit-test-btn"
                                                title="Edit"
                                                data-test-id="<?php echo htmlspecialchars($package['test_id'] ?? ''); ?>">
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                    <path d="M3 13.5H13M2 11L11.5 1.5C11.8 1.2 12.3 1.2 12.6 1.5L14.5 3.4C14.8 3.7 14.8 4.2 14.5 4.5L5 14H2V11Z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </button>
                                            <button type="button"
                                                class="action-btn-delete js-delete-test-btn"
                                                title="Delete"
                                                data-test-id="<?php echo htmlspecialchars($package['test_id'] ?? ''); ?>"
                                                data-test-name="<?php echo htmlspecialchars($package['test_name'] ?? ''); ?>">
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                    <path d="M2 4H14M6.5 7V11M9.5 7V11M3 4L4 13C4 13.5 4.5 14 5 14H11C11.5 14 12 13.5 12 13L13 4M5.5 4V2.5C5.5 2.2 5.7 2 6 2H10C10.3 2 10.5 2.2 10.5 2.5V4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr class="rd-empty-row">
                                    <td colspan="5" style="text-align: center; padding: 40px; color: #999;">
                                        <div style="font-size: 48px; margin-bottom: 10px;">📋</div>
                                        <div>No tests found in the catalog</div>
                                        <a href="/lab_sync/index.php?controller=TestCatalog&action=add_test" style="color: #1bc47d; text-decoration: none; margin-top: 10px; display: inline-block;">+ Add your first test</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="rd-table-footer">
                    <p id="tcShowingText">Showing 0-0 of 0 tests</p>
                    <div class="rd-pagination" id="tcPagination"></div>
                </div>
                </section>

                <!-- Test Catalog Table -->
                
                </div>
                
                <div>

                </div>

        <?php
        $csrfToken = $_SESSION['csrf_token'] ?? '';
        if (empty($csrfToken)) {
            $csrfToken = bin2hex(random_bytes(24));
            $_SESSION['csrf_token'] = $csrfToken;
        }
        ?>
        <script>
        window.LAB_SYNC_CONFIG = {
            baseUrl: '/lab_sync',
            csrfToken: '<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>'
        };
        </script>

        <!-- View Test Modal -->
        <div id="testCatalogViewModal" class="test-catalog-view-modal" aria-hidden="true">
            <div class="test-catalog-view-dialog" role="dialog" aria-modal="true" aria-labelledby="testCatalogViewTitle">
                <div class="test-catalog-view-topbar">
                    <div id="testCatalogViewTitle" class="test-catalog-view-title">Test Details</div>
                    <button id="testCatalogViewClose" class="test-catalog-view-close" type="button" aria-label="Close details">&times;</button>
                </div>
                <div id="testCatalogViewBody" class="test-catalog-view-body"></div>
            </div>
        </div>

        <!-- Edit Test Modal -->
        <div id="testCatalogEditModal" class="test-catalog-edit-modal" aria-hidden="true">
            <div class="test-catalog-edit-dialog" role="dialog" aria-modal="true" aria-labelledby="editTestCatalogTitle">
                <form id="editTestCatalogForm" novalidate>
                    <input type="hidden" id="editTestId" name="test_id" value="">

                    <div class="test-catalog-edit-header">
                        <div>
                            <h2 id="editTestCatalogTitle">Edit Test</h2>
                            <p class="test-catalog-edit-subtitle">TEST CATALOG UPDATE</p>
                        </div>
                        <button id="editTestCatalogClose" type="button" class="test-catalog-edit-close" aria-label="Close edit modal">&times;</button>
                    </div>

                    <div id="editTestCatalogAlert" class="tc-edit-alert" hidden></div>

                    <div class="test-catalog-edit-body">

                        <div class="tc-edit-section">
                            <div class="tc-edit-section-title">Test Identity</div>
                            <div class="tc-edit-grid-2">
                                <div class="tc-edit-row">
                                    <label class="tc-edit-label" for="editTcTestName">Test Name *</label>
                                    <input type="text" id="editTcTestName" name="test_name" class="tc-edit-input" required>
                                </div>
                                <div class="tc-edit-row">
                                    <label class="tc-edit-label" for="editTcPrintName">Print Name</label>
                                    <input type="text" id="editTcPrintName" name="print_name" class="tc-edit-input">
                                </div>
                                <div class="tc-edit-row">
                                    <label class="tc-edit-label" for="editTcDepartment">Department</label>
                                    <select id="editTcDepartment" name="department" class="tc-edit-select">
                                        <option value="">-- Select --</option>
                                        <option value="biochemistry">Biochemistry</option>
                                        <option value="hematology">Hematology</option>
                                        <option value="immunology">Immunology</option>
                                        <option value="microbiology">Microbiology</option>
                                        <option value="radiology">Radiology</option>
                                    </select>
                                </div>
                                <div class="tc-edit-row">
                                    <label class="tc-edit-label" for="editTcDefaultUnit">Default Unit</label>
                                    <input type="text" id="editTcDefaultUnit" name="default_unit" class="tc-edit-input">
                                </div>
                            </div>
                        </div>

                        <div class="tc-edit-section">
                            <div class="tc-edit-section-title">Pricing</div>
                            <div class="tc-edit-grid-2">
                                <div class="tc-edit-row">
                                    <label class="tc-edit-label" for="editTcCostPrice">Cost Price</label>
                                    <input type="number" id="editTcCostPrice" name="cost_price" class="tc-edit-input" min="0" step="0.01">
                                </div>
                                <div class="tc-edit-row">
                                    <label class="tc-edit-label" for="editTcDiscount">Discount (%)</label>
                                    <input type="number" id="editTcDiscount" name="discount" class="tc-edit-input" min="0" max="100" step="0.01">
                                </div>
                            </div>
                            <p id="editTcPricePreview" class="tc-price-preview">Calculated price: <strong>LKR 0.00</strong></p>
                        </div>

                        <div class="tc-edit-section">
                            <div class="tc-edit-section-title">Status &amp; Notes</div>
                            <div class="tc-edit-row">
                                <label class="tc-edit-label" for="editTcIsActive">Status</label>
                                <select id="editTcIsActive" name="is_active" class="tc-edit-select">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <div class="tc-edit-row">
                                <label class="tc-edit-label" for="editTcDescription">Description</label>
                                <textarea id="editTcDescription" name="description" class="tc-edit-textarea" rows="3"></textarea>
                            </div>
                            <div class="tc-edit-row">
                                <label class="tc-edit-label" for="editTcReportComments">Report Comments</label>
                                <textarea id="editTcReportComments" name="report_comments" class="tc-edit-textarea" rows="3"></textarea>
                            </div>
                            <p class="tc-units-note">To edit units &amp; reference ranges, use the <a href="/lab_sync/index.php?controller=TestCatalog&action=add_test">Add Test</a> workflow.</p>
                        </div>

                    </div>

                    <div class="test-catalog-edit-footer">
                        <button type="button" id="editTestCatalogCancel" class="tc-edit-cancel-btn">Cancel</button>
                        <button type="submit" id="editTestCatalogSubmit" class="tc-edit-submit-btn">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Test Modal -->
        <div id="testCatalogDeleteModal" class="test-catalog-delete-modal" aria-hidden="true">
            <div class="test-catalog-delete-dialog" role="dialog" aria-modal="true" aria-labelledby="tcDeleteTitle">
                <div class="tc-delete-header">
                    <div class="tc-delete-icon" aria-hidden="true">!</div>
                    <h2 id="tcDeleteTitle">Archive Test</h2>
                    <button type="button" id="testCatalogDeleteClose" class="tc-delete-close" aria-label="Close delete modal">&times;</button>
                </div>
                <p class="tc-delete-copy">Are you sure you want to archive this test? It will be hidden from active test selections.</p>
                <div id="tcDeleteAlert" class="tc-delete-alert" hidden></div>
                <div class="tc-delete-summary">
                    <div class="summary-label">TEST NAME</div>
                    <div id="tcDeleteTestName" class="summary-value">Unknown Test</div>
                </div>
                <button type="button" id="testCatalogDeleteConfirm" class="tc-delete-confirm-btn">Archive Test</button>
                <button type="button" id="testCatalogDeleteCancel" class="tc-delete-cancel-btn">Cancel</button>
                <div class="tc-delete-footer-note">SYSTEM: AUTHORIZATION REQUIRED • ARCHIVE ACTION</div>
            </div>
        </div>
                
                <script src="/lab_sync/public/js/testCatalogTable.js"></script>
                <script src="/lab_sync/public/js/testCatalogViewModal.js"></script>
                <script src="/lab_sync/public/js/testCatalogEditModal.js"></script>
                <script src="/lab_sync/public/js/testCatalogDeleteModal.js"></script>
<?php
$content = ob_get_clean();
require VIEW_PATH . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'main_layout.php';
?>