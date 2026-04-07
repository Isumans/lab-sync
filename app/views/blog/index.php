<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Health Updates – LabSync</title>
  <meta name="description" content="Read health articles, new test announcements, and patient care guides from the LabSync medical team.">
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/lab_sync/public/css/globals.css" />
  <link rel="stylesheet" href="/lab_sync/public/css/nav.css" />
  <link rel="stylesheet" href="/lab_sync/public/css/footer.css" />
  <link rel="stylesheet" href="/lab_sync/public/css/blog.css" />
</head>
<body>
  <?php require_once __DIR__ . '/../../../public/partials/header.php'; ?>

  <main>
    <!-- Page Header -->
    <div class="blog-page-header">
      <div class="blog-header-inner">
        <div class="blog-header">
          <span class="blog-badge">Health Updates</span>
          <h1>Patient Health Guides &amp; Articles</h1>
          <p>Lab test guidance, health education, and important announcements from the LabSync medical team.</p>
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

        <select name="cat" id="categoryFilter" onchange="this.form.submit()">
          <option value="">All Categories</option>
          <?php if (!empty($categories)): ?>
            <?php foreach ($categories as $cat): ?>
              <option
                value="<?php echo htmlspecialchars($cat['slug']); ?>"
                <?php echo (isset($activeCategory) && $activeCategory === $cat['slug']) ? 'selected' : ''; ?>
              ><?php echo htmlspecialchars($cat['name']); ?></option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>

        <select name="sort" id="sortFilter" onchange="this.form.submit()">
          <option value="latest" <?php echo (!isset($activeSort) || $activeSort === 'latest') ? 'selected' : ''; ?>>Latest First</option>
          <option value="oldest" <?php echo (isset($activeSort) && $activeSort === 'oldest') ? 'selected' : ''; ?>>Oldest First</option>
        </select>

        <button type="submit" class="btn-primary">Search</button>

        <?php if (isset($hasFilters) && $hasFilters): ?>
          <a href="/lab_sync/index.php?controller=blog&action=index" class="btn-clear">Clear</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- Blog Grid -->
    <div class="blog-container">
      <?php if (!empty($filteredPosts)): ?>
        <div class="blog-grid">
          <?php foreach ($filteredPosts as $post): ?>
            <?php
              // Safe category label for fallback icon selection
              $catSlug = $post['category_slug'] ?? '';
              $catLabel = $post['category'] ?? '';
              // Determine fallback icon/label by category slug
              if ($catSlug === 'new-tests') {
                  $fallbackIcon = '🔬';
                  $fallbackLabel = 'New Test';
                  $fallbackMod  = 'fallback-tests';
              } elseif ($catSlug === 'patient-instructions') {
                  $fallbackIcon = '📋';
                  $fallbackLabel = 'Patient Guide';
                  $fallbackMod  = 'fallback-guide';
              } else {
                  $fallbackIcon = '💡';
                  $fallbackLabel = 'Health Article';
                  $fallbackMod  = 'fallback-health';
              }
            ?>
            <article class="blog-card">
              <?php if (!empty($post['featured_image'])): ?>
                <div class="blog-card-thumb">
                  <img
                    src="/lab_sync/public/<?php echo htmlspecialchars($post['featured_image']); ?>"
                    alt="<?php echo htmlspecialchars($post['title']); ?>"
                    class="blog-card-thumb__img"
                  >
                </div>
              <?php else: ?>
                <div class="blog-card-image blog-card-fallback <?php echo $fallbackMod; ?>">
                  <span class="fallback-icon" aria-hidden="true"><?php echo $fallbackIcon; ?></span>
                  <span class="fallback-label"><?php echo htmlspecialchars($fallbackLabel); ?></span>
                </div>
              <?php endif; ?>

              <div class="blog-card-body">
                <?php if (!empty($catLabel)): ?>
                  <span class="blog-category"><?php echo htmlspecialchars($catLabel); ?></span>
                <?php endif; ?>

                <h3>
                  <a href="/lab_sync/index.php?controller=blog&action=view&slug=<?php echo urlencode($post['slug']); ?>">
                    <?php echo htmlspecialchars($post['title']); ?>
                  </a>
                </h3>

                <?php if (!empty($post['excerpt'])): ?>
                  <p class="blog-excerpt"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                <?php endif; ?>

                <div class="blog-meta">
                  <span class="blog-date"><?php echo htmlspecialchars($post['display_date']); ?></span>
                  <span class="blog-byline">LabSync Team</span>
                </div>

                <a href="/lab_sync/index.php?controller=blog&action=view&slug=<?php echo urlencode($post['slug']); ?>" class="blog-read-more">
                  Read article
                </a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if (isset($totalPages) && $totalPages > 1): ?>
          <nav class="blog-pagination" aria-label="Article pages">
            <?php
              $currentPage = $currentPage ?? 1;
              $urlBase = '/lab_sync/index.php?controller=blog&action=index'
                  . (!empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '')
                  . (!empty($activeCategory) ? '&cat=' . urlencode($activeCategory) : '')
                  . (!empty($activeSort) ? '&sort=' . urlencode($activeSort) : '');
            ?>
            <?php if ($currentPage > 1): ?>
              <a href="<?php echo $urlBase; ?>&page=<?php echo $currentPage - 1; ?>" class="page-btn">← Prev</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
              <a href="<?php echo $urlBase; ?>&page=<?php echo $i; ?>"
                 class="page-btn <?php echo ($i === $currentPage) ? 'page-btn--active' : ''; ?>">
                <?php echo $i; ?>
              </a>
            <?php endfor; ?>

            <?php if ($currentPage < $totalPages): ?>
              <a href="<?php echo $urlBase; ?>&page=<?php echo $currentPage + 1; ?>" class="page-btn">Next →</a>
            <?php endif; ?>
          </nav>
        <?php endif; ?>

      <?php else: ?>
        <!-- Empty State -->
        <div class="blog-empty">
          <div class="blog-empty-icon" aria-hidden="true">📭</div>
          <h3>No health articles found</h3>
          <?php if (isset($hasFilters) && $hasFilters): ?>
            <p>No articles matched your search. Try a different keyword or browse all categories.</p>
            <a href="/lab_sync/index.php?controller=blog&action=index" class="btn-primary">Browse all articles</a>
          <?php else: ?>
            <p>Health articles will appear here once they are published by the LabSync team.</p>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div><!-- .blog-container -->
  </main>

  <?php require_once __DIR__ . '/../../../public/partials/footer.php'; ?>
</body>
</html>
