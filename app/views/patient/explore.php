<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>LabSync - Home</title>
  <link rel="stylesheet" href="/lab_sync/public/css/patient.css" />
  <link rel="stylesheet" href="/lab_sync/public/css/nav.css" />
  <link rel="stylesheet" href="/lab_sync/public/css/book.css" />
</head>
<body>
  <?php require 'C:\xampp\htdocs\lab_sync\public\partials\header.php'; ?>
  <main class="container">
    <h2 class="page-title">Explore Tests</h2>
    <p class="muted">Find and book the lab tests you need.</p>
    <div class="catalog">
      <?php
        foreach ($tests as $t):
      ?>
      <div class="card test-card" id="<?= $id ?>">
        <div class="chip"></div>
        <h3><?= htmlspecialchars($t['test_name']) ?></h3>
        <p class="muted"><?= htmlspecialchars($t['description']) ?></p>
        <button class="btn-primary" ><a href='index.php?controller=home&action=book&test=<?= urlencode($t['test_id']) ?>'>Book Test</a></button>
        <!-- <button class="btn-ghost" onclick="openTestModal()">View details</button> -->
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