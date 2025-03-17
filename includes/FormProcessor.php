<?php
/**
 * İlan formunu işleyen yardımcı sınıf
 * includes/FormProcessor.php dosyasına ekleyin
 */
class FormProcessor {
    private $errors = [];
    
    /**
     * İlan formunu doğrula ve işle
     * 
     * @param array $postData $_POST verisi
     * @param array $files $_FILES verisi
     * @param Listing $listingObj Listing sınıfı nesnesi
     * @param Image $imageObj Image sınıfı nesnesi
     * @param int|null $listingId Düzenleme için ilan ID (yeni ilan için null)
     * @return array İşlem sonucu [success, errors, listingId]
     */
    public function processListingForm($postData, $files, $listingObj, $imageObj, $listingId = null) {
        $this->errors = [];
        $isEditMode = ($listingId !== null);
        
        try {
            // İlan tipi ve fiyat kontrolü
            $listingType = $postData['listing_type'] ?? 'sale';
            $salePrice = 0;
            $rentPrice = null;
            
            if ($listingType == 'sale' || $listingType == 'both') {
                $salePrice = !empty($postData['sale_price']) ? (float)$postData['sale_price'] : 0;
                if ($salePrice <= 0) {
                    $this->errors[] = 'Geçerli bir satış fiyatı girilmelidir.';
                }
            }
            
            if ($listingType == 'rent' || $listingType == 'both') {
                $rentPrice = !empty($postData['rent_price']) ? (float)$postData['rent_price'] : 0;
                if ($rentPrice <= 0) {
                    $this->errors[] = 'Geçerli bir kira fiyatı girilmelidir.';
                }
            }
            
            // Zorunlu alanları kontrol et
            $requiredFields = [
                'title' => 'Başlık',
                'description' => 'Açıklama',
                'property_size' => 'Alan (m²)',
                'rooms' => 'Oda Sayısı',
                'bathrooms' => 'Banyo Sayısı',
                'floors_no' => 'Kat Sayısı',
                'city' => 'Şehir',
                'state' => 'İl/Bölge',
                'country' => 'Ülke',
                'street' => 'Sokak/Cadde',
                'zip' => 'Posta Kodu'
            ];
            
            foreach ($requiredFields as $field => $label) {
                if (empty($postData[$field])) {
                    $this->errors[] = $label . ' alanı gereklidir.';
                }
            }
            
            // Harita koordinatları kontrolü
            $latitude = $postData['latitude'] ?? 0;
            $longitude = $postData['longitude'] ?? 0;
            
            if (empty($latitude) || empty($longitude) || $latitude == 0 || $longitude == 0) {
                $this->errors[] = 'Lütfen haritada bir konum seçin.';
            }
            
            // Dosya yükleme doğrulamasını iyileştir (yeni ilan ekleme için)
            $fieldName = $isEditMode ? 'new_images' : 'images';
            if (!$isEditMode && (!isset($files[$fieldName]) || empty($files[$fieldName]['name'][0]))) {
                $this->errors[] = 'En az bir görsel yüklemeniz gerekmektedir.';
            }
            
            // Hata varsa işlemi sonlandır
            if (!empty($this->errors)) {
                return [
                    'success' => false,
                    'errors' => $this->errors,
                    'listingId' => $listingId
                ];
            }
            
            // Form verilerini birleştir
            $listingData = [
                'title' => $postData['title'] ?? '',
                'description' => $postData['description'] ?? '',
                'short_description' => $postData['short_description'] ?? '',
                'sale_price' => $salePrice,
                'rent_price' => $rentPrice,
                'property_size' => $postData['property_size'] ?? 0,
                'rooms' => $postData['rooms'] ?? 0,
                'bathrooms' => $postData['bathrooms'] ?? 0,
                'floors_no' => $postData['floors_no'] ?? 0,
                'garages' => $postData['garages'] ?? 0,
                'energy_efficiency' => $postData['energy_efficiency'] ?? null,
                'year_built' => !empty($postData['year_built']) ? $postData['year_built'] : null,
                'property_lot_size' => !empty($postData['property_lot_size']) ? $postData['property_lot_size'] : null,
                'category' => $postData['category'] ?? 'Other',
                'latitude' => $latitude,
                'longitude' => $longitude,
                'city' => $postData['city'] ?? '',
                'state' => $postData['state'] ?? '',
                'country' => $postData['country'] ?? '',
                'street' => $postData['street'] ?? '',
                'zip' => $postData['zip'] ?? '',
                'keywords' => $postData['keywords'] ?? '',
                'featured' => isset($postData['featured']) ? 1 : 0,
                'multimedia' => [
                    'video_url' => $postData['video_url'] ?? '',
                    'virtual_tour' => $postData['virtual_tour'] ?? ''
                ],
                'distances' => [],
                'features' => [
                    'İç Özellikler' => [],
                    'Dış Özellikler' => [],
                    'Çevre Özellikleri' => []
                ]
            ];
            
            // Mesafeleri işle
            $distanceNames = $postData['distance_name'] ?? [];
            $distanceValues = $postData['distance_value'] ?? [];
            
            for ($i = 0; $i < count($distanceNames); $i++) {
                if (!empty($distanceNames[$i]) && isset($distanceValues[$i])) {
                    $listingData['distances'][$distanceNames[$i]] = $distanceValues[$i];
                }
            }
            
            // Özellikleri işle
            if (isset($postData['interior_features'])) {
                $listingData['features']['İç Özellikler'] = $postData['interior_features'];
            }
            
            if (isset($postData['exterior_features'])) {
                $listingData['features']['Dış Özellikler'] = $postData['exterior_features'];
            }
            
            if (isset($postData['env_features'])) {
                $listingData['features']['Çevre Özellikleri'] = $postData['env_features'];
            }
            
            // İlanı ekle veya güncelle
            if ($isEditMode) {
                $listingObj->updateListing($listingId, $listingData);
            } else {
                $listingId = $listingObj->addListing($listingData);
                
                if (!$listingId) {
                    throw new Exception("İlan eklenirken bir hata oluştu.");
                }
            }
            
            // Görsel yükleme işlemleri (düzenleme modunda varolan görseller üzerinde değişiklik)
            if ($isEditMode) {
                // Ana görsel güncelleme
                if (isset($postData['main_image']) && is_numeric($postData['main_image'])) {
                    $mainImageId = (int)$postData['main_image'];
                    $images = $listingObj->getListingImages($listingId);
                    
                    foreach ($images as $img) {
                        if ($img['id'] == $mainImageId) {
                            // Ana görsel olarak işaretle
                            $listingObj->addListingImage($listingId, $img['image_url'], 1);
                            break;
                        }
                    }
                }
                
                // Silinen görselleri işle
                if (isset($postData['deleted_images']) && !empty($postData['deleted_images'])) {
                    $deletedImages = explode(',', $postData['deleted_images']);
                    
                    foreach ($deletedImages as $imgId) {
                        if (is_numeric($imgId)) {
                            $listingObj->deleteListingImage((int)$imgId);
                        }
                    }
                }
            }
            
            // Yeni görselleri işle (yeni ilan için "images", düzenleme için "new_images")
            $fieldName = $isEditMode ? 'new_images' : 'images';
            $mainImageIndex = isset($postData['main_image_index']) ? (int)$postData['main_image_index'] : 0;
            
            if (isset($files[$fieldName]) && !empty($files[$fieldName]['name'][0])) {
                for ($i = 0; $i < count($files[$fieldName]['name']); $i++) {
                    if ($files[$fieldName]['error'][$i] === UPLOAD_ERR_OK) {
                        $file = [
                            'name' => $files[$fieldName]['name'][$i],
                            'type' => $files[$fieldName]['type'][$i],
                            'tmp_name' => $files[$fieldName]['tmp_name'][$i],
                            'error' => $files[$fieldName]['error'][$i],
                            'size' => $files[$fieldName]['size'][$i]
                        ];
                        
                        try {
                            $imageUrl = $imageObj->upload($file, $listingId);
                            $listingObj->addListingImage($listingId, $imageUrl, $i == $mainImageIndex ? 1 : 0);
                        } catch (Exception $e) {
                            // Hata logla ama işleme devam et
                            error_log("Görsel yükleme hatası: " . $e->getMessage());
                            $this->errors[] = "Görsel yüklenirken hata: " . $e->getMessage();
                        }
                    }
                }
            }
            
        } catch (Exception $e) {
            $this->errors[] = "İşlem sırasında bir hata oluştu: " . $e->getMessage();
            return [
                'success' => false,
                'errors' => $this->errors,
                'listingId' => $listingId
            ];
        }
        
        return [
            'success' => empty($this->errors),
            'errors' => $this->errors,
            'listingId' => $listingId
        ];
    }
}
