<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$user_id = $_SESSION['user_id'] ?? null;
$email = $_SESSION['email'] ?? null;
$user_role = $_SESSION['user_role'] ?? null;
$showAppointmentCta = !isset($user_id) || $user_role === 'patient';

$patientEmail = (is_string($email) && $email !== '') ? $email : 'patient@labsync.com';
$patientSourceName = trim((string)($_SESSION['username'] ?? ''));
if ($patientSourceName === '') {
  $patientSourceName = explode('@', $patientEmail)[0] ?? 'Patient';
}

$patientSourceName = preg_replace('/\d+$/', '', $patientSourceName);
$patientSourceName = preg_replace('/[^a-zA-Z\s._-]/', ' ', $patientSourceName);
$nameParts = preg_split('/[\s._-]+/', trim((string)$patientSourceName));
$firstToken = is_array($nameParts) && isset($nameParts[0]) ? trim((string)$nameParts[0]) : '';

if ($firstToken === '') {
  $firstToken = 'Patient';
}

if (strlen($firstToken) > 12) {
  $firstToken = substr($firstToken, 0, 9);
}

$patientDisplayName = ucwords(strtolower($firstToken));
$patientInitial = strtoupper(substr($patientDisplayName, 0, 1));
if ($patientInitial === '') {
  $patientInitial = 'P';
}
?>
<header class="navbar">
    <div class="container">
      <div class="nav-logo">
        <a href="/lab_sync/index.php">
          <div class="logo-mark">
            <img src="/lab_sync/public/assests/Labsync-3.png" alt="LabSync Logo">
          </div>
          <span><span style="color:#1F2B5B;">Lab</span><span style="color:#3DBDEC;">Sync</span></span>
        </a>
      </div>

      <nav class="nav-links">
        <a href="index.php?controller=home&action=explore">Tests</a>
        <a href="index.php?controller=home&action=how">How it works</a>
        <a href="index.php?controller=home&action=about">About</a>
        
        <?php if (isset($user_id) && $user_role === 'patient'): ?>
          <a href="/lab_sync/index.php?controller=home&action=dashboard">Dashboard</a>
        <?php endif; ?>
      </nav>

      <div class="nav-actions">
        <?php if ($showAppointmentCta): ?>
          <a href="/lab_sync/index.php?controller=home&action=appointment_options" class="appointment-cta">Book Appointment</a>
        <?php endif; ?>
        <?php if (isset($user_id) && isset($user_role)): ?>
          <?php if (in_array($user_role, ['admin','receptionist','technician'])): ?>
            <a href="/lab_sync/index.php?controller=dashboard&action=index" class="appointment-cta">
              <?= htmlspecialchars(ucwords($user_role)) ?> Panel
            </a>
          <?php endif; ?>
          <?php if ($user_role === 'patient'): ?>
            <div class="profile-menu" id="profileMenuWrap">
              <button
                type="button"
                class="profile-trigger"
                id="profileMenuButton"
                aria-haspopup="true"
                aria-expanded="false"
                aria-controls="profileMenuCard"
              >
                <img id="user-icon" class="profile-logo" src="/lab_sync/public/assests/user.png" alt="Open profile menu">
              </button>

              <div class="profile-menu-card" id="profileMenuCard" role="menu" aria-labelledby="profileMenuButton">
                <div class="profile-menu-top">
                  <div class="profile-avatar-lg" aria-hidden="true"><?= htmlspecialchars($patientInitial) ?></div>
                  <p class="profile-menu-greet">Hi,</p>
                  <p class="profile-menu-name"><?= htmlspecialchars($patientDisplayName) ?>!</p>
                  <p class="profile-menu-email"><?= htmlspecialchars($patientEmail) ?></p>
                </div>

                <div class="profile-menu-actions">
                  <a role="menuitem" href="/lab_sync/index.php?controller=profile&action=view" class="profile-action profile-link">
                    <span class="action-icon" aria-hidden="true">
                      <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21a8 8 0 1 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
                    </span>
                    Patient Profile
                  </a>
                  <a role="menuitem" href="/lab_sync/index.php?controller=Auth&action=logout" class="profile-action profile-logout">
                    <span class="action-icon" aria-hidden="true">
                      <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                    </span>
                    Logout
                  </a>
                </div>
              </div>
            </div>
          <?php endif; ?>
        <?php else: ?>
          <a href="/lab_sync/index.php?controller=Auth&action=login" class="login">Login</a>
          <a href="/lab_sync/index.php?controller=Auth&action=patient_signup" class="signup">Sign up</a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <?php if (isset($user_id) && isset($user_role) && $user_role === 'patient'): ?>
    <script>
      (function () {
        const menuWrap = document.getElementById('profileMenuWrap');
        const trigger = document.getElementById('profileMenuButton');

        if (!menuWrap || !trigger) {
          return;
        }

        const toggleMenu = function (forceOpen) {
          const shouldOpen = typeof forceOpen === 'boolean' ? forceOpen : !menuWrap.classList.contains('open');
          menuWrap.classList.toggle('open', shouldOpen);
          trigger.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
        };

        trigger.addEventListener('click', function (event) {
          event.preventDefault();
          toggleMenu();
        });

        document.addEventListener('click', function (event) {
          if (!menuWrap.contains(event.target)) {
            toggleMenu(false);
          }
        });

        document.addEventListener('keydown', function (event) {
          if (event.key === 'Escape') {
            toggleMenu(false);
          }
        });
      })();
    </script>
  <?php endif; ?>
