<?php
if (session_status() === PHP_SESSION_NONE) {
    // session_start();
}

$styleVersion = @filemtime(__DIR__ . '/../../../public/css/nav.css');
if ($styleVersion === false) {
  $styleVersion = time();
}

$staffNeedsPasswordChange = false;
$passwordPromptDismissed = false;
$csrfToken = (string)($_SESSION['csrf_token'] ?? '');
$passwordChangeError = trim((string)($_GET['passwordChangeError'] ?? ''));

$role = (string)($_SESSION['user_role'] ?? '');
$isStaff = in_array($role, ['admin', 'receptionist', 'technician'], true);
if ($isStaff && intval($_SESSION['must_change_password'] ?? 0) === 1) {
  $staffNeedsPasswordChange = true;
  $passwordPromptDismissed = intval($_SESSION['password_change_prompt_dismissed'] ?? 0) === 1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>LabSync - Home</title>

  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="/lab_sync/public/css/globals.css?v=<?= $styleVersion ?>" />
  <link rel="stylesheet" href="/lab_sync/public/css/nav.css?v=<?= $styleVersion ?>" />
  <link rel="stylesheet" href="/lab_sync/public/css/home.css?v=<?= $styleVersion ?>" />
  <link rel="stylesheet" href="/lab_sync/public/css/footer.css?v=<?= $styleVersion ?>" />
  <style>
    .first-login-modal {
      position: fixed;
      inset: 0;
      background: rgba(15, 23, 42, 0.55);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 2000;
      padding: 16px;
    }

    .first-login-modal.is-open {
      display: flex;
    }

    .first-login-modal-dialog {
      width: min(560px, 100%);
      background: #ffffff;
      border-radius: 14px;
      border: 1px solid #d7deea;
      box-shadow: 0 12px 28px rgba(15, 23, 42, 0.24);
      overflow: hidden;
    }

    .first-login-modal-header {
      padding: 16px 18px;
      border-bottom: 1px solid #e5eaf3;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 10px;
    }

    .first-login-modal-title {
      margin: 0;
      font-size: 1.1rem;
      color: #1f2b5b;
      font-weight: 700;
    }

    .first-login-modal-close {
      background: transparent;
      border: none;
      font-size: 1.35rem;
      line-height: 1;
      color: #4b5563;
      cursor: pointer;
    }

    .first-login-modal-body {
      padding: 18px;
      color: #1f2b5b;
    }

    .first-login-instruction {
      margin: 0 0 14px;
      font-size: 0.96rem;
    }

    .first-login-error {
      margin: 0 0 12px;
      background: #fee2e2;
      color: #9f1239;
      border: 1px solid #fecaca;
      border-radius: 8px;
      padding: 10px 12px;
      font-size: 0.9rem;
    }

    .first-login-form-grid {
      display: grid;
      gap: 12px;
    }

    .first-login-form-grid label {
      display: grid;
      gap: 6px;
      font-size: 0.9rem;
      color: #334155;
      font-weight: 600;
    }

    .first-login-form-grid input {
      border: 1px solid #cfd8e3;
      border-radius: 8px;
      padding: 10px 12px;
      font-size: 0.94rem;
    }

    .first-login-modal-footer {
      padding: 14px 18px;
      border-top: 1px solid #e5eaf3;
      display: flex;
      justify-content: flex-end;
      gap: 10px;
    }

    .first-login-btn {
      border: none;
      border-radius: 8px;
      padding: 10px 14px;
      cursor: pointer;
      font-weight: 600;
      font-size: 0.9rem;
    }

    .first-login-btn-secondary {
      background: #eef2f7;
      color: #1f2b5b;
    }

    .first-login-btn-primary {
      background: #1f2b5b;
      color: #fff;
    }
  </style>
</head>
<body>
  <?php require_once __DIR__ . '/../../../public/partials/header.php'; ?>


  <!-- =============================================
       HERO
       ============================================= -->
  <section class="hero" style="padding-top: 9rem;">
    <div class="hero-bg">
      <img src="https://images.unsplash.com/photo-1579154204601-01588f351e67?auto=format&fit=crop&w=2000&q=80" alt="Laboratory">
    </div>

    <div class="hero-inner">
      <!-- Home-visit badge — your KEY differentiator -->
      <div class="home-visit-pill">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        We Come to You &nbsp;·&nbsp; Home Sample Collection
      </div>

      <div class="hero-badge">
        <span class="dot"></span>
        <span>Precision Diagnostics · Colombo &amp; Beyond</span>
      </div>

      <h1>
        Your Health,<br>
        <span class="accent">at Your Doorstep.</span>
      </h1>

      <p>
        Skip the clinic queue. Our trained sample-collection team visits your home, collects your samples, and delivers digital results all within 24 hours.
      </p>

      <!-- Search bar -->
      <form class="hero-search glass-card" method="get" action="/lab_sync/index.php">
        <input type="hidden" name="controller" value="home">
        <input type="hidden" name="action" value="explore">
        <div class="hero-search-wrap">
          <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          <input type="text" name="q" placeholder="Search test e.g., CBC, Lipid Panel, Vitamin D..." aria-label="Search tests">
        </div>
        <button type="submit" class="btn-search">
          Search Tests
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </button>
      </form>
    </div>
  </section>


  <!-- =============================================
       TRUST STATS
       ============================================= -->
  <section class="trust-bar">
    <div class="container">
      <div class="trust-item"><p class="num">50k+</p><p class="label">Happy Patients</p></div>
      <div class="trust-item"><p class="num accent">24hr</p><p class="label">Turnaround Time</p></div>
      <div class="trust-item"><p class="num">150+</p><p class="label">Certified Tests</p></div>
      <div class="trust-item"><p class="num green">99.9%</p><p class="label">Accuracy Rate</p></div>
    </div>
  </section>


  <!-- =============================================
       HOME-VISIT HIGHLIGHT BANNER
       ============================================= -->
  <section class="home-banner">
    <div class="container">
      <div class="home-banner-left">
        <p class="eyebrow">
          <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
          Our Signature Service
        </p>
        <h2>Lab-Quality Testing,<br>Right at Your Home.</h2>
        <p>No more waiting rooms. Our trained collection staff arrive at a time that suits you, collect your blood or urine sample, and your results appear in your dashboard — fast.</p>
        <div class="home-banner-features">
          <span>
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg>
            Same-day slots available
          </span>
          <span>
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg>
            Trained collection staff
          </span>
          <span>
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg>
            Digital results in 24 hrs
          </span>
        </div>
      </div>
      <a href="/lab_sync/index.php?controller=home&action=about" class="home-banner-cta">
        About LabSync
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
    </div>
  </section>


  <!-- =============================================
       HOW IT WORKS
       ============================================= -->
  <section class="how-section">
    <div class="section-header">
      <h2>Frictionless Healthcare</h2>
      <p>We've streamlined the diagnostic process so you can focus on what matters most — your health.</p>
    </div>

    <div class="steps-grid">
      <div class="step">
        <div class="step-icon-wrap">
          <div class="step-icon-inner">
            <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          </div>
        </div>
        <h3>1. Choose Test</h3>
        <p>Search our catalogue and pick a convenient home-visit time slot online.</p>
      </div>
      <div class="step">
        <div class="step-icon-wrap">
          <div class="step-icon-inner green">
            <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          </div>
        </div>
        <h3>2. We Visit You</h3>
        <p>Our trained collection staff arrives at your home at the scheduled time for a quick, professional collection.</p>
      </div>
      <div class="step">
        <div class="step-icon-wrap">
          <div class="step-icon-inner">
            <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"/></svg>
          </div>
        </div>
        <h3>3. Get Digital Results</h3>
        <p>Receive a secure notification and view your detailed PDF report on your dashboard within 24 hours.</p>
      </div>
    </div>
  </section>


  <!-- =============================================
       FEATURED TESTS
       ============================================= -->
  <section class="featured-section">
    <div class="section-header">
      <h2>Popular Health Screenings</h2>
      <p>Comprehensive tests to keep you at your best — all available as home visits.</p>
    </div>

    <?php
    $featuredTests = isset($featuredTests) && is_array($featuredTests) ? $featuredTests : [];

    function ft_icon($id){
      $s = 'stroke="currentColor" stroke-width="1.8"';
      if($id==='drop')   return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M12 21s7-6.5 7-11a7 7 0 0 0-14 0c0 4.5 7 11 7 11Z" '.$s.'/><path d="M9.5 12a2.5 2.5 0 0 0 5 0" '.$s.' stroke-linecap="round"/></svg>';
      if($id==='heart')  return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M12 20s-7-4.3-9-7.5A5 5 0 0 1 11 7a5 5 0 0 1 8 5.5C17 15.7 12 20 12 20Z" '.$s.' stroke-linecap="round" stroke-linejoin="round"/></svg>';
      if($id==='bolt')   return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M13 2 4 14h7l-1 8 9-12h-7l1-8Z" '.$s.' stroke-linecap="round" stroke-linejoin="round"/></svg>';
      if($id==='wave')   return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M3 12c2 0 2-6 4-6s2 12 4 12 2-12 4-12 2 6 4 6" '.$s.' stroke-linecap="round"/></svg>';
      if($id==='beaker') return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M9 3h6M10 3v5l-5.5 8.7A3 3 0 0 0 7 21h10a3 3 0 0 0 2.5-4.7L14 8V3" '.$s.' stroke-linecap="round"/></svg>';
      return '';
    }
    ?>

    <div class="featured-grid">
      <?php foreach ($featuredTests as $t): ?>
        <article class="featured-card">
          <div class="ft-icon"><?= ft_icon($t['icon']) ?></div>
          <div class="ft-body">
            <h3 class="ft-title"><?= htmlspecialchars($t['name']) ?></h3>
            <p class="ft-desc"><?= htmlspecialchars($t['desc']) ?></p>
            <div class="ft-cta">
              <a class="pill-btn" href="/lab_sync/index.php?controller=home&action=book&test=<?= (int)($t['id'] ?? 0) ?>">
                Book Test
              </a>
              <span class="home-tag">
                <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                At-home
              </span>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <div class="featured-more">
      <a class="ghost-pill" href="/lab_sync/index.php?controller=home&action=explore">
        View All Tests <span>→</span>
      </a>
    </div>
  </section>


  <!-- =============================================
       WHY LABSYNC
       ============================================= -->
  <section class="why-section">
    <div class="section-header">
      <h2>Why Choose LabSync?</h2>
      <p>Patient-first healthcare that puts you in control.</p>
    </div>

    <div class="why-grid">
      <article class="why-card">
        <div class="why-icon">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
            <path d="M12 3l7 3v6c0 4.97-3.38 8.5-7 9-3.62-.5-7-4.03-7-9V6l7-3Z" stroke-linejoin="round"/>
            <path d="M9.5 12.5l2 2 3.5-4" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <h3 class="why-card-title">Privacy Protected</h3>
        <p class="why-card-text">Your health data is encrypted and secure.</p>
      </article>
      <article class="why-card">
        <div class="why-icon">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
            <circle cx="12" cy="12" r="9"/>
            <path d="M12 7v5l3 2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <h3 class="why-card-title">Fast Results</h3>
        <p class="why-card-text">Get results within 24–48 hours, delivered digitally.</p>
      </article>
      <article class="why-card">
        <div class="why-icon">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
            <circle cx="12" cy="9" r="4"/>
            <path d="M10 13l-3 8 5-3 5 3-3-8" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <h3 class="why-card-title">Certified Labs</h3>
        <p class="why-card-text">Every report follows a quality-check process before release.</p>
      </article>
      <article class="why-card">
        <div class="why-icon">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
            <circle cx="12" cy="12" r="9"/>
            <path d="M8.5 12.5l2.5 2.5 4.5-5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <h3 class="why-card-title">Expert Support</h3>
        <p class="why-card-text">24/7 help for all your questions and concerns.</p>
      </article>
    </div>
  </section>


  <?php if ($staffNeedsPasswordChange): ?>
    <div
      id="firstLoginPasswordModal"
      class="first-login-modal <?php echo $passwordPromptDismissed ? '' : 'is-open'; ?>"
      data-open="<?php echo $passwordPromptDismissed ? '0' : '1'; ?>"
      data-csrf="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>"
      data-dismiss-url="/lab_sync/index.php?controller=userController&action=dismissPasswordPrompt"
      data-force-required="1"
    >
      <div class="first-login-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="firstLoginModalTitle">
        <div class="first-login-modal-header">
          <h3 id="firstLoginModalTitle" class="first-login-modal-title">Change Your Temporary Password</h3>
          <button type="button" id="firstLoginModalClose" class="first-login-modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="first-login-modal-body">
          <p class="first-login-instruction">You are signed in with a temporary password. Set a new password now to secure your account. If you close this, a reminder stays in the notification bell.</p>
          <?php if ($passwordChangeError !== ''): ?>
            <p class="first-login-error"><?php echo htmlspecialchars($passwordChangeError, ENT_QUOTES, 'UTF-8'); ?></p>
          <?php endif; ?>

          <form method="post" action="/lab_sync/index.php?controller=userController&action=changePassword" class="first-login-form-grid">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="from_landing" value="1">

            <label>
              Current Password
              <input type="password" name="current_password" required autocomplete="current-password">
            </label>
            <label>
              New Password
              <input type="password" name="new_password" required minlength="8" autocomplete="new-password">
            </label>
            <label>
              Confirm New Password
              <input type="password" name="confirm_password" required minlength="8" autocomplete="new-password">
            </label>

            <div class="first-login-modal-footer">
              <button type="button" id="firstLoginMaybeLater" class="first-login-btn first-login-btn-secondary">Maybe Later</button>
              <button type="submit" class="first-login-btn first-login-btn-primary">Change Password</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  <?php endif; ?>


  <?php require_once __DIR__ . '/../../../public/partials/footer.php'; ?>
  <script src="/lab_sync/public/js/firstLoginPasswordPrompt.js"></script>

</body>
</html>
