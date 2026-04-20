<?php

$appointments = is_array($appointments ?? null) ? $appointments : [];
$prescriptionRequests = is_array($prescriptionRequests ?? null) ? $prescriptionRequests : [];
$requestTests = is_array($requestTests ?? null) ? $requestTests : [];
$flashSuccess = $_SESSION['success'] ?? '';
$flashError = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>LabSync - Patient Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/lab_sync/public/css/globals.css" />
  <link rel="stylesheet" href="/lab_sync/public/css/nav.css" />
  <link rel="stylesheet" href="/lab_sync/public/css/footer.css" />
  <link rel="stylesheet" href="/lab_sync/public/paymentModal.css" />
  <link rel="stylesheet" href="/lab_sync/public/appointmentEditModal.css" />
  <link rel="stylesheet" href="/lab_sync/public/appointmentDetailsModal.css" />
  <style>
    :root {
      --primary-color: #3DBDEC;
      --secondary-color: #ffffff;
      --background-color: #f4f6f9;
      --bg: #f4f6f9;
      --card: #ffffff;
      --line: #e3e7ee;
      --text: #243042;
      --muted: #6f7c8f;
      --danger: #a33b32;
      --danger-bg: #f3e3e3;
      --action-muted: #e6e7ea;
    }

    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: "DM Sans", Arial, sans-serif;
      background: var(--bg);
      color: var(--text);
    }

    .dashboard-wrap {
      max-width: 1180px;
      margin: 0 auto;
      padding: calc(5rem + 28px) 20px 56px;
    }

    .page-head {
      margin-bottom: 18px;
    }

    .page-head h1 {
      margin: 0;
      font-size: clamp(1.45rem, 2.3vw, 1.85rem);
      color: #1c2736;
    }

    .slider-tabs {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: #f2f4f7;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 5px;
      margin-bottom: 16px;
    }

    .slider-tab {
      border: 0;
      border-radius: 9px;
      background: transparent;
      color: #5b6f84;
      font-size: 0.96rem;
      font-weight: 700;
      padding: 10px 18px;
      cursor: pointer;
      transition: background-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
    }

    .slider-tab:hover {
      color: #36536a;
    }

    .slider-tab.is-active {
      background: #ffffff;
      color: var(--primary-color, #3DBDEC);
      box-shadow: 0 4px 10px rgba(16, 24, 40, 0.08);
    }

    .tab-panel[hidden] {
      display: none !important;
    }

    .card {
      background: var(--card);
      border: 1px solid var(--line);
      border-radius: 14px;
      box-shadow: 0 6px 18px rgba(21, 32, 53, 0.06);
    }

    .card-head {
      padding: 18px 18px 12px;
      border-bottom: 1px solid var(--line);
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
    }

    .card-head h2 {
      margin: 0;
      font-size: 1.05rem;
      letter-spacing: 0.02em;
    }

    .table-card {
      padding-top: 6px;
    }

    .flash {
      margin: 0 18px 14px;
      border-radius: 10px;
      padding: 10px 12px;
      font-weight: 600;
      font-size: 0.92rem;
    }

    .flash.success {
      background: #e9f9f2;
      border: 1px solid #bce9d1;
      color: #146742;
    }

    .flash.error {
      background: #fff1f1;
      border: 1px solid #ffd6d6;
      color: #9f1f1f;
    }

    .table-wrap {
      overflow-x: auto;
      padding: 0;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      min-width: 820px;
    }

    thead th {
      text-align: left;
      padding: 14px 18px;
      border-bottom: 1px solid #e5edf4;
      font-size: 0.66rem;
      letter-spacing: 0.09em;
      font-weight: 800;
      text-transform: uppercase;
      color: #708193;
    }

    tbody td {
      padding: 14px 18px;
      border-bottom: 1px solid #edf2f6;
      vertical-align: middle;
      font-size: 0.9rem;
      color: #263b50;
    }

    tbody tr:hover {
      background: #f8fbff;
    }

    .status-pill {
      display: inline-flex;
      align-items: center;
      border-radius: 999px;
      padding: 6px 11px;
      font-weight: 800;
      font-size: 0.74rem;
      letter-spacing: 0.03em;
      background: #eef4fa;
      color: #35526e;
      text-transform: uppercase;
    }

    .status-pill.confirmed { background: #e6f8ed; color: #1e7f48; }
    .status-pill.pending { background: #fff7e4; color: #916b0f; }
    .status-pill.cancelled { background: #f5e7e7; color: #9f2d2d; }

    .home-badge {
      font-weight: 700;
      color: #35526e;
    }

    .home-badge.no { color: #7a8595; }

    .actions {
      display: flex;
      gap: 8px;
      justify-content: flex-end;
      align-items: center;
      min-width: 86px;
    }

    .action-btn {
      width: 36px;
      height: 36px;
      border: 0;
      border-radius: 6px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: transform .16s ease, box-shadow .16s ease, background-color .16s ease;
    }

    .action-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 2px 8px rgba(19, 29, 48, 0.16);
    }

    .action-btn.edit { background: var(--primary-500); color: #fff; }
    .action-btn.edit:hover { background: var(--primary-600); }
    .action-btn.delete { background: #fbe8e8; color: #c2413c; }
    .action-btn svg { width: 16px; height: 16px; }

    .empty {
      color: var(--muted);
      text-align: center;
      padding: 30px 10px;
      font-weight: 600;
    }

    .results-card {
      margin-top: 20px;
      padding: 18px;
    }

    .rx-card {
      margin-top: 20px;
      padding-bottom: 10px;
    }

    .rx-note {
      margin: 0 18px 14px;
      color: #5f6d80;
      font-size: 0.92rem;
    }

    .rx-pill {
      display: inline-flex;
      align-items: center;
      border-radius: 999px;
      padding: 6px 11px;
      font-weight: 800;
      font-size: 0.74rem;
      letter-spacing: 0.03em;
      background: #eef4fa;
      color: #35526e;
      text-transform: uppercase;
    }

    .rx-pill.pending { background: #fff7e4; color: #916b0f; }
    .rx-pill.approved { background: #e6f8ed; color: #1e7f48; }
    .rx-pill.rejected { background: #f5e7e7; color: #9f2d2d; }

    .file-link {
      color: var(--primary-600);
      font-weight: 700;
      text-decoration: none;
    }

    .file-link:hover {
      text-decoration: underline;
    }

    .cell-strong {
      font-weight: 700;
      color: #445d76;
    }

    .tests-cell {
      max-width: 320px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .th-right,
    .td-right {
      text-align: right;
    }

    .table-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 16px;
      gap: 12px;
      flex-wrap: wrap;
      border-top: 1px solid #edf2f6;
    }

    .table-footer p {
      margin: 0;
      font-size: 0.8rem;
      color: #6b7d91;
    }

    .table-pagination {
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .page-btn {
      min-width: 32px;
      height: 32px;
      padding: 0 10px;
      border: 1px solid #dbe5ef;
      border-radius: 8px;
      background: #fff;
      color: #5a7087;
      font-weight: 700;
      cursor: pointer;
      transition: border-color .16s ease, color .16s ease, background-color .16s ease;
    }

    .page-btn:hover:not(:disabled) {
      border-color: var(--primary-color, #3DBDEC);
      color: var(--primary-color, #3DBDEC);
    }

    .page-btn.is-active {
      background: var(--primary-color, #3DBDEC);
      border-color: var(--primary-color, #3DBDEC);
      color: #fff;
    }

    .page-btn:disabled {
      opacity: 0.45;
      cursor: default;
    }

    .results-note {
      margin: 0 0 14px;
      color: #5f6d80;
      font-size: 0.92rem;
    }

    .results-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 14px;
    }

    .result-item {
      border: 1px solid var(--line);
      border-radius: 12px;
      background: #f9fbfe;
      padding: 13px;
    }

    .result-item h3 {
      margin: 0 0 8px;
      font-size: 0.98rem;
      color: #1d2939;
    }

    .result-meta {
      margin: 0;
      font-size: 0.87rem;
      color: #64748b;
      line-height: 1.55;
    }

    .modal {
      position: fixed;
      inset: 0;
      background: rgba(8, 15, 30, 0.58);
      display: none;
      align-items: center;
      justify-content: center;
      padding: 16px;
      z-index: 1100;
    }

    .modal.is-open { display: flex; }

    .modal-dialog {
      width: min(560px, 100%);
      background: #fff;
      border-radius: 14px;
      border: 1px solid #dae2eb;
      box-shadow: 0 24px 58px rgba(0, 0, 0, 0.23);
      overflow: hidden;
    }

    .modal-head {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 14px 16px;
      border-bottom: 1px solid #e7ecf2;
    }

    .modal-head h3 {
      margin: 0;
      font-size: 1rem;
    }

    .modal-close {
      border: 0;
      background: transparent;
      font-size: 25px;
      line-height: 1;
      color: #5d6a7b;
      cursor: pointer;
    }

    .modal-body {
      padding: 14px 16px 16px;
    }

    .modal-row {
      margin-bottom: 12px;
    }

    .modal-row label {
      display: block;
      margin-bottom: 6px;
      color: #526072;
      font-size: 0.82rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }

    .modal-row input,
    .modal-row textarea,
    .modal-row p,
    .modal-row select {
      margin: 0;
      width: 100%;
      border: 1px solid #d5dee8;
      border-radius: 10px;
      padding: 10px;
      font-size: 0.92rem;
      color: #223046;
      background: #fff;
    }

    .modal-row p {
      background: #f7f9fc;
      line-height: 1.4;
    }

    .checkbox-row {
      display: flex;
      align-items: center;
      gap: 10px;
      border: 1px solid #d5dee8;
      border-radius: 10px;
      background: #f7f9fc;
      padding: 10px 12px;
    }

    .checkbox-row input {
      width: 16px;
      height: 16px;
      margin: 0;
      flex: 0 0 auto;
    }

    .checkbox-row span {
      font-size: 0.92rem;
      color: #223046;
      font-weight: 600;
    }

    .modal-actions {
      display: flex;
      justify-content: flex-end;
      gap: 8px;
      margin-top: 8px;
    }

    .btn {
      border: 0;
      border-radius: 10px;
      padding: 10px 12px;
      font-weight: 700;
      cursor: pointer;
      font-size: 0.9rem;
    }

    .btn.secondary { background: #edf2f8; color: #314156; }
    .btn.primary { background: var(--primary-500); color: #fff; }
    .btn.primary:hover { background: var(--primary-600); }
    .btn.danger { background: #c53d3d; color: #fff; }

    @media (max-width: 720px) {
      .dashboard-wrap {
        padding: calc(5rem + 14px) 14px 34px;
      }

      .page-head {
        margin-bottom: 14px;
      }

      .slider-tabs {
        display: flex;
        width: 100%;
        overflow-x: auto;
        white-space: nowrap;
        padding: 5px;
        scrollbar-width: thin;
      }

      .slider-tab {
        flex: 0 0 auto;
        padding: 10px 14px;
        font-size: 0.9rem;
      }

      .card-head {
        padding: 14px;
        flex-direction: column;
        align-items: flex-start;
      }

      .flash {
        margin-left: 14px;
        margin-right: 14px;
      }

      thead th,
      tbody td {
        padding: 12px 14px;
      }

      .actions {
        justify-content: flex-start;
        min-width: 0;
        flex-wrap: wrap;
      }

      .action-btn {
        width: 42px;
        height: 42px;
      }

      .action-btn svg {
        width: 18px;
        height: 18px;
      }

      .modal-actions {
        flex-direction: column;
      }

      .modal-actions .btn {
        width: 100%;
      }
    }

    @media (max-width: 480px) {
      .dashboard-wrap {
        padding-left: 12px;
        padding-right: 12px;
      }

      .slider-tab {
        font-size: 0.85rem;
      }

      .card-head h2 {
        font-size: 0.98rem;
      }
    }
  </style>
</head>
<body>
<?php require_once __DIR__ . '/../../../public/partials/header.php'; ?>

<main class="dashboard-wrap">
  <div class="page-head">
    <h1>Patient Dashboard</h1>
  </div>

  <div class="slider-tabs" role="tablist" aria-label="Patient dashboard sections">
    <button type="button" class="slider-tab is-active" role="tab" aria-selected="true" data-patient-tab="appointments">
      Appointments
    </button>
    <button type="button" class="slider-tab" role="tab" aria-selected="false" data-patient-tab="prescriptions">
      Prescription Submissions
    </button>
    <button type="button" class="slider-tab" role="tab" aria-selected="false" data-patient-tab="results">
      Test Results
    </button>
    <button type="button" class="slider-tab" role="tab" aria-selected="false" data-patient-tab="bills">
      Bills
    </button>
  </div>

  <div id="patientAppointmentsSection" class="tab-panel is-active" role="tabpanel" aria-label="Appointments">
  <section class="card table-card">
    <div class="card-head">
      <h2>Appointments</h2>
    </div>

    <?php if ($flashSuccess !== ''): ?>
      <div class="flash success"><?php echo htmlspecialchars($flashSuccess); ?></div>
    <?php endif; ?>

    <?php if ($flashError !== ''): ?>
      <div class="flash error"><?php echo htmlspecialchars($flashError); ?></div>
    <?php endif; ?>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Appointment ID</th>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
            <th>Home Collection</th>
            <th class="th-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($appointments) > 0): ?>
            <?php foreach ($appointments as $appointment): ?>
              <?php
                $id = (int)($appointment['appointment_id'] ?? 0);
                $date = (string)($appointment['appointment_date'] ?? 'N/A');
                $time = (string)($appointment['appointment_time'] ?? 'N/A');
                $testsSummary = (string)($appointment['tests_summary'] ?? $appointment['test_name'] ?? 'N/A');
                $status = (string)($appointment['appointment_status'] ?? 'Pending');
                $homeCollection = !empty($appointment['home_collection']);
                $statusClass = strtolower($status);
                $appointmentRef = 'APP-' . str_pad((string)$id, 4, '0', STR_PAD_LEFT);
              ?>
              <tr
                data-id="<?php echo htmlspecialchars((string)$id); ?>"
                data-date="<?php echo htmlspecialchars($date); ?>"
                data-time="<?php echo htmlspecialchars($time); ?>"
                data-tests="<?php echo htmlspecialchars($testsSummary); ?>"
                data-status="<?php echo htmlspecialchars($status); ?>"
                data-address="<?php echo htmlspecialchars((string)($appointment['collection_address'] ?? '')); ?>"
                data-home="<?php echo $homeCollection ? 'Yes' : 'No'; ?>"
              >
                <td class="cell-strong"><?php echo htmlspecialchars($appointmentRef); ?></td>
                <td class="cell-strong"><?php echo htmlspecialchars($date); ?></td>
                <td><?php echo htmlspecialchars($time); ?></td>
                <td><span class="status-pill <?php echo htmlspecialchars($statusClass); ?>"><?php echo htmlspecialchars($status); ?></span></td>
                <td><span class="home-badge <?php echo $homeCollection ? '' : 'no'; ?>"><?php echo $homeCollection ? 'Yes' : 'No'; ?></span></td>
                <td class="td-right">
                  <div class="actions">
                    <button type="button" class="action-btn edit" title="View" onclick="openView(this)">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                    <button type="button" class="action-btn edit" title="Edit" onclick="openEdit(this)">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"></path></svg>
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="empty">No appointments found. Book your first appointment to get started.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="table-footer">
      <p id="appointmentsShowingText">Showing 0-0 of 0 appointments</p>
      <div class="table-pagination" id="appointmentsPagination"></div>
    </div>
  </section>
  </div>

  <div id="patientPrescriptionsSection" class="tab-panel" role="tabpanel" aria-label="Prescription submissions" hidden>
  <section class="card table-card rx-card" id="prescription-submissions">
    <div class="card-head">
      <h2>Prescription Submissions</h2>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Submitted On</th>
            <th>Type</th>
            <th>Status</th>
            <th>File</th>
            <th class="th-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($prescriptionRequests) > 0): ?>
            <?php foreach ($prescriptionRequests as $request): ?>
              <?php
                $reqId       = (int)($request['request_id'] ?? 0);
                $createdAt   = (string)($request['created_at'] ?? 'N/A');
                $status      = strtolower(trim((string)($request['status'] ?? 'pending')));
                $statusLabel = ucwords(str_replace('_', ' ', $status));
                $filePath    = ltrim(str_replace('\\', '/', (string)($request['prescription_file_path'] ?? '')), '/');
                $fileUrl     = $filePath !== '' ? '/lab_sync/' . $filePath : '';
                $visitType   = strtoupper(trim((string)($request['visit_type'] ?? '')));
                $homeCol     = !empty($request['home_collection']);
                $typeLabel   = ($visitType === 'HOME_VISIT' || $homeCol) ? 'Home Visit' : 'Onsite';
                $isCommunicated = $status === 'communicated';
                $isBooked    = !empty($request['linked_appointment_id']);
                $tests       = $requestTests[$reqId] ?? [];
                $testsForModal = array_map(function($t) {
                    return ['test_id' => (int)$t['test_id'], 'test_name' => (string)$t['test_name'], 'unit_price' => (float)$t['unit_price']];
                }, $tests);
              ?>
              <tr>
                <td class="cell-strong"><?php echo htmlspecialchars($createdAt); ?></td>
                <td><?php echo htmlspecialchars($typeLabel); ?></td>
                <td><span class="rx-pill <?php echo htmlspecialchars($status); ?>"><?php echo htmlspecialchars($statusLabel); ?></span></td>
                <td>
                  <?php if ($fileUrl !== ''): ?>
                    <a class="file-link" href="<?php echo htmlspecialchars($fileUrl); ?>" target="_blank" rel="noopener">View File</a>
                  <?php else: ?>
                    N/A
                  <?php endif; ?>
                </td>
                <td class="td-right">
                  <?php if ($isCommunicated && !$isBooked): ?>
                    <button type="button" class="rx-book-btn"
                      data-request-id="<?php echo $reqId; ?>"
                      data-tests="<?php echo htmlspecialchars(json_encode($testsForModal), ENT_QUOTES); ?>"
                      data-home="<?php echo $homeCol ? '1' : '0'; ?>"
                      data-address="<?php echo htmlspecialchars((string)($request['collection_address'] ?? '')); ?>"
                      onclick="openRxBookingModal(this)">
                      Book Appointment
                    </button>
                  <?php elseif ($isBooked): ?>
                    <span class="rx-booked-label">Appointment booked</span>
                  <?php else: ?>
                    —
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="empty">No prescription submissions yet. Submit one from the Help section and it will appear here.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="table-footer">
      <p id="prescriptionsShowingText">Showing 0-0 of 0 requests</p>
      <div class="table-pagination" id="prescriptionsPagination"></div>
    </div>
  </section>
  </div>

  <div id="patientResultsSection" class="tab-panel" role="tabpanel" aria-label="Test results" hidden>
  <section class="card table-card">
    <div class="card-head">
      <h2>Test Results</h2>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Appointment ID</th>
            <th>Test</th>
            <th>Report Date</th>
            <th>Status</th>
            <th class="th-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $patientReports = is_array($patientReports ?? null) ? $patientReports : [];
          ?>
          <?php if (count($patientReports) > 0): ?>
            <?php foreach ($patientReports as $rep): ?>
              <?php
                $appIdFormatted = 'APP-' . str_pad((string)intval($rep['appointment_id']), 4, '0', STR_PAD_LEFT);
                $reportDate     = '';
                if (!empty($rep['pdf_generated_at'])) {
                    try {
                        $dt = new DateTime($rep['pdf_generated_at']);
                        $reportDate = $dt->format('d/m/Y H:i');
                    } catch (Exception $e) {
                        $reportDate = (string)$rep['pdf_generated_at'];
                    }
                }
                $viewUrl = '/lab_sync/index.php?controller=reportsController&action=printReport'
                         . '&appointment_id=' . intval($rep['appointment_id'])
                         . '&test_id=' . intval($rep['test_id'])
                         . '&auto_print=1';
              ?>
              <tr>
                <td class="cell-strong"><?php echo htmlspecialchars($appIdFormatted); ?></td>
                <td><?php echo htmlspecialchars((string)($rep['test_name'] ?? '—')); ?></td>
                <td><?php echo htmlspecialchars($reportDate ?: '—'); ?></td>
                <td><span class="status-pill confirmed">Authorized</span></td>
                <td class="td-right">
                  <div class="actions">
                    <a href="<?php echo htmlspecialchars($viewUrl); ?>" target="_blank" rel="noopener"
                       class="action-btn edit" title="View Report" style="text-decoration:none;">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                      </svg>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" style="text-align:center; padding:28px; color:#64748b;">
                No authorized test reports available yet.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
  </div>

  <div id="patientBillsSection" class="tab-panel" role="tabpanel" aria-label="Bills" hidden>
  <section class="card table-card">
    <div class="card-head">
      <h2>Bills</h2>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Bill No.</th>
            <th>Appointment Date</th>
            <th>Bill Date</th>
            <th class="th-right">Total</th>
            <th class="th-right">Paid</th>
            <th>Status</th>
            <th class="th-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $patientBills = is_array($patientBills ?? null) ? $patientBills : [];
            $billStatusClasses = [
              'PAID'           => 'confirmed',
              'PARTIALLY_PAID' => 'pending',
              'PENDING'        => 'pending',
              'DRAFT'          => 'pending',
              'CANCELLED'      => 'cancelled',
            ];
            $billStatusLabels = [
              'PAID'           => 'Paid',
              'PARTIALLY_PAID' => 'Partially Paid',
              'PENDING'        => 'Pending',
              'DRAFT'          => 'Draft',
              'CANCELLED'      => 'Cancelled',
            ];
          ?>
          <?php if (count($patientBills) > 0): ?>
            <?php foreach ($patientBills as $bill): ?>
              <?php
                $billStatus     = strtoupper((string)($bill['status'] ?? 'PENDING'));
                $statusClass    = $billStatusClasses[$billStatus] ?? 'pending';
                $statusLabel    = $billStatusLabels[$billStatus]  ?? ucfirst(strtolower($billStatus));
                $invoiceUrl     = '/lab_sync/index.php?controller=billingController&action=printInvoice&bill_id=' . intval($bill['bill_id']);
              ?>
              <tr>
                <td class="cell-strong"><?php echo htmlspecialchars((string)($bill['bill_number'] ?? '—')); ?></td>
                <td><?php echo htmlspecialchars((string)($bill['appointment_date'] ?? '—')); ?></td>
                <td><?php echo htmlspecialchars((string)($bill['bill_date'] ?? '—')); ?></td>
                <td class="td-right">LKR <?php echo number_format((float)($bill['total_amount'] ?? 0), 2); ?></td>
                <td class="td-right">LKR <?php echo number_format((float)($bill['paid_amount'] ?? 0), 2); ?></td>
                <td><span class="status-pill <?php echo htmlspecialchars($statusClass); ?>"><?php echo htmlspecialchars($statusLabel); ?></span></td>
                <td class="td-right">
                  <div class="actions">
                    <a href="<?php echo htmlspecialchars($invoiceUrl); ?>" target="_blank" rel="noopener"
                       class="action-btn edit" title="View Invoice" style="text-decoration:none;">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                      </svg>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="empty">No bills yet. Bills will appear here after your payments are processed.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="table-footer">
      <p id="billsShowingText">Showing 0-0 of 0 bills</p>
      <div class="table-pagination" id="billsPagination"></div>
    </div>
  </section>
  </div>
</main>

<div id="appointmentDetailsModal" class="appointment-details-modal" aria-hidden="true">
  <div class="appointment-details-dialog" role="dialog" aria-modal="true" aria-labelledby="appointmentDetailsTitle">
    <div class="appointment-details-topbar">
      <div id="appointmentDetailsTitle" class="appointment-details-title">Appointment Details</div>
      <button id="appointmentDetailsClose" class="appointment-details-close" type="button" aria-label="Close details">&times;</button>
    </div>
    <div id="appointmentDetailsBody" class="appointment-details-body"></div>
  </div>
</div>

<div class="appointment-edit-modal" id="patientEditAppointmentModal" aria-hidden="true">
  <div class="appointment-edit-dialog" role="dialog" aria-modal="true" aria-labelledby="patientEditAppointmentTitle">
    <form id="patientEditAppointmentForm" method="POST" action="/lab_sync/index.php?controller=home&action=edit_appointment">
      <div class="appointment-edit-header">
        <div>
          <h2 id="patientEditAppointmentTitle">Edit Appointment</h2>
          <p class="appointment-edit-subtitle">UPDATE YOUR APPOINTMENT DETAILS</p>
        </div>
        <button type="button" class="appointment-edit-close" id="patientEditAppointmentClose" aria-label="Close">&times;</button>
      </div>

      <div class="appointment-edit-alert" id="patientEditAppointmentAlert" hidden></div>

      <div class="appointment-edit-body">
        <input type="hidden" name="appointment_id" id="patientEditAppointmentId" value="">
        <input type="hidden" name="time" id="patientEditTimeInput" value="">

        <section class="edit-section-card">
          <div class="edit-section-title">
            <span class="section-icon" aria-hidden="true">&#128197;</span>
            <h3>Appointment Summary</h3>
          </div>
          <div class="patient-readonly-card">
            <div class="patient-identity">
              <span class="patient-avatar" aria-hidden="true">AP</span>
              <div>
                <p class="patient-name" id="patientEditSummaryTitle">Appointment</p>
                <p class="patient-pid" id="patientEditSummaryTests">Tests</p>
              </div>
            </div>
            <span class="readonly-badge" id="patientEditSummaryStatus">Pending</span>
          </div>
        </section>

        <section class="edit-section-card">
          <div class="edit-section-title">
            <span class="section-icon" aria-hidden="true">&#9201;</span>
            <h3>Scheduling</h3>
          </div>

          <div class="schedule-grid">
            <div>
              <label class="edit-label" for="patientEditDateInput">Select Date</label>
              <div class="date-input-wrap">
                <span class="date-icon" aria-hidden="true">&#128197;</span>
                <input id="patientEditDateInput" name="date" type="date" required>
              </div>
            </div>

            <div>
              <label class="edit-label">Time Slots</label>
              <div class="time-slot-grid" id="patientEditTimeSlots"></div>
            </div>
          </div>

        </section>

        <section class="edit-section-card">
          <div class="edit-section-title">
            <span class="section-icon" aria-hidden="true">&#127968;</span>
            <h3>Collection</h3>
          </div>

          <div class="modal-row">
            <label>Home Collection</label>
            <div class="checkbox-row">
              <input id="patientEditHomeCollection" name="home_collection" type="checkbox" value="1">
              <span>Require a home visit for this appointment</span>
            </div>
          </div>

          <div class="modal-row" id="patientEditAddressRow">
            <label for="patientEditAddressInput">Collection Address</label>
            <textarea id="patientEditAddressInput" name="collection_address" rows="3" placeholder="Enter the sample collection address"></textarea>
          </div>
        </section>
      </div>

      <div class="appointment-edit-footer">
        <button type="button" class="edit-cancel-btn" id="patientEditAppointmentCancel">Cancel</button>
        <button type="submit" name="edit" class="edit-submit-btn" id="patientEditAppointmentSubmit">
          <span aria-hidden="true">&#128190;</span> Update Appointment
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ── Prescription Booking Modal ───────────────────────── -->
<div id="rxBookingModal" class="rxm-overlay" aria-hidden="true" hidden>
  <div class="rxm-card" role="dialog" aria-modal="true" aria-labelledby="rxmTitle">

    <div class="rxm-header">
      <div>
        <h2 class="rxm-title" id="rxmTitle">Book Appointment</h2>
        <p class="rxm-sub">SCHEDULE NEW LABORATORY ANALYSIS</p>
      </div>
      <button type="button" class="rxm-close" onclick="closeRxBookingModal()" aria-label="Close">&times;</button>
    </div>

    <div class="rxm-body">
      <form id="rxBookingForm" method="POST" action="/lab_sync/index.php?controller=home&action=bookAppointment">
        <input type="hidden" name="from_request"      id="rxFromRequest">
        <input type="hidden" name="appointment_time"  id="rxApptTime">
        <input type="hidden" name="appointment_date"  id="rxApptDate">
        <input type="hidden" name="home_collection"   id="rxHomeCollection" value="0">
        <input type="hidden" name="collection_address" id="rxCollectionAddr" value="">

        <!-- Selected tests -->
        <section class="rxm-section">
          <h3 class="rxm-section-title">SELECTED TESTS</h3>
          <div id="rxTestRows"></div>
          <p id="rxNoTests" class="rxm-empty-note" hidden>No tests were included with this request.</p>
        </section>


        <!-- Scheduling -->
        <section class="rxm-section">
          <h3 class="rxm-section-title">SCHEDULING</h3>

          <div class="rxm-sched-row">
            <h4 class="rxm-sched-label">SELECT DATE</h4>
            <div class="rxm-date-btns" id="rxDateBtns">
              <button type="button" class="rxm-date-btn" data-offset="0">Today</button>
              <button type="button" class="rxm-date-btn" data-offset="1">Tomorrow</button>
              <button type="button" class="rxm-date-btn rxm-date-other" id="rxBtnOther">
                <span>&#128197;</span> Other
              </button>
            </div>
            <input type="date" id="rxDatePicker" class="rxm-date-picker" style="display:none;">
          </div>

          <div class="rxm-sched-row">
            <h4 class="rxm-sched-label">SELECT TIME SLOT</h4>
            <div id="rxSlotsLoading" style="display:none;font-size:12px;color:#8b9ab0;padding:4px 0;">Loading slots...</div>
            <div id="rxSlotsEmpty"   style="display:none;font-size:12px;color:#8b9ab0;padding:4px 0;">No slots available for this day.</div>
            <div id="rxSlotGrid" class="rxm-slot-rail"></div>
          </div>
        </section>

        <!-- Footer -->
        <div class="rxm-footer">
          <div class="rxm-total-block">
            <span class="rxm-total-label">GRAND TOTAL</span>
            <span id="rxGrandTotal" class="rxm-total-val">LKR 0.00</span>
          </div>
          <button type="button" class="rxm-confirm-btn" id="rxConfirmBtn" onclick="submitRxBooking()">
            Confirm &amp; Proceed Payment &#8594;
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
  /* ── Button in table ── */
  .rx-book-btn {
    padding: 6px 14px; background: var(--primary-color, #3DBDEC); color: #fff;
    border: none; border-radius: 6px; font-size: .82rem; font-weight: 600;
    cursor: pointer; white-space: nowrap;
  }
  .rx-book-btn:hover { background: var(--primary-color, #3DBDEC); }
  .rx-booked-label { font-size: .8rem; color: var(--muted, #6f7c8f); }

  /* ── Overlay ── */
  .rxm-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.45);
    display: flex; align-items: center; justify-content: center;
    z-index: 9000; padding: 16px;
  }
  .rxm-overlay[hidden] { display: none; }

  /* ── Card ── */
  .rxm-card {
    background: #fff; border-radius: 14px; width: 100%; max-width: 540px;
    max-height: 90vh; display: flex; flex-direction: column;
    box-shadow: 0 20px 60px rgba(0,0,0,.22); overflow: hidden;
  }

  /* ── Header ── */
  .rxm-header {
    display: flex; align-items: flex-start; justify-content: space-between;
    background: #0f1e35; color: #fff; padding: 20px 22px 16px;
  }
  .rxm-title { margin: 0 0 3px; font-size: 1.15rem; font-weight: 700; color: #fff; }
  .rxm-sub   { margin: 0; font-size: .7rem; letter-spacing: .08em; color: #8fa3bc; font-weight: 600; }
  .rxm-close {
    background: none; border: none; color: #8fa3bc; font-size: 1.5rem;
    cursor: pointer; line-height: 1; padding: 0 0 0 12px; margin-top: -2px;
  }
  .rxm-close:hover { color: #fff; }

  /* ── Scrollable body ── */
  .rxm-body { overflow-y: auto; flex: 1; padding: 0 22px; }

  /* ── Sections ── */
  .rxm-section { padding: 18px 0 14px; border-bottom: 1px solid #eaecf0; }
  .rxm-section:last-of-type { border-bottom: 0; }
  .rxm-section-title {
    margin: 0 0 12px; font-size: .68rem; letter-spacing: .1em;
    font-weight: 700; color: #8b9ab0; text-transform: uppercase;
  }

  /* ── Test rows ── */
  .rxm-test-row {
    display: flex; align-items: center; gap: 10px;
    background: #f6f8fb; border: 1px solid #e2e7ef;
    border-radius: 8px; padding: 9px 12px; margin-bottom: 7px;
  }
  .rxm-test-icon { width: 26px; height: 26px; background: #dde8f7; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: .8rem; }
  .rxm-test-name { flex: 1; font-size: .9rem; font-weight: 500; color: #1a2840; }
  .rxm-test-price { font-size: .88rem; font-weight: 600; color: #1a2840; white-space: nowrap; }
  .rxm-empty-note { font-size: .85rem; color: #8b9ab0; margin: 4px 0 0; }

  /* ── Search ── */

  /* ── Date buttons ── */
  .rxm-date-btns { display: flex; gap: 8px; flex-wrap: wrap; }
  .rxm-date-btn {
    padding: 7px 18px; border: 1.5px solid #d1d8e3; border-radius: 8px;
    background: #fff; font-size: .85rem; font-weight: 600; color: #3a4a5c;
    cursor: pointer; display: flex; align-items: center; gap: 5px;
  }
  .rxm-date-btn:hover { border-color: #1b6fcb; color: #1b6fcb; }
  .rxm-date-btn.active { background: #1b6fcb; border-color: #1b6fcb; color: #fff; }
  .rxm-date-picker {
    margin-top: 10px; padding: 7px 10px; border: 1.5px solid #d1d8e3;
    border-radius: 8px; font-size: .88rem; color: #1a2840; width: 180px;
  }
  .rxm-date-picker:focus { border-color: #1b6fcb; outline: none; }

  /* ── Slots ── */
  .rxm-sched-row { margin-bottom: 14px; }
  .rxm-sched-label { margin: 0 0 8px; font-size: .78rem; font-weight: 600; color: #3a4a5c; text-transform: uppercase; letter-spacing: .06em; }
  .rxm-slot-label { font-size: .76rem; color: #8b9ab0; font-weight: 600; margin: 8px 0 5px; }
  .rxm-slot-rail { display: flex; flex-wrap: wrap; gap: 7px; }
  .rxm-slot {
    padding: 6px 13px; border: 1.5px solid #d1d8e3; border-radius: 7px;
    background: #fff; font-size: .82rem; font-weight: 600; color: #3a4a5c;
    cursor: pointer;
  }
  .rxm-slot:hover:not(:disabled) { border-color: #1b6fcb; color: #1b6fcb; }
  .rxm-slot.active { background: #1b6fcb; border-color: #1b6fcb; color: #fff; }
  .rxm-slot:disabled { opacity: .4; cursor: default; }

  /* ── Footer ── */
  .rxm-footer {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 22px 20px; border-top: 1px solid #eaecf0; gap: 14px; flex-wrap: wrap;
  }
  .rxm-total-block { display: flex; flex-direction: column; }
  .rxm-total-label { font-size: .68rem; letter-spacing: .1em; font-weight: 700; color: #8b9ab0; text-transform: uppercase; }
  .rxm-total-val { font-size: 1.2rem; font-weight: 700; color: #0f1e35; margin-top: 2px; }
  .rxm-confirm-btn {
    padding: 10px 22px; background: var(--primary-color, #3DBDEC); color: #fff;
    border: none; border-radius: 8px; font-size: .9rem; font-weight: 700;
    cursor: pointer; white-space: nowrap;
  }
  .rxm-confirm-btn:hover { background: var(--primary-color, #3DBDEC); }
  .rxm-confirm-btn:disabled { background: #b0bfce; cursor: default; }
</style>

<script>
(function () {
  var BASE = (window.LAB_SYNC_CONFIG && window.LAB_SYNC_CONFIG.baseUrl)
    ? String(window.LAB_SYNC_CONFIG.baseUrl).replace(/\/$/, '')
    : '/lab_sync';

  var state = {
    requestId: 0,
    tests: [],           // [{test_id, test_name, unit_price}]
    date: '',
    time: ''
  };

  /* ── helpers ── */
  function esc(str) {
    return String(str == null ? '' : str)
      .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
      .replace(/"/g,'&quot;').replace(/'/g,'&#039;');
  }

  function fmtTime(t) {
    var m = t.match(/^(\d{1,2}):(\d{2})$/);
    if (!m) return t;
    var h = parseInt(m[1], 10), min = m[2];
    return (h % 12 || 12) + ':' + min + ' ' + (h >= 12 ? 'PM' : 'AM');
  }

  function updateTotal() {
    var sum = state.tests.reduce(function(a, t){ return a + Number(t.unit_price || 0); }, 0);
    document.getElementById('rxGrandTotal').textContent = 'LKR ' + sum.toFixed(2);
  }

  /* ── render test list ── */
  function renderTests() {
    var container = document.getElementById('rxTestRows');
    var noTests   = document.getElementById('rxNoTests');
    if (state.tests.length === 0) {
      container.innerHTML = '';
      noTests.hidden = false;
    } else {
      noTests.hidden = true;
      container.innerHTML = state.tests.map(function(t) {
        return '<div class="rxm-test-row">' +
          '<span class="rxm-test-icon">&#128300;</span>' +
          '<span class="rxm-test-name">' + esc(t.test_name) + '</span>' +
          '<span class="rxm-test-price">LKR ' + Number(t.unit_price || 0).toFixed(2) + '</span>' +
          '</div>';
      }).join('');
    }
    updateTotal();
  }

  /* ── date selection ── */
  function setDate(dateStr) {
    state.date = dateStr;
    document.getElementById('rxApptDate').value = dateStr;
    renderSlots();
  }

  function todayStr(offsetDays) {
    var d = new Date();
    d.setDate(d.getDate() + (offsetDays || 0));
    return d.toISOString().slice(0, 10);
  }

  document.getElementById('rxDateBtns').addEventListener('click', function(e) {
    var btn = e.target.closest('.rxm-date-btn');
    if (!btn) return;

    document.querySelectorAll('.rxm-date-btn').forEach(function(b){ b.classList.remove('active'); });
    btn.classList.add('active');

    var picker = document.getElementById('rxDatePicker');
    if (btn.id === 'rxBtnOther') {
      picker.style.display = 'block';
      picker.focus();
    } else {
      picker.style.display = 'none';
      setDate(todayStr(parseInt(btn.getAttribute('data-offset') || '0', 10)));
    }
  });

  document.getElementById('rxDatePicker').addEventListener('change', function() {
    if (this.value) setDate(this.value);
  });

  /* ── slot rendering ── */
  function renderSlots() {
    var grid    = document.getElementById('rxSlotGrid');
    var loading = document.getElementById('rxSlotsLoading');
    var empty   = document.getElementById('rxSlotsEmpty');

    grid.innerHTML = '';
    state.time = '';
    document.getElementById('rxApptTime').value = '';

    if (!state.date) {
      loading.style.display = 'none';
      empty.style.display   = 'none';
      return;
    }

    loading.style.display = 'block';
    empty.style.display   = 'none';

    fetch(BASE + '/index.php?controller=home&action=getAvailableSlots&date=' + encodeURIComponent(state.date))
      .then(function(r) { return r.json(); })
      .then(function(slots) {
        loading.style.display = 'none';
        grid.innerHTML = '';
        if (!Array.isArray(slots) || slots.length === 0) {
          empty.style.display = 'block';
          return;
        }

        var selectedDate = state.date;
        var today = new Date();
        var todayStr = today.toISOString().slice(0, 10);
        var nowMinutes = today.getHours() * 60 + today.getMinutes();

        var visibleSlots = slots.filter(function(slot) {
          if (selectedDate !== todayStr) {
            return true;
          }

          var parts = String(slot.start_time || '').split(':');
          if (parts.length < 2) {
            return false;
          }

          var slotMinutes = Number(parts[0]) * 60 + Number(parts[1]);
          return slotMinutes > nowMinutes;
        });

        if (visibleSlots.length === 0) {
          empty.style.display = 'block';
          return;
        }

        visibleSlots.forEach(function(slot) {
          var btn = document.createElement('button');
          btn.type = 'button';
          btn.className = 'rxm-slot';
          btn.textContent = slot.start_time + ' – ' + slot.end_time;
          btn.disabled = !slot.available;
          if (!slot.available) btn.style.opacity = '0.4';
          btn.onclick = function() {
            state.time = slot.start_time;
            document.getElementById('rxApptTime').value = slot.start_time + ':00';
            document.querySelectorAll('.rxm-slot').forEach(function(s){ s.classList.remove('active'); });
            btn.classList.add('active');
          };
          grid.appendChild(btn);
        });
      })
      .catch(function() {
        loading.style.display = 'none';
        empty.style.display = 'block';
      });
  }

  /* ── test search ── */

  /* ── open / close ── */
  window.openRxBookingModal = function(button) {
    state.requestId = parseInt(button.getAttribute('data-request-id') || '0', 10);
    state.tests     = JSON.parse(button.getAttribute('data-tests') || '[]');
    state.date      = '';
    state.time      = '';

    document.getElementById('rxFromRequest').value      = state.requestId;
    document.getElementById('rxHomeCollection').value   = button.getAttribute('data-home') || '0';
    document.getElementById('rxCollectionAddr').value   = button.getAttribute('data-address') || '';
    document.getElementById('rxApptDate').value         = '';
    document.getElementById('rxApptTime').value         = '';

    // Default to today and load slots immediately.
    document.querySelectorAll('.rxm-date-btn').forEach(function(b){ b.classList.remove('active'); });
    var todayBtn = document.querySelector('.rxm-date-btn[data-offset="0"]');
    if (todayBtn) {
      todayBtn.classList.add('active');
    }
    document.getElementById('rxDatePicker').style.display = 'none';
    document.getElementById('rxDatePicker').value = '';

    renderTests();
    setDate(todayStr(0));

    var modal = document.getElementById('rxBookingModal');
    modal.hidden = false;
    modal.setAttribute('aria-hidden', 'false');
  };

  window.closeRxBookingModal = function() {
    var modal = document.getElementById('rxBookingModal');
    modal.hidden = true;
    modal.setAttribute('aria-hidden', 'true');
  };

  document.getElementById('rxBookingModal').addEventListener('click', function(e) {
    if (e.target === this) closeRxBookingModal();
  });

  /* ── submit ── */
  window.submitRxBooking = function() {
    if (state.tests.length === 0) { alert('Please select at least one test.'); return; }
    if (!state.date)  { alert('Please select a date.'); return; }
    if (!state.time)  { alert('Please select a time slot.'); return; }

    var formattedTime = state.time.length === 5 ? state.time + ':00' : state.time;

    closeRxBookingModal();

    if (typeof window.openRxPaymentModal === 'function') {
      window.openRxPaymentModal({
        tests:          state.tests,
        date:           state.date,
        time:           formattedTime,
        requestId:      state.requestId,
        homeCollection: document.getElementById('rxHomeCollection').value === '1',
        address:        document.getElementById('rxCollectionAddr').value
      });
    }
  };
})();
</script>

<script>
  function setupSectionTabs() {
    const tabButtons = Array.from(document.querySelectorAll('[data-patient-tab]'));
    const panels = {
      appointments: document.getElementById('patientAppointmentsSection'),
      prescriptions: document.getElementById('patientPrescriptionsSection'),
      results: document.getElementById('patientResultsSection'),
      bills: document.getElementById('patientBillsSection')
    };

    if (!tabButtons.length || !panels.appointments || !panels.prescriptions || !panels.results) {
      return;
    }

    const applyTabState = (nextTab) => {
      const activeTab = Object.prototype.hasOwnProperty.call(panels, nextTab) ? nextTab : 'appointments';

      Object.keys(panels).forEach((key) => {
        const isActive = key === activeTab;
        panels[key].classList.toggle('is-active', isActive);
        panels[key].hidden = !isActive;
      });

      tabButtons.forEach((button) => {
        const isActive = (button.getAttribute('data-patient-tab') || '') === activeTab;
        button.classList.toggle('is-active', isActive);
        button.setAttribute('aria-selected', isActive ? 'true' : 'false');
      });
    };

    tabButtons.forEach((button) => {
      button.addEventListener('click', () => {
        applyTabState(button.getAttribute('data-patient-tab') || 'appointments');
      });
    });

    applyTabState('appointments');
  }

  function setupTablePagination(config) {
    const tableSection = document.querySelector(config.sectionSelector);
    const showingText = document.getElementById(config.showingTextId);
    const paginationNode = document.getElementById(config.paginationId);

    if (!tableSection || !showingText || !paginationNode) {
      return;
    }

    const tableBody = tableSection.querySelector('tbody');
    if (!tableBody) {
      return;
    }

    const allRows = Array.from(tableBody.querySelectorAll('tr'));
    const dataRows = allRows.filter((row) => !row.querySelector('.empty'));
    const emptyRow = allRows.find((row) => row.querySelector('.empty')) || null;
    const pageSize = Number(config.pageSize || 7);
    let currentPage = 1;

    const renderPagination = (totalPages) => {
      if (!dataRows.length || totalPages <= 1) {
        paginationNode.innerHTML = '';
        return;
      }

      const buttons = [];
      buttons.push(
        `<button type="button" class="page-btn" data-page="${currentPage - 1}" ${currentPage === 1 ? 'disabled' : ''} aria-label="Previous page">&#8249;</button>`
      );

      for (let page = 1; page <= totalPages; page += 1) {
        buttons.push(
          `<button type="button" class="page-btn${page === currentPage ? ' is-active' : ''}" data-page="${page}">${page}</button>`
        );
      }

      buttons.push(
        `<button type="button" class="page-btn" data-page="${currentPage + 1}" ${currentPage === totalPages ? 'disabled' : ''} aria-label="Next page">&#8250;</button>`
      );

      paginationNode.innerHTML = buttons.join('');
    };

    const renderPage = (page) => {
      if (!dataRows.length) {
        if (emptyRow) {
          emptyRow.style.display = '';
        }
        showingText.textContent = `Showing 0-0 of 0 ${config.label}`;
        paginationNode.innerHTML = '';
        return;
      }

      const totalItems = dataRows.length;
      const totalPages = Math.max(1, Math.ceil(totalItems / pageSize));
      currentPage = Math.min(Math.max(1, page), totalPages);

      const startIndex = (currentPage - 1) * pageSize;
      const endIndex = Math.min(startIndex + pageSize, totalItems);

      dataRows.forEach((row, index) => {
        row.style.display = index >= startIndex && index < endIndex ? '' : 'none';
      });

      if (emptyRow) {
        emptyRow.style.display = 'none';
      }

      showingText.textContent = `Showing ${startIndex + 1}-${endIndex} of ${totalItems} ${config.label}`;
      renderPagination(totalPages);
    };

    paginationNode.addEventListener('click', (event) => {
      const button = event.target.closest('.page-btn');
      if (!button || button.disabled) {
        return;
      }

      const nextPage = Number(button.getAttribute('data-page') || currentPage);
      renderPage(nextPage);
    });

    renderPage(1);
  }

  function rowDataFromButton(button) {
    const row = button.closest('tr');
    if (!row) return null;

    return {
      id: row.getAttribute('data-id') || '',
      date: row.getAttribute('data-date') || '',
      time: row.getAttribute('data-time') || '',
      tests: row.getAttribute('data-tests') || '',
      status: row.getAttribute('data-status') || '',
      home: row.getAttribute('data-home') || 'No',
      address: row.getAttribute('data-address') || ''
    };
  }

  function patientAppointmentDetailsConfig() {
    var base = (window.LAB_SYNC_CONFIG && window.LAB_SYNC_CONFIG.baseUrl)
      ? String(window.LAB_SYNC_CONFIG.baseUrl).replace(/\/$/, '')
      : '/lab_sync';

    return {
      endpoint: base + '/index.php?controller=home&action=getAppointmentDetails'
    };
  }

  function openPatientDetailsModal() {
    const modal = document.getElementById('appointmentDetailsModal');
    if (!modal) {
      return;
    }

    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  function closePatientDetailsModal() {
    const modal = document.getElementById('appointmentDetailsModal');
    const body = document.getElementById('appointmentDetailsBody');
    if (!modal) {
      return;
    }

    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    if (body) {
      body.innerHTML = '';
    }
    document.body.style.overflow = '';
  }

  function openView(button) {
    const row = rowDataFromButton(button);
    if (!row || !row.id) {
      return;
    }

    const body = document.getElementById('appointmentDetailsBody');
    if (!body) {
      return;
    }

    body.innerHTML = '<div class="appointment-details-loading"><div class="spinner" aria-hidden="true"></div><p>Loading appointment details...</p></div>';
    openPatientDetailsModal();

    const config = patientAppointmentDetailsConfig();
    fetch(config.endpoint + '&appointment_id=' + encodeURIComponent(row.id), {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
      .then(function (response) {
        if (!response.ok) {
          return response.text().then(function (text) {
            throw new Error(text || ('Request failed with status ' + response.status));
          });
        }
        return response.text();
      })
      .then(function (html) {
        if (!html || !html.trim()) {
          throw new Error('The server returned an empty response.');
        }
        body.innerHTML = html;
      })
      .catch(function (error) {
        body.innerHTML = '<div class="appointment-details-error-state"><h3>Unable to load details</h3><p>' +
          String(error && error.message ? error.message : 'A network error occurred.') +
          '</p></div>';
      });
  }

  function openModal(id) {
    const modal = document.getElementById(id);
    if (!modal) {
      return;
    }

    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
  }

  function closeModal(id) {
    const modal = document.getElementById(id);
    if (!modal) {
      return;
    }

    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
  }

  // Slot list is now dynamic — fetched from the server per date

  function formatAppointmentNumber(value) {
    const raw = String(value || '').trim();
    return raw.startsWith('APP-') ? raw : `APP-${raw}`;
  }

  function normalizePatientTime(value) {
    const raw = String(value || '').trim().toUpperCase();
    if (!raw) {
      return '';
    }

    if (/^\d{2}:\d{2}:\d{2}$/.test(raw)) {
      return raw.slice(0, 5);
    }

    if (/^\d{2}:\d{2}$/.test(raw)) {
      return raw;
    }

    return raw;
  }

  function formatDisplayTime(value) {
    const normalized = normalizePatientTime(value);
    const match = normalized.match(/^(\d{2}):(\d{2})$/);
    if (!match) {
      return value || '';
    }

    const hour = Number(match[1]);
    const minute = match[2];
    return `${hour % 12 || 12}:${minute} ${hour >= 12 ? 'PM' : 'AM'}`;
  }

  function setPatientEditAlert(message) {
    const alertNode = document.getElementById('patientEditAppointmentAlert');
    if (!alertNode) {
      return;
    }

    alertNode.textContent = message || '';
    alertNode.hidden = !message;
  }

  function setPatientEditModalOpen(open) {
    const modal = document.getElementById('patientEditAppointmentModal');
    if (!modal) {
      return;
    }

    modal.classList.toggle('is-open', open);
    modal.setAttribute('aria-hidden', open ? 'false' : 'true');
    document.body.style.overflow = open ? 'hidden' : '';
    if (!open) {
      setPatientEditAlert('');
    }
  }

  function renderPatientEditTimeSlots(selectedTime) {
    const host = document.getElementById('patientEditTimeSlots');
    if (!host) return;

    const date = document.getElementById('patientEditDateInput').value;
    const normalizedSelected = normalizePatientTime(selectedTime);

    host.innerHTML = '<span style="font-size:12px;color:#8b9ab0;">Loading slots...</span>';

    if (!date) {
      host.innerHTML = '';
      return;
    }

    fetch('/lab_sync/index.php?controller=home&action=getAvailableSlots&date=' + encodeURIComponent(date))
      .then(r => r.json())
      .then(slots => {
        host.innerHTML = '';
        if (!Array.isArray(slots) || slots.length === 0) {
          host.innerHTML = '<span style="font-size:12px;color:#8b9ab0;">No slots available for this day.</span>';
          const hiddenInput = document.getElementById('patientEditTimeInput');
          if (hiddenInput) hiddenInput.value = '';
          return;
        }

        const now = new Date();
        const today = now.toISOString().slice(0, 10);
        const nowMinutes = now.getHours() * 60 + now.getMinutes();
        const visibleSlots = slots.filter(slot => {
          if (date !== today) return true;
          const pieces = String(slot.start_time || '').split(':');
          if (pieces.length < 2) return false;
          const slotMinutes = Number(pieces[0]) * 60 + Number(pieces[1]);
          return slotMinutes > nowMinutes;
        });

        if (visibleSlots.length === 0) {
          host.innerHTML = '<span style="font-size:12px;color:#8b9ab0;">No future slots are available for this day.</span>';
          const hiddenInput = document.getElementById('patientEditTimeInput');
          if (hiddenInput) hiddenInput.value = '';
          return;
        }

        let hasSelected = false;
        visibleSlots.forEach(slot => {
          const button = document.createElement('button');
          button.type = 'button';
          button.className = 'time-slot' + (normalizedSelected === slot.start_time ? ' is-selected' : '');
          button.textContent = slot.start_time + ' – ' + slot.end_time;
          button.setAttribute('data-time', slot.start_time);
          button.disabled = !slot.available;
          button.addEventListener('click', () => {
            setPatientEditSelectedTime(slot.start_time);
          });
          host.appendChild(button);

          if (normalizedSelected === slot.start_time && slot.available) {
            hasSelected = true;
          }
        });

        if (!hasSelected) {
          const hiddenInput = document.getElementById('patientEditTimeInput');
          if (hiddenInput) hiddenInput.value = '';
        }
      })
      .catch(() => {
        host.innerHTML = '<span style="font-size:12px;color:#c00;">Failed to load slots.</span>';
        const hiddenInput = document.getElementById('patientEditTimeInput');
        if (hiddenInput) hiddenInput.value = '';
      });
  }

  function setPatientEditSelectedTime(timeValue) {
    const normalized = normalizePatientTime(timeValue);
    const hiddenInput = document.getElementById('patientEditTimeInput');

    if (hiddenInput) {
      hiddenInput.value = normalized;
    }

    renderPatientEditTimeSlots(normalized);
  }

  function togglePatientEditHomeCollection() {
    const checkbox = document.getElementById('patientEditHomeCollection');
    const addressRow = document.getElementById('patientEditAddressRow');
    const addressInput = document.getElementById('patientEditAddressInput');

    if (!checkbox || !addressRow || !addressInput) {
      return;
    }

    if (checkbox.checked) {
      addressRow.style.display = 'block';
      addressInput.required = true;
    } else {
      addressRow.style.display = 'none';
      addressInput.required = false;
      addressInput.value = '';
    }
  }

  function openEdit(button) {
    const row = rowDataFromButton(button);
    if (!row) return;

    if (String(row.status || '').toLowerCase() === 'cancelled') {
      window.alert('Cancelled appointments cannot be edited.');
      return;
    }

    document.getElementById('patientEditAppointmentId').value = row.id;
    document.getElementById('patientEditAppointmentTitle').textContent = `Edit Appointment #${formatAppointmentNumber(row.id)}`;
    document.getElementById('patientEditSummaryTitle').textContent = `Appointment #${formatAppointmentNumber(row.id)}`;
    document.getElementById('patientEditSummaryTests').textContent = row.tests || 'No tests selected';
    document.getElementById('patientEditSummaryStatus').textContent = row.status || 'Pending';
    document.getElementById('patientEditDateInput').value = row.date;
    document.getElementById('patientEditAddressInput').value = row.address || '';
    document.getElementById('patientEditHomeCollection').checked = row.home === 'Yes';
    togglePatientEditHomeCollection();
    setPatientEditSelectedTime(row.time);
    setPatientEditAlert('');
    setPatientEditModalOpen(true);
  }

  document.addEventListener('DOMContentLoaded', function () {
    setupSectionTabs();
    setupTablePagination({
      sectionSelector: 'main .table-card:first-of-type',
      showingTextId: 'appointmentsShowingText',
      paginationId: 'appointmentsPagination',
      label: 'appointments',
      pageSize: 7
    });
    setupTablePagination({
      sectionSelector: '#prescription-submissions',
      showingTextId: 'prescriptionsShowingText',
      paginationId: 'prescriptionsPagination',
      label: 'requests',
      pageSize: 7
    });
    setupTablePagination({
      sectionSelector: '#patientBillsSection .table-card',
      showingTextId: 'billsShowingText',
      paginationId: 'billsPagination',
      label: 'bills',
      pageSize: 7
    });

    const patientEditClose = document.getElementById('patientEditAppointmentClose');
    const patientEditCancel = document.getElementById('patientEditAppointmentCancel');
    const patientEditModal = document.getElementById('patientEditAppointmentModal');
    const patientEditHomeCollection = document.getElementById('patientEditHomeCollection');
    const patientEditForm = document.getElementById('patientEditAppointmentForm');

    patientEditClose && patientEditClose.addEventListener('click', () => setPatientEditModalOpen(false));
    patientEditCancel && patientEditCancel.addEventListener('click', () => setPatientEditModalOpen(false));
    patientEditHomeCollection && patientEditHomeCollection.addEventListener('change', togglePatientEditHomeCollection);

    const patientEditDateInput = document.getElementById('patientEditDateInput');
    patientEditDateInput && patientEditDateInput.addEventListener('change', function () {
      document.getElementById('patientEditTimeInput').value = '';
      renderPatientEditTimeSlots('');
    });

    patientEditForm && patientEditForm.addEventListener('submit', function (event) {
      const selectedTime = normalizePatientTime(document.getElementById('patientEditTimeInput').value);
      const homeCollection = document.getElementById('patientEditHomeCollection').checked;
      const addressValue = document.getElementById('patientEditAddressInput').value.trim();

      if (!selectedTime) {
        event.preventDefault();
        setPatientEditAlert('Please choose an available time slot.');
        return;
      }

      if (homeCollection && addressValue === '') {
        event.preventDefault();
        setPatientEditAlert('Please provide a collection address for home sample collection.');
        return;
      }
    });

    patientEditModal && patientEditModal.addEventListener('click', function (event) {
      if (event.target === patientEditModal) {
        setPatientEditModalOpen(false);
      }
    });

    const detailsModal = document.getElementById('appointmentDetailsModal');
    const detailsClose = document.getElementById('appointmentDetailsClose');

    detailsClose && detailsClose.addEventListener('click', closePatientDetailsModal);
    detailsModal && detailsModal.addEventListener('click', function (event) {
      if (event.target === detailsModal) {
        closePatientDetailsModal();
      }
    });
  });

  document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') {
      return;
    }

    const patientEditModal = document.getElementById('patientEditAppointmentModal');
    const detailsModal = document.getElementById('appointmentDetailsModal');

    if (patientEditModal && patientEditModal.classList.contains('is-open')) {
      setPatientEditModalOpen(false);
    }

    if (detailsModal && detailsModal.classList.contains('is-open')) {
      closePatientDetailsModal();
    }
  });

  document.addEventListener('click', function (event) {
    const modal = event.target.closest('.modal');
    if (!modal) {
      return;
    }

    if (event.target === modal) {
      modal.classList.remove('is-open');
      modal.setAttribute('aria-hidden', 'true');
    }
  });
</script>
<?php require __DIR__ . '/../../../public/partials/footer.php'; ?>

<div id="rxPaymentModal" class="payment-modal" aria-hidden="true">
  <div class="payment-dialog">
    <div class="payment-header">
      <span class="payment-icon-wrap">💳</span>
      <h2>Complete Payment</h2>
      <button id="rxBtnCancelPayment" class="payment-close" aria-label="Close">&times;</button>
    </div>
    <div class="pm-divider"></div>
    <div class="pm-section-label">Order Summary</div>
    <div id="rxPmOrderLines" class="pm-order-lines"></div>
    <div class="pm-total-row">
      <span class="pm-total-label">Total</span>
      <strong id="rxPmTotal" class="pm-total-val">LKR 0.00</strong>
    </div>
    <div id="rxPmError" class="pm-error"></div>
    <button id="rxBtnPayNow" class="btn-pay-now">Pay Now via Payhere</button>
    <button id="rxBtnCancelPaymentBottom" class="btn-cancel-payment">Cancel</button>
    <div class="pm-secure-note">🔒 Secured by Payhere</div>
    <div id="rxPmSpinnerOverlay" class="pm-spinner-overlay">
      <div class="pm-spinner"></div>
    </div>
  </div>
</div>

<script>
window.LAB_SYNC_RX_CONFIG = {
    baseUrl:   '/lab_sync',
    csrfToken: '<?php echo htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8'); ?>'
};
</script>
<script src="https://www.payhere.lk/lib/payhere.js"></script>
<script src="/lab_sync/public/js/rxPaymentModal.js"></script>
<script src="/lab_sync/public/js/showAlert.js"></script>
</body>
</html>
