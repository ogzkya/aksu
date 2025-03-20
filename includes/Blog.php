<?php
// includes/Blog.php
class Blog {
    public $db; // Made public so we can access it directly in add.php
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Blog yazıları
    public function getAllPosts($limit = 10, $offset = 0, $status = 'published') {
        $sql = "SELECT p.*, GROUP_CONCAT(c.name) as categories 
                FROM blog_posts p 
                LEFT JOIN post_category pc ON p.id = pc.post_id 
                LEFT JOIN blog_categories c ON pc.category_id = c.id 
                WHERE p.status = ? 
                GROUP BY p.id 
                ORDER BY p.created_at DESC 
                LIMIT ?, ?";
        
        return $this->db->fetchAll($sql, [$status, $offset, $limit]);
    }
    
    public function getPostById($id) {
        $sql = "SELECT p.*, GROUP_CONCAT(c.name) as categories, GROUP_CONCAT(c.slug) as category_slugs 
                FROM blog_posts p 
                LEFT JOIN post_category pc ON p.id = pc.post_id 
                LEFT JOIN blog_categories c ON pc.category_id = c.id 
                WHERE p.id = ?";
        
        return $this->db->fetch($sql, [$id]);
    }
    
    public function getPostBySlug($slug) {
        $sql = "SELECT p.*, GROUP_CONCAT(c.name) as categories, GROUP_CONCAT(c.slug) as category_slugs 
                FROM blog_posts p 
                LEFT JOIN post_category pc ON p.id = pc.post_id 
                LEFT JOIN blog_categories c ON pc.category_id = c.id 
                WHERE p.slug = ?";
        
        return $this->db->fetch($sql, [$slug]);
    }
    
    public function createPost($data) {
        $sql = "INSERT INTO blog_posts (title, slug, content, excerpt, image, author, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['title'],
            $data['slug'] ?? $this->createSlug($data['title']),
            $data['content'],
            $data['excerpt'] ?? substr(strip_tags($data['content']), 0, 200),
            $data['image'] ?? null,
            $data['author'] ?? null,
            $data['status'] ?? 'published'
        ];
        
        $this->db->query($sql, $params);
        $postId = $this->db->lastInsertId();
        
        // Kategorileri ekle
        if (isset($data['categories']) && is_array($data['categories'])) {
            foreach ($data['categories'] as $categoryId) {
                $this->addPostToCategory($postId, $categoryId);
            }
        }
        
        return $postId;
    }
    
    public function updatePost($id, $data) {
        $sql = "UPDATE blog_posts SET 
                title = ?, 
                slug = ?, 
                content = ?, 
                excerpt = ?, 
                image = ?, 
                author = ?, 
                status = ?, 
                updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?";
        
        $params = [
            $data['title'],
            $data['slug'] ?? $this->createSlug($data['title']),
            $data['content'],
            $data['excerpt'] ?? substr(strip_tags($data['content']), 0, 200),
            $data['image'] ?? null,
            $data['author'] ?? null,
            $data['status'] ?? 'published',
            $id
        ];
        
        $this->db->query($sql, $params);
        
        // Kategorileri güncelle
        if (isset($data['categories'])) {
            // Önce mevcut kategorileri temizle
            $this->removeAllCategoriesFromPost($id);
            
            // Yeni kategorileri ekle
            foreach ($data['categories'] as $categoryId) {
                $this->addPostToCategory($id, $categoryId);
            }
        }
        
        return true;
    }
    
    public function deletePost($id) {
        $sql = "DELETE FROM blog_posts WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function countPosts($status = 'published') {
        $sql = "SELECT COUNT(*) as total FROM blog_posts WHERE status = ?";
        $result = $this->db->fetch($sql, [$status]);
        return $result['total'];
    }
    
    // Kategoriler
    public function getAllCategories() {
        $sql = "SELECT c.*, COUNT(pc.post_id) as post_count 
                FROM blog_categories c 
                LEFT JOIN post_category pc ON c.id = pc.category_id 
                GROUP BY c.id 
                ORDER BY c.name";
        
        return $this->db->fetchAll($sql);
    }
    
    public function getCategoryById($id) {
        $sql = "SELECT * FROM blog_categories WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    public function getCategoryBySlug($slug) {
        $sql = "SELECT * FROM blog_categories WHERE slug = ?";
        return $this->db->fetch($sql, [$slug]);
    }
    
    public function getPostsByCategory($categoryId, $limit = 10, $offset = 0) {
        $sql = "SELECT p.* 
                FROM blog_posts p 
                JOIN post_category pc ON p.id = pc.post_id 
                WHERE pc.category_id = ? AND p.status = 'published' 
                ORDER BY p.created_at DESC 
                LIMIT ?, ?";
        
        return $this->db->fetchAll($sql, [$categoryId, $offset, $limit]);
    }
    
    public function getPostsByCategorySlug($slug, $limit = 10, $offset = 0) {
        $sql = "SELECT p.* 
                FROM blog_posts p 
                JOIN post_category pc ON p.id = pc.post_id 
                JOIN blog_categories c ON pc.category_id = c.id 
                WHERE c.slug = ? AND p.status = 'published' 
                ORDER BY p.created_at DESC 
                LIMIT ?, ?";
        
        return $this->db->fetchAll($sql, [$slug, $offset, $limit]);
    }
    
    public function createCategory($name, $slug = null) {
        $slug = $slug ?? $this->createSlug($name);
        
        $sql = "INSERT INTO blog_categories (name, slug) VALUES (?, ?)";
        $this->db->query($sql, [$name, $slug]);
        
        return $this->db->lastInsertId();
    }
    
    public function updateCategory($id, $name, $slug = null) {
        $slug = $slug ?? $this->createSlug($name);
        
        $sql = "UPDATE blog_categories SET name = ?, slug = ? WHERE id = ?";
        return $this->db->query($sql, [$name, $slug, $id]);
    }
    
    public function deleteCategory($id) {
        $sql = "DELETE FROM blog_categories WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    // Kategori bağlantıları
    public function addPostToCategory($postId, $categoryId) {
        $sql = "INSERT INTO post_category (post_id, category_id) VALUES (?, ?)";
        return $this->db->query($sql, [$postId, $categoryId]);
    }
    
    public function removePostFromCategory($postId, $categoryId) {
        $sql = "DELETE FROM post_category WHERE post_id = ? AND category_id = ?";
        return $this->db->query($sql, [$postId, $categoryId]);
    }
    
    public function removeAllCategoriesFromPost($postId) {
        $sql = "DELETE FROM post_category WHERE post_id = ?";
        return $this->db->query($sql, [$postId]);
    }
    
    // Yorumlar
    public function getCommentsByPostId($postId, $status = 'approved') {
        $sql = "SELECT * FROM blog_comments WHERE post_id = ? AND status = ? ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, [$postId, $status]);
    }
    
    public function addComment($postId, $name, $email, $comment) {
        $sql = "INSERT INTO blog_comments (post_id, name, email, comment) VALUES (?, ?, ?, ?)";
        $this->db->query($sql, [$postId, $name, $email, $comment]);
        
        return $this->db->lastInsertId();
    }
    
    public function approveComment($id) {
        $sql = "UPDATE blog_comments SET status = 'approved' WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function markCommentAsSpam($id) {
        $sql = "UPDATE blog_comments SET status = 'spam' WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function deleteComment($id) {
        $sql = "DELETE FROM blog_comments WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    // Yardımcı fonksiyonlar - Changed from private to public
    public function createSlug($text) {
        // Türkçe karakterleri değiştir
        $text = strtr($text, [
            'ı' => 'i', 'ğ' => 'g', 'ü' => 'u', 'ş' => 's', 'ö' => 'o', 'ç' => 'c',
            'İ' => 'I', 'Ğ' => 'G', 'Ü' => 'U', 'Ş' => 'S', 'Ö' => 'O', 'Ç' => 'C'
        ]);
        
        // Küçük harfe çevir
        $text = strtolower($text);
        
        // Alfanumerik olmayan karakterleri tire ile değiştir
        $text = preg_replace('/[^a-z0-9\-]/', '-', $text);
        
        // Birden fazla tireyi tek tireye indir
        $text = preg_replace('/-+/', '-', $text);
        
        // Baştaki ve sondaki tireleri kaldır
        $text = trim($text, '-');
        
        return $text;
    }
    
    // İlgili yazıları getir
    public function getRelatedPosts($postId, $limit = 3) {
        // İlgili yazı kategorilerini al
        $sql = "SELECT category_id FROM post_category WHERE post_id = ?";
        $categories = $this->db->fetchAll($sql, [$postId]);
        
        if (empty($categories)) {
            // Kategori yoksa, son yazıları getir
            $sql = "SELECT * FROM blog_posts WHERE id != ? AND status = 'published' ORDER BY created_at DESC LIMIT ?";
            return $this->db->fetchAll($sql, [$postId, $limit]);
        }
        
        // Kategori ID'lerini çıkar
        $categoryIds = array_column($categories, 'category_id');
        
        // SQL sorgusu için kategori ID'lerini string haline getir
        $inClause = implode(',', array_fill(0, count($categoryIds), '?'));
        
        // Aynı kategorideki diğer yazıları getir
        $sql = "SELECT DISTINCT p.* FROM blog_posts p 
                JOIN post_category pc ON p.id = pc.post_id 
                WHERE p.id != ? AND pc.category_id IN ($inClause) AND p.status = 'published' 
                ORDER BY p.created_at DESC 
                LIMIT ?";
        
        $params = array_merge([$postId], $categoryIds, [$limit]);
        
        return $this->db->fetchAll($sql, $params);
    }
}