<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Blogs - LabSync</title>
  <meta name="description" content="Read health articles, test updates, and patient instructions from LabSync's medical team.">
  <link rel="stylesheet" href="/lab_sync/public/css/patient.css" />
  <link rel="stylesheet" href="/lab_sync/public/css/nav.css" />
  <link rel="stylesheet" href="/lab_sync/public/css/blog.css" />
</head>
<body>
  <?php require 'C:\xampp\htdocs\lab_sync\public\partials\header.php'; ?>
  
  <main>
    <!-- Premium Page Header (replaces purple hero) -->
    <div class="blog-page-header">
      <div class="blog-header-inner">
        <div class="blog-header">
          <span class="blog-badge">Health Updates</span>
          <h1>Blogs</h1>
          <p>New tests, patient instructions, and health articles.</p>
        </div>
      </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="filter-toolbar">
      <form method="GET" action="/lab_sync/index.php" class="filter-bar">
        <input type="hidden" name="controller" value="blog">
        <input type="hidden" name="action" value="index">
        
        <input 
          type="text" 
          name="search" 
          placeholder="Search articles…" 
          value="<?php echo htmlspecialchars($searchQuery ?? ''); ?>"
        >
        
        <select name="cat" id="categoryFilter">
          <option value="">All Categories</option>
          <option value="new-tests" <?php echo (isset($activeCategory) && $activeCategory === 'new-tests') ? 'selected' : ''; ?>>New Tests</option>
          <option value="patient-instructions" <?php echo (isset($activeCategory) && $activeCategory === 'patient-instructions') ? 'selected' : ''; ?>>Patient Instructions</option>
          <option value="health-education" <?php echo (isset($activeCategory) && $activeCategory === 'health-education') ? 'selected' : ''; ?>>Health Education</option>
        </select>
        
        <select name="sort" id="sortFilter">
          <option value="latest" <?php echo (isset($activeSort) && $activeSort === 'latest') ? 'selected' : ''; ?>>Latest First</option>
          <option value="oldest" <?php echo (isset($activeSort) && $activeSort === 'oldest') ? 'selected' : ''; ?>>Oldest First</option>
        </select>
        
        <button type="submit" class="btn-primary">Filter</button>
        
        <?php if (isset($hasFilters) && $hasFilters): ?>
          <a href="/lab_sync/index.php?controller=blog&action=index" class="btn-clear">Clear Filters</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- Blog Grid -->
    <div class="blog-container">
    <?php if (!empty($filteredPosts)): ?>
      <div class="blog-grid">
        <?php foreach ($filteredPosts as $post): ?>
          <article class="blog-card">
            <?php if (!empty($post['featured_image'])): ?>
              <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="blog-card-image">
            <?php else: ?>
              <div class="blog-card-image">📄</div>
            <?php endif; ?>
            
            <div class="blog-card-body">
              <span class="blog-category"><?php echo htmlspecialchars($post['category']); ?></span>
              <h3><?php echo htmlspecialchars($post['title']); ?></h3>
              <p class="blog-excerpt"><?php echo htmlspecialchars($post['excerpt']); ?></p>
              
              <div class="blog-meta">
                <span class="blog-date"><?php echo date('M j, Y', strtotime($post['date'])); ?></span>
                <span class="blog-author"><?php echo htmlspecialchars($post['author']); ?></span>
              </div>
              
              <a href="/lab_sync/index.php?controller=blog&action=view&slug=<?php echo urlencode($post['slug']); ?>" class="blog-read-more">
                Read more →
              </a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="blog-empty">
        <h3>No posts found</h3>
        <p>Try a different search term or clear your filters to see all articles.</p>
        <a href="/lab_sync/index.php?controller=blog&action=index" class="btn-primary">Clear Filters</a>
      </div>
    <?php endif; ?>
    </div><!-- .blog-container -->
  </main>

  <?php require 'C:\xampp\htdocs\lab_sync\public\partials\footer.php'; ?>
</body>
</html>
