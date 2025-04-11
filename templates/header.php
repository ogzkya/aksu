<!DOCTYPE html>
<html lang="tr">
<head>
<base href="<?= $config['app']['url'] ?>/">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= isset($pageDescription) ? htmlspecialchars($pageDescription) : 'Aksu Emlak - Satılık ve kiralık emlak ilanları' ?>">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?>Aksu Emlak</title>
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css">
<link rel="stylesheet" href="assets/css/optimized.css">
    
    <!-- Site CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/map-markers.css">
    <link rel="stylesheet" href="assets/css/modern-styles.css">
    <link rel="stylesheet" href="assets/css/color-scheme.css">
    <!-- Adım 1: templates/header.php dosyasına aşağıdaki kod parçasını hemen </head> etiketinden önce ekleyin -->

<!-- templates/header.php dosyasına </head> etiketinden önce ekleyin -->

    
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
     <a class="navbar-brand d-flex align-items-center" href="index.php">
    <div class="logo-container me-4">
        <img src="assets/img/logo.png" alt="" class="site-logo" onerror="this.src='assets/img/logo.png'; this.onerror='';">
    </div>
    <!-- <span><span class="text-accent fw-bold"></span>Aksu Emlak</span> -->
</a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= $activePage === 'home' ? 'active' : '' ?>" href="index.php">Ana Sayfa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $activePage === 'sale' ? 'active' : '' ?>" href="search.php?listing_type=sale">Satılık</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $activePage === 'rent' ? 'active' : '' ?>" href="search.php?listing_type=rent">Kiralık</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $activePage === 'blog' ? 'active' : '' ?>" href="blog.php">Blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $activePage === 'contact' ? 'active' : '' ?>" href="contact.php">İletişim</a>
                    </li>
                    <?php
                    // Admin erişimi kontrolü
                    if (isset($_SESSION['user_id'])): 
                    ?>
                    <li class="nav-item ms-2">
                        <a class="btn btn-primary rounded-pill px-3" href="admin/index.php">
                            <i class="bi bi-speedometer2 me-1"></i> Yönetim Paneli
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Ana İçerik Başlangıç -->
    <main>
      
<?php
// Önce duyuruları göster
?>
