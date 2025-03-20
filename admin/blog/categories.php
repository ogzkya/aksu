<?php
// admin/blog/categories.php
require_once '../../includes/init.php';

$auth = new Auth();
$auth->requireLogin();

$blog = new Blog();
$errors = [];
$success = false;
$editingCategory = null;

// Kategori ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $name = $_POST['name'] ?? '';
        $slug = $_POST['slug'] ?? '';
        
        // Doğrulama
        if (empty($name)) {
            $errors[] = 'Kategori adı gereklidir.';
        } else {
            // Kategori adının benzersiz olup olmadığını kontrol et
            $existingCategory = $blog->db->fetch("SELECT * FROM blog_categories WHERE name = ?", [$name]);
            if ($existingCategory) {
                $errors[] = 'Bu isimde bir kategori zaten var.';
            }
            
            // Eğer özel slug girilmişse kontrol et
            if (!empty($slug)) {
                $existingSlug = $blog->db->fetch("SELECT * FROM blog_categories WHERE slug = ?", [$slug]);
                if ($existingSlug) {
                    $errors[] = 'Bu slug ile bir kategori zaten var.';
                }
            }
            
            // Hata yoksa kategoriyi ekle
            if (empty($errors)) {
                try {
                    $blog->createCategory($name, $slug);
                    $success = true;
                } catch (Exception $e) {
                    $errors[] = 'Kategori eklenirken hata: ' . $e->getMessage();
                }
            }
        }
    } elseif ($_POST['action'] === 'edit' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $name = $_POST['name'] ?? '';
        $slug = $_POST['slug'] ?? '';
        
        // Doğrulama
        if (empty($name)) {
            $errors[] = 'Kategori adı gereklidir.';
        } else {
            // Kategori adının benzersiz olup olmadığını kontrol et (kendi ID'si hariç)
            $existingCategory = $blog->db->fetch("SELECT * FROM blog_categories WHERE name = ? AND id != ?", [$name, $id]);
            if ($existingCategory) {
                $errors[] = 'Bu isimde bir kategori zaten var.';
            }
            
            // Eğer özel slug girilmişse kontrol et
            if (!empty($slug)) {
                $existingSlug = $blog->db->fetch("SELECT * FROM blog_categories WHERE slug = ? AND id != ?", [$slug, $id]);
                if ($existingSlug) {
                    $errors[] = 'Bu slug ile bir kategori zaten var.';
                }
            }
            
            // Hata yoksa kategoriyi güncelle
            if (empty($errors)) {
                try {
                    $blog->updateCategory($id, $name, $slug);
                    $success = true;
                } catch (Exception $e) {
                    $errors[] = 'Kategori güncellenirken hata: ' . $e->getMessage();
                }
            }
        }
    }
}

// Kategori silme işlemi
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Kategori kullanımda mı kontrol et
    $sql = "SELECT COUNT(*) as count FROM post_category WHERE category_id = ?";
    $result = $blog->db->fetch($sql, [$id]);
    
    if ($result && $result['count'] > 0) {
        $errors[] = 'Bu kategori kullanımda olduğu için silinemez. Önce ilişkili yazıları başka kategorilere taşıyın.';
    } else {
        // Kategoriyi sil
        try {
            $blog->deleteCategory($id);
            $success = true;
        } catch (Exception $e) {
            $errors[] = 'Kategori silinirken hata: ' . $e->getMessage();
        }
    }
}

// Düzenleme işlemi için kategori bilgisi
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $editingCategory = $blog->getCategoryById($id);
}

// Tüm kategorileri getir
$categories = $blog->getAllCategories();

$pageTitle = "Blog Kategorileri";
$activePage = "blog";
require_once '../templates/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Blog Kategorileri</h1>
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
        İşlem başarıyla tamamlandı.
    </div>
<?php endif; ?>

<div class="row">
    <!-- Kategori Listesi -->
    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Kategoriler</h6>
            </div>
            <div class="card-body">
                <?php if (empty($categories)): ?>
                    <p class="text-center">Henüz kategori bulunmuyor.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Kategori Adı</th>
                                    <th>Slug</th>
                                    <th>Yazı Sayısı</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $category): ?>
                                    <tr>
                                        <td><?= $category['id'] ?></td>
                                        <td><?= htmlspecialchars($category['name']) ?></td>
                                        <td><?= htmlspecialchars($category['slug']) ?></td>
                                        <td><?= $category['post_count'] ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="categories.php?edit=<?= $category['id'] ?>" class="btn btn-info">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <?php if ($category['post_count'] == 0): ?>
                                                    <a href="categories.php?delete=<?= $category['id'] ?>" class="btn btn-danger" onclick="return confirm('Bu kategoriyi silmek istediğinize emin misiniz?');">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <button class="btn btn-secondary" disabled title="Bu kategori kullanımda olduğu için silinemez">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Kategori Ekleme/Düzenleme Formu -->
    <div class="col-md-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <?= $editingCategory ? 'Kategori Düzenle' : 'Yeni Kategori Ekle' ?>
                </h6>
            </div>
            <div class="card-body">
                <form action="categories.php<?= $editingCategory ? '?edit=' . $editingCategory['id'] : '' ?>" method="post">
                    <input type="hidden" name="action" value="<?= $editingCategory ? 'edit' : 'add' ?>">
                    <?php if ($editingCategory): ?>
                        <input type="hidden" name="id" value="<?= $editingCategory['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Kategori Adı <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($editingCategory['name'] ?? '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug</label>
                        <input type="text" class="form-control" id="slug" name="slug" value="<?= htmlspecialchars($editingCategory['slug'] ?? '') ?>">
                        <div class="form-text">Boş bırakırsanız, kategori adından otomatik oluşturulur.</div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <?php if ($editingCategory): ?>
                            <a href="categories.php" class="btn btn-secondary">İptal</a>
                        <?php else: ?>
                            <div></div>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary">
                            <?= $editingCategory ? 'Güncelle' : 'Kategori Ekle' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Kategori adından otomatik slug oluşturma
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    
    nameInput.addEventListener('blur', function() {
        if (!slugInput.value && this.value) {
            // Basit bir slug oluşturma
            let slug = this.value.toLowerCase()
                .replace(/[ğ]/g, 'g').replace(/[ü]/g, 'u').replace(/[ş]/g, 's')
                .replace(/[ı]/g, 'i').replace(/[ö]/g, 'o').replace(/[ç]/g, 'c')
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-+|-+$/g,
                .replace(/^-+|-+$/g, '');
                
            slugInput.value = slug;
        }
    });
});
</script>

<?php require_once '../templates/footer.php'; ?>