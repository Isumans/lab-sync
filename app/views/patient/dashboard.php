<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$appointments = is_array($appointments ?? null) ? $appointments : [];
$prescriptionRequests = is_array($prescriptionRequests ?? null) ? $prescriptionRequests : [];
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
            <th>Status</th>
            <th>File</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($prescriptionRequests) > 0): ?>
            <?php foreach ($prescriptionRequests as $request): ?>
              <?php
                $createdAt = (string)($request['created_at'] ?? 'N/A');
                $status = strtolower(trim((string)($request['status'] ?? 'pending')));
                $statusLabel = ucfirst($status);
                $filePath = ltrim(str_replace('\\\\', '/', (string)($request['prescription_file_path'] ?? '')), '/');
                $fileUrl = $filePath !== '' ? '/lab_sync/' . $filePath : '';
              ?>
              <tr>
                <td><?php echo htmlspecialchars($createdAt); ?></td>
                <td><span class="rx-pill <?php echo htmlspecialchars($status); ?>"><?php echo htmlspecialchars($statusLabel); ?></span></td>
                <td>
                  <?php if ($fileUrl !== ''): ?>
                    <a class="file-link" href="<?php echo htmlspecialchars($fileUrl); ?>" target="_blank" rel="noopener">View File</a>
                  <?php else: ?>
                    N/A
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="3" class="empty">No prescription submissions yet. Submit one from Help section and it will appear here.</td>
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

<?php require_once __DIR__ . '/../../../public/partials/footer.php'; ?>

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
