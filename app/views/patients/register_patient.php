<?php
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect to login page
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}
$role = $_GET['role'] ?? ($_GET['user_role'] ?? '');
?>
<html>
<head>

    <title>Register Patients</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="/lab_sync/public/styles.css">
        <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
        <link rel="stylesheet" href="/lab_sync/public/table.css">
        <link rel="stylesheet" href="/lab_sync/public/patientStyles.css">
</head>
    <body>
        <!-- Navigation Bar -->
        <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
        <div class="container">
            <!-- Sidebar -->
            <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>
        
            <!-- Main Body Section -->
            <main class="main-content">
                 <div class="Tmain-content rwp-page-shell">
                    <?php
                        $pageTitle = 'Patient';
                        $pageBreadcrumbText = 'Patients->Register-Walk-In-Patient';
                        $pageActionHtml = '<a class="add-user-button" href="/lab_sync/index.php?controller=patientController&action=index&role=' . rawurlencode((string)$role) . '">Back to Patients</a>';
                        require __DIR__ . '/../../../public/partials/page-header.php';
                    ?>

                    <section class="rwp-card" aria-label="Walk-in patient registration form">
                        <form class="rwp-form" action="/lab_sync/index.php?controller=patientController&action=register&role=<?php echo htmlspecialchars((string)$role); ?>" method="POST">
                            <div class="rwp-section-head">
                                <h2>Personal Information</h2>
                                <span class="rwp-required-tag">Required</span>
                            </div>

                            <div class="rwp-field-group">
                                <label for="patient_name">Patient Name <span class="rwp-mark">*</span></label>
                                <input
                                    type="text"
                                    id="patient_name"
                                    name="patient_name"
                                    maxlength="120"
                                    placeholder="e.g. sanju samson"
                                    required
                                >
                            </div>

                            <div class="rwp-grid-2">
                                <div class="rwp-field-group">
                                    <label for="date_of_birth">Date of Birth <span class="rwp-mark">*</span></label>
                                    <div class="rwp-input-wrap">
                                        <input type="date" id="date_of_birth" name="date_of_birth" required>
                                        <span class="rwp-input-icon" aria-hidden="true">&#128197;</span>
                                    </div>
                                </div>

                                <div class="rwp-field-group">
                                    <label for="gender">Gender <span class="rwp-mark">*</span></label>
                                    <div class="rwp-input-wrap">
                                        <select id="gender" name="gender" required>
                                            <option value="">Select Gender</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="other">Other</option>
                                        </select>
                                        <span class="rwp-input-icon" aria-hidden="true">&#9662;</span>
                                    </div>
                                </div>
                            </div>

                            <div class="rwp-section-head rwp-section-head-tight">
                                <h2>Contact Details</h2>
                            </div>

                            <div class="rwp-grid-2">
                                <div class="rwp-field-group">
                                    <label for="contact_no">Contact Number <span class="rwp-mark">*</span></label>
                                    <div class="rwp-input-wrap">
                                        <input
                                            type="tel"
                                            id="contact_no"
                                            name="contact_no"
                                            maxlength="25"
                                            pattern="^[0-9+()\-\s]{7,25}$"
                                            title="Use 7-25 characters: digits, space, plus, parentheses, or hyphen."
                                            placeholder="+94 71 234 5678"
                                            required
                                        >
                                        <span class="rwp-input-icon" aria-hidden="true">&#9742;</span>
                                    </div>
                                </div>

                                <div class="rwp-field-group">
                                    <label for="email">Email Address <span class="rwp-optional">(Optional)</span></label>
                                    <div class="rwp-input-wrap">
                                        <input
                                            type="email"
                                            id="email"
                                            name="email"
                                            maxlength="120"
                                            placeholder="patient@example.com"
                                        >
                                        <span class="rwp-input-icon" aria-hidden="true">&#9993;</span>
                                    </div>
                                </div>
                            </div>

                            <div class="rwp-actions">
                                <a class="rwp-cancel-btn" href="/lab_sync/index.php?controller=patientController&action=index&role=<?php echo rawurlencode((string)$role); ?>">Cancel</a>
                                <button type="submit" class="rwp-submit-btn">Register Patient</button>
                            </div>
                        </form>
                    </section>
                 </div>
            </main>
        </div>
    </body>
</html>