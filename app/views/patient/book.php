<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>LabSync - Home</title>
  <link rel="stylesheet" href="/lab_sync/public/css/patient.css" />
</head>
<body>
  <?php require 'C:\xampp\htdocs\lab_sync\public\partials\header.php'; ?>
<main class="book-wrap">
  <header class="book-head">
    <div>
      <h1 class="book-title">Book a Test</h1>
      <p class="book-sub">Choose your test, pick a date and time, then confirm — it takes under a minute.</p>
    </div>

    <!-- simple visual stepper (no JS) -->
    <ol class="book-steps">
      <li class="done">Test</li>
      <li>Date &amp; Time</li>
      <li>Confirm</li>
    </ol>
  </header>

  <section class="book-layout">
    <!-- LEFT: compact form -->
    <div class="card book-card">
      <div class="form-grid">
        <div class="form-field">
          <label class="label">Test / Package</label>
          <select id="test" class="input input-lg" onchange="onTestChange()">
            <?php
              $tests = ['Full Blood Count (FBC)','Lipid Profile','Fasting Blood Sugar (FBS)','Thyroid Panel (TSH/T3/T4)','Liver Function Test (LFT)','Kidney Function Test (KFT)','HbA1c'];
              $pre = isset($_GET['test']) ? $_GET['test'] : 'Full Blood Count (FBC)';
              foreach ($tests as $t) {
                $sel = ($t === $pre) ? 'selected' : '';
                echo "<option $sel>" . htmlspecialchars($t) . "</option>";
              }
            ?>
          </select>
        </div>

        <div class="form-field">
          <label class="label">Date</label>
          <div class="date-wrap">
            <input id="date" type="date" class="input input-lg" />
            <span class="date-ico" aria-hidden="true">📅</span>
          </div>
        </div>
      </div>

      <!-- Slots (now compact + can scroll horizontally on narrower widths) -->
      <div class="slot-cluster">

        <div class="slot-group">
          <div class="slot-label"><span class="chip chip-soft">Morning</span></div>
          <div id="gridMorning" class="slot-rail"></div>
        </div>

        <div class="slot-group">
          <div class="slot-label"><span class="chip chip-soft">Afternoon</span></div>
          <div id="gridAfternoon" class="slot-rail"></div>
        </div>

      </div>

      <!-- Dynamic prep note -->
      <div id="prep" class="info-strip">Select a test to see preparation notes.</div>
    </div>

    <!-- RIGHT: premium summary -->
    <aside class="card summary sticky">
      <div class="sum-head">
        <div class="sum-ico">🗂️</div>
        <div>
          <h3 class="sum-title">Summary</h3>
          <div class="sum-sub">Review and confirm your booking</div>
        </div>
      </div>

      <div class="kv">
        <span class="k">Test</span>
        <span id="sumTest" class="v">—</span>
      </div>
      <div class="kv">
        <span class="k">Date</span>
        <span id="sumDate" class="v">—</span>
      </div>
      <div class="kv">
        <span class="k">Time</span>
        <span id="sumTime" class="v">—</span>
      </div>

      <div class="hint">
        Please arrive 10 minutes before your scheduled time.
      </div>

      <div class="sum-actions">
        <button class="btn-primary btn-lg" onclick="confirmBooking()">Confirm Booking</button>
        <button class="btn-outline btn-lg" onclick="history.back()">Back</button>
      </div>

      <div class="sum-footnote">
        You can reschedule or cancel anytime from your Dashboard.
      </div>
    </aside>
  </section>
</main>



<script>
// slots
const slotsMorning = ['08:00','08:30','09:00','09:30','10:00','10:30','11:00'];
const slotsAfternoon = ['13:00','13:30','14:00','14:30','15:00','15:30'];
let selectedSlot = null;

function makeSlots(hostId, times){
  const host = document.getElementById(hostId);
  host.innerHTML = '';
  times.forEach(t=>{
    const b = document.createElement('button');
    b.className = 'slot';
    b.textContent = t;
    b.onclick = ()=>{
      document.querySelectorAll('.slot').forEach(s=>s.classList.remove('active'));
      b.classList.add('active');
      selectedSlot = t;
      updateSummary();
    };
    host.appendChild(b);
  });
}

function renderSlots(){ makeSlots('gridMorning', slotsMorning); makeSlots('gridAfternoon', slotsAfternoon); }

const prep = {
  'Full Blood Count (FBC)' : 'No special preparation required.',
  'Lipid Profile'          : '12-hour fasting required. Water allowed.',
  'Fasting Blood Sugar (FBS)' : '8–12 hour fasting preferred.',
  'Thyroid Panel (TSH/T3/T4)' : 'Avoid biotin 48 hours before sample if applicable.',
  'Liver Function Test (LFT)' : 'Avoid alcohol for 24 hours prior.',
  'Kidney Function Test (KFT)': 'Stay hydrated unless told otherwise.',
  'HbA1c' : 'No fasting required.'
};

function onTestChange(){
  const t = document.getElementById('test').value;
  document.getElementById('prep').textContent = prep[t] || '';
  updateSummary();
}
function updateSummary(){
  document.getElementById('sumTest').textContent = document.getElementById('test').value;
  document.getElementById('sumDate').textContent = document.getElementById('date').value || '—';
  document.getElementById('sumTime').textContent = selectedSlot || '—';
}

// UI-only: save to localStorage and go to dashboard
function confirmBooking(){
  const t = document.getElementById('test').value;
  const d = document.getElementById('date').value;
  const tm = selectedSlot;
  if(!t || !d || !tm){ alert('Please select test, date and time.'); return; }
  const key = 'labsync_appointments';
  const list = JSON.parse(localStorage.getItem(key) || '[]');
  list.push({id:'a'+Date.now(), test:t, date:d, time:tm, status:'Pending'});
  localStorage.setItem(key, JSON.stringify(list));
  location.href = '/dashboard.php';
}

renderSlots(); onTestChange();
document.getElementById('date').addEventListener('input', updateSummary);
</script>
<?php require 'C:\xampp\htdocs\lab_sync\public\partials\footer.php'; ?>
</body>
</html>
