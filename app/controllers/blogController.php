<?php
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';
}

require_once MODEL_PATH . '/blogModel.php';
require_once 'C:\xampp\htdocs\lab_sync\config\db.php';

class blogController {
    
    // ============================================================
    // PUBLIC METHODS (for visitors and patients)
    // ============================================================
    
    /**
     * Blog list page - shows published posts only
     */
    public function index($role = '') {
        $conn = connect();
        if (!$conn) {
            echo "<div style='padding:40px; text-align:center;'><h2>Database Connection Error</h2><p>Unable to connect to database. Please ensure the database is configured correctly.</p></div>";
            return;
        }
        
        $model = new blogModel($conn);
        
        // Get filter parameters
        $search = $_GET['search'] ?? '';
        $category = $_GET['cat'] ?? '';
        $sort = $_GET['sort'] ?? 'latest';
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 9;
        $offset = ($page - 1) * $perPage;
        
        // Get posts and count
        $posts = $model->getPublishedPosts($search, $category, $sort, $perPage, $offset);
        $totalPosts = $model->countPublishedPosts($search, $category);
        $totalPages = ceil($totalPosts / $perPage);
        
        // Make available to view
        $filteredPosts = $posts;
        $hasFilters = !empty($search) || !empty($category);
        $searchQuery = $search;
        $activeCategory = $category;
        $activeSort = $sort;
        $currentPage = $page;
        $categories = $model->getCategories(); // for dynamic filter dropdown

        // Compute display_date for each post: published_at → created_at → updated_at → null
        foreach ($filteredPosts as &$p) {
            $raw = $p['published_at'] ?? $p['created_at'] ?? $p['updated_at'] ?? null;
            $p['display_date'] = ($raw && strtotime($raw)) ? date('M j, Y', strtotime($raw)) : '—';
        }
        unset($p);

        include VIEW_PATH . '/blog/index.php';
    }

    /**
     * Single blog post page - shows published post only
     */
    public function view($role = '') {
        $slug = $_GET['slug'] ?? '';
        
        if (empty($slug)) {
            $errorMessage = "Invalid post URL";
            include VIEW_PATH . '/blog/not_found.php';
            return;
        }
        
        $conn = connect();
        if (!$conn) {
            echo "<div style='padding:40px; text-align:center;'><h2>Database Connection Error</h2><p>Unable to connect to database.</p></div>";
            return;
        }
        
        $model = new blogModel($conn);
        $post = $model->getPublishedPostBySlug($slug);

        // If post not found, show error
        if (!$post) {
            $errorMessage = "Post not found";
            include VIEW_PATH . '/blog/not_found.php';
            return;
        }

        // Compute display_date: published_at → created_at → updated_at → '—'
        $rawDate = $post['published_at'] ?? $post['created_at'] ?? $post['updated_at'] ?? null;
        $post['display_date'] = ($rawDate && strtotime($rawDate)) ? date('F j, Y', strtotime($rawDate)) : '—';

        include VIEW_PATH . '/blog/view.php';
    }
    
    // ============================================================
    // ADMIN METHODS (for administrators only)
    // ============================================================
    
    /**
     * Admin: Manage all posts (drafts, published, archived)
     */
    public function manage($role) {
        // Role check
        if ($role !== 'admin') {
            header('Location: /lab_sync/index.php');
            exit;
        }
        
        $conn = connect();
        if (!$conn) {
            echo "Database connection error";
            return;
        }
        
        $model = new blogModel($conn);
        
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $category = $_GET['cat'] ?? '';
        $sort = $_GET['sort'] ?? 'latest';
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 15;
        $offset = ($page - 1) * $perPage;
        
        $posts = $model->getAllPosts($search, $status, $category, $sort, $perPage, $offset);
        $totalPosts = $model->countAllPosts($search, $status, $category);
        $totalPages = ceil($totalPosts / $perPage);
        $categories = $model->getCategories();
        
        $currentPage = $page;
        $hasFilters = !empty($search) || !empty($status) || !empty($category);
        
        include VIEW_PATH . '/administrator/blog/index.php';
    }
    
    /**
     * Admin: Show create post form
     */
    public function create($role) {
        if ($role !== 'admin') {
            header('Location: /lab_sync/index.php');
            exit;
        }
        
        $conn = connect();
        $model = new blogModel($conn);
        $categories = $model->getCategories();
        
        include VIEW_PATH . '/administrator/blog/create.php';
    }
    
    /**
     * Admin: Store new post (POST)
     */
    public function store($role) {
        if ($role !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /lab_sync/index.php');
            exit;
        }
        
        $conn = connect();
        $model = new blogModel($conn);
        
        // Validate inputs
        $title = trim($_POST['title'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        
        if (empty($title) || empty($excerpt) || empty($content)) {
            $_SESSION['error'] = "Title, excerpt, and content are required";
            header('Location: /lab_sync/index.php?controller=blog&action=create&role=' . urlencode($role));
            exit;
        }
        
        // Generate unique slug
        $slug = $this->generateUniqueSlug($title, $model);
        
        // Handle image upload
        $featuredImage = null;
        if (!empty($_FILES['featured_image']['name'])) {
            $featuredImage = $model->uploadFeaturedImage($_FILES['featured_image']);
            if ($featuredImage === false) {
                $_SESSION['warning'] = "Image upload failed, but post was saved";
            }
        }
        
        // Get logged-in author ID
        $authorId = $_SESSION['user_id'] ?? 1;
        
        $data = [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'content' => $content,
            'featured_image' => $featuredImage,
            'category_id' => $category_id,
            'author_id' => $authorId,
            'status' => 'draft'
        ];
        
        $postId = $model->createPost($data);
        
        if ($postId) {
            $_SESSION['success'] = "Post created successfully as draft";
            header('Location: /lab_sync/index.php?controller=blog&action=manage&role=' . urlencode($role));
        } else {
            $_SESSION['error'] = "Error creating post. Please check all fields and try again.";
            header('Location: /lab_sync/index.php?controller=blog&action=create&role=' . urlencode($role));
        }
        exit;
    }
    
    /**
     * Admin: Show edit post form
     */
    public function edit($role) {
        if ($role !== 'admin') {
            header('Location: /lab_sync/index.php');
            exit;
        }
        
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        $conn = connect();
        $model = new blogModel($conn);
        $post = $model->getPostById($id);
        $categories = $model->getCategories();
        
        if (!$post) {
            $_SESSION['error'] = "Post not found";
            header('Location: /lab_sync/index.php?controller=blog&action=manage&role=admin');
            exit;
        }
        
        include VIEW_PATH . '/administrator/blog/edit.php';
    }
    
    /**
     * Admin: Update post (POST)
     */
    public function update($role) {
        if ($role !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /lab_sync/index.php');
            exit;
        }
        
        $id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
        
        $conn = connect();
        $model = new blogModel($conn);
        
        // Validate inputs
        $title = trim($_POST['title'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        
        if (empty($title) || empty($excerpt) || empty($content)) {
            $_SESSION['error'] = "Title, excerpt, and content are required";
            header("Location: /lab_sync/index.php?controller=blog&action=edit&id=$id&role=admin");
            exit;
        }
        
        // Get current post for featured image
        $currentPost = $model->getPostById($id);
        $featuredImage = $currentPost['featured_image'];
        
        // Handle image upload
        if (!empty($_FILES['featured_image']['name'])) {
            $newImage = $model->uploadFeaturedImage($_FILES['featured_image']);
            if ($newImage) {
                $featuredImage = $newImage;
            } else {
                $_SESSION['warning'] = "Image upload failed, other changes were saved";
            }
        }
        
        $data = [
            'title' => $title,
            'excerpt' => $excerpt,
            'content' => $content,
            'featured_image' => $featuredImage,
            'category_id' => $category_id
        ];
        
        $success = $model->updatePost($id, $data);
        
        if ($success) {
            $_SESSION['success'] = "Post updated successfully";
        } else {
            $_SESSION['error'] = "Error updating post";
        }
        
        header("Location: /lab_sync/index.php?controller=blog&action=manage&role=" . urlencode($role));
        exit;
    }
    
    /**
     * Admin: Publish post
     */
    public function publish($role) {
        if ($role !== 'admin') {
            header('Location: /lab_sync/index.php');
            exit;
        }
        
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        $conn = connect();
        $model = new blogModel($conn);
        
        $publishedAt = date('Y-m-d H:i:s');
        $success = $model->setStatus($id, 'published', $publishedAt);
        
        if ($success) {
            $_SESSION['success'] = "Post published successfully";
        } else {
            $_SESSION['error'] = "Error publishing post";
        }
        
        header('Location: /lab_sync/index.php?controller=blog&action=manage&role=' . urlencode($role));
        exit;
    }
    
    /**
     * Admin: Unpublish post (back to draft)
     */
    public function unpublish($role) {
        if ($role !== 'admin') {
            header('Location: /lab_sync/index.php');
            exit;
        }
        
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        $conn = connect();
        $model = new blogModel($conn);
        
        $success = $model->setStatus($id, 'draft', null);
        
        if ($success) {
            $_SESSION['success'] = "Post unpublished (back to draft)";
        } else {
            $_SESSION['error'] = "Error unpublishing post";
        }
        
        header('Location: /lab_sync/index.php?controller=blog&action=manage&role=' . urlencode($role));
        exit;
    }
    
    /**
     * Admin: Archive post
     */
    public function archive($role) {
        if ($role !== 'admin') {
            header('Location: /lab_sync/index.php');
            exit;
        }
        
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        $conn = connect();
        $model = new blogModel($conn);
        
        $success = $model->setStatus($id, 'archived', null);
        
        if ($success) {
            $_SESSION['success'] = "Post archived";
        } else {
            $_SESSION['error'] = "Error archiving post";
        }
        
        header('Location: /lab_sync/index.php?controller=blog&action=manage&role=' . urlencode($role));
        exit;
    }
    
    // ============================================================
    // HELPER METHODS
    // ============================================================
    
    /**
     * Generate unique slug from title
     */
    private function generateUniqueSlug($title, $model) {
        // Convert to lowercase and replace spaces/special chars with hyphens
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $slug = preg_replace('/-+/', '-', $slug); // Remove multiple consecutive hyphens
        $slug = trim($slug, '-'); // Remove leading/trailing hyphens
        
        $originalSlug = $slug;
        $counter = 2;
        
        // Check uniqueness
        while ($model->slugExists($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
}
