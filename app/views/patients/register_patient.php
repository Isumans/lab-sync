<html>
<head>

    <title>Register Patients</title>
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
                 <div class="Tmain-content">
                    <div class="test-catalog-header">
                        <h1>Patient</h1>
                    </div>
                    <div>
                        <p class="MC-p">Patients->Register-Walk-In-Patient</p>
                    </div>
                    <div>
                        <form class="formStyle" action="/lab_sync/index.php?controller=patientController&action=register" method="POST">
                            <!-- Form fields for patient registration -->
                            <label for="patient_name">Patient Name:</label>
                            <input type="text" id="patient_name" name="patient_name" required>

                            <label for="date_of_birth">Date of Birth:</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" required>
                            <label for="gender">Gender:</label>
                            <select id="gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            <label for="contact_no">Contact Number:</label>
                            <input type="tel" id="contact_no" name="contact_no" required>
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" required>
                            <button type="submit">Register Patient</button>

                        </form>
                    </div>
                 </div>
            </main>
        </div>
    </body>
</html>