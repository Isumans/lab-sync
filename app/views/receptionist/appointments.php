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
                        <button class="add-test-button" ><a href="/lab_sync/index.php?controller=TestCatalog&action=Register_patient">Register Walk-in Patient</a></button>
                    </div>
                    <div>
                        <p class="MC-p">Appointments-></p>
                    </div>
                
                    <div class="heading-row">
                        <h2 class="heading2">
                            Add New Appointments
                        </h2>
                    
                        <form class="appointment-form formStyle"><br>
                            <label for="patient-name">Patient patient by: 
                                <select id="patient-name" name="patient-name" required>
                                    <option value="">Email</option>
                                    <option value="John Doe">UserId</option>
                                </select>
                            </label>
                            <input type="text" class="search-bar" placeholder="  Search patient...">

                            <label for="appointment-date">Appointment Date:</label>
                            <input type="date" id="appointment-date" name="appointment-date" required>

                            <label for="appointment-time">Appointment Time:</label>
                            <input type="time" id="appointment-time" name="appointment-time" required>
                            
                            <label for="test-type">Test Type:</label>
                            <select id="test-type" name="test-type" required>
                                <option value="">Select Test Type</option>
                                <option value="Blood Test">Blood Test</option>
                                <option value="Urine Test">Urine Test</option>
                                <option value="X-Ray">X-Ray</option>
                                <option value="MRI">MRI</option>
                                <option value="CT Scan">CT Scan</option>
                                <option value="Other">Other</option>
                            </select>

                            <label for="amount">Amount</label>
                            <input type="text" id="amount" name="amount" required>


                            <button type="submit">Add New Appointment</button>
                        </form>
                    </div>
                    <br/>

                    
                    <div class="heading-row">
                        <h2 class="heading3">Appointment Requests </h2>
                        <div class="user-list">
                            <table class="test-catalog-table">
                                <thead>
                                    <tr>
                                        <th>Request ID</th>
                                        <th>Patient Name</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Test Type</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>John Doe</td>
                                        <td>2023-10-01</td>
                                        <td>10:00 AM</td>
                                        <td>Blood Test</td>
                                        <td>
                                            <button class="Status">Approve</button>
                                            <button class="Status">Reject</button>
                                        </td>
                                    </tr>
    
                                </tbody>
                            </table>   
                        </div>
                                 
                </div>
            </main>
    
</body>
</html>