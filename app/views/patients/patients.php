<html>
<head>

    <title>Document</title>
</head>
    <title>Appointments</title>
        <link rel="stylesheet" href="/lab_sync/public/styles.css">
        <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
        <link rel="stylesheet" href="/lab_sync/public/table.css">
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
                        <button class="add-test-button" ><a href="/lab_sync/index.php?controller=Patients&action=Register_patient">Register Walk-in Patient</a></button>
                    </div>
                    <div>
                        <p class="MC-p">Appointments-></p>
                    </div>
                </div>




            </main>

        </div>
        <script src="/lab_sync/public/js/sideAction.js"></script>
    </body>
        
        
</html>