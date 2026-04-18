<?php
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect to login page
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}
?>

<html>
    <head>
        <title>+ Add Partner Lab</title>
        <link rel="stylesheet" href="/lab_sync/public/styles.css">
        <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
        <link rel="stylesheet" href="/lab_sync/public/table.css">
        <link rel="stylesheet" href="/lab_sync/public/partnerLabForm.css">
    </head>
    <body>
        <!-- Navigation Bar -->
        <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
        <div class="container">
            <!-- Sidebar -->
            <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>

            <!-- Main Body Section -->
            <main class="main-content">
                 <div class="Tmain-content">
                    <?php
                        $pageTitle = 'Add Partner Lab';
                        $pageBreadcrumbText = 'Settings->Add Partner Lab';
                        $pageActionHtml = '';
                        require __DIR__ . '/../../../../public/partials/page-header.php';
                    ?>

                    <div class="partner-form-container">
                        <form id="partnerLabForm" method="POST" action="/lab_sync/index.php?controller=partnerLabController&action=storeLab">
                            
                            <!-- Basic Information Section -->
                            <div class="form-section">
                                <div class="section-header">
                                    <span class="section-icon">ℹ️</span>
                                    <h2>Basic Information</h2>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="required">Lab Name</label>
                                        <input type="text" name="lab_name" placeholder="e.g. Metro Diagnostic Center" maxlength="120" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="required">Email</label>
                                        <input type="email" name="email" placeholder="contact@metrolab.com" maxlength="120" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="required">Contact Person Name</label>
                                        <input type="text" name="contact_person" placeholder="John Doe" maxlength="120" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="required">Contact Person Phone Number</label>
                                        <input type="tel" name="phone" placeholder="+1 (555) 000-0000" maxlength="25" pattern="^[0-9+()\-\s]{7,25}$" title="Use 7-25 characters: digits, space, plus, parentheses, or hyphen." required>
                                    </div>
                                </div>

                                <div class="form-row full-width">
                                    <div class="form-group">
                                        <label class="optional">Website (Optional)</label>
                                        <input type="url" name="website" placeholder="https://www.metrolab.com" maxlength="255">
                                    </div>
                                </div>

                                <div class="form-row full-width">
                                    <div class="form-group">
                                        <label class="required">Address</label>
                                        <textarea name="address" placeholder="Enter the full laboratory street address, city, and zip code" maxlength="255" required></textarea>
                                    </div>
                                </div>
                                
                            </div>

                            <!-- Tests Offered Section -->
                            <div class="form-section">
                                <div class="section-header">
                                    <span class="section-icon">🔬</span>
                                    <h2>Tests Offered by the Partner Lab</h2>
                                </div>
                                <p class="section-description">Select all the testing services provided by this laboratory partner.</p>

                                <div class="services-section">
                                    <div class="search-tests-container">
                                        <span class="search-icon">🔍</span>
                                        <input type="text" id="testSearch" placeholder="Search tests..." class="test-search-input">
                                    </div>

                                    <div class="services-grid" id="testsGrid">
                                        <?php
                                            // Fetch tests from database
                                            
                                            if (!empty($tests)) {
                                                $count= 0;
                                                foreach ($tests as $test) {
                                                    if ($count >=9) break;
                                                    ?>
                                                    <label class="service-item">
                                                        <input type="checkbox" name="services[]" value="<?php echo htmlspecialchars($test['test_id']); ?>">
                                                        <div class="service-content">
                                                            <h4><?php echo htmlspecialchars($test['test_name']); ?></h4>
                                                            <p><?php echo htmlspecialchars($test['description'] ?? ''); ?></p>
                                                        </div>
                                                    </label>
                                                    <?php
                                                    $count++;
                                                }
                                            } else {
                                                echo '<p style="color: #718096;">No tests available. Please add tests in the Test Catalog.</p>';
                                            }
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="form-actions">
                                <button type="submit" class="btn btn-save">💾 Save Partner Lab</button>
                                <button type="button" class="btn btn-cancel" onclick="window.history.back()">Cancel</button>
                            </div>
                        </form>
                    </div>
                    
                 </div>
            </main>
        </div>
    </body>
    <script src="/lab_sync/public/js/regPartnerLab.js"></script>
</html>