<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect to login page
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}
?>
<div>
    <table class="test-catalog-table">
        <thead>
            <tr>
                <th>Appointment ID</th>
                <th>Sample ID</th>
                <th>Sample Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>RPT001</td>
                <td>Sample001</td>
                <td>Blood Test</td>
                <td>
                    <button class="status">Create Report</button>
                </td>
            </tr>
            <tr>
                <td>RPT002</td>
                <td>Sample002</td>
                <td>Urine Test</td>
                <td>
                    <button class="status">Create Report</button>
                </td>
            </tr>
        </tbody>

    </table>
</div>