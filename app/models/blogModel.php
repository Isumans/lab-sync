<?php

class blogModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // ============================================================
    // PUBLIC METHODS (for visitors and patients)
    // ============================================================

    /**
     * Get published blog posts with filters, sorting, and pagination
     * @param string $q - Search query
     * @param string $catSlug - Category slug filter
     * @param string $sort - Sort order ('latest' or 'oldest')
     * @param int $limit - Number of posts per page
     * @param int $offset - Offset for pagination
     * @return array - Array of posts
     */
    public function getPublishedPosts($q = '', $catSlug = '', $sort = 'latest', $limit = 9, $offset = 0) {
        $sql = "SELECT p.post_id, p.title, p.slug, p.excerpt, p.featured_image, 
                       p.published_at, p.created_at, p.updated_at,
                       c.name as category, c.slug as category_slug,
                       u.username as author_name
                FROM blog_posts p
                LEFT JOIN blog_categories c ON p.category_id = c.category_id
                LEFT JOIN users u ON p.author_id = u.user_id
                WHERE p.status = 'published'";
        
        $params = [];
        $types = '';

        // Search filter
        if (!empty($q)) {
            $sql .= " AND (p.title LIKE ? OR p.excerpt LIKE ? OR p.content LIKE ?)";
            $searchTerm = '%' . $q . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= 'sss';
        }

        // Category filter
        if (!empty($catSlug)) {
            $sql .= " AND c.slug = ?";
            $params[] = $catSlug;
            $types .= 's';
        }

        // Sorting
        if ($sort === 'oldest') {
            $sql .= " ORDER BY p.published_at ASC";
        } else {
            $sql .= " ORDER BY p.published_at DESC";
        }

        // Pagination
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        
        $posts = [];
        while ($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }

        $stmt->close();
        return $posts;
    }

    /**
     * Count published posts (for pagination)
     */
    public function countPublishedPosts($q = '', $catSlug = '') {
        $sql = "SELECT COUNT(*) as total
                FROM blog_posts p
                LEFT JOIN blog_categories c ON p.category_id = c.category_id
                WHERE p.status = 'published'";
        
        $params = [];
        $types = '';

        if (!empty($q)) {
            $sql .= " AND (p.title LIKE ? OR p.excerpt LIKE ? OR p.content LIKE ?)";
            $searchTerm = '%' . $q . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= 'sss';
        }

        if (!empty($catSlug)) {
            $sql .= " AND c.slug = ?";
            $params[] = $catSlug;
            $types .= 's';
        }

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return 0;
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return (int)$row['total'];
    }

    /**
     * Get single published post by slug
     */
    public function getPublishedPostBySlug($slug) {
        $sql = "SELECT p.*, c.name as category, c.slug as category_slug, u.username as author_name
                FROM blog_posts p
                LEFT JOIN blog_categories c ON p.category_id = c.category_id
                LEFT JOIN users u ON p.author_id = u.user_id
                WHERE p.slug = ? AND p.status = 'published'
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('s', $slug);
        $stmt->execute();
        $result = $stmt->get_result();
        $post = $result->fetch_assoc();
        $stmt->close();

        return $post;
    }

    /**
     * Get all categories
     */
    public function getCategories() {
        $result = $this->db->query("SELECT * FROM blog_categories ORDER BY name ASC");
        if (!$result) {
            return [];
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // ============================================================
    // ADMIN METHODS (for administrators only)
    // ============================================================

    /**
     * Get all posts (including drafts and archived) for admin management
     */
    public function getAllPosts($q = '', $status = '', $catSlug = '', $sort = 'latest', $limit = 15, $offset = 0) {
        $sql = "SELECT p.post_id, p.title, p.slug, p.status, p.updated_at, 
                       c.name as category, c.slug as category_slug
                FROM blog_posts p
                LEFT JOIN blog_categories c ON p.category_id = c.category_id
                WHERE 1=1";
        
        $params = [];
        $types = '';

        // Search filter
        if (!empty($q)) {
            $sql .= " AND (p.title LIKE ? OR p.excerpt LIKE ?)";
            $searchTerm = '%' . $q . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= 'ss';
        }

        // Status filter
        if (!empty($status)) {
            $sql .= " AND p.status = ?";
            $params[] = $status;
            $types .= 's';
        }

        // Category filter
        if (!empty($catSlug)) {
            $sql .= " AND c.slug = ?";
            $params[] = $catSlug;
            $types .= 's';
        }

        // Sorting
        if ($sort === 'oldest') {
            $sql .= " ORDER BY p.updated_at ASC";
        } else {
            $sql .= " ORDER BY p.updated_at DESC";
        }

        // Pagination
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        
        $posts = [];
        while ($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }

        $stmt->close();
        return $posts;
    }

    /**
     * Count all posts for admin (for pagination)
     */
    public function countAllPosts($q = '', $status = '', $catSlug = '') {
        $sql = "SELECT COUNT(*) as total
                FROM blog_posts p
                LEFT JOIN blog_categories c ON p.category_id = c.category_id
                WHERE 1=1";
        
        $params = [];
        $types = '';

        if (!empty($q)) {
            $sql .= " AND (p.title LIKE ? OR p.excerpt LIKE ?)";
            $searchTerm = '%' . $q . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= 'ss';
        }

        if (!empty($status)) {
            $sql .= " AND p.status = ?";
            $params[] = $status;
            $types .= 's';
        }

        if (!empty($catSlug)) {
            $sql .= " AND c.slug = ?";
            $params[] = $catSlug;
            $types .= 's';
        }

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return 0;
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return (int)$row['total'];
    }

    /**
     * Get single post by ID (for admin editing)
     */
    public function getPostById($id) {
        $sql = "SELECT p.*, c.slug as category_slug
                FROM blog_posts p
                LEFT JOIN blog_categories c ON p.category_id = c.category_id
                WHERE p.post_id = ?
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $post = $result->fetch_assoc();
        $stmt->close();

        return $post;
    }

    /**
     * Create new blog post
     * @param array $data - Post data
     * @return int|false - New post ID or false on failure
     */
    public function createPost($data) {
        $sql = "INSERT INTO blog_posts 
                (title, slug, excerpt, content, featured_image, category_id, author_id, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        // Store in local vars — required for pass-by-reference in PHP 7.4 MySQLi bind_param
        $title       = $data['title'];
        $slug        = $data['slug'];
        $excerpt     = $data['excerpt'];
        $content     = $data['content'];
        $featuredImg = $data['featured_image'];          // string or null
        $categoryId  = $data['category_id'];             // int or null — bind as 'i', MySQLi accepts null
        $authorId    = (int)$data['author_id'];
        $status      = $data['status'];

        // Column order: title(s), slug(s), excerpt(s), content(s), featured_image(s), category_id(i), author_id(i), status(s)
        $stmt->bind_param(
            'sssssiss',
            $title,
            $slug,
            $excerpt,
            $content,
            $featuredImg,
            $categoryId,
            $authorId,
            $status
        );

        $success  = $stmt->execute();
        $insertId = $success ? $stmt->insert_id : false;
        $stmt->close();

        return $insertId;
    }


    /**
     * Update existing blog post
     */
    public function updatePost($id, $data) {
        $sql = "UPDATE blog_posts 
                SET title = ?, excerpt = ?, content = ?, featured_image = ?, category_id = ?, updated_at = NOW()
                WHERE post_id = ?";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        // Local vars for by-reference binding; featured_image is string (s), category_id nullable int (i)
        $title       = $data['title'];
        $excerpt     = $data['excerpt'];
        $content     = $data['content'];
        $featuredImg = $data['featured_image'];  // string or null
        $categoryId  = $data['category_id'];     // int or null
        $postId      = (int)$id;

        $stmt->bind_param(
            'ssssii',
            $title,
            $excerpt,
            $content,
            $featuredImg,
            $categoryId,
            $postId
        );

        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    /**
     * Set post status (publish/unpublish/archive)
     */
    public function setStatus($id, $status, $publishedAt = null) {
        if ($publishedAt) {
            $sql = "UPDATE blog_posts SET status = ?, published_at = ?, updated_at = NOW() WHERE post_id = ?";
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                return false;
            }
            $stmt->bind_param('ssi', $status, $publishedAt, $id);
        } else {
            $sql = "UPDATE blog_posts SET status = ?, updated_at = NOW() WHERE post_id = ?";
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                return false;
            }
            $stmt->bind_param('si', $status, $id);
        }

        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    /**
     * Check if slug exists (for uniqueness validation)
     */
    public function slugExists($slug) {
        $sql = "SELECT COUNT(*) as count FROM blog_posts WHERE slug = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('s', $slug);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row['count'] > 0;
    }

    /**
     * Upload featured image
     * @param array $file - $_FILES['featured_image']
     * @return string|false - Relative path to image or false on failure
     */
    public function uploadFeaturedImage($file) {
        // Check if file was uploaded
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return false;
        }

        // Check file size (max 2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            return false;
        }

        // Check MIME type
        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedMimes)) {
            return false;
        }

        // Get file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($extension, $allowedExtensions)) {
            return false;
        }

        // Generate unique filename
        $filename = uniqid('blog_', true) . '.' . $extension;
        $uploadDir = __DIR__ . '/../../public/images/blog/';
        $uploadPath = $uploadDir . $filename;

        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Return relative path for database storage
            return 'images/blog/' . $filename;
        }

        return false;
    }
}
