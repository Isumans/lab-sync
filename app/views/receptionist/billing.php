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
        <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>

        <main class="main-content">
            <div class="billing-wrapper">
                <div class="billing-top">
                    <div>
                        <h1>Billing</h1>
                        <p class="breadcrumb">Dashboard > Billing</p>
                    </div>
                    <a href="/lab_sync/index.php?controller=Billing&action=create_bill" class="create-btn">Create New Bill</a>
                </div>

                <div class="filters">
                    <input type="text" placeholder="🔍 Search by Patient Name" class="filter-input">
                    <input type="text" placeholder="📅 Date Range" class="filter-input">
                    <select class="filter-input">
                        <option>≡ Status</option>
                        <option>Paid</option>
                        <option>Pending</option>
                    </select>
                </div>

                <table class="billing-table">
                    <thead>
                        <tr>
                            <th style="width: 15%;">BILL ID</th>
                            <th style="width: 20%;">PATIENT NAME & EMAIL</th>
                            <th style="width: 15%; text-align: center;">BILLING DATE</th>
                            <th style="width: 15%; text-align: center;">AMOUNT (LKR)</th>
                            <th style="width: 10%; text-align: center;">STATUS</th>
                            <th style="width: 15%; text-align: center;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="bill-id-cell">
                                    <div class="bill-avatar">JD</div>
                                    <div class="bill-number">BL-1001</div>
                                </div>
                            </td>
                            <td>
                                <div class="patient-info-cell">
                                    <div class="patient-name">John Doe</div>
                                    <div class="patient-email">johndoe@example.com</div>
                                </div>
                            </td>
                            <td class="bill-date">Oct 25, 2023</td>
                            <td class="bill-amount">LKR 5,000</td>
                            <td class="bill-status">
                                <span class="status-badge status-paid">Paid</span>
                            </td>
                            <td class="bill-actions">
                                <button class="action-btn-edit">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path
                                            d="M3 13.5H13M2 11L11.5 1.5C11.8 1.2 12.3 1.2 12.6 1.5L14.5 3.4C14.8 3.7 14.8 4.2 14.5 4.5L5 14H2V11Z"
                                            stroke="currentColor" stroke-width="1.2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </button>
                                <button class="action-btn-delete">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path
                                            d="M2 4H14M6.5 7V11M9.5 7V11M3 4L4 13C4 13.5 4.5 14 5 14H11C11.5 14 12 13.5 12 13L13 4M5.5 4V2.5C5.5 2.2 5.7 2 6 2H10C10.3 2 10.5 2.2 10.5 2.5V4"
                                            stroke="currentColor" stroke-width="1.2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="bill-id-cell">
                                    <div class="bill-avatar">AS</div>
                                    <div class="bill-number">BL-1003</div>
                                </div>
                            </td>
                            <td>
                                <div class="patient-info-cell">
                                    <div class="patient-name">Jane Smith</div>
                                    <div class="patient-email">issumanmitha@gmail.com</div>
                                </div>
                            </td>
                            <td class="bill-date">Oct 25, 2023</td>
                            <td class="bill-amount">LKR 3,000</td>
                            <td class="bill-status">
                                <span class="status-badge status-pending">Pending</span>
                            </td>
                            <td class="bill-actions">
                                <button class="action-btn-edit">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path
                                            d="M3 13.5H13M2 11L11.5 1.5C11.8 1.2 12.3 1.2 12.6 1.5L14.5 3.4C14.8 3.7 14.8 4.2 14.5 4.5L5 14H2V11Z"
                                            stroke="currentColor" stroke-width="1.2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </button>
                                <button class="action-btn-delete">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path
                                            d="M2 4H14M6.5 7V11M9.5 7V11M3 4L4 13C4 13.5 4.5 14 5 14H11C11.5 14 12 13.5 12 13L13 4M5.5 4V2.5C5.5 2.2 5.7 2 6 2H10C10.3 2 10.5 2.2 10.5 2.5V4"
                                            stroke="currentColor" stroke-width="1.2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="bill-id-cell">
                                    <div class="bill-avatar">JD</div>
                                    <div class="bill-number">BL-1004</div>
                                </div>
                            </td>
                            <td>
                                <div class="patient-info-cell">
                                    <div class="patient-name">John Doe</div>
                                    <div class="patient-email">udemy4ucsc@gmail.com</div>
                                </div>
                            </td>
                            <td class="bill-date">Oct 24, 2023</td>
                            <td class="bill-amount">LKR 3,000</td>
                            <td class="bill-status">
                                <span class="status-badge status-paid">Paid</span>
                            </td>
                            <td class="bill-actions">
                                <button class="action-btn-edit">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path
                                            d="M3 13.5H13M2 11L11.5 1.5C11.8 1.2 12.3 1.2 12.6 1.5L14.5 3.4C14.8 3.7 14.8 4.2 14.5 4.5L5 14H2V11Z"
                                            stroke="currentColor" stroke-width="1.2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </button>
                                <button class="action-btn-delete">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path
                                            d="M2 4H14M6.5 7V11M9.5 7V11M3 4L4 13C4 13.5 4.5 14 5 14H11C11.5 14 12 13.5 12 13L13 4M5.5 4V2.5C5.5 2.2 5.7 2 6 2H10C10.3 2 10.5 2.2 10.5 2.5V4"
                                            stroke="currentColor" stroke-width="1.2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="bill-id-cell">
                                    <div class="bill-avatar">JS</div>
                                    <div class="bill-number">BL-1005</div>
                                </div>
                            </td>
                            <td>
                                <div class="patient-info-cell">
                                    <div class="patient-name">Jane Smith</div>
                                    <div class="patient-email">issumanmitha@gmail.com</div>
                                </div>
                            </td>
                            <td class="bill-date">Oct 25, 2023</td>
                            <td class="bill-amount">LKR 5,000</td>
                            <td class="bill-status">
                                <span class="status-badge status-paid">Paid</span>
                            </td>
                            <td class="bill-actions">
                                <button class="action-btn-edit">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path
                                            d="M3 13.5H13M2 11L11.5 1.5C11.8 1.2 12.3 1.2 12.6 1.5L14.5 3.4C14.8 3.7 14.8 4.2 14.5 4.5L5 14H2V11Z"
                                            stroke="currentColor" stroke-width="1.2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </button>
                                <button class="action-btn-delete">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path
                                            d="M2 4H14M6.5 7V11M9.5 7V11M3 4L4 13C4 13.5 4.5 14 5 14H11C11.5 14 12 13.5 12 13L13 4M5.5 4V2.5C5.5 2.2 5.7 2 6 2H10C10.3 2 10.5 2.2 10.5 2.5V4"
                                            stroke="currentColor" stroke-width="1.2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="bill-id-cell">
                                    <div class="bill-avatar">JD</div>
                                    <div class="bill-number">BL-1006</div>
                                </div>
                            </td>
                            <td>
                                <div class="patient-info-cell">
                                    <div class="patient-name">John Doe</div>
                                    <div class="patient-email">john001@gmail.com</div>
                                </div>
                            </td>
                            <td class="bill-date">Oct 25, 2023</td>
                            <td class="bill-amount">LKR 5,000</td>
                            <td class="bill-status">
                                <span class="status-badge status-pending">Pending</span>
                            </td>
                            <td class="bill-actions">
                                <button class="action-btn-edit">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path
                                            d="M3 13.5H13M2 11L11.5 1.5C11.8 1.2 12.3 1.2 12.6 1.5L14.5 3.4C14.8 3.7 14.8 4.2 14.5 4.5L5 14H2V11Z"
                                            stroke="currentColor" stroke-width="1.2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </button>
                                <button class="action-btn-delete">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path
                                            d="M2 4H14M6.5 7V11M9.5 7V11M3 4L4 13C4 13.5 4.5 14 5 14H11C11.5 14 12 13.5 12 13L13 4M5.5 4V2.5C5.5 2.2 5.7 2 6 2H10C10.3 2 10.5 2.2 10.5 2.5V4"
                                            stroke="currentColor" stroke-width="1.2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="bill-id-cell">
                                    <div class="bill-avatar">JS</div>
                                    <div class="bill-number">BL-1007</div>
                                </div>
                            </td>
                            <td>
                                <div class="patient-info-cell">
                                    <div class="patient-name">Jane Smith</div>
                                    <div class="patient-email">asme1@gmail.com</div>
                                </div>
                            </td>
                            <td class="bill-date">Oct 25, 2023</td>
                            <td class="bill-amount">LKR 10,000</td>
                            <td class="bill-status">
                                <span class="status-badge status-paid">Paid</span>
                            </td>
                            <td class="bill-actions">
                                <button class="action-btn-edit">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path
                                            d="M3 13.5H13M2 11L11.5 1.5C11.8 1.2 12.3 1.2 12.6 1.5L14.5 3.4C14.8 3.7 14.8 4.2 14.5 4.5L5 14H2V11Z"
                                            stroke="currentColor" stroke-width="1.2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </button>
                                <button class="action-btn-delete">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path
                                            d="M2 4H14M6.5 7V11M9.5 7V11M3 4L4 13C4 13.5 4.5 14 5 14H11C11.5 14 12 13.5 12 13L13 4M5.5 4V2.5C5.5 2.2 5.7 2 6 2H10C10.3 2 10.5 2.2 10.5 2.5V4"
                                            stroke="currentColor" stroke-width="1.2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>

</html>