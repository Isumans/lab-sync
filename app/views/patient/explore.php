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
  <main class="container">
    <h2 class="page-title">Explore Tests</h2>
    <p class="muted">Find and book the lab tests you need.</p>
    <div class="catalog">
      <?php
        $tests = [
          ['Full Blood Count (FBC)','Measures components of your blood','No special preparation','15 minutes'],
          ['Lipid Profile','Cholesterol & heart risk','12-hour fasting, water allowed','20 minutes'],
          ['Fasting Blood Sugar (FBS)','Diabetes check','8–12 hour fasting preferred','15 minutes'],
          ['Thyroid Panel (TSH/T3/T4)','Thyroid hormone levels','Avoid biotin for 48h if applicable','15 minutes'],
          ['Liver Function Test (LFT)','Liver health markers','Avoid alcohol 24h before','20 minutes'],
          ['Kidney Function Test (KFT)','Kidney function markers','Stay hydrated unless told otherwise','15 minutes'],
          ['HbA1c','Average blood sugar over 3 months','No fasting required','15 minutes'],
        ];
        foreach ($tests as $t):
          [$name,$desc,$prep,$dur] = $t;
          $id = 't_' . md5($name);
      ?>
      <div class="card test-card" id="<?= $id ?>">
        <div class="chip"></div>
        <h3><?= htmlspecialchars($name) ?></h3>
        <p class="muted"><?= htmlspecialchars($desc) ?></p>
        <button class="btn-primary" onclick="location.href='/book.php?controller=home&action=book_test&test='.urlencode($name)">Book Test</button>
        <button class="btn-ghost" onclick="openTestModal('<?= htmlspecialchars($name) ?>','<?= htmlspecialchars($prep) ?>','<?= htmlspecialchars($dur) ?>')">View details</button>
      </div>
      <?php endforeach; ?>
    </div>
  </main>

  <!-- Modal -->
  <div id="testModal" class="modal">
    <div class="modal-sheet">
      <div class="modal-head">
        <h3 id="mTitle">Test</h3>
        <button class="modal-x" onclick="closeTestModal()">×</button>
      </div>
      <div class="modal-body">
        <h4>About This Test</h4>
        <p id="mDesc" class="muted">A brief description of the test.</p>
        <div class="modal-grid">
          <div class="modal-card">
            <strong>Preparation</strong>
            <p id="mPrep" class="muted"></p>
          </div>
          <div class="modal-card">
            <strong>Duration</strong>
            <p id="mDur" class="muted"></p>
          </div>
        </div>
      </div>
      <div class="modal-foot">
        <button class="btn-outline" onclick="closeTestModal()">Close</button>
        <button id="mBook" class="btn-primary">Book This Test</button>
      </div>
    </div>
  </div>

<script>
function openTestModal(name, prep, dur){
  document.getElementById('mTitle').textContent = name;
  document.getElementById('mDesc').textContent = 'Details for ' + name + '.';
  document.getElementById('mPrep').textContent = prep;
  document.getElementById('mDur').textContent  = dur;
  const btn = document.getElementById('mBook');
  btn.onclick = () => location.href = '/book.php?test=' + encodeURIComponent(name);
  document.getElementById('testModal').classList.add('open');
}
function closeTestModal(){ document.getElementById('testModal').classList.remove('open'); }
</script>
<?php require 'C:\xampp\htdocs\lab_sync\public\partials\footer.php'; ?>
</body>
</html>