<?php
require_once '../../includes/init.php';

$auth = new Auth();
$auth->requireLogin();

$listing = new Listing();
$image = new Image();
$errors = [];
$success = false;

// Form gönderildi mi kontrol et
// admin/listings/add.php ve edit.php için çoklu fotoğraf yükleme düzeltmesi

/**
 * Bu kodu add.php ve edit.php dosyalarında form işleme kısmından önce yerleştirin
 */

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Debug bilgisi - hata ayıklama için
        if (isset($_GET['debug'])) {
            echo "<pre>";
            echo "POST Verileri:\n";
            print_r($_POST);
            echo "\nFILES Verileri:\n";
            print_r($_FILES);
            echo "</pre>";
        }
        
        // Form verilerini al ve doğrula
        // İlan tipi ve fiyat kontrolü
        $listingType = $_POST['listing_type'] ?? 'sale';
        $salePrice = 0;
        $rentPrice = null;
        
        if ($listingType == 'sale' || $listingType == 'both') {
            $salePrice = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : 0;
            if ($salePrice <= 0) {
                $errors[] = 'Geçerli bir satış fiyatı girilmelidir.';
            }
        }
        
        if ($listingType == 'rent' || $listingType == 'both') {
            $rentPrice = !empty($_POST['rent_price']) ? (float)$_POST['rent_price'] : 0;
            if ($rentPrice <= 0) {
                $errors[] = 'Geçerli bir kira fiyatı girilmelidir.';
            }
        }
        
        // Görsel kontrolü - bu biraz daha karmaşık çünkü "images" ve "new_images" adlarını kullanabiliyoruz
        $hasImages = false;
        
        // add.php için (images[] adında olabilir)
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $hasImages = true;
        }
        
        // edit.php için (new_images[] adında olabilir)
        if (isset($_FILES['new_images']) && !empty($_FILES['new_images']['name'][0])) {
            $hasImages = true;
        }
        
        // Mevcut görseller de olabilir (edit.php için)
        if (isset($_POST['existing_images']) && !empty($_POST['existing_images'])) {
            $hasImages = true;
        }
        
        // Harita koordinatları kontrolü
        $latitude = $_POST['latitude'] ?? 0;
        $longitude = $_POST['longitude'] ?? 0;
        
        if (empty($latitude) || empty($longitude) || $latitude == 0 || $longitude == 0) {
            $errors[] = 'Lütfen haritada bir konum seçin.';
        }
        
        // Diğer form doğrulamaları...
        
        // Hata yoksa devam et
        if (empty($errors)) {
            // Form verilerini birleştir
            $listingData = [
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'short_description' => $_POST['short_description'] ?? '',
                'sale_price' => $salePrice,
                'rent_price' => $rentPrice,
                'property_size' => $_POST['property_size'] ?? 0,
                'rooms' => $_POST['rooms'] ?? 0,
                'bathrooms' => $_POST['bathrooms'] ?? 0,
                'floors_no' => $_POST['floors_no'] ?? 0,
                'garages' => $_POST['garages'] ?? 0,
                'energy_efficiency' => $_POST['energy_efficiency'] ?? null,
                'year_built' => !empty($_POST['year_built']) ? $_POST['year_built'] : null,
                'property_lot_size' => !empty($_POST['property_lot_size']) ? $_POST['property_lot_size'] : null,
                'category' => $_POST['category'] ?? 'Other',
                'latitude' => $latitude,
                'longitude' => $longitude,
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
            
            $listing = new Listing();
            
            // Edit sayfası için ilanı güncelle, add sayfası için yeni ilan ekle
            if (isset($listingId)) {
                // edit.php
                $listing->updateListing($listingId, $listingData);
            } else {
                // add.php
                $listingId = $listing->addListing($listingData);
                
                if (!$listingId) {
                    throw new Exception("İlan eklenirken bir hata oluştu.");
                }
            }
            
            // Görsel yükleme sınıfı
            $image = new Image();
            
            // Ana görsel indeksi
            $mainImageIndex = isset($_POST['main_image_index']) ? (int)$_POST['main_image_index'] : 0;
            
            // add.php için görselleri işle
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
                    if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                        $file = [
                            'name' => $_FILES['images']['name'][$i],
                            'type' => $_FILES['images']['type'][$i],
                            'tmp_name' => $_FILES['images']['tmp_name'][$i],
                            'error' => $_FILES['images']['error'][$i],
                            'size' => $_FILES['images']['size'][$i]
                        ];
                        
                        try {
                            $imageUrl = $image->upload($file, $listingId);
                            $listing->addListingImage($listingId, $imageUrl, $i == $mainImageIndex ? 1 : 0);
                        } catch (Exception $e) {
                            // Hata logla ama diğer görseller için devam et
                            error_log("Görsel yükleme hatası: " . $e->getMessage());
                        }
                    }
                }
            }
            
            // edit.php için yeni görselleri işle
            if (isset($_FILES['new_images']) && !empty($_FILES['new_images']['name'][0])) {
                for ($i = 0; $i < count($_FILES['new_images']['name']); $i++) {
                    if ($_FILES['new_images']['error'][$i] === UPLOAD_ERR_OK) {
                        $file = [
                            'name' => $_FILES['new_images']['name'][$i],
                            'type' => $_FILES['new_images']['type'][$i],
                            'tmp_name' => $_FILES['new_images']['tmp_name'][$i],
                            'error' => $_FILES['new_images']['error'][$i],
                            'size' => $_FILES['new_images']['size'][$i]
                        ];
                        
                        try {
                            $imageUrl = $image->upload($file, $listingId);
                            $listing->addListingImage($listingId, $imageUrl, $i == $mainImageIndex ? 1 : 0);
                        } catch (Exception $e) {
                            error_log("Görsel yükleme hatası: " . $e->getMessage());
                        }
                    }
                }
            }
            
            // edit.php için ana görsel güncelleme
            if (isset($_POST['main_image']) && is_numeric($_POST['main_image'])) {
                $mainImageId = (int)$_POST['main_image'];
                
                // Mevcut görsellerin içinden ana görseli bul
                if (isset($images) && is_array($images)) {
                    foreach ($images as $img) {
                        if ($img['id'] == $mainImageId) {
                            // Ana görsel olarak işaretle
                            $listing->addListingImage($listingId, $img['image_url'], 1);
                            break;
                        }
                    }
                }
            }
            
            // edit.php için silinen görselleri işle
            if (isset($_POST['deleted_images']) && !empty($_POST['deleted_images'])) {
                $deletedImages = explode(',', $_POST['deleted_images']);
                
                foreach ($deletedImages as $imgId) {
                    if (is_numeric($imgId)) {
                        $listing->deleteListingImage((int)$imgId);
                    }
                }
            }
            
            $success = true;
            
            // add.php için yönlendirme
            if (!isset($editMode)) {
                header('Location: edit.php?id=' . $listingId . '&success=1');
                exit;
            }
        }
    } catch (Exception $e) {
        $errors[] = "İşlem sırasında bir hata oluştu: " . $e->getMessage();
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

    <!-- Hata ve başarı mesajlarının gösterimi -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <strong><i class="bi bi-exclamation-triangle-fill me-2"></i>Lütfen aşağıdaki hataları düzeltin:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill me-2"></i>İlan başarıyla eklendi.
        </div>
    <?php endif; ?>

    <!-- Wizard Adım Göstergesi -->
    <div class="wizard-progress mb-4 d-none d-md-flex">
        <div class="wizard-progress-step active" data-step="basic-tab">
            <div class="wizard-progress-step-icon">
                <i class="bi bi-1-circle"></i>
            </div>
            <div class="wizard-progress-step-label">Temel Bilgiler</div>
        </div>
        <div class="wizard-progress-step" data-step="details-tab">
            <div class="wizard-progress-step-icon">
                <i class="bi bi-2-circle"></i>
            </div>
            <div class="wizard-progress-step-label">Detaylar</div>
        </div>
        <div class="wizard-progress-step" data-step="location-tab">
            <div class="wizard-progress-step-icon">
                <i class="bi bi-3-circle"></i>
            </div>
            <div class="wizard-progress-step-label">Konum</div>
        </div>
        <div class="wizard-progress-step" data-step="media-tab">
            <div class="wizard-progress-step-icon">
                <i class="bi bi-4-circle"></i>
            </div>
            <div class="wizard-progress-step-label">Medya</div>
        </div>
        <div class="wizard-progress-step" data-step="features-tab">
            <div class="wizard-progress-step-icon">
                <i class="bi bi-5-circle"></i>
            </div>
            <div class="wizard-progress-step-label">Özellikler</div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header">
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
            <form action="add.php" method="post" enctype="multipart/form-data" id="listingForm" class="admin-listing-form">
                <div class="tab-content" id="listingTabsContent">
                    <!-- Temel Bilgiler -->
                    <div class="tab-pane fade show active" id="basic" role="tabpanel">
                        <div class="p-lg-3">
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
                                    <div class="d-flex flex-wrap">
                                        <div class="form-check me-4 mb-2">
                                            <input class="form-check-input" type="radio" name="listing_type" id="type_sale" value="sale" checked>
                                            <label class="form-check-label" for="type_sale">Satılık</label>
                                        </div>
                                        <div class="form-check me-4 mb-2">
                                            <input class="form-check-input" type="radio" name="listing_type" id="type_rent" value="rent">
                                            <label class="form-check-label" for="type_rent">Kiralık</label>
                                        </div>
                                        <div class="form-check mb-2">
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
                    </div>
                    
                    <!-- Detay Bilgileri -->
                    <div class="tab-pane fade" id="details" role="tabpanel">
                        <div class="p-lg-3">
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
                                <div class="col-md-3 col-6 mb-3">
                                    <label for="rooms" class="form-label">Oda Sayısı <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="rooms" name="rooms" min="0" required>
                                </div>
                                
                                <div class="col-md-3 col-6 mb-3">
                                    <label for="bathrooms" class="form-label">Banyo Sayısı <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="bathrooms" name="bathrooms" min="0" required>
                                </div>
                                
                                <div class="col-md-3 col-6 mb-3">
                                    <label for="floors_no" class="form-label">Kat Sayısı <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="floors_no" name="floors_no" min="1" required>
                                </div>
                                
                                <div class="col-md-3 col-6 mb-3">
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
                    </div>
                    
                    <!-- Konum Bilgileri -->
                    <div class="tab-pane fade" id="location" role="tabpanel">
                        <div class="p-lg-3">
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
                    </div>
                    
                    <!-- Medya -->
                    <div class="tab-pane fade" id="media" role="tabpanel">
                        <div class="p-lg-3">
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
                    </div>
                    
                    <!-- Özellikler -->
                    <div class="tab-pane fade" id="features" role="tabpanel">
                        <div class="p-lg-3">
                            <h5 class="mb-3">Özellikler</h5>
                            
                            <div class="mb-4">
                                <label class="form-label d-block">İç Özellikler</label>
                                <div class="row">
                                    <div class="col-md-4 col-6">
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
                                    <div class="col-md-4 col-6">
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
                                    <div class="col-md-4 col-6">
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
                                    <div class="col-md-4 col-6">
                                        <div class="form-check feature-checkbox mb-2">
                                            <input class="form-check-input" type="checkbox" name="exterior_features[]" value="Bahçe" id="feature_bahce">
                                            <label class="form-check-label" for="feature_bahce">Bahçe</label>
                                        </div>
                                        <div class="form-check feature-checkbox mb-2">
                                            <input class="form-check-input" type="checkbox" name="exterior_features[]" value="Havuz" id="feature_havuz">
                                            <label class="form-check-label" for="feature_havuz">Havuz</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <div class="form-check feature-checkbox mb-2">
                                            <input class="form-check-input" type="checkbox" name="exterior_features[]" value="Otopark" id="feature_otopark">
                                            <label class="form-check-label" for="feature_otopark">Otopark</label>
                                        </div>
                                        <div class="form-check feature-checkbox mb-2">
                                            <input class="form-check-input" type="checkbox" name="exterior_features[]" value="Teras" id="feature_teras">
                                            <label class="form-check-label" for="feature_teras">Teras</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-6">
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
                                    <div class="col-md-4 col-6">
                                        <div class="form-check feature-checkbox mb-2">
                                            <input class="form-check-input" type="checkbox" name="env_features[]" value="Okula Yakın" id="feature_okul">
                                            <label class="form-check-label" for="feature_okul">Okula Yakın</label>
                                        </div>
                                        <div class="form-check feature-checkbox mb-2">
                                            <input class="form-check-input" type="checkbox" name="env_features[]" value="Toplu Taşımaya Yakın" id="feature_toplu_tasima">
                                            <label class="form-check-label" for="feature_toplu_tasima">Toplu Taşımaya Yakın</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <div class="form-check feature-checkbox mb-2">
                                            <input class="form-check-input" type="checkbox" name="env_features[]" value="Markete Yakın" id="feature_market">
                                            <label class="form-check-label" for="feature_market">Markete Yakın</label>
                                        </div>
                                        <div class="form-check feature-checkbox mb-2">
                                            <input class="form-check-input" type="checkbox" name="env_features[]" value="Hastaneye Yakın" id="feature_hastane">
                                            <label class="form-check-label" for="feature_hastane">Hastaneye Yakın</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-6">
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
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle"></i> İlanı Kaydet
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form gönderiminde yükleme göstergesi ekleyen JS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('#listingForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (this.checkValidity()) {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>İlan Kaydediliyor...';
                }
            }
        });
    }
});
</script>

<!-- Ek JS ve CSS kodları: Sidebar, tab navigasyonu, drag & drop, resim önizleme, mesafe ekleme, harita entegrasyonu vs. -->
<!-- (Bu kodlar admin.js gibi harici dosyaya taşınabilir ancak burada örnek olması için eklenmiştir.) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar ve tab toggle işlemleri
    const sidebarToggle = document.querySelector('#sidebarToggle');
    const sidebarToggleTop = document.querySelector('#sidebarToggleTop');
    const body = document.querySelector('body');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            if (sidebar) sidebar.classList.toggle('toggled');
            body.classList.toggle('sidebar-toggled');
        });
    }
    if (sidebarToggleTop) {
        sidebarToggleTop.addEventListener('click', function(e) {
            e.preventDefault();
            if (sidebar) sidebar.classList.toggle('toggled');
            body.classList.toggle('sidebar-toggled');
        });
    }
    
    // Tablar arası geçiş
    const nextTabButtons = document.querySelectorAll('.next-tab');
    nextTabButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const nextTabId = this.getAttribute('data-next');
            const nextTab = document.getElementById(nextTabId);
            if (nextTab) nextTab.click();
        });
    });
    const prevTabButtons = document.querySelectorAll('.prev-tab');
    prevTabButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const prevTabId = this.getAttribute('data-prev');
            const prevTab = document.getElementById(prevTabId);
            if (prevTab) prevTab.click();
        });
    });
    
    // Drag and drop dosya yükleme
    const dragDropAreas = document.querySelectorAll('#drag-drop-area');
    dragDropAreas.forEach(area => {
        const fileInput = area.querySelector('input[type="file"]');
        const selectBtn = area.querySelector('#select-files-btn');
        if (selectBtn && fileInput) {
            selectBtn.addEventListener('click', function(e) {
                e.preventDefault();
                fileInput.click();
            });
        }
        area.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });
        area.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });
        area.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            if (fileInput && e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                const event = new Event('change', { bubbles: true });
                fileInput.dispatchEvent(event);
            }
        });
    });
    
    // Resim önizleme ve ana görsel seçimi
    const fileInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    fileInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            const previewContainer = document.querySelector(this.dataset.preview || '#image-previews');
            if (!previewContainer) return;
            previewContainer.innerHTML = '';
            if (this.files && this.files.length > 0) {
                document.getElementById('main-image-container').style.display = 'block';
                for (let i = 0; i < this.files.length; i++) {
                    const file = this.files[i];
                    if (!file.type.startsWith('image/')) continue;
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = document.createElement('div');
                        preview.className = 'image-preview';
                        
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.alt = file.name;
                        preview.appendChild(img);
                        
                        const filenameDiv = document.createElement('div');
                        filenameDiv.className = 'image-filename';
                        filenameDiv.textContent = file.name.length > 15 ? file.name.substring(0,12) + '...' : file.name;
                        preview.appendChild(filenameDiv);
                        
                        const deleteBtn = document.createElement('button');
                        deleteBtn.className = 'delete-btn';
                        deleteBtn.innerHTML = '&times;';
                        deleteBtn.addEventListener('click', function() {
                            preview.remove();
                            if (previewContainer.children.length === 0) {
                                document.getElementById('main-image-container').style.display = 'none';
                            }
                        });
                        preview.appendChild(deleteBtn);
                        
                        previewContainer.appendChild(preview);
                    };
                    reader.readAsDataURL(file);
                }
                const mainImageSelect = document.getElementById('main-image-select');
                if (mainImageSelect) {
                    mainImageSelect.innerHTML = '';
                    const defaultOption = document.createElement('option');
                    defaultOption.value = "0";
                    defaultOption.textContent = 'İlk yüklenen görsel';
                    mainImageSelect.appendChild(defaultOption);
                    for (let i = 0; i < this.files.length; i++) {
                        const option = document.createElement('option');
                        option.value = i;
                        option.textContent = `Görsel ${i + 1}: ${this.files[i].name}`;
                        mainImageSelect.appendChild(option);
                    }
                }
            } else {
                document.getElementById('main-image-container').style.display = 'none';
            }
        });
    });
    
    // Mesafe satırları ekleme ve silme
    const addDistanceBtn = document.getElementById('add-distance');
    const distancesContainer = document.getElementById('distances-container');
    if (addDistanceBtn && distancesContainer) {
        addDistanceBtn.addEventListener('click', function() {
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
            newRow.querySelector('.remove-distance').addEventListener('click', function() {
                newRow.remove();
            });
        });
        document.querySelectorAll('.remove-distance').forEach(function(btn) {
            btn.addEventListener('click', function() {
                this.closest('.distance-row').remove();
            });
        });
    }
});
</script>

<!-- Ek Inline CSS -->
<style>
/* Wizard Adım Göstergesi */
.wizard-progress {
    display: flex;
    justify-content: space-between;
    position: relative;
    margin-bottom: 1.5rem;
    padding: 0 1rem;
}
.wizard-progress::before {
    content: '';
    position: absolute;
    top: 14px;
    left: 0;
    right: 0;
    height: 2px;
    background: var(--gray-light);
    z-index: 1;
}
.wizard-progress-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 2;
    flex: 1;
    text-align: center;
}
.wizard-progress-step-icon {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: var(--white);
    border: 2px solid var(--gray-light);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.5rem;
    color: var(--gray);
    font-size: 0.9rem;
    font-weight: 600;
    transition: all 0.3s ease;
}
.wizard-progress-step-label {
    font-size: 0.85rem;
    color: var(--gray);
    font-weight: 500;
    transition: all 0.3s ease;
}
.wizard-progress-step.active .wizard-progress-step-icon {
    background: var(--primary);
    border-color: var(--primary);
    color: var(--white);
}
.wizard-progress-step.active .wizard-progress-step-label {
    color: var(--primary);
    font-weight: 600;
}
.wizard-progress-step.completed .wizard-progress-step-icon {
    background: var(--success);
    border-color: var(--success);
    color: var(--white);
}
.wizard-progress-step.completed .wizard-progress-step-label {
    color: var(--success);
}
/* Drag & Drop Alanı */
.border-dashed {
    border-style: dashed !important;
    border-width: 2px !important;
    border-color: var(--gray-light) !important;
    transition: all 0.3s ease;
}
.border-dashed:hover {
    border-color: var(--primary) !important;
    background-color: rgba(67, 56, 202, 0.05);
}
/* Resim Önizleme Stilleri */
#image-previews {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
.image-preview {
    position: relative;
    border-radius: 6px;
    overflow: hidden;
}
.image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
/* Responsive Ayarlamalar */
@media (max-width: 768px) {
    .wizard-progress {
        display: none;
    }
    .nav-tabs {
        flex-wrap: nowrap;
        overflow-x: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .nav-tabs::-webkit-scrollbar {
        display: none;
    }
    .nav-tabs .nav-link {
        white-space: nowrap;
    }
}
</style>

<?php require_once '../templates/footer.php'; ?>
