<?php
// admin/announcements/index.php
require_once '../../includes/init.php';

$auth = new Auth();
$auth->requireLogin();

$announcement = new Announcement();
$errors = [];
$success = false;

// Duyuru silme işlemi
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    try {
        $announcement->deleteAnnouncement($id);
        $success = true;
        $successMessage = 'Duyuru başarıyla silindi.';
    } catch (Exception $e) {
        $errors[] = 'Duyuru silinirken hata: ' . $e->getMessage();
    }
}

// Duyuruları getir
$announcements = $announcement->getAllAnnouncements();

$pageTitle = "Duyuru Yönetimi";
$activePage = "announcements";
require_once '../templates/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Duyuru Yönetimi</h1>
    <a href="add.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Yeni Duyuru Ekle
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
        <?= htmlspecialchars($successMessage ?? 'İşlem başarıyla tamamlandı.') ?>
    </div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Duyurular</h6>
    </div>
    <div class="card-body">
        <?php if (empty($announcements)): ?>
            <p class="text-center">Henüz duyuru bulunmuyor.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Başlık</th>
                            <th>İçerik</th>
                            <th>Durum</th>
                            <th>Tarih Aralığı</th>
                            <th>Öncelik</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($announcements as $item): ?>
                            <tr>
                                <td><?= $item['id'] ?></td>
                                <td><?= htmlspecialchars($item['title']) ?></td>
                                <td>
                                    <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <?= htmlspecialchars($item['content']) ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($item['is_active']): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Pasif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($item['start_date'] && $item['end_date']): ?>
                                        <?= date('d.m.Y', strtotime($item['start_date'])) ?> - <?= date('d.m.Y', strtotime($item['end_date'])) ?>
                                    <?php elseif ($item['start_date']): ?>
                                        <?= date('d.m.Y', strtotime($item['start_date'])) ?>'den itibaren
                                    <?php elseif ($item['end_date']): ?>
                                        <?= date('d.m.Y', strtotime($item['end_date'])) ?>'e kadar
                                    <?php else: ?>
                                        Süresiz
                                    <?php endif; ?>
                                </td>
                                <td><?= $item['priority'] ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="edit.php?id=<?= $item['id'] ?>" class="btn btn-info" title="Düzenle">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="index.php?delete=<?= $item['id'] ?>" 
                                           class="btn btn-danger" 
                                           title="Sil" 
                                           onclick="return confirm('Bu duyuruyu silmek istediğinize emin misiniz?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
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

<!-- Duyuru Önizleme -->
<div class="card shadow">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Aktif Duyuru Önizleme</h6>
    </div>
    <div class="card-body">
        <h5>Duyurular böyle görüntülenecek:</h5>
        
        <?php 
        $activeAnnouncements = $announcement->getActiveAnnouncements(); 
        if (empty($activeAnnouncements)):
        ?>
            <div class="alert alert-info">
                Aktif duyuru bulunmuyor. Duyuru eklemek için "Yeni Duyuru Ekle" butonunu kullanabilirsiniz.
            </div>
        <?php else: ?>
            <?php foreach ($activeAnnouncements as $item): ?>
                <div class="alert" style="background-color: <?= htmlspecialchars($item['bg_color']) ?>; color: <?= htmlspecialchars($item['text_color']) ?>; margin-bottom: 10px;">
                    <h5 class="alert-heading"><?= htmlspecialchars($item['title']) ?></h5>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($item['content'])) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?>