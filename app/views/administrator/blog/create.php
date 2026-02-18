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
    <title>Create Blog Post — LabSync Admin</title>
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
            <h1>Create Blog Post</h1>
            <p class="MC-p">Admin → Blog Posts → Create</p>
        </div>

        <!-- Flash error -->
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="flash-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- Form -->
        <div class="blog-form">
            <form class="formStyle"
                  method="POST"
                  action="/lab_sync/index.php?controller=blog&action=store&role=<?php echo urlencode($role); ?>"
                  enctype="multipart/form-data">

                <!-- Title -->
                <label for="title">Title <span style="color:#ef4444;">*</span></label>
                <input type="text" id="title" name="title" required
                       placeholder="Enter post title"
                       value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                <span class="form-helper">Slug will be auto-generated from the title.</span>

                <!-- Category -->
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id">
                    <option value="">— Uncategorized —</option>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo (int)$cat['category_id']; ?>"
                                <?php echo ((int)($_POST['category_id'] ?? 0) === (int)$cat['category_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>No categories found — please seed the database</option>
                    <?php endif; ?>
                </select>

                <!-- Excerpt -->
                <label for="excerpt">Excerpt <span style="color:#ef4444;">*</span></label>
                <textarea id="excerpt" name="excerpt" required
                          placeholder="A short summary shown in the blog list (2–3 sentences)."><?php echo htmlspecialchars($_POST['excerpt'] ?? ''); ?></textarea>
                <span class="form-helper">Keep it concise — this appears on the blog listing page.</span>

                <!-- Content -->
                <label for="content">Content <span style="color:#ef4444;">*</span></label>
                <textarea id="content" name="content" required
                          placeholder="Full post content…"><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                <span class="form-helper">Plain text with line breaks. Content is displayed safely on the public page.</span>

                <!-- Featured image -->
                <label for="featured_image">Featured Image <span style="color:#6b7280;">(optional)</span></label>
                <input type="file" id="featured_image" name="featured_image"
                       accept="image/jpeg,image/jpg,image/png,image/webp">
                <span class="form-helper">Max 2 MB. Accepted formats: JPG, PNG, WEBP.</span>

                <!-- Actions -->
                <div class="form-actions">
                    <button type="submit" class="add-test-button" style="margin-bottom:0;">Save as Draft</button>
                    <a href="/lab_sync/index.php?controller=blog&action=manage&role=<?php echo urlencode($role); ?>"
                       class="btn-outline">Cancel</a>
                </div>

            </form>
        </div>

    </main>
</div>
</body>
</html>
