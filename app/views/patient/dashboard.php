<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>LabSync - Home</title>
  <link rel="stylesheet" href="/lab_sync/public/css/patient.css" />
  <link rel="stylesheet" href="/lab_sync/public/css/nav.css" />
  <link rel="stylesheet" href="/lab_sync/public/table.css" />
</head>
<body>
  <?php require 'C:\xampp\htdocs\lab_sync\public\partials\header.php'; ?>
<main class="container">
  <h2 class="page-title">Good morning, John!</h2>

  <div class="dash-grid">
    <div class="card">
      <div class="card-head">
        <h3>Next Appointment</h3>
        <span id="nextStatus" class="pill pill-pending">Pending</span>
      </div>
      <p id="nextInfo" class="muted">No appointment yet.</p>
      <div class="row">
        <button class="btn-outline" onclick="openRescheduleFromNext()">Reschedule</button>
        <button class="btn-outline" onclick="cancelFromNext()">Cancel</button>
        <a class="btn-ghost" target="_blank" href="https://maps.google.com">Directions</a>
      </div>
    </div>

    <!-- <aside class="card">
      <h3>Quick Actions</h3>
      <div class="list">
        <a class="btn-primary" href="/explore.php">Book Test</a>
        <a class="btn-outline" href="/results.php">View Results</a>
      </div>
    </aside> -->
  </div>

  <div class="card" style="margin-top:16px">
    <div class="toolbar">
      <h3>Your Appointments</h3>
    </div>
    <table class="test-catalog-table">
        <thead>
          <tr>
            <th>Appointment ID</th>
            <th>Test Id</th>
            <th>Time</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (is_array($appointments)): ?>
          <?php foreach ($appointments as $appointment): ?>
            <form method="POST" action="/lab_sync/index.php?controller=home&action=edit_appointment" class="editForm">
              <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars($appointment['appointment_id']); ?>">
              <tr>
                <td><?php echo htmlspecialchars($appointment['appointment_id']); ?></td>
                <td><?php echo htmlspecialchars($appointment['test_id']); ?></td>
                <td><input class="form1" name="time" type="time" value="<?php echo htmlspecialchars($appointment['appointment_time']); ?>"></td>
                <td><input class="form1" name="date" type="date" value="<?php echo htmlspecialchars($appointment['appointment_date']); ?>"></td>

                <td>
                  <button type="submit" name="edit" onclick="return showAlertAndSubmit(event, 'edit')">
                    <img src="/lab_sync/public/assests/edit.png" alt="Edit">
                  </button>
                  <button type="submit" name="delete" onclick="return showAlertAndSubmit(event, 'delete')">
                    <img src="/lab_sync/public/assests/delete.png" alt="Delete">
                  </button>
                </td>
              </tr>
            </form>
            <?php endforeach; ?>
          <?php else: ?>
        <tr><td colspan="5">No tests found or database error.</td></tr>
      <?php endif; ?>
                        
      </tbody>
    </table>
</div>
</main>

<!-- Edit/Reschedule Modal -->
<div id="editModal" class="modal">
  <div class="modal-sheet small">
    <div class="modal-head">
      <h3>Edit / Reschedule</h3>
      <button class="modal-x" onclick="closeEdit()">×</button>
    </div>
    <div class="modal-body">
      <label class="label">New Date</label>
      <input id="editDate" type="date" class="input">
      <label class="label" style="margin-top:8px">New Time</label>
      <select id="editTime" class="input">
        <option>08:00</option><option>08:30</option><option>09:00</option><option>09:30</option>
        <option>10:00</option><option>10:30</option><option>11:00</option>
        <option>13:00</option><option>13:30</option><option>14:00</option><option>14:30</option><option>15:00</option>
      </select>
    </div>
    <div class="modal-foot">
      <button class="btn-outline" onclick="closeEdit()">Close</button>
      <button class="btn-primary" onclick="saveEdit()">Save</button>
    </div>
  </div>
</div>

<script>
// Remove localStorage usage and modify renderAppointments to work with PHP data
function renderAppointments() {
    // Get the appointments from PHP-rendered table
    const appointments = <?php echo json_encode($appointments ?? []); ?>;
    
    // Update next appointment card
    const nextAppointment = appointments[0];
    const nextInfo = document.getElementById('nextInfo');
    const nextStatus = document.getElementById('nextStatus');
    
    if (nextAppointment) {
        nextInfo.textContent = `Test ID: ${nextAppointment.test_id} — ${nextAppointment.appointment_date} at ${nextAppointment.appointment_time}`;
        nextStatus.textContent = nextAppointment.status || 'Pending';
        nextStatus.className = 'pill ' + (nextAppointment.status === 'Confirmed' ? 'pill-ok' : 'pill-pending');
    } else {
        nextInfo.textContent = 'No appointment yet.';
        nextStatus.textContent = 'Pending';
        nextStatus.className = 'pill pill-pending';
    }
}

// Simplified modal functions
function openEdit(id) {
    document.getElementById('editModal').classList.add('open');
}

function closeEdit() {
    document.getElementById('editModal').classList.remove('open');
}

function showAlertAndSubmit(event, action) {
    event.preventDefault();
    const message = action === 'delete' ? 'Delete this appointment?' : 'Update this appointment?';
    if (confirm(message)) {
        event.target.closest('form').submit();
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', renderAppointments);
</script>
<?php require 'C:\xampp\htdocs\lab_sync\public\partials\footer.php'; ?>
<script src="/lab_sync/public/js/showAlert.js"></script>
</body>
</html>
