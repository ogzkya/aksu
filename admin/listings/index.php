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
            <table class="table table-bordered table-striped">
                <!-- Güncellenmiş Tablo Başlığı -->
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Resim</th>
                        <th>Başlık</th>
                        <th>İlan Tipi</th>
                        <th>Fiyat</th>
                        <th>Lokasyon</th>
                        <th>Kategori</th>
                        <th>Durum</th>
                        <th>Tarih</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <!-- Güncellenmiş Tablo Gövdesi -->
                <tbody>
                    <?php foreach ($listings as $item): ?>
                        <tr>
                            <td><?= $item['id'] ?></td>
                            <td class="text-center">
                                <?php if (!empty($item['main_image'])): ?>
                                    <img src="<?= htmlspecialchars($item['main_image']) ?>" 
                                         alt="" 
                                         class="img-thumbnail" 
                                         style="width: 60px; height: 40px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center" 
                                         style="width: 60px; height: 40px;">
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="../../listing.php?id=<?= $item['id'] ?>" target="_blank">
                                    <?= htmlspecialchars($item['title']) ?>
                                </a>
                            </td>
                            <td>
                                <?php if ($item['rent_price'] && $item['rent_price'] > 0): ?>
                                    <?php if ($item['sale_price'] && $item['sale_price'] > 0): ?>
                                        <span class="badge bg-info">Satılık/Kiralık</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Kiralık</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge bg-danger">Satılık</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($item['rent_price'] && $item['rent_price'] > 0): ?>
                                    <?= number_format($item['rent_price'], 0, ',', '.') ?> ₺/ay
                                    <?php if ($item['sale_price'] && $item['sale_price'] > 0): ?>
                                        <br><?= number_format($item['sale_price'], 0, ',', '.') ?> ₺
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?= number_format($item['sale_price'], 0, ',', '.') ?> ₺
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($item['city']) ?></td>
                            <td>
                                <?php
                                $categories = [
                                    'House' => 'Müstakil Ev',
                                    'Apartment' => 'Daire',
                                    'Commercial' => 'Ticari',
                                    'Land' => 'Arsa',
                                    'Other' => 'Diğer'
                                ];
                                echo $categories[$item['category']] ?? $item['category'];
                                ?>
                            </td>
                            <td>
                                <?php if ($item['featured']): ?>
                                    <span class="badge bg-primary">Öne Çıkan</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Normal</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d.m.Y', strtotime($item['created_at'])) ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="edit.php?id=<?= $item['id'] ?>" class="btn btn-info" title="Düzenle">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="feature.php?id=<?= $item['id'] ?>" class="btn btn-warning" title="<?= $item['featured'] ? 'Öne Çıkarma' : 'Öne Çıkar' ?>">
                                        <i class="bi bi-star<?= $item['featured'] ? '-fill' : '' ?>"></i>
                                    </a>
                                    <a href="delete.php?id=<?= $item['id'] ?>" class="btn btn-danger" title="Sil" onclick="return confirm('Bu ilanı silmek istediğinize emin misiniz?');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <?php if (count($listings) === 0): ?>
                        <tr>
                            <td colspan="10" class="text-center">İlan bulunamadı</td>
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
