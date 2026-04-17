<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>LabSync - Results</title>
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

    .results-wrap {
      max-width: 1100px;
      margin: 0 auto;
      padding: calc(4.75rem + 2rem) 1.5rem 4rem;
    }

    .results-title {
      margin: 0 0 0.75rem;
      font-family: var(--font-heading);
      font-size: clamp(1.75rem, 3vw, 2.25rem);
      font-weight: 700;
      color: var(--tertiary-900);
      letter-spacing: -0.02em;
    }

    .results-card {
      background: #fff;
      border: 1px solid var(--neutral-200);
      border-radius: var(--radius-lg);
      padding: 1.5rem;
      box-shadow: var(--shadow);
    }

    .results-muted {
      margin: 0;
      color: var(--neutral-500);
      line-height: 1.7;
    }
  </style>
</head>
<body>
  <?php require 'C:\xampp\htdocs\lab_sync\public\partials\header.php'; ?>
  <main class="results-wrap">
    <h2 class="results-title">Results</h2>
    <div class="results-card">
      <p class="results-muted">Connect this page to your PHP/MySQL backend later. For now this is a UI placeholder.</p>
    </div>
  </main>
  <?php require 'C:\xampp\htdocs\lab_sync\public\partials\footer.php'; ?>
</body>
</html>
