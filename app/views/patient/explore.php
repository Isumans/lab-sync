<?php /* app/views/patient/explore.php */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Tests & Screenings – LabSync</title>
  <meta name="description" content="Browse and book from 150+ certified lab tests with home sample collection. LabSync — Precision Diagnostics at Your Doorstep.">

  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="/lab_sync/public/css/globals.css" />
  <link rel="stylesheet" href="/lab_sync/public/css/nav.css" />
  <link rel="stylesheet" href="/lab_sync/public/css/footer.css" />

  <style>
    body {
      font-family: var(--font-body);
      background: var(--bg-100);
      color: var(--neutral-700);
      -webkit-font-smoothing: antialiased;
    }

    /* ---- Page Hero ---- */
    .page-hero {
      padding: 7rem 1.5rem 4rem;
      background: var(--tertiary-900);
      position: relative;
      overflow: hidden;
      text-align: center;
    }
    .page-hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(ellipse 120% 80% at 50% -30%, rgba(0,180,216,.25), transparent 70%);
      pointer-events: none;
    }
    .page-hero-inner { position: relative; z-index: 1; max-width: 48rem; margin: 0 auto; }
    .page-eyebrow {
      display: inline-flex; align-items: center; gap: 0.5rem;
      padding: 0.3rem 1rem; border-radius: 9999px;
      border: 1px solid rgba(0,180,216,.3);
      background: rgba(0,180,216,.08);
      font-size: 0.7rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase;
      color: var(--primary-300); margin-bottom: 1.25rem;
    }
    .page-hero h1 {
      font-family: var(--font-heading);
      font-size: clamp(2rem, 5vw, 3rem);
      font-weight: 800; color: #fff;
      letter-spacing: -.02em; line-height: 1.2;
      margin-bottom: 1rem;
    }
    .page-hero p {
      font-size: 1.0625rem; color: rgba(219,234,254,.75);
      line-height: 1.7; max-width: 38rem; margin: 0 auto 2rem;
    }

    /* ---- Test Grid ---- */
    .tests-section { padding: 3rem 1.5rem 5rem; }
    .tests-inner { max-width: 1100px; margin: 0 auto; }
    .catalog-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1.5rem;
    }
    .test-card {
      background: #fff;
      border: 1px solid var(--neutral-200);
      border-radius: var(--radius-lg);
      padding: 1.5rem;
      display: flex; flex-direction: column; gap: 1rem;
      transition: box-shadow .25s, transform .25s, border-color .25s;
      position: relative; overflow: hidden;
    }
    .test-card::before {
      content: '';
      position: absolute; top: 0; left: 0; right: 0; height: 3px;
      background: linear-gradient(90deg, var(--primary-400), var(--primary-600));
      opacity: 0; transition: opacity .25s;
    }
    .test-card:hover { box-shadow: var(--shadow-hover); transform: translateY(-3px); border-color: var(--primary-200); }
    .test-card:hover::before { opacity: 1; }
    .test-card-top { display: flex; align-items: flex-start; gap: 1rem; }
    .test-icon {
      width: 3rem; height: 3rem; min-width: 3rem;
      background: var(--primary-50); border-radius: var(--radius-sm);
      display: flex; align-items: center; justify-content: center;
      color: var(--primary-600);
    }
    .test-info { flex: 1; }
    .test-name {
      font-family: var(--font-heading); font-size: 1rem; font-weight: 700;
      color: var(--tertiary-900); margin-bottom: 0.25rem; line-height: 1.3;
    }
    .test-cat {
      font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em;
      color: var(--primary-600);
    }
    .test-desc { font-size: 0.875rem; color: var(--neutral-500); line-height: 1.6; }
    .test-card-footer {
      display: flex; align-items: center; justify-content: space-between;
      padding-top: 0.75rem; border-top: 1px solid var(--neutral-100);
    }
    .home-badge {
      display: inline-flex; align-items: center; gap: 0.3rem;
      font-size: 0.7rem; font-weight: 700; color: var(--primary-600);
      background: var(--primary-50); border-radius: 9999px; padding: 0.2rem 0.6rem;
    }
    .btn-book {
      background: var(--primary-500); color: #fff;
      padding: 0.5rem 1.25rem; border-radius: var(--radius-sm);
      font-size: 0.85rem; font-weight: 700; text-decoration: none;
      transition: background .2s; border: none; cursor: pointer; font-family: var(--font-body);
    }
    .btn-book:hover { background: var(--primary-600); }

    .no-results {
      grid-column: 1 / -1; text-align: center; padding: 4rem 0;
      display: block;
    }
    .no-results svg { color: var(--neutral-300); margin-bottom: 1rem; }
    .no-results p { color: var(--neutral-400); font-size: 0.975rem; }

    /* Modal */
    .modal { position: fixed; inset: 0; background: rgba(10,25,47,.55); display: none; place-items: center; padding: 1rem; z-index: 100; }
    .modal.open { display: grid; }
    .modal-sheet { width: min(540px, 96vw); background: #fff; border-radius: var(--radius-lg); box-shadow: 0 20px 60px rgba(10,25,47,.2); overflow: hidden; }
    .modal-head { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--neutral-200); }
    .modal-head h3 { font-family: var(--font-heading); font-size: 1.1rem; font-weight: 700; color: var(--tertiary-900); }
    .modal-x { background: var(--neutral-100); border: none; border-radius: var(--radius-sm); width: 2rem; height: 2rem; cursor: pointer; font-size: 1.1rem; color: var(--neutral-500); display: flex; align-items: center; justify-content: center; }
    .modal-body { padding: 1.5rem; }
    .modal-body p { color: var(--neutral-500); line-height: 1.7; margin-bottom: 1.25rem; }
    .modal-info { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
    .modal-info-card { background: var(--bg-100); border: 1px solid var(--neutral-200); border-radius: var(--radius-sm); padding: 0.875rem; }
    .modal-info-card strong { display: block; font-size: 0.75rem; font-weight: 700; color: var(--neutral-500); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 0.3rem; }
    .modal-info-card span { font-size: 0.9rem; color: var(--tertiary-900); font-weight: 600; }
    .modal-foot { display: flex; justify-content: flex-end; gap: 0.75rem; padding: 1rem 1.5rem; border-top: 1px solid var(--neutral-200); }
    .btn-outline-modal { background: none; border: 1.5px solid var(--neutral-300); color: var(--neutral-600); padding: 0.6rem 1.25rem; border-radius: var(--radius-sm); font-weight: 600; cursor: pointer; font-family: var(--font-body); transition: border-color .2s; }
    .btn-outline-modal:hover { border-color: var(--primary-400); }

    @media (max-width: 1024px) { .catalog-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 640px)  { .catalog-grid { grid-template-columns: 1fr; } .filter-inner { gap: 0.75rem; } }
  </style>
</head>
<body>

  <?php require_once __DIR__ . '/../../../public/partials/header.php'; ?>

  <!-- PAGE HERO -->
  <section class="page-hero">
    <div class="page-hero-inner">
      <div class="page-eyebrow">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 3h6M10 3v5l-5.5 8.7A3 3 0 0 0 7 21h10a3 3 0 0 0 2.5-4.7L14 8V3"/></svg>
        150+ Certified Tests
      </div>
      <h1>Find Your Test</h1>
      <p>Comprehensive screenings and panels — all available with home sample collection by our trained team.</p>
    </div>
  </section>

  <!-- TESTS GRID -->
  <section class="tests-section">
    <div class="tests-inner">
      <div class="catalog-grid" id="catalogGrid">

        <?php foreach ($tests as $t): ?>
        <article class="test-card">
          <div class="test-card-top">
            <div class="test-icon">
              <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M9 3h6M10 3v5l-5.5 8.7A3 3 0 0 0 7 21h10a3 3 0 0 0 2.5-4.7L14 8V3" stroke-linecap="round"/></svg>
            </div>
            <div class="test-info">
              <p class="test-name"><?= htmlspecialchars($t['test_name']) ?></p>
              <p class="test-cat"><?= htmlspecialchars($t['category'] ?? 'Lab Test') ?></p>
            </div>
          </div>
          <p class="test-desc"><?= htmlspecialchars($t['description']) ?></p>
          <div class="test-card-footer">
            <span class="home-badge">
              <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
              Home visit
            </span>
            <a class="btn-book" href="/lab_sync/index.php?controller=home&action=book&test=<?= urlencode($t['test_id']) ?>">Book Test</a>
          </div>
        </article>
        <?php endforeach; ?>

        <?php if (count($tests) === 0): ?>
          <div class="no-results" id="noResults">
            <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <p>No tests matched your search. Try a different keyword.</p>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </section>

  <!-- MODAL -->
  <div id="testModal" class="modal">
    <div class="modal-sheet">
      <div class="modal-head">
        <h3 id="mTitle">Test Details</h3>
        <button class="modal-x" onclick="closeTestModal()">✕</button>
      </div>
      <div class="modal-body">
        <p id="mDesc"></p>
        <div class="modal-info">
          <div class="modal-info-card">
            <strong>Preparation</strong>
            <span id="mPrep">—</span>
          </div>
          <div class="modal-info-card">
            <strong>Turnaround</strong>
            <span id="mDur">—</span>
          </div>
        </div>
      </div>
      <div class="modal-foot">
        <button class="btn-outline-modal" onclick="closeTestModal()">Close</button>
        <a id="mBook" class="btn-book" href="#">Book Home Visit</a>
      </div>
    </div>
  </div>

  <?php require_once __DIR__ . '/../../../public/partials/footer.php'; ?>

  <script>
    // --- Modal ---
    function openTestModal(name, desc, prep, dur, bookUrl) {
      document.getElementById('mTitle').textContent = name;
      document.getElementById('mDesc').textContent  = desc || 'Comprehensive lab test available as a home visit.';
      document.getElementById('mPrep').textContent  = prep || 'No special preparation required.';
      document.getElementById('mDur').textContent   = dur  || '24–48 hours';
      document.getElementById('mBook').href         = bookUrl;
      document.getElementById('testModal').classList.add('open');
    }
    function closeTestModal() {
      document.getElementById('testModal').classList.remove('open');
    }
    document.getElementById('testModal').addEventListener('click', e => {
      if (e.target === e.currentTarget) closeTestModal();
    });
  </script>
</body>
</html>
