<?php
require_once '../../includes/init.php';

$auth = new Auth();
$auth->requireLogin();

$listing = new Listing();

// Filtreler ve sayfalama
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Arama filtresi
$search = $_GET['search'] ?? '';
$filters = [];

if (!empty($search)) {
    $filters['search'] = $search;
}

// İlanları getir
$listings = $listing->getAllListings($perPage, $offset, $filters);
$totalListings = $listing->countListings($filters);
$totalPages = ceil($totalListings / $perPage);

$pageTitle = "İlan Yönetimi";
$activePage = "listings";
require_once '../templates/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">İlan Yönetimi</h1>
    <a href="add.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Yeni İlan Ekle
    </a>
</div>

<!-- Arama Formu -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form action="index.php" method="get" class="row g-3">
            <div class="col-md-10">
                <input type="text" name="search" class="form-control" placeholder="İlan başlığı veya adresi ara..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Ara</button>
            </div>
        </form>
    </div>
</div>

<!-- İlanlar Tablosu -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">İlanlar</h6>
        <span class="text-muted">Toplam: <?= $totalListings ?> ilan</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Başlık</th>
                        <th>Kategori</th>
                        <th>Fiyat</th>
                        <th>Emlakçı</th>
                        <th>Durum</th>
                        <th>Tarih</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($listings)): ?>
                        <tr>
                            <td colspan="8" class="text-center">Henüz ilan yok</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($listings as $listing): ?>
                            <tr>
                                <td><?= $listing['id'] ?></td>
                                <td><?= htmlspecialchars($listing['title']) ?></td>
                                <td><?= ucfirst($listing['category']) ?></td>
                                <td>
                                    <?php if ($listing['sale_price']): ?>
                                        <?= number_format($listing['sale_price']) ?> ₺
                                    <?php endif; ?>
                                    <?php if ($listing['rent_price']): ?>
                                        <br><?= number_format($listing['rent_price']) ?> ₺/ay
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $listing['agent_name'] ? htmlspecialchars($listing['agent_name']) : '<span class="text-muted">Belirtilmemiş</span>' ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $listing['status'] === 'active' ? 'success' : 'secondary' ?>">
                                        <?= $listing['status'] === 'active' ? 'Aktif' : 'Pasif' ?>
                                    </span>
                                </td>
                                <td><?= date('d.m.Y', strtotime($listing['created_at'])) ?></td>
                                <td>
                                    <a href="view.php?id=<?= $listing['id'] ?>" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="edit.php?id=<?= $listing['id'] ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="delete.php?id=<?= $listing['id'] ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Bu ilanı silmek istediğinizden emin misiniz?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
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
                            <a class="page-link" href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                Önceki
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
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
