<?php
require_once 'includes/init.php';

$listing = new Listing();
$announcement = new Announcement(); // Yeni eklenen

$featuredListings = $listing->getFeaturedListings(6);
$newListings = $listing->getNewListings(6);
$saleListings = $listing->getSaleListings(3);
$rentListings = $listing->getRentListings(3);
$mapData = $listing->getMapData();
$activeAnnouncements = $announcement->getAllAnnouncements(true); // Aktif duyurular

// JSON formatında harita verileri
$mapDataJson = json_encode($mapData);

$pageTitle = "Ana Sayfa";
require_once 'templates/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="hero-content fadeInUp">
            <h1 class="hero-title">Hayalinizdeki Mülkü Bulun</h1>
            <p class="hero-subtitle">Aksu Emlak ile hayalinizdeki ev veya yatırım için arayışınız sona eriyor.</p>
            <a href="search.php" class="btn btn-primary btn-lg px-4 py-2">İlanları Keşfedin</a>
        </div>
    </div>
</section>

<!-- Arama Formu -->
<section class="search-section">
    <div class="container">
        <div class="search-form-wrapper">
            <form action="search.php" method="get">
                <div class="row g-3">
                    <!-- İlan Tipi Seçimi -->
                    <div class="col-lg-2 col-md-6">
                        <label for="listing_type" class="form-label">İlan Tipi</label>
                        <select class="form-select" id="listing_type" name="listing_type">
                            <option value="">Tümü</option>
                            <option value="sale">Satılık</option>
                            <option value="rent">Kiralık</option>
                        </select>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <label for="location" class="form-label">Konum</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                            <input type="text" class="form-control" id="location" name="city" placeholder="Şehir, ilçe...">
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <label for="category" class="form-label">Kategori</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">Tümü</option>
                            <option value="House">Müstakil Ev</option>
                            <option value="Apartment">Daire</option>
                            <option value="Commercial">Ticari</option>
                            <option value="Land">Arsa</option>
                            <option value="Other">Diğer</option>
                        </select>
                    </div>
                    
                    <div class="col-lg-2 col-md-6">
                        <label for="price_range" class="form-label">Fiyat Aralığı</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="min_price" name="min_price" min="0" step="1000" placeholder="Min">
                            <input type="number" class="form-control" id="max_price" name="max_price" min="0" step="1000" placeholder="Max">
                        </div>
                    </div>
                    
                    <div class="col-lg-2 col-md-12">
                        <label class="form-label d-md-block d-none">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-2"></i> Ara</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
<?php include 'templates/announcement-slider.php'; ?>
<!-- Hızlı İstatistikler (İlk Kod Bloğundan) -->
<section class="statistics-section py-5 my-5" style="background: linear-gradient(rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 0.9)), url(assets/img/stats-bg.jpg); background-size: cover; background-position: center; color: white; background-attachment: fixed;">
    <div class="container py-4">
        <div class="row text-center">
            <div class="col-md-3 col-6 mb-4 mb-md-0">
                <div class="stat-item">
                    <div class="stat-number h1 fw-bold">5200+</div>
                    <div class="stat-label">Satılan Emlak</div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4 mb-md-0">
                <div class="stat-item">
                    <div class="stat-number h1 fw-bold">1800+</div>
                    <div class="stat-label">Mutlu Müşteri</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-item">
                    <div class="stat-number h1 fw-bold">150+</div>
                    <div class="stat-label">Aktif Projeler</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-item">
                    <div class="stat-number h1 fw-bold">20+</div>
                    <div class="stat-label">Yıllık Tecrübe</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Öne Çıkan İlanlar -->
<section class="featured-listings py-5 mt-4">
    <div class="container">
        <h2 class="section-title">Öne Çıkan İlanlar</h2>
        <div class="row">
            <?php if (!empty($featuredListings)): ?>
                <?php foreach ($featuredListings as $listing): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card property-card h-100">
                            <div class="position-relative">
                                <img src="<?= htmlspecialchars($listing['main_image'] ?? 'assets/img/property-placeholder.jpg') ?>" 
                                     class="card-img-top" 
                                     alt="<?= htmlspecialchars($listing['title']) ?>"
                                     loading="lazy">
                                <span class="badge bg-danger position-absolute top-0 end-0 m-3 py-2 px-3">
                                    Öne Çıkan
                                </span>
                                <?php if ($listing['category']): ?>
                                    <span class="badge bg-primary position-absolute top-0 start-0 m-3">
                                        <?php
                                            $categories = [
                                                'House' => 'Müstakil Ev',
                                                'Apartment' => 'Daire',
                                                'Commercial' => 'Ticari',
                                                'Land' => 'Arsa',
                                                'Other' => 'Diğer'
                                            ];
                                            echo $categories[$listing['category']] ?? $listing['category'];
                                        ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="listing.php?id=<?= $listing['id'] ?>">
                                        <?= htmlspecialchars($listing['title']) ?>
                                    </a>
                                </h5>
                                <p class="card-text small text-muted mb-2">
                                    <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($listing['city']) ?>, <?= htmlspecialchars($listing['state']) ?>
                                </p>
                                <p class="card-text text-primary fw-bold mb-3"><?= number_format($listing['sale_price'], 0, ',', '.') ?> ₺</p>
                                
                                <div class="property-features d-flex justify-content-between text-center border-top pt-3">
                                    <div>
                                        <i class="bi bi-house-door"></i>
                                        <p class="small mb-0"><?= htmlspecialchars($listing['property_size']) ?> m²</p>
                                    </div>
                                    <div>
                                        <i class="bi bi-door-closed"></i>
                                        <p class="small mb-0"><?= htmlspecialchars($listing['rooms']) ?> Oda</p>
                                    </div>
                                    <div>
                                        <i class="bi bi-water"></i>
                                        <p class="small mb-0"><?= htmlspecialchars($listing['bathrooms']) ?> Banyo</p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0">
                                <a href="listing.php?id=<?= $listing['id'] ?>" class="btn btn-outline-primary w-100">Detaylar</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">Henüz öne çıkan ilan bulunmuyor.</div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-3">
            <a href="search.php" class="btn btn-primary px-4">Tüm İlanları Görüntüle</a>
        </div>
    </div>
</section>

<!-- Satılık İlanlar -->
<section class="sale-listings py-5 bg-light">
    <div class="container">
        <h2 class="section-title">Satılık İlanlar</h2>
        <div class="row">
            <?php if (!empty($saleListings)): ?>
                <?php foreach ($saleListings as $saleItem): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card property-card h-100">
                            <div class="position-relative">
                                <img src="<?= htmlspecialchars($saleItem['main_image'] ?? 'assets/img/property-placeholder.jpg') ?>" 
                                     class="card-img-top" 
                                     alt="<?= htmlspecialchars($saleItem['title']) ?>"
                                     loading="lazy">
                                <span class="badge bg-danger position-absolute top-0 end-0 m-3 py-2 px-3">
                                    Satılık
                                </span>
                                <?php if ($saleItem['category']): ?>
                                    <span class="badge bg-primary position-absolute top-0 start-0 m-3">
                                        <?php
                                            $categories = [
                                                'House' => 'Müstakil Ev',
                                                'Apartment' => 'Daire',
                                                'Commercial' => 'Ticari',
                                                'Land' => 'Arsa',
                                                'Other' => 'Diğer'
                                            ];
                                            echo $categories[$saleItem['category']] ?? $saleItem['category'];
                                        ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="listing.php?id=<?= $saleItem['id'] ?>">
                                        <?= htmlspecialchars($saleItem['title']) ?>
                                    </a>
                                </h5>
                                <p class="card-text small text-muted mb-2">
                                    <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($saleItem['city']) ?>, <?= htmlspecialchars($saleItem['state']) ?>
                                </p>
                                <p class="card-text text-primary fw-bold mb-3"><?= number_format($saleItem['sale_price'], 0, ',', '.') ?> ₺</p>
                                
                                <div class="property-features d-flex justify-content-between text-center border-top pt-3">
                                    <div>
                                        <i class="bi bi-house-door"></i>
                                        <p class="small mb-0"><?= htmlspecialchars($saleItem['property_size']) ?> m²</p>
                                    </div>
                                    <div>
                                        <i class="bi bi-door-closed"></i>
                                        <p class="small mb-0"><?= htmlspecialchars($saleItem['rooms']) ?> Oda</p>
                                    </div>
                                    <div>
                                        <i class="bi bi-water"></i>
                                        <p class="small mb-0"><?= htmlspecialchars($saleItem['bathrooms']) ?> Banyo</p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0">
                                <a href="listing.php?id=<?= $saleItem['id'] ?>" class="btn btn-outline-primary w-100">Detaylar</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">Henüz satılık ilan bulunmuyor.</div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-3">
            <a href="search.php?listing_type=sale" class="btn btn-primary px-4">Tüm Satılık İlanlar</a>
        </div>
    </div>
</section>

<!-- Kiralık İlanlar -->
<section class="rent-listings py-5">
    <div class="container">
        <h2 class="section-title">Kiralık İlanlar</h2>
        <div class="row">
            <?php if (!empty($rentListings)): ?>
                <?php foreach ($rentListings as $rentItem): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card property-card h-100">
                            <div class="position-relative">
                                <img src="<?= htmlspecialchars($rentItem['main_image'] ?? 'assets/img/property-placeholder.jpg') ?>" 
                                     class="card-img-top" 
                                     alt="<?= htmlspecialchars($rentItem['title']) ?>"
                                     loading="lazy">
                                <span class="badge bg-success position-absolute top-0 end-0 m-3 py-2 px-3">
                                    Kiralık
                                </span>
                                <?php if ($rentItem['category']): ?>
                                    <span class="badge bg-primary position-absolute top-0 start-0 m-3">
                                        <?php
                                            $categories = [
                                                'House' => 'Müstakil Ev',
                                                'Apartment' => 'Daire',
                                                'Commercial' => 'Ticari',
                                                'Land' => 'Arsa',
                                                'Other' => 'Diğer'
                                            ];
                                            echo $categories[$rentItem['category']] ?? $rentItem['category'];
                                        ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="listing.php?id=<?= $rentItem['id'] ?>">
                                        <?= htmlspecialchars($rentItem['title']) ?>
                                    </a>
                                </h5>
                                <p class="card-text small text-muted mb-2">
                                    <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($rentItem['city']) ?>, <?= htmlspecialchars($rentItem['state']) ?>
                                </p>
                                <p class="card-text text-primary fw-bold mb-3"><?= number_format($rentItem['rent_price'], 0, ',', '.') ?> ₺/ay</p>
                                
                                <div class="property-features d-flex justify-content-between text-center border-top pt-3">
                                    <div>
                                        <i class="bi bi-house-door"></i>
                                        <p class="small mb-0"><?= htmlspecialchars($rentItem['property_size']) ?> m²</p>
                                    </div>
                                    <div>
                                        <i class="bi bi-door-closed"></i>
                                        <p class="small mb-0"><?= htmlspecialchars($rentItem['rooms']) ?> Oda</p>
                                    </div>
                                    <div>
                                        <i class="bi bi-water"></i>
                                        <p class="small mb-0"><?= htmlspecialchars($rentItem['bathrooms']) ?> Banyo</p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0">
                                <a href="listing.php?id=<?= $rentItem['id'] ?>" class="btn btn-outline-primary w-100">Detaylar</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">Henüz kiralık ilan bulunmuyor.</div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-3">
            <a href="search.php?listing_type=rent" class="btn btn-primary px-4">Tüm Kiralık İlanlar</a>
        </div>
    </div>
</section>

<!-- Harita Bölümü -->
<section class="map-section py-5 bg-light">
    <div class="container">
        <h2 class="section-title">Konuma Göre İlanlar</h2>
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div id="property-map" style="height: 600px;"></div>
            </div>
        </div>
    </div>
</section>

<!-- Neden Biz (İlk Kod Bloğundan) -->
<section class="section-container section-bg-light">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">NEDEN BİZ</span>
            <div class="section-divider"></div>
            <h2 class="section-title">Bizi Tercih Etme Sebepleriniz</h2>
            <p class="section-description">Aksu Emlak olarak müşterilerimize en iyi hizmeti sunmak için buradayız.</p>
        </div>
        <div class="row mt-5">
            <div class="col-md-4 mb-4">
                <div class="feature-box hover-lift hover-shadow">
                    <div class="feature-icon-wrapper">
                        <i class="bi bi-building-check fs-1 text-primary mb-4"></i>
                    </div>
                    <h3 class="feature-title">Geniş Portföy</h3>
                    <p class="feature-description">
                        Her bütçeye ve ihtiyaca uygun binlerce emlak seçeneği arasından size en uygun olanı bulmanıza yardımcı oluyoruz.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="feature-box hover-lift hover-shadow">
                    <div class="feature-icon-wrapper">
                        <i class="bi bi-person-check fs-1 text-primary mb-4"></i>
                    </div>
                    <h3 class="feature-title">Uzman Danışmanlar</h3>
                    <p class="feature-description">
                        Alanında uzman gayrimenkul danışmanlarımız, ihtiyaçlarınıza en uygun çözümü sunmak için yanınızda.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="feature-box hover-lift hover-shadow">
                    <div class="feature-icon-wrapper">
                        <i class="bi bi-shield-check fs-1 text-primary mb-4"></i>
                    </div>
                    <h3 class="feature-title">Güvenilir Hizmet</h3>
                    <p class="feature-description">
                        20 yılı aşkın tecrübemizle güvenilir ve şeffaf hizmet anlayışımızı her zaman ön planda tutuyoruz.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Neden Bizi Seçmelisiniz? (İkinci Kod Bloğundan) -->
<section class="why-us-section py-5">
    <div class="container">
        <h2 class="section-title">Neden Bizi Seçmelisiniz?</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon mb-3">
                            <i class="bi bi-house-check text-primary" style="font-size: 3rem;"></i>
                        </div>
                        <h4 class="card-title">Güvenilir İlanlar</h4>
                        <p class="card-text">Tüm ilanlarımız profesyonel ekibimiz tarafından kontrol edilmektedir. Size sadece kaliteli ve güvenilir mülkler sunuyoruz.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon mb-3">
                            <i class="bi bi-person-check text-primary" style="font-size: 3rem;"></i>
                        </div>
                        <h4 class="card-title">Uzman Danışmanlar</h4>
                        <p class="card-text">Deneyimli gayrimenkul danışmanlarımız, size en uygun mülkü bulmanızda ve tüm süreçte yanınızda olacaktır.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon mb-3">
                            <i class="bi bi-graph-up-arrow text-primary" style="font-size: 3rem;"></i>
                        </div>
                        <h4 class="card-title">Yatırım Fırsatları</h4>
                        <p class="card-text">Sadece ev değil, geleceğinize yatırım yapın. Uzmanlarımız size en karlı yatırım fırsatlarını sunmaktadır.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Müşteri Yorumları (İlk Kod Bloğundan) -->
<section class="section-container">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">REFERANSLARIMIZ</span>
            <div class="section-divider"></div>
            <h2 class="section-title">Müşterilerimiz Ne Diyor?</h2>
            <p class="section-description">İşimizi en iyi yapmanın tek yolu müşteri memnuniyetidir.</p>
        </div>
        <div class="row mt-5">
            <div class="col-lg-10 mx-auto">
                <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <div class="testimonial-item text-center">
                                <div class="testimonial-avatar mb-4">
                                    <img src="assets/img/avatar-1.jpg" alt="Müşteri" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                                </div>
                                <div class="testimonial-content">
                                    <p class="testimonial-text fs-5 mb-4">"Aksu Emlak sayesinde hayalimdeki evi çok kısa sürede buldum. Profesyonel ekibi ve güler yüzlü hizmetiyle tüm süreci sorunsuz bir şekilde tamamladık."</p>
                                    <h4 class="testimonial-name fw-bold">Ayşe Yılmaz</h4>
                                    <p class="testimonial-position text-muted">Müşteri</p>
                                </div>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="testimonial-item text-center">
                                <div class="testimonial-avatar mb-4">
                                    <img src="assets/img/avatar-2.jpg" alt="Müşteri" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                                </div>
                                <div class="testimonial-content">
                                    <p class="testimonial-text fs-5 mb-4">"İşimi büyütmek için aradığım ofisi Aksu Emlak'ta buldum. Danışmanım ihtiyaçlarımı çok iyi analiz ederek bana en uygun seçenekleri sundu."</p>
                                    <h4 class="testimonial-name fw-bold">Mehmet Demir</h4>
                                    <p class="testimonial-position text-muted">İş İnsanı</p>
                                </div>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="testimonial-item text-center">
                                <div class="testimonial-avatar mb-4">
                                    <img src="assets/img/avatar-3.jpg" alt="Müşteri" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                                </div>
                                <div class="testimonial-content">
                                    <p class="testimonial-text fs-5 mb-4">"Kiralık daire arayışımda Aksu Emlak'ın yardımlarıyla çok kısa sürede istediğim bölgede uygun fiyatlı bir daire bulabildim. Teşekkürler!"</p>
                                    <h4 class="testimonial-name fw-bold">Zeynep Kaya</h4>
                                    <p class="testimonial-position text-muted">Öğretmen</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
                        <i class="bi bi-chevron-left fs-3"></i>
                        <span class="visually-hidden">Önceki</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
                        <i class="bi bi-chevron-right fs-3"></i>
                        <span class="visually-hidden">Sonraki</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Yeni İlanlar -->
<section class="new-listings py-5">
    <div class="container">
        <h2 class="section-title">Yeni İlanlar</h2>
        <div class="row">
            <?php if (!empty($newListings)): ?>
                <?php foreach ($newListings as $listing): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card property-card h-100">
                            <div class="position-relative">
                                <img src="<?= htmlspecialchars($listing['main_image'] ?? 'assets/img/property-placeholder.jpg') ?>" 
                                     class="card-img-top" 
                                     alt="<?= htmlspecialchars($listing['title']) ?>"
                                     loading="lazy">
                                <span class="badge bg-success position-absolute top-0 end-0 m-3 py-2 px-3">
                                    Yeni
                                </span>
                                <?php if ($listing['category']): ?>
                                    <span class="badge bg-primary position-absolute top-0 start-0 m-3">
                                        <?php
                                            $categories = [
                                                'House' => 'Müstakil Ev',
                                                'Apartment' => 'Daire',
                                                'Commercial' => 'Ticari',
                                                'Land' => 'Arsa',
                                                'Other' => 'Diğer'
                                            ];
                                            echo $categories[$listing['category']] ?? $listing['category'];
                                        ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="listing.php?id=<?= $listing['id'] ?>">
                                        <?= htmlspecialchars($listing['title']) ?>
                                    </a>
                                </h5>
                                <p class="card-text small text-muted mb-2">
                                    <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($listing['city']) ?>, <?= htmlspecialchars($listing['state']) ?>
                                </p>
                                <p class="card-text text-primary fw-bold mb-3"><?= number_format($listing['sale_price'], 0, ',', '.') ?> ₺</p>
                                
                                <div class="property-features d-flex justify-content-between text-center border-top pt-3">
                                    <div>
                                        <i class="bi bi-house-door"></i>
                                        <p class="small mb-0"><?= htmlspecialchars($listing['property_size']) ?> m²</p>
                                    </div>
                                    <div>
                                        <i class="bi bi-door-closed"></i>
                                        <p class="small mb-0"><?= htmlspecialchars($listing['rooms']) ?> Oda</p>
                                    </div>
                                    <div>
                                        <i class="bi bi-water"></i>
                                        <p class="small mb-0"><?= htmlspecialchars($listing['bathrooms']) ?> Banyo</p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0">
                                <a href="listing.php?id=<?= $listing['id'] ?>" class="btn btn-outline-primary w-100">Detaylar</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">Henüz yeni ilan bulunmuyor.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Blog Bölümü -->
<section class="blog-section py-5 bg-light">
    <div class="container">
        <h2 class="section-title">Emlak Rehberi</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card blog-card">
                    <img src="assets/img/blog-1.jpg" class="card-img-top" alt="Blog Resmi">
                    <div class="card-body">
                        <p class="blog-date mb-2"><i class="bi bi-calendar3"></i> 15 Mart 2023</p>
                        <h5 class="card-title">Ev Alırken Dikkat Edilmesi Gerekenler</h5>
                        <p class="blog-excerpt">Yeni ev almayı düşünüyorsanız, bu süreçte dikkat etmeniz gereken önemli noktaları sizler için derledik.</p>
                        <a href="blog-detail.php?id=1" class="btn btn-outline-primary">Devamını Oku</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card blog-card">
                    <img src="assets/img/blog-2.jpg" class="card-img-top" alt="Blog Resmi">
                    <div class="card-body">
                        <p class="blog-date mb-2"><i class="bi bi-calendar3"></i> 28 Şubat 2023</p>
                        <h5 class="card-title">Gayrimenkul Yatırımında Doğru Strateji</h5>
                        <p class="blog-excerpt">Gayrimenkul yatırımı yaparken izlemeniz gereken stratejiler ve dikkat edilmesi gereken püf noktalar.</p>
                        <a href="blog-detail.php?id=2" class="btn btn-outline-primary">Devamını Oku</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card blog-card">
                    <img src="assets/img/blog-3.jpg" class="card-img-top" alt="Blog Resmi">
                    <div class="card-body">
                        <p class="blog-date mb-2"><i class="bi bi-calendar3"></i> 10 Şubat 2023</p>
                        <h5 class="card-title">Evinizi Satarken Değerini Artıracak İpuçları</h5>
                        <p class="blog-excerpt">Evinizi satışa çıkarmadan önce değerini artıracak küçük dokunuşlar ve profesyonel tavsiyeler.</p>
                        <a href="blog-detail.php?id=3" class="btn btn-outline-primary">Devamını Oku</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-3">
            <a href="blog.php" class="btn btn-primary px-4">Tüm Yazıları Görüntüle</a>
        </div>
    </div>
</section>

<!-- Hızlı İletişim (CTA) -->
<section class="contact-cta py-5" style="background-color: var(--primary); color: white;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <h2 class="mb-2">Hayalinizdeki mülk için bize ulaşın</h2>
                <p class="mb-0">Uzman ekibimiz size en uygun seçenekleri sunmak için hazır.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="contact.php" class="btn btn-light btn-lg px-4">Bize Ulaşın</a>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Harita merkezi (Türkiye)
    const map = L.map('property-map').setView([39.1, 35.6], 6);
    
    // OpenStreetMap katmanı
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 18
    }).addTo(map);
    
    // Fiyatı biçimlendiren yardımcı fonksiyon
    function formatPrice(price) {
        return new Intl.NumberFormat('tr-TR').format(price);
    }
    
    const propertyData = <?= $mapDataJson ?>;
    
    propertyData.forEach(function(property) {
    // İlan tipine ve öne çıkan durumuna göre işaretleyici sınıfını belirle
    let markerClass = property.featured ? 'featured' : '';
    let priceClass = property.rent_price > 0 ? 'rent' : 'sale';
    
    // Uygun stille işaretleyici oluştur
    const markerIcon = L.divIcon({
        className: 'property-marker',
        html: `<div class="marker-container ${markerClass}">
              <div class="marker-pin ${markerClass}"><i class="bi bi-house-door-fill"></i></div>
              <div class="marker-price marker-price-${priceClass}">${formatPrice(property.rent_price > 0 ? property.rent_price : property.sale_price)} ${property.rent_price > 0 ? '₺/ay' : '₺'}</div>
           </div>`,
        iconSize: [80, 60],
        iconAnchor: [40, 60]
    });
    
    // İşaretleyiciyi haritaya ekle
    const marker = L.marker([property.latitude, property.longitude], {
        icon: markerIcon
    }).addTo(map);
    
    // Mülk detaylarıyla açılır pencere ekle
    marker.bindPopup(createPopupContent(property));
});
        
        // Popup içeriği - kiralık/satılık durumunu da ekleyin
        const popupContent = `
            <div class="map-popup">
                <img src="${property.main_image || 'assets/img/property-placeholder.jpg'}" class="popup-image" alt="${property.title}">
                <h5 class="popup-title">${property.title}</h5>
                <p class="popup-price">${priceText}</p>
                <a href="listing.php?id=${property.id}" class="btn btn-sm btn-primary w-100">Detaylar</a>
            </div>
        `;
        
        // Popup ekle
        marker.bindPopup(popupContent, {
            maxWidth: 250,
            className: 'property-popup'
        });
    });
});
</script>

<?php require_once 'templates/footer.php'; ?>
