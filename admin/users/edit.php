// admin/users/edit.php
<?php
require_once '../../includes/init.php';

$auth = new Auth();
$auth->requireLogin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$userId = (int)$_GET['id'];
$user = $auth->getUser($userId);

if (!$user) {
    header('Location: index.php');
    exit;
}

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
    $changePassword = !empty($newPassword);
    
    if ($changePassword) {
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

$pageTitle = "Kullanıcı Düzenle";
$activePage = "users";
require_once '../templates/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Kullanıcı Düzenle</h1>
    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kullanıcılara Dön
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
        Kullanıcı bilgileri başarıyla güncellendi.
    </div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-body">
        <form action="edit.php?id=<?= $userId ?>" method="post">
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
                <label for="new_password" class="form-label">Yeni Şifre</label>
                <input type="password" class="form-control" id="new_password" name="new_password">
                <div class="form-text">Değiştirmek istemiyorsanız boş bırakın. En az 6 karakter.</div>
            </div>
            
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Yeni Şifre Tekrarı</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Değişiklikleri Kaydet</button>
                <a href="index.php" class="btn btn-secondary ms-2">İptal</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?>