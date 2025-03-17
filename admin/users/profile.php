<?php
// admin/users/profile.php
require_once '../../includes/init.php';

$auth = new Auth();
$auth->requireLogin();

// Kullanıcı bilgilerini getir
$userId = $_SESSION['user_id'];
$user = $auth->getUser($userId);

$errors = [];
$success = false;

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Doğrulama kontrolleri
    if (empty($email)) {
        $errors[] = 'E-posta adresi gereklidir.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Geçerli bir e-posta adresi giriniz.';
    }
    
    // E-posta benzersiz mi kontrol et
    $users = $auth->getAllUsers();
    foreach ($users as $u) {
        if ($u['id'] != $userId && $u['email'] === $email) {
            $errors[] = 'Bu e-posta adresi zaten kullanılıyor.';
        }
    }
    
    // Şifre değiştirilecek mi kontrol et
    $changePassword = !empty($newPassword) || !empty($confirmPassword);
    
    if ($changePassword) {
        // Mevcut şifreyi kontrol et
        if (empty($currentPassword)) {
            $errors[] = 'Mevcut şifrenizi girmelisiniz.';
        } elseif (!$auth->verifyPassword($userId, $currentPassword)) {
            $errors[] = 'Mevcut şifreniz yanlış.';
        }
        
        if (strlen($newPassword) < 6) {
            $errors[] = 'Yeni şifre en az 6 karakter olmalıdır.';
        }
        
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'Yeni şifreler eşleşmiyor.';
        }
    }
    
    // Hata yoksa kullanıcıyı güncelle
    if (empty($errors)) {
        try {
            if ($changePassword) {
                $auth->updateUser($userId, $email, $newPassword);
            } else {
                $auth->updateUser($userId, $email);
            }
            
            $success = true;
            
            // Kullanıcı bilgilerini yenile
            $user = $auth->getUser($userId);
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
}

$pageTitle = "Profil Yönetimi";
$activePage = "profile";
require_once '../templates/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Profil Yönetimi</h1>
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
        <i class="bi bi-check-circle me-2"></i> Profil bilgileri başarıyla güncellendi.
    </div>
<?php endif; ?>

<div class="row">
    <!-- Sol Kolon: Profil Formu -->
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Profil Bilgileri</h6>
            </div>
            <div class="card-body">
                <form action="profile.php" method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label">Kullanıcı Adı</label>
                        <input type="text" class="form-control" id="username" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                        <div class="form-text">Kullanıcı adı değiştirilemez</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">E-posta <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    
                    <h5 class="mt-4 mb-3">Şifre Değiştir</h5>
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Mevcut Şifre</label>
                        <input type="password" class="form-control" id="current_password" name="current_password">
                        <div class="form-text">Şifre değiştirmek istiyorsanız mevcut şifrenizi girin</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Yeni Şifre</label>
                        <input type="password" class="form-control" id="new_password" name="new_password">
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Yeni Şifre Tekrarı</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i> Değişiklikleri Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Sağ Kolon: Profil Resmi ve Ek Bilgiler -->
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Profil Resmi</h6>
            </div>
            <div class="card-body text-center">
                <?php if (!empty($user['profile_image'])): ?>
                    <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="Profil Resmi" class="img-fluid rounded-circle mb-3" style="max-width: 150px;">
                <?php else: ?>
                    <img src="assets/img/default-profile.png" alt="Varsayılan Profil Resmi" class="img-fluid rounded-circle mb-3" style="max-width: 150px;">
                <?php endif; ?>
                <p class="mb-0"><strong><?= htmlspecialchars($user['username']) ?></strong></p>
                <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>
                <a href="change_profile_image.php" class="btn btn-sm btn-secondary mt-2">Profil Resmini Değiştir</a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?>
