<?php

$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

$profileName = $patient['patient_name'] ?? $patient['username'] ?? 'Patient';
$profileEmail = $patient['patient_email'] ?? $patient['user_email'] ?? '';
$profileContact = $patient['contact_number'] ?? $patient['user_contact'] ?? '';
$profileGender = $patient['gender'] ?? '';
$profileAddress = $patient['address'] ?? '';

$initials = 'PP';
$nameParts = preg_split('/\s+/', trim((string)$profileName));
if (is_array($nameParts)) {
    $letters = [];
    foreach ($nameParts as $part) {
        if ($part !== '') {
            $letters[] = strtoupper(substr($part, 0, 1));
        }
        if (count($letters) === 2) {
            break;
        }
    }

    if (!empty($letters)) {
        $initials = implode('', $letters);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>LabSync Profile</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/lab_sync/public/css/globals.css" />
  <link rel="stylesheet" href="/lab_sync/public/css/nav.css" />
  <link rel="stylesheet" href="/lab_sync/public/css/footer.css" />
  <link rel="stylesheet" href="/lab_sync/public/profile.css" />
</head>
<body>
  <?php require_once __DIR__ . '/../../../public/partials/header.php'; ?>

  <main class="profile-wrap">
    <header class="profile-head">
      <div>
        <h1 class="profile-title">My Profile</h1>
        <p class="profile-sub">View your details and keep your profile up to date.</p>
      </div>
      <button type="button" class="btn-primary" id="openEditProfileModal">Edit Profile</button>
    </header>

    <?php if ($success !== ''): ?>
      <div class="profile-alert success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
      <div class="profile-alert error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <section class="profile-card">
      <div class="profile-summary">
        <div class="avatar" aria-hidden="true"><?php echo htmlspecialchars($initials); ?></div>
        <div>
          <h2 class="profile-name"><?php echo htmlspecialchars($profileName); ?></h2>
          <p class="profile-role">Patient Account</p>
        </div>
      </div>

      <div class="details-grid">
        <article class="detail-item">
          <h3>Full Name</h3>
          <p><?php echo htmlspecialchars($profileName !== '' ? $profileName : 'Not set'); ?></p>
        </article>
        <article class="detail-item">
          <h3>Email</h3>
          <p><?php echo htmlspecialchars($profileEmail !== '' ? $profileEmail : 'Not set'); ?></p>
        </article>
        <article class="detail-item">
          <h3>Contact Number</h3>
          <p><?php echo htmlspecialchars($profileContact !== '' ? $profileContact : 'Not set'); ?></p>
        </article>
        <article class="detail-item">
          <h3>Gender</h3>
          <p><?php echo htmlspecialchars($profileGender !== '' ? $profileGender : 'Not set'); ?></p>
        </article>
        <article class="detail-item detail-wide">
          <h3>Address</h3>
          <p><?php echo htmlspecialchars($profileAddress !== '' ? $profileAddress : 'Not set'); ?></p>
        </article>
      </div>
    </section>

    <section class="security-card">
      <div class="security-head">
        <h2>Security</h2>
        <p>Change your password regularly to keep your account secure.</p>
      </div>
      <form class="security-form" action="/lab_sync/index.php?controller=profile&action=changePassword" method="POST">
        <div class="security-grid">
          <div class="field full">
            <label for="curPwd">Current Password</label>
            <input id="curPwd" name="current_password" type="password" placeholder="Enter current password" required>
          </div>
          <div class="field">
            <label for="newPwd">New Password</label>
            <input id="newPwd" name="new_password" type="password" placeholder="Enter new password" required>
          </div>
          <div class="field">
            <label for="cnfPwd">Confirm New Password</label>
            <input id="cnfPwd" name="confirm_password" type="password" placeholder="Confirm new password" required>
          </div>
        </div>
        <div class="security-actions">
          <button class="btn-primary" type="submit">Change Password</button>
        </div>
      </form>
    </section>

    <div class="logout-row">
      <a class="btn-outline" href="/lab_sync/index.php?controller=Auth&action=logout">Logout</a>
    </div>
  </main>

  <div class="profile-modal" id="profileEditModal" aria-hidden="true">
    <div class="profile-modal-card" role="dialog" aria-modal="true" aria-labelledby="profileModalTitle">
      <div class="profile-modal-head">
        <h2 id="profileModalTitle">Edit Profile</h2>
        <button type="button" class="modal-close" id="closeEditProfileModal" aria-label="Close">&times;</button>
      </div>
      <form class="profile-modal-form" action="/lab_sync/index.php?controller=profile&action=update" method="POST">
        <div class="field-grid">
          <div class="field">
            <label for="pfName">Full Name</label>
            <input id="pfName" name="pfName" type="text" value="<?php echo htmlspecialchars($profileName); ?>" required>
          </div>
          <div class="field">
            <label for="pfEmail">Email</label>
            <input id="pfEmail" name="pfEmail" type="email" value="<?php echo htmlspecialchars($profileEmail); ?>" required>
          </div>
          <div class="field">
            <label for="pfContact">Contact Number</label>
            <input id="pfContact" name="pfContact" type="tel" value="<?php echo htmlspecialchars($profileContact); ?>">
          </div>
          <div class="field">
            <label for="pfGender">Gender</label>
            <select id="pfGender" name="pfGender">
              <option value="" <?php echo ($profileGender === '') ? 'selected' : ''; ?>>Select gender</option>
              <option value="Male" <?php echo ($profileGender === 'Male') ? 'selected' : ''; ?>>Male</option>
              <option value="Female" <?php echo ($profileGender === 'Female') ? 'selected' : ''; ?>>Female</option>
              <option value="Other" <?php echo ($profileGender === 'Other') ? 'selected' : ''; ?>>Other</option>
            </select>
          </div>
          <div class="field full">
            <label for="pfAddress">Address</label>
            <textarea id="pfAddress" name="pfAddress" rows="3" placeholder="Enter your address"><?php echo htmlspecialchars($profileAddress); ?></textarea>
          </div>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn-outline" id="cancelEditProfileModal">Cancel</button>
          <button type="submit" class="btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <?php require_once __DIR__ . '/../../../public/partials/footer.php'; ?>

  <script>
    (function () {
      const modal = document.getElementById('profileEditModal');
      const openBtn = document.getElementById('openEditProfileModal');
      const closeBtn = document.getElementById('closeEditProfileModal');
      const cancelBtn = document.getElementById('cancelEditProfileModal');

      function openModal() {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
      }

      function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
      }

      if (openBtn) {
        openBtn.addEventListener('click', openModal);
      }
      if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
      }
      if (cancelBtn) {
        cancelBtn.addEventListener('click', closeModal);
      }

      modal.addEventListener('click', function (event) {
        if (event.target === modal) {
          closeModal();
        }
      });

      document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
          closeModal();
        }
      });
    })();
  </script>

  <script src="/lab_sync/public/js/showAlert.js"></script>
</body>
</html>