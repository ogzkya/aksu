<?php
// admin/announcements/add.php
require_once '../../includes/init.php';

$auth = new Auth();
$auth->requireLogin();

$announcement = new Announcement();
$errors = [];
$success = false;

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $bgColor = $_POST['bg_color'] ?? '#f8d7da';
    $textColor = $_POST['text_color'] ?? '#721c24';
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    $priority = isset($_POST['priority']) ? (int)$_POST['priority'] : 0;
    $isActive = isset($_POST['is_active']);
    
    // Doğrulama
    if (empty($title)) {
        $errors[] = 'Başlık gereklidir.';
    }
    
    if (empty($content)) {
        $errors[] = 'İçerik gereklidir.';
    }
    
    // Tarih kontrolü
    if (!empty($startDate) && !empty($endDate)) {
        $startTimestamp = strtotime($startDate);
        $endTimestamp = strtotime($endDate);
        
        if ($startTimestamp > $endTimestamp) {
            $errors[] = 'Başlangıç tarihi, bitiş tarihinden sonra olamaz.';
        }
    }
    
    // Hata yoksa duyuru ekle
    if (empty($errors)) {
        $data = [
            'title' => $title,
            'content' => $content,
            'bg_color' => $bgColor,
            'text_color' => $textColor,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'priority' => $priority,
            'is_active' => $isActive
        ];
        
        try {
            $announcement->addAnnouncement($data);
            $success = true;
        } catch (Exception $e) {
            $errors[] = 'Duyuru eklenirken hata: ' . $e->getMessage();
        }
    }
}

$pageTitle = "Yeni Duyuru Ekle";
$activePage = "announcements";
require_once '../templates/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Yeni Duyuru Ekle</h1>
    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Duyurulara Dön
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
        Duyuru başarıyla eklendi. <a href="index.php">Duyurulara dönmek için tıklayın</a>.
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Duyuru Bilgileri</h6>
            </div>
            <div class="card-body">
                <form action="add.php" method="post">
                    <div class="mb-3">
                        <label for="title" class="form-label">Başlık <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">İçerik <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="content" name="content" rows="5" required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Başlangıç Tarihi</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?= htmlspecialchars($_POST['start_date'] ?? '') ?>">
                            <div class="form-text">Boş bırakırsanız, duyuru hemen aktif olur.</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label">Bitiş Tarihi</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?= htmlspecialchars($_POST['end_date'] ?? '') ?>">
                            <div class="form-text">Boş bırakırsanız, duyuru süresiz olarak gösterilir.</div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="bg_color" class="form-label">Arkaplan Rengi</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="bg_color" name="bg_color" value="<?= htmlspecialchars($_POST['bg_color'] ?? '#f8d7da') ?>">
                                <input type="text" class="form-control" id="bg_color_text" value="<?= htmlspecialchars($_POST['bg_color'] ?? '#f8d7da') ?>" readonly>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="text_color" class="form-label">Yazı Rengi</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="text_color" name="text_color" value="<?= htmlspecialchars($_POST['text_color'] ?? '#721c24') ?>">
                                <input type="text" class="form-control" id="text_color_text" value="<?= htmlspecialchars($_POST['text_color'] ?? '#721c24') ?>" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="priority" class="form-label">Öncelik</label>
                            <input type="number" class="form-control" id="priority" name="priority" value="<?= htmlspecialchars($_POST['priority'] ?? '0') ?>" min="0" max="10">
                            <div class="form-text">Yüksek değerler daha öncelikli gösterilir (0-10 arası).</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label d-block">Durum</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?= isset($_POST['is_active']) ? 'checked' : 'checked' ?>>
                                <label class="form-check-label" for="is_active">Aktif</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Duyuru Ekle</button>
                        <a href="index.php" class="btn btn-secondary">İptal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Önizleme</h6>
            </div>
            <div class="card-body">
                <div id="announcement-preview" class="alert" style="background-color: #f8d7da; color: #721c24;">
                    <h5 class="alert-heading" id="preview-title">Duyuru Başlığı</h5>
                    <p class="mb-0" id="preview-content">Duyuru içeriği burada görüntülenecek.</p>
                </div>
                
                <div class="alert alert-info">
                    <h5 class="alert-heading">Bilgi</h5>
                    <p class="mb-0">Duyurular, site genelinde önemli bilgileri kullanıcılara iletmek için kullanılır. Önemli etkinlikler, geçici kapatmalar veya güncellemeler için duyuru ekleyebilirsiniz.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.getElementById('title');
    const contentInput = document.getElementById('content');
    const bgColorInput = document.getElementById('bg_color');
    const textColorInput = document.getElementById('text_color');
    const bgColorText = document.getElementById('bg_color_text');
    const textColorText = document.getElementById('text_color_text');
    
    const previewTitle = document.getElementById('preview-title');
    const previewContent = document.getElementById('preview-content');
    const previewContainer = document.getElementById('announcement-preview');
    
    // Initial preview
    previewTitle.textContent = titleInput.value || 'Duyuru Başlığı';
    previewContent.textContent = contentInput.value || 'Duyuru içeriği burada görüntülenecek.';
    
    // Update preview on input
    titleInput.addEventListener('input', function() {
        previewTitle.textContent = this.value || 'Duyuru Başlığı';
    });
    
    contentInput.addEventListener('input', function() {
        previewContent.textContent = this.value || 'Duyuru içeriği burada görüntülenecek.';
    });
    
    bgColorInput.addEventListener('input', function() {
        previewContainer.style.backgroundColor = this.value;
        bgColorText.value = this.value;
    });
    
    textColorInput.addEventListener('input', function() {
        previewContainer.style.color = this.value;
        textColorText.value = this.value;
    });
});
</script>

<?php require_once '../templates/footer.php'; ?>