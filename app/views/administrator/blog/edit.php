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
    <title>Edit Blog Post — LabSync Admin</title>
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
            <h1>
                Edit Post
                <span class="badge badge-<?php echo htmlspecialchars($post['status']); ?>" style="font-size:0.75rem; vertical-align:middle; margin-left:10px;">
                    <?php echo ucfirst($post['status']); ?>
                </span>
            </h1>
            <p class="MC-p">Admin → Blog Posts → Edit</p>
        </div>

        <!-- Flash messages -->
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="flash-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['warning'])): ?>
            <div class="flash-warning"><?php echo htmlspecialchars($_SESSION['warning']); unset($_SESSION['warning']); ?></div>
        <?php endif; ?>

        <!-- Form -->
        <div class="blog-form">
            <form class="formStyle"
                  method="POST"
                  action="/lab_sync/index.php?controller=blog&action=update&role=<?php echo urlencode($role); ?>"
                  enctype="multipart/form-data">

                <input type="hidden" name="post_id" value="<?php echo (int)$post['post_id']; ?>">

                <!-- Title -->
                <label for="title">Title <span style="color:#ef4444;">*</span></label>
                <input type="text" id="title" name="title" required
                       value="<?php echo htmlspecialchars($post['title']); ?>">
                <span class="form-helper">Slug: <code><?php echo htmlspecialchars($post['slug']); ?></code> (slug does not change on edit)</span>

                <!-- Category -->
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id">
                    <option value="">— Uncategorized —</option>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo (int)$cat['category_id']; ?>"
                                <?php echo ((int)$post['category_id'] === (int)$cat['category_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>

                <!-- Excerpt -->
                <label for="excerpt">Excerpt <span style="color:#ef4444;">*</span></label>
                <textarea id="excerpt" name="excerpt" required><?php echo htmlspecialchars($post['excerpt']); ?></textarea>
                <span class="form-helper">Short summary shown on the blog listing page.</span>

                <!-- Content -->
                <label for="content">Content <span style="color:#ef4444;">*</span></label>
                <textarea id="content" name="content" required><?php echo htmlspecialchars($post['content']); ?></textarea>

                <!-- Featured image -->
                <label for="featured_image">Featured Image</label>
                <?php if (!empty($post['featured_image'])): ?>
                    <img src="/lab_sync/public/<?php echo htmlspecialchars($post['featured_image']); ?>"
                         alt="Current featured image" class="current-img-preview">
                    <span class="form-helper">Current image shown above. Upload a new file to replace it.</span>
                <?php endif; ?>
                <input type="file" id="featured_image" name="featured_image"
                       accept="image/jpeg,image/jpg,image/png,image/webp">
                <span class="form-helper">Max 2 MB. Accepted formats: JPG, PNG, WEBP.</span>

                <!-- Actions -->
                <div class="form-actions">
                    <button type="submit" class="add-test-button" style="margin-bottom:0;">Update Post</button>

                    <?php if ($post['status'] === 'draft'): ?>
                        <a href="/lab_sync/index.php?controller=blog&action=publish&id=<?php echo (int)$post['post_id']; ?>&role=<?php echo urlencode($role); ?>"
                           class="action-link action-link-publish"
                           onclick="return confirm('Save and publish this post?')">Publish</a>
                    <?php elseif ($post['status'] === 'published'): ?>
                        <a href="/lab_sync/index.php?controller=blog&action=unpublish&id=<?php echo (int)$post['post_id']; ?>&role=<?php echo urlencode($role); ?>"
                           class="action-link action-link-unpublish"
                           onclick="return confirm('Move back to draft?')">Unpublish</a>
                    <?php endif; ?>

                    <a href="/lab_sync/index.php?controller=blog&action=manage&role=<?php echo urlencode($role); ?>"
                       class="btn-outline">Cancel</a>
                </div>

            </form>
        </div>

    </main>
</div>
</body>
</html>
