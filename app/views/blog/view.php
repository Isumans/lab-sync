<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars($post['title']); ?> - LabSync Health Updates</title>
  <meta name="description" content="<?php echo htmlspecialchars(substr($post['excerpt'] ?? '', 0, 155)); ?>">
  <link rel="stylesheet" href="/lab_sync/public/css/patient.css" />
  <link rel="stylesheet" href="/lab_sync/public/css/nav.css" />
  <link rel="stylesheet" href="/lab_sync/public/css/blog.css" />
</head>
<body>
  <?php require 'C:\xampp\htdocs\lab_sync\public\partials\header.php'; ?>

  <main>
    <div class="post-container">

      <!-- Breadcrumb -->
      <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="/lab_sync/index.php">Home</a>
        <span aria-hidden="true">/</span>
        <a href="/lab_sync/index.php?controller=blog&action=index">Health Updates</a>
        <span aria-hidden="true">/</span>
        <span><?php echo htmlspecialchars($post['title']); ?></span>
      </nav>

      <!-- Post Header -->
      <header class="post-header">
        <?php if (!empty($post['category'])): ?>
          <span class="blog-category"><?php echo htmlspecialchars($post['category']); ?></span>
        <?php endif; ?>
        <h1><?php echo htmlspecialchars($post['title']); ?></h1>
        <div class="post-meta">
          <span>📅 <?php echo htmlspecialchars($post['display_date']); ?></span>
          <span>✍️ LabSync Team</span>
        </div>
      </header>

      <!-- Featured Image -->
      <?php if (!empty($post['featured_image'])): ?>
        <div class="post-featured-wrap">
          <img
            src="/lab_sync/public/<?php echo htmlspecialchars($post['featured_image']); ?>"
            alt="<?php echo htmlspecialchars($post['title']); ?>"
            class="post-featured-img"
          >
        </div>
      <?php else: ?>
        <?php
          $catSlug = $post['category_slug'] ?? '';
          if ($catSlug === 'new-tests') {
              $fbIcon = '🔬'; $fbLabel = 'New Test Announcement'; $fbMod = 'fallback-tests';
          } elseif ($catSlug === 'patient-instructions') {
              $fbIcon = '📋'; $fbLabel = 'Patient Guide'; $fbMod = 'fallback-guide';
          } else {
              $fbIcon = '💡'; $fbLabel = 'Health Education'; $fbMod = 'fallback-health';
          }
        ?>
        <div class="post-featured-image post-featured-fallback <?php echo $fbMod; ?>">
          <span class="fallback-icon" aria-hidden="true"><?php echo $fbIcon; ?></span>
          <span class="fallback-label"><?php echo $fbLabel; ?></span>
        </div>
      <?php endif; ?>

      <!-- Post Content -->
      <div class="post-content">
        <?php
          $content = htmlspecialchars($post['content'] ?? '');
          echo nl2br($content);
        ?>
      </div>

      <!-- Medical Disclaimer -->
      <div class="post-disclaimer">
        📋 <strong>Educational content only.</strong> This article is for informational purposes and is not a substitute for professional medical advice, diagnosis, or treatment. Always consult your healthcare provider.
      </div>

      <!-- CTA -->
      <div class="post-cta">
        <div class="post-cta-inner">
          <div class="post-cta-text">
            <h3>Ready to book a test?</h3>
            <p>Browse our full test catalog and schedule an appointment online.</p>
          </div>
          <a href="/lab_sync/index.php?controller=home&action=explore" class="btn-primary">
            Explore Tests →
          </a>
        </div>
      </div>

      <!-- Back Link -->
      <div class="post-actions">
        <a href="/lab_sync/index.php?controller=blog&action=index" class="post-back">
          ← Back to Health Updates
        </a>
      </div>

    </div><!-- .post-container -->
  </main>

  <?php require 'C:\xampp\htdocs\lab_sync\public\partials\footer.php'; ?>
</body>
</html>
