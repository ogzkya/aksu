<?php
// admin/blog/index.php
require_once '../../includes/init.php';

$auth = new Auth();
$auth->requireLogin();

$blog = new Blog();

// Sayfalama ve filtreleme
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

// Duruma göre yazıları getir
if ($status === 'all') {
    $posts = $blog->getAllPosts($perPage, $offset, null);
    $totalPosts = $blog->countPosts(null);
} else {
    $posts = $blog->getAllPosts($perPage, $offset, $status);
    $totalPosts = $blog->countPosts($status);
}

$totalPages = ceil($totalPosts / $perPage);

$pageTitle = "Blog Yönetimi";
$activePage = "blog";
require_once '../templates/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Blog Yönetimi</h1>
    <a href="add.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Yeni Yazı Ekle
    </a>
</div>

<?php if (isset($_GET['added']) && $_GET['added'] == 1): ?>
    <div class="alert alert-success">
        Yazı başarıyla eklendi.
    </div>
<?php endif; ?>

<?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
    <div class="alert alert-success">
        Yazı başarıyla güncellendi.
    </div>
<?php endif; ?>

<?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
    <div class="alert alert-success">
        Yazı başarıyla silindi.
    </div>
<?php endif; ?>

<!-- Filtreler -->
<div class="card shadow mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="btn-group" role="group">
                    <a href="index.php" class="btn btn-<?= $status === 'all' ? 'primary' : 'outline-primary' ?>">
                        Tüm Yazılar
                    </a>
                    <a href="index.php?status=published" class="btn btn-<?= $status === 'published' ? 'success' : 'outline-success' ?>">
                        Yayında
                    </a>
                    <a href="index.php?status=draft" class="btn btn-<?= $status === 'draft' ? 'secondary' : 'outline-secondary' ?>">
                        Taslak
                    </a>
                </div>
            </div>
            <div class="col-md-6">
                <form action="index.php" method="get" class="d-flex">
                    <input type="text" name="search" class="form-control me-2" placeholder="Yazı ara...">
                    <button type="submit" class="btn btn-outline-primary">Ara</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Blog Yazıları Tablosu -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Blog Yazıları</h6>
        <a href="categories.php" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-tag"></i> Kategorileri Yönet
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Başlık</th>
                        <th>Kategori</th>
                        <th>Yazar</th>
                        <th>Durum</th>
                        <th>Tarih</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($posts) > 0): ?>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td><?= $post['id'] ?></td>
                                <td>
                                    <a href="../../blog-detail.php?slug=<?= $post['slug'] ?>" target="_blank">
                                        <?= htmlspecialchars($post['title']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($post['categories'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($post['author'] ?? '—') ?></td>
                                <td>
                                    <?php if ($post['status'] === 'published'): ?>
                                        <span class="badge bg-success">Yayında</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Taslak</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d.m.Y', strtotime($post['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="edit.php?id=<?= $post['id'] ?>" class="btn btn-info" title="Düzenle">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="delete.php?id=<?= $post['id'] ?>" class="btn btn-danger" title="Sil" 
                                           onclick="return confirm('Bu yazıyı silmek istediğinize emin misiniz?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">Henüz blog yazısı bulunmuyor.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Sayfalama -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center mt-4">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page - 1 ?><?= $status !== 'all' ? '&status=' . $status : '' ?>">
                                Önceki
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?><?= $status !== 'all' ? '&status=' . $status : '' ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?><?= $status !== 'all' ? '&status=' . $status : '' ?>">
                                Sonraki
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?>