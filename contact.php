// contact.php
<?php
require_once 'includes/init.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $messageText = $_POST['message'] ?? '';
    
    // Doğrulama
    if (empty($name)) {
        $errors[] = 'İsim gereklidir.';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Geçerli bir e-posta adresi giriniz.';
    }
    
    if (empty($subject)) {
        $errors[] = 'Konu gereklidir.';
    }
    
    if (empty($messageText)) {
        $errors[] = 'Mesaj gereklidir.';
    }
    
    // Hata yoksa mesajı ekle
    if (empty($errors)) {
        $messageData = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'subject' => $subject,
            'message' => $messageText
        ];
        
        $message = new Message();
        $message->addMessage($messageData);
        
        $success = true;
        
        // Formu temizle
        $name = $email = $phone = $subject = $messageText = '';
    }
}

$pageTitle = "İletişim";
$pageDescription = "Aksu Emlak - Bizimle iletişime geçin. Size yardımcı olmaktan mutluluk duyarız.";

require_once 'templates/header.php';
?>

<!-- Sayfa Başlık -->
<div class="page-header">
    <div class="container">
        <h1>İletişim</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Ana Sayfa</a></li>
                <li class="breadcrumb-item active" aria-current="page">İletişim</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-5">
    <div class="row">
        <!-- İletişim Bilgileri -->
        <div class="col-lg-4 mb-4 mb-lg-0">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h3 class="h4 mb-4">İletişim Bilgileri</h3>
                    
                    <div class="d-flex mb-4">
                        <div class="contact-icon me-3">
                            <i class="bi bi-geo-alt-fill text-primary fs-3"></i>
                        </div>
                        <div>
                            <h5 class="h6">Adres</h5>
                            <p class="mb-0">Atatürk Cad. No: 123<br>Aksu, Antalya</p>
                        </div>
                    </div>
                    
                    <div class="d-flex mb-4">
                        <div class="contact-icon me-3">
                            <i class="bi bi-telephone-fill text-primary fs-3"></i>
                        </div>
                        <div>
                            <h5 class="h6">Telefon</h5>
                            <p class="mb-0">+90 (242) 555 55 55</p>
                        </div>
                    </div>
                    
                    <div class="d-flex mb-4">
                        <div class="contact-icon me-3">
                            <i class="bi bi-envelope-fill text-primary fs-3"></i>
                        </div>
                        <div>
                            <h5 class="h6">E-posta</h5>
                            <p class="mb-0">info@aksu-emlak.com</p>
                        </div>
                    </div>
                    
                    <div class="d-flex">
                        <div class="contact-icon me-3">
                            <i class="bi bi-clock-fill text-primary fs-3"></i>
                        </div>
                        <div>
                            <h5 class="h6">Çalışma Saatleri</h5>
                            <p class="mb-0">Pazartesi - Cumartesi: 09:00 - 18:00<br>Pazar: Kapalı</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- İletişim Formu -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h3 class="h4 mb-4">Bize Mesaj Gönderin</h3>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <p class="mb-0">Mesajınız başarıyla gönderildi. En kısa sürede sizinle iletişime geçeceğiz.</p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form action="contact.php" method="post">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">İsim Soyisim <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($name ?? '') ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">E-posta <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Telefon</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($phone ?? '') ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="subject" class="form-label">Konu <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="subject" name="subject" value="<?= htmlspecialchars($subject ?? '') ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Mesajınız <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="message" name="message" rows="5" required><?= htmlspecialchars($messageText ?? '') ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary px-4 py-2">Mesaj Gönder</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Harita -->
    <div class="mt-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3195.7380204974!2d30.83650461527207!3d36.92039127991959!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14c390393d4cec97%3A0xf83942bc77d7a3da!2sAksu%2C%20Antalya!5e0!3m2!1str!2str!4v1647862231954!5m2!1str!2str" 
                        width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>