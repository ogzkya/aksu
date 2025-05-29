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
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <div class="testimonial-item text-center">
                                <div class="testimonial-avatar mb-4">
                                    <img src="assets/img/avatar-1.jpg" alt="Müşteri" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;" loading="lazy">
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
                                    <img src="assets/img/avatar-2.jpg" alt="Müşteri" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;" loading="lazy">
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
                                    <img src="assets/img/avatar-3.jpg" alt="Müşteri" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;" loading="lazy">
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
                         <div class="card blog-card h-100">
                             <img src="<?= htmlspecialchars($post['image'] ?? 'assets/img/blog-placeholder.jpg') ?>"
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
    // Harita başlatma
    try {
        // Harita merkezi (Türkiye)
        const map = L.map('property-map').setView([39.1, 35.6], 6);
        
        // OpenStreetMap katmanı
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 18
        }).addTo(map);
        
        // Fiyatı biçimlendiren yardımcı fonksiyon
        function formatPrice(price) {
            // Sadece sayısal değerler için formatla
            if (price === null || price === undefined || isNaN(parseFloat(price)) || parseFloat(price) <= 0) {
                return false; // Geçersiz fiyat için false döndür
            }
            // Sayıyı formatla
            return new Intl.NumberFormat('tr-TR').format(Math.round(parseFloat(price)));
        }
        
        // Kategori adı fonksiyonu
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
        
        // Kısa adres fonksiyonu
        function getShortAddress(property) {
            const parts = [];
            if (property.city) parts.push(property.city);
            if (property.district) parts.push(property.district);
            return parts.join(', ');
        }
        
        const propertyData = <?= $mapDataJson ?>;
        const markers = [];
        
        if (propertyData && propertyData.length > 0) {
            propertyData.forEach(function(property) {
                if (!property.latitude || !property.longitude) return;
                
                // Marker üzerindeki fiyat için değerleri hazırla
                let markerPriceText = '';
                let markerClass = '';
                
                let formattedPinRentPrice = formatPrice(property.rent_price);
                let formattedPinSalePrice = formatPrice(property.sale_price);

                // Marker üzerinde gösterilecek fiyatı belirle (öncelik kiralık, sonra satılık)
                if (formattedPinRentPrice) {
                    markerPriceText = `${formattedPinRentPrice} ₺/ay`;
                    markerClass = 'marker-price-rent';
                } else if (formattedPinSalePrice) {
                    markerPriceText = `${formattedPinSalePrice} ₺`;
                    markerClass = 'marker-price-sale';
                }
                
                // Marker gösterimi için HTML
                const markerHtml = `
                    <div class="marker-container">
                        <div class="marker-pin ${property.featured ? 'featured' : ''}">
                            <i class="bi bi-house-fill"></i>
                        </div>
                        ${markerPriceText ? `<div class="marker-price ${markerClass}">${markerPriceText}</div>` : ''}
                    </div>
                `;
                
                // Özel marker ikonu
                const markerIcon = L.divIcon({
                    className: 'property-marker',
                    html: markerHtml,
                    iconSize: [80, 60], // Boyutlar içeriğe göre ayarlanabilir
                    iconAnchor: [40, 60], // İkonun haritadaki bağlantı noktası
                    popupAnchor: [0, -60] // Popup'ın ikona göre konumu
                });
                
                // Popup içeriği için fiyatları hazırla
                let formattedPopupSalePrice = formatPrice(property.sale_price);
                let formattedPopupRentPrice = formatPrice(property.rent_price);
                let priceHtml = ''; // Popup içinde gösterilecek fiyat HTML'i
                
                if (formattedPopupSalePrice && formattedPopupRentPrice) {
                    // Hem satılık hem kiralık fiyatı varsa
                    priceHtml = `
                        <div class="popup-prices">
                            <span class="popup-price sale">${formattedPopupSalePrice} ₺</span>
                            <span class="popup-price-divider">|</span>
                            <span class="popup-price rent">${formattedPopupRentPrice} ₺/ay</span>
                        </div>
                    `;
                } else if (formattedPopupSalePrice) {
                    // Sadece satılık fiyatı varsa
                    priceHtml = `<div class="popup-prices"><span class="popup-price sale">${formattedPopupSalePrice} ₺</span></div>`;
                } else if (formattedPopupRentPrice) {
                    // Sadece kiralık fiyatı varsa
                    priceHtml = `<div class="popup-prices"><span class="popup-price rent">${formattedPopupRentPrice} ₺/ay</span></div>`;
                } else {
                    // Hiçbir fiyat belirtilmemişse
                    priceHtml = `<div class="popup-prices"><span class="popup-price none">Fiyat belirtilmemiş</span></div>`;
                }
                
                // Modern popup içeriği
                const popupContent = `
                    <div class="popup-modern">
                        <div class="popup-img-wrap">
                            <img src="${property.main_image || 'assets/img/property-placeholder.jpg'}" 
                                 alt="${property.title || 'İlan görseli'}" 
                                 class="popup-img" 
                                 onerror="this.onerror=null; this.src='assets/img/property-placeholder.jpg';"
                                 loading="lazy">
                            ${property.featured ? '<span class="popup-featured">Öne Çıkan</span>' : ''}
                        </div>
                        <div class="popup-main-content">
                            <div class="popup-title-row">
                                <span class="popup-title">${property.title || 'İlan Detayı'}</span>
                            </div>
                            ${priceHtml}
                            <div class="popup-meta">
                                <span class="popup-category"><i class="bi bi-tag"></i> ${getCategoryName(property.category)}</span>
                                ${getShortAddress(property) ? `<span class="popup-address"><i class="bi bi-geo-alt"></i> ${getShortAddress(property)}</span>` : ''}
                            </div>
                            <a href="listing.php?id=${property.id}" class="popup-btn-modern">
                                <i class="bi bi-info-circle"></i> Detayları Gör
                            </a>
                        </div>
                    </div>
                `;
                
                // İşaretleyiciyi oluştur ve haritaya ekle
                const marker = L.marker([property.latitude, property.longitude], {
                    icon: markerIcon
                }).addTo(map);
                
                // Açılır pencere ekle
                marker.bindPopup(popupContent, {
                    maxWidth: 280,
                    minWidth: 200,
                    className: 'property-popup'
                });
                
                markers.push(marker);
            });
            
            // Eğer marker varsa, haritayı otomatik sığdır
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
/* Harita marker ve popup stillerini iyileştir */
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

.map-popup {
    padding: 0;
    overflow: hidden;
}

.map-popup-image {
    position: relative;
    height: 120px;
    overflow: hidden;
}

.map-popup-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.popup-badge {
    position: absolute;
    top: 5px;
    right: 5px;
    background: #ffc107;
    color: #000;
    padding: 2px 6px;
    font-size: 10px;
    border-radius: 3px;
}

.map-popup-content {
    padding: 8px;
}

.popup-title {
    font-size: 14px;
    margin-bottom: 5px;
    font-weight: bold;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.popup-price {
    font-weight: bold;
    margin-bottom: 5px;
    font-size: 13px;
}

.popup-price.sale {
    color: #dc3545;
}

.popup-price.rent {
    color: #28a745;
}

.popup-category {
    font-size: 12px;
    color: #6c757d;
    margin-bottom: 8px;
}

.property-popup .leaflet-popup-content {
    margin: 0;
}

.property-popup .leaflet-popup-content-wrapper {
    padding: 0;
    border-radius: 5px;
    overflow: hidden;
}

.popup-price-container {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-bottom: 5px;
}

.popup-price-divider {
    color: #aaa;
    font-weight: normal;
}

/* Popup butonu için stil */
.popup-btn-modern {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px 15px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    text-decoration: none;
    transition: background 0.3s;
}

.popup-btn-modern i {
    margin-right: 5px;
}

.popup-btn-modern:hover {
    background: #0056b3;
}
</style>
<?php require_once 'templates/footer.php'; ?>