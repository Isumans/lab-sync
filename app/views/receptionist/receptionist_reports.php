<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receptionist Reports</title>
    <link rel="stylesheet" href="/lab_sync/public/styles.css">
    <link rel="stylesheet" href="/lab_sync/public/reportsDashboard.css?v=20260413">
    <link rel="stylesheet" href="/lab_sync/public/receptionistReports.css?v=20260413">
</head>
<body>
    <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
    <div class="container">
        <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>

        <main class="main-content">
            <section class="reports-dashboard rr-dashboard" aria-label="Receptionist Reports Dashboard">
                <?php
                    $pageTitle = 'Authorized Reports';
                    $pageBreadcrumbText = 'Reports->Authorized Reports';
                    $pageActionHtml = '';
                    require __DIR__ . '/../../../public/partials/page-header.php';
                ?>

                <section class="rd-filter-card" aria-label="Search Reports">
                    <div class="rr-filter-row">
                        <div class="rd-filter-field rd-filter-field-search">
                            <label for="rrSearch">Search Records</label>
                            <input id="rrSearch" type="text" placeholder="Search by UHID, patient name, or reference no..." />
                        </div>
                        <button type="button" class="rd-clear-btn" id="rrClearBtn">Clear Search</button>
                    </div>
                </section>

                <section class="rd-table-card" aria-label="Authorized Reports Table">
                    <div class="rd-table-wrap">
                        <table class="rd-table rr-table">
                            <thead>
                                <tr>
                                    <th>Reference No</th>
                                    <th>Patient</th>
                                    <th>UHID</th>
                                    <th>Test</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th class="rd-th-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="rrTableBody"></tbody>
                        </table>
                    </div>

                    <div class="rd-table-footer">
                        <p id="rrShowingText">Showing 0-0 of 0 reports</p>
                        <div class="rd-pagination" id="rrPagination"></div>
                    </div>
                </section>

                <div class="rr-send-modal" id="rrSendModal" hidden>
                    <div class="rr-send-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="rrSendTitle">
                        <div class="rr-send-modal__header">
                            <h2 id="rrSendTitle">Send to Patient</h2>
                            <button type="button" id="rrSendCloseBtn" class="rr-send-modal__close" aria-label="Close">&times;</button>
                        </div>
                        <p id="rrSendDetails" class="rr-send-modal__details"></p>
                        <p class="rr-send-modal__note">This is a placeholder action for now. Email/SMS delivery will be added in a future update.</p>
                        <div class="rr-send-modal__actions">
                            <button type="button" class="rd-btn rd-btn-primary" id="rrSendOkBtn">OK</button>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script src="/lab_sync/public/js/receptionistReports.js?v=20260413"></script>
</body>
</html>
