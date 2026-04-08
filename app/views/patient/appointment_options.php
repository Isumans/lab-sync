<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Choose Booking Path - LabSync</title>
  <link rel="stylesheet" href="/lab_sync/public/css/globals.css" />
  <link rel="stylesheet" href="/lab_sync/public/css/nav.css" />
  <link rel="stylesheet" href="/lab_sync/public/css/footer.css" />
  <style>
    body { background: var(--bg-100); color: var(--neutral-700); }
    .wrap { max-width: 1100px; margin: 0 auto; padding: 8rem 1.25rem 3rem; }
    .heading { text-align: center; margin-bottom: 2rem; }
    .heading h1 { font-family: var(--font-heading); color: var(--tertiary-900); margin: 0 0 .5rem; }
    .heading p { color: var(--neutral-500); margin: 0; }

    .path-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 1.25rem;
    }

    .path-card {
      background: #fff;
      border: 1px solid var(--neutral-200);
      border-radius: var(--radius-lg);
      padding: 1.5rem;
      box-shadow: var(--shadow);
    }

    .path-card h2 { margin-top: 0; color: var(--tertiary-900); }
    .path-card p { color: var(--neutral-500); line-height: 1.6; min-height: 4rem; }

    .path-btn {
      display: inline-block;
      text-decoration: none;
      font-weight: 700;
      border-radius: var(--radius-sm);
      padding: .7rem 1rem;
    }

    .path-btn.self {
      color: #fff;
      background: var(--primary-500);
    }

    .path-btn.help {
      color: #fff;
      background: var(--tertiary-900);
    }

    .meta { margin-top: .75rem; color: var(--neutral-400); font-size: .9rem; }

    @media (max-width: 900px) {
      .path-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
  <?php require_once __DIR__ . '/../../../public/partials/header.php'; ?>

  <main class="wrap">
    <section class="heading">
      <h1>Test Appointment</h1>
      <p>Choose how you want to continue your booking.</p>
    </section>

    <section class="path-grid">
      <article class="path-card">
        <h2>Self Booking</h2>
        <p>Choose your tests, pick your slot, and confirm the appointment yourself in a few steps.</p>
        <a class="path-btn self" href="/lab_sync/index.php?controller=home&action=explore">Book Tests Myself</a>
        <div class="meta">Shows test prices and status during booking.</div>
      </article>

      <article class="path-card">
        <h2>Get Help</h2>
        <p>Upload your prescription and our receptionist will contact you and help complete your booking.</p>
        <a class="path-btn help" href="/lab_sync/index.php?controller=home&action=get_help">Upload Prescription & Get Help</a>
        <div class="meta">Receptionist-assisted path for patient guidance.</div>
      </article>
    </section>
  </main>

  <?php require_once __DIR__ . '/../../../public/partials/footer.php'; ?>
</body>
</html>
