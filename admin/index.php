<?php
// admin/index.php
require_once '../includes/init.php';

$auth = new Auth();
$auth->requireLogin();

$listing = new Listing();
$message = new Message();

// İstatistikler
$totalListings = $listing->countListings();
$saleListings = $listing->countListings(['listing_type' => 'sale']);
$rentListings = $listing->countListings(['listing_type' => 'rent']);
$featuredListings = $listing->countListings(['featured' => 1]);

// Son eklenen ilanlar
$recentListings = $listing->getAllListings(5, 0);

// Son mesajlar
$recentMessages = $message->getAllMessages(5, 0);

// Admin kullanıcıları
$users = $auth->getAllUsers();

$pageTitle = "Yönetim Paneli";
$activePage = "dashboard";
require_once 'templates/header.php';
?>

<div class="container-fluid">
    <!-- Sayfa Başlığı -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        <a href="listings/add.php" class="d-sm-inline-block btn btn-primary shadow-sm">
            <i class="bi bi-plus-circle"></i> Yeni İlan Ekle
        </a>
    </div>

    <!-- İstatistik Kartları Satırı -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card status-card status-card-primary h-100">
                <div class="card-body">
                    <h5 class="status-title">Toplam İlan</h5>
                    <div class="status-value"><?= $totalListings ?></div>
                    <p class="mb-0 small">Sistemdeki tüm ilanlar</p>
                    <i class="bi bi-house status-icon"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card status-card status-card-danger h-100">
                <div class="card-body">
                    <h5 class="status-title">Satılık İlanlar</h5>
                    <div class="status-value"><?= $saleListings ?></div>
                    <p class="mb-0 small">Toplam satılık mülkler</p>
                    <i class="bi bi-currency-dollar status-icon"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card status-card status-card-success h-100">
                <div class="card-body">
                    <h5 class="status-title">Kiralık İlanlar</h5>
                    <div class="status-value"><?= $rentListings ?></div>
                    <p class="mb-0 small">Toplam kiralık mülkler</p>
                    <i class="bi bi-calendar3 status-icon"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card status-card status-card-warning h-100">
                <div class="card-body">
                    <h5 class="status-title">Öne Çıkan İlanlar</h5>
                    <div class="status-value"><?= $featuredListings ?></div>
                    <p class="mb-0 small">Ana sayfada görüntülenenler</p>
                    <i class="bi bi-star status-icon"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- İçerik Satırı -->
    <div class="row">
        <!-- Son Eklenen İlanlar -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="card-title">Son Eklenen İlanlar</h6>
                    <a href="listings/index.php" class="btn btn-sm btn-primary">
                        Tüm İlanlar
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Başlık</th>
                                    <th>Tür</th>
                                    <th>Fiyat</th>
                                    <th>Tarih</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentListings as $item): ?>
                                    <tr>
                                        <td><?= $item['id'] ?></td>
                                        <td>
                                            <a href="../listing.php?id=<?= $item['id'] ?>" target="_blank" class="text-decoration-none">
                                                <?= htmlspecialchars($item['title']) ?>
                                            </a>
                                            <?php if ($item['featured']): ?>
                                                <span class="badge bg-warning text-dark ms-1">Öne Çıkan</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($item['rent_price'] > 0): ?>
                                                <span class="badge bg-success">Kiralık</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Satılık</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($item['rent_price'] > 0): ?>
                                                <?= number_format($item['rent_price'], 0, ',', '.') ?> ₺/ay
                                            <?php else: ?>
                                                <?= number_format($item['sale_price'], 0, ',', '.') ?> ₺
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d.m.Y', strtotime($item['created_at'])) ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="listings/edit.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-icon btn-primary" title="Düzenle">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="listings/delete.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-icon btn-danger" title="Sil" onclick="return confirm('Bu ilanı silmek istediğinize emin misiniz?');">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                
                                <?php if (count($recentListings) === 0): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Henüz ilan bulunmuyor.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sağ Kolon -->
        <div class="col-xl-4 col-lg-5">
            <!-- Son Mesajlar -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="card-title">Son Mesajlar</h6>
                    <a href="messages/index.php" class="btn btn-sm btn-primary">
                        Tümü
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentMessages as $message): ?>
                            <a href="messages/view.php?id=<?= $message['id'] ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= htmlspecialchars($message['name']) ?></h6>
                                    <small class="text-muted"><?= date('d.m.Y', strtotime($message['created_at'])) ?></small>
                                </div>
                                <p class="mb-1 text-truncate"><?= htmlspecialchars($message['subject']) ?></p>
                                <small class="text-muted"><?= substr(htmlspecialchars($message['message']), 0, 60) ?>...</small>
                            </a>
                        <?php endforeach; ?>
                        
                        <?php if (count($recentMessages) === 0): ?>
                            <div class="list-group-item">
                                <p class="mb-0 text-center">Henüz mesaj bulunmuyor.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Admin Kullanıcıları -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="card-title">Admin Kullanıcıları</h6>
                    <?php if (count($users) < 5): ?>
                        <a href="users/add.php" class="btn btn-sm btn-primary">
                            <i class="bi bi-person-plus"></i> Ekle
                        </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Kullanıcı Adı</th>
                                    <th>E-posta</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= $user['id'] ?></td>
                                        <td><?= htmlspecialchars($user['username']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Hızlı Erişim Kartı -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="card-title">Hızlı Erişim</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <a href="listings/add.php" class="btn btn-primary w-100 py-3">
                                <i class="bi bi-house-add d-block fs-3 mb-2"></i>
                                İlan Ekle
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="users/index.php" class="btn btn-info w-100 py-3 text-white">
                                <i class="bi bi-people d-block fs-3 mb-2"></i>
                                Kullanıcılar
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="blog/posts.php" class="btn btn-success w-100 py-3">
                                <i class="bi bi-file-earmark-text d-block fs-3 mb-2"></i>
                                Blog
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="../index.php" target="_blank" class="btn btn-warning w-100 py-3">
                                <i class="bi bi-globe d-block fs-3 mb-2"></i>
                                Siteyi Gör
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>