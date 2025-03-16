<!-- admin/templates/header.php -->
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
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - Emlak İlan Sitesi</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css">
    
    <!-- Admin CSS -->
    <link rel="stylesheet" href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/assets/css/admin.css">
    
    <!-- Custom styles override -->
    <style>
        /* Hide PHP warnings */
        .warning, div[class*="warning"] {
            display: none !important;
        }
    </style>
</head>
<body id="page-top">
    <div id="wrapper">
        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/index.php">
                <div class="sidebar-brand-icon">
                    <i class="bi bi-building"></i>
                </div>
                <div class="sidebar-brand-text mx-3">Emlak Admin</div>
            </a>
            
            <!-- Divider -->
            <hr class="sidebar-divider my-0">
            
            <!-- Nav Item - Dashboard -->
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
            
            <!-- Nav Item - İlanlar -->
            <ul class="navbar-nav">
                <li class="nav-item <?= $activePage === 'listings' ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/listings/index.php">
                        <i class="bi bi-houses"></i>
                        <span>İlanlar</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/listings/add.php">
                        <i class="bi bi-plus-circle"></i>
                        <span>Yeni İlan Ekle</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/blog/index.php">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>Blog</span>
                    </a>
                </li>
            </ul>
            
            <!-- Divider -->
            <hr class="sidebar-divider">
            
            <!-- Heading -->
            <div class="sidebar-heading">Kullanıcı Yönetimi</div>
            
            <!-- Nav Item - Kullanıcılar -->
            <ul class="navbar-nav">
                <li class="nav-item <?= $activePage === 'users' ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/users/index.php">
                        <i class="bi bi-people"></i>
                        <span>Kullanıcılar</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/users/profile.php">
                        <i class="bi bi-person-circle"></i>
                        <span>Profil</span>
                    </a>
                </li>
            </ul>
            
            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">
            
            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle">
                    <i class="bi bi-chevron-left"></i>
                </button>
            </div>
        </div>
        <!-- End of Sidebar -->
        
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="bi bi-list"></i>
                    </button>
                    
                    <!-- Page Title -->
                    <h1 class="h3 mb-0 text-gray-800 d-none d-md-inline-block"><?= htmlspecialchars($pageTitle) ?></h1>
                    
                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                    <?= htmlspecialchars($_SESSION['username']) ?>
                                </span>
                                <i class="bi bi-person-circle fs-5"></i>
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/users/profile.php">
                                    <i class="bi bi-person fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profil
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/logout.php">
                                    <i class="bi bi-box-arrow-right fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Çıkış Yap
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>
                <!-- End of Topbar -->
                
                <!-- Begin Page Content -->
                <div class="container-fluid px-4">