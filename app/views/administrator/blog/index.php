<?php
// Session already started by bootstrap.php
if (!isset($_SESSION['user_id'])) {
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}
$role = $_SESSION['user_role'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Blog Posts — LabSync Admin</title>
    <link rel="stylesheet" href="/lab_sync/public/styles.css">
    <link rel="stylesheet" href="/lab_sync/public/table.css">
    <link rel="stylesheet" href="/lab_sync/public/css/blog_admin.css">
</head>
<body>
<?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
<div class="container">
    <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>

    <main class="main-content">

        <!-- Page header -->
        <div class="main-content-header">
            <h1>Blog Posts</h1>
            <p class="MC-p">Admin → Blog Posts</p>
        </div>

        <!-- Flash messages -->
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="flash-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="flash-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['warning'])): ?>
            <div class="flash-warning"><?php echo htmlspecialchars($_SESSION['warning']); unset($_SESSION['warning']); ?></div>
        <?php endif; ?>

        <!-- Toolbar: title + new post button -->
        <div class="test-catalog-header">
            <h2>All Posts</h2>
            <button class="add-test-button">
                <a href="/lab_sync/index.php?controller=blog&action=create&role=<?php echo urlencode($role); ?>">
                    + New Post
                </a>
            </button>
        </div>

        <!-- Filter row -->
        <form method="GET" action="/lab_sync/index.php">
            <input type="hidden" name="controller" value="blog">
            <input type="hidden" name="action" value="manage">
            <input type="hidden" name="role" value="<?php echo htmlspecialchars($role); ?>">
            <div class="blog-filter-row">
                <input type="text" name="search" class="search-bar"
                       placeholder="Search posts…"
                       value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">

                <select name="status">
                    <option value="">All Status</option>
                    <option value="draft"     <?php echo (($_GET['status'] ?? '') === 'draft')     ? 'selected' : ''; ?>>Draft</option>
                    <option value="published" <?php echo (($_GET['status'] ?? '') === 'published') ? 'selected' : ''; ?>>Published</option>
                    <option value="archived"  <?php echo (($_GET['status'] ?? '') === 'archived')  ? 'selected' : ''; ?>>Archived</option>
                </select>

                <select name="cat">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat['slug']); ?>"
                            <?php echo (($_GET['cat'] ?? '') === $cat['slug']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="add-test-button" style="margin-bottom:0;">Filter</button>

                <?php if ($hasFilters): ?>
                    <a href="/lab_sync/index.php?controller=blog&action=manage&role=<?php echo urlencode($role); ?>"
                       class="btn-outline">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Posts table -->
        <table class="test-catalog-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($posts)): ?>
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <p>No posts found.</p>
                            <a href="/lab_sync/index.php?controller=blog&action=create&role=<?php echo urlencode($role); ?>"
                               class="add-test-button" style="text-decoration:none; display:inline-block; padding:10px 20px;">
                                Create your first post
                            </a>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($post['title']); ?></strong><br>
                        <small style="color:#6b7280; font-size:0.78rem;">/<?php echo htmlspecialchars($post['slug']); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($post['category'] ?? '—'); ?></td>
                    <td>
                        <div class="TStatus_Td">
                            <span class="badge badge-<?php echo $post['status']; ?>">
                                <?php echo ucfirst($post['status']); ?>
                            </span>
                        </div>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($post['updated_at'])); ?></td>
                    <td>
                        <a href="/lab_sync/index.php?controller=blog&action=edit&id=<?php echo $post['post_id']; ?>&role=<?php echo urlencode($role); ?>"
                           class="action-link action-link-edit">Edit</a>

                        <?php if ($post['status'] === 'draft'): ?>
                            <a href="/lab_sync/index.php?controller=blog&action=publish&id=<?php echo $post['post_id']; ?>&role=<?php echo urlencode($role); ?>"
                               class="action-link action-link-publish"
                               onclick="return confirm('Publish this post?')">Publish</a>
                        <?php elseif ($post['status'] === 'published'): ?>
                            <a href="/lab_sync/index.php?controller=blog&action=unpublish&id=<?php echo $post['post_id']; ?>&role=<?php echo urlencode($role); ?>"
                               class="action-link action-link-unpublish"
                               onclick="return confirm('Move back to draft?')">Unpublish</a>
                        <?php endif; ?>

                        <?php if ($post['status'] !== 'archived'): ?>
                            <a href="/lab_sync/index.php?controller=blog&action=archive&id=<?php echo $post['post_id']; ?>&role=<?php echo urlencode($role); ?>"
                               class="action-link action-link-archive"
                               onclick="return confirm('Archive this post?')">Archive</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if (isset($totalPages) && $totalPages > 1): ?>
            <?php
                $baseUrl = '/lab_sync/index.php?controller=blog&action=manage&role=' . urlencode($role);
                if (!empty($_GET['search'])) $baseUrl .= '&search=' . urlencode($_GET['search']);
                if (!empty($_GET['status'])) $baseUrl .= '&status=' . urlencode($_GET['status']);
                if (!empty($_GET['cat']))    $baseUrl .= '&cat='    . urlencode($_GET['cat']);
            ?>
            <div class="blog-pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="<?php echo $baseUrl; ?>&page=<?php echo $currentPage - 1; ?>">← Prev</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="<?php echo $baseUrl; ?>&page=<?php echo $i; ?>"
                       class="<?php echo ($i === $currentPage) ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                <?php if ($currentPage < $totalPages): ?>
                    <a href="<?php echo $baseUrl; ?>&page=<?php echo $currentPage + 1; ?>">Next →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </main>
</div>
</body>
</html>
