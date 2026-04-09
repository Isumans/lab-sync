<?php
  // Fetch patient data from the database
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }

  $success = $_SESSION['success'] ?? '';
  $error = $_SESSION['error'] ?? '';
  unset($_SESSION['success'], $_SESSION['error']);

?>

<html lang="en">
<head>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>LabSync Profile</title>
  <link rel="stylesheet" href="/lab_sync/public/css/patient.css"/>
  <link rel="stylesheet" href="/lab_sync/public/css/nav.css"/>
  <link rel="stylesheet" href="/lab_sync/public/profile.css"/>
</head>
<body>
  <?php require __DIR__ . '/../../../public/partials/header.php'; ?>

  <main class="profile-wrap">
    <header class="profile-head">
      <div>
        <h1 class="profile-title">Profile Settings</h1>
        <p class="profile-sub">Manage your personal information and preferences</p>
      </div>
    </header>

    <?php if (!empty($success)): ?>
      <div style="margin-bottom:12px; padding:10px 12px; border-radius:8px; border:1px solid #b7ebcd; background:#e8f7ef; color:#067647;">
        <?php echo htmlspecialchars($success); ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div style="margin-bottom:12px; padding:10px 12px; border-radius:8px; border:1px solid #f1c2c2; background:#fff0f0; color:#b32525;">
        <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

    <!-- ===== Personal Information ===== -->
    <section class="card profile-card">
      <div class="profile-card-head">
        <div class="head-left">
          <div class="avatar" id="avatar">
            <span id="avatarInitials">JD</span>
            <img id="avatarImg" alt="" />
          </div>

          <div class="upload">
            <label for="photoInput" class="btn-outline upload-btn">
              <span class="ico">⇧</span> Upload Photo
            </label>
            <input id="photoInput" type="file" accept="image/png, image/jpeg, image/gif" hidden/>
            <div class="upload-hint">JPG, PNG or GIF. Max 2MB</div>
          </div>
        </div>

        <div class="head-right">
          <div class="badge-soft">Personal Information</div>
        </div>
      </div>

      <form class="profile-form" action="/lab_sync/index.php?controller=profile&action=update" method="POST" >
        <?php if  (isset($patient)): ?>
          <?php
          $profileName = $patient['patient_name'] ?? $patient['username'] ?? '';
          $profileEmail = $patient['patient_email'] ?? $patient['user_email'] ?? '';
          $profileContact = $patient['contact_number'] ?? $patient['user_contact'] ?? '';
          $profileGender = $patient['gender'] ?? '';
          $profileAddress = $patient['address'] ?? '';
          ?>
          
            <div class="pf-grid">
            <div class="pf-field">
                <label for="pfName" class="label">Full Name</label>
            <input id="pfName" name="pfName" class="input input-lg" type="text" value="<?php echo htmlspecialchars($profileName); ?>"/>
            </div>

            <div class="pf-field">
                <label for="pfEmail" class="label">Email</label>
            <input id="pfEmail" name="pfEmail" class="input input-lg" type="email" placeholder="john@example.com" value="<?php echo htmlspecialchars($profileEmail); ?>"/>
            </div>

            <div class="pf-field">
                <label for="pfContact" class="label">Contact Number</label>
            <input id="pfContact" name="pfContact" class="input input-lg" type="tel" placeholder="+94 77 123 4567" value="<?php echo htmlspecialchars($profileContact); ?>"/>
            </div>

            <div class="pf-field">
                <label for="pfGender" class="label">Gender</label>
                <select id="pfGender" name="pfGender" class="input input-lg">
            <option value="" <?php echo (empty($profileGender)) ? 'selected' : ''; ?>>Select gender</option>
            <option value="Male" <?php echo ($profileGender === 'Male') ? 'selected' : ''; ?>>Male</option>
            <option value="Female" <?php echo ($profileGender === 'Female') ? 'selected' : ''; ?>>Female</option>
            <option value="Other" <?php echo ($profileGender === 'Other') ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>

            <div class="pf-field pf-wide">
                <label for="pfAddress" class="label">Address</label>
            <textarea id="pfAddress" name="pfAddress" class="input input-lg" rows="3" placeholder="123 Main Street, Colombo"><?php echo htmlspecialchars($profileAddress); ?></textarea>
            </div>
            </div>

            <div class="profile-actions">
            <button class="btn-outline" type="button" onclick="profileReset()">Cancel</button>
            <button class="btn-primary" type="submit">Save Changes</button>
            </div>
        <?php endif; ?>
      </form>
    </section>

    <!-- ===== Security / Change Password ===== -->
    <section class="card security-card">
      <div class="security-head">
        <div class="sec-left">
          <div class="sec-lock">🔒</div>
          <div>
            <h3 class="sec-title">Security</h3>
            <p class="sec-sub">Update your password to keep your account secure</p>
          </div>
        </div>
      </div>

      <form class="security-form" action="/lab_sync/index.php?controller=profile&action=changePassword" method="POST">
        <div class="sec-grid">
          <div class="pf-field pf-wide">
            <label class="label">Current Password</label>
            <input id="curPwd" name="current_password" class="input input-lg" type="password" placeholder="Enter current password" required/>
          </div>

          <div class="pf-field">
            <label class="label">New Password</label>
            <input id="newPwd" name="new_password" class="input input-lg" type="password" placeholder="Enter new password" oninput="showStrength(this.value)" required/>
            <div class="pwd-meter">
              <div id="meterBar" class="meter-bar"></div>
            </div>
            <div id="meterText" class="meter-text muted">Use at least 8 characters, with letters & numbers</div>
          </div>

          <div class="pf-field">
            <label class="label">Confirm New Password</label>
            <input id="cnfPwd" name="confirm_password" class="input input-lg" type="password" placeholder="Confirm new password" required/>
          </div>
        </div>

        <div class="profile-actions">
          <button class="btn-primary" type="submit">Change Password</button>
        </div>
      </form>
    </section>

    <div style="display:flex; justify-content:flex-end; margin-top:16px; padding-bottom:8px;">
      <a class="btn-outline" href="/lab_sync/index.php?controller=Auth&action=logout" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">Logout</a>
    </div>
  </main>

  <?php require __DIR__ . '/../../../public/partials/footer.php'; ?>

<!-- <script>
/* ===== Avatar preview + initials fallback (UI only) ===== */
const photoInput = document.getElementById('photoInput');
const avatarImg  = document.getElementById('avatarImg');
const avatarInitials = document.getElementById('avatarInitials');
const nameInput = document.getElementById('pfName');

function setInitialsFromName() {
  const n = (nameInput.value || '').trim();
  const parts = n ? n.split(/\s+/).slice(0,2) : [];
  const initials = parts.map(p => p[0]?.toUpperCase() || '').join('') || 'PP';
  avatarInitials.textContent = initials;
}
setInitialsFromName();
nameInput.addEventListener('input', setInitialsFromName);

photoInput.addEventListener('change', () => {
  const f = photoInput.files?.[0];
  if(!f) return;
  if(f.size > 2 * 1024 * 1024){ alert('Please choose an image under 2MB.'); photoInput.value=''; return; }
  const reader = new FileReader();
  reader.onload = e => {
    avatarImg.src = e.target.result;
    avatarImg.style.display = 'block';
    avatarInitials.style.display = 'none';
  };
  reader.readAsDataURL(f);
});

/* ===== Profile save/reset (UI only) ===== */
function profileReset(){
  document.querySelector('.profile-form').reset();
  avatarImg.src = '';
  avatarImg.style.display = 'none';
  avatarInitials.style.display = 'block';
  setInitialsFromName();
}
function profileSave(){
  alert('Saved (UI only). Wire this to your PHP controller to persist.');
}

/* ===== Password strength + change (UI only) ===== */
const meterBar  = document.getElementById('meterBar');
const meterText = document.getElementById('meterText');

function scorePassword(pwd){
  let score = 0;
  if(!pwd) return score;
  const letters = {};
  for(let i=0;i<pwd.length;i++){ letters[pwd[i]] = (letters[pwd[i]] || 0) + 1; score += 5.0 / letters[pwd[i]]; }
  const variations = { digits:/\d/.test(pwd), lower:/[a-z]/.test(pwd), upper:/[A-Z]/.test(pwd), nonWords:/\W/.test(pwd) };
  let variationCount = 0; for (let check in variations){ variationCount += (variations[check] === true) ? 1 : 0; }
  score += (variationCount - 1) * 10;
  return parseInt(score);
}

function showStrength(pwd){
  const s = scorePassword(pwd);
  let w=0, label='Weak', color='#f09b8f';
  if(s>60){ w=100; label='Strong'; color='#2fb173'; }
  else if(s>45){ w=70; label='Good'; color='#4fb6ff'; }
  else if(s>25){ w=40; label='Fair'; color='#f0c66f'; }
  else { w=20; }
  meterBar.style.width = w + '%';
  meterBar.style.background = color;
  meterText.textContent = label === 'Weak'
    ? 'Use at least 8 characters, with letters & numbers'
    : label + ' password';
}

function changePassword(){
  const cur = document.getElementById('curPwd').value.trim();
  const np  = document.getElementById('newPwd').value.trim();
  const cp  = document.getElementById('cnfPwd').value.trim();

  if(!cur || !np || !cp){ alert('Please fill all password fields.'); return; }
  if(np.length < 8){ alert('New password must be at least 8 characters.'); return; }
  if(np !== cp){ alert('New password and confirmation do not match.'); return; }

  // UI only — connect to your PHP endpoint later
  alert('Password updated (UI only). Hook to your controller to persist.');
}
</script> -->
<script src="/lab_sync/public/js/showAlert.js"></script>
</body>
</html>
