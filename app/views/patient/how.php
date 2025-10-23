<?php /* app/views/patient/how.php (clean, airy version) */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>LabSync – How It Works</title>
  <link rel="stylesheet" href="/lab_sync/public/css/patient.css" />
  <link rel="stylesheet" href="/lab_sync/public/css/nav.css" />
  
  <style>
    :root{
      --navy:#1F2B5B; --blue:#3DBDEC; --white:#fff; --bay:#2E3B63;
      --ink:#101828; --muted:#667085; --ring:#e8edf3;
    }

    /* Page-scoped styling */
    .how-hero{
      position:relative; overflow:hidden;
      background: radial-gradient(1400px 500px at 50% -200px, rgba(61,189,236,.20), transparent 60%),
                  linear-gradient(#fff, #F6F9FC);
      padding:72px 6% 44px;
      text-align:center;
      border-bottom:1px solid var(--ring);
    }
    .how-eyebrow{ color:#6B7AA1; font-weight:600; letter-spacing:.08em; text-transform:uppercase; }
    .how-title{ margin:.35rem 0; font-size:clamp(2rem, 3.6vw, 3rem); color:var(--navy); }
    .how-sub{ color:#5b6a94; max-width:880px; margin:0 auto; }

    /* Timeline */
    .steps-timeline{ display:flex; gap:18px; justify-content:center; align-items:center; margin:30px auto 10px; flex-wrap:wrap;}
    .dot{
      width:46px; height:46px; border-radius:999px; background:var(--white);
      border:2px solid var(--blue); color:var(--navy); font-weight:700; display:grid; place-items:center;
      box-shadow:0 6px 18px rgba(61,189,236,.25);
    }
    .dash{ width:70px; height:2px; background:linear-gradient(90deg, rgba(61,189,236,.35), rgba(61,189,236,1), rgba(61,189,236,.35)); }
    .step-labels{ display:flex; gap:56px; justify-content:center; flex-wrap:wrap; color:var(--muted); font-weight:600;}

    /* Wider wrapper to give more breathing room than the default container */
    .how-wrap{
      max-width:1280px;
      margin:0 auto;
      padding:40px 6% 10px;
    }

    /* Workflow rail */
    .rail{
      position:relative;
      display:grid; grid-template-columns:repeat(4,1fr);
      gap:24px;          /* more horizontal breathing room */
    }
    .rail-card{
      background:#fff; border:1px solid var(--ring); border-radius:20px; padding:22px;
      box-shadow:0 12px 28px rgba(16,24,40,.06);
      transition:transform .2s ease, box-shadow .2s ease, border-color .2s ease;
      cursor:pointer; position:relative; min-height:200px; /* taller card */
    }
    .rail-card:hover{ transform:translateY(-3px); box-shadow:0 18px 36px rgba(16,24,40,.10); border-color:#d7e6f3; }
    .ic{
      width:44px; height:44px; border-radius:12px; display:grid; place-items:center;
      background:rgba(61,189,236,.12); color:var(--bay); margin-bottom:10px;
    }
    .rail-card h4{ margin:.25rem 0 .45rem; color:var(--ink); }
    .rail-card p{ color:#5b6a94; margin:0; line-height:1.55; }
    .arrow{
      position:absolute; top:58%; transform:translateY(-50%); right:-12px; width:30px; height:30px;
      background:var(--blue); border-radius:999px; display:grid; place-items:center; color:#fff;
      box-shadow:0 10px 22px rgba(61,189,236,.35);
    }
    .rail .rail-card:last-child .arrow{ display:none; }

    /* Reveal panel */
    .reveal{
      grid-column:1 / -1; background:#F7FBFF; border:1px dashed #cfeaf6; border-radius:16px; padding:18px 18px 14px; margin-top:14px;
      display:none;
    }
    .reveal.open{ display:block; }
    .reveal h5{ margin:0 0 10px; color:#355a7a; font-size:1.05rem; }
    .reveal-grid{ display:grid; grid-template-columns:repeat(3, 1fr); gap:14px; }
    .reveal-item{ background:#fff; border:1px solid #e7f1f8; border-radius:12px; padding:14px; }
    .reveal-item strong{ display:block; color:#355a7a; margin-bottom:6px; }
    .note{ font-size:.98rem; color:#4a6b85; line-height:1.55; }

    /* CTAs */
    .cta-row{ display:flex; gap:14px; justify-content:center; margin:26px 0 34px; }
    .btn-primary{ background:var(--blue); color:#fff; border-color:var(--blue); }
    .btn-outline{ border-color:var(--blue); color:var(--bay); background:#fff; }

    /* FAQ */
    .faq{ max-width:1000px; margin:28px auto 10px; }
    .faq h3{ text-align:center; color:var(--navy); margin-bottom:16px; }
    .qa{ background:#fff; border:1px solid var(--ring); border-radius:14px; padding:12px 16px; margin-bottom:12px; }
    .qa summary{ cursor:pointer; list-style:none; color:var(--ink); font-weight:600; }
    .qa summary::-webkit-details-marker{ display:none; }
    .qa p{ color:#5b6a94; margin-top:8px; line-height:1.6; }

    @media (max-width:1100px){
      .rail{ grid-template-columns:repeat(2,1fr); }
    }
    @media (max-width:640px){
      .rail{ grid-template-columns:1fr; }
      .reveal-grid{ grid-template-columns:1fr; }
    }
  </style>
</head>
<body>

  <?php require_once PUBLIC_PATH . '/partials/header.php'; ?>

  <!-- HERO -->
  <section class="how-hero">
    <div class="how-eyebrow">Guided workflow</div>
    <h1 class="how-title">How LabSync Works</h1>
    <p class="how-sub">From selecting your test to getting your results, here’s the simple, streamlined flow built for patients.</p>

    <div class="steps-timeline">
      <div class="dot">1</div><div class="dash"></div>
      <div class="dot">2</div><div class="dash"></div>
      <div class="dot">3</div><div class="dash"></div>
      <div class="dot">4</div>
    </div>
    <div class="step-labels">
      <span>Choose Test</span><span>Pick Time</span><span>Visit Lab</span><span>Get Results</span>
    </div>
  </section>

  <!-- WIDE WRAP to avoid cramped look -->
  <div class="how-wrap">
    <!-- WORKFLOW RAIL -->
    <section class="rail" id="rail">
      <!-- 1 -->
      <article class="rail-card" data-step="1">
        <div class="ic">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M9 3h6M10 3v5l-5.5 8.7A3 3 0 0 0 7 21h10a3 3 0 0 0 2.5-4.7L14 8V3" stroke="#2E3B63" stroke-width="1.7" stroke-linecap="round"/></svg>
        </div>
        <h4>Choose Test / Package</h4>
        <p>Browse our catalog and pick the test or health package you need.</p>
        <div class="arrow">→</div>
      </article>

      <!-- 2 -->
      <article class="rail-card" data-step="2">
        <div class="ic">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="16" rx="3" stroke="#2E3B63" stroke-width="1.7"/><path d="M8 3v4M16 3v4M3 10h18" stroke="#2E3B63" stroke-width="1.7" stroke-linecap="round"/></svg>
        </div>
        <h4>Select Date & Time</h4>
        <p>Pick a convenient slot. We’ll show prep notes (e.g., fasting) if needed.</p>
        <div class="arrow">→</div>
      </article>

      <!-- 3 -->
      <article class="rail-card" data-step="3">
        <div class="ic">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M12 21s7-6.3 7-11a7 7 0 1 0-14 0c0 4.7 7 11 7 11Z" stroke="#2E3B63" stroke-width="1.7"/><circle cx="12" cy="10" r="2.5" stroke="#2E3B63" stroke-width="1.7"/></svg>
        </div>
        <h4>Visit the Lab</h4>
        <p>Arrive a few minutes early; our staff will take care of the rest.</p>
        <div class="arrow">→</div>
      </article>

      <!-- 4 -->
      <article class="rail-card" data-step="4">
        <div class="ic">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5Z" stroke="#2E3B63" stroke-width="1.7"/><path d="m9 13 2.2 2.2L15 11" stroke="#2E3B63" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <h4>Results & Follow-up</h4>
        <p>Get secure results online. Download PDFs or share with your doctor.</p>
      </article>

      <!-- reveal panel -->
      <section class="reveal" id="reveal">
        <h5 id="rvTitle">Step details</h5>
        <div class="reveal-grid">
          <div class="reveal-item">
            <strong>Preparation</strong>
            <p id="rvPrep" class="note">—</p>
          </div>
          <div class="reveal-item">
            <strong>What happens</strong>
            <p id="rvWhat" class="note">—</p>
          </div>
          <div class="reveal-item">
            <strong>Time estimate</strong>
            <p id="rvTime" class="note">—</p>
          </div>
        </div>
      </section>
    </section>

    <!-- CTAs -->
    <div class="cta-row">
      <a class="btn-primary" href="/lab_sync/app/views/patient/explore.php">Book a Test →</a>
      <a class="btn-outline" href="/lab_sync/app/views/patient/dashboard.php">Go to Dashboard</a>
    </div>

    <!-- FAQ -->
    <section class="faq">
      <h3>Frequently asked</h3>
      <details class="qa">
        <summary>Do I need a doctor’s prescription?</summary>
        <p>For most routine tests, no. If a prescription is needed, the UI will indicate it during booking.</p>
      </details>
      <details class="qa">
        <summary>How will I receive my results?</summary>
        <p>Results appear in your LabSync account with status updates. You can view, download PDF, or share securely.</p>
      </details>
      <details class="qa">
        <summary>What if I need to reschedule?</summary>
        <p>Use the Dashboard → Your Appointments. Tap “Edit/Reschedule” to pick a new slot.</p>
      </details>
    </section>
  </div>

  <?php require __DIR__ . '/../../../public/partials/footer.php'; ?>

  <script>
    // Step details (UI only)
    const detail = {
      1:{ title:'Choose Test / Package',
          prep:'Some tests include fasting or medication notes which you’ll see before booking.',
          what:'Browse tests, read descriptions and preparation tips, and add to booking.',
          time:'~1–2 minutes to choose.'
        },
      2:{ title:'Select Date & Time',
          prep:'For fasting tests (e.g., Lipid Profile), morning slots are suggested.',
          what:'Pick a preferred date and an available time slot. You’ll see any prep reminders.',
          time:'~30–60 seconds.'
        },
      3:{ title:'Visit the Lab',
          prep:'Bring your ID. Stay hydrated unless fasting is required.',
          what:'Our staff verifies details and collects the sample quickly and safely.',
          time:'~10–15 minutes on site.'
        },
      4:{ title:'Results & Follow-up',
          prep:'No prep needed. You’ll get a notification when your results are ready.',
          what:'View results online, download PDFs, and share with your physician.',
          time:'Most results within 24–48 hours.'
        }
    };

    const rail = document.getElementById('rail');
    const reveal = document.getElementById('reveal');
    const rvTitle = document.getElementById('rvTitle');
    const rvPrep  = document.getElementById('rvPrep');
    const rvWhat  = document.getElementById('rvWhat');
    const rvTime  = document.getElementById('rvTime');

    rail.addEventListener('click', (e)=>{
      const card = e.target.closest('.rail-card');
      if(!card) return;
      const step = card.getAttribute('data-step');
      const d = detail[step];
      if(!d) return;
      rvTitle.textContent = d.title;
      rvPrep.textContent  = d.prep;
      rvWhat.textContent  = d.what;
      rvTime.textContent  = d.time;
      reveal.classList.add('open');
      reveal.scrollIntoView({behavior:'smooth', block:'center'});
    });
  </script>
</body>
</html>