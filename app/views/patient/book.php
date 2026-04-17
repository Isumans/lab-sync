<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>LabSync - Book a Test</title>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/lab_sync/public/css/globals.css" />
  <link rel="stylesheet" href="/lab_sync/public/css/nav.css" />
  <link rel="stylesheet" href="/lab_sync/public/css/footer.css" />
  <link rel="stylesheet" href="/lab_sync/public/book.css" />
  <link rel="stylesheet" href="/lab_sync/public/paymentModal.css" />
</head>
<body>
  <?php require 'C:\xampp\htdocs\lab_sync\public\partials\header.php'; ?>
<main class="book-wrap">
  <header class="book-head">
    <div>
      <h1 class="book-title">Book a Test</h1>
      <p class="book-sub">Choose your test, pick a date and time, then confirm — it takes under a minute.</p>
    </div>
  </header>

  <section class="book-layout">
    <div class="book-main">
      <form id="bookingForm" method="POST" action="/lab_sync/index.php?controller=home&action=bookAppointment">
        <?php if (!empty($fromRequestId)): ?>
          <input type="hidden" name="from_request" value="<?php echo htmlspecialchars($fromRequestId); ?>">
        <?php endif; ?>
        <div class="card book-card tests-card">
          <div class="sec-head">
            <h2 class="sec-title">Select tests</h2>
            <span id="selectedCount" class="count-badge">0 selected</span>
          </div>

          <div id="testList" class="test-list">
              <div class="selection-float">
                <div>
                  <div class="float-k">Selected total</div>
                  <div id="floatPrice" class="float-v">LKR 0.00</div>
                </div>
                <div id="floatCount" class="float-count">0 tests</div>
              </div>

              <?php foreach($tests as $t): ?>
                  <?php
                $isChecked = (($selectedTestId ?? 0) === (int)$t['test_id'])
                    || (!empty($preSelectedTestIds) && in_array((int)$t['test_id'], $preSelectedTestIds, true));
              ?>
                  <label class="test-row">
                    <span class="test-left">
                      <input
                        type="checkbox"
                        name="test_ids[]"
                        class="test-checkbox"
                        value="<?= htmlspecialchars($t['test_id']) ?>"
                        data-name="<?= htmlspecialchars($t['test_name']) ?>"
                        data-price="<?= htmlspecialchars($t['price'] ?? 0) ?>"
                        <?= $isChecked ? 'checked' : '' ?>
                      />
                      <span class="check-box"><span class="check-icon">✓</span></span>
                      <span class="test-name"><?= htmlspecialchars($t['test_name']) ?></span>
                    </span>
                    <strong class="test-price">LKR <?= htmlspecialchars(number_format((float)($t['price'] ?? 0), 2)) ?></strong>
                  </label>
              <?php endforeach; ?>
          </div>

          <div class="prep-strip">
            <span class="prep-dot" aria-hidden="true"></span>
            <span id="prepText">Select a test to see preparation notes.</span>
          </div>
        </div>

        <div class="card book-card details-card">
          <div class="sec-head">
            <h2 class="sec-title">Appointment details</h2>
          </div>

          <div class="details-body">
            <div>
              <label class="label" for="date">Date</label>
              <div class="date-wrap">
                <input name="appointment_date" id="date" type="date" class="date-input" required />
              </div>
            </div>

            <div>
              <label class="label">Time slot</label>
              <div class="slot-group">
                <div class="slot-period">Morning</div>
                <div id="gridMorning" class="slot-rail"></div>
              </div>

              <div class="slot-group">
                <div class="slot-period">Afternoon</div>
                <div id="gridAfternoon" class="slot-rail"></div>
              </div>
            </div>

            <div>
              <label class="label">Home collection</label>
              <div class="home-box">
                <label class="home-toggle" for="homeCollectionToggle">
                  <input type="checkbox" id="homeCollectionToggle" name="home_collection" value="1" />
                  <div>
                    <div class="home-label">Home sample collection</div>
                    <div class="home-sub">We come to your home to collect samples</div>
                  </div>
                </label>
                <div id="collectionAddressWrap" class="home-address" style="display:none;">
                  <input name="collection_address" id="collectionAddress" type="text" class="addr-input" placeholder="Enter your full collection address" />
                </div>
              </div>
            </div>

            <input type="hidden" name="appointment_time" id="selectedTime" required>
          </div>
        </div>
      </form>
    </div>

    <aside class="card summary">
      <div class="sum-head">
        <div class="sum-eyebrow">Booking summary</div>
        <h3 class="sum-title">Review &amp; confirm</h3>
      </div>

      <div class="sum-body">
        <div class="kv">
          <span class="kk">Tests</span>
          <span id="sumTest" class="vv">—</span>
        </div>
        <div class="kv">
          <span class="kk">Date</span>
          <span id="sumDate" class="vv">—</span>
        </div>
        <div class="kv">
          <span class="kk">Time</span>
          <span id="sumTime" class="vv">—</span>
        </div>
        <div class="kv kv-no-border">
          <span class="kk">Status</span>
          <span class="vv status-row">
            <span id="statusDot" class="status-dot wait"></span>
            <span id="sumStatus">Waiting for details</span>
          </span>
        </div>
      </div>

      <div id="sumItems" class="sum-items"></div>

      <div class="sum-total">
        <span class="sum-total-label">Total</span>
        <span id="sumPrice" class="sum-total-val">LKR 0.00</span>
      </div>

      <div class="sum-actions">
        <div class="sum-note-inline">⏱️ Please arrive 10 minutes before your scheduled time.</div>
        <button id="btnConfirm" class="btn-confirm" onclick="return openPaymentModal(event)">Confirm &amp; Continue Payment</button>
        <button class="btn-back" onclick="history.back()">Back</button>
      </div>

      <div class="sum-note">
        You can reschedule or cancel anytime from your Dashboard.
      </div>

      <div class="sum-footnote" style="display:none;">
        <div class="hint hint-inline">
          <span aria-hidden="true">⏱️</span>
          <span>Please arrive 10 minutes before your scheduled time.</span>
        </div>
      </div>
    </aside>
  </section>
</main>

<div id="paymentModal" class="payment-modal" aria-hidden="true">
  <div class="payment-dialog">
    <div class="payment-header">
      <span class="payment-icon-wrap">💳</span>
      <h2>Complete Payment</h2>
      <button id="btnCancelPayment" class="payment-close" aria-label="Close">&times;</button>
    </div>
    <div class="pm-divider"></div>

    <div class="pm-section-label">Order Summary</div>
    <div id="pmOrderLines" class="pm-order-lines"></div>

    <div class="pm-total-row">
      <span class="pm-total-label">Total</span>
      <strong id="pmTotal" class="pm-total-val">LKR 0.00</strong>
    </div>

    <div id="pmError" class="pm-error"></div>

    <button id="btnPayNow" class="btn-pay-now">Pay Now via Payhere</button>
    <button class="btn-cancel-payment" id="btnCancelPaymentBottom">Cancel</button>

    <div class="pm-secure-note">🔒 Secured by Payhere</div>

    <div id="pmSpinnerOverlay" class="pm-spinner-overlay">
      <div class="pm-spinner"></div>
    </div>
  </div>
</div>

<script>
// Inject config for paymentModal.js
window.LAB_SYNC_BOOK_CONFIG = {
    baseUrl: '/lab_sync',
    csrfToken: '<?php echo htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8'); ?>',
    fromRequest: <?php echo (int)($fromRequestId ?? 0); ?>
};
</script>

<script>
// slots
const slotsMorning = ['08:00','08:30','09:00','09:30','10:00','10:30','11:00'];
const slotsAfternoon = ['13:00','13:30','14:00','14:30','15:00','15:30'];
let selectedSlot = null;

function makeSlots(hostId, times){
  const host = document.getElementById(hostId);
  host.innerHTML = '';
  const dateValue = document.getElementById('date').value;
  const slotsDisabled = !dateValue;
  times.forEach(t=>{
    const b = document.createElement('button');
    b.className = 'slot';
    b.type = 'button'; // Prevent form submission on slot click
    b.textContent = t;
    b.disabled = slotsDisabled;
    b.onclick = ()=>{
      if (b.disabled) return;
      document.querySelectorAll('.slot').forEach(s=>s.classList.remove('active'));
      b.classList.add('active');
      selectedSlot = t;
      document.getElementById('selectedTime').value = t;
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

function getSelectedTests() {
  const checked = Array.from(document.querySelectorAll('.test-checkbox:checked'));
  return checked.map(item => ({
    id: item.value,
    name: item.dataset.name || 'Test',
    price: Number(item.dataset.price || 0)
  }));
}

function onTestChange(){
  document.querySelectorAll('.test-row').forEach(row => {
    const checkbox = row.querySelector('.test-checkbox');
    row.classList.toggle('selected', !!checkbox && checkbox.checked);
  });

  const selected = getSelectedTests();
  if (selected.length === 0) {
    document.getElementById('prepText').textContent = 'Select tests to see preparation notes.';
  } else if (selected.length === 1) {
    document.getElementById('prepText').textContent = prep[selected[0].name] || 'No special preparation notes.';
  } else {
    document.getElementById('prepText').textContent = 'Multiple tests selected. Follow fasting requirements if any selected test requires fasting.';
  }
  updateSummary();
}

function updateSummary(){
  const selected = getSelectedTests();
  const selectedNames = selected.map(item => item.name);
  const totalPrice = selected.reduce((sum, item) => sum + item.price, 0);
  const selectedCount = selected.length;
  const isReady = selectedCount > 0 && document.getElementById('date').value && selectedSlot;

  document.getElementById('sumTest').textContent = selectedCount ? `${selectedCount} test${selectedCount === 1 ? '' : 's'}` : '—';
  document.getElementById('sumItems').innerHTML = selected.length
    ? selected.map(item => `<div class="sum-item"><span class="sum-item-name">${item.name}</span><span class="sum-item-price">LKR ${item.price.toFixed(2)}</span></div>`).join('')
    : '<div class="sum-item-empty">No tests selected.</div>';
  document.getElementById('selectedCount').textContent = `${selectedCount} selected`;
  document.getElementById('sumDate').textContent = document.getElementById('date').value || '—';
  document.getElementById('sumTime').textContent = selectedSlot || '—';
  document.getElementById('sumPrice').textContent = `LKR ${totalPrice.toFixed(2)}`;
  document.getElementById('floatPrice').textContent = `LKR ${totalPrice.toFixed(2)}`;
  document.getElementById('floatCount').textContent = `${selectedCount} test${selectedCount === 1 ? '' : 's'}`;
  document.getElementById('sumStatus').textContent = isReady ? 'Ready to confirm' : 'Waiting for details';
  document.getElementById('statusDot').className = `status-dot ${isReady ? 'ready' : 'wait'}`;
  document.getElementById('btnConfirm').disabled = !isReady;
}

function syncCollectionFields() {
  const toggle = document.getElementById('homeCollectionToggle');
  const wrap = document.getElementById('collectionAddressWrap');
  const input = document.getElementById('collectionAddress');

  if (!toggle || !wrap || !input) {
    return;
  }

  const enabled = toggle.checked;
  wrap.style.display = enabled ? 'block' : 'none';
  input.required = enabled;
  if (!enabled) {
    input.value = '';
  }
}

// Validate and submit the form
function validateAndSubmit(event) {
    event.preventDefault();
    
    const selectedTests = getSelectedTests();
    const date = document.getElementById('date').value;
    const time = selectedSlot;
    const homeCollection = document.getElementById('homeCollectionToggle').checked;
    const collectionAddress = document.getElementById('collectionAddress').value.trim();
    
    if (selectedTests.length === 0 || !date || !time) {
      alert('Please select at least one test, date and time.');
        return false;
    }

    if (homeCollection && collectionAddress === '') {
      alert('Please enter a collection address for home sample collection.');
      return false;
    }
    
    // Format time to HH:MM:SS
    const formattedTime = time + ':00';
    document.getElementById('selectedTime').value = formattedTime;
    
    if (confirm('Confirm appointment booking?')) {
        document.getElementById('bookingForm').submit();
        return true;
    }
    
    return false;
}

renderSlots(); onTestChange();
document.getElementById('date').addEventListener('input', () => {
  selectedSlot = null;
  document.getElementById('selectedTime').value = '';
  renderSlots();
  updateSummary();
});
document.querySelectorAll('.test-checkbox').forEach(item => {
  item.addEventListener('change', onTestChange);
});
document.getElementById('homeCollectionToggle').addEventListener('change', syncCollectionFields);
syncCollectionFields();
</script>
<?php require 'C:\xampp\htdocs\lab_sync\public\partials\footer.php'; ?>
<script src="https://www.payhere.lk/lib/payhere.js"></script>
<script src="/lab_sync/public/js/paymentModal.js"></script>
</body>
</html>
