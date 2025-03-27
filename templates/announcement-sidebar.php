<?php
// templates/announcement-sidebar.php
// Yan panelde duyuruları göstermek için kullanılır (arama çubuğunun yanında olabilir)

// Duyuru sınıfını yükle
require_once __DIR__ . '/../includes/Announcement.php';

// Aktif duyuruları getir
$announcementObj = new Announcement();
$announcements = $announcementObj->getActiveAnnouncements(3); // En fazla 3 duyuru göster
?>

<?php if (!empty($announcements)): ?>
<!-- Duyurular Yan Panel -->
<div class="card shadow-sm mb-4 announcement-sidebar">
    <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0"><i class="bi bi-megaphone me-2"></i>Duyurular</h5>
    </div>
    <div class="card-body p-0">
        <?php foreach ($announcements as $announcement): ?>
        <div class="sidebar-announcement p-3" 
             style="border-left: 4px solid <?= htmlspecialchars($announcement['bg_color']) ?>; 
                    <?= $announcement !== end($announcements) ? 'border-bottom: 1px solid #eee;' : '' ?>">
            <h6 style="color: <?= htmlspecialchars($announcement['text_color']) ?>;">
                <?= htmlspecialchars($announcement['title']) ?>
            </h6>
            <p class="small mb-0"><?= htmlspecialchars($announcement['content']) ?></p>
            
            <?php if ($announcement['end_date']): ?>
            <div class="announcement-date small text-muted mt-2">
                <i class="bi bi-clock"></i> 
                <?= date('d.m.Y', strtotime($announcement['end_date'])) ?> tarihine kadar
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.announcement-sidebar {
    border-radius: 0.5rem;
    overflow: hidden;
}

.sidebar-announcement {
    transition: background-color 0.3s ease;
}

.sidebar-announcement:hover {
    background-color: #f8f9fa;
}

.card-header.bg-primary {
    background: linear-gradient(to right, #4338ca, #6366f1) !important;
}
</style>
<?php endif; ?>