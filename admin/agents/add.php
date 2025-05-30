<?php
require_once '../../includes/init.php';
$auth = new Auth();
$auth->requireLogin();

require_once '../../includes/Agent.php';
$agentModel = new Agent();
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name'      => trim($_POST['name'] ?? ''),
        'phone'     => trim($_POST['phone'] ?? ''),
        'email'     => trim($_POST['email'] ?? ''),
        'photo_url' => trim($_POST['photo_url'] ?? '')
    ];
    
    if (empty($data['name'])) {
        $errors[] = 'Ad alanı zorunludur.';
    }
    
    if (empty($errors)) {
        try {
            $agentModel->create($data);
            header('Location: index.php?success=1');
            exit;
        } catch (Exception $e) {
            $errors[] = 'Kaydetme hatası: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Yeni Emlakçı Ekle';
$activePage = 'agents';
require_once '../templates/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Yeni Emlakçı Ekle</h1>
    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Geri Dön
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
        <form method="post">
            <div class="mb-3">
                <label for="name" class="form-label">Ad <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" name="name" 
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="phone" class="form-label">Telefon</label>
                <input type="text" class="form-control" id="phone" name="phone" 
                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" 
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            
            <div class="mb-3">
                <label for="photo_url" class="form-label">Fotoğraf URL</label>
                <input type="url" class="form-control" id="photo_url" name="photo_url" 
                       value="<?= htmlspecialchars($_POST['photo_url'] ?? '') ?>">
                <div class="form-text">Örnek: https://example.com/foto.jpg</div>
            </div>
            
            <button type="submit" class="btn btn-success">
                <i class="bi bi-check-circle"></i> Kaydet
            </button>
            <a href="index.php" class="btn btn-secondary">İptal</a>
        </form>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?>
