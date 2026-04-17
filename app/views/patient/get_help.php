<?php
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
$csrfToken = (string)($csrfToken ?? ($_SESSION['csrf_token'] ?? ''));
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Prescription Help - LabSync</title>
  <link rel="stylesheet" href="/lab_sync/public/css/globals.css" />
  <link rel="stylesheet" href="/lab_sync/public/css/nav.css" />
  <link rel="stylesheet" href="/lab_sync/public/css/footer.css" />
  <style>
    body { background: var(--bg-100); color: var(--neutral-700); }
    .wrap { max-width: 860px; margin: 0 auto; padding: 8rem 1.25rem 3rem; }
    .heading h1 { margin: 0 0 .5rem; color: var(--tertiary-900); }
    .heading p { margin: 0 0 1rem; color: var(--neutral-500); }

    .flash { padding: .8rem 1rem; border-radius: var(--radius-sm); margin-bottom: 1rem; }
    .flash.success { background: #e8f7ef; color: #0f7a3d; border: 1px solid #bbe8cc; }
    .flash.error { background: #fff0f0; color: #b32525; border: 1px solid #f1c2c2; }

    .form-box {
      background: #fff;
      border: 1px solid var(--neutral-200);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow);
      padding: 1.25rem;
    }

    .grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: .9rem;
    }

    .toggle-row {
      display: flex;
      align-items: center;
      gap: .75rem;
      font-weight: 600;
      color: var(--neutral-600);
      border: 1px solid var(--neutral-300);
      border-radius: var(--radius-sm);
      background: #fff;
      padding: .7rem .75rem;
      width: fit-content;
    }

    .toggle-row input[type="checkbox"] {
      width: 18px;
      height: 18px;
      margin: 0;
      accent-color: var(--primary-500);
      flex: 0 0 auto;
    }

    .toggle-row span {
      line-height: 1.2;
    }

    label { display: block; font-weight: 600; margin-bottom: .35rem; color: var(--neutral-600); }
    input, textarea {
      width: 100%;
      border: 1px solid var(--neutral-300);
      border-radius: var(--radius-sm);
      padding: .65rem .75rem;
      font: inherit;
      background: #fff;
    }
    textarea { min-height: 110px; resize: vertical; }

    .full { grid-column: 1 / -1; }

    .actions { display: flex; gap: .75rem; margin-top: 1rem; }
    .btn {
      border: 0;
      border-radius: var(--radius-sm);
      padding: .7rem 1rem;
      font-weight: 700;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
    }
    .btn.primary { background: var(--primary-500); color: #fff; }
    .btn.primary:hover { background: var(--primary-600); }
    .btn.secondary { background: var(--neutral-200); color: var(--neutral-700); }

    .cta-box {
      margin-top: 1rem;
      background: #fff;
      border: 1px solid var(--neutral-200);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow);
      padding: 1rem 1.25rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      flex-wrap: wrap;
    }

    .cta-box p {
      margin: 0;
      color: var(--neutral-600);
      font-weight: 500;
    }

    .hint { margin-top: .75rem; color: var(--neutral-400); font-size: .9rem; }

    @media (max-width: 760px) {
      .grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
  <?php require_once __DIR__ . '/../../../public/partials/header.php'; ?>

  <main class="wrap">
    <section class="heading">
      <h1>Get Help with Booking</h1>
      <p>Upload a prescription or submit a home visit request. Receptionist will review and contact you to finalize your test appointment.</p>
    </section>

    <?php if ($success): ?>
      <div class="flash success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="flash error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <section class="form-box">
      <form action="/lab_sync/index.php?controller=home&action=submit_prescription_help" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        <div class="grid">
          <div class="full">
            <label for="prescription_file">Prescription File (Optional, PDF/JPG/PNG, max 5MB)</label>
            <input type="file" id="prescription_file" name="prescription_file" accept=".pdf,.jpg,.jpeg,.png">
          </div>

          <div class="full">
            <label class="toggle-row" for="home_collection">
              <input type="checkbox" id="home_collection" name="home_collection" value="1">
              <span>Home sample collection</span>
            </label>
          </div>

          <div class="full" id="collection_address_wrap" style="display:none;">
            <label for="collection_address">Collection Address</label>
            <input type="text" id="collection_address" name="collection_address" placeholder="Enter the address for sample collection">
          </div>

          <div class="full">
            <label for="notes">Notes for Receptionist (Optional)</label>
            <textarea id="notes" name="notes" placeholder="Any details, symptoms, or preferred tests..."></textarea>
          </div>
        </div>

        <div class="actions">
          <button type="submit" class="btn primary">Submit Request</button>
          <a class="btn secondary" href="/lab_sync/index.php?controller=home&action=appointment_options">Back</a>
        </div>

        <div class="hint">After submission, status will start as Pending and receptionist will follow up. If you do not upload a file, enable Home sample collection to submit a home visit request.</div>
      </form>
    </section>

    <section class="cta-box">
      <p>Want to check your recent prescription uploads?</p>
      <a class="btn primary" href="/lab_sync/index.php?controller=home&action=dashboard#prescription-submissions">See Recent Prescription Uploads</a>
    </section>
  </main>

  <script>
    const homeCollection = document.getElementById('home_collection');
    const collectionAddressWrap = document.getElementById('collection_address_wrap');
    const collectionAddress = document.getElementById('collection_address');

    function syncHelpCollectionField() {
      const enabled = homeCollection.checked;
      collectionAddressWrap.style.display = enabled ? 'block' : 'none';
      collectionAddress.required = enabled;
      if (!enabled) {
        collectionAddress.value = '';
      }
    }

    homeCollection.addEventListener('change', syncHelpCollectionField);
    syncHelpCollectionField();
  </script>

  <?php require_once __DIR__ . '/../../../public/partials/footer.php'; ?>
</body>
</html>
