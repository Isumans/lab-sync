<html>
<head>

    <title>Billing</title>
        <link rel="stylesheet" href="/lab_sync/public/styles.css">
        <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
        <link rel="stylesheet" href="/lab_sync/public/table.css">
        <link rel="stylesheet" href="/lab_sync/public/billingStyles.css">
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
                        <h1>Billing</h1>
                        <button class="add-test-button" ><a href="/lab_sync/index.php?controller=billingController&action=Register_billing">Create New Bill</a></button>
                    </div>
                    <div>
                        <p class="MC-p">Billing-></p>
                    </div>
                    <div class="billingArea">
                        <h2>Billing Details(Last Month)</h2>
                        <table class="test-catalog-table">
                                    <thead>
                                        <tr>
                                            <th>Bill ID</th>
                                            <th>PatientId</th>
                                            <th>Test</th>
                                            <th>Date</th>
                                            <th>Amount</th> 
                                            <th>Payment Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>John Doe</td>
                                            <td>Blood Test</td>
                                            <td>2023-09-15</td>
                                            <td>$100</td>
                                            <td><select class="payment-status">
                                                <option value="Paid">Paid</option>
                                                <option value="Pending">Pending</option>
                                                <option value="Overdue">Overdue</option>
                                            </select></td>
                                            <td>
                                                <button class="view-button">View</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td>Jane Smith  </td>
                                            <td>Blood Test</td>
                                            <td>2023-09-15</td>
                                            <td>$100</td>
                                            <td><select class="payment-status">
                                                <option value="Paid">Paid</option>
                                                <option value="Pending">Pending</option>
                                                <option value="Overdue">Overdue</option>
                                            </select></td>
                                            <td>
                                                <button class="view-button">View</button>
                                            </td>
                                        </tr>
                                       
                                    </tbody>
                                </table>
                       
                    </div>
                </div>
            </main>
        </div>
    </body>
</html>