<!-- // listing.php --> 
<?php
require_once 'includes/init.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$listingId = (int)$_GET['id'];
$listingObj = new Listing();
$listing = $listingObj->getListingById($listingId);

if (!$listing) {
    header('Location: index.php');
    exit;
}

$images = $listingObj->getListingImages($listingId);
$mainImage = null;

foreach ($images as $img) {
    if ($img['is_main']) {
        $mainImage = $img;
        break;
    }
}

// JSON formatında özellikler ve mesafeler
$features = json_decode($listing['features'], true) ?: [];
$distances = json_decode($listing['distances'], true) ?: [];
$multimedia = json_decode($listing['multimedia'], true) ?: [];

$pageTitle = htmlspecialchars($listing['title']);
require_once 'templates/header.php';
?>

<!-- İlan Detay -->
<div class="container mt-4 mb-5">
    <div class="row">
        <!-- Sol Kolon (Görsel Galeri) -->
        <div class="col-lg-8">
            <!-- Görsel Galerisi -->
            <div class="property-gallery">
                <?php if (count($images) > 0): ?>
                    <div id="propertyCarousel" class="carousel slide" data-bs-ride="false">
                        <div class="carousel-indicators">
                            <?php foreach ($images as $index => $img): ?>
                                <button type="button" 
                                        data-bs-target="#propertyCarousel" 
                                        data-bs-slide-to="<?= $index ?>" 
                                        <?= $img['is_main'] ? 'class="active"' : '' ?>
                                        aria-current="<?= $img['is_main'] ? 'true' : 'false' ?>">
                                </button>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="carousel-inner">
                            <?php foreach ($images as $index => $img): ?>
                                <div class="carousel-item <?= $img['is_main'] ? 'active' : '' ?>">
                                    <img src="<?= htmlspecialchars($img['image_url']) ?>" 
                                         class="d-block w-100 rounded" 
                                         alt="<?= htmlspecialchars($listing['title']) ?>"
                                         loading="lazy">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <button class="carousel-control-prev" type="button" data-bs-target="#propertyCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Önceki</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#propertyCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Sonraki</span>
                        </button>
                    </div>
                    
                    <!-- Galerinin küçük görselleri -->
                    <div class="property-thumbnails mt-3 row g-2">
                        <?php foreach ($images as $index => $img): ?>
                            <div class="col-3 col-md-2">
                                <img src="<?= htmlspecialchars($img['image_url']) ?>" 
                                     class="img-thumbnail thumbnail-img" 
                                     alt=""
                                     data-bs-target="#propertyCarousel" 
                                     data-bs-slide-to="<?= $index ?>"
                                     loading="lazy">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="property-image">
                        <img src="assets/img/property-placeholder.jpg" 
                             class="img-fluid rounded w-100" 
                             alt="<?= htmlspecialchars($listing['title']) ?>">
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- İlan Açıklaması -->
            <div class="property-description mt-4">
                <h1 class="h2 mb-2"><?= htmlspecialchars($listing['title']) ?></h1>
                <p class="text-muted mb-1">
                    <i class="bi bi-geo-alt"></i> 
                    <?= htmlspecialchars($listing['street']) ?>, 
                    <?= htmlspecialchars($listing['city']) ?>, 
                    <?= htmlspecialchars($listing['state']) ?>, 
                    <?= htmlspecialchars($listing['country']) ?>
                </p>
                
              <!-- İlan detay sayfasında, fiyat gösterimi kısmını güncelleyin -->
<div class="property-price mb-4">
    <?php if ($listing['rent_price'] && $listing['rent_price'] > 0): ?>
        <span class="h3 text-primary fw-bold">
            <?= number_format($listing['rent_price'], 0, ',', '.') ?> ₺/ay
        </span>
        <span class="badge bg-success ms-2">Kiralık</span>
    <?php else: ?>
        <span class="h3 text-primary fw-bold">
            <?= number_format($listing['sale_price'], 0, ',', '.') ?> ₺
        </span>
        <span class="badge bg-danger ms-2">Satılık</span>
    <?php endif; ?>
</div>
                <div class="property-features d-flex mb-4 flex-wrap">
                    <div class="pe-4 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-house-door fs-5 me-2"></i>
                            <div>
                                <small class="text-muted d-block">Alan</small>
                                <span class="fw-bold"><?= htmlspecialchars($listing['property_size']) ?> m²</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="pe-4 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-door-closed fs-5 me-2"></i>
                            <div>
                                <small class="text-muted d-block">Oda</small>
                                <span class="fw-bold"><?= htmlspecialchars($listing['rooms']) ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="pe-4 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-water fs-5 me-2"></i>
                            <div>
                                <small class="text-muted d-block">Banyo</small>
                                <span class="fw-bold"><?= htmlspecialchars($listing['bathrooms']) ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="pe-4 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-layers fs-5 me-2"></i>
                            <div>
                                <small class="text-muted d-block">Kat</small>
                                <span class="fw-bold"><?= htmlspecialchars($listing['floors_no']) ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($listing['garages']): ?>
                    <div class="pe-4 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-car-front fs-5 me-2"></i>
                            <div>
                                <small class="text-muted d-block">Garaj</small>
                                <span class="fw-bold"><?= htmlspecialchars($listing['garages']) ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($listing['year_built']): ?>
                    <div class="pe-4 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-calendar-event fs-5 me-2"></i>
                            <div>
                                <small class="text-muted d-block">Yapım Yılı</small>
                                <span class="fw-bold"><?= htmlspecialchars($listing['year_built']) ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($listing['energy_efficiency']): ?>
                    <div class="pe-4 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-lightning fs-5 me-2"></i>
                            <div>
                                <small class="text-muted d-block">Enerji Verimliliği</small>
                                <span class="fw-bold">Sınıf <?= htmlspecialchars($listing['energy_efficiency']) ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="property-description mb-4">
                    <h3 class="h5 mb-3">Açıklama</h3>
                    <div class="description-content">
                        <?= nl2br(htmlspecialchars($listing['description'])) ?>
                    </div>
                </div>
                
                <!-- Özellikler -->
                <?php if (!empty($features)): ?>
                <div class="property-amenities mb-4">
                    <h3 class="h5 mb-3">Özellikler</h3>
                    <div class="row">
                        <?php foreach ($features as $category => $categoryFeatures): ?>
                            <div class="col-md-6 mb-4">
                                <h4 class="h6 mb-2"><?= htmlspecialchars($category) ?></h4>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($categoryFeatures as $feature): ?>
                                        <li class="list-group-item d-flex align-items-center ps-0 border-0 py-1">
                                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                                            <?= htmlspecialchars($feature) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Multimedya Bölümü -->
                <?php if (!empty($multimedia) && (!empty($multimedia['video_url']) || !empty($multimedia['virtual_tour']))): ?>
                <div class="property-multimedia mb-4">
                    <h3 class="h5 mb-3">Multimedya</h3>
                    
                    <ul class="nav nav-tabs" id="multimediaTab" role="tablist">
                        <?php if (!empty($multimedia['video_url'])): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="video-tab" data-bs-toggle="tab" data-bs-target="#video-tab-pane" type="button" role="tab">Video</button>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($multimedia['virtual_tour'])): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?= empty($multimedia['video_url']) ? 'active' : '' ?>" id="tour-tab" data-bs-toggle="tab" data-bs-target="#tour-tab-pane" type="button" role="tab">Sanal Tur</button>
                        </li>
                        <?php endif; ?>
                    </ul>
                    
                    <div class="tab-content pt-3" id="multimediaTabContent">
                        <?php if (!empty($multimedia['video_url'])): ?>
                        <div class="tab-pane fade show active" id="video-tab-pane" role="tabpanel" tabindex="0">
                            <div class="ratio ratio-16x9">
                                <iframe src="<?= htmlspecialchars($multimedia['video_url']) ?>" 
                                        title="Property Video" 
                                        allowfullscreen 
                                        loading="lazy"></iframe>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($multimedia['virtual_tour'])): ?>
                        <div class="tab-pane fade <?= empty($multimedia['video_url']) ? 'show active' : '' ?>" id="tour-tab-pane" role="tabpanel" tabindex="0">
                            <div class="ratio ratio-16x9">
                                <iframe src="<?= htmlspecialchars($multimedia['virtual_tour']) ?>" 
                                        title="Virtual Tour" 
                                        allowfullscreen 
                                        loading="lazy"></iframe>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Harita -->
                <div class="property-map mb-4">
                    <h3 class="h5 mb-3">Konum</h3>
                    <div id="property-location-map" style="height: 400px;"></div>
                </div>
            </div>
        </div>
        
        <!-- Sağ Kolon (Yan Bilgiler) -->
        <div class="col-lg-4">
            <div class="position-sticky" style="top: 2rem;">
                <!-- Yakın Çevre Mesafeleri -->
                <?php if (!empty($distances)): ?>
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h4 class="h5 mb-0">Yakın Çevre</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($distances as $place => $distance): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <?= htmlspecialchars($place) ?>
                                    <span class="badge bg-primary rounded-pill"><?= htmlspecialchars($distance) ?> km</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- İletişim Bilgileri -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h4 class="h5 mb-0">İletişim</h4>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            Bu ilan hakkında daha fazla bilgi almak için lütfen bizimle iletişime geçin.
                        </p>
                        <a href="tel:+905555555555" class="btn btn-primary w-100 mb-2">
                            <i class="bi bi-telephone me-2"></i> Hemen Ara
                        </a>
                        <a href="mailto:info@emlak.com" class="btn btn-outline-primary w-100">
                            <i class="bi bi-envelope me-2"></i> E-posta Gönder
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Leaflet haritayı başlat
    document.addEventListener('DOMContentLoaded', function() {
        // Konum bilgileri
        const lat = <?= $listing['latitude'] ?>;
        const lng = <?= $listing['longitude'] ?>;
        const title = "<?= htmlspecialchars(addslashes($listing['title'])) ?>";
        
        // Harita oluştur
        const map = L.map('property-location-map').setView([lat, lng], 15);
        
        // OpenStreetMap katmanı
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 18
        }).addTo(map);
        
        // Marker ikon stilini özelleştir
        const propertyIcon = L.divIcon({
            className: 'property-marker',
            html: `<div class="marker-price"><?= number_format($listing['sale_price'], 0, ',', '.') ?> ₺</div>`,
            iconSize: [80, 30]
        });
        
        // Marker ekle
        L.marker([lat, lng], {
            icon: propertyIcon
        }).addTo(map)
          .bindPopup(`<b>${title}</b><br>${"<?= htmlspecialchars(addslashes($listing['street'])) ?>"}`);
        
        // Galeri küçük resimlerine tıklama
        document.querySelectorAll('.thumbnail-img').forEach(function(img) {
            img.addEventListener('click', function() {
                const slideIndex = this.getAttribute('data-bs-slide-to');
                const carousel = bootstrap.Carousel.getInstance(document.getElementById('propertyCarousel'));
                carousel.to(slideIndex);
            });
        });
    });
</script>

<?php require_once 'templates/footer.php'; ?>