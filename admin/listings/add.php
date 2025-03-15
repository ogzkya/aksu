<?php
require_once '../../includes/init.php';

$auth = new Auth();
$auth->requireLogin();

$listing = new Listing();
$image = new Image();
$errors = [];
$success = false;

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Form verilerini al
        $listingData = [
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'short_description' => $_POST['short_description'] ?? '',
            'sale_price' => $_POST['sale_price'] ?? 0,
            'rent_price' => !empty($_POST['rent_price']) ? $_POST['rent_price'] : null,
            'property_size' => $_POST['property_size'] ?? 0,
            'rooms' => $_POST['rooms'] ?? 0,
            'bathrooms' => $_POST['bathrooms'] ?? 0,
            'floors_no' => $_POST['floors_no'] ?? 0,
            'garages' => $_POST['garages'] ?? 0,
            'energy_efficiency' => $_POST['energy_efficiency'] ?? null,
            'year_built' => !empty($_POST['year_built']) ? $_POST['year_built'] : null,
            'property_lot_size' => !empty($_POST['property_lot_size']) ? $_POST['property_lot_size'] : null,
            'category' => $_POST['category'] ?? 'Other',
            'latitude' => $_POST['latitude'] ?? 0,
            'longitude' => $_POST['longitude'] ?? 0,
            'city' => $_POST['city'] ?? '',
            'state' => $_POST['state'] ?? '',
            'country' => $_POST['country'] ?? '',
            'street' => $_POST['street'] ?? '',
            'zip' => $_POST['zip'] ?? '',
            'keywords' => $_POST['keywords'] ?? '',
            'featured' => isset($_POST['featured']) ? 1 : 0,
            'multimedia' => [
                'video_url' => $_POST['video_url'] ?? '',
                'virtual_tour' => $_POST['virtual_tour'] ?? ''
            ],
            'distances' => [],
            'features' => [
                'İç Özellikler' => [],
                'Dış Özellikler' => [],
                'Çevre Özellikleri' => []
            ]
        ];
        
        // Mesafeleri işle
        $distanceNames = $_POST['distance_name'] ?? [];
        $distanceValues = $_POST['distance_value'] ?? [];
        
        for ($i = 0; $i < count($distanceNames); $i++) {
            if (!empty($distanceNames[$i]) && isset($distanceValues[$i])) {
                $listingData['distances'][$distanceNames[$i]] = $distanceValues[$i];
            }
        }
        
        // Özellikleri işle
        if (isset($_POST['interior_features'])) {
            $listingData['features']['İç Özellikler'] = $_POST['interior_features'];
        }
        
        if (isset($_POST['exterior_features'])) {
            $listingData['features']['Dış Özellikler'] = $_POST['exterior_features'];
        }
        
        if (isset($_POST['env_features'])) {
            $listingData['features']['Çevre Özellikleri'] = $_POST['env_features'];
        }
        
        // Minimal doğrulama
        if (empty($listingData['title'])) {
            $errors[] = 'İlan başlığı gereklidir.';
        }
        
        if (empty($listingData['description'])) {
            $errors[] = 'İlan açıklaması gereklidir.';
        }
        
        // İlana göre fiyat kontrolü
        if (isset($_POST['listing_type'])) {
            if ($_POST['listing_type'] == 'sale' || $_POST['listing_type'] == 'both') {
                if (!is_numeric($listingData['sale_price']) || $listingData['sale_price'] <= 0) {
                    $errors[] = 'Geçerli bir satış fiyatı girilmelidir.';
                }
            } else {
                $listingData['sale_price'] = 0;
            }
            
            if ($_POST['listing_type'] == 'rent' || $_POST['listing_type'] == 'both') {
                if (!is_numeric($listingData['rent_price']) || $listingData['rent_price'] <= 0) {
                    $errors[] = 'Geçerli bir kira fiyatı girilmelidir.';
                }
            } else {
                $listingData['rent_price'] = null;
            }
        }
        
        if (!is_numeric($listingData['property_size']) || $listingData['property_size'] <= 0) {
            $errors[] = 'Geçerli bir alan (m²) girilmelidir.';
        }
        
        if (empty($listingData['city']) || empty($listingData['country'])) {
            $errors[] = 'Şehir ve ülke bilgileri gereklidir.';
        }
        
        // Hata yoksa ilanı ekle
        if (empty($errors)) {
            $listingId = $listing->addListing($listingData);
            
            // Görselleri yükle
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                $mainImageIndex = $_POST['main_image_index'] ?? 0;
                
                for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
                    if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                        $file = [
                            'name' => $_FILES['images']['name'][$i],
                            'type' => $_FILES['images']['type'][$i],
                            'tmp_name' => $_FILES['images']['tmp_name'][$i],
                            'error' => $_FILES['images']['error'][$i],
                            'size' => $_FILES['images']['size'][$i]
                        ];
                        
                        $imageUrl = $image->upload($file, $listingId);
                        $listing->addListingImage($listingId, $imageUrl, $i == $mainImageIndex ? 1 : 0);
                    }
                }
            }
            
            $success = true;
            // Yönlendirme
            header('Location: edit.php?id=' . $listingId . '&success=1');
            exit;
        }
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

$pageTitle = "Yeni İlan Ekle";
$activePage = "listings";
require_once '../templates/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Yeni İlan Ekle</h1>
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> İlanlara Dön
        </a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            İlan başarıyla eklendi.
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <ul class="nav nav-tabs card-header-tabs" id="listingTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="basic-tab" data-bs-toggle="tab" href="#basic" role="tab">
                        <i class="bi bi-info-circle"></i> Temel Bilgiler
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="details-tab" data-bs-toggle="tab" href="#details" role="tab">
                        <i class="bi bi-list-ul"></i> Detaylar
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="location-tab" data-bs-toggle="tab" href="#location" role="tab">
                        <i class="bi bi-geo-alt"></i> Konum
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="media-tab" data-bs-toggle="tab" href="#media" role="tab">
                        <i class="bi bi-image"></i> Medya
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="features-tab" data-bs-toggle="tab" href="#features" role="tab">
                        <i class="bi bi-check2-square"></i> Özellikler
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <form action="add.php" method="post" enctype="multipart/form-data" id="listingForm">
                <div class="tab-content" id="listingTabsContent">
                    <!-- Temel Bilgiler -->
                    <div class="tab-pane fade show active" id="basic" role="tabpanel">
                        <h5 class="mb-3">İlan Bilgileri</h5>
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Başlık <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" required>
                            <div class="form-text">İlanınız için dikkat çekici bir başlık yazın</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Açıklama <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="description" name="description" rows="6" required></textarea>
                            <div class="form-text">Mülkü detaylı bir şekilde tanımlayın</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="short_description" class="form-label">Kısa Açıklama</label>
                            <textarea class="form-control" id="short_description" name="short_description" rows="2"></textarea>
                            <div class="form-text">Listeleme sayfalarında gösterilecek kısa açıklama</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="keywords" class="form-label">Anahtar Kelimeler</label>
                            <input type="text" class="form-control" id="keywords" name="keywords">
                            <div class="form-text">Virgülle ayırarak birden fazla anahtar kelime girebilirsiniz</div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">Kategori <span class="text-danger">*</span></label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="House">Müstakil Ev</option>
                                    <option value="Apartment">Daire</option>
                                    <option value="Commercial">Ticari</option>
                                    <option value="Land">Arsa</option>
                                    <option value="Other">Diğer</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="featured" class="form-label d-block">Öne Çıkar</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="featured" name="featured">
                                    <label class="form-check-label" for="featured">Bu ilanı ana sayfada öne çıkar</label>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        <h5 class="mb-3">Fiyat Bilgileri</h5>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">İlan Tipi <span class="text-danger">*</span></label>
                                <div class="d-flex">
                                    <div class="form-check me-3">
                                        <input class="form-check-input" type="radio" name="listing_type" id="type_sale" value="sale" checked>
                                        <label class="form-check-label" for="type_sale">Satılık</label>
                                    </div>
                                    <div class="form-check me-3">
                                        <input class="form-check-input" type="radio" name="listing_type" id="type_rent" value="rent">
                                        <label class="form-check-label" for="type_rent">Kiralık</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="listing_type" id="type_both" value="both">
                                        <label class="form-check-label" for="type_both">Hem Satılık Hem Kiralık</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3" id="sale_price_container">
                                <label for="sale_price" class="form-label">Satış Fiyatı (₺) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="sale_price" name="sale_price" min="0" step="1000">
                                    <span class="input-group-text">₺</span>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3" id="rent_price_container" style="display: none;">
                                <label for="rent_price" class="form-label">Kira Fiyatı (₺/ay) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="rent_price" name="rent_price" min="0" step="100">
                                    <span class="input-group-text">₺/ay</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="button" class="btn btn-primary next-tab" data-next="details-tab">
                                İleri <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Detay Bilgileri -->
                    <div class="tab-pane fade" id="details" role="tabpanel">
                        <h5 class="mb-3">Gayrimenkul Özellikleri</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="property_size" class="form-label">Alan (m²) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="property_size" name="property_size" min="1" step="0.01" required>
                                    <span class="input-group-text">m²</span>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="property_lot_size" class="form-label">Arsa Alanı (m²)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="property_lot_size" name="property_lot_size" min="0" step="0.01">
                                    <span class="input-group-text">m²</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="rooms" class="form-label">Oda Sayısı <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="rooms" name="rooms" min="0" required>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="bathrooms" class="form-label">Banyo Sayısı <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="bathrooms" name="bathrooms" min="0" required>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="floors_no" class="form-label">Kat Sayısı <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="floors_no" name="floors_no" min="1" required>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="garages" class="form-label">Garaj Sayısı</label>
                                <input type="number" class="form-control" id="garages" name="garages" min="0">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="year_built" class="form-label">Yapım Yılı</label>
                                <input type="number" class="form-control" id="year_built" name="year_built" min="1900" max="<?= date('Y') ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="energy_efficiency" class="form-label">Enerji Verimliliği</label>
                                <select class="form-select" id="energy_efficiency" name="energy_efficiency">
                                    <option value="">Seçiniz</option>
                                    <option value="A">A Sınıfı</option>
                                    <option value="B">B Sınıfı</option>
                                    <option value="C">C Sınıfı</option>
                                    <option value="D">D Sınıfı</option>
                                    <option value="E">E Sınıfı</option>
                                    <option value="F">F Sınıfı</option>
                                    <option value="G">G Sınıfı</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="button" class="btn btn-secondary prev-tab" data-prev="basic-tab">
                                <i class="bi bi-arrow-left"></i> Geri
                            </button>
                            <button type="button" class="btn btn-primary next-tab" data-next="location-tab">
                                İleri <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Konum Bilgileri -->
                    <div class="tab-pane fade" id="location" role="tabpanel">
                        <h5 class="mb-3">Adres Bilgileri</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="street" class="form-label">Sokak/Cadde <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="street" name="street" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="zip" class="form-label">Posta Kodu <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="zip" name="zip" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="city" class="form-label">Şehir <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="city" name="city" required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="state" class="form-label">İl/Bölge <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="state" name="state" required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="country" class="form-label">Ülke <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="country" name="country" value="Türkiye" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Konum (Haritada İşaretleyin) <span class="text-danger">*</span></label>
                            <div id="map-container" style="height: 400px; border-radius: 5px;"></div>
                            <div class="form-text mt-2">Haritada mülkün konumunu işaretlemek için tıklayın. Bu konumu daha sonra düzenleyebilirsiniz.</div>
                            <input type="hidden" id="latitude" name="latitude" required>
                            <input type="hidden" id="longitude" name="longitude" required>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="button" class="btn btn-secondary prev-tab" data-prev="details-tab">
                                <i class="bi bi-arrow-left"></i> Geri
                            </button>
                            <button type="button" class="btn btn-primary next-tab" data-next="media-tab">
                                İleri <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Medya -->
                    <div class="tab-pane fade" id="media" role="tabpanel">
                        <h5 class="mb-3">Görsel Galerisi</h5>
                        
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="images" class="form-label">Görseller (maksimum 25 adet)</label>
                                    <div id="drag-drop-area" class="p-5 text-center border border-dashed rounded mb-3">
                                        <i class="bi bi-cloud-arrow-up fs-2 mb-2"></i>
                                        <p class="mb-2">Dosyaları sürükleyip bırakın veya seçmek için tıklayın</p>
                                        <p class="small text-muted mb-2">Kabul edilen formatlar: JPG, JPEG, PNG</p>
                                        <input type="file" class="form-control" id="images" name="images[]" accept="image/jpeg,image/png,image/jpg" multiple style="display: none;">
                                        <button type="button" class="btn btn-outline-primary" id="select-files-btn">Dosya Seç</button>
                                    </div>
                                </div>
                                
                                <div id="image-previews" class="d-flex flex-wrap gap-3"></div>
                                
                                <div class="mt-3" id="main-image-container" style="display: none;">
                                    <label for="main-image-select" class="form-label">Ana Görsel</label>
                                    <select class="form-select" id="main-image-select" name="main_image_index">
                                        <option value="0">İlk yüklenen görsel</option>
                                    </select>
                                    <div class="form-text">Ana görsel, listeleme sayfalarında ve haritada gösterilir</div>
                                </div>
                            </div>
                        </div>
                        
                        <h5 class="mb-3 mt-4">Multimedya</h5>
                        
                        <div class="mb-3">
                            <label for="video_url" class="form-label">Video URL (Youtube/Vimeo)</label>
                            <input type="url" class="form-control" id="video_url" name="video_url">
                            <div class="form-text">YouTube veya Vimeo video bağlantısı ekleyebilirsiniz</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="virtual_tour" class="form-label">Sanal Tur URL</label>
                            <input type="url" class="form-control" id="virtual_tour" name="virtual_tour">
                            <div class="form-text">360° sanal tur bağlantısı ekleyebilirsiniz</div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="button" class="btn btn-secondary prev-tab" data-prev="location-tab">
                                <i class="bi bi-arrow-left"></i> Geri
                            </button>
                            <button type="button" class="btn btn-primary next-tab" data-next="features-tab">
                                İleri <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Özellikler -->
                    <div class="tab-pane fade" id="features" role="tabpanel">
                        <h5 class="mb-3">Özellikler</h5>
                        
                        <div class="mb-4">
                            <label class="form-label d-block">İç Özellikler</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check feature-checkbox mb-2">
                                        <input class="form-check-input" type="checkbox" name="interior_features[]" value="Klima" id="feature_klima">
                                        <label class="form-check-label" for="feature_klima">Klima</label>
                                    </div>
                                    <div class="form-check feature-checkbox mb-2">
                                        <input class="form-check-input" type="checkbox" name="interior_features[]" value="Merkezi Isıtma" id="feature_isitma">
                                        <label class="form-check-label" for="feature_isitma">Merkezi Isıtma</label>
                                    </div>
                                    <div class="form-check feature-checkbox mb-2">
                                        <input class="form-check-input" type="checkbox" name="interior_features[]" value="Ankastre Mutfak" id="feature_ankastre">
                                        <label class="form-check-label" for="feature_ankastre">Ankastre Mutfak</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check feature-checkbox mb-2">
                                        <input class="form-check-input" type="checkbox" name="interior_features[]" value="Akıllı Ev Sistemi" id="feature_akilli_ev">
                                        <label class="form-check-label" for="feature_akilli_ev">Akıllı Ev Sistemi</label>
                                    </div>
                                    <div class="form-check feature-checkbox mb-2">
                                        <input class="form-check-input" type="checkbox" name="interior_features[]" value="Güvenlik Sistemi" id="feature_guvenlik">
                                        <label class="form-check-label" for="feature_guvenlik">Güvenlik Sistemi</label>
                                    </div>
                                    <div class="form-check feature-checkbox mb-2">
                                        <input class="form-check-input" type="checkbox" name="interior_features[]" value="İnternet Bağlantısı" id="feature_internet">
                                        <label class="form-check-label" for="feature_internet">İnternet Bağlantısı</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check feature-checkbox mb-2">
                                        <input class="form-check-input" type="checkbox" name="interior_features[]" value="Ebeveyn Banyosu" id="feature_ebeveyn">
                                        <label class="form-check-label" for="feature_ebeveyn">Ebeveyn Banyosu</label>
                                    </div>
                                    <div class="form-check feature-checkbox mb-2">
                                        <input class="form-check-input" type="checkbox" name="interior_features[]" value="Giyinme Odası" id="feature_giyinme">
                                        <label class="form-check-label" for="feature_giyinme">Giyinme Odası</label>
                                    </div>
                                    <div class="form-check feature-checkbox mb-2">
                                        <input class="form-check-input" type="checkbox" name="interior_features[]" value="Beyaz Eşya" id="feature_beyaz_esya">
                                        <label class="form-check-label" for="feature_beyaz_esya">Beyaz Eşya</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label d-block">Dış Özellikler</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check feature-checkbox mb-2">
                                        <input class="form-check-input" type="checkbox" name="exterior_features[]" value="Bahçe" id="feature_bahce">
                                        <label class="form-check-label" for="feature_bahce">Bahçe</label>
                                    </div>
                                    <div class="form-check feature-checkbox mb-2">
                                        <input class="form-check-input" type="checkbox" name="exterior_features[]" value="Havuz" id="feature_havuz">
                                        <label class="form-check-label" for="feature_havuz">Havuz</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check feature-checkbox mb-2">
                                        <input class="form-check-input" type="checkbox" name="exterior_features[]" value="Otopark" id="feature_otopark">
                                        <label class="form-check-label" for="feature_otopark">Otopark</label>
                                    </div>
                                    <div class="form-check feature-checkbox mb-2">
                                        <input class="form-check-input" type="checkbox" name="exterior_features[]" value="Teras" id="feature_teras">
                                        <label class="form-check-label" for="feature_teras">Teras</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check feature-checkbox mb-2">
                                        <input class="form-check-input" type="checkbox" name="exterior_features[]" value="Güvenlik" id="feature_guvenlik_dis">
                                        <label class="form-check-label" for="feature_guvenlik_dis">Güvenlik</label>
                                    </div>
                                    <div class="form-check feature-checkbox mb-2">
                                        <input class="form-check-input" type="checkbox" name="exterior_features[]" value="Asansör" id="feature_asansor">
                                        <label class="form-check-label" for="feature_asansor">Asansör</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label d-block">Çevre Özellikleri</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check feature-checkbox mb-2">
                                        <input class="form-check-input" type="checkbox" name="env_features[]" value="Okula Yakın" id="feature_okul">
                                        <label class="form-check-label" for="feature_okul">Okula Yakın</label>
                                    </div>
                                    <div class="form-check feature-checkbox mb-2">
                                        <input class="form-check-input" type="checkbox" name="env_features[]" value="Toplu Taşımaya Yakın" id="feature_toplu_tasima">
                                        <label class="form-check-label" for="feature_toplu_tasima">Toplu Taşımaya Yakın</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check feature-checkbox mb-2">
                                        <input class="form-check-input" type="checkbox" name="env_features[]" value="Markete Yakın" id="feature_market">
                                        <label class="form-check-label" for="feature_market">Markete Yakın</label>
                                    </div>
                                    <div class="form-check feature-checkbox mb-2">
                                        <input class="form-check-input" type="checkbox" name="env_features[]" value="Hastaneye Yakın" id="feature_hastane">
                                        <label class="form-check-label" for="feature_hastane">Hastaneye Yakın</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check feature-checkbox mb-2">
                                        <input class="form-check-input" type="checkbox" name="env_features[]" value="Denize Yakın" id="feature_deniz">
                                        <label class="form-check-label" for="feature_deniz">Denize Yakın</label>
                                    </div>
                                    <div class="form-check feature-checkbox mb-2">
                                        <input class="form-check-input" type="checkbox" name="env_features[]" value="Şehir Merkezine Yakın" id="feature_merkez">
                                        <label class="form-check-label" for="feature_merkez">Şehir Merkezine Yakın</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <h5 class="mb-3 mt-4">Yakın Çevre Mesafeleri</h5>
                        
                        <div id="distances-container">
                            <div class="distance-row row mb-3">
                                <div class="col-md-5">
                                    <input type="text" class="form-control" name="distance_name[]" placeholder="Mekan Adı (örn. Metro)">
                                </div>
                                <div class="col-md-5">
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="distance_value[]" placeholder="Mesafe" step="0.1" min="0">
                                        <span class="input-group-text">km</span>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger w-100 remove-distance">Sil</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <button type="button" class="btn btn-success btn-sm" id="add-distance">
                                <i class="bi bi-plus-circle"></i> Yeni Mesafe Ekle
                            </button>
                        </div>

                        <div class="mt-4 d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary prev-tab" data-prev="media-tab">
                                <i class="bi bi-arrow-left"></i> Geri
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> İlanı Kaydet
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab Geçişleri
        document.querySelectorAll('.next-tab').forEach(button => {
            button.addEventListener('click', function() {
                const nextTabId = this.getAttribute('data-next');
                const nextTab = document.getElementById(nextTabId);
                
                if (nextTab) {
                    const bsTab = new bootstrap.Tab(nextTab);
                    bsTab.show();
                }
            });
        });
        
        document.querySelectorAll('.prev-tab').forEach(button => {
            button.addEventListener('click', function() {
                const prevTabId = this.getAttribute('data-prev');
                const prevTab = document.getElementById(prevTabId);
                
                if (prevTab) {
                    const bsTab = new bootstrap.Tab(prevTab);
                    bsTab.show();
                }
            });
        });
        
        // İlan Tipi Değişimi
        const typeRadios = document.querySelectorAll('input[name="listing_type"]');
        const salePriceContainer = document.getElementById('sale_price_container');
        const rentPriceContainer = document.getElementById('rent_price_container');
        const salePriceInput = document.getElementById('sale_price');
        const rentPriceInput = document.getElementById('rent_price');
        
        typeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'sale') {
                    salePriceContainer.style.display = 'block';
                    rentPriceContainer.style.display = 'none';
                    salePriceInput.required = true;
                    rentPriceInput.required = false;
                } 
                else if (this.value === 'rent') {
                    salePriceContainer.style.display = 'none';
                    rentPriceContainer.style.display = 'block';
                    salePriceInput.required = false;
                    rentPriceInput.required = true;
                }
                else {
                    salePriceContainer.style.display = 'block';
                    rentPriceContainer.style.display = 'block';
                    salePriceInput.required = true;
                    rentPriceInput.required = true;
                }
            });
        });
        
        // Görsel Önizleme
        const imageInput = document.getElementById('images');
        const imagePreviews = document.getElementById('image-previews');
        const mainImageSelect = document.getElementById('main-image-select');
        const mainImageContainer = document.getElementById('main-image-container');
        const selectFilesBtn = document.getElementById('select-files-btn');
        const dragDropArea = document.getElementById('drag-drop-area');
        
        selectFilesBtn.addEventListener('click', function() {
            imageInput.click();
        });
        
        // Sürükle bırak
        dragDropArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            dragDropArea.classList.add('bg-light');
        });
        
        dragDropArea.addEventListener('dragleave', function() {
            dragDropArea.classList.remove('bg-light');
        });
        
        dragDropArea.addEventListener('drop', function(e) {
            e.preventDefault();
            dragDropArea.classList.remove('bg-light');
            
            if (e.dataTransfer.files.length) {
                imageInput.files = e.dataTransfer.files;
                const event = new Event('change');
                imageInput.dispatchEvent(event);
            }
        });
        
        imageInput.addEventListener('change', function() {
            imagePreviews.innerHTML = '';
            mainImageSelect.innerHTML = '';
            
            if (this.files.length > 25) {
                alert('En fazla 25 görsel yükleyebilirsiniz.');
                this.value = '';
                return;
            }
            
            if (this.files.length > 0) {
                mainImageContainer.style.display = 'block';
                
                for (let i = 0; i < this.files.length; i++) {
                    const file = this.files[i];
                    
                    // Görsel önizleme
                    const preview = document.createElement('div');
                    preview.className = 'image-preview position-relative';
                    preview.style.width = '150px';
                    preview.style.height = '100px';
                    preview.style.overflow = 'hidden';
                    preview.style.borderRadius = '5px';
                    preview.style.border = '1px solid #dee2e6';
                    
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    img.style.width = '100%';
                    img.style.height = '100%';
                    img.style.objectFit = 'cover';
                    
                    img.onload = function() {
                        URL.revokeObjectURL(this.src);
                    }
                    
                    const filenameBadge = document.createElement('div');
                    filenameBadge.className = 'position-absolute bottom-0 start-0 end-0 bg-dark bg-opacity-75 text-white p-1 small text-truncate';
                    filenameBadge.textContent = file.name;
                    
                    preview.appendChild(img);
                    preview.appendChild(filenameBadge);
                    imagePreviews.appendChild(preview);
                    
                    // Ana görsel seçeneği ekle
                    const option = document.createElement('option');
                    option.value = i;
                    option.textContent = `Görsel ${i + 1}: ${file.name}`;
                    mainImageSelect.appendChild(option);
                }
            } else {
                mainImageContainer.style.display = 'none';
            }
        });
        
        // Mesafe ekleme/silme
        const distancesContainer = document.getElementById('distances-container');
        
        document.getElementById('add-distance').addEventListener('click', function() {
            const newRow = document.createElement('div');
            newRow.className = 'distance-row row mb-3';
            newRow.innerHTML = `
                <div class="col-md-5">
                    <input type="text" class="form-control" name="distance_name[]" placeholder="Mekan Adı (örn. Metro)">
                </div>
                <div class="col-md-5">
                    <div class="input-group">
                        <input type="number" class="form-control" name="distance_value[]" placeholder="Mesafe" step="0.1" min="0">
                        <span class="input-group-text">km</span>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger w-100 remove-distance">Sil</button>
                </div>
            `;
            
            distancesContainer.appendChild(newRow);
        });
        
        distancesContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-distance')) {
                const row = e.target.closest('.distance-row');
                
                if (distancesContainer.children.length > 1) {
                    row.remove();
                } else {
                    // İlk satırı temizle
                    row.querySelectorAll('input').forEach(input => input.value = '');
                }
            }
        });
        
        // Leaflet harita
        const map = L.map('map-container').setView([39.1, 35.6], 6);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 18
        }).addTo(map);
        
        let marker;
        
        map.on('click', function(e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
            
            if (marker) {
                marker.setLatLng(e.latlng);
            } else {
                marker = L.marker(e.latlng).addTo(map);
            }
        });
    });
</script>

<?php require_once '../templates/footer.php'; ?>