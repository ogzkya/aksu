<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <h3>Aksu Emlak Admin</h3>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?= $activePage==='dashboard'?'active':''?>" href="<?= $basePath ?>admin/index.php">
                <i class="bi bi-house-door-fill"></i>
                <span>Anasayfa</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $activePage==='properties'?'active':''?>" href="<?= $basePath ?>admin/properties/index.php">
                <i class="bi bi-building-fill"></i>
                <span>İlanlar</span>
            </a>
        </li>

        <!-- İçerik Yönetimi Başlığı -->
        <div class="sidebar-heading">
            İçerik Yönetimi
        </div>

        <!-- İlanlar -->
        <li class="nav-item">
            <a class="nav-link <?= $activePage==='listings'?'active':''?>" href="<?= $basePath ?>admin/listings/index.php">
                <i class="bi bi-house-fill"></i><span>İlanlar</span>
            </a>
        </li>

        <!-- Yeni: Emlakçılar -->
        <li class="nav-item">
            <a class="nav-link <?= $activePage==='agents'?'active':''?>" href="<?= $basePath ?>admin/agents/index.php">
                <i class="bi bi-people-fill"></i><span>Emlakçılar</span>
            </a>
        </li>

        <!-- Blog -->
        <li class="nav-item">
            <a class="nav-link <?= $activePage==='blog'?'active':''?>" href="<?= $basePath ?>admin/blog/index.php">
                <i class="bi bi-journal-text"></i>
                <span>Blog</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?= $activePage==='settings'?'active':''?>" href="<?= $basePath ?>admin/settings/index.php">
                <i class="bi bi-gear-fill"></i>
                <span>Ayarlar</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $activePage==='messages'?'active':''?>" href="<?= $basePath ?>admin/messages/index.php">
                <i class="bi bi-envelope-fill"></i>
                <span>Mesajlar</span>
            </a>
        </li>
    </ul>
</div>
<!-- End Sidebar -->