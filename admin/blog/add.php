<?php
// admin/blog/add.php
require_once '../../includes/init.php';

$auth = new Auth();
$auth->requireLogin();

$blog = new Blog();
$errors = [];
$success = false;

// Kategorileri getir
$categories = $blog->getAllCategories();

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $excerpt = $_POST['excerpt'] ?? '';
    $author = $_POST['author'] ?? '';
    $status = $_POST['status'] ?? 'published';
    $selectedCategories = $_POST['categories'] ?? [];
    $customSlug = !empty($_POST['custom_slug']) ? $_POST['slug'] : null;
    
    // Doğrulama
    if (empty($title)) {
        $errors[] = 'Başlık gereklidir.';
    }
    
    if (empty($content)) {
        $errors[] = 'İçerik gereklidir.';
    }
    
    // Başlık benzersiz mi kontrol et
    $existingPost = $blog->getPostBySlug($blog->createSlug($title));
    if ($existingPost) {
        $errors[] = 'Bu başlıkla bir yazı zaten var.';
    }
    
    // Hata yoksa blog yazısını ekle
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
        if (!empty($customSlug)) {
            $blogData['slug'] = $customSlug;
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
                $postId = $blog->createPost($blogData);
                $success = true;
            } catch (Exception $e) {
                $errors[] = 'Blog yazısı eklenirken hata: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = "Yeni Blog Yazısı Ekle";
$activePage = "blog";
require_once '../templates/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Yeni Blog Yazısı Ekle</h1>
    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Blog Yazılarına Dön
    </a>
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
        Blog yazısı başarıyla eklendi. <a href="edit.php?id=<?= $postId ?>">Düzenlemek için tıklayın</a> veya 
        <a href="../../blog-detail.php?slug=<?= $blog->getPostById($postId)['slug'] ?>" target="_blank">görüntülemek için tıklayın</a>.
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
        <form action="add.php" method="post" enctype="multipart/form-data">
            <div class="tab-content">
                <!-- İçerik Tab -->
                <div class="tab-pane fade show active" id="content" role="tabpanel">
                    <div class="mb-3">
                        <label for="title" class="form-label">Başlık <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">İçerik <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="content" name="content" rows="12" required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="excerpt" class="form-label">Özet</label>
                        <textarea class="form-control" id="excerpt" name="excerpt" rows="3"><?= htmlspecialchars($_POST['excerpt'] ?? '') ?></textarea>
                        <div class="form-text">Blog listelerinde gösterilecek kısa özet. Boş bırakırsanız, içerikten otomatik oluşturulur.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="author" class="form-label">Yazar</label>
                        <input type="text" class="form-control" id="author" name="author" value="<?= htmlspecialchars($_POST['author'] ?? $_SESSION['username']) ?>">
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
                                            <?= (isset($_POST['categories']) && in_array($category['id'], $_POST['categories'])) ? 'checked' : '' ?>>
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
                                <?= isset($_POST['custom_slug']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="custom_slug">
                                Özel URL Kullan
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3" id="slug-container" style="display: <?= isset($_POST['custom_slug']) ? 'block' : 'none' ?>;">
                        <label for="slug" class="form-label">SEO URL (Slug)</label>
                        <input type="text" class="form-control" id="slug" name="slug" value="<?= htmlspecialchars($_POST['slug'] ?? '') ?>">
                        <div class="form-text">Boş bırakırsanız, başlıktan otomatik oluşturulur.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Durum</label>
                        <select class="form-select" id="status" name="status">
                            <option value="published" <?= (!isset($_POST['status']) || $_POST['status'] == 'published') ? 'selected' : '' ?>>Yayında</option>
                            <option value="draft" <?= (isset($_POST['status']) && $_POST['status'] == 'draft') ? 'selected' : '' ?>>Taslak</option>
                        </select>
                    </div>
                </div>
                
                <!-- Kapak Görseli Tab -->
                <div class="tab-pane fade" id="image" role="tabpanel">
                    <div class="mb-3">
                        <label for="image" class="form-label">Kapak Görseli</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,image/jpg">
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
                <a href="index.php" class="btn btn-secondary">İptal</a>
                <button type="submit" class="btn btn-primary">Blog Yazısını Yayınla</button>
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
        if (!slugInput.value && this.value) {
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