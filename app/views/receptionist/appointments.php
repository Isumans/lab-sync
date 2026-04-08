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
                        <h1>Appointments</h1>
                        <div style="display:flex; gap:8px;">
                            <button class="add-test-button"><a href="/lab_sync/index.php?controller=appointmentsController&action=prescriptionDecisionReport&role=<?php echo urlencode($role ?? ''); ?>">Decisions Report</a></button>
                            <button class="add-test-button"><a href="/lab_sync/index.php?controller=appointmentsController&action=prescriptionQueue&role=<?php echo urlencode($role ?? ''); ?>">Prescription Queue</a></button>
                            <button class="add-test-button" ><a href="/lab_sync/index.php?controller=appointmentsController&action=createAppointment&role=<?php echo $role; ?>">Create Appointment</a></button>
                        </div>
                    </div>
                    <?php if (!empty($_SESSION['success'])): ?>
                        <div style="color:#067647; margin: 8px 0;"><?php echo htmlspecialchars($_SESSION['success']); ?></div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>
                    <?php if (!empty($_SESSION['error'])): ?>
                        <div style="color:#b42318; margin: 8px 0;"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>
                    <div>
                        <p class="MC-p">Appointments-></p>
                    </div>
                    <div class="heading-row">
                        <h2 class="heading3">Online Appointment </h2>
                        <div class="user-list">
                            <table class="test-catalog-table">
                                <thead>
                                    <tr>
                                        <th>Appointment ID</th>
                                        <th>Patient ID</th>
                                        <th>Tests</th>
                                        <th>Items</th>
                                        <th>Total (LKR)</th>
                                        <th>Home Collection</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($appointmentsOnline as $appointment): ?>
                                    <tr>
                                        <td><?php echo $appointment['appointment_id']; ?></td>
                                        <td><?php echo $appointment['patient_id']; ?></td>
                                        <td><?php echo htmlspecialchars($appointment['tests_summary'] ?? ($appointment['test_name'] ?? ('Test #' . $appointment['test_id']))); ?></td>
                                        <td><?php echo htmlspecialchars((string)($appointment['item_count'] ?? 1)); ?></td>
                                        <td><?php echo htmlspecialchars(number_format((float)($appointment['total_price'] ?? $appointment['test_price'] ?? 0), 2)); ?></td>
                                        <td><?php echo !empty($appointment['home_collection']) ? 'Yes' : 'No'; ?><?php if (!empty($appointment['collection_address'])): ?><div style="font-size:12px; color:#667085;"><?php echo htmlspecialchars($appointment['collection_address']); ?></div><?php endif; ?></td>
                                        <td><?php echo htmlspecialchars($appointment['appointment_status'] ?? 'Pending'); ?></td>
                                        <td><?php echo $appointment['appointment_date']; ?></td>
                                        <td><?php echo $appointment['appointment_time']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>   
                        </div>
                                 
                </div>
                <div class="heading-row">
                        <h2 class="heading3">Physical/Call Appointments</h2>
                        <div class="user-list">
                            <table class="test-catalog-table">
                                <thead>
                                    <tr>
                                        <th>Appointment ID</th>
                                        <th>Patient ID</th>
                                        <th>Method</th>
                                        <th>Tests</th>
                                        <th>Items</th>
                                        <th>Total (LKR)</th>
                                        <th>Home Collection</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($appointmentsPhysical as $appointment): ?>
                                    <tr>
                                        <td><?php echo $appointment['appointment_id']; ?></td>
                                        <td><?php echo $appointment['patient_id']; ?></td>
                                        <td><?php echo htmlspecialchars($appointment['method'] ?? 'Physical'); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['tests_summary'] ?? ($appointment['test_name'] ?? ('Test #' . $appointment['test_id']))); ?></td>
                                        <td><?php echo htmlspecialchars((string)($appointment['item_count'] ?? 1)); ?></td>
                                        <td><?php echo htmlspecialchars(number_format((float)($appointment['total_price'] ?? $appointment['test_price'] ?? 0), 2)); ?></td>
                                        <td><?php echo !empty($appointment['home_collection']) ? 'Yes' : 'No'; ?><?php if (!empty($appointment['collection_address'])): ?><div style="font-size:12px; color:#667085;"><?php echo htmlspecialchars($appointment['collection_address']); ?></div><?php endif; ?></td>
                                        <td><?php echo htmlspecialchars($appointment['appointment_status'] ?? 'Pending'); ?></td>
                                        <td><?php echo $appointment['appointment_date']; ?></td>
                                        <td><?php echo $appointment['appointment_time']; ?></td>
                                        <td>
                                            <button class="Status" disabled>Cancel</button>
                                            <button class="Status" disabled>Reschedule</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
    
                                </tbody>
                            </table>   
                        </div>
                                 
                </div>
            </main>
    
</body>
</html>