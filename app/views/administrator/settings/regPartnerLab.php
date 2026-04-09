<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect to login page
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}
?>

<html>
    <head>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
        <title>+ Add Partner Lab</title>
        <link rel="stylesheet" href="/lab_sync/public/styles.css">
        <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
        <link rel="stylesheet" href="/lab_sync/public/table.css">
        <link rel="stylesheet" href="/lab_sync/public/partnerLabForm.css">
    </head>
    <body>
        <!-- Navigation Bar -->
        <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
        <div class="page-wrapper">
            <!-- Sidebar -->
            <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>

            <!-- Main Body Section -->
            <main class="main-content">
                 <div class="Tmain-content">
                    <div class="test-catalog-header">
                        <h1>Add Partner Lab</h1>
                    </div>
                    <div>
                        <p class="MC-p">Settings->Add Partner Lab</p>
                    </div>
                    <br/>

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
                                        <input type="text" name="lab_name" placeholder="e.g. Metro Diagnostic Center" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="required">Email</label>
                                        <input type="email" name="email" placeholder="contact@metrolab.com" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="required">Contact Person Name</label>
                                        <input type="text" name="contact_person" placeholder="John Doe" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="required">Contact Person Phone Number</label>
                                        <input type="tel" name="phone" placeholder="+1 (555) 000-0000" required>
                                    </div>
                                </div>

                                <div class="form-row full-width">
                                    <div class="form-group">
                                        <label class="optional">Website (Optional)</label>
                                        <input type="url" name="website" placeholder="https://www.metrolab.com">
                                    </div>
                                </div>

                                <div class="form-row full-width">
                                    <div class="form-group">
                                        <label class="required">Address</label>
                                        <textarea name="address" placeholder="Enter the full laboratory street address, city, and zip code" required></textarea>
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
