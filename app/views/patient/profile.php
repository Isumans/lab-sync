<?php
  // Fetch patient data from the database

?>

<html lang="en">
<head>
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
      <h1 class="profile-title">Profile Settings</h1>
      <p class="profile-sub">Manage your personal information and preferences</p>
    </header>

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

      <form class="profile-form" action="index.php?controller=profile&action=update" method="POST" >
        <?php if  (isset($patient)): ?>
          
            <div class="pf-grid">
            <div class="pf-field">
                <label for="pfName" class="label">Full Name</label>
                <input id="pfName" name="pfName" class="input input-lg" type="text" value="<?php echo htmlspecialchars($patient['patient_name']); ?>"/>
            </div>

            <div class="pf-field">
                <label for="pfEmail" class="label">Email</label>
                <input id="pfEmail" name="pfEmail" class="input input-lg" type="email" placeholder="john@example.com" value="<?php echo htmlspecialchars($patient['email']); ?>"/>
            </div>

            <div class="pf-field">
                <label for="pfContact" class="label">Contact Number</label>
                <input id="pfContact" name="pfContact" class="input input-lg" type="tel" placeholder="+94 77 123 4567" value="<?php echo htmlspecialchars($patient['contact_number']); ?>"/>
            </div>

            <div class="pf-field">
                <label for="pfGender" class="label">Gender</label>
                <select id="pfGender" name="pfGender" class="input input-lg">
                <option value="male" <?php echo ($patient['gender'] === 'male') ? 'selected' : ''; ?>>Male</option>
                <option value="female" <?php echo ($patient['gender'] === 'female') ? 'selected' : ''; ?>>Female</option>
                <option value="other" <?php echo ($patient['gender'] === 'other') ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>

            <div class="pf-field pf-wide">
                <label for="pfAddress" class="label">Address</label>
                <textarea id="pfAddress" name="pfAddress" class="input input-lg" rows="3" placeholder="123 Main Street, Colombo"><?php echo htmlspecialchars($patient['address']); ?></textarea>
            </div>
            </div>

            <div class="profile-actions">
            <button class="btn-outline" type="button" onclick="profileReset()">Cancel</button>
            <button class="btn-primary" type="submit" onclick="showAlertAndSubmit()">Save Changes</button>
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

      <form class="security-form" onsubmit="return false">
        <div class="sec-grid">
          <div class="pf-field pf-wide">
            <label class="label">Current Password</label>
            <input id="curPwd" class="input input-lg" type="password" placeholder="Enter current password"/>
          </div>

          <div class="pf-field">
            <label class="label">New Password</label>
            <input id="newPwd" class="input input-lg" type="password" placeholder="Enter new password" oninput="showStrength(this.value)"/>
            <div class="pwd-meter">
              <div id="meterBar" class="meter-bar"></div>
            </div>
            <div id="meterText" class="meter-text muted">Use at least 8 characters, with letters & numbers</div>
          </div>

          <div class="pf-field">
            <label class="label">Confirm New Password</label>
            <input id="cnfPwd" class="input input-lg" type="password" placeholder="Confirm new password"/>
          </div>
        </div>

        <div class="profile-actions">
          <button class="btn-primary" type="submit" onclick="changePassword()">Change Password</button>
        </div>
      </form>
    </section>
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