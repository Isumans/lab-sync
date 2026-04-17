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
  <style>
    :root {
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
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      margin-bottom: 18px;
    }

    .page-head h1 {
      margin: 0;
      font-size: clamp(1.45rem, 2.3vw, 1.85rem);
      color: #1c2736;
    }

    .book-link {
      text-decoration: none;
      background: var(--primary-500);
      color: #fff;
      border-radius: 10px;
      padding: 10px 14px;
      font-weight: 700;
      font-size: 0.92rem;
      transition: background-color .2s ease;
    }

    .book-link:hover {
      background: var(--primary-600);
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
      padding: 0 18px 18px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      min-width: 780px;
    }

    th,
    td {
      text-align: left;
      padding: 14px 10px;
      border-bottom: 1px solid #e8edf3;
      vertical-align: middle;
      font-size: 0.94rem;
    }

    th {
      color: #526072;
      font-size: 0.82rem;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      font-weight: 700;
    }

    .status-pill {
      display: inline-flex;
      align-items: center;
      border-radius: 999px;
      padding: 5px 10px;
      font-weight: 700;
      font-size: 0.78rem;
      background: #eef4fa;
      color: #35526e;
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
      justify-content: flex-start;
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
      padding: 5px 10px;
      font-weight: 700;
      font-size: 0.78rem;
      background: #eef4fa;
      color: #35526e;
      text-transform: capitalize;
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
      .dashboard-wrap { padding: 18px 14px 34px; }
      .action-btn { width: 56px; height: 56px; }
      .action-btn svg { width: 22px; height: 22px; }
    }
  </style>
</head>
<body>
<?php require_once __DIR__ . '/../../../public/partials/header.php'; ?>

<main class="dashboard-wrap">
  <div class="page-head">
    <h1>Your Appointments</h1>
    <a class="book-link" href="/lab_sync/index.php?controller=home&action=appointment_options">Book New Appointment</a>
  </div>

  <section class="card">
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
            <th>Date</th>
            <th>Time</th>
            <th>Tests</th>
            <th>Status</th>
            <th>Home Collection</th>
            <th>Actions</th>
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
                <td><?php echo htmlspecialchars($date); ?></td>
                <td><?php echo htmlspecialchars($time); ?></td>
                <td><?php echo htmlspecialchars($testsSummary); ?></td>
                <td><span class="status-pill <?php echo htmlspecialchars($statusClass); ?>"><?php echo htmlspecialchars($status); ?></span></td>
                <td><span class="home-badge <?php echo $homeCollection ? '' : 'no'; ?>"><?php echo $homeCollection ? 'Yes' : 'No'; ?></span></td>
                <td>
                  <div class="actions">
                    <button type="button" class="action-btn edit" title="Edit" onclick="openEdit(this)">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"></path></svg>
                    </button>
                    <button type="button" class="action-btn delete" title="Delete" onclick="openDelete(this)">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path></svg>
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
  </section>

  <section class="card rx-card" id="prescription-submissions">
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
            <th>Actions</th>
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
                <td><?php echo htmlspecialchars($createdAt); ?></td>
                <td><?php echo htmlspecialchars($typeLabel); ?></td>
                <td><span class="rx-pill <?php echo htmlspecialchars($status); ?>"><?php echo htmlspecialchars($statusLabel); ?></span></td>
                <td>
                  <?php if ($fileUrl !== ''): ?>
                    <a class="file-link" href="<?php echo htmlspecialchars($fileUrl); ?>" target="_blank" rel="noopener">View File</a>
                  <?php else: ?>
                    N/A
                  <?php endif; ?>
                </td>
                <td>
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
  </section>

  <section class="card results-card">
    <div class="card-head" style="padding: 0 0 12px; border-bottom: 0;">
      <h2>Test Results</h2>
    </div>
    <p class="results-note">Front-end preview section for upcoming backend integration. Your teammate can replace this mock data with real result records.</p>
    <div class="results-grid">
      <article class="result-item">
        <h3>Complete Blood Count (CBC)</h3>
        <p class="result-meta">Requested Date: 2026-04-02<br>Status: Awaiting Lab Upload<br>Report: Not Available Yet</p>
      </article>
      <article class="result-item">
        <h3>Lipid Profile</h3>
        <p class="result-meta">Requested Date: 2026-04-05<br>Status: Processing<br>Report: Not Available Yet</p>
      </article>
      <article class="result-item">
        <h3>Vitamin D</h3>
        <p class="result-meta">Requested Date: 2026-04-09<br>Status: Ready for Delivery<br>Report Button: Disabled (Backend Pending)</p>
      </article>
    </div>
  </section>
</main>

<div class="modal" id="viewModal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-head">
      <h3>Appointment Details</h3>
      <button class="modal-close" type="button" onclick="closeModal('viewModal')">&times;</button>
    </div>
    <div class="modal-body">
      <div class="modal-row"><label>Date</label><p id="viewDate">-</p></div>
      <div class="modal-row"><label>Time</label><p id="viewTime">-</p></div>
      <div class="modal-row"><label>Tests</label><p id="viewTests">-</p></div>
      <div class="modal-row"><label>Status</label><p id="viewStatus">-</p></div>
      <div class="modal-row"><label>Home Collection</label><p id="viewHome">-</p></div>
      <div class="modal-actions">
        <button type="button" class="btn secondary" onclick="closeModal('viewModal')">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="editModal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-head">
      <h3>Edit Appointment</h3>
      <button class="modal-close" type="button" onclick="closeModal('editModal')">&times;</button>
    </div>
    <div class="modal-body">
      <form method="POST" action="/lab_sync/index.php?controller=home&action=edit_appointment">
        <input type="hidden" name="appointment_id" id="editAppointmentId" value="">
        <div class="modal-row">
          <label for="editDateInput">Date</label>
          <input id="editDateInput" name="date" type="date" required>
        </div>
        <div class="modal-row">
          <label for="editTimeInput">Time</label>
          <input id="editTimeInput" name="time" type="time" required>
        </div>
        <div class="modal-row">
          <label>Home Collection</label>
          <div class="checkbox-row">
            <input id="editHomeCollection" name="home_collection" type="checkbox" value="1" onchange="toggleHomeCollectionEdit()">
            <span>Require a home visit for this appointment</span>
          </div>
        </div>
        <div class="modal-row" id="editAddressRow">
          <label for="editAddressInput">Collection Address</label>
          <textarea id="editAddressInput" name="collection_address" rows="3" placeholder="Enter the sample collection address"></textarea>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn secondary" onclick="closeModal('editModal')">Cancel</button>
          <button type="submit" name="edit" class="btn primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal" id="deleteModal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-head">
      <h3>Delete Appointment</h3>
      <button class="modal-close" type="button" onclick="closeModal('deleteModal')">&times;</button>
    </div>
    <div class="modal-body">
      <p style="margin-top:0; color:#5c6b7d;">Are you sure you want to delete this appointment?</p>
      <div class="modal-row"><label>Appointment Date</label><p id="deleteDate">-</p></div>
      <div class="modal-row"><label>Appointment Time</label><p id="deleteTime">-</p></div>
      <form method="POST" action="/lab_sync/index.php?controller=home&action=edit_appointment">
        <input type="hidden" name="appointment_id" id="deleteAppointmentId" value="">
        <div class="modal-actions">
          <button type="button" class="btn secondary" onclick="closeModal('deleteModal')">Cancel</button>
          <button type="submit" name="delete" class="btn danger">Delete</button>
        </div>
      </form>
    </div>
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
            <div class="rxm-slot-label">Morning</div>
            <div id="rxSlotMorning" class="rxm-slot-rail"></div>
            <div class="rxm-slot-label">Afternoon</div>
            <div id="rxSlotAfternoon" class="rxm-slot-rail"></div>
          </div>
        </section>

        <!-- Footer -->
        <div class="rxm-footer">
          <div class="rxm-total-block">
            <span class="rxm-total-label">GRAND TOTAL</span>
            <span id="rxGrandTotal" class="rxm-total-val">LKR 0.00</span>
          </div>
          <button type="button" class="rxm-confirm-btn" id="rxConfirmBtn" onclick="submitRxBooking()">
            Confirm Appointment &#8594;
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

  var slotsMorning   = ['08:00','08:30','09:00','09:30','10:00','10:30','11:00'];
  var slotsAfternoon = ['13:00','13:30','14:00','14:30','15:00','15:30'];

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
    renderSlotGroup('rxSlotMorning',   slotsMorning);
    renderSlotGroup('rxSlotAfternoon', slotsAfternoon);
  }

  function renderSlotGroup(containerId, times) {
    var host = document.getElementById(containerId);
    host.innerHTML = '';
    times.forEach(function(t) {
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'rxm-slot' + (state.time === t ? ' active' : '');
      btn.textContent = fmtTime(t);
      btn.disabled = !state.date;
      btn.onclick = function() {
        state.time = t;
        document.getElementById('rxApptTime').value = t + ':00';
        document.querySelectorAll('.rxm-slot').forEach(function(s){ s.classList.remove('active'); });
        btn.classList.add('active');
      };
      host.appendChild(btn);
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

    // Clear active date button selection
    document.querySelectorAll('.rxm-date-btn').forEach(function(b){ b.classList.remove('active'); });
    document.getElementById('rxDatePicker').style.display = 'none';
    document.getElementById('rxDatePicker').value = '';

    renderTests();
    renderSlots();

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

    // Inject test_ids as hidden inputs
    var form = document.getElementById('rxBookingForm');
    form.querySelectorAll('.rxm-hidden-test').forEach(function(el){ el.remove(); });
    state.tests.forEach(function(t) {
      var inp = document.createElement('input');
      inp.type = 'hidden'; inp.name = 'test_ids[]'; inp.value = t.test_id;
      inp.className = 'rxm-hidden-test';
      form.appendChild(inp);
    });

    if (confirm('Confirm appointment booking?')) {
      form.submit();
    }
  };
})();
</script>

<script>
  function rowDataFromButton(button) {
    const row = button.closest('tr');
    if (!row) return null;

    return {
      id: row.getAttribute('data-id') || '',
      date: row.getAttribute('data-date') || '',
      time: row.getAttribute('data-time') || '',
      tests: row.getAttribute('data-tests') || '',
      status: row.getAttribute('data-status') || '',
      home: row.getAttribute('data-home') || 'No'
    };
  }

  function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
      modal.classList.add('is-open');
      modal.setAttribute('aria-hidden', 'false');
    }
  }

  function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
      modal.classList.remove('is-open');
      modal.setAttribute('aria-hidden', 'true');
    }
  }

  function openEdit(button) {
    const row = rowDataFromButton(button);
    if (!row) return;

    document.getElementById('editAppointmentId').value = row.id;
    document.getElementById('editDateInput').value = row.date;
    document.getElementById('editTimeInput').value = row.time;
    document.getElementById('editHomeCollection').checked = row.home === 'Yes';
    document.getElementById('editAddressInput').value = button.closest('tr').getAttribute('data-address') || '';
    toggleHomeCollectionEdit();
    openModal('editModal');
  }

  function openDelete(button) {
    const row = rowDataFromButton(button);
    if (!row) return;

    document.getElementById('deleteAppointmentId').value = row.id;
    document.getElementById('deleteDate').textContent = row.date;
    document.getElementById('deleteTime').textContent = row.time;
    openModal('deleteModal');
  }

  function toggleHomeCollectionEdit() {
    const checkbox = document.getElementById('editHomeCollection');
    const addressRow = document.getElementById('editAddressRow');
    const addressInput = document.getElementById('editAddressInput');

    if (!checkbox || !addressRow || !addressInput) return;

    if (checkbox.checked) {
      addressRow.style.display = 'block';
      addressInput.required = true;
    } else {
      addressRow.style.display = 'none';
      addressInput.required = false;
      addressInput.value = '';
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    toggleHomeCollectionEdit();
  });

  document.addEventListener('click', function (event) {
    const modal = event.target.closest('.modal');
    if (!modal) return;

    if (event.target === modal) {
      modal.classList.remove('is-open');
      modal.setAttribute('aria-hidden', 'true');
    }
  });
</script>
<?php require __DIR__ . '/../../../public/partials/footer.php'; ?>
<script src="/lab_sync/public/js/showAlert.js"></script>
</body>
</html>
