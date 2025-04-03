<?php
// admin/listings/add.php - Düzeltilmiş versiyon
require_once '../../includes/init.php';

// Yetki kontrolü
$auth = new Auth();
$auth->requireLogin();

// Gerekli sınıfları yükle
$listing = new Listing();
$image = new Image();
require_once '../../includes/FormProcessor.php';
$formProcessor = new FormProcessor();

$errors = [];
$success = false;

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form işleme
    $result = $formProcessor->processListingForm($_POST, $_FILES, $listing, $image);
    
    $success = $result['success'];
    $errors = $result['errors'];
    $listingId = $result['listingId'];
    
    // Başarılıysa yönlendir
    if ($success) {
        header('Location: edit.php?id=' . $listingId . '&success=1');
        exit;
    }
}

$pageTitle = "Yeni İlan Ekle";
$activePage = "listings";
require_once '../templates/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Yeni İlan Ekle</h1>
    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> İlanlara Dön
    </a>
</div>

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
        <form action="add.php" method="post" enctype="multipart/form-data" id="listingForm" class="needs-validation" novalidate>
            <div class="tab-content">
                <!-- Temel Bilgiler Tab -->
                <div class="tab-pane fade show active" id="basic" role="tabpanel">
                    <div class="p-lg-3">
                        <h5 class="mb-3">İlan Bilgileri</h5>
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Başlık <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
                            <div class="invalid-feedback">Lütfen bir başlık girin</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Açıklama <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="description" name="description" 
                                     rows="6" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                            <div class="invalid-feedback">Lütfen bir açıklama girin</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="short_description" class="form-label">Kısa Açıklama</label>
                            <textarea class="form-control" id="short_description" name="short_description" 
                                     rows="2"><?= htmlspecialchars($_POST['short_description'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="keywords" class="form-label">Anahtar Kelimeler</label>
                            <input type="text" class="form-control" id="keywords" name="keywords" 
                                   value="<?= htmlspecialchars($_POST['keywords'] ?? '') ?>">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">Kategori <span class="text-danger">*</span></label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="House" <?= isset($_POST['category']) && $_POST['category'] == 'House' ? 'selected' : '' ?>>Müstakil Ev</option>
                                    <option value="Apartment" <?= isset($_POST['category']) && $_POST['category'] == 'Apartment' ? 'selected' : '' ?>>Daire</option>
                                    <option value="Commercial" <?= isset($_POST['category']) && $_POST['category'] == 'Commercial' ? 'selected' : '' ?>>Ticari</option>
                                    <option value="Land" <?= isset($_POST['category']) && $_POST['category'] == 'Land' ? 'selected' : '' ?>>Arsa</option>
                                    <option value="Other" <?= isset($_POST['category']) && $_POST['category'] == 'Other' ? 'selected' : '' ?>>Diğer</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label d-block">Öne Çıkar</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="featured" name="featured" 
                                           <?= isset($_POST['featured']) ? 'checked' : '' ?>>
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
                                        <input class="form-check-input" type="radio" name="listing_type" id="type_sale" 
                                               value="sale" <?= (!isset($_POST['listing_type']) || $_POST['listing_type'] == 'sale') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="type_sale">Satılık</label>
                                    </div>
                                    <div class="form-check me-3">
                                        <input class="form-check-input" type="radio" name="listing_type" id="type_rent" 
                                               value="rent" <?= isset($_POST['listing_type']) && $_POST['listing_type'] == 'rent' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="type_rent">Kiralık</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="listing_type" id="type_both" 
                                               value="both" <?= isset($_POST['listing_type']) && $_POST['listing_type'] == 'both' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="type_both">Hem Satılık Hem Kiralık</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3" id="sale-price-container" 
                                 <?= isset($_POST['listing_type']) && $_POST['listing_type'] == 'rent' ? 'style="display:none"' : '' ?>>
                                <label for="sale_price" class="form-label">Satış Fiyatı (₺) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="sale_price" name="sale_price" min="0" step="1000"
                                           value="<?= htmlspecialchars($_POST['sale_price'] ?? '') ?>" required>
                                    <span class="input-group-text">₺</span>
                                </div>
                                <div class="invalid-feedback">Lütfen bir satış fiyatı girin</div>
                            </div>
                            
                            <div class="col-md-6 mb-3" id="rent-price-container"
                                 <?= (!isset($_POST['listing_type']) || ($_POST['listing_type'] != 'rent' && $_POST['listing_type'] != 'both')) ? 'style="display:none"' : '' ?>>
                                <label for="rent_price" class="form-label">Kira Fiyatı (₺/ay) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="rent_price" name="rent_price" min="0" step="100"
                                           value="<?= htmlspecialchars($_POST['rent_price'] ?? '') ?>">
                                    <span class="input-group-text">₺/ay</span>
                                </div>
                                <div class="invalid-feedback">Lütfen bir kira fiyatı girin</div>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="button" class="btn btn-primary next-tab" data-next="details-tab">
                                İleri <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Detaylar Tab -->
                <div class="tab-pane fade" id="details" role="tabpanel">
                    <div class="p-lg-3">
                        <h5 class="mb-3">Gayrimenkul Özellikleri</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="property_size" class="form-label">Alan (m²) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="property_size" name="property_size" 
                                           min="1" step="0.01" value="<?= htmlspecialchars($_POST['property_size'] ?? '') ?>" required>
                                    <span class="input-group-text">m²</span>
                                </div>
                                <div class="invalid-feedback">Lütfen alan bilgisi girin</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="property_lot_size" class="form-label">Arsa Alanı (m²)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="property_lot_size" name="property_lot_size" 
                                           min="0" step="0.01" value="<?= htmlspecialchars($_POST['property_lot_size'] ?? '') ?>">
                                    <span class="input-group-text">m²</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="rooms" class="form-label">Oda Sayısı <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="rooms" name="rooms" 
                                       min="0" value="<?= htmlspecialchars($_POST['rooms'] ?? '') ?>" required>
                                <div class="invalid-feedback">Lütfen oda sayısı girin</div>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="bathrooms" class="form-label">Banyo Sayısı <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="bathrooms" name="bathrooms" 
                                       min="0" value="<?= htmlspecialchars($_POST['bathrooms'] ?? '') ?>" required>
                                <div class="invalid-feedback">Lütfen banyo sayısı girin</div>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="floors_no" class="form-label">Kat Sayısı <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="floors_no" name="floors_no" 
                                       min="1" value="<?= htmlspecialchars($_POST['floors_no'] ?? '') ?>" required>
                                <div class="invalid-feedback">Lütfen kat sayısı girin</div>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="garages" class="form-label">Garaj Sayısı</label>
                                <input type="number" class="form-control" id="garages" name="garages" 
                                       min="0" value="<?= htmlspecialchars($_POST['garages'] ?? '0') ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="year_built" class="form-label">Yapım Yılı</label>
                                <input type="number" class="form-control" id="year_built" name="year_built" 
                                       min="1900" max="<?= date('Y') ?>" value="<?= htmlspecialchars($_POST['year_built'] ?? '') ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="energy_efficiency" class="form-label">Enerji Verimliliği</label>
                                <select class="form-select" id="energy_efficiency" name="energy_efficiency">
                                    <option value="">Seçiniz</option>
                                    <option value="A" <?= isset($_POST['energy_efficiency']) && $_POST['energy_efficiency'] == 'A' ? 'selected' : '' ?>>A Sınıfı</option>
                                    <option value="B" <?= isset($_POST['energy_efficiency']) && $_POST['energy_efficiency'] == 'B' ? 'selected' : '' ?>>B Sınıfı</option>
                                    <option value="C" <?= isset($_POST['energy_efficiency']) && $_POST['energy_efficiency'] == 'C' ? 'selected' : '' ?>>C Sınıfı</option>
                                    <option value="D" <?= isset($_POST['energy_efficiency']) && $_POST['energy_efficiency'] == 'D' ? 'selected' : '' ?>>D Sınıfı</option>
                                    <option value="E" <?= isset($_POST['energy_efficiency']) && $_POST['energy_efficiency'] == 'E' ? 'selected' : '' ?>>E Sınıfı</option>
                                    <option value="F" <?= isset($_POST['energy_efficiency']) && $_POST['energy_efficiency'] == 'F' ? 'selected' : '' ?>>F Sınıfı</option>
                                    <option value="G" <?= isset($_POST['energy_efficiency']) && $_POST['energy_efficiency'] == 'G' ? 'selected' : '' ?>>G Sınıfı</option>
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
                
                <!-- Konum Tab -->
                <div class="tab-pane fade" id="location" role="tabpanel">
                    <div class="p-lg-3">
                        <h5 class="mb-3">Adres Bilgileri</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="street" class="form-label">Sokak/Cadde <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="street" name="street" 
                                       value="<?= htmlspecialchars($_POST['street'] ?? '') ?>" required>
                                <div class="invalid-feedback">Lütfen sokak/cadde bilgisi girin</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="zip" class="form-label">Posta Kodu <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="zip" name="zip" 
                                       value="<?= htmlspecialchars($_POST['zip'] ?? '') ?>" required>
                                <div class="invalid-feedback">Lütfen posta kodu girin</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="city" class="form-label">Şehir <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="city" name="city" 
                                       value="<?= htmlspecialchars($_POST['city'] ?? '') ?>" required>
                                <div class="invalid-feedback">Lütfen şehir bilgisi girin</div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="state" class="form-label">İl/Bölge <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="state" name="state" 
                                       value="<?= htmlspecialchars($_POST['state'] ?? '') ?>" required>
                                <div class="invalid-feedback">Lütfen il/bölge bilgisi girin</div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="country" class="form-label">Ülke <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="country" name="country" 
                                       value="<?= htmlspecialchars($_POST['country'] ?? 'Türkiye') ?>" required>
                                <div class="invalid-feedback">Lütfen ülke bilgisi girin</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Konum (Haritada İşaretleyin) <span class="text-danger">*</span></label>
                            <div id="map-container" class="mb-2"></div>
                            <div class="form-text mb-2">Haritada mülkün konumunu belirlemek için tıklayın</div>
                            <input type="hidden" id="latitude" name="latitude" value="<?= htmlspecialchars($_POST['latitude'] ?? '') ?>" required>
                            <input type="hidden" id="longitude" name="longitude" value="<?= htmlspecialchars($_POST['longitude'] ?? '') ?>" required>
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
                
                <!-- Medya Tab -->
                <div class="tab-pane fade" id="media" role="tabpanel">
                    <div class="p-lg-3">
                        <h5 class="mb-3">Görsel Galerisi</h5>
                        
                        <div class="mb-3">
                            <label for="images" class="form-label">Görseller <span class="text-danger">*</span></label>
                            <div id="drag-drop-area" class="p-4 text-center border border-dashed rounded mb-3">
                                <i class="bi bi-cloud-arrow-up display-4 mb-3 text-muted"></i>
                                <h5>Dosyaları sürükleyip bırakın veya tıklayın</h5>
                                <p class="text-muted mb-3">Kabul edilen formatlar: JPG, JPEG, PNG (Maksimum: 10MB)</p>
                                <input type="file" class="form-control d-none" id="images" name="images[]" 
                                       accept="image/jpeg,image/png,image/jpg" multiple>
                                <button type="button" class="btn btn-outline-primary px-4" id="select-files-btn">
                                    <i class="bi bi-file-earmark-image me-2"></i>Dosya Seç
                                </button>
                            </div>
                            <div id="image-previews" class="d-flex flex-wrap gap-3 mb-3"></div>
                            <div id="main-image-container" class="mt-3 d-none">
                                <label for="main-image-select" class="form-label">Ana Görsel</label>
                                <select class="form-select" id="main-image-select" name="main_image_index">
                                    <option value="0">İlk yüklenen görsel</option>
                                </select>
                                <div class="form-text">Ana görsel, listeleme sayfalarında ve haritada gösterilir</div>
                            </div>
                        </div>
                        
                        <h5 class="mb-3 mt-4">Multimedya</h5>
                        
                        <div class="mb-3">
                            <label for="video_url" class="form-label">Video URL (Youtube/Vimeo)</label>
                            <input type="url" class="form-control" id="video_url" name="video_url" 
                                   value="<?= htmlspecialchars($_POST['video_url'] ?? '') ?>">
                            <div class="form-text">YouTube veya Vimeo video bağlantısı ekleyebilirsiniz</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="virtual_tour" class="form-label">Sanal Tur URL</label>
                            <input type="url" class="form-control" id="virtual_tour" name="virtual_tour" 
                                   value="<?= htmlspecialchars($_POST['virtual_tour'] ?? '') ?>">
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
                
                <!-- Özellikler Tab -->
                <div class="tab-pane fade" id="features" role="tabpanel">
                    <div class="p-lg-3">
                        <h5 class="mb-3">Özellikler</h5>
                        
                        <div class="mb-4">
                            <label class="form-label d-block">İç Özellikler</label>
                            <div class="row">
                                <div class="col-md-4 col-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="interior_features[]" value="Klima" id="feature_klima"
                                               <?= isset($_POST['interior_features']) && in_array('Klima', $_POST['interior_features']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="feature_klima">Klima</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="interior_features[]" value="Merkezi Isıtma" id="feature_isitma"
                                               <?= isset($_POST['interior_features']) && in_array('Merkezi Isıtma', $_POST['interior_features']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="feature_isitma">Merkezi Isıtma</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="interior_features[]" value="Ankastre Mutfak" id="feature_ankastre"
                                               <?= isset($_POST['interior_features']) && in_array('Ankastre Mutfak', $_POST['interior_features']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="feature_ankastre">Ankastre Mutfak</label>
                                    </div>
                                </div>
                                <div class="col-md-4 col-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="interior_features[]" value="Akıllı Ev Sistemi" id="feature_akilli_ev"
                                               <?= isset($_POST['interior_features']) && in_array('Akıllı Ev Sistemi', $_POST['interior_features']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="feature_akilli_ev">Akıllı Ev Sistemi</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="interior_features[]" value="Güvenlik Sistemi" id="feature_guvenlik"
                                               <?= isset($_POST['interior_features']) && in_array('Güvenlik Sistemi', $_POST['interior_features']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="feature_guvenlik">Güvenlik Sistemi</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="interior_features[]" value="İnternet Bağlantısı" id="feature_internet"
                                               <?= isset($_POST['interior_features']) && in_array('İnternet Bağlantısı', $_POST['interior_features']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="feature_internet">İnternet Bağlantısı</label>
                                    </div>
                                </div>
                                <div class="col-md-4 col-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="interior_features[]" value="Ebeveyn Banyosu" id="feature_ebeveyn"
                                               <?= isset($_POST['interior_features']) && in_array('Ebeveyn Banyosu', $_POST['interior_features']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="feature_ebeveyn">Ebeveyn Banyosu</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="interior_features[]" value="Giyinme Odası" id="feature_giyinme"
                                               <?= isset($_POST['interior_features']) && in_array('Giyinme Odası', $_POST['interior_features']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="feature_giyinme">Giyinme Odası</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="interior_features[]" value="Beyaz Eşya" id="feature_beyaz_esya"
                                               <?= isset($_POST['interior_features']) && in_array('Beyaz Eşya', $_POST['interior_features']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="feature_beyaz_esya">Beyaz Eşya</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label d-block">Dış Özellikler</label>
                            <div class="row">
                                <div class="col-md-4 col-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="exterior_features[]" value="Bahçe" id="feature_bahce"
                                               <?= isset($_POST['exterior_features']) && in_array('Bahçe', $_POST['exterior_features']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="feature_bahce">Bahçe</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="exterior_features[]" value="Havuz" id="feature_havuz"
                                               <?= isset($_POST['exterior_features']) && in_array('Havuz', $_POST['exterior_features']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="feature_havuz">Havuz</label>
                                    </div>
                                </div>
                                <div class="col-md-4 col-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="exterior_features[]" value="Otopark" id="feature_otopark"
                                               <?= isset($_POST['exterior_features']) && in_array('Otopark', $_POST['exterior_features']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="feature_otopark">Otopark</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="exterior_features[]" value="Teras" id="feature_teras"
                                               <?= isset($_POST['exterior_features']) && in_array('Teras', $_POST['exterior_features']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="feature_teras">Teras</label>
                                    </div>
                                </div>
                                <div class="col-md-4 col-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="exterior_features[]" value="Güvenlik" id="feature_guvenlik_dis"
                                               <?= isset($_POST['exterior_features']) && in_array('Güvenlik', $_POST['exterior_features']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="feature_guvenlik_dis">Güvenlik</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="exterior_features[]" value="Asansör" id="feature_asansor"
                                               <?= isset($_POST['exterior_features']) && in_array('Asansör', $_POST['exterior_features']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="feature_asansor">Asansör</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label d-block">Çevre Özellikleri</label>
                            <div class="row">
                                <div class="col-md-4 col-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="env_features[]" value="Okula Yakın" id="feature_okul"
                                               <?= isset($_POST['env_features']) && in_array('Okula Yakın', $_POST['env_features']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="feature_okul">Okula Yakın</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="env_features[]" value="Toplu Taşımaya Yakın" id="feature_toplu_tasima"
                                               <?= isset($_POST['env_features']) && in_array('Toplu Taşımaya Yakın', $_POST['env_features']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="feature_toplu_tasima">Toplu Taşımaya Yakın</label>
                                    </div>
                                </div>
                                <div class="col-md-4 col-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="env_features[]" value="Markete Yakın" id="feature_market"
                                               <?= isset($_POST['env_features']) && in_array('Markete Yakın', $_POST['env_features']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="feature_market">Markete Yakın</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="env_features[]" value="Hastaneye Yakın" id="feature_hastane"
                                               <?= isset($_POST['env_features']) && in_array('Hastaneye Yakın', $_POST['env_features']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="feature_hastane">Hastaneye Yakın</label>
                                    </div>
                                </div>
                                <div class="col-md-4 col-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="env_features[]" value="Denize Yakın" id="feature_deniz"
                                               <?= isset($_POST['env_features']) && in_array('Denize Yakın', $_POST['env_features']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="feature_deniz">Denize Yakın</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="env_features[]" value="Şehir Merkezine Yakın" id="feature_merkez"
                                               <?= isset($_POST['env_features']) && in_array('Şehir Merkezine Yakın', $_POST['env_features']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="feature_merkez">Şehir Merkezine Yakın</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <h5 class="mb-3 mt-4">Yakın Çevre Mesafeleri</h5>
                        
                        <div id="distances-container">
                            <?php if (isset($_POST['distance_name']) && is_array($_POST['distance_name'])): ?>
                                <?php for ($i = 0; $i < count($_POST['distance_name']); $i++): ?>
                                    <?php if (!empty($_POST['distance_name'][$i])): ?>
                                        <div class="distance-row row mb-3">
                                            <div class="col-md-5">
                                                <input type="text" class="form-control" name="distance_name[]" 
                                                       value="<?= htmlspecialchars($_POST['distance_name'][$i]) ?>" 
                                                       placeholder="Mekan Adı (örn. Metro)">
                                            </div>
                                            <div class="col-md-5">
                                                <div class="input-group">
                                                    <input type="number" class="form-control" name="distance_value[]" 
                                                           value="<?= htmlspecialchars($_POST['distance_value'][$i] ?? '') ?>" 
                                                           placeholder="Mesafe" step="0.1" min="0">
                                                    <span class="input-group-text">km</span>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-danger w-100 remove-distance">Sil</button>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            <?php else: ?>
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
                            <?php endif; ?>
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
                            <button type="submit" class="btn btn-success" id="submitBtn">
                                <i class="bi bi-check-circle"></i> İlanı Kaydet
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- JS Kodları -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form doğrulama
    const form = document.getElementById('listingForm');
    
    form.addEventListener('submit', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            
            // Hatalı sekmeyi bul ve aç
            const invalidInput = form.querySelector(':invalid');
            if (invalidInput) {
                const tabPane = invalidInput.closest('.tab-pane');
                if (tabPane && !tabPane.classList.contains('show')) {
                    const tabId = tabPane.id;
                    document.querySelector(`a[href="#${tabId}"]`).click();
                    
                    setTimeout(() => {
                        invalidInput.focus();
                    }, 500);
                }
            }
            
            // Uyarı göster
            alert('Lütfen zorunlu alanları doldurun!');
        } else {
            // Gönderi sırasında düğmeyi devre dışı bırak
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('submitBtn').innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Kaydediliyor...';
        }
        
        form.classList.add('was-validated');
    });
    
    // İlan tipi değiştiğinde fiyat alanlarını göster/gizle
    const listingTypeRadios = document.querySelectorAll('input[name="listing_type"]');
    const salePriceContainer = document.getElementById('sale-price-container');
    const rentPriceContainer = document.getElementById('rent-price-container');
    
    listingTypeRadios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            if (this.value === 'sale') {
                salePriceContainer.style.display = 'block';
                rentPriceContainer.style.display = 'none';
                
                document.getElementById('sale_price').required = true;
                document.getElementById('rent_price').required = false;
                document.getElementById('rent_price').value = '';
            } else if (this.value === 'rent') {
                salePriceContainer.style.display = 'none';
                rentPriceContainer.style.display = 'block';
                
                document.getElementById('rent_price').required = true;
                document.getElementById('sale_price').required = false;
                document.getElementById('sale_price').value = '';
            } else if (this.value === 'both') {
                salePriceContainer.style.display = 'block';
                rentPriceContainer.style.display = 'block';
                
                document.getElementById('sale_price').required = true;
                document.getElementById('rent_price').required = true;
            }
        });
    });
    
    // Tab gezinme butonları
    const tabLinks = document.querySelectorAll('.nav-link[data-bs-toggle="tab"]');
    const nextTabButtons = document.querySelectorAll('.next-tab');
    const prevTabButtons = document.querySelectorAll('.prev-tab');
    
    // Tab değişiminde wizard adımlarını güncelle
    tabLinks.forEach(function(tabLink) {
        tabLink.addEventListener('shown.bs.tab', function(e) {
            const targetId = e.target.getAttribute('id');
            document.querySelectorAll('.wizard-progress-step').forEach(function(step) {
                step.classList.remove('active');
                if (step.getAttribute('data-step') === targetId) {
                    step.classList.add('active');
                }
            });
        });
    });
    
    // İleri/geri butonları
    nextTabButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const nextTabId = this.getAttribute('data-next');
            document.getElementById(nextTabId).click();
        });
    });
    
    prevTabButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const prevTabId = this.getAttribute('data-prev');
            document.getElementById(prevTabId).click();
        });
    });
    
    // Sürükle bırak görsel yükleme
    const dragDropArea = document.getElementById('drag-drop-area');
    const fileInput = document.getElementById('images');
    const selectBtn = document.getElementById('select-files-btn');
    const previewsContainer = document.getElementById('image-previews');
    const mainImageContainer = document.getElementById('main-image-container');
    const mainImageSelect = document.getElementById('main-image-select');
    
    if (dragDropArea && fileInput) {
        // Dosya seçme butonu
        selectBtn.addEventListener('click', function() {
            fileInput.click();
        });
        
        // Sürükle bırak olayları
        dragDropArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('bg-light');
        });
        
        dragDropArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('bg-light');
        });
        
        dragDropArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('bg-light');
            
            if (fileInput && e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                const event = new Event('change', { bubbles: true });
                fileInput.dispatchEvent(event);
            }
        });
        
        // Dosya seçildiğinde önizleme göster
        fileInput.addEventListener('change', function() {
            previewsContainer.innerHTML = '';
            mainImageSelect.innerHTML = '';
            
            if (this.files && this.files.length > 0) {
                mainImageContainer.classList.remove('d-none');
                
                // Varsayılan seçeneği ekle
                const defaultOption = document.createElement('option');
                defaultOption.value = "0";
                defaultOption.textContent = 'İlk yüklenen görsel';
                mainImageSelect.appendChild(defaultOption);
                
                for (let i = 0; i < this.files.length; i++) {
                    const file = this.files[i];
                    
                    if (!file.type.match('image.*')) {
                        continue;
                    }
                    
                    const reader = new FileReader();
                    const previewIndex = i;
                    
                    reader.onload = function(e) {
                        // Görsel önizleme oluştur
                        const preview = document.createElement('div');
                        preview.className = 'image-preview-item position-relative border rounded overflow-hidden';
                        preview.style.width = '150px';
                        preview.style.height = '100px';
                        
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'w-100 h-100 object-fit-cover';
                        preview.appendChild(img);
                        
                        // Dosya adı göster
                        const filename = document.createElement('div');
                        filename.className = 'bg-dark bg-opacity-50 text-white small p-1 position-absolute bottom-0 w-100 text-truncate';
                        filename.textContent = file.name;
                        preview.appendChild(filename);
                        
                        // Silme butonu ekle
                        const removeBtn = document.createElement('button');
                        removeBtn.className = 'btn btn-sm btn-danger position-absolute top-0 end-0 m-1 p-0 d-flex align-items-center justify-content-center';
                        removeBtn.style.width = '20px';
                        removeBtn.style.height = '20px';
                        removeBtn.innerHTML = '×';
                        removeBtn.type = 'button';
                        removeBtn.addEventListener('click', function() {
                            // Bu işlevsiz, gerçekte dosya girdisinden kaldırma yapamıyoruz
                            // Ancak kullanıcıya görsel geri bildirim verir
                            preview.remove();
                            
                            // Tüm önizlemeler kaldırıldıysa, ana görsel seçiciyi gizle
                            if (previewsContainer.children.length === 0) {
                                mainImageContainer.classList.add('d-none');
                            }
                        });
                        preview.appendChild(removeBtn);
                        
                        previewsContainer.appendChild(preview);
                        
                        // Ana görsel seçici için seçenek ekle
                        const option = document.createElement('option');
                        option.value = previewIndex;
                        option.textContent = `Görsel ${previewIndex + 1}: ${file.name}`;
                        mainImageSelect.appendChild(option);
                    };
                    
                    reader.readAsDataURL(file);
                }
            } else {
                mainImageContainer.classList.add('d-none');
            }
        });
    }
    
    // // Mesafe satırları
    // const addDistanceBtn = document.getElementById('add-distance');
    // const distancesContainer = document.getElementById('distances-container');
    
    // if (addDistanceBtn && distancesContainer) {
    //     addDistanceBtn.addEventListener('click', function() {
    //         const newRow = document.createElement('div');
    //         newRow.className = 'distance-row row mb-3';
    //         newRow.innerHTML = `
    //             <div class="col-md-5">
    //                 <input type="text" class="form-control" name="distance_name[]" placeholder="Mekan Adı (örn. Metro)">
    //             </div>
    //             <div class="col-md-5">
    //                 <div class="input-group">
    //                     <input type="number" class="form-control" name="distance_value[]" placeholder="Mesafe" step="0.1" min="0">
    //                     <span class="input-group-text">km</span>
    //                 </div>
    //             </div>
    //             <div class="col-md-2">
    //                 <button type="button" class="btn btn-danger w-100 remove-distance">Sil</button>
    //             </div>
    //         `;
            
    //         distancesContainer.appendChild(newRow);
            
    //         // Yeni eklenen satırın silme düğmesine olay dinleyicisi ekle
    //         newRow.querySelector('.remove-distance').addEventListener('click', function() {
    //             newRow.remove();
    //         });
    //     });
        
    //     // Mevcut silme düğmelerine olay dinleyicileri ekle
    //     document.querySelectorAll('.remove-distance').forEach(function(button) {
    //         button.addEventListener('click', function() {
    //             this.closest('.distance-row').remove();
    //         });
    //     });
    // }
});
</script>

<?php require_once '../templates/footer.php'; ?>