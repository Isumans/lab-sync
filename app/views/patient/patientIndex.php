<?php
if (session_status() === PHP_SESSION_NONE) {
    // session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>LabSync - Home</title>
  <link rel="stylesheet" href="/lab_sync/public/css/patient.css" />
  <link rel="stylesheet" href="/lab_sync/public/css/nav.css" />
  <link rel="stylesheet" href="/lab_sync/public/why.css" />
  <link rel="stylesheet" href="/lab_sync/public/featured.css" />
</head>
<body>
  <?php require_once PUBLIC_PATH . '/partials/header.php'; ?>
  <section class="hero">
      <div class="hero-text">
        <h1>Your Health,<br><span>Simplified</span></h1>
        <p>Book lab tests online, get fast results, and take control of your health journey with LabSync's modern platform.</p>
        <div class="hero-buttons">
          <a href="/lab_sync/index.php?controller=home&action=explore" class="btn-primary">Book a Test →</a>
          <?php if (isset($_SESSION['user_id'])&& isset($_SESSION['user_role'])&& $_SESSION['user_role']==='patient'): ?>
            <a href="/lab_sync/index.php?controller=home&action=dashboard" class="btn-outline">Go to Dashboard</a>
          <?php endif; ?>
        </div>
      </div>
      <div class="hero-image">
        <img src="/lab_sync/public/images/image.png" alt="Hero Image">
        <div class="trust-badge">✅ Trusted by 50,000+ patients</div>
      </div>
    </section>



    <!-- ===== Featured / Popular Lab Tests (premium look) ===== -->
  <section id="tests" class="featured-tests">
    <h2 class="featured-title">Our most popular health screening tests</h2>
    <p class="featured-sub">Comprehensive health screenings to keep you at your best.</p>

    <?php
    $tests = [
      ['name'=>'Full Blood Count (FBC)',     'desc'=>'Comprehensive blood health analysis',       'icon'=>'drop'],
      ['name'=>'Lipid Profile',              'desc'=>'Heart health and cholesterol screening',    'icon'=>'heart'],
      ['name'=>'Fasting Blood Sugar (FBS)',  'desc'=>'Diabetes screening and monitoring',         'icon'=>'bolt'],
      ['name'=>'Thyroid Panel (TSH/T3/T4)',  'desc'=>'Complete thyroid hormone analysis',         'icon'=>'wave'],
      ['name'=>'Liver Function Test (LFT)',  'desc'=>'Liver health assessment',                   'icon'=>'beaker'],
      ['name'=>'Kidney Function Test (KFT)', 'desc'=>'Kidney health evaluation',                  'icon'=>'beaker'],
    ];

    function ft_icon($id){
      if($id==='drop')  return '<svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M12 21s7-6.5 7-11a7 7 0 0 0-14 0c0 4.5 7 11 7 11Z" stroke="#2E3B63" stroke-width="1.8"/><path d="M9.5 12a2.5 2.5 0 0 0 5 0" stroke="#2E3B63" stroke-width="1.6" stroke-linecap="round"/></svg>';
      if($id==='heart') return '<svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M12 20s-7-4.3-9-7.5A5 5 0 0 1 11 7a5 5 0 0 1 8 5.5C17 15.7 12 20 12 20Z" stroke="#2E3B63" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
      if($id==='bolt')  return '<svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M13 2 4 14h7l-1 8 9-12h-7l1-8Z" stroke="#2E3B63" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
      if($id==='wave')  return '<svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M3 12c2 0 2-6 4-6s2 12 4 12 2-12 4-12 2 6 4 6" stroke="#2E3B63" stroke-width="1.8" stroke-linecap="round"/></svg>';
      if($id==='beaker')return '<svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M9 3h6M10 3v5l-5.5 8.7A3 3 0 0 0 7 21h10a3 3 0 0 0 2.5-4.7L14 8V3" stroke="#2E3B63" stroke-width="1.8" stroke-linecap="round"/></svg>';
      return '';
    }
    ?>

    <div class="featured-grid">
      <?php foreach ($tests as $t): ?>
        <article class="featured-card">
          <div class="ft-icon"><?= ft_icon($t['icon']) ?></div>
          <div class="ft-body">
            <h3 class="ft-title"><?= htmlspecialchars($t['name']) ?></h3>
            <p class="ft-desc"><?= htmlspecialchars($t['desc']) ?></p>

            <div class="ft-cta">
              <!-- keep your existing routes -->
              <a class="pill-btn" href="book.php?test=<?= urlencode($t['name']) ?>">Book</a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <div class="featured-more">
      <a class="ghost-pill" href="explore.php">
        View All Tests <span class="arr">→</span>
      </a>
    </div>
  </section>

    <!-- Why Choose LabSync (premium cards) -->
  <section id="help" class="why-premium">
    <h2 class="why-title">Why Choose LabSync?</h2>
    <p class="why-sub">Patient-first healthcare that puts you in control</p>

    <div class="why-grid">
      <!-- Card 1 -->
      <article class="why-card">
        <div class="why-icon">
          <!-- shield -->
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
            <path d="M12 3l7 3v6c0 4.97-3.38 8.5-7 9-3.62-.5-7-4.03-7-9V6l7-3Z"
                  stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
            <path d="M9.5 12.5l2 2 3.5-4" stroke="currentColor" stroke-width="1.8"
                  stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <h3 class="why-card-title">Privacy Protected</h3>
        <p class="why-card-text">Your health data is encrypted and secure.</p>
      </article>

      <!-- Card 2 -->
      <article class="why-card">
        <div class="why-icon">
          <!-- clock -->
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/>
            <path d="M12 7v5l3 2" stroke="currentColor" stroke-width="1.8"
                  stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <h3 class="why-card-title">Fast Results</h3>
        <p class="why-card-text">Get results within 24-48 hours.</p>
      </article>

      <!-- Card 3 -->
      <article class="why-card">
        <div class="why-icon">
          <!-- ribbon -->
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="9" r="4" stroke="currentColor" stroke-width="1.6"/>
            <path d="M10 13l-3 8 5-3 5 3-3-8" stroke="currentColor" stroke-width="1.6"
                  stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <h3 class="why-card-title">Certified Labs</h3>
        <p class="why-card-text">All tests performed in accredited facilities.</p>
      </article>

      <!-- Card 4 -->
      <article class="why-card">
        <div class="why-icon">
          <!-- check circle -->
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/>
            <path d="M8.5 12.5l2.5 2.5 4.5-5" stroke="currentColor" stroke-width="1.8"
                  stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <h3 class="why-card-title">Expert Support</h3>
        <p class="why-card-text">24/7 help for all your questions.</p>
      </article>
    </div>
  </section>
  <?php require 'C:\xampp\htdocs\lab_sync\public\partials\footer.php'; ?>
</body>
</html>