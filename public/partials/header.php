<?php
// set your project base once; change only this if the folder name changes
$BASE = '/lab_sync';
?>
<header class="navbar">
  <div class="nav-logo" style="cursor:pointer"
       onclick="location.href='<?= $BASE ?>/app/views/patient/patientIndex.php'">
    <img src="<?= $BASE ?>/public/assests/Labsync-3.png" alt="LabSync Logo"/>
    <span>LabSync</span>
  </div>

  <nav class="nav-links">
    <a href="<?= $BASE ?>/app/views/patient/explore.php">Tests</a>
    <a href="<?= $BASE ?>/app/views/patient/how.php">How it works</a>
    <a href="<?= $BASE ?>/app/views/patient/patientIndex.php#help">Help</a>
  </nav>

  <div class="nav-actions">
    <a href="<?= $BASE ?>/index.php?controller=Auth&action=login" class="login">Login</a>
    <a href="<?= $BASE ?>/index.php?controller=Auth&action=signup" class="signup">Sign up</a>
  </div>
</header>
