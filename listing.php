<?php
// listing.php
require_once 'includes/init.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$listingId = (int)$_GET['id'];
$listingObj = new Listing();
$listing = $listingObj->getListingById($listingId);

// İlan bulunamazsa veya fiyatı yoksa (opsiyonel) ana sayfaya yönlendir
if (!$listing || ($listing['sale_price'] <= 0 && $listing['rent_price'] <= 0) ) {
    // header('Location: index.php');
    // exit;
    // Veya 404 sayfası gösterilebilir
     http_response_code(404);
     echo "İlan bulunamadı."; // Basit bir mesaj
     exit;
}


$images = $listingObj->getListingImages($listingId);
$mainImage = null;
$otherImages = []; // Ana resim dışındaki resimler

if (!empty($images)) {
    // Ana görseli bul ve ayır
    foreach ($images as $index => $img) {
        if ($img['is_main'] && !$mainImage) { // Sadece ilk ana görseli al
            $mainImage = $img;
        } else {
            $otherImages[] = $img;
        }
    }
    // Eğer hiçbiri ana değilse, ilk resmi ana olarak kabul et
    if (!$mainImage && !empty($images)) {
        $mainImage = $images[0];
        // Diğer resimler dizisinden ilk resmi çıkar (eğer oradaysa)
        if ($otherImages[0]['id'] == $mainImage['id']) {
             array_shift($otherImages);
        }
    }
    // Ana görseli diğer resimlerin başına ekle (Carousel için)
    $carouselImages = $mainImage ? array_merge([$mainImage], $otherImages) : $images;
} else {
    $carouselImages = [];
}


// JSON formatında özellikler ve mesafeler
$features = json_decode($listing['features'] ?? '[]', true) ?: [];
$distances = json_decode($listing['distances'] ?? '[]', true) ?: [];
$multimedia = json_decode($listing['multimedia'] ?? '[]', true) ?: [];

$pageTitle = htmlspecialchars($listing['title']);
$pageDescription = htmlspecialchars($listing['short_description'] ?? substr(strip_tags($listing['description']), 0, 160)); // Description eklendi
$activePage = ""; // Belirli bir menü öğesi aktif değil
require_once 'templates/header.php';
?>

<div class="container mt-4 mb-5">
    <div class="row">
        <div class="col-lg-8">
            <h1 class="h2 mb-2 listing-title"><?= htmlspecialchars($listing['title']) ?></h1>
             <p class="text-muted mb-3 listing-location">
                <i class="bi bi-geo-alt"></i>
                <?= htmlspecialchars($listing['street'] ?? '') ?>,
                <?= htmlspecialchars($listing['city']) ?>,
                <?= htmlspecialchars($listing['state']) ?>,
                <?= htmlspecialchars($listing['country']) ?>
            </p>

            <div class="property-gallery card shadow-sm mb-4">
                 <div class="card-body p-2">
                    <?php if (count($carouselImages) > 0): ?>
                        <div id="propertyCarousel" class="carousel slide" data-bs-ride="carousel"> 
                           <div class="carousel-indicators">
                                <?php foreach ($carouselImages as $index => $img): ?>
                                    <button type="button"
                                            data-bs-target="#propertyCarousel"
                                            data-bs-slide-to="<?= $index ?>"
                                            class="<?= $index === 0 ? 'active' : '' ?>"
                                            aria-current="<?= $index === 0 ? 'true' : 'false' ?>">
                                    </button>
                                <?php endforeach; ?>
                            </div>

                            <div class="carousel-inner rounded">
                                <?php foreach ($carouselImages as $index => $img): ?>
                                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                        <img src="<?= htmlspecialchars($img['image_url']) ?>"
                                             class="d-block w-100"
                                             style="height: 500px; object-fit: cover;"
                                             alt="<?= htmlspecialchars($listing['title']) ?> - Görsel <?= $index + 1 ?>"
                                             loading="<?= $index === 0 ? 'eager' : 'lazy' ?>"> 
                                    </div>
                                <?php endforeach; ?>
                            </div>

                           <?php if (count($carouselImages) > 1): ?>
                                <button class="carousel-control-prev" type="button" data-bs-target="#propertyCarousel" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Önceki</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#propertyCarousel" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Sonraki</span>
                                </button>
                            <?php endif; ?>
                        </div>

                        <?php if (count($carouselImages) > 1): ?>
                            <div class="property-thumbnails mt-3 row g-2 px-2">
                                <?php foreach ($carouselImages as $index => $img): ?>
                                    <div class="col-3 col-md-2">
                                        <img src="<?= htmlspecialchars($img['image_url']) ?>"
                                             class="img-thumbnail thumbnail-img"
                                             style="cursor: pointer; height: 70px; object-fit: cover;"
                                             alt="Küçük Görsel <?= $index + 1 ?>"
                                             data-bs-target="#propertyCarousel"
                                             data-bs-slide-to="<?= $index ?>"
                                             loading="lazy">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="property-image">
                            <img src="assets/img/property-placeholder.jpg"
                                 class="img-fluid rounded w-100"
                                 style="height: 500px; object-fit: cover;"
                                 alt="<?= htmlspecialchars($listing['title']) ?>">
                        </div>
                    <?php endif; ?>
                 </div>
            </div>

            <div class="property-details card shadow-sm mb-4">
                 <div class="card-body p-4">
                     <div class="property-price mb-4">
                        <?php
                        $displayPrice = '';
                        $priceBadge = '';
                        if ($listing['rent_price'] && $listing['rent_price'] > 0) {
                            $displayPrice = number_format($listing['rent_price'], 0, ',', '.') . ' ₺/ay';
                            $priceBadge = '<span class="badge bg-success ms-2">Kiralık</span>';
                        } elseif ($listing['sale_price'] && $listing['sale_price'] > 0) {
                            $displayPrice = number_format($listing['sale_price'], 0, ',', '.') . ' ₺';
                            $priceBadge = '<span class="badge bg-danger ms-2">Satılık</span>';
                        } else {
                            $displayPrice = 'Fiyat Belirtilmemiş';
                        }
                        ?>
                        <span class="h3 text-primary fw-bold"><?= $displayPrice ?></span>
                        <?= $priceBadge ?>
                    </div>
                     <div class="property-features d-flex mb-4 flex-wrap border-bottom pb-3">
                        <div class="pe-4 mb-2 feature-item">
                            <i class="bi bi-house-door fs-5 me-2 text-primary"></i>
                            <div>
                                <small class="text-muted d-block">Alan</small>
                                <span class="fw-bold"><?= htmlspecialchars($listing['property_size'] ?? 0) ?> m²</span>
                            </div>
                        </div>

                        <div class="pe-4 mb-2 feature-item">
                            <i class="bi bi-door-closed fs-5 me-2 text-primary"></i>
                            <div>
                                <small class="text-muted d-block">Oda</small>
                                <span class="fw-bold"><?= htmlspecialchars($listing['rooms'] ?? 0) ?></span>
                            </div>
                        </div>

                        <div class="pe-4 mb-2 feature-item">
                            <i class="bi bi-water fs-5 me-2 text-primary"></i>
                            <div>
                                <small class="text-muted d-block">Banyo</small>
                                <span class="fw-bold"><?= htmlspecialchars($listing['bathrooms'] ?? 0) ?></span>
                            </div>
                        </div>

                        <div class="pe-4 mb-2 feature-item">
                            <i class="bi bi-layers fs-5 me-2 text-primary"></i>
                            <div>
                                <small class="text-muted d-block">Kat</small>
                                <span class="fw-bold"><?= htmlspecialchars($listing['floors_no'] ?? 0) ?></span>
                            </div>
                        </div>

                        <?php if ($listing['garages']): ?>
                        <div class="pe-4 mb-2 feature-item">
                            <i class="bi bi-car-front fs-5 me-2 text-primary"></i>
                            <div>
                                <small class="text-muted d-block">Garaj</small>
                                <span class="fw-bold"><?= htmlspecialchars($listing['garages']) ?></span>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($listing['year_built']): ?>
                        <div class="pe-4 mb-2 feature-item">
                            <i class="bi bi-calendar-event fs-5 me-2 text-primary"></i>
                            <div>
                                <small class="text-muted d-block">Yapım Yılı</small>
                                <span class="fw-bold"><?= htmlspecialchars($listing['year_built']) ?></span>
                            </div>
                        </div>
                        <?php endif; ?>                        <?php if ($listing['energy_efficiency']): ?>
                        <div class="pe-4 mb-2 feature-item">
                           <i class="bi bi-lightning-charge-fill fs-5 me-2 text-primary"></i>
                            <div>
                                <small class="text-muted d-block">Enerji Verimliliği</small>
                                <span class="fw-bold">Sınıf <?= htmlspecialchars($listing['energy_efficiency']) ?></span>
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

                    <?php if (!empty($features) && ( !empty($features['İç Özellikler']) || !empty($features['Dış Özellikler']) || !empty($features['Çevre Özellikleri']) )): ?>
                    <div class="property-amenities mb-4">
                        <h3 class="h5 mb-3">Özellikler</h3>
                        <div class="row">
                             <?php if(!empty($features['İç Özellikler'])): ?>
                                <div class="col-md-6 mb-3">
                                    <h4 class="h6 mb-2">İç Özellikler</h4>
                                    <ul class="list-unstyled amenity-list">
                                        <?php foreach ($features['İç Özellikler'] as $feature): ?>
                                            <li><i class="bi bi-check-circle-fill text-success me-2"></i><?= htmlspecialchars($feature) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                             <?php endif; ?>
                             <?php if(!empty($features['Dış Özellikler'])): ?>
                                 <div class="col-md-6 mb-3">
                                    <h4 class="h6 mb-2">Dış Özellikler</h4>
                                    <ul class="list-unstyled amenity-list">
                                        <?php foreach ($features['Dış Özellikler'] as $feature): ?>
                                            <li><i class="bi bi-check-circle-fill text-success me-2"></i><?= htmlspecialchars($feature) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <?php if(!empty($features['Çevre Özellikleri'])): ?>
                                <div class="col-md-6 mb-3">
                                    <h4 class="h6 mb-2">Çevre Özellikleri</h4>
                                    <ul class="list-unstyled amenity-list">
                                        <?php foreach ($features['Çevre Özellikleri'] as $feature): ?>
                                            <li><i class="bi bi-check-circle-fill text-success me-2"></i><?= htmlspecialchars($feature) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                             <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                     <?php if (!empty($multimedia) && (!empty($multimedia['video_url']) || !empty($multimedia['virtual_tour']))): ?>
                    <div class="property-multimedia mb-4">
                        <h3 class="h5 mb-3">Multimedya</h3>

                        <ul class="nav nav-tabs" id="multimediaTab" role="tablist">
                            <?php $hasVideo = !empty($multimedia['video_url']); ?>
                            <?php $hasTour = !empty($multimedia['virtual_tour']); ?>

                            <?php if ($hasVideo): ?>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="video-tab" data-bs-toggle="tab" data-bs-target="#video-tab-pane" type="button" role="tab">Video</button>
                            </li>
                            <?php endif; ?>

                            <?php if ($hasTour): ?>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?= !$hasVideo ? 'active' : '' ?>" id="tour-tab" data-bs-toggle="tab" data-bs-target="#tour-tab-pane" type="button" role="tab">Sanal Tur</button>
                            </li>
                            <?php endif; ?>
                        </ul>

                        <div class="tab-content pt-3" id="multimediaTabContent">
                            <?php if ($hasVideo): ?>
                            <div class="tab-pane fade show active" id="video-tab-pane" role="tabpanel" tabindex="0">
                                <div class="ratio ratio-16x9 rounded overflow-hidden">
                                     <iframe src="<?= htmlspecialchars($multimedia['video_url']) ?>"
                                            title="Property Video"
                                            allowfullscreen
                                            loading="lazy"></iframe>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($hasTour): ?>
                            <div class="tab-pane fade <?= !$hasVideo ? 'show active' : '' ?>" id="tour-tab-pane" role="tabpanel" tabindex="0">
                                <div class="ratio ratio-16x9 rounded overflow-hidden">
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

                    <?php if ($listing['latitude'] && $listing['longitude']): ?>
                    <div class="property-map mb-4">
                        <h3 class="h5 mb-3">Konum</h3>
                        <div class="card">
                            <div class="card-body p-0">
                                 <div id="property-location-map" style="height: 400px;"></div>
                            </div>
                        </div>
                    </div>
                     <?php endif; ?>

                </div>
            </div>


        </div>

        <div class="col-lg-4">
            <div class="position-sticky" style="top: 90px;">
                 <div class="card shadow-sm mb-4 contact-card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="h5 mb-0"><i class="bi bi-person-rolodex me-2"></i>İletişim</h4>
                    </div>                    <div class="card-body text-center">
                         <img src="assets/img/logo.png" alt="Aksu Emlak" class="mb-3" width="80" loading="lazy">
                        <h5 class="card-title">Aksu Emlak</h5>
                        <p class="card-text text-muted">Size yardımcı olmak için buradayız.</p>                        <a href="tel:+902126939088" class="btn btn-success w-100 mb-2">
                            <i class="bi bi-telephone me-2"></i> (0212) 693 90 88
                        </a>
                         <a href="mailto:aksu-emlak@hotmail.com.tr" class="btn btn-outline-primary w-100">
                            <i class="bi bi-envelope me-2"></i> E-posta Gönder
                        </a>
                         <a href="contact.php" class="btn btn-link w-100 mt-2">İletişim Formu</a>
                    </div>
                </div>


                <?php if (!empty($distances)): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h4 class="h5 mb-0"><i class="bi bi-geo me-2"></i>Yakın Çevre</h4>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($distances as $place => $distance): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-2">
                                     <span><i class="bi bi-pin-map me-2 text-muted"></i><?= htmlspecialchars($place) ?></span>
                                    <span class="badge bg-primary rounded-pill"><?= htmlspecialchars($distance) ?> km</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Harita başlatma (sadece koordinat varsa)
     const lat = <?= $listing['latitude'] ?? 'null' ?>;
     const lng = <?= $listing['longitude'] ?? 'null' ?>;
     const mapContainer = document.getElementById('property-location-map');

     if (lat && lng && mapContainer) {
        try {
            const map = L.map('property-location-map').setView([lat, lng], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                maxZoom: 18
            }).addTo(map);            // Fiyatı biçimlendiren yardımcı fonksiyon
            function formatPrice(price) {
                 if (typeof price !== 'number' || isNaN(price)) return 'N/A';
                 return new Intl.NumberFormat('tr-TR').format(price);
            }

            // Kategori adını döndüren fonksiyon
            function getCategoryName(category) {
                const categories = {
                    'House': 'Müstakil Ev',
                    'Apartment': 'Daire',
                    'Commercial': 'Ticari',
                    'Land': 'Arsa',
                    'Other': 'Diğer'
                };
                return categories[category] || 'Belirtilmemiş';
            }            // Marker stilini ve içeriğini hazırla
            let markerBgColor, markerTextColor, markerText;
            const rentPrice = <?= $listing['rent_price'] ?? 'null' ?>;
            const salePrice = <?= $listing['sale_price'] ?? 'null' ?>;
            
            // İlan tipine göre marker rengini ve metni belirle
            if (rentPrice && rentPrice > 0) {
                markerBgColor = '#35addc'; // Mavi
                markerTextColor = '#ffffff'; // Beyaz
                markerText = `${formatPrice(rentPrice)} ₺/ay`;
            } else if (salePrice && salePrice > 0) {
                markerBgColor = '#ffb400'; // Sarı/Turuncu
                markerTextColor = '#333333'; // Koyu gri
                markerText = `${formatPrice(salePrice)} ₺`;
            } else {
                markerBgColor = '#cccccc'; // Gri
                markerTextColor = '#333333';
                markerText = 'Belirtilmemiş';
            }

            // Modern popup içeriği için fiyat hazırlama
            let formattedPopupSalePrice = salePrice ? formatPrice(salePrice) : null;
            let formattedPopupRentPrice = rentPrice ? formatPrice(rentPrice) : null;
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
                `;            }

            // Özel marker ikonu (yeni tasarım)
            const propertyIcon = L.divIcon({
                className: 'property-marker',
                html: `
                    <div class="marker-container">
                        <div class="marker-house-icon <?= $listing['featured'] ? 'featured' : '' ?>">
                            <i class="bi bi-house-fill"></i>
                        </div>
                        <div class="marker-price-label <?= $listing['featured'] ? 'featured' : '' ?>">${markerText}</div>
                    </div>
                `,
                iconSize: [120, 60],
                iconAnchor: [60, 60],
                popupAnchor: [0, -60]
            });// Ekran görüntüsüne benzer popup tasarımı
            const popupContent = `
                <div class="custom-property-popup">
                    <div class="popup-image-top">
                        <img src="<?= htmlspecialchars($listing['main_image'] ?? 'assets/img/property-placeholder.jpg') ?>"
                             alt="<?= htmlspecialchars($listing['title'] ?? 'İlan görseli') ?>"
                             onerror="this.onerror=null; this.src='assets/img/property-placeholder.jpg';"
                             loading="lazy">
                        <?= $listing['featured'] ? '<div class="popup-featured-badge"><i class="bi bi-star-fill"></i> Öne Çıkan</div>' : '' ?>
                    </div>
                    
                    <div class="popup-content">
                        <h3 class="popup-title"><?= htmlspecialchars($listing['title'] ?? 'İlan Detayı') ?></h3>
                        
                        <div class="popup-location">
                            <i class="bi bi-geo-alt"></i>
                            <span><?= htmlspecialchars(($listing['district'] ? $listing['district'] . ', ' : '') . ($listing['city'] ?? 'Konum belirtilmemiş')) ?></span>
                        </div>
                        
                        ${formattedPopupSalePrice || formattedPopupRentPrice ? `
                            <div class="popup-price-container">
                                ${formattedPopupSalePrice ? `
                                    <div class="popup-price-box sale">
                                        <span class="popup-price-label">SATILIK</span>
                                        <span class="popup-price-value">${formattedPopupSalePrice} ₺</span>
                                    </div>
                                ` : ''}
                                
                                ${formattedPopupRentPrice ? `
                                    <div class="popup-price-box rent">
                                        <span class="popup-price-label">KİRALIK</span>
                                        <span class="popup-price-value">${formattedPopupRentPrice} ₺/ay</span>
                                    </div>
                                ` : ''}
                            </div>
                        ` : `<div class="popup-price-container"><div class="popup-price-box">Fiyat Belirtilmemiş</div></div>`}
                        
                        <div class="popup-category">
                            <i class="bi bi-building"></i> 
                            <span>Kategori: ${getCategoryName('<?= $listing['category'] ?? '' ?>')}</span>
                        </div>
                        
                        <a href="listing.php?id=<?= $listing['id'] ?>" class="popup-details-button">
                            <span>Bu Sayfayı Yenile</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            `;

            // Marker ekle
            L.marker([lat, lng], {
                icon: propertyIcon
            }).addTo(map)
              .bindPopup(popupContent, {
                  maxWidth: 300,
                  minWidth: 300,
                  className: 'custom-popup-container',
                  closeButton: true,
                  autoPan: true
              })
              .openPopup(); // Başlangıçta popup açık olsun

             // Harita yüklendiğinde boyutu düzelt
            setTimeout(function() {
                map.invalidateSize();
             }, 100);

         } catch (error) {
             console.error("Harita başlatılırken hata:", error);
             mapContainer.innerHTML = '<p class="text-danger text-center p-3">Harita yüklenemedi.</p>';
         }
    }


    // Galeri küçük resimlerine tıklama (Bootstrap 5 Carousel API)
    document.querySelectorAll('.thumbnail-img').forEach(function(img) {
        img.addEventListener('click', function() {
            const slideIndex = parseInt(this.getAttribute('data-bs-slide-to'));
            const carouselElement = document.getElementById('propertyCarousel');
            if (carouselElement) {
                const carouselInstance = bootstrap.Carousel.getOrCreateInstance(carouselElement);
                carouselInstance.to(slideIndex);
            }
        });
    });
});
</script>
<?php require_once 'templates/footer.php'; ?>