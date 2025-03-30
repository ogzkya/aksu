<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= isset($pageDescription) ? htmlspecialchars($pageDescription) : 'Aksu Emlak - Satılık ve kiralık emlak ilanları' ?>">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?>Aksu Emlak</title>
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="manifest" href="/site.webmanifest">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css">
    
    <!-- Site CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Harita Marker Stilleri -->
<link rel="stylesheet" href="/assets/css/map-markers.css">
<!-- Modern Stiller -->
<link rel="stylesheet" href="/assets/css/modern-styles.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-house-door text-primary"></i> Aksu Emlak
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Ana Sayfa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="search.php?listing_type=sale">Satılık</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="search.php?listing_type=rent">Kiralık</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="blog.php">Blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">İletişim</a>
                    </li>
                    <?php
                    // Admin erişimi kontrolü
                    if (isset($_SESSION['user_id'])): 
                    ?>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-primary ms-2" href="admin/index.php">
                            Yönetim Paneli
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

