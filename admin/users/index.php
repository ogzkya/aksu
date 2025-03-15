// admin/users/index.php
<?php
require_once '../../includes/init.php';

$auth = new Auth();
$auth->requireLogin();

// Kullanıcıları getir
$users = $auth->getAllUsers();

$pageTitle = "Kullanıcı Yönetimi";
$activePage = "users";
require_once '../templates/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Kullanıcı Yönetimi</h1>
    
    <?php if (count($users) < 5): ?>
        <a href="add.php" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> Yeni Kullanıcı Ekle
        </a>
    <?php endif; ?>
</div>

<?php if (isset($_GET['added']) && $_GET['added'] == 1): ?>
    <div class="alert alert-success">
        Kullanıcı başarıyla eklendi.
    </div>
<?php endif; ?>

<?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
    <div class="alert alert-success">
        Kullanıcı bilgileri başarıyla güncellendi.
    </div>
<?php endif; ?>

<?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
    <div class="alert alert-success">
        Kullanıcı başarıyla silindi.
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        <?php 
        $error = $_GET['error'];
        switch ($error) {
            case 'max_users':
                echo 'Maksimum kullanıcı sayısına ulaşıldı (5).';
                break;
            case 'min_users':
                echo 'En az bir admin kullanıcısı gereklidir.';
                break;
            default:
                echo 'Bir hata oluştu.';
        }
        ?>
    </div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Admin Kullanıcıları</h6>
        <span>Toplam: <?= count($users) ?>/5 kullanıcı</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kullanıcı Adı</th>
                        <th>E-posta</th>
                        <th>Oluşturulma Tarihi</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="edit.php?id=<?= $user['id'] ?>" class="btn btn-info" title="Düzenle">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if (count($users) > 1): ?>
                                        <a href="delete.php?id=<?= $user['id'] ?>" class="btn btn-danger" title="Sil" onclick="return confirm('Bu kullanıcıyı silmek istediğinize emin misiniz?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?>