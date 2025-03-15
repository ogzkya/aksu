// admin/users/add.php
<?php
require_once '../../includes/init.php';

$auth = new Auth();
$auth->requireLogin();

$errors = [];
$success = false;

// Kullanıcıların sayısını kontrol et
$users = $auth->getAllUsers();
if (count($users) >= 5) {
    header('Location: index.php?error=max_users');
    exit;
}

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Doğrulama kontrolleri
    if (empty($username)) {
        $errors[] = 'Kullanıcı adı gereklidir.';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Kullanıcı adı en az 3 karakter olmalıdır.';
    }
    
    if (empty($email)) {
        $errors[] = 'E-posta adresi gereklidir.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Geçerli bir e-posta adresi giriniz.';
    }
    
    if (empty($password)) {
        $errors[] = 'Şifre gereklidir.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Şifre en az 6 karakter olmalıdır.';
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = 'Şifreler eşleşmiyor.';
    }
    
    // Kullanıcı adı ve e-posta benzersiz mi kontrol et
    foreach ($users as $user) {
        if ($user['username'] === $username) {
            $errors[] = 'Bu kullanıcı adı zaten kullanılıyor.';
        }
        
        if ($user['email'] === $email) {
            $errors[] = 'Bu e-posta adresi zaten kullanılıyor.';
        }
    }
    
    // Hata yoksa kullanıcı ekle
    if (empty($errors)) {
        try {
            $auth->createUser($username, $password, $email);
            
            // Başarılı, yönlendirme yap
            header('Location: index.php?added=1');
            exit;
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
}

$pageTitle = "Yeni Kullanıcı Ekle";
$activePage = "users";
require_once '../templates/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Yeni Kullanıcı Ekle</h1>
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

<div class="card shadow mb-4">
    <div class="card-body">
        <form action="add.php" method="post">
            <div class="mb-3">
                <label for="username" class="form-label">Kullanıcı Adı <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                <div class="form-text">En az 3 karakter, benzersiz olmalıdır</div>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">E-posta <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Şifre <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="password" name="password" required>
                <div class="form-text">En az 6 karakter</div>
            </div>
            
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Şifre Tekrarı <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Kullanıcı Ekle</button>
                <a href="index.php" class="btn btn-secondary ms-2">İptal</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?>