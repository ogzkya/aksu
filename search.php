<?php
require_once 'includes/init.php';

// Arama parametreleri (listing_type eklenmiş)
$filters = [
    'listing_type' => $_GET['listing_type'] ?? '',
    'city'         => $_GET['city'] ?? '',
    'category'     => $_GET['category'] ?? '',
    'min_price'    => $_GET['min_price'] ?? '',
    'max_price'    => $_GET['max_price'] ?? '',
    'min_rent'     => $_GET['min_rent'] ?? '',
    'max_rent'     => $_GET['max_rent'] ?? '',
    'rooms'        => $_GET['rooms'] ?? ''
];

$page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;
$offset  = ($page - 1) * $perPage;

$listing       = new Listing();
$listings      = $listing->getAllListings($perPage, $offset, $filters);
$totalListings = $listing->countListings($filters);
$totalPages    = ceil($totalListings / $perPage);

// Harita verileri
$mapData   = $listing->getMapData();
$mapDataJson = json_encode($mapData);

$pageTitle = "Arama Sonuçları";
require_once 'templates/header.php';
?>

<div class="container-fluid mt-4 mb-5 px-lg-4">
    <div class="row">
        <!-- Sol Kolon (Filtreler) -->
        <div class="col-lg-3 mb-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Arama Filtreleri</h5>
                </div>
                <!-- Güncellenmiş filtre formu -->
                <div class="card-body">
                    <form action="search.php" method="get" class="row g-3">
                        <!-- İlan Tipi Seçimi -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">İlan Tipi</label>
                            <div class="d-flex">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="listing_type" id="type_all" value="" <?= !isset($_GET['listing_type']) || $_GET['listing_type'] === '' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="type_all">Tümü</label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="listing_type" id="type_sale" value="sale" <?= isset($_GET['listing_type']) && $_GET['listing_type'] === 'sale' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="type_sale">Satılık</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="listing_type" id="type_rent" value="rent" <?= isset($_GET['listing_type']) && $_GET['listing_type'] === 'rent' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="type_rent">Kiralık</label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Diğer filtreler -->
                        <div class="col-md-10">
                            <input type="text" name="city" class="form-control" placeholder="İlan başlığı veya adresi ara..." value="<?= htmlspecialchars($_GET['city'] ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Ara</button>
                        </div>
                        
                        <!-- Kategori ve Oda Sayısı Filtreleri -->
                        <div class="col-md-6 col-lg-4">
                            <label for="category" class="form-label">Kategori</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">Tümü</option>
                                <option value="House" <?= isset($_GET['category']) && $_GET['category'] == 'House' ? 'selected' : '' ?>>Müstakil Ev</option>
                                <option value="Apartment" <?= isset($_GET['category']) && $_GET['category'] == 'Apartment' ? 'selected' : '' ?>>Daire</option>
                                <option value="Commercial" <?= isset($_GET['category']) && $_GET['category'] == 'Commercial' ? 'selected' : '' ?>>Ticari</option>
                                <option value="Land" <?= isset($_GET['category']) && $_GET['category'] == 'Land' ? 'selected' : '' ?>>Arsa</option>
                                <option value="Other" <?= isset($_GET['category']) && $_GET['category'] == 'Other' ? 'selected' : '' ?>>Diğer</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 col-lg-4">
                            <label for="rooms" class="form-label">Oda Sayısı</label>
                            <select class="form-select" id="rooms" name="rooms">
                                <option value="">Tümü</option>
                                <option value="1" <?= isset($_GET['rooms']) && $_GET['rooms'] == '1' ? 'selected' : '' ?>>1</option>
                                <option value="2" <?= isset($_GET['rooms']) && $_GET['rooms'] == '2' ? 'selected' : '' ?>>2</option>
                                <option value="3" <?= isset($_GET['rooms']) && $_GET['rooms'] == '3' ? 'selected' : '' ?>>3</option>
                                <option value="4" <?= isset($_GET['rooms']) && $_GET['rooms'] == '4' ? 'selected' : '' ?>>4</option>
                                <option value="5" <?= isset($_GET['rooms']) && $_GET['rooms'] == '5' ? 'selected' : '' ?>>5+</option>
                            </select>
                        </div>
                        
                        <!-- Fiyat Filtreleri -->
                        <div class="col-md-6 col-lg-4" id="sale-price-container" <?= isset($_GET['listing_type']) && $_GET['listing_type'] === 'rent' ? 'style="display:none;"' : '' ?>>
                            <label class="form-label">Satış Fiyatı (₺)</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="number" class="form-control" name="min_price" placeholder="Min" value="<?= htmlspecialchars($_GET['min_price'] ?? '') ?>">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control" name="max_price" placeholder="Max" value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 col-lg-4" id="rent-price-container" <?= isset($_GET['listing_type']) && $_GET['listing_type'] === 'rent' ? '' : 'style="display:none;"' ?>>
                            <label class="form-label">Kira Fiyatı (₺/ay)</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="number" class="form-control" name="min_rent" placeholder="Min" value="<?= htmlspecialchars($_GET['min_rent'] ?? '') ?>">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control" name="max_rent" placeholder="Max" value="<?= htmlspecialchars($_GET['max_rent'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Sağ Kolon (Sonuçlar) -->
        <div class="col-lg-9">
            <!-- Harita Görünümü -->
            <div class="mb-4">
                <div id="search-map" style="height: 400px;"></div>
            </div>
            
            <!-- Sonuç Bilgisi -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="h5 mb-0">
                    <?= $totalListings ?> ilan bulundu
                    <?php if ($filters['city']) echo ' - "' . htmlspecialchars($filters['city']) . '"'; ?>
                </h3>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary active" id="grid-view-btn">
                        <i class="bi bi-grid"></i>
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="list-view-btn">
                        <i class="bi bi-list"></i>
                    </button>
                </div>
            </div>
            
            <!-- Sonuç Listesi -->
            <?php if (count($listings) > 0): ?>
                <div class="row" id="grid-view">
                    <?php foreach ($listings as $item): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card property-card h-100">
                                <div class="position-relative">
                                    <img src="<?= htmlspecialchars($item['main_image'] ?? 'assets/img/property-placeholder.jpg') ?>" 
                                         class="card-img-top" 
                                         alt="<?= htmlspecialchars($item['title']) ?>"
                                         loading="lazy">
                                    <?php if ($item['rent_price'] && $item['rent_price'] > 0): ?>
                                        <span class="badge bg-success position-absolute top-0 end-0 m-3 py-2 px-3">
                                            Kiralık
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger position-absolute top-0 end-0 m-3 py-2 px-3">
                                            Satılık
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($item['featured']): ?>
                                        <span class="badge bg-warning position-absolute top-0 start-0 m-3">
                                            Öne Çıkan
                                        </span>
                                    <?php elseif ($item['category']): ?>
                                        <span class="badge bg-primary position-absolute top-0 start-0 m-3">
                                            <?php
                                                $categories = [
                                                    'House'      => 'Müstakil Ev',
                                                    'Apartment'  => 'Daire',
                                                    'Commercial' => 'Ticari',
                                                    'Land'       => 'Arsa',
                                                    'Other'      => 'Diğer'
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
                                    <?php if ($item['rent_price'] && $item['rent_price'] > 0): ?>
                                        <p class="card-text text-primary fw-bold mb-3"><?= number_format($item['rent_price'], 0, ',', '.') ?> ₺/ay</p>
                                    <?php else: ?>
                                        <p class="card-text text-primary fw-bold mb-3"><?= number_format($item['sale_price'], 0, ',', '.') ?> ₺</p>
                                    <?php endif; ?>
                                    <div class="property-features d-flex justify-content-between text-center border-top pt-3">
                                        <div>
                                            <i class="bi bi-house-door"></i>
                                            <p class="small mb-0"><?= htmlspecialchars($item['property_size']) ?> m²</p>
                                        </div>
                                        <div>
                                            <i class="bi bi-door-closed"></i>
                                            <p class="small mb-0"><?= htmlspecialchars($item['rooms']) ?> Oda</p>
                                        </div>
                                        <div>
                                            <i class="bi bi-water"></i>
                                            <p class="small mb-0"><?= htmlspecialchars($item['bathrooms']) ?> Banyo</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-0">
                                    <a href="listing.php?id=<?= $item['id'] ?>" class="btn btn-outline-primary w-100">Detaylar</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="list-view d-none" id="list-view">
                    <?php foreach ($listings as $item): ?>
                        <div class="card mb-3">
                            <div class="row g-0">
                                <div class="col-md-4">
                                    <div class="position-relative h-100">
                                        <img src="<?= htmlspecialchars($item['main_image'] ?? 'assets/img/property-placeholder.jpg') ?>" 
                                             class="img-fluid rounded-start h-100 w-100 object-fit-cover" 
                                             alt="<?= htmlspecialchars($item['title']) ?>"
                                             loading="lazy">
                                        <?php if ($item['featured']): ?>
                                            <span class="badge bg-primary position-absolute top-0 end-0 m-2">
                                                Öne Çıkan
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="card-body h-100 d-flex flex-column">
                                        <h5 class="card-title">
                                            <a href="listing.php?id=<?= $item['id'] ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($item['title']) ?>
                                            </a>
                                        </h5>
                                        <?php if ($item['rent_price'] && $item['rent_price'] > 0): ?>
                                            <p class="card-text text-primary fw-bold"><?= number_format($item['rent_price'], 0, ',', '.') ?> ₺/ay</p>
                                        <?php else: ?>
                                            <p class="card-text text-primary fw-bold"><?= number_format($item['sale_price'], 0, ',', '.') ?> ₺</p>
                                        <?php endif; ?>
                                        <p class="card-text small">
                                            <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($item['city']) ?>, <?= htmlspecialchars($item['state']) ?>
                                        </p>
                                        <div class="property-features d-flex flex-wrap mt-2 mb-3">
                                            <div class="me-4">
                                                <i class="bi bi-house-door"></i>
                                                <span class="small"><?= htmlspecialchars($item['property_size']) ?> m²</span>
                                            </div>
                                            <div class="me-4">
                                                <i class="bi bi-door-closed"></i>
                                                <span class="small"><?= htmlspecialchars($item['rooms']) ?> Oda</span>
                                            </div>
                                            <div class="me-4">
                                                <i class="bi bi-water"></i>
                                                <span class="small"><?= htmlspecialchars($item['bathrooms']) ?> Banyo</span>
                                            </div>
                                        </div>
                                        <p class="card-text small text-muted mb-3">
                                            <?= htmlspecialchars(substr($item['short_description'] ?? $item['description'], 0, 150)) ?>...
                                        </p>
                                        <a href="listing.php?id=<?= $item['id'] ?>" class="btn btn-outline-primary mt-auto align-self-start">
                                            Detaylar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Sayfalama -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                        Önceki
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                        Sonraki
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="alert alert-info">
                    <p class="mb-0">Aramanıza uygun sonuç bulunamadı. Lütfen farklı arama kriterleri deneyin.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // İlan tipi değiştiğinde fiyat filtrelerini göster/gizle
    const typeRadios         = document.querySelectorAll('input[name="listing_type"]');
    const salePriceContainer   = document.getElementById('sale-price-container');
    const rentPriceContainer   = document.getElementById('rent-price-container');
    
    typeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'rent') {
                salePriceContainer.style.display = 'none';
                rentPriceContainer.style.display = 'block';
            } else {
                salePriceContainer.style.display = 'block';
                rentPriceContainer.style.display = 'none';
            }
        });
    });
    
    // Harita görünümü
    const map = L.map('search-map').setView([39.1, 35.6], 6);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 18
    }).addTo(map);
    
    // Fiyatı biçimlendiren yardımcı fonksiyon
    function formatPrice(price) {
        return new Intl.NumberFormat('tr-TR').format(price);
    }
    
    const propertyData = <?= $mapDataJson ?>;
    const markers = [];
    
    propertyData.forEach(function(property) {
        // Fiyat bilgisi ve renk ayarlama
        let priceText    = '';
        let markerColor  = '#e74c3c'; // Default: satılık için kırmızı
        
        if (property.rent_price && property.rent_price > 0) {
            priceText   = `${formatPrice(property.rent_price)} ₺/ay`;
            markerColor = '#2ecc71'; // Kiralık için yeşil
        } else {
            priceText   = `${formatPrice(property.sale_price)} ₺`;
        }
        
        // Marker ikon stilini güncelle
        const propertyIcon = L.divIcon({
            className: 'property-marker',
            html: `<div class="marker-price" style="background-color: ${markerColor};">${priceText}</div>`,
            iconSize: [80, 30],
            iconAnchor: [40, 15]
        });
        
        // Marker ekle
        const marker = L.marker([property.latitude, property.longitude], {
            icon: propertyIcon
        }).addTo(map);
        
        // Popup içeriği - kiralık/satılık durumunu da ekleyin
        const popupContent = `
            <div class="map-popup">
                <img src="${property.main_image || 'assets/img/property-placeholder.jpg'}" class="popup-image" alt="${property.title}">
                <h5 class="popup-title">${property.title}</h5>
                <p class="popup-price">${priceText}</p>
                <a href="listing.php?id=${property.id}" class="btn btn-sm btn-primary w-100">Detaylar</a>
            </div>
        `;
        
        marker.bindPopup(popupContent, {
            maxWidth: 250,
            className: 'property-popup'
        });
        markers.push(marker);
    });
    
    if (markers.length > 0) {
        const group = new L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.1));
    }
});
</script>

<?php require_once 'templates/footer.php'; ?>
