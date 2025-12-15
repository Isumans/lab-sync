<?php /* app/views/patient/about.php */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>About LabSync</title>
  <link rel="stylesheet" href="/lab_sync/public/css/patient.css"/>
  <link rel="stylesheet" href="/lab_sync/public/about.css"/>
</head>
<body>
  <?php require __DIR__ . '/../../../public/partials/header.php'; ?>

  <!-- ===== HERO ===== -->
  <section class="about-hero">
    <div class="about-hero-inner">
      <h1>Healthcare, simplified—built for patients</h1>
      <p>LabSync helps you book tests in seconds, get results within 24-48 hours, and keep all your health
         information safe and organized in one modern portal.</p>
      <div class="hero-cta">
        <a href="/lab_sync/app/views/patient/explore.php" class="btn-primary">Book a Test →</a>
        <a href="/lab_sync/app/views/patient/dashboard.php" class="btn-outline">Go to Dashboard</a>
      </div>
    </div>
  </section>

  <main class="container">

    <!-- ===== WHO WE ARE ===== -->
    <section class="about-intro card">
      <div class="about-grid">
        <div class="about-copy">
          <h2>Who we are</h2>
          <p class="muted">
            We’re a patient-first team of clinicians and engineers on a mission to make diagnostics
            accessible, transparent, and stress-free. From booking to results, LabSync keeps you in control.
          </p>
          <ul class="about-list">
            <li>Easy online bookings and reminders</li>
            <li>Results available within 24–48 hours</li>
            <li>Bank-grade encryption & secure sharing</li>
            <li>Accredited partner laboratories</li>
          </ul>
        </div>

        <aside class="about-stats">
          <div class="stat-card">
            <div class="stat">50k+</div>
            <div class="label">Patients served</div>
          </div>
          <div class="stat-card">
            <div class="stat">24–48h</div>
            <div class="label">Typical results window</div>
          </div>
          <div class="stat-card">
            <div class="stat">99.9%</div>
            <div class="label">Portal uptime</div>
          </div>
          <div class="stat-card">
            <div class="stat">4.8★</div>
            <div class="label">Patient rating</div>
          </div>
        </aside>
      </div>
    </section>

    <!-- ===== VALUES ===== -->
    <section class="values">
      <h2 class="section-title">Our values</h2>
      <div class="values-grid">
        <div class="value-card">
          <div class="v-ico">🔒</div>
          <h3>Privacy by design</h3>
          <p class="muted">All health data is encrypted at rest and in transit with strict access controls.</p>
        </div>

        <div class="value-card">
          <div class="v-ico">⚡️</div>
          <h3>Speed with clarity</h3>
          <p class="muted">Fast results with clear, patient-friendly reporting and notifications.</p>
        </div>

        <div class="value-card">
          <div class="v-ico">🎯</div>
          <h3>Accuracy you can trust</h3>
          <p class="muted">Every test is processed by accredited labs with robust QA workflows.</p>
        </div>

        <div class="value-card">
          <div class="v-ico">🤝</div>
          <h3>Care that listens</h3>
          <p class="muted">We design features around real patient feedback and everyday needs.</p>
        </div>
      </div>
    </section>

    <!-- ===== ACCREDITATION & SECURITY ===== -->
    <section class="about-sec card">
      <div class="about-sec-grid">
        <div>
          <h2>Accreditation & security</h2>
          <p class="muted">
            LabSync partners only with accredited facilities. We follow rigorous security practices
            including TLS 1.2+, role-based access controls, and continuous monitoring.
          </p>
          <ul class="about-list tight">
            <li>Accredited partner labs (e.g., ISO/IEC 15189 where applicable)</li>
            <li>Bank-grade encryption (AES-256 at rest, TLS in transit)</li>
            <li>Granular consent & audit trails</li>
            <li>Regular security reviews and backups</li>
          </ul>
        </div>
        <div class="about-sec-badge">
          <div class="shield">🛡️</div>
          <div class="shield-copy">
            <strong>Security first</strong>
            <p class="muted">Built to protect your privacy—always.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== CTA STRIP ===== -->
    <section class="about-cta card">
      <div class="cta-copy">
        <h2>Ready to take control of your health?</h2>
        <p class="muted">Book in seconds, get results in days, and keep everything organized.</p>
      </div>
      <div class="cta-actions">
        <a href="/lab_sync/app/views/patient/explore.php" class="btn-primary">Book a Test →</a>
        <a href="mailto:support@labsync.com" class="btn-outline">Contact Support</a>
      </div>
    </section>

  </main>

  <?php require __DIR__ . '/../../../public/partials/footer.php'; ?>
</body>
</html>
