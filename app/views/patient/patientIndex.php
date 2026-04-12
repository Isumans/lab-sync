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

  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="/lab_sync/public/css/globals.css" />
  <link rel="stylesheet" href="/lab_sync/public/css/nav.css" />
  <link rel="stylesheet" href="/lab_sync/public/css/home.css" />
  <link rel="stylesheet" href="/lab_sync/public/css/footer.css" />
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
        Skip the clinic queue. Our certified phlebotomists visit your home, collect your samples, and deliver digital results — all within 24 hours.
      </p>

      <!-- Search bar -->
      <div class="hero-search glass-card">
        <div class="hero-search-wrap">
          <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          <input type="text" placeholder="Search test e.g., CBC, Lipid Panel, Vitamin D…">
        </div>
        <button class="btn-search" onclick="location.href='/lab_sync/index.php?controller=home&action=explore'">
          Book Home Visit
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </button>
      </div>
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
        <p>No more waiting rooms. Our trained phlebotomists arrive at a time that suits you, collect your blood or urine sample, and your results appear securely in your dashboard — fast.</p>
        <div class="home-banner-features">
          <span>
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg>
            Same-day slots available
          </span>
          <span>
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg>
            Certified phlebotomists
          </span>
          <span>
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg>
            Digital results in 24 hrs
          </span>
        </div>
      </div>
      <a href="/lab_sync/index.php?controller=home&action=explore" class="home-banner-cta">
        Schedule a Home Visit
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
        <p>Our certified phlebotomist arrives at your home at the scheduled time for a quick, professional collection.</p>
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
    $tests = [
      ['name'=>'Full Blood Count (FBC)',     'desc'=>'Comprehensive blood health analysis',    'icon'=>'drop'],
      ['name'=>'Lipid Profile',              'desc'=>'Heart health and cholesterol screening', 'icon'=>'heart'],
      ['name'=>'Fasting Blood Sugar (FBS)',  'desc'=>'Diabetes screening and monitoring',      'icon'=>'bolt'],
      ['name'=>'Thyroid Panel (TSH/T3/T4)', 'desc'=>'Complete thyroid hormone analysis',      'icon'=>'wave'],
      ['name'=>'Liver Function Test (LFT)',  'desc'=>'Liver health assessment',                'icon'=>'beaker'],
      ['name'=>'Kidney Function Test (KFT)','desc'=>'Kidney health evaluation',               'icon'=>'beaker'],
    ];

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
      <?php foreach ($tests as $t): ?>
        <article class="featured-card">
          <div class="ft-icon"><?= ft_icon($t['icon']) ?></div>
          <div class="ft-body">
            <h3 class="ft-title"><?= htmlspecialchars($t['name']) ?></h3>
            <p class="ft-desc"><?= htmlspecialchars($t['desc']) ?></p>
            <div class="ft-cta">
              <a class="pill-btn" href="/lab_sync/index.php?controller=booking&action=create&test=<?= urlencode($t['name']) ?>">
                Book Home Visit
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
        <p class="why-card-text">All tests performed in accredited facilities.</p>
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


  <!-- =============================================
       HEALTH TRENDS (Blog)
       ============================================= -->
  <section class="blog-section">
    <div class="container" style="max-width:1100px">
      <div class="section-header-row">
        <div>
          <h2 style="font-family:var(--font-heading);font-size:clamp(1.75rem,4vw,2.5rem);font-weight:800;color:var(--tertiary-900);letter-spacing:-0.02em;margin-bottom:0.25rem;">Health Trends</h2>
          <p style="color:var(--neutral-500);">Insights and education from our clinical experts.</p>
        </div>
        <a href="index.php?controller=blog&action=index" class="view-all">
          View all articles
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
        </a>
      </div>

      <div class="blog-grid">
        <div class="blog-card">
          <div class="blog-img">
            <img src="https://images.unsplash.com/photo-1505576399279-565b52d4ac71?auto=format&fit=crop&w=600&q=80" alt="Heart Health">
            <span class="blog-chip">Cardiology</span>
          </div>
          <div class="blog-body">
            <p class="blog-meta">Oct 12, 2023 · 5 min read</p>
            <h3 class="blog-title">Understanding Your Lipid Panel Results</h3>
            <p class="blog-excerpt">A comprehensive guide to decoding your cholesterol levels and what they mean for your long-term heart health.</p>
          </div>
        </div>
        <div class="blog-card">
          <div class="blog-img">
            <img src="https://images.unsplash.com/photo-1512069772995-ec65ed45afd6?auto=format&fit=crop&w=600&q=80" alt="Vitamins">
            <span class="blog-chip">Nutrition</span>
          </div>
          <div class="blog-body">
            <p class="blog-meta">Oct 08, 2023 · 4 min read</p>
            <h3 class="blog-title">The Importance of Vitamin D in Winter</h3>
            <p class="blog-excerpt">Why seasonal changes affect your vitamin levels and how a simple blood test can help you optimise your immunity.</p>
          </div>
        </div>
        <div class="blog-card">
          <div class="blog-img">
            <img src="https://images.unsplash.com/photo-1579684385127-1ef15d508118?auto=format&fit=crop&w=600&q=80" alt="Lab Tech">
            <span class="blog-chip">Technology</span>
          </div>
          <div class="blog-body">
            <p class="blog-meta">Oct 01, 2023 · 6 min read</p>
            <h3 class="blog-title">How Automation is Speeding Up Diagnostics</h3>
            <p class="blog-excerpt">Inside the modern lab: how robotics and AI are ensuring your test results are delivered faster and more accurately.</p>
          </div>
        </div>
      </div>
    </div>
  </section>


  <?php require_once __DIR__ . '/../../../public/partials/footer.php'; ?>

</body>
</html>