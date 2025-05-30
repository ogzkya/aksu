<?php
require_once 'includes/init.php';

$listing = new Listing();
$announcement = new Announcement(); // Yeni eklenen

// Ana sayfa için daha az ilan çekilebilir (örneğin 3 veya 4)
$featuredListings = $listing->getFeaturedListings(3); // 3 öne çıkan
$saleListings = $listing->getSaleListings(3); // 3 satılık
$rentListings = $listing->getRentListings(3); // 3 kiralık

// Harita için tüm ilan verilerini çekmeye devam edebiliriz veya limitli çekebiliriz
$mapData = $listing->getMapData(); // Tüm fiyatı olan ilanlar
// ÖNEMLİ NOT: $listing->getMapData() metodunun, her ilan için hem 'sale_price' hem de 'rent_price'
// alanlarını (varsa) getirdiğinden emin olun. Veritabanınızda ve Listing sınıfınızdaki
// kaydetme/güncelleme mantığı, bu iki fiyatın birbirinden bağımsız olarak ayarlanabilmesine
// olanak tanımalıdır. Böylece bir mülk aynı anda hem satılık hem kiralık olabilir veya
// sadece biri için fiyatı olabilir. Popup gösterimi bu esnekliği zaten desteklemektedir.

$activeAnnouncements = $announcement->getActiveAnnouncements(true); // Aktif duyurular

// JSON formatında harita verileri
$mapDataJson = json_encode($mapData);

$pageTitle = "Ana Sayfa";
$activePage = "home"; // Aktif sayfa belirteci eklendi
$config = require_once __DIR__ . '/config/config.php';
require_once 'templates/header.php';
?>

<section class="hero-section" style="background-image: url('assets/img/img-hero-bg-3.jpg');">
    <div class="container">
        <div class="hero-content" data-aos="fade-up">
            <h1 class="hero-title">Hayalinizdeki Mülkü Bulun</h1>
            <p class="hero-subtitle">Aksu Emlak ile hayalinizdeki ev veya yatırım için arayışınız sona eriyor.</p>
            <div class="mt-4">
                <a href="search.php?listing_type=sale" class="btn btn-accent btn-lg me-2 mb-2">
                    <i class="bi bi-house me-2"></i>Satılık İlanlar
                </a>
                <a href="search.php?listing_type=rent" class="btn btn-light btn-lg mb-2">
                    <i class="bi bi-key me-2"></i>Kiralık İlanlar
                </a>
            </div>
        </div>
    </div>
</section>

<section class="search-section">
    <div class="container">
        <div class="search-form-wrapper" data-aos="fade-up" data-aos-delay="200">
            <form action="search.php" method="get">
                <div class="row g-3">
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
                            <input type="text" class="form-control" id="location" name="city" placeholder="Şehir, ilçe, anahtar kelime...">
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
                        <label for="price_range" class="form-label">Fiyat Aralığı (₺)</label>
                         <div class="input-group">
                            <input type="number" class="form-control" name="min_price" placeholder="Min Fiyat" min="0" step="1000">
                            <input type="number" class="form-control" name="max_price" placeholder="Max Fiyat" min="0" step="1000">
                        </div>
                         <div class="form-text small">Satılık için satış, kiralık için kira fiyatı</div>
                    </div>

                    <div class="col-lg-2 col-md-12">
                        <label class="form-label d-md-block d-none">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center">
                            <i class="bi bi-search me-2"></i> İlan Ara
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<?php // Duyuru slider'ını çağırın (eğer aktif duyuru varsa)
 if (!empty($activeAnnouncements)) {
    include 'templates/announcement-slider.php';
 }
?>


<section class="statistics-section py-5 my-5">
    <div class="container py-4">
        <div class="row text-center">
            <div class="col-md-3 col-6 mb-4 mb-md-0" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-item">
                    <div class="stat-number" data-counter="5200">0</div>
                    <div class="stat-label">Satılan Emlak</div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4 mb-md-0" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-item">
                    <div class="stat-number" data-counter="1800">0</div>
                    <div class="stat-label">Mutlu Müşteri</div>
                </div>
            </div>
            <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-item">
                    <div class="stat-number" data-counter="150">0</div>
                    <div class="stat-label">Aktif Projeler</div>
                </div>
            </div>
            <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="400">
                <div class="stat-item">
                    <div class="stat-number" data-counter="20">0</div>
                    <div class="stat-label">Yıllık Tecrübe</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="featured-listings py-5 mt-4">
    <div class="container">
        <h2 class="section-title" data-aos="fade-up">Öne Çıkan İlanlar</h2>
        <div class="row">
            <?php if (!empty($featuredListings)): ?>
                <?php foreach ($featuredListings as $index => $item): // $listing yerine $item kullandık karışmaması için ?>
                    <div class="col-md-6 col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="<?= 100 + ($index * 100) ?>">
                        <div class="card property-card h-100">
                            <div class="position-relative">
                                <img src="<?= htmlspecialchars($item['main_image'] ?? 'assets/img/property-placeholder.jpg') ?>"
                                     class="card-img-top"
                                     alt="<?= htmlspecialchars($item['title']) ?>"
                                     loading="lazy">
                                <span class="badge bg-danger position-absolute top-0 end-0 m-3 py-2 px-3">
                                    Öne Çıkan
                                </span>
                                <?php if ($item['category']): ?>
                                    <span class="badge bg-primary position-absolute top-0 start-0 m-3">
                                        <?php
                                            $categories = [
                                                'House' => 'Müstakil Ev',
                                                'Apartment' => 'Daire',
                                                'Commercial' => 'Ticari',
                                                'Land' => 'Arsa',
                                                'Other' => 'Diğer'
                                            ];
                                            echo $categories[$item['category']] ?? $item['category'];
                                        ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="listing.php?id=<?= $item['id'] ?>">
                                        <?= htmlspecialchars($item['title']) ?>
                                    </a>
                                </h5>
                                <p class="card-text small text-muted mb-2">
                                    <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($item['city']) ?>, <?= htmlspecialchars($item['state']) ?>
                                </p>
                                <?php
                                $displayPrice = '';
                                if ($item['rent_price'] && $item['rent_price'] > 0) {
                                    $displayPrice = number_format($item['rent_price'], 0, ',', '.') . ' ₺/ay';
                                } elseif ($item['sale_price'] && $item['sale_price'] > 0) {
                                    $displayPrice = number_format($item['sale_price'], 0, ',', '.') . ' ₺';
                                } else {
                                    $displayPrice = 'Fiyat Belirtilmemiş';
                                }
                                ?>
                                <p class="card-text text-primary fw-bold mb-3"><?= $displayPrice ?></p>
                                <div class="property-features d-flex justify-content-between text-center border-top pt-3">
                                    <div>
                                        <i class="bi bi-house-door"></i>
                                        <p class="small mb-0"><?= htmlspecialchars($item['property_size'] ?? 0) ?> m²</p>
                                    </div>
                                    <div>
                                        <i class="bi bi-door-closed"></i>
                                        <p class="small mb-0"><?= htmlspecialchars($item['rooms'] ?? 0) ?> Oda</p>
                                    </div>
                                    <div>
                                        <i class="bi bi-water"></i>
                                        <p class="small mb-0"><?= htmlspecialchars($item['bathrooms'] ?? 0) ?> Banyo</p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0">
                                <a href="listing.php?id=<?= $item['id'] ?>" class="btn btn-outline-primary w-100">Detayları Gör</a>
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

        <div class="text-center mt-4" data-aos="fade-up">
            <a href="search.php" class="btn btn-primary px-4">Tüm İlanları Görüntüle</a>
        </div>
    </div>
</section>

<section class="sale-listings py-5 bg-light">
    <div class="container">
        <h2 class="section-title">Satılık İlanlar</h2>
        <div class="row">
            <?php if (!empty($saleListings)): ?>
                <?php foreach ($saleListings as $item): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card property-card h-100">
                            <div class="position-relative">
                                <img src="<?= htmlspecialchars($item['main_image'] ?? 'assets/img/property-placeholder.jpg') ?>"
                                     class="card-img-top"
                                     alt="<?= htmlspecialchars($item['title']) ?>"
                                     loading="lazy">
                                <span class="badge bg-danger position-absolute top-0 end-0 m-3 py-2 px-3">
                                    Satılık
                                </span>
                                <?php if ($item['category']): ?>
                                    <span class="badge bg-primary position-absolute top-0 start-0 m-3">
                                        <?= $categories[$item['category']] ?? $item['category'] ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="listing.php?id=<?= $item['id'] ?>">
                                        <?= htmlspecialchars($item['title']) ?>
                                    </a>
                                </h5>
                                <p class="card-text small text-muted mb-2">
                                    <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($item['city']) ?>, <?= htmlspecialchars($item['state']) ?>
                                </p>
                                <?php
                                $displayPrice = '';
                                if ($item['sale_price'] && $item['sale_price'] > 0) {
                                    $displayPrice = number_format($item['sale_price'], 0, ',', '.') . ' ₺';
                                } else {
                                    $displayPrice = 'Fiyat Belirtilmemiş';
                                }
                                ?>
                                <p class="card-text text-primary fw-bold mb-3"><?= $displayPrice ?></p>
                                <div class="property-features d-flex justify-content-between text-center border-top pt-3">
                                    <div>
                                        <i class="bi bi-house-door"></i>
                                        <p class="small mb-0"><?= htmlspecialchars($item['property_size'] ?? 0) ?> m²</p>
                                    </div>
                                    <div>
                                        <i class="bi bi-door-closed"></i>
                                        <p class="small mb-0"><?= htmlspecialchars($item['rooms'] ?? 0) ?> Oda</p>
                                    </div>
                                    <div>
                                        <i class="bi bi-water"></i>
                                        <p class="small mb-0"><?= htmlspecialchars($item['bathrooms'] ?? 0) ?> Banyo</p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0">
                                <a href="listing.php?id=<?= $item['id'] ?>" class="btn btn-outline-primary w-100">Detaylar</a>
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

<section class="rent-listings py-5">
    <div class="container">
        <h2 class="section-title">Kiralık İlanlar</h2>
        <div class="row">
            <?php if (!empty($rentListings)): ?>
                <?php foreach ($rentListings as $item): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card property-card h-100">
                            <div class="position-relative">
                                <img src="<?= htmlspecialchars($item['main_image'] ?? 'assets/img/property-placeholder.jpg') ?>"
                                     class="card-img-top"
                                     alt="<?= htmlspecialchars($item['title']) ?>"
                                     loading="lazy">
                                <span class="badge bg-success position-absolute top-0 end-0 m-3 py-2 px-3">
                                    Kiralık
                                </span>
                                <?php if ($item['category']): ?>
                                    <span class="badge bg-primary position-absolute top-0 start-0 m-3">
                                        <?= $categories[$item['category']] ?? $item['category'] ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="listing.php?id=<?= $item['id'] ?>">
                                        <?= htmlspecialchars($item['title']) ?>
                                    </a>
                                </h5>
                                <p class="card-text small text-muted mb-2">
                                    <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($item['city']) ?>, <?= htmlspecialchars($item['state']) ?>
                                </p>
                                <?php
                                $displayPrice = '';
                                if ($item['rent_price'] && $item['rent_price'] > 0) {
                                    $displayPrice = number_format($item['rent_price'], 0, ',', '.') . ' ₺/ay';
                                } else {
                                    $displayPrice = 'Fiyat Belirtilmemiş';
                                }
                                ?>
                                <p class="card-text text-primary fw-bold mb-3"><?= $displayPrice ?></p>
                                <div class="property-features d-flex justify-content-between text-center border-top pt-3">
                                    <div>
                                        <i class="bi bi-house-door"></i>
                                        <p class="small mb-0"><?= htmlspecialchars($item['property_size'] ?? 0) ?> m²</p>
                                    </div>
                                    <div>
                                        <i class="bi bi-door-closed"></i>
                                        <p class="small mb-0"><?= htmlspecialchars($item['rooms'] ?? 0) ?> Oda</p>
                                    </div>
                                    <div>
                                        <i class="bi bi-water"></i>
                                        <p class="small mb-0"><?= htmlspecialchars($item['bathrooms'] ?? 0) ?> Banyo</p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0">
                                <a href="listing.php?id=<?= $item['id'] ?>" class="btn btn-outline-primary w-100">Detaylar</a>
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

<section class="why-us-section py-5 my-5" data-aos="fade-up">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h5 class="text-primary mb-2">NEDEN BİZ</h5>
            <h2 class="section-title mb-4" style="padding-bottom: 0">Bizi Tercih Etme Sebepleriniz</h2>
            <p class="section-description mx-auto" style="max-width: 700px;">Aksu Emlak olarak müşterilerimize en iyi hizmeti sunmak için buradayız.</p>
        </div>
        <div class="row g-4 mt-5">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-box hover-lift hover-shadow h-100">
                    <div class="feature-icon-wrapper">
                         <i class="bi bi-building-check fs-1 text-primary mb-4"></i>
                    </div>
                    <h3 class="feature-title">Geniş Portföy</h3>
                    <p class="feature-description">
                        Her bütçeye ve ihtiyaca uygun binlerce emlak seçeneği arasından size en uygun olanı bulmanıza yardımcı oluyoruz.
                    </p>
                </div>
            </div>

             <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-box hover-lift hover-shadow h-100">
                    <div class="feature-icon-wrapper">
                        <i class="bi bi-person-check fs-1 text-primary mb-4"></i>
                    </div>
                    <h3 class="feature-title">Uzman Danışmanlar</h3>
                    <p class="feature-description">
                        Alanında uzman gayrimenkul danışmanlarımız, ihtiyaçlarınıza en uygun çözümü sunmak için yanınızda.
                    </p>
                </div>
            </div>

             <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-box hover-lift hover-shadow h-100">
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


<section class="section-container section-bg-light py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <span class="section-subtitle">REFERANSLARIMIZ</span>
            <div class="section-divider"></div>
            <h2 class="section-title">Müşterilerimiz Ne Diyor?</h2>
            <p class="section-description mx-auto" style="max-width: 700px;">İşimizi en iyi yapmanın tek yolu müşteri memnuniyetidir.</p>
        </div>
        <div class="row mt-5">
            <div class="col-lg-10 mx-auto">
                <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">                        <div class="carousel-item active">
                            <div class="testimonial-item text-center" data-aos="fade-up" data-aos-delay="100">
                                <div class="testimonial-avatar mb-4">
                                    <div class="avatar-wrapper position-relative">
                                        <img src="assets/img/kadin1.png" alt="Ayşe Yılmaz" class="rounded-circle testimonial-img" style="width: 100px; height: 100px; object-fit: cover;" loading="lazy">
                                        <div class="avatar-border"></div>
                                        <div class="quote-icon">
                                            <i class="bi bi-quote text-primary"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="testimonial-content">
                                    <p class="testimonial-text fs-5 mb-4">"Aksu Emlak sayesinde hayalimdeki evi çok kısa sürede buldum. Profesyonel ekibi ve güler yüzlü hizmetiyle tüm süreci sorunsuz bir şekilde tamamladık."</p>
                                    <div class="star-rating mb-3">
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <i class="bi bi-star-fill text-warning"></i>
                                    </div>
                                    <h4 class="testimonial-name fw-bold">Ayşe Yılmaz</h4>
                                    <p class="testimonial-position text-muted">Müşteri</p>
                                </div>
                            </div>
                        </div>                        <div class="carousel-item">
                            <div class="testimonial-item text-center" data-aos="fade-up" data-aos-delay="100">
                                <div class="testimonial-avatar mb-4">
                                    <div class="avatar-wrapper position-relative">
                                        <img src="assets/img/mehmet_demir.png" alt="Mehmet Demir" class="rounded-circle testimonial-img" style="width: 100px; height: 100px; object-fit: cover;" loading="lazy">
                                        <div class="avatar-border"></div>
                                        <div class="quote-icon">
                                            <i class="bi bi-quote text-primary"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="testimonial-content">
                                    <p class="testimonial-text fs-5 mb-4">"İşimi büyütmek için aradığım ofisi Aksu Emlak'ta buldum. Danışmanım ihtiyaçlarımı çok iyi analiz ederek bana en uygun seçenekleri sundu."</p>
                                    <div class="star-rating mb-3">
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <i class="bi bi-star-fill text-warning"></i>
                                    </div>
                                    <h4 class="testimonial-name fw-bold">Mehmet Demir</h4>
                                    <p class="testimonial-position text-muted">İş İnsanı</p>
                                </div>
                            </div>
                        </div>                        <div class="carousel-item">
                            <div class="testimonial-item text-center" data-aos="fade-up" data-aos-delay="100">
                                <div class="testimonial-avatar mb-4">
                                    <div class="avatar-wrapper position-relative">
                                        <img src="assets/img/kadin2.png" alt="Zeynep Kaya" class="rounded-circle testimonial-img" style="width: 100px; height: 100px; object-fit: cover;" loading="lazy">
                                        <div class="avatar-border"></div>
                                        <div class="quote-icon">
                                            <i class="bi bi-quote text-primary"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="testimonial-content">
                                    <p class="testimonial-text fs-5 mb-4">"Kiralık daire arayışımda Aksu Emlak'ın yardımlarıyla çok kısa sürede istediğim bölgede uygun fiyatlı bir daire bulabildim. Teşekkürler!"</p>
                                    <div class="star-rating mb-3">
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <i class="bi bi-star-fill text-warning"></i>
                                    </div>
                                    <h4 class="testimonial-name fw-bold">Zeynep Kaya</h4>
                                    <p class="testimonial-position text-muted">Öğretmen</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
                        <i class="bi bi-chevron-left fs-3 text-secondary"></i>
                        <span class="visually-hidden">Önceki</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
                        <i class="bi bi-chevron-right fs-3 text-secondary"></i>
                        <span class="visually-hidden">Sonraki</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>


<section class="blog-section py-5">
    <div class="container">
         <h2 class="section-title">Emlak Rehberi</h2>
         <?php
             $blog = new Blog();
             $latestPosts = $blog->getAllPosts(3); // Son 3 blog yazısını al
         ?>
         <div class="row">
             <?php if (!empty($latestPosts)): ?>
                 <?php foreach ($latestPosts as $post): ?>
                     <div class="col-md-4 mb-4">
                         <div class="card blog-card h-100">                             <img src="<?= htmlspecialchars($post['image'] ?? 'assets/img/blog.jpg') ?>"
                                  class="card-img-top"
                                  alt="<?= htmlspecialchars($post['title']) ?>" loading="lazy">
                             <div class="card-body d-flex flex-column">
                                 <p class="blog-date mb-2"><i class="bi bi-calendar3"></i> <?= date('d F Y', strtotime($post['created_at'])) ?></p>
                                 <h5 class="card-title flex-grow-1"><?= htmlspecialchars($post['title']) ?></h5>
                                 <p class="blog-excerpt"><?= htmlspecialchars($post['excerpt'] ?? substr(strip_tags($post['content']), 0, 100) . '...') ?></p>
                                 <a href="blog-detail.php?slug=<?= htmlspecialchars($post['slug']) ?>" class="btn btn-outline-primary mt-auto align-self-start">Devamını Oku</a>
                             </div>
                         </div>
                     </div>
                 <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <p>Henüz blog yazısı bulunmuyor.</p>
                </div>
             <?php endif; ?>
         </div>

         <div class="text-center mt-3">
             <a href="blog.php" class="btn btn-primary px-4">Tüm Yazıları Görüntüle</a>
         </div>
     </div>
 </section>


<section class="contact-cta py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-4 mb-lg-0" data-aos="fade-right">
                <h2 class="mb-2">Hayalinizdeki mülk için bize ulaşın</h2>
                <p class="mb-0">Uzman ekibimiz size en uygun seçenekleri sunmak için hazır.</p>
            </div>
            <div class="col-lg-4 text-lg-end" data-aos="fade-left">
                <a href="contact.php" class="btn btn-light btn-lg px-4">
                    <i class="bi bi-telephone me-2"></i> Bize Ulaşın
                </a>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    try {
        const map = L.map('property-map').setView([39.1, 35.6], 6);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 18
        }).addTo(map);
        
        function formatPrice(price) {
            if (price === null || price === undefined || isNaN(parseFloat(price)) || parseFloat(price) <= 0) {
                return false;
            }
            return new Intl.NumberFormat('tr-TR').format(Math.round(parseFloat(price)));
        }
        
        function getCategoryName(category) {
            const categories = {
                'House': 'Müstakil Ev',
                'Apartment': 'Daire',
                'Commercial': 'Ticari',
                'Land': 'Arsa',
                'Other': 'Diğer'
            };
            return categories[category] || category;
        }
        
        function getShortAddress(property) {
            const parts = [];
            if (property.city) parts.push(property.city);
            if (property.district) parts.push(property.district);
            else if (property.state) parts.push(property.state);
            return parts.join(', ');
        }
        
        const propertyData = <?= $mapDataJson ?>;
        const markers = [];
        
        if (propertyData && propertyData.length > 0) {
            propertyData.forEach(function(property) {
                if (!property.latitude || !property.longitude) return;
                
                // Marker üzerindeki fiyat için değerleri hazırla (değişmiyor)
                let markerPriceText = '';
                let markerClass = '';
                let formattedPinRentPrice = formatPrice(property.rent_price);
                let formattedPinSalePrice = formatPrice(property.sale_price);

                if (formattedPinRentPrice) {
                    markerPriceText = `${formattedPinRentPrice} ₺/ay`;
                    markerClass = 'marker-price-rent';
                } else if (formattedPinSalePrice) {
                    markerPriceText = `${formattedPinSalePrice} ₺`;
                    markerClass = 'marker-price-sale';
                }
                
                const markerHtml = `
                    <div class="marker-container">
                        <div class="marker-pin ${property.featured ? 'featured' : ''}">
                            <i class="bi bi-house-fill"></i>
                        </div>
                        ${markerPriceText ? `<div class="marker-price ${markerClass}">${markerPriceText}</div>` : ''}
                    </div>
                `;
                
                const markerIcon = L.divIcon({
                    className: 'property-marker',
                    html: markerHtml,
                    iconSize: [80, 60],
                    iconAnchor: [40, 60],
                    popupAnchor: [0, -60]
                });
                
                // Modern popup içeriği için fiyat hazırlama
                let formattedPopupSalePrice = formatPrice(property.sale_price);
                let formattedPopupRentPrice = formatPrice(property.rent_price);
                let priceHtml = '';
                
                if (formattedPopupSalePrice && formattedPopupRentPrice) {
                    priceHtml = `
                        <div class="modern-popup-prices">
                            <div class="price-item sale-price">
                                <span class="price-label">Satılık</span>
                                <span class="price-value">${formattedPopupSalePrice} ₺</span>
                            </div>
                            <div class="price-divider"></div>
                            <div class="price-item rent-price">
                                <span class="price-label">Kiralık</span>
                                <span class="price-value">${formattedPopupRentPrice} ₺/ay</span>
                            </div>
                        </div>
                    `;
                } else if (formattedPopupSalePrice) {
                    priceHtml = `
                        <div class="modern-popup-prices">
                            <div class="price-item sale-price">
                                <span class="price-label">Satılık</span>
                                <span class="price-value">${formattedPopupSalePrice} ₺</span>
                            </div>
                        </div>
                    `;
                } else if (formattedPopupRentPrice) {
                    priceHtml = `
                        <div class="modern-popup-prices">
                            <div class="price-item rent-price">
                                <span class="price-label">Kiralık</span>
                                <span class="price-value">${formattedPopupRentPrice} ₺/ay</span>
                            </div>
                        </div>
                    `;
                } else {
                    priceHtml = `
                        <div class="modern-popup-prices">
                            <div class="price-item no-price">
                                <span class="price-value">Fiyat Belirtilmemiş</span>
                            </div>
                        </div>
                    `;
                }
                
                // Ultra modern popup tasarımı
                const popupContent = `
                    <div class="modern-property-popup">
                        <div class="popup-image-container">
                            <img src="${property.main_image || 'assets/img/property-placeholder.jpg'}" 
                                 alt="${property.title || 'İlan görseli'}" 
                                 class="popup-property-image"
                                 onerror="this.onerror=null; this.src='assets/img/property-placeholder.jpg';"
                                 loading="lazy">
                            ${property.featured ? '<div class="featured-badge"><i class="bi bi-star-fill"></i> Öne Çıkan</div>' : ''}
                            <div class="image-overlay"></div>
                        </div>
                        
                        <div class="popup-content-area">
                            <div class="popup-header">
                                <h3 class="property-title">${property.title || 'İlan Detayı'}</h3>
                                <div class="property-location">
                                    <i class="bi bi-geo-alt-fill"></i>
                                    <span>${getShortAddress(property) || 'Konum belirtilmemiş'}</span>
                                </div>
                            </div>
                            
                            ${priceHtml}
                            
                            <div class="property-details">
                                <div class="detail-item">
                                    <i class="bi bi-house-door-fill"></i>
                                    <span class="detail-label">Kategori</span>
                                    <span class="detail-value">${getCategoryName(property.category)}</span>
                                </div>
                            </div>
                            
                            <div class="popup-actions">
                                <a href="listing.php?id=${property.id}" class="modern-detail-btn">
                                    <i class="bi bi-eye-fill"></i>
                                    <span>Detayları İncele</span>
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                `;
                
                const marker = L.marker([property.latitude, property.longitude], {
                    icon: markerIcon
                }).addTo(map);
                
                marker.bindPopup(popupContent, {
                    maxWidth: 320,
                    minWidth: 280,
                    className: 'modern-property-popup-container',
                    closeButton: true,
                    autoPan: true
                });
                
                markers.push(marker);
            });
            
            if (markers.length > 0) {
                const group = new L.featureGroup(markers);
                map.fitBounds(group.getBounds().pad(0.1));
            }
        }
        
        // İstatistik sayacı
        const counters = document.querySelectorAll('.stat-number[data-counter]');
        const speed = 200; // Animasyon hızı

        const animateCounter = (counter) => {
            const target = +counter.getAttribute('data-counter');
            const count = +counter.innerText;
            const increment = target / speed;

            if (count < target) {
                counter.innerText = Math.ceil(count + increment);
                setTimeout(() => animateCounter(counter), 1);
            } else {
                counter.innerText = new Intl.NumberFormat('tr-TR').format(target); // Hedef sayıya ulaşınca formatla
            }
        };

         // Sayaçları tetiklemek için Intersection Observer kullan
         const observer = new IntersectionObserver((entries, observer) => {
             entries.forEach(entry => {
                 if (entry.isIntersecting) {
                     animateCounter(entry.target);
                     observer.unobserve(entry.target); // Bir kez çalıştır
                 }
             });
         }, { threshold: 0.5 }); // %50 görünür olunca başla

         counters.forEach(counter => {
             observer.observe(counter);
         });


    } catch (error) {
        console.error("Harita başlatılırken bir hata oluştu:", error);
        const mapContainer = document.getElementById('property-map');
        if (mapContainer) {
            mapContainer.innerHTML = '<p class="text-danger text-center">Harita yüklenemedi.</p>';
        }
    }
});
</script>

<style>
/* Marker stilleri (değişmiyor) */
.property-marker {
    z-index: 1000;
}

.marker-container {
    position: relative;
    width: auto;
    min-width: 30px;
}

.marker-pin {
    width: 30px;
    height: 30px;
    background-color: #ff6b6b;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    color: white;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
}

.marker-pin.featured {
    background-color: #ffc107;
}

.marker-pin i {
    font-size: 16px;
}

.marker-price {
    position: absolute;
    bottom: -25px;
    left: 50%;
    transform: translateX(-50%);
    background: white;
    padding: 3px 8px;
    border-radius: 3px;
    white-space: nowrap;
    font-weight: bold;
    font-size: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.marker-price-sale {
    color: #dc3545;
}

.marker-price-rent {
    color: #28a745;
}

/* Ultra Modern Popup Tasarımı */
.modern-property-popup-container .leaflet-popup-content-wrapper {
    background: transparent;
    padding: 0;
    border-radius: 16px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    overflow: hidden;
    border: none;
}

.modern-property-popup-container .leaflet-popup-content {
    margin: 0;
    padding: 0;
    border-radius: 16px;
    overflow: hidden;
}

.modern-property-popup-container .leaflet-popup-close-button {
    color: white;
    font-size: 18px;
    font-weight: bold;
    top: 10px;
    right: 10px;
    background: rgba(0,0,0,0.5);
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    z-index: 1000;
}

.modern-property-popup-container .leaflet-popup-close-button:hover {
    background: rgba(0,0,0,0.7);
    color: white;
}

.modern-property-popup {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    min-width: 280px;
    max-width: 320px;
}

.popup-image-container {
    position: relative;
    height: 160px;
    overflow: hidden;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.popup-property-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.popup-property-image:hover {
    transform: scale(1.05);
}

.image-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 50%;
    background: linear-gradient(to top, rgba(0,0,0,0.6), transparent);
    pointer-events: none;
}

.featured-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    background: linear-gradient(135deg, #ffd700, #ffed4e);
    color: #333;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 4px;
    box-shadow: 0 2px 8px rgba(255,215,0,0.3);
}

.popup-content-area {
    padding: 18px;
}

.popup-header {
    margin-bottom: 16px;
}

.property-title {
    font-size: 16px;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0 0 8px 0;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.property-location {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #6b7280;
    font-size: 13px;
}

.property-location i {
    color: #ef4444;
    font-size: 14px;
}

.modern-popup-prices {
    background: linear-gradient(135deg, #f8fafc, #e2e8f0);
    border-radius: 12px;
    padding: 14px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
}

.price-item {
    text-align: center;
    flex: 1;
}

.price-label {
    display: block;
    font-size: 11px;
    color: #6b7280;
    font-weight: 500;
    margin-bottom: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.price-value {
    display: block;
    font-size: 15px;
    font-weight: 700;
    color: #1a1a1a;
}

.sale-price .price-value {
    color: #dc2626;
}

.rent-price .price-value {
    color: #059669;
}

.no-price .price-value {
    color: #6b7280;
    font-style: italic;
    font-weight: 500;
}

.price-divider {
    width: 1px;
    height: 30px;
    background: #d1d5db;
}

.property-details {
    margin-bottom: 16px;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    color: #4b5563;
    font-size: 13px;
}

.detail-item i {
    color: #6366f1;
    font-size: 16px;
    width: 16px;
}

.detail-label {
    color: #6b7280;
    font-weight: 500;
}

.detail-value {
    font-weight: 600;
    color: #1f2937;
    margin-left: auto;
}

.popup-actions {
    margin-top: 16px;
}

.modern-detail-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 12px 16px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    text-decoration: none;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.modern-detail-btn:hover {
    background: linear-gradient(135deg, #2563eb, #1e40af);
    color: white;
    text-decoration: none;
    transform: translateY(-1px);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
}

.modern-detail-btn:active {
    transform: translateY(0);
}

.modern-detail-btn i:first-child {
    font-size: 16px;
}

.modern-detail-btn i:last-child {
    font-size: 14px;
    transition: transform 0.3s ease;
}

.modern-detail-btn:hover i:last-child {
    transform: translateX(2px);
}
</style>
<?php require_once 'templates/footer.php'; ?>