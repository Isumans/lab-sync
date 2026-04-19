<?php /* app/views/patient/about.php */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>About LabSync – Healthcare at Your Doorstep</title>
  <meta name="description" content="Learn about LabSync — our mission to make diagnostics accessible, private, and fast. Certified labs, home visits, results in 24 hours.">
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/lab_sync/public/css/globals.css" />
  <link rel="stylesheet" href="/lab_sync/public/css/nav.css" />
  <link rel="stylesheet" href="/lab_sync/public/css/footer.css" />
  <style>
    body { font-family:var(--font-body); background:var(--bg-100); color:var(--neutral-700); -webkit-font-smoothing:antialiased; }

    /* Hero */
    .about-hero { padding:8rem 1.5rem 6rem; background:var(--tertiary-900); position:relative; overflow:hidden; }
    .about-hero::before { content:''; position:absolute; inset:0; background:radial-gradient(ellipse 130% 90% at 60% -20%, rgba(0,180,216,.2), transparent 65%); pointer-events:none; }
    .about-hero-inner { position:relative; z-index:1; max-width:1100px; margin:0 auto; display:grid; grid-template-columns:1fr 1.1fr; gap:5rem; align-items:center; }
    .ah-eyebrow { font-size:.7rem; font-weight:700; letter-spacing:.12em; text-transform:uppercase; color:var(--primary-300); margin-bottom:1rem; }
    .about-hero h1 { font-family:var(--font-heading); font-size:clamp(2rem,4.5vw,3.25rem); font-weight:800; color:#fff; letter-spacing:-.02em; line-height:1.18; margin-bottom:1.25rem; }
    .about-hero h1 .accent { background:linear-gradient(135deg,var(--primary-400),#86efac); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
    .about-hero p { font-size:1.0625rem; color:rgba(219,234,254,.75); line-height:1.75; margin-bottom:2rem; max-width:36rem; }
    .ah-buttons { display:flex; gap:1rem; flex-wrap:wrap; }
    .btn-primary-hero { display:inline-flex; align-items:center; gap:.5rem; background:var(--primary-500); color:#fff; padding:.875rem 2rem; border-radius:var(--radius-sm); font-weight:700; font-size:.95rem; text-decoration:none; transition:background .2s; }
    .btn-primary-hero:hover { background:var(--primary-600); }
    .btn-outline-hero { display:inline-flex; align-items:center; gap:.5rem; background:transparent; color:rgba(255,255,255,.8); border:1.5px solid rgba(255,255,255,.3); padding:.875rem 2rem; border-radius:var(--radius-sm); font-weight:700; font-size:.95rem; text-decoration:none; transition:border-color .2s, color .2s; }
    .btn-outline-hero:hover { border-color:#fff; color:#fff; }

    /* Stats panel */
    .ah-stats { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
    .stat-card { background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.1); border-radius:var(--radius-lg); padding:1.75rem 1.5rem; text-align:center; backdrop-filter:blur(8px); transition:background .25s; }
    .stat-card:hover { background:rgba(255,255,255,.1); }
    .stat-val { font-family:var(--font-heading); font-size:2.25rem; font-weight:800; color:#fff; line-height:1.1; margin-bottom:.25rem; }
    .stat-val.c1 { color:var(--primary-400); }
    .stat-val.c2 { color:#86efac; }
    .stat-val.c3 { color:#fbbf24; }
    .stat-label { font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.08em; color:rgba(219,234,254,.5); }

    /* Mission section */
    .mission-section { padding:5rem 1.5rem; background:#fff; }
    .mission-inner { max-width:1100px; margin:0 auto; display:grid; grid-template-columns:1fr 1fr; gap:5rem; align-items:center; }
    .section-label { font-size:.7rem; font-weight:700; letter-spacing:.12em; text-transform:uppercase; color:var(--primary-500); margin-bottom:.75rem; }
    .section-title { font-family:var(--font-heading); font-size:clamp(1.75rem,3.5vw,2.25rem); font-weight:800; color:var(--tertiary-900); letter-spacing:-.02em; line-height:1.25; margin-bottom:1rem; }
    .section-body { font-size:1rem; color:var(--neutral-500); line-height:1.8; margin-bottom:1.25rem; }
    .check-list { list-style:none; display:flex; flex-direction:column; gap:.7rem; }
    .check-list li { display:flex; align-items:center; gap:.625rem; font-size:.9375rem; font-weight:600; color:var(--neutral-600); }
    .check-list li svg { color:var(--primary-500); flex-shrink:0; }
    .mission-img { background:var(--bg-100); border:1px solid var(--neutral-200); border-radius:var(--radius-lg); padding:2.5rem; display:flex; align-items:center; justify-content:center; min-height:20rem; box-shadow:var(--shadow); position:relative; overflow:hidden; }
    .mission-img::before { content:''; position:absolute; inset:0; background:radial-gradient(circle at 60% 40%, rgba(0,180,216,.08), transparent 70%); }
    .mission-icon { width:8rem; height:8rem; border-radius:50%; background:var(--primary-50); border:4px solid var(--primary-100); display:flex; align-items:center; justify-content:center; color:var(--primary-600); position:relative; z-index:1; }

    /* Values */
    .values-section { padding:5rem 1.5rem; background:var(--bg-100); }
    .values-inner { max-width:1100px; margin:0 auto; }
    .section-header-c { text-align:center; max-width:42rem; margin:0 auto 3.5rem; }
    .section-header-c h2 { font-family:var(--font-heading); font-size:clamp(1.75rem,4vw,2.5rem); font-weight:800; color:var(--tertiary-900); letter-spacing:-.02em; margin-bottom:.75rem; }
    .section-header-c p { font-size:1.0625rem; color:var(--neutral-500); line-height:1.6; }
    .values-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:1.5rem; }
    .value-card { background:#fff; border:1px solid var(--neutral-200); border-radius:var(--radius-lg); padding:2rem 1.5rem; text-align:center; box-shadow:var(--shadow); transition:transform .25s, box-shadow .25s; }
    .value-card:hover { transform:translateY(-5px); box-shadow:var(--shadow-hover); }
    .value-icon { width:3.5rem; height:3.5rem; border-radius:50%; background:var(--primary-50); display:flex; align-items:center; justify-content:center; color:var(--primary-700); margin:0 auto 1.25rem; }
    .value-title { font-family:var(--font-heading); font-size:1rem; font-weight:700; color:var(--tertiary-900); margin-bottom:.5rem; }
    .value-text { font-size:.875rem; color:var(--neutral-500); line-height:1.6; }

    /* Security */
    .security-section { padding:5rem 1.5rem; background:#fff; }
    .security-inner { max-width:1100px; margin:0 auto; }
    .security-card { background:linear-gradient(135deg, var(--tertiary-900) 0%, #0d2144 100%); border-radius:var(--radius-lg); padding:3.5rem; display:grid; grid-template-columns:1fr auto; gap:3rem; align-items:center; position:relative; overflow:hidden; }
    .security-card::before { content:''; position:absolute; right:-4rem; top:-4rem; width:20rem; height:20rem; border-radius:50%; background:rgba(0,180,216,.06); }
    .sc-left h2 { font-family:var(--font-heading); font-size:clamp(1.5rem,3vw,2rem); font-weight:800; color:#fff; letter-spacing:-.02em; margin-bottom:.875rem; }
    .sc-left p { color:rgba(219,234,254,.7); line-height:1.75; margin-bottom:1.5rem; }
    .sc-list { list-style:none; display:flex; flex-direction:column; gap:.6rem; }
    .sc-list li { display:flex; align-items:center; gap:.6rem; font-size:.9rem; font-weight:600; color:rgba(219,234,254,.8); }
    .sc-list li svg { color:var(--primary-400); flex-shrink:0; }
    .sc-right { text-align:center; flex-shrink:0; }
    .shield-wrap { width:9rem; height:9rem; border-radius:50%; background:rgba(0,180,216,.1); border:2px solid rgba(0,180,216,.25); display:flex; align-items:center; justify-content:center; color:var(--primary-400); margin:0 auto 1rem; }
    .shield-label { font-family:var(--font-heading); font-size:.8rem; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:rgba(219,234,254,.5); }

    /* CTA */
    .cta-section { padding:5rem 1.5rem; background:var(--bg-100); text-align:center; }
    .cta-card { background:linear-gradient(135deg, var(--primary-600) 0%, var(--tertiary-900) 100%); border-radius:var(--radius-lg); padding:4rem 2rem; max-width:760px; margin:0 auto; position:relative; overflow:hidden; }
    .cta-card::before { content:''; position:absolute; right:-4rem; bottom:-4rem; width:16rem; height:16rem; border-radius:50%; background:rgba(255,255,255,.04); }
    .cta-card-inner { position:relative; z-index:1; }
    .cta-card h2 { font-family:var(--font-heading); font-size:clamp(1.5rem,3.5vw,2.25rem); font-weight:800; color:#fff; letter-spacing:-.02em; margin-bottom:.875rem; }
    .cta-card p { color:rgba(219,234,254,.75); line-height:1.7; margin-bottom:2rem; }
    .cta-btns { display:flex; gap:1rem; justify-content:center; flex-wrap:wrap; }
    .btn-w { display:inline-flex; align-items:center; gap:.5rem; background:#fff; color:var(--primary-700); padding:.875rem 2rem; border-radius:var(--radius-sm); font-weight:700; font-size:.95rem; text-decoration:none; transition:box-shadow .2s; }
    .btn-w:hover { box-shadow:0 6px 24px rgba(0,0,0,.18); }
    .btn-gw { display:inline-flex; align-items:center; gap:.5rem; background:transparent; color:rgba(255,255,255,.85); border:1.5px solid rgba(255,255,255,.35); padding:.875rem 2rem; border-radius:var(--radius-sm); font-weight:700; font-size:.95rem; text-decoration:none; transition:border-color .2s, color .2s; }
    .btn-gw:hover { border-color:#fff; color:#fff; }

    @media (max-width:1024px) { .values-grid { grid-template-columns:repeat(2,1fr); } }
    @media (max-width:900px) { .about-hero-inner { grid-template-columns:1fr; gap:3rem; } .mission-inner { grid-template-columns:1fr; gap:2.5rem; } .security-card { grid-template-columns:1fr; } }
    @media (max-width:640px) { .values-grid { grid-template-columns:1fr; } }
  </style>
</head>
<body>
  <?php require_once __DIR__ . '/../../../public/partials/header.php'; ?>

  <!-- HERO -->
  <section class="about-hero">
    <div class="about-hero-inner">
      <div class="ah-left">
        <p class="ah-eyebrow">Our Story</p>
        <h1>Healthcare Simplified,<br><span class="accent">Built for Patients</span></h1>
        <p>LabSync helps you book tests in seconds, get certified results within 24 hours, and keep all your health information safe and organized — without ever visiting a clinic.</p>
        <div class="ah-buttons">
          <a href="/lab_sync/index.php?controller=home&action=explore" class="btn-primary-hero">Book a Test <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
          <a href="/lab_sync/index.php?controller=home&action=how" class="btn-outline-hero">How it works</a>
        </div>
      </div>
      <div class="ah-stats">
        <div class="stat-card">
          <div class="stat-val c1">50k+</div>
          <div class="stat-label">Happy Patients</div>
        </div>
        <div class="stat-card">
          <div class="stat-val">24hr</div>
          <div class="stat-label">Turnaround Time</div>
        </div>
        <div class="stat-card">
          <div class="stat-val c2">99.9%</div>
          <div class="stat-label">Portal Uptime</div>
        </div>
        <div class="stat-card">
          <div class="stat-val c3">4.8★</div>
          <div class="stat-label">Patient Rating</div>
        </div>
      </div>
    </div>
  </section>

  <!-- MISSION -->
  <section class="mission-section">
    <div class="mission-inner">
      <div>
        <p class="section-label">Who We Are</p>
        <h2 class="section-title">A Patient-First Team You Can Trust</h2>
        <p class="section-body">We're on a mission to make diagnostics accessible, transparent, and stress-free. From booking to results, LabSync keeps you in complete control of your health journey.</p>
        <ul class="check-list">
          <li><svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg> Online booking with instant confirmations</li>
          <li><svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg> Results available within 24–48 hours</li>
          <li><svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg> Your reports stay private in your LabSync account</li>
          <li><svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg> Consistent quality checks for every test</li>
        </ul>
      </div>
      <div class="mission-img">
        <div class="mission-icon">
          <svg width="60" height="60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 3l7 3v6c0 4.97-3.38 8.5-7 9-3.62-.5-7-4.03-7-9V6l7-3Z" stroke-linejoin="round"/><path d="M9.5 12.5l2 2 3.5-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
      </div>
    </div>
  </section>

  <!-- VALUES -->
  <section class="values-section">
    <div class="values-inner">
      <div class="section-header-c">
        <h2>Our Core Values</h2>
        <p>Every decision we make is guided by these principles — designed around real patient needs.</p>
      </div>
      <div class="values-grid">
        <div class="value-card">
          <div class="value-icon">
            <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M12 3l7 3v6c0 4.97-3.38 8.5-7 9-3.62-.5-7-4.03-7-9V6l7-3Z" stroke-linejoin="round"/><path d="M9.5 12.5l2 2 3.5-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </div>
          <h3 class="value-title">Your Data, Kept Private</h3>
          <p class="value-text">Your reports are protected inside your account and shown only to authorized users.</p>
        </div>
        <div class="value-card">
          <div class="value-icon">
            <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </div>
          <h3 class="value-title">Speed with Clarity</h3>
          <p class="value-text">Fast results with clear, patient-friendly reporting and proactive notifications.</p>
        </div>
        <div class="value-card">
          <div class="value-icon">
            <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><circle cx="12" cy="9" r="4"/><path d="M10 13l-3 8 5-3 5 3-3-8" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </div>
          <h3 class="value-title">Accuracy You Trust</h3>
          <p class="value-text">Every test follows quality-check workflows before results are released.</p>
        </div>
        <div class="value-card">
          <div class="value-icon">
            <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78z"/></svg>
          </div>
          <h3 class="value-title">Care That Listens</h3>
          <p class="value-text">Features built around real patient feedback and real-world everyday needs.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- SECURITY -->
  <section class="security-section">
    <div class="security-inner">
      <div class="security-card">
        <div class="sc-left">
          <h2>Quality &amp; Data Protection</h2>
          <p>We follow clear quality processes and practical protections to keep your medical information private.</p>
          <ul class="sc-list">
            <li><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg> Every report goes through quality checks before release</li>
            <li><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg> Your dashboard is protected with account login controls</li>
            <li><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg> Report access is limited to authorized staff and the patient</li>
            <li><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg> You control when to download or share your report</li>
          </ul>
        </div>
        <div class="sc-right">
          <div class="shield-wrap">
            <svg width="56" height="56" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 3l7 3v6c0 4.97-3.38 8.5-7 9-3.62-.5-7-4.03-7-9V6l7-3Z" stroke-linejoin="round"/><path d="M9.5 12.5l2 2 3.5-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </div>
          <p class="shield-label">Privacy Matters</p>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="cta-section">
    <div class="cta-card">
      <div class="cta-card-inner">
        <h2>Ready to Take Control of Your Health?</h2>
        <p>Book in seconds, get results in days, and keep everything organized in one secure place.</p>
        <div class="cta-btns">
          <a href="/lab_sync/index.php?controller=home&action=explore" class="btn-w">Book a Test <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
          <a href="mailto:support@labsync.com" class="btn-gw">Contact Support</a>
        </div>
      </div>
    </div>
  </section>

  <?php require_once __DIR__ . '/../../../public/partials/footer.php'; ?>
</body>
</html>
