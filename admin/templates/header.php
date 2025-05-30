<?php
if (!isset($auth)) {
    require_once '../includes/init.php';
    $auth = new Auth();
    $auth->requireLogin();
}

if (!isset($pageTitle)) {
    $pageTitle = 'Yönetim Paneli';
}

if (!isset($activePage)) {
    $activePage = '';
}

// BasePath tanımı admin paneli için
$basePath = '../../';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - Aksu Emlak Admin</title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    
    <!-- Admin CSS - TEK DOSYA -->
    <link rel="stylesheet" href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/assets/css/admin-clean.css">
    
    <!-- Meta tags -->
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="Aksu Emlak Admin Panel">
</head>
<body id="page-top">
    <div id="wrapper">
        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Brand -->
            <a class="sidebar-brand" href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/index.php">
                <div class="sidebar-brand-icon">
                    <i class="bi bi-building"></i>
                </div>
                <div class="sidebar-brand-text">Aksu Emlak</div>
            </a>
            
            <!-- Divider -->
            <hr class="sidebar-divider">
            
            <!-- Dashboard -->
            <ul class="navbar-nav">
                <li class="nav-item <?= $activePage === 'dashboard' ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/index.php">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
            </ul>
            
            <!-- Divider -->
            <hr class="sidebar-divider">
            
            <!-- Heading -->
            <div class="sidebar-heading">İçerik Yönetimi</div>
            
            <!-- Navigation -->
            <ul class="navbar-nav">
                <li class="nav-item <?= $activePage === 'listings' ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/listings/index.php">
                        <i class="bi bi-houses"></i>
                        <span>İlanlar</span>
                    </a>
                </li>
                
                <!-- EMLAKÇILAR MENÜSÜ EKLENDİ -->
                <li class="nav-item <?= $activePage === 'agents' ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/agents/index.php">
                        <i class="bi bi-people-fill"></i>
                        <span>Emlakçılar</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/listings/add.php">
                        <i class="bi bi-plus-circle"></i>
                        <span>Yeni İlan</span>
                    </a>
                </li>
                <li class="nav-item <?= $activePage === 'blog' ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/blog/index.php">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>Blog</span>
                    </a>
                </li>
                <li class="nav-item <?= $activePage === 'announcements' ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/announcements/index.php">
                        <i class="bi bi-megaphone"></i>
                        <span>Duyurular</span>
                    </a>
                </li>
                <li class="nav-item <?= $activePage === 'features' ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/features/index.php">
                        <i class="bi bi-list-check"></i>
                        <span>Özellik Yönetimi</span>
                    </a>
                </li>
            </ul>
            
            <!-- Divider -->
            <hr class="sidebar-divider">
            
            <!-- Heading -->
            <div class="sidebar-heading">Sistem</div>
            
            <!-- System Navigation -->
            <ul class="navbar-nav">
                <li class="nav-item <?= $activePage === 'users' ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/users/index.php">
                        <i class="bi bi-people"></i>
                        <span>Kullanıcılar</span>
                    </a>
                </li>
            </ul>
            
            <!-- Sidebar Toggler -->
            <div class="text-center d-none d-md-inline mt-4">
                <button class="btn btn-link text-white" id="sidebarToggle">
                    <i class="bi bi-chevron-left"></i>
                </button>
            </div>
        </div>
        <!-- End Sidebar -->
        
        <!-- Content Wrapper -->
        <div id="content-wrapper">
            <!-- Topbar -->
            <nav class="navbar navbar-expand navbar-light bg-white topbar static-top">
                <!-- Sidebar Toggle (Mobile) -->
                <button id="sidebarToggleTop" class="btn btn-link d-md-none">
                    <i class="bi bi-list"></i>
                </button>
                
                <!-- Page Title -->
                <div class="d-none d-sm-inline-block">
                    <h1 class="h4 mb-0 text-gray-800"><?= htmlspecialchars($pageTitle) ?></h1>
                </div>
                
                <!-- Topbar Navbar -->
                <ul class="navbar-nav ms-auto">
                    <!-- Quick Links -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>index.php" target="_blank" title="Siteyi Görüntüle">
                            <i class="bi bi-globe"></i>
                        </a>
                    </li>
                    
                    <!-- User Info -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="me-2 d-none d-lg-inline text-gray-600">
                                <?= htmlspecialchars($_SESSION['username']) ?>
                            </span>
                            <i class="bi bi-person-circle fs-5"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/users/profile.php">
                                    <i class="bi bi-person"></i>
                                    Profil
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/logout.php">
                                    <i class="bi bi-box-arrow-right"></i>
                                    Çıkış Yap
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>
            <!-- End Topbar -->
            
            <!-- Main Content -->
            <div class="container-fluid">