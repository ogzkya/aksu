// admin/listings/edit.php
<?php
require_once '../../includes/init.php';

$auth = new Auth();
$auth->requireLogin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$listingId = (int)$_GET['id'];
$listing = new Listing();
$image = new Image();
$errors = [];
$success = isset($_GET['success']) || false;

// İlan bilgilerini getir
$listingData = $listing->getListingById($listingId);

if (!$listingData) {
    header('Location: index.php');
    exit;
}

// İlan görselleri
$images = $listing->getListingImages($listingId);

// JSON verileri
$multimedia = json_decode($listingData['multimedia'] ?? '[]', true) ?: [];
$distances = json_decode($listingData['distances'] ?? '[]', true) ?: [];
$features = json_decode($listingData['features'] ?? '[]', true) ?: [
    'İç Özellikler' => [],
    'Dış Özellikler' => [],
    'Çevre Özellikleri' => []
];

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Form verilerini al
        $updatedData = [
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
                $updatedData['distances'][$distanceNames[$i]] = $distanceValues[$i];
            }
        }
        
        // Özellikleri işle
        if (isset($_POST['interior_features'])) {
            $updatedData['features']['İç Özellikler'] = $_POST['interior_features'];
        }
        
        if (isset($_POST['exterior_features'])) {
            $updatedData['features']['Dış Özellikler'] = $_POST['exterior_features'];
        }
        
        if (isset($_POST['env_features'])) {
            $updatedData['features']['Çevre Özellikleri'] = $_POST['env_features'];
        }
        
        // Verilerin doğruluğunu kontrol et
        if (empty($updatedData['title'])) {
            $errors[] = 'İlan başlığı gereklidir.';
        }
        
        if (empty($updatedData['description'])) {
            $errors[] = 'İlan açıklaması gereklidir.';
        }
        
        if (!is_numeric($updatedData['sale_price']) || $updatedData['sale_price'] <= 0) {
            $errors[] = 'Geçerli bir satış fiyatı girilmelidir.';
        }
        
        if (!is_numeric($updatedData['property_size']) || $updatedData['property_size'] <= 0) {
            $errors[] = 'Geçerli bir alan (m²) girilmelidir.';
        }
        
        if (empty($updatedData['city']) || empty($updatedData['country'])) {
            $errors[] = 'Şehir ve ülke bilgileri gereklidir.';
        }
        
        // Hata yoksa ilanı güncelle
        if (empty($errors)) {
            $listing->updateListing($listingId, $updatedData);
            
            // Görselleri yükle
            if (isset($_FILES['new_images']) && !empty($_FILES['new_images']['name'][0])) {
                $mainImageIndex = $_POST['main_image_index'] ?? -1;
                
                for ($i = 0; $i < count($_FILES['new_images']['name']); $i++) {
                    if ($_FILES['new_images']['error'][$i] === UPLOAD_ERR_OK) {
                        $file = [
                            'name' => $_FILES['new_images']['name'][$i],
                            'type' => $_FILES['new_images']['type'][$i],
                            'tmp_name' => $_FILES['new_images']['tmp_name'][$i],
                            'error' => $_FILES['new_images']['error'][$i],
                            'size' => $_FILES['new_images']['size'][$i]
                        ];
                        
                        $imageUrl = $image->upload($file, $listingId);
                        $listing->addListingImage($listingId, $imageUrl, $i == $mainImageIndex ? 1 : 0);
                    }
                }
            }
            
            // Ana görseli güncelle
          // admin/listings/edit.php dosyasındaki ana görsel seçimini düzeltin
if (isset($_POST['main_image']) && is_numeric($_POST['main_image'])) {
    $mainImageId = (int)$_POST['main_image'];
    // Önce tüm ana görselleri temizle
    $db->query("UPDATE listing_images SET is_main = 0 WHERE listing_id = ?", [$listingId]);
    // Sonra seçilen görseli ana görsel olarak ayarla
    $db->query("UPDATE listing_images SET is_main = 1 WHERE id = ? AND listing_id = ?", [$mainImageId, $listingId]);
}
            
            // Silinen görseller
            if (isset($_POST['deleted_images']) && !empty($_POST['deleted_images'])) {
                $deletedImages = explode(',', $_POST['deleted_images']);
                
                foreach ($deletedImages as $imgId) {
                    if (is_numeric($imgId)) {
                        $listing->deleteListingImage((int)$imgId);
                    }
                }
            }
            
            $success = true;
            // Verileri yeniden yükle
            $listingData = $listing->getListingById($listingId);
            $images = $listing->getListingImages($listingId);
            $multimedia = json_decode($listingData['multimedia'] ?? '[]', true) ?: [];
            $distances = json_decode($listingData['distances'] ?? '[]', true) ?: [];
            $features = json_decode($listingData['features'] ?? '[]', true) ?: [
                'İç Özellikler' => [],
                'Dış Özellikler' => [],
                'Çevre Özellikleri' => []
            ];
        }
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

$pageTitle = "İlan Düzenle";
$activePage = "listings";
require_once '../templates/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">İlan Düzenle</h1>
    <div>
        <a href="../../listing.php?id=<?= $listingId ?>" class="btn btn-info me-2" target="_blank">
            <i class="bi bi-eye"></i> İlanı Görüntüle
        </a>
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> İlanlara Dön
        </a>
    </div>
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
        İlan başarıyla güncellendi.
    </div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-body">
        <form action="edit.php?id=<?= $listingId ?>" method="post" enctype="multipart/form-data">
            <div class="row">
                <!-- Sol Kolon -->
                <div class="col-lg-8">
                    <h5 class="mb-3">İlan Bilgileri</h5>
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Başlık <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($listingData['title']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Açıklama <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="6" required><?= htmlspecialchars($listingData['description']) ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="short_description" class="form-label">Kısa Açıklama</label>
                        <textarea class="form-control" id="short_description" name="short_description" rows="2"><?= htmlspecialchars($listingData['short_description'] ?? '') ?></textarea>
                        <div class="form-text">Listeleme sayfalarında gösterilecek kısa açıklama</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="keywords" class="form-label">Anahtar Kelimeler</label>
                        <input type="text" class="form-control" id="keywords" name="keywords" value="<?= htmlspecialchars($listingData['keywords'] ?? '') ?>">
                        <div class="form-text">Virgülle ayırarak birden fazla anahtar kelime girebilirsiniz</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="House" <?= $listingData['category'] == 'House' ? 'selected' : '' ?>>Müstakil Ev</option>
                                <option value="Apartment" <?= $listingData['category'] == 'Apartment' ? 'selected' : '' ?>>Daire</option>
                                <option value="Commercial" <?= $listingData['category'] == 'Commercial' ? 'selected' : '' ?>>Ticari</option>
                                <option value="Land" <?= $listingData['category'] == 'Land' ? 'selected' : '' ?>>Arsa</option>
                                <option value="Other" <?= $listingData['category'] == 'Other' ? 'selected' : '' ?>>Diğer</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="featured" class="form-label d-block">Öne Çıkar</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="featured" name="featured" <?= $listingData['featured'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="featured">Bu ilanı ana sayfada öne çıkar</label>
                            </div>
                        </div>
                    </div>
                    
                    <h5 class="mb-3 mt-4">Fiyat Bilgileri</h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="sale_price" class="form-label">Satış Fiyatı (₺) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="sale_price" name="sale_price" min="0" step="1000" value="<?= $listingData['sale_price'] ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="rent_price" class="form-label">Kira Fiyatı (₺/ay)</label>
                            <input type="number" class="form-control" id="rent_price" name="rent_price" min="0" step="100" value="<?= $listingData['rent_price'] ?? '' ?>">
                            <div class="form-text">Kiralık değilse boş bırakın</div>
                        </div>
                    </div>
                    
                    <h5 class="mb-3 mt-4">Gayrimenkul Özellikleri</h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="property_size" class="form-label">Alan (m²) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="property_size" name="property_size" min="1" step="0.01" value="<?= $listingData['property_size'] ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="property_lot_size" class="form-label">Arsa Alanı (m²)</label>
                            <input type="number" class="form-control" id="property_lot_size" name="property_lot_size" min="0" step="0.01" value="<?= $listingData['property_lot_size'] ?? '' ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="rooms" class="form-label">Oda Sayısı <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="rooms" name="rooms" min="0" value="<?= $listingData['rooms'] ?>" required>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="bathrooms" class="form-label">Banyo Sayısı <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="bathrooms" name="bathrooms" min="0" value="<?= $listingData['bathrooms'] ?>" required>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="floors_no" class="form-label">Kat Sayısı <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="floors_no" name="floors_no" min="1" value="<?= $listingData['floors_no'] ?>" required>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="garages" class="form-label">Garaj Sayısı</label>
                            <input type="number" class="form-control" id="garages" name="garages" min="0" value="<?= $listingData['garages'] ?? 0 ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="year_built" class="form-label">Yapım Yılı</label>
                            <input type="number" class="form-control" id="year_built" name="year_built" min="1900" max="<?= date('Y') ?>" value="<?= $listingData['year_built'] ?? '' ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="energy_efficiency" class="form-label">Enerji Verimliliği</label>
                            <select class="form-select" id="energy_efficiency" name="energy_efficiency">
                                <option value="">Seçiniz</option>
                                <option value="A" <?= $listingData['energy_efficiency'] == 'A' ? 'selected' : '' ?>>A Sınıfı</option>
                                <option value="B" <?= $listingData['energy_efficiency'] == 'B' ? 'selected' : '' ?>>B Sınıfı</option>
                                <option value="C" <?= $listingData['energy_efficiency'] == 'C' ? 'selected' : '' ?>>C Sınıfı</option>
                                <option value="D" <?= $listingData['energy_efficiency'] == 'D' ? 'selected' : '' ?>>D Sınıfı</option>
                                <option value="E" <?= $listingData['energy_efficiency'] == 'E' ? 'selected' : '' ?>>E Sınıfı</option>
                                <option value="F" <?= $listingData['energy_efficiency'] == 'F' ? 'selected' : '' ?>>F Sınıfı</option>
                                <option value="G" <?= $listingData['energy_efficiency'] == 'G' ? 'selected' : '' ?>>G Sınıfı</option>
                            </select>
                        </div>
                    </div>
                    
                    <h5 class="mb-3 mt-4">Adres Bilgileri</h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="street" class="form-label">Sokak/Cadde <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="street" name="street" value="<?= htmlspecialchars($listingData['street']) ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="zip" class="form-label">Posta Kodu <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="zip" name="zip" value="<?= htmlspecialchars($listingData['zip']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="city" class="form-label">Şehir <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="city" name="city" value="<?= htmlspecialchars($listingData['city']) ?>" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="state" class="form-label">İl/Bölge <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="state" name="state" value="<?= htmlspecialchars($listingData['state']) ?>" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="country" class="form-label">Ülke <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="country" name="country" value="<?= htmlspecialchars($listingData['country']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Konum (Haritada İşaretleyin) <span class="text-danger">*</span></label>
                        <div id="map-container"></div>
                        <input type="hidden" id="latitude" name="latitude" value="<?= $listingData['latitude'] ?>" required>
                        <input type="hidden" id="longitude" name="longitude" value="<?= $listingData['longitude'] ?>" required>
                    </div>
                    
                    <h5 class="mb-3 mt-4">Multimedya</h5>
                    
                    <div class="mb-3">
                        <label for="video_url" class="form-label">Video URL (Youtube/Vimeo)</label>
                        <input type="url" class="form-control" id="video_url" name="video_url" value="<?= htmlspecialchars($multimedia['video_url'] ?? '') ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="virtual_tour" class="form-label">Sanal Tur URL</label>
                        <input type="url" class="form-control" id="virtual_tour" name="virtual_tour" value="<?= htmlspecialchars($multimedia['virtual_tour'] ?? '') ?>">
                    </div>
                    
                    <h5 class="mb-3 mt-4">Özellikler</h5>
                    
                    <div class="mb-3">
                        <label class="form-label d-block">İç Özellikler</label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check feature-checkbox">
                                    <input class="form-check-input" type="checkbox" name="interior_features[]" value="Klima" id="feature_klima" <?= in_array('Klima', $features['İç Özellikler'] ?? []) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="feature_klima">Klima</label>
                                </div>
                                <div class="form-check feature-checkbox">
                                    <input class="form-check-input" type="checkbox" name="interior_features[]" value="Merkezi Isıtma" id="feature_isitma" <?= in_array('Merkezi Isıtma', $features['İç Özellikler'] ?? []) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="feature_isitma">Merkezi Isıtma</label>
                                </div>
                                <div class="form-check feature-checkbox">
                                    <input class="form-check-input" type="checkbox" name="interior_features[]" value="Ankastre Mutfak" id="feature_ankastre" <?= in_array('Ankastre Mutfak', $features['İç Özellikler'] ?? []) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="feature_ankastre">Ankastre Mutfak</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check feature-checkbox">
                                    <input class="form-check-input" type="checkbox" name="interior_features[]" value="Akıllı Ev Sistemi" id="feature_akilli_ev" <?= in_array('Akıllı Ev Sistemi', $features['İç Özellikler'] ?? []) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="feature_akilli_ev">Akıllı Ev Sistemi</label>
                                </div>
                                <div class="form-check feature-checkbox">
                                    <input class="form-check-input" type="checkbox" name="interior_features[]" value="Güvenlik Sistemi" id="feature_guvenlik" <?= in_array('Güvenlik Sistemi', $features['İç Özellikler'] ?? []) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="feature_guvenlik">Güvenlik Sistemi</label>
                                </div>
                                <div class="form-check feature-checkbox">
                                    <input class="form-check-input" type="checkbox" name="interior_features[]" value="İnternet Bağlantısı" id="feature_internet" <?= in_array('İnternet Bağlantısı', $features['İç Özellikler'] ?? []) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="feature_internet">İnternet Bağlantısı</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check feature-checkbox">
                                    <input class="form-check-input" type="checkbox" name="interior_features[]" value="Ebeveyn Banyosu" id="feature_ebeveyn" <?= in_array('Ebeveyn Banyosu', $features['İç Özellikler'] ?? []) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="feature_ebeveyn">Ebeveyn Banyosu</label>
                                </div>
                                <div class="form-check feature-checkbox">
                                    <input class="form-check-input" type="checkbox" name="interior_features[]" value="Giyinme Odası" id="feature_giyinme" <?= in_array('Giyinme Odası', $features['İç Özellikler'] ?? []) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="feature_giyinme">Giyinme Odası</label>
                                </div>
                                <div class="form-check feature-checkbox">
                                    <input class="form-check-input" type="checkbox" name="interior_features[]" value="Beyaz Eşya" id="feature_beyaz_esya" <?= in_array('Beyaz Eşya', $features['İç Özellikler'] ?? []) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="feature_beyaz_esya">Beyaz Eşya</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label d-block">Dış Özellikler</label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check feature-checkbox">
                                    <input class="form-check-input" type="checkbox" name="exterior_features[]" value="Bahçe" id="feature_bahce" <?= in_array('Bahçe', $features['Dış Özellikler'] ?? []) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="feature_bahce">Bahçe</label>
                                </div>
                                <div class="form-check feature-checkbox">
                                    <input class="form-check-input" type="checkbox" name="exterior_features[]" value="Havuz" id="feature_havuz" <?= in_array('Havuz', $features['Dış Özellikler'] ?? []) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="feature_havuz">Havuz</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check feature-checkbox">
                                    <input class="form-check-input" type="checkbox" name="exterior_features[]" value="Otopark" id="feature_otopark" <?= in_array('Otopark', $features['Dış Özellikler'] ?? []) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="feature_otopark">Otopark</label>
                                </div>
                                <div class="form-check feature-checkbox">
                                    <input class="form-check-input" type="checkbox" name="exterior_features[]" value="Teras" id="feature_teras" <?= in_array('Teras', $features['Dış Özellikler'] ?? []) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="feature_teras">Teras</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check feature-checkbox">
                                    <input class="form-check-input" type="checkbox" name="exterior_features[]" value="Güvenlik" id="feature_guvenlik_dis" <?= in_array('Güvenlik', $features['Dış Özellikler'] ?? []) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="feature_guvenlik_dis">Güvenlik</label>
                                </div>
                                <div class="form-check feature-checkbox">
                                    <input class="form-check-input" type="checkbox" name="exterior_features[]" value="Asansör" id="feature_asansor" <?= in_array('Asansör', $features['Dış Özellikler'] ?? []) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="feature_asansor">Asansör</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label d-block">Çevre Özellikleri</label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check feature-checkbox">
                                    <input class="form-check-input" type="checkbox" name="env_features[]" value="Okula Yakın" id="feature_okul" <?= in_array('Okula Yakın', $features['Çevre Özellikleri'] ?? []) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="feature_okul">Okula Yakın</label>
                                </div>
                                <div class="form-check feature-checkbox">
                                    <input class="form-check-input" type="checkbox" name="env_features[]" value="Toplu Taşımaya Yakın" id="feature_toplu_tasima" <?= in_array('Toplu Taşımaya Yakın', $features['Çevre Özellikleri'] ?? []) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="feature_toplu_tasima">Toplu Taşımaya Yakın</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check feature-checkbox">
                                    <input class="form-check-input" type="checkbox" name="env_features[]" value="Markete Yakın" id="feature_market" <?= in_array('Markete Yakın', $features['Çevre Özellikleri'] ?? []) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="feature_market">Markete Yakın</label>
                                </div>
                                <div class="form-check feature-checkbox">
                                    <input class="form-check-input" type="checkbox" name="env_features[]" value="Hastaneye Yakın" id="feature_hastane" <?= in_array('Hastaneye Yakın', $features['Çevre Özellikleri'] ?? []) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="feature_hastane">Hastaneye Yakın</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check feature-checkbox">
                                    <input class="form-check-input" type="checkbox" name="env_features[]" value="Denize Yakın" id="feature_deniz" <?= in_array('Denize Yakın', $features['Çevre Özellikleri'] ?? []) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="feature_deniz">Denize Yakın</label>
                                </div>
                                <div class="form-check feature-checkbox">
                                    <input class="form-check-input" type="checkbox" name="env_features[]" value="Şehir Merkezine Yakın" id="feature_merkez" <?= in_array('Şehir Merkezine Yakın', $features['Çevre Özellikleri'] ?? []) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="feature_merkez">Şehir Merkezine Yakın</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <h5 class="mb-3 mt-4">Yakın Çevre Mesafeleri</h5>
                    
                    <div id="distances-container">
                        <?php if (!empty($distances)): ?>
                            <?php foreach ($distances as $place => $distance): ?>
                                <div class="distance-row row mb-3">
                                    <div class="col-md-5">
                                        <input type="text" class="form-control" name="distance_name[]" value="<?= htmlspecialchars($place) ?>" placeholder="Mekan Adı (örn. Metro)">
                                    </div>
                                    <div class="col-md-5">
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="distance_value[]" value="<?= htmlspecialchars($distance) ?>" placeholder="Mesafe" step="0.1" min="0">
                                            <span class="input-group-text">km</span>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger w-100 remove-distance">Sil</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
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
                        <button type="button" class="btn btn-success" id="add-distance">
                            <i class="bi bi-plus-circle"></i> Yeni Mesafe Ekle
                        </button>
                    </div>
                </div>
                
                <!-- Sağ Kolon -->
                <div class="col-lg-4">
                    <h5 class="mb-3">Görsel Galerisi</h5>
                    
                    <div class="card mb-3">
                        <div class="card-body">
                            <!-- Mevcut Görseller -->
                            <?php if (!empty($images)): ?>
                                <div class="mb-4">
                                    <label class="form-label">Mevcut Görseller</label>
                                    <div class="image-preview-container">
                                        <?php foreach ($images as $img): ?>
                                            <div class="image-preview" data-image-id="<?= $img['id'] ?>">
                                                <img src="<?= htmlspecialchars($img['image_url']) ?>" alt="">
                                                <?php if ($img['is_main']): ?>
                                                    <span class="main-image-label">Ana</span>
                                                <?php endif; ?>
                                                <button type="button" class="delete-btn delete-existing-image">×</button>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <label for="main-image-select" class="form-label">Ana Görsel Seç</label>
                                        <select class="form-select" id="main-image-select" name="main_image">
                                            <?php foreach ($images as $img): ?>
                                                <option value="<?= $img['id'] ?>" <?= $img['is_main'] ? 'selected' : '' ?>>
                                                    Görsel <?= $img['id'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <!-- Silinen görselleri saklamak için gizli alan -->
                                    <input type="hidden" name="deleted_images" id="deleted-images-input" value="">
                                </div>
                            <?php endif; ?>
                            
                            <!-- Yeni Görseller -->
                            <div class="mb-3">
                                <label for="new-images" class="form-label">Yeni Görseller Ekle</label>
                                <div class="input-group custom-file-button">
                                    <input type="file" class="form-control" id="new-images" name="new_images[]" accept="image/jpeg,image/png,image/jpg" multiple>
                                    <label class="input-group-text" for="new-images">Gözat</label>
                                </div>
                                <div class="form-text">Kabul edilen formatlar: JPG, JPEG, PNG</div>
                            </div>
                            
                            <div id="new-image-previews" class="image-preview-container">
                                <!-- JavaScript ile yeni görsel önizlemeleri burada gösterilecek -->
                            </div>
                            
                            <div class="mt-3" id="new-main-image-container" style="display: none;">
                                <label for="new-main-image-select" class="form-label">Yeni Ana Görsel</label>
                                <select class="form-select" id="new-main-image-select" name="main_image_index">
                                    <option value="-1">Ana görsel seçilmedi</option>
                                </select>
                                <div class="form-text">Yüklenen yeni görsellerden ana görsel seçebilirsiniz</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 d-flex justify-content-between">
                <a href="index.php" class="btn btn-secondary">İptal</a>
                <button type="submit" class="btn btn-primary">Değişiklikleri Kaydet</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Silinen görselleri izleme
        const deletedImages = [];
        const deletedImagesInput = document.getElementById('deleted-images-input');
        
        // Görsel silme işlemi
        document.querySelectorAll('.delete-existing-image').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const preview = this.closest('.image-preview');
                const imageId = preview.getAttribute('data-image-id');
                
                // Silinecek ID'yi ekle
                deletedImages.push(imageId);
                deletedImagesInput.value = deletedImages.join(',');
                
                // Önizlemeyi gizle
                preview.style.display = 'none';
                
                // Ana görsel seçimi güncellemesi
                const mainImageSelect = document.getElementById('main-image-select');
                const option = mainImageSelect.querySelector(`option[value="${imageId}"]`);
                
                if (option) {
                    option.disabled = true;
                    
                    // Eğer silinen görsel ana görsel ise, başka bir görsel seç
                    if (option.selected) {
                        const availableOptions = Array.from(mainImageSelect.options).filter(opt => !opt.disabled);
                        if (availableOptions.length > 0) {
                            availableOptions[0].selected = true;
                        }
                    }
                }
            });
        });
        
        // Yeni görsel önizleme
        const newImageInput = document.getElementById('new-images');
        const newImagePreviews = document.getElementById('new-image-previews');
        const newMainImageSelect = document.getElementById('new-main-image-select');
        const newMainImageContainer = document.getElementById('new-main-image-container');
        
        newImageInput.addEventListener('change', function() {
            newImagePreviews.innerHTML = '';
            newMainImageSelect.innerHTML = '<option value="-1">Ana görsel seçilmedi</option>';
            
            if (this.files.length > 0) {
                newMainImageContainer.style.display = 'block';
                
                for (let i = 0; i < this.files.length; i++) {
                    const file = this.files[i];
                    
                    // Görsel önizleme
                    const preview = document.createElement('div');
                    preview.className = 'image-preview';
                    
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    img.onload = function() {
                        URL.revokeObjectURL(this.src);
                    }
                    
                    preview.appendChild(img);
                    newImagePreviews.appendChild(preview);
                    
                    // Ana görsel seçeneği ekle
                    const option = document.createElement('option');
                    option.value = i;
                    option.textContent = `Yeni Görsel ${i + 1}: ${file.name}`;
                    newMainImageSelect.appendChild(option);
                }
            } else {
                newMainImageContainer.style.display = 'none';
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
        const map = L.map('map-container').setView([<?= $listingData['latitude'] ?>, <?= $listingData['longitude'] ?>], 15);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 18
        }).addTo(map);
        
        const marker = L.marker([<?= $listingData['latitude'] ?>, <?= $listingData['longitude'] ?>]).addTo(map);
        
        map.on('click', function(e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
            
            marker.setLatLng(e.latlng);
        });
    });
</script>

<?php require_once '../templates/footer.php'; ?>