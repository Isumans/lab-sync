<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars($post['title']); ?> - LabSync Blogs</title>
  <meta name="description" content="<?php echo htmlspecialchars(substr($post['excerpt'], 0, 155)); ?>">
  <link rel="stylesheet" href="/lab_sync/public/css/patient.css" />
  <link rel="stylesheet" href="/lab_sync/public/css/nav.css" />
  <link rel="stylesheet" href="/lab_sync/public/css/blog.css" />
</head>
<body>
  <?php require 'C:\xampp\htdocs\lab_sync\public\partials\header.php'; ?>
  
  <main>
    <div class="post-container">
      <!-- Breadcrumb -->
      <div class="breadcrumb">
        <a href="/lab_sync/index.php">Home</a>
        <span>/</span>
        <a href="/lab_sync/index.php?controller=blog&action=index">Blogs</a>
        <span>/</span>
        <span><?php echo htmlspecialchars($post['title']); ?></span>
      </div>

      <!-- Post Header -->
      <div class="post-header">
        <span class="blog-category"><?php echo htmlspecialchars($post['category']); ?></span>
        <h1><?php echo htmlspecialchars($post['title']); ?></h1>
        <div class="post-meta">
          <span>📅 <?php echo date('F j, Y', strtotime($post['date'])); ?></span>
          <span>✍️ <?php echo htmlspecialchars($post['author']); ?></span>
        </div>
      </div>

      <!-- Featured Image -->
      <?php if (!empty($post['featured_image'])): ?>
        <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="post-featured-image" style="object-fit: cover;">
      <?php else: ?>
        <div class="post-featured-image">
          <span style="position: relative; z-index: 1;">📄</span>
        </div>
      <?php endif; ?>

    <!-- Post Content -->
    <div class="post-content">
      <?php 
        // Safely render content with line breaks
        $content = htmlspecialchars($post['content']);
        $content = nl2br($content);
        echo $content;
      ?>
    </div>

    <!-- Post Actions -->
    <div class="post-actions">
      <a href="/lab_sync/index.php?controller=blog&action=index" class="post-back">
        ← Back to Blogs
      </a>

      <!-- CTA Card -->
      <div class="cta-card">
        <h3>Ready to book a test?</h3>
        <p>Explore our test catalog and book online in minutes.</p>
        <a href="/lab_sync/index.php?controller=home&action=explore" class="btn-primary">
          Explore Tests
        </a>
      </div>

      <!-- Footer Note -->
     <!-- <div class="post-footer-note">
        📋 <strong>Educational Content:</strong> This information is for educational purposes only and is not a substitute for professional medical advice, diagnosis, or treatment. Always consult your healthcare provider with any questions about your health.
      </div>-->
    </div><!-- .post-actions -->
    </div><!-- .post-container -->
  </main>

  <?php require 'C:\xampp\htdocs\lab_sync\public\partials\footer.php'; ?>
</body>
</html>
