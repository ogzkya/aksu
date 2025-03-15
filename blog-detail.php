// blog-detail.php - Blog Yazı Detay Sayfası
<?php
require_once 'includes/init.php';

// Yazı slug kontrolü
if (!isset($_GET['slug'])) {
    header('Location: blog.php');
    exit;
}

$slug = $_GET['slug'];
$blog = new Blog();
$post = $blog->getPostBySlug($slug);

if (!$post) {
    header('Location: blog.php');
    exit;
}

// Yorum işleme
$errors = [];
$commentSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $comment = $_POST['comment'] ?? '';
    
    // Doğrulama
    if (empty($name)) {
        $errors[] = 'İsim gereklidir.';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Geçerli bir e-posta adresi giriniz.';
    }
    
    if (empty($comment)) {
        $errors[] = 'Yorum gereklidir.';
    }
    
    // Hata yoksa yorumu ekle
    if (empty($errors)) {
        $blog->addComment($post['id'], $name, $email, $comment);
        $commentSuccess = true;
        
        // Formu temizle
        $name = $email = $comment = '';
    }
}

// Onaylanmış yorumları getir
$comments = $blog->getCommentsByPostId($post['id']);

// İlgili yazıları getir
$relatedPosts = $blog->getRelatedPosts($post['id']);

// Kategorileri getir
$categories = $blog->getAllCategories();

$pageTitle = htmlspecialchars($post['title']);
$pageDescription = htmlspecialchars($post['excerpt'] ?? substr(strip_tags($post['content']), 0, 160));

require_once 'templates/header.php';
?>

<!-- Sayfa Başlık -->
<div class="page-header">
    <div class="container">
        <h1><?= htmlspecialchars($post['title']) ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Ana Sayfa</a></li>
                <li class="breadcrumb-item"><a href="blog.php">Blog</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($post['title']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-5">
    <div class="row">
        <!-- Blog İçeriği -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <img src="<?= htmlspecialchars($post['image'] ?? 'assets/img/blog-placeholder.jpg') ?>" 
                     class="card-img-top" 
                     alt="<?= htmlspecialchars($post['title']) ?>"
                     style="max-height: 400px; object-fit: cover;">
                <div class="card-body p-4">
                    <div class="blog-meta mb-4">
                        <span class="me-3"><i class="bi bi-calendar3 me-1"></i> <?= date('d F Y', strtotime($post['created_at'])) ?></span>
                        <?php if ($post['author']): ?>
                            <span class="me-3"><i class="bi bi-person me-1"></i> <?= htmlspecialchars($post['author']) ?></span>
                        <?php endif; ?>
                        <?php if ($post['categories']): ?>
                            <span><i class="bi bi-tags me-1"></i> 
                                <?php
                                    $categoriesArray = explode(',', $post['categories']);
                                    $categorySlugsArray = explode(',', $post['category_slugs']);
                                    
                                    $links = [];
                                    for ($i = 0; $i < count($categoriesArray); $i++) {
                                        $links[] = '<a href="blog-category.php?slug=' . htmlspecialchars($categorySlugsArray[$i]) . '">' . htmlspecialchars($categoriesArray[$i]) . '</a>';
                                    }
                                    
                                    echo implode(', ', $links);
                                ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="blog-content">
                        <?= $post['content'] ?>
                    </div>
                </div>
            </div>
            
            <!-- İlgili Yazılar -->
            <?php if (!empty($relatedPosts)): ?>
                <div class="related-posts mb-5">
                    <h3 class="h4 mb-4">İlgili Yazılar</h3>
                    <div class="row">
                        <?php foreach ($relatedPosts as $relatedPost): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card blog-card h-100">
                                    <img src="<?= htmlspecialchars($relatedPost['image'] ?? 'assets/img/blog-placeholder.jpg') ?>" 
                                         class="card-img-top" 
                                         alt="<?= htmlspecialchars($relatedPost['title']) ?>"
                                         loading="lazy">
                                    <div class="card-body">
                                        <p class="blog-date mb-2"><i class="bi bi-calendar3"></i> <?= date('d F Y', strtotime($relatedPost['created_at'])) ?></p>
                                        <h5 class="card-title" style="font-size: 1rem;"><?= htmlspecialchars($relatedPost['title']) ?></h5>
                                        <a href="blog-detail.php?slug=<?= htmlspecialchars($relatedPost['slug']) ?>" class="btn btn-sm btn-outline-primary">Devamını Oku</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Yorumlar -->
            <div class="comments mb-5">
                <h3 class="h4 mb-4">Yorumlar (<?= count($comments) ?>)</h3>
                
                <?php if (!empty($comments)): ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment-item">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <?= strtoupper(substr($comment['name'], 0, 1)) ?>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="comment-header">
                                        <h5 class="comment-author"><?= htmlspecialchars($comment['name']) ?></h5>
                                        <p class="comment-date mb-2"><?= date('d F Y, H:i', strtotime($comment['created_at'])) ?></p>
                                    </div>
                                    <div class="comment-content">
                                        <p><?= nl2br(htmlspecialchars($comment['comment'])) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <p class="mb-0">Henüz yorum yapılmamış. İlk yorumu siz yapın!</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Yorum Formu -->
            <div class="comment-form">
                <h3 class="h4 mb-4">Yorum Yapın</h3>
                
                <?php if ($commentSuccess): ?>
                    <div class="alert alert-success">
                        <p class="mb-0">Yorumunuz başarıyla gönderildi. Onaylandıktan sonra yayınlanacaktır.</p>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form action="blog-detail.php?slug=<?= htmlspecialchars($slug) ?>" method="post">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">İsim <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($name ?? '') ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">E-posta <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
                            <div class="form-text">E-posta adresiniz yayınlanmayacaktır.</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="comment" class="form-label">Yorumunuz <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="comment" name="comment" rows="5" required><?= htmlspecialchars($comment ?? '') ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary px-4 py-2">Yorum Gönder</button>
                </form>
            </div>
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