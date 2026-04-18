<?php /* app/views/patient/how.php */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>How It Works – LabSync</title>
  <meta name="description" content="From booking to digital results in 24 hours. See how LabSync's home-visit lab testing works.">
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/lab_sync/public/css/globals.css" />
  <link rel="stylesheet" href="/lab_sync/public/css/nav.css" />
  <link rel="stylesheet" href="/lab_sync/public/css/footer.css" />
  <style>
    body { font-family: var(--font-body); background: var(--bg-100); color: var(--neutral-700); -webkit-font-smoothing: antialiased; }

    .page-hero { padding: 7rem 1.5rem 5rem; background: var(--tertiary-900); position: relative; overflow: hidden; text-align: center; }
    .page-hero::before { content:''; position:absolute; inset:0; background:radial-gradient(ellipse 120% 80% at 50% -30%, rgba(0,180,216,.22), transparent 70%); pointer-events:none; }
    .page-hero-inner { position:relative; z-index:1; max-width:50rem; margin:0 auto; }
    .page-eyebrow { display:inline-flex; align-items:center; gap:.5rem; padding:.3rem 1rem; border-radius:9999px; border:1px solid rgba(0,180,216,.3); background:rgba(0,180,216,.08); font-size:.7rem; font-weight:700; letter-spacing:.12em; text-transform:uppercase; color:var(--primary-300); margin-bottom:1.25rem; }
    .page-hero h1 { font-family:var(--font-heading); font-size:clamp(2rem,5vw,3.25rem); font-weight:800; color:#fff; letter-spacing:-.02em; line-height:1.2; margin-bottom:1rem; }
    .page-hero p { font-size:1.0625rem; color:rgba(219,234,254,.75); line-height:1.7; max-width:40rem; margin:0 auto 2.5rem; }

    .step-progress { display:flex; align-items:center; justify-content:center; gap:0; margin-bottom:1rem; }
    .sp-col { display:flex; flex-direction:column; align-items:center; }
    .sp-dot { width:2.75rem; height:2.75rem; border-radius:50%; background:rgba(0,180,216,.15); border:2px solid rgba(0,180,216,.4); color:var(--primary-300); font-family:var(--font-heading); font-weight:800; font-size:1rem; display:flex; align-items:center; justify-content:center; }
    .sp-label { display:block; font-size:.7rem; font-weight:600; color:rgba(219,234,254,.55); margin-top:.35rem; white-space:nowrap; }
    .sp-line { width:5rem; height:1px; background:rgba(0,180,216,.25); flex-shrink:0; }

    .workflow-section { padding:5rem 1.5rem; }
    .workflow-inner { max-width:1100px; margin:0 auto; }
    .workflow-step { display:grid; grid-template-columns:1fr 1fr; gap:4rem; align-items:center; margin-bottom:5rem; }
    .workflow-step:last-child { margin-bottom:0; }
    .workflow-step.reverse { direction:rtl; }
    .workflow-step.reverse > * { direction:ltr; }

    .ws-visual { background:#fff; border:1px solid var(--neutral-200); border-radius:var(--radius-lg); padding:2.5rem; box-shadow:var(--shadow); display:flex; align-items:center; justify-content:center; min-height:16rem; position:relative; overflow:hidden; }
    .ws-visual::before { content:''; position:absolute; inset:0; background:radial-gradient(circle at 70% 30%, rgba(0,180,216,.06), transparent 65%); }
    .ws-icon-big { width:7rem; height:7rem; border-radius:50%; background:var(--primary-50); border:4px solid var(--primary-100); display:flex; align-items:center; justify-content:center; color:var(--primary-600); position:relative; z-index:1; }

    .step-num { font-family:var(--font-heading); font-size:.75rem; font-weight:800; text-transform:uppercase; letter-spacing:.12em; color:var(--primary-500); margin-bottom:.5rem; }
    .ws-content h2 { font-family:var(--font-heading); font-size:clamp(1.5rem,3vw,2rem); font-weight:800; color:var(--tertiary-900); letter-spacing:-.02em; line-height:1.2; margin-bottom:.875rem; }
    .ws-content p { font-size:1rem; color:var(--neutral-500); line-height:1.75; margin-bottom:1.25rem; }
    .ws-highlights { list-style:none; display:flex; flex-direction:column; gap:.6rem; }
    .ws-highlights li { display:flex; align-items:center; gap:.6rem; font-size:.9rem; font-weight:600; color:var(--neutral-600); }
    .ws-highlights li svg { color:var(--primary-500); flex-shrink:0; }

    .cta-section { background:linear-gradient(135deg, var(--primary-600) 0%, var(--tertiary-900) 100%); padding:5rem 1.5rem; text-align:center; position:relative; overflow:hidden; }
    .cta-section::before { content:''; position:absolute; right:-6rem; top:-6rem; width:24rem; height:24rem; border-radius:50%; background:rgba(255,255,255,.04); }
    .cta-inner { position:relative; z-index:1; max-width:40rem; margin:0 auto; }
    .cta-inner h2 { font-family:var(--font-heading); font-size:clamp(1.75rem,4vw,2.5rem); font-weight:800; color:#fff; letter-spacing:-.02em; margin-bottom:.875rem; }
    .cta-inner p { color:rgba(219,234,254,.75); line-height:1.7; margin-bottom:2rem; }
    .cta-buttons { display:flex; gap:1rem; justify-content:center; flex-wrap:wrap; }
    .btn-white { display:inline-flex; align-items:center; gap:.5rem; background:#fff; color:var(--primary-700); padding:.875rem 2rem; border-radius:var(--radius-sm); font-weight:700; font-size:.95rem; text-decoration:none; transition:box-shadow .2s; }
    .btn-white:hover { box-shadow:0 6px 24px rgba(0,0,0,.18); }
    .btn-ghost-white { display:inline-flex; align-items:center; gap:.5rem; background:transparent; color:rgba(255,255,255,.85); padding:.875rem 2rem; border-radius:var(--radius-sm); border:1.5px solid rgba(255,255,255,.35); font-weight:700; font-size:.95rem; text-decoration:none; transition:border-color .2s, color .2s; }
    .btn-ghost-white:hover { border-color:#fff; color:#fff; }

    .faq-section { padding:5rem 1.5rem; background:#fff; }
    .faq-inner { max-width:760px; margin:0 auto; }
    .section-heading { font-family:var(--font-heading); font-size:clamp(1.5rem,3vw,2rem); font-weight:800; color:var(--tertiary-900); letter-spacing:-.02em; text-align:center; margin-bottom:2.5rem; }
    .faq-item { background:var(--bg-100); border:1px solid var(--neutral-200); border-radius:var(--radius-sm); margin-bottom:.75rem; overflow:hidden; transition:border-color .2s; }
    .faq-item:hover { border-color:var(--primary-200); }
    .faq-q { display:flex; align-items:center; justify-content:space-between; padding:1.1rem 1.25rem; cursor:pointer; font-weight:600; color:var(--neutral-700); font-size:.975rem; user-select:none; list-style:none; }
    .faq-q::-webkit-details-marker { display:none; }
    .faq-icon { transition:transform .25s; flex-shrink:0; color:var(--primary-500); }
    details[open] .faq-icon { transform:rotate(45deg); }
    .faq-a { padding:0 1.25rem 1.1rem; color:var(--neutral-500); line-height:1.75; font-size:.9375rem; }

    @media (max-width:900px) { .workflow-step { grid-template-columns:1fr; gap:2rem; } .workflow-step.reverse { direction:ltr; } }
    @media (max-width:480px) { .sp-line { width:2.5rem; } }
  </style>
</head>
<body>
  <?php require_once __DIR__ . '/../../../public/partials/header.php'; ?>

  <section class="page-hero">
    <div class="page-hero-inner">
      <div class="page-eyebrow">Guided Workflow</div>
      <h1>From Booking to<br>Results in 24 Hours</h1>
      <p>No clinics, no queues. Our trained sample-collection staff come to you — here's exactly how it works.</p>
      <div class="step-progress">
        <div class="sp-col"><div class="sp-dot">1</div><span class="sp-label">Choose Test</span></div>
        <div class="sp-line"></div>
        <div class="sp-col"><div class="sp-dot">2</div><span class="sp-label">Pick Slot</span></div>
        <div class="sp-line"></div>
        <div class="sp-col"><div class="sp-dot">3</div><span class="sp-label">We Visit</span></div>
        <div class="sp-line"></div>
        <div class="sp-col"><div class="sp-dot">4</div><span class="sp-label">Get Results</span></div>
      </div>
    </div>
  </section>

  <section class="workflow-section">
    <div class="workflow-inner">

      <div class="workflow-step">
        <div class="ws-visual">
          <div class="ws-icon-big">
            <svg width="52" height="52" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          </div>
        </div>
        <div class="ws-content">
          <p class="step-num">Step 01</p>
          <h2>Browse & Choose Your Test</h2>
          <p>Search our catalogue of 150+ certified tests. Filter by health category, read preparation notes, and select what you need — all in under a minute.</p>
          <ul class="ws-highlights">
            <li><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg> 150+ tests &amp; health panels</li>
            <li><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg> Clear descriptions &amp; prep instructions</li>
            <li><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg> No prescription needed for most tests</li>
          </ul>
        </div>
      </div>

      <div class="workflow-step reverse">
        <div class="ws-visual">
          <div class="ws-icon-big">
            <svg width="52" height="52" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="3"/><path d="M8 3v4M16 3v4M3 10h18" stroke-linecap="round"/></svg>
          </div>
        </div>
        <div class="ws-content">
          <p class="step-num">Step 02</p>
          <h2>Select a Home-Visit Slot</h2>
          <p>Pick any convenient time slot. Real-time availability for your area. For fasting tests, we suggest early-morning slots and send prep reminders the night before.</p>
          <ul class="ws-highlights">
            <li><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg> Same-day slots available</li>
            <li><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg> SMS &amp; email confirmation</li>
            <li><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg> Free rescheduling up to 2 hours before</li>
          </ul>
        </div>
      </div>

      <div class="workflow-step">
        <div class="ws-visual">
          <div class="ws-icon-big">
            <svg width="52" height="52" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          </div>
        </div>
        <div class="ws-content">
          <p class="step-num">Step 03</p>
          <h2>We Come to Your Home</h2>
          <p>A trained sample-collection staff member arrives at your doorstep. The collection is quick and simple, so no clinic visit is needed.</p>
          <ul class="ws-highlights">
            <li><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg> Trained &amp; ID-verified sample collection staff</li>
            <li><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg> Sterile equipment, sealed packaging</li>
            <li><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg> Real-time technician tracking</li>
          </ul>
        </div>
      </div>

      <div class="workflow-step reverse">
        <div class="ws-visual">
          <div class="ws-icon-big">
            <svg width="52" height="52" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5Z"/><path d="m9 13 2.2 2.2L15 11" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </div>
        </div>
        <div class="ws-content">
          <p class="step-num">Step 04</p>
          <h2>Receive Digital Results</h2>
          <p>You'll get a notification the moment results are ready. View your detailed report, download a PDF, or share it directly with your doctor — all from your secure dashboard.</p>
          <ul class="ws-highlights">
            <li><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg> Most results within 24 hours</li>
            <li><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg> Download PDF report</li>
            <li><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg> Securely share with your physician</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <section class="cta-section">
    <div class="cta-inner">
      <h2>Ready for Lab Testing at Your Doorstep?</h2>
      <p>Join 50,000+ patients who've switched to a smarter, more convenient way to manage their health.</p>
      <div class="cta-buttons">
        <a href="/lab_sync/index.php?controller=home&action=explore" class="btn-white">Browse Tests <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
        <a href="/lab_sync/index.php?controller=Auth&action=patient_signup" class="btn-ghost-white">Create Free Account</a>
      </div>
    </div>
  </section>

  <section class="faq-section">
    <div class="faq-inner">
      <h2 class="section-heading">Frequently Asked Questions</h2>
      <details class="faq-item">
        <summary class="faq-q">Do I need a doctor's prescription? <svg class="faq-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></summary>
        <p class="faq-a">For most routine tests, no prescription is required. If a test does require a referral, you'll be notified clearly during booking.</p>
      </details>
      <details class="faq-item">
        <summary class="faq-q">How will I receive my results? <svg class="faq-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></summary>
        <p class="faq-a">Results appear in your LabSync dashboard with SMS & email notifications. You can view, download as PDF, or share securely with your physician.</p>
      </details>
      <details class="faq-item">
        <summary class="faq-q">Can I reschedule or cancel my visit? <svg class="faq-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></summary>
        <p class="faq-a">Yes — free rescheduling or cancellation from Dashboard → Appointments, up to 2 hours before the visit.</p>
      </details>
      <details class="faq-item">
        <summary class="faq-q">What areas do you cover? <svg class="faq-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></summary>
        <p class="faq-a">We currently serve Colombo and surrounding districts and expanding rapidly. Enter your postal code during booking to check availability.</p>
      </details>
      <details class="faq-item">
        <summary class="faq-q">Is my health data secure? <svg class="faq-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></summary>
        <p class="faq-a">Your health information is kept private in your LabSync account, and access is limited to authorized users only.</p>
      </details>
    </div>
  </section>

  <?php require_once __DIR__ . '/../../../public/partials/footer.php'; ?>
</body>
</html>