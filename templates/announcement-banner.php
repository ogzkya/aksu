<?php
// templates/announcement-banner.php
// Bu dosya, sitenin üst kısmında duyuruları göstermek için kullanılır

// Duyuru sınıfını yükle
require_once __DIR__ . '/../includes/Announcement.php';

// Aktif duyuruları getir
$announcementObj = new Announcement();
$announcements = $announcementObj->getActiveAnnouncements(3); // En fazla 3 duyuru göster
?>

<?php if (!empty($announcements)): ?>
<div class="announcement-container">
    <?php foreach ($announcements as $announcement): ?>
    <div class="announcement-banner" style="background-color: <?= htmlspecialchars($announcement['bg_color']) ?>; color: <?= htmlspecialchars($announcement['text_color']) ?>;">
        <div class="container py-2">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <strong><?= htmlspecialchars($announcement['title']) ?>:</strong>
                    <?= htmlspecialchars($announcement['content']) ?>
                </div>
                <button type="button" class="btn-close btn-close-white announcement-close" aria-label="Close"></button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<style>
.announcement-container {
    position: relative;
    z-index: 1020;
}

.announcement-banner {
    position: relative;
    width: 100%;
    transition: all 0.3s ease;
    margin-bottom: 1px;
}

.announcement-banner.hidden {
    max-height: 0;
    padding-top: 0;
    padding-bottom: 0;
    margin-bottom: 0;
    overflow: hidden;
    opacity: 0;
}

.announcement-close {
    cursor: pointer;
    opacity: 0.5;
    transition: opacity 0.2s;
}

.announcement-close:hover {
    opacity: 1;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Duyuru kapatma işlevi
    const closeBtns = document.querySelectorAll('.announcement-close');
    
    closeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const banner = this.closest('.announcement-banner');
            
            // Animasyonlu kapatma
            banner.classList.add('hidden');
            
            // DOM'dan kaldırma
            setTimeout(() => {
                banner.remove();
                
                // Tüm duyurular kapatıldıysa container'ı gizle
                const container = document.querySelector('.announcement-container');
                if (container && container.querySelectorAll('.announcement-banner:not(.hidden)').length === 0) {
                    container.style.display = 'none';
                }
            }, 300);
            
            // Kapatılan duyuruları localStorage'a kaydet
            const announcementId = banner.getAttribute('data-id');
            if (announcementId) {
                const closedAnnouncements = JSON.parse(localStorage.getItem('closedAnnouncements') || '[]');
                closedAnnouncements.push(announcementId);
                localStorage.setItem('closedAnnouncements', JSON.stringify(closedAnnouncements));
            }
        });
    });
    
    // Önceden kapatılan duyuruları kontrol et ve gizle
    const closedAnnouncements = JSON.parse(localStorage.getItem('closedAnnouncements') || '[]');
    if (closedAnnouncements.length > 0) {
        document.querySelectorAll('.announcement-banner[data-id]').forEach(banner => {
            const announcementId = banner.getAttribute('data-id');
            if (closedAnnouncements.includes(announcementId)) {
                banner.remove();
            }
        });
        
        // Tüm duyurular kapatıldıysa container'ı gizle
        const container = document.querySelector('.announcement-container');
        if (container && container.querySelectorAll('.announcement-banner:not(.hidden)').length === 0) {
            container.style.display = 'none';
        }
    }
});
</script>
<?php endif; ?>