<?php
// admin/blog/blog-category.php - Frontend for displaying blog posts by category
require_once '../includes/init.php';

// Check if category slug is provided
if (!isset($_GET['slug'])) {
    header('Location: blog.php');
    exit;
}

$slug = $_GET['slug'];
$blog = new Blog();
$category = $blog->getCategoryBySlug($slug);

// If category doesn't exist, redirect to blog page
if (!$category) {
    header('Location: blog.php');
    exit;
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 6;
$offset = ($page - 1) * $perPage;

// Get posts by category
$posts = $blog->getPostsByCategorySlug($slug, $perPage, $offset);

// Count total posts for pagination
$sql = "SELECT COUNT(*) as total FROM blog_posts p 
         JOIN post_category pc ON p.id = pc.post_id 
         JOIN blog_categories c ON pc.category_id = c.id 
         WHERE c.slug = ? AND p.status = 'published'";
$result = $blog->db->fetch($sql, [$slug]);
$totalPosts = $result['total'];
$totalPages = ceil($totalPosts / $perPage);

// Get all categories for sidebar
$categories = $blog->getAllCategories();

$pageTitle = $category['name'] . " - Blog";
$pageDescription = "Aksu Emlak - " . $category['name'] . " kategorisindeki emlak yazıları ve makaleler.";

require_once '../templates/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1><?= htmlspecialchars($category['name']) ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Ana Sayfa</a></li>
                <li class="breadcrumb-item"><a href="blog.php">Blog</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($category['name']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-5">
    <div class="row">
        <!-- Blog Posts -->
        <div class="col-lg-8">
            <div class="row">
                <?php if (!empty($posts)): ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card blog-card h-100">
                                <img src="<?= htmlspecialchars($post['image'] ?? 'assets/img/blog-placeholder.jpg') ?>" 
                                     class="card-img-top" 
                                     alt="<?= htmlspecialchars($post['title']) ?>"
                                     loading="lazy">
                                <div class="card-body">
                                    <p class="blog-date mb-2"><i class="bi bi-calendar3"></i> <?= date('d F Y', strtotime($post['created_at'])) ?></p>
                                    <h5 class="card-title"><?= htmlspecialchars($post['title']) ?></h5>
                                    <p class="blog-excerpt"><?= htmlspecialchars($post['excerpt'] ?? substr(strip_tags($post['content']), 0, 150) . '...') ?></p>
                                    <a href="blog-detail.php?slug=<?= htmlspecialchars($post['slug']) ?>" class="btn btn-outline-primary">Devamını Oku</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info">Bu kategoride henüz yazı bulunmuyor.</div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?slug=<?= htmlspecialchars($slug) ?>&page=<?= $page - 1 ?>">Önceki</a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?slug=<?= htmlspecialchars($slug) ?>&page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?slug=<?= htmlspecialchars($slug) ?>&page=<?= $page + 1 ?>">Sonraki</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h3 class="h5 mb-3">Kategoriler</h3>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($categories as $cat): ?>
                            <li class="list-group-item bg-transparent px-0 <?= $cat['id'] == $category['id'] ? 'active' : '' ?>">
                                <a href="blog-category.php?slug=<?= htmlspecialchars($cat['slug']) ?>" 
                                   class="text-decoration-none d-flex justify-content-between align-items-center <?= $cat['id'] == $category['id'] ? 'text-white' : '' ?>">
                                    <?= htmlspecialchars($cat['name']) ?>
                                    <span class="badge <?= $cat['id'] == $category['id'] ? 'bg-white text-primary' : 'bg-primary text-white' ?> rounded-pill">
                                        <?= $cat['post_count'] ?>
                                    </span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h3 class="h5 mb-3">Günün İpucu</h3>
                    <div class="card-text">
                        <p>Ev satın alırken sadece evin kendisine değil, çevresindeki altyapı ve sosyal imkanlara da dikkat edin. Ulaşım, okullar, sağlık hizmetleri ve alışveriş merkezlerine yakınlık, yaşam kalitenizi doğrudan etkileyecektir.</p>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h3 class="h5 mb-3">Bizimle İletişime Geçin</h3>
                    <p>Emlak ile ilgili sorularınız mı var? Uzman ekibimiz size yardımcı olmaktan mutluluk duyacaktır.</p>
                    <a href="contact.php" class="btn btn-primary w-100">İletişime Geçin</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?>