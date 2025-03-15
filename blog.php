<!-- blog.php - Blog Ana Sayfası
<?php
require_once 'includes/init.php';

$blog = new Blog();

// Sayfalama
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 6;
$offset = ($page - 1) * $perPage;

// Yazıları getir
$posts = $blog->getAllPosts($perPage, $offset);
$totalPosts = $blog->countPosts();
$totalPages = ceil($totalPosts / $perPage);

// Kategorileri getir
$categories = $blog->getAllCategories();

$pageTitle = "Blog";
$pageDescription = "Aksu Emlak - Emlak dünyasındaki son gelişmeler, tavsiyeler ve piyasa analizleri.";

require_once 'templates/header.php';
?>

<!-- Sayfa Başlık -->
<div class="page-header">
    <div class="container">
        <h1>Emlak Rehberi</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Ana Sayfa</a></li>
                <li class="breadcrumb-item active" aria-current="page">Blog</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-5">
    <div class="row">
        <!-- Blog Yazıları -->
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
                        <div class="alert alert-info">Henüz blog yazısı bulunmuyor.</div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Sayfalama -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?>">Önceki</a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?>">Sonraki</a>
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
                        <?php foreach ($categories as $category): ?>
                            <li class="list-group-item bg-transparent px-0">
                                <a href="blog-category.php?slug=<?= htmlspecialchars($category['slug']) ?>" class="text-decoration-none d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($category['name']) ?>
                                    <span class="badge bg-primary rounded-pill"><?= $category['post_count'] ?></span>
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

<?php require_once 'templates/footer.php'; ?>