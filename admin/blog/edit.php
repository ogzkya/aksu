<?php
// admin/blog/edit.php
require_once '../../includes/init.php';

$auth = new Auth();
$auth->requireLogin();

// Post ID'si kontrol edilir
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$postId = (int)$_GET['id'];
$blog = new Blog();
$post = $blog->getPostById($postId);

// Post bulunamadıysa ana sayfaya yönlendir
if (!$post) {
    header('Location: index.php');
    exit;
}

// Kategorileri getir
$categories = $blog->getAllCategories();

// Post kategorilerini getir
$postCategories = [];
$sql = "SELECT category_id FROM post_category WHERE post_id = ?";
$postCategoryResults = $blog->db->fetchAll($sql, [$postId]);
foreach ($postCategoryResults as $category) {
    $postCategories[] = $category['category_id'];
}

$errors = [];
$success = isset($_GET['success']) ? true : false;

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $excerpt = $_POST['excerpt'] ?? '';
    $author = $_POST['author'] ?? '';
    $status = $_POST['status'] ?? 'published';
    $selectedCategories = $_POST['categories'] ?? [];
    $customSlug = !empty($_POST['custom_slug']);
    $slug = $_POST['slug'] ?? '';
    
    // Doğrulama
    if (empty($title)) {
        $errors[] = 'Başlık gereklidir.';
    }
    
    if (empty($content)) {
        $errors[] = 'İçerik gereklidir.';
    }
    
    // Eğer özel slug kullanılıyorsa ve değiştiyse, slug benzersiz mi kontrol et
    if ($customSlug && !empty($slug) && $slug !== $post['slug']) {
        $existingPost = $blog->getPostBySlug($slug);
        if ($existingPost && $existingPost['id'] != $postId) {
            $errors[] = 'Bu slug ile başka bir yazı zaten var.';
        }
    }
    
    // Hata yoksa blog yazısını güncelle
    if (empty($errors)) {
        $blogData = [
            'title' => $title,
            'content' => $content,
            'excerpt' => $excerpt,
            'author' => $author,
            'status' => $status,
            'categories' => $selectedCategories
        ];
        
        // Özel slug kullanılacaksa ekle
        if ($customSlug && !empty($slug)) {
            $blogData['slug'] = $slug;
        }
        
        // Resim yükleme
        if (!empty($_FILES['image']['name'])) {
            $image = new Image();
            try {
                $imageUrl = $image->upload($_FILES['image'], 'blog');
                $blogData['image'] = $imageUrl;
            } catch (Exception $e) {
                $errors[] = 'Görsel yüklenirken hata: ' . $e->getMessage();
            }
        }
        
        if (empty($errors)) {
            try {
                $blog->updatePost($postId, $blogData);
                
                // Başarılı güncelleme, yönlendirme yapalım
                header('Location: edit.php?id=' . $postId . '&success=1');
                exit;
            } catch (Exception $e) {
                $errors[] = 'Blog yazısı güncellenirken hata: ' . $e->getMessage();
            }
        }
    }
} else {
    // Form ilk kez yüklendiğinde, post verilerini doldur
    $title = $post['title'];
    $content = $post['content'];
    $excerpt = $post['excerpt'];
    $author = $post['author'];
    $status = $post['status'];
    $slug = $post['slug'];
}

$pageTitle = "Blog Yazısı Düzenle";
$activePage = "blog";
require_once '../templates/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Blog Yazısı Düzenle</h1>
    <div>
        <a href="../../blog-detail.php?slug=<?= htmlspecialchars($post['slug']) ?>" class="btn btn-info me-2" target="_blank">
            <i class="bi bi-eye"></i> Yazıyı Görüntüle
        </a>
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Blog Yazılarına Dön
        </a>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success">
        Blog yazısı başarıyla güncellendi.
    </div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <ul class="nav nav-tabs card-header-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="content-tab" data-bs-toggle="tab" href="#content" role="tab">
                    <i class="bi bi-file-text"></i> İçerik
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="meta-tab" data-bs-toggle="tab" href="#meta" role="tab">
                    <i class="bi bi-tags"></i> Kategoriler ve SEO
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="image-tab" data-bs-toggle="tab" href="#image" role="tab">
                    <i class="bi bi-image"></i> Kapak Görseli
                </a>
            </li>
        </ul>
    </div>
    
    <div class="card-body">
        <form action="edit.php?id=<?= $postId ?>" method="post" enctype="multipart/form-data">
            <div class="tab-content">
                <!-- İçerik Tab -->
                <div class="tab-pane fade show active" id="content" role="tabpanel">
                    <div class="mb-3">
                        <label for="title" class="form-label">Başlık <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($title) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">İçerik <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="content" name="content" rows="12" required><?= htmlspecialchars($content) ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="excerpt" class="form-label">Özet</label>
                        <textarea class="form-control" id="excerpt" name="excerpt" rows="3"><?= htmlspecialchars($excerpt) ?></textarea>
                        <div class="form-text">Blog listelerinde gösterilecek kısa özet. Boş bırakırsanız, içerikten otomatik oluşturulur.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="author" class="form-label">Yazar</label>
                        <input type="text" class="form-control" id="author" name="author" value="<?= htmlspecialchars($author) ?>">
                    </div>
                </div>
                
                <!-- Kategoriler ve SEO Tab -->
                <div class="tab-pane fade" id="meta" role="tabpanel">
                    <div class="mb-3">
                        <label class="form-label">Kategoriler</label>
                        <div class="categories-container">
                            <?php if (!empty($categories)): ?>
                                <?php foreach ($categories as $category): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="categories[]" value="<?= $category['id'] ?>" id="category-<?= $category['id'] ?>"
                                            <?= in_array($category['id'], $postCategories) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="category-<?= $category['id'] ?>">
                                            <?= htmlspecialchars($category['name']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>Henüz kategori bulunmuyor. <a href="categories.php">Kategori eklemek için tıklayın</a>.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="custom_slug" name="custom_slug" 
                                <?= isset($_POST['custom_slug']) || $slug !== $blog->createSlug($title) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="custom_slug">
                                Özel URL Kullan
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3" id="slug-container" style="display: <?= isset($_POST['custom_slug']) || $slug !== $blog->createSlug($title) ? 'block' : 'none' ?>;">
                        <label for="slug" class="form-label">SEO URL (Slug)</label>
                        <input type="text" class="form-control" id="slug" name="slug" value="<?= htmlspecialchars($slug) ?>">
                        <div class="form-text">Boş bırakırsanız, başlıktan otomatik oluşturulur.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Durum</label>
                        <select class="form-select" id="status" name="status">
                            <option value="published" <?= $status == 'published' ? 'selected' : '' ?>>Yayında</option>
                            <option value="draft" <?= $status == 'draft' ? 'selected' : '' ?>>Taslak</option>
                        </select>
                    </div>
                </div>
                
                <!-- Kapak Görseli Tab -->
                <div class="tab-pane fade" id="image" role="tabpanel">
                    <?php if (!empty($post['image'])): ?>
                        <div class="mb-4">
                            <label class="form-label">Mevcut Görsel</label>
                            <div class="card">
                                <img src="<?= htmlspecialchars($post['image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="img-fluid" style="max-height: 300px; object-fit: cover;">
                                <div class="card-body">
                                    <p class="text-muted mb-0">Görseli değiştirmek için aşağıdan yeni bir görsel seçebilirsiniz.</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">Kapak Görseli <?= empty($post['image']) ? '<span class="text-danger">*</span>' : '' ?></label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,image/jpg" <?= empty($post['image']) ? 'required' : '' ?>>
                        <div class="form-text">Önerilen boyutlar: 1200x800px. Maksimum dosya boyutu: 5MB</div>
                    </div>
                    
                    <div id="image-preview" class="mt-3" style="display: none;">
                        <div class="card">
                            <div class="card-body">
                                <img src="" alt="Kapak Görseli Önizleme" class="img-fluid">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 d-flex justify-content-between">
                <div>
                    <a href="index.php" class="btn btn-secondary">İptal</a>
                    <a href="delete.php?id=<?= $postId ?>" class="btn btn-danger ms-2" onclick="return confirm('Bu blog yazısını silmek istediğinize emin misiniz?');">
                        <i class="bi bi-trash"></i> Sil
                    </a>
                </div>
                <button type="submit" class="btn btn-primary">Değişiklikleri Kaydet</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // WYSIWYG Editor (TinyMCE veya benzeri bir editör ekleyebilirsiniz)
    // Örnek: TinyMCE yüklüyse
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '#content',
            height: 500,
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
        });
    }
    
    // Özel URL Seçeneği
    const customSlugCheckbox = document.getElementById('custom_slug');
    const slugContainer = document.getElementById('slug-container');
    
    customSlugCheckbox.addEventListener('change', function() {
        slugContainer.style.display = this.checked ? 'block' : 'none';
    });
    
    // Başlık değiştiğinde otomatik slug önerisi
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    
    titleInput.addEventListener('blur', function() {
        if (!customSlugCheckbox.checked && this.value) {
            // Basit bir slug oluşturma (daha gelişmiş bir versiyonu backend'de var)
            let slug = this.value.toLowerCase()
                .replace(/[ğ]/g, 'g').replace(/[ü]/g, 'u').replace(/[ş]/g, 's')
                .replace(/[ı]/g, 'i').replace(/[ö]/g, 'o').replace(/[ç]/g, 'c')
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-+|-+$/g, '');
                
            slugInput.value = slug;
        }
    });
    
    // Görsel önizleme
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('image-preview');
    const previewImage = imagePreview.querySelector('img');
    
    imageInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(this.files[0]);
        } else {
            imagePreview.style.display = 'none';
        }
    });
});
</script>

<?php require_once '../templates/footer.php'; ?>