<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$user_id = $_SESSION['user_id'] ?? null;
$email = $_SESSION['email'] ?? null;
$user_role = $_SESSION['user_role'] ?? null;
?>
<header class="navbar">
    <div class="container">
      <div class="nav-logo">
        <a href="/lab_sync/index.php">
          <div class="logo-mark">
            <img src="/lab_sync/public/assests/Labsync-3.png" alt="LabSync Logo">
          </div>
          <span>LabSync</span>
        </a>
      </div>

      <nav class="nav-links">
        <a href="index.php?controller=home&action=explore">Tests</a>
        <a href="index.php?controller=blog&action=index">Health Updates</a>
        <a href="index.php?controller=home&action=how">How it works</a>
        <a href="index.php?controller=home&action=about">About</a>
      </nav>

      <div class="nav-actions">
        <a href="/lab_sync/index.php?controller=home&action=appointment_options" class="appointment-cta">Test Appointment</a>
        <?php if (isset($user_id) && isset($user_role)): ?>
          <?php if (in_array($user_role, ['admin','receptionist','technician'])): ?>
            <a href="/lab_sync/index.php?controller=dashboard&action=index" class="login">
              <span class="user-role"><?= htmlspecialchars($user_role) ?> panel</span>
            </a>
          <?php endif; ?>
          <?php if ($user_role === 'patient'): ?>
            <a href="/lab_sync/index.php?controller=profile&action=view">
              <img id="user-icon" class="profile-logo" src="/lab_sync/public/assests/user.png" alt="Profile">
            </a>
          <?php endif; ?>
        <?php else: ?>
          <a href="/lab_sync/index.php?controller=Auth&action=login" class="login">Login</a>
          <a href="/lab_sync/index.php?controller=Auth&action=patient_signup" class="signup">Sign up</a>
        <?php endif; ?>
      </div>
    </div>
  </header>
