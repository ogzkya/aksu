<?php
// templates/announcement-slider.php
// Duyuruları kayan slider şeklinde göstermek için kullanılır

// Duyuru sınıfını yükle
require_once __DIR__ . '/../includes/Announcement.php';

// Aktif duyuruları getir
$announcementObj = new Announcement();
$announcements = $announcementObj->getActiveAnnouncements(10); // En fazla 10 duyuru göster
?>

<?php if (!empty($announcements)): ?>
<!-- Duyuru Slider Bölümü -->
<section class="announcement-slider-section py-4 my-4 bg-light">
    <div class="container">
        <div class="row mb-3">
            <div class="col-md-12">
                <h3 class="section-title"><i class="bi bi-megaphone me-2"></i>Duyurular</h3>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div id="announcementCarousel" class="carousel slide" data-bs-ride="carousel">
                    <!-- Carousel Indicators -->
                    <?php if (count($announcements) > 1): ?>
                    <div class="carousel-indicators">
                        <?php for ($i = 0; $i < count($announcements); $i++): ?>
                        <button type="button" data-bs-target="#announcementCarousel" data-bs-slide-to="<?= $i ?>" 
                                <?= $i === 0 ? 'class="active" aria-current="true"' : '' ?> 
                                aria-label="Slide <?= $i + 1 ?>"></button>
                        <?php endfor; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Carousel Items -->
                    <div class="carousel-inner">
                        <?php foreach ($announcements as $index => $announcement): ?>
                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                            <div class="announcement-slide d-block w-100" 
                                 style="background-color: <?= htmlspecialchars($announcement['bg_color']) ?>; 
                                        color: <?= htmlspecialchars($announcement['text_color']) ?>;">
                                <div class="announcement-content py-4 px-5 text-center">
                                    <h4 class="mb-3"><?= htmlspecialchars($announcement['title']) ?></h4>
                                    <p class="mb-2"><?= htmlspecialchars($announcement['content']) ?></p>
                                    
                                    <?php if ($announcement['start_date'] || $announcement['end_date']): ?>
                                    <div class="announcement-dates mt-3">
                                        <small>
                                            <?php if ($announcement['start_date'] && $announcement['end_date']): ?>
                                                <i class="bi bi-calendar3"></i> 
                                                <?= date('d.m.Y', strtotime($announcement['start_date'])) ?> - 
                                                <?= date('d.m.Y', strtotime($announcement['end_date'])) ?>
                                            <?php elseif ($announcement['end_date']): ?>
                                                <i class="bi bi-calendar3"></i> 
                                                <?= date('d.m.Y', strtotime($announcement['end_date'])) ?> tarihine kadar
                                            <?php elseif ($announcement['start_date']): ?>
                                                <i class="bi bi-calendar3"></i> 
                                                <?= date('d.m.Y', strtotime($announcement['start_date'])) ?> tarihinden itibaren
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Carousel Controls -->
                    <?php if (count($announcements) > 1): ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#announcementCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Önceki</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#announcementCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Sonraki</span>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.announcement-slider-section {
    position: relative;
}

.section-title {
    position: relative;
    font-weight: 600;
    color: #333;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
}

.announcement-slide {
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    min-height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.announcement-content {
    max-width: 800px;
    margin: 0 auto;
}

.carousel-indicators {
    bottom: -40px;
}

.carousel-indicators [data-bs-target] {
    background-color: #999;
    height: 10px;
    width: 10px;
    border-radius: 50%;
    margin: 0 5px;
}

.carousel-control-prev, 
.carousel-control-next {
    width: 40px;
    height: 40px;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255,255,255,0.8);
    border-radius: 50%;
    opacity: 0.8;
}

.carousel-control-prev-icon,
.carousel-control-next-icon {
    filter: invert(1) grayscale(100%);
}

.carousel-control-prev {
    left: -20px;
}

.carousel-control-next {
    right: -20px;
}

@media (max-width: 768px) {
    .announcement-content {
        padding: 15px !important;
    }
    
    .carousel-control-prev,
    .carousel-control-next {
        display: none;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Carousel otomatik kaydırma ayarları
    const announcementCarousel = document.getElementById('announcementCarousel');
    if (announcementCarousel) {
        const carousel = new bootstrap.Carousel(announcementCarousel, {
            interval: 7000,  // 7 saniye
            wrap: true,
            pause: 'hover'
        });
    }
});
</script>
<?php endif; ?>