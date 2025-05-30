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
     * @param ImageUploader $imageUploader ImageUploader sınıfı nesnesi (Güncellendi)
     * @param int|null $listingId Düzenleme için ilan ID (yeni ilan için null)
     * @return array İşlem sonucu [success, errors, listingId]
     */
    public function processListingForm($postData, $files, $listingObj, $imageUploader, $listingId = null) { // ImageUploader kullanıldı
        $this->errors = [];
        $isEditMode = ($listingId !== null);

        try {
            // *** GÜNCELLENMİŞ FİYAT İŞLEME ***
            $listingType = $postData['listing_type'] ?? 'sale';
            $salePriceInput = $postData['sale_price'] ?? '';
            $rentPriceInput = $postData['rent_price'] ?? '';
            $salePrice = null;
            $rentPrice = null;

            // Satış fiyatını işle
            if ($listingType == 'sale' || $listingType == 'both') {
                // Sadece pozitif sayısal değerleri kabul et, yoksa NULL
                if (is_numeric($salePriceInput) && $salePriceInput > 0) {
                    $salePrice = (float)$salePriceInput;
                }
            }

            // Kira fiyatını işle
             if ($listingType == 'rent' || $listingType == 'both') {
                 // Sadece pozitif sayısal değerleri kabul et, yoksa NULL
                if (is_numeric($rentPriceInput) && $rentPriceInput > 0) {
                    $rentPrice = (float)$rentPriceInput;
                }
            }

            // Zorunlu fiyat kontrolleri
            if ($listingType == 'sale' && $salePrice === null) {
                $this->errors[] = 'Satılık ilan için geçerli bir satış fiyatı girilmelidir.';
            } elseif ($listingType == 'rent' && $rentPrice === null) {
                $this->errors[] = 'Kiralık ilan için geçerli bir kira fiyatı girilmelidir.';
            } elseif ($listingType == 'both' && $salePrice === null && $rentPrice === null) {
                 $this->errors[] = '"Hem Satılık Hem Kiralık" ilan için en az bir geçerli fiyat (satış veya kira) girilmelidir.';
            }
             // *** /GÜNCELLENMİŞ FİYAT İŞLEME ***


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
                'zip' => 'Posta Kodu',
                'category' => 'Kategori' // Kategori de zorunlu olmalı
            ];

            foreach ($requiredFields as $field => $label) {
                if (empty($postData[$field])) {
                    $this->errors[] = $label . ' alanı gereklidir.';
                }
            }

            // Harita koordinatları kontrolü
            $latitude = $postData['latitude'] ?? 0;
            $longitude = $postData['longitude'] ?? 0;

            if (empty($latitude) || empty($longitude) || !is_numeric($latitude) || !is_numeric($longitude) || ($latitude == 0 && $longitude == 0)) {
                 // Not: Başlangıç merkezi 0,0 olabilir, daha spesifik bir kontrol gerekebilir.
                 // Şimdilik boş veya 0 olmamasını kontrol ediyoruz.
                $this->errors[] = 'Lütfen haritada geçerli bir konum seçin.';
            }


            // Görsel yükleme doğrulamasını iyileştir
            $imageFieldName = $isEditMode ? 'new_images' : 'images'; // Düzenlemede 'new_images', eklemede 'images'
            $hasExistingImages = $isEditMode && !empty($listingObj->getListingImages($listingId));
            $hasNewFiles = isset($files[$imageFieldName]) && !empty($files[$imageFieldName]['name'][0]);

            if (!$isEditMode && !$hasNewFiles) { // Yeni ilanda hiç dosya seçilmediyse
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
                'short_description' => $postData['short_description'] ?? null,
                'sale_price' => $salePrice, // Güncellenmiş fiyat
                'rent_price' => $rentPrice, // Güncellenmiş fiyat
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
                'keywords' => $postData['keywords'] ?? null,
                'featured' => isset($postData['featured']) ? 1 : 0,
                'agent_id' => !empty($postData['agent_id']) ? (int)$postData['agent_id'] : null,
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
                if (!empty($distanceNames[$i]) && isset($distanceValues[$i]) && is_numeric($distanceValues[$i]) && $distanceValues[$i] >= 0) {
                     $listingData['distances'][trim($distanceNames[$i])] = (float)$distanceValues[$i];
                }
            }


            // Özellikleri işle
            if (!empty($postData['interior_features']) && is_array($postData['interior_features'])) {
                $listingData['features']['İç Özellikler'] = $postData['interior_features'];
            }
            if (!empty($postData['exterior_features']) && is_array($postData['exterior_features'])) {
                $listingData['features']['Dış Özellikler'] = $postData['exterior_features'];
            }
            if (!empty($postData['env_features']) && is_array($postData['env_features'])) {
                 $listingData['features']['Çevre Özellikleri'] = $postData['env_features'];
            }

            // İlanı ekle veya güncelle
            if ($isEditMode) {
                $listingObj->updateListing($listingId, $listingData);
            } else {
                $listingId = $listingObj->addListing($listingData);

                if (!$listingId) {
                    throw new Exception("İlan eklenirken bir veritabanı hatası oluştu.");
                }
            }

            // Görsel işlemleri (Düzenleme Modu)
            if ($isEditMode) {
                 // Silinen görselleri işle
                if (!empty($postData['deleted_images'])) {
                    $deletedImages = explode(',', $postData['deleted_images']);
                    foreach ($deletedImages as $imgId) {
                        if (is_numeric(trim($imgId))) {
                            $listingObj->deleteListingImage((int)trim($imgId));
                        }
                    }
                 }

                 // Ana görsel güncelleme (Mevcut görseller arasından)
                 if (isset($postData['main_image']) && is_numeric($postData['main_image'])) {
                     $mainImageId = (int)$postData['main_image'];
                     if ($mainImageId > 0) { // Eğer geçerli bir ID seçilmişse
                         $listingObj->setMainImage($listingId, $mainImageId);
                     }
                 }
            }


            // Yeni görselleri işle (ImageUploader kullanarak)
            if ($hasNewFiles) {
                $uploadResult = $imageUploader->uploadMultiple($files[$imageFieldName], $listingId);

                if (!$uploadResult['success']) {
                    // Yükleme hatalarını ana hatalara ekle
                     $this->errors = array_merge($this->errors, $uploadResult['errors']);
                } else {
                    // *** YENİ KOD BAŞLANGICI: Başarılı yüklenen her resmi DB'ye ekle ***
                    $isFirstNewImage = true; // İlk yeni resmi işaretlemek için
                    // Düzenleme modunda mevcut resim sayısını al (yeni eklenen ilk resmi ana yapmak için)
                    $existingImagesData = [];
                    if ($isEditMode) {
                        $existingImagesData = $listingObj->getListingImages($listingId);
                         // Silinmesi istenen resimleri çıkardıktan sonraki durumu hesaba katmak daha doğru olabilir,
                         // ancak şimdilik mevcut durumu alalım. Silme işlemi zaten yapıldı.
                    }
                    $existingImagesCount = count($existingImagesData);

                    // Yeni yüklenen resimleri döngüye al
                    foreach ($uploadResult['files'] as $index => $uploadedUrl) {
                        // Bu görselin ana görsel olup olmadığını belirle
                        $isMain = 0;
                        // Formdan gelen YENİ ana resim seçimi (genellikle select/radio ile, index'e göre)
                        $newMainImageIndex = isset($postData['main_image_index']) ? (int)$postData['main_image_index'] : -1;
                        // Formdan gelen MEVCUT ana resim seçimi (ID'ye göre)
                        $existingMainImageId = isset($postData['main_image']) ? (int)$postData['main_image'] : 0;

                        if ($newMainImageIndex === $index) {
                            // Eğer kullanıcı bu YENİ resmi ana olarak seçtiyse
                            $isMain = 1;
                        } elseif (!$isEditMode && $isFirstNewImage) {
                            // Eğer YENİ ilan ekleniyorsa ve bu İLK yüklenen resimse
                            $isMain = 1;
                        } elseif ($isEditMode && $existingImagesCount === 0 && $isFirstNewImage) {
                            // Eğer DÜZENLEME modunda, HİÇ mevcut resim yoksa (veya hepsi silinmişse)
                            // ve bu İLK yüklenen YENİ resimse
                            $isMain = 1;
                        }

                        // Resmi veritabanına ekle
                        try {
                            // $listingObj, Listing sınıfının bir örneği olmalı
                            $newImageDbId = $listingObj->addListingImage($listingId, $uploadedUrl, $isMain);

                            // Eğer bu resim ana olarak ayarlandıysa veya seçildiyse,
                            // diğerlerinin 'is_main'ini 0 yapalım (güvenlik için)
                            // ve bu yeni eklenen ID'yi ana resim ID'si olarak kullanalım.
                            if ($isMain == 1 && $newImageDbId) {
                                $listingObj->setMainImage($listingId, $newImageDbId);
                                // Mevcut resimlerden birinin ana seçilme ihtimalini ortadan kaldır
                                $existingMainImageId = 0;
                            }

                        } catch (Exception $dbError) {
                            // Veritabanı ekleme hatasını logla veya kullanıcıya bildir
                            $this->errors[] = "Görsel veritabanına eklenirken hata oluştu ('" . basename($uploadedUrl) . "'): " . $dbError->getMessage();
                        }
                        $isFirstNewImage = false; // İlk resimden sonra bayrağı sıfırla
                    }
                    // *** YENİ KOD BİTİŞİ ***

                    // --- Mevcut ana resim seçimi kontrolü ---
                    // Kullanıcı MEVCUT bir resmi ana olarak seçmişse VE YENİ bir resmi ana olarak seçmemişse
                    if ($isEditMode && $existingMainImageId > 0 && $newMainImageIndex < 0) {
                        // Seçilen mevcut ID'nin hala var olduğunu ve silinmediğini kontrol etmek iyi olur,
                        // ancak setMainImage fonksiyonu bunu zaten ID ile yapmalı.
                        $listingObj->setMainImage($listingId, $existingMainImageId);
                    }
                    // --- Mevcut ana resim seçimi kontrolü bitişi ---
                }
            }


        } catch (Exception $e) {
            $this->errors[] = "İşlem sırasında bir hata oluştu: " . $e->getMessage();
            // Hata durumunda ID null olabilir
            return [
                'success' => false,
                'errors' => $this->errors,
                'listingId' => $listingId
            ];
        }

        // Hataları kontrol et
        $finalSuccess = empty($this->errors);

        return [
            'success' => $finalSuccess,
            'errors' => $this->errors,
            'listingId' => $listingId // ID her zaman döndürülür
        ];
    }

    public function processListingFormOld($post, $files, $listing, $imageUploader) {
        $errors = [];
        
        // Kategori kontrolü
        $category = $post['category'] ?? '';
        $isLand = ($category === 'Land');
        
        // Temel alanlar
        if (empty($post['title'])) $errors[] = 'Başlık gereklidir.';
        if (empty($post['description'])) $errors[] = 'Açıklama gereklidir.';
        if (empty($post['category'])) $errors[] = 'Kategori gereklidir.';
        if (empty($post['property_size'])) $errors[] = 'Alan bilgisi gereklidir.';
        
        // Arsa değilse oda/banyo/kat zorunlu
        if (!$isLand) {
            if (empty($post['rooms']) || $post['rooms'] <= 0) $errors[] = 'Oda sayısı gereklidir.';
            if (empty($post['bathrooms']) || $post['bathrooms'] <= 0) $errors[] = 'Banyo sayısı gereklidir.';
            if (empty($post['floors_no']) || $post['floors_no'] <= 0) $errors[] = 'Kat sayısı gereklidir.';
        }
        
        // Fiyat kontrolü
        $listingType = $post['listing_type'] ?? 'sale';
        if ($listingType === 'sale' || $listingType === 'both') {
            if (empty($post['sale_price']) || $post['sale_price'] <= 0) {
                $errors[] = 'Satış fiyatı gereklidir.';
            }
        }
        if ($listingType === 'rent' || $listingType === 'both') {
            if (empty($post['rent_price']) || $post['rent_price'] <= 0) {
                $errors[] = 'Kira fiyatı gereklidir.';
            }
        }
        
        // Konum kontrolü
        if (empty($post['street'])) $errors[] = 'Sokak/Cadde gereklidir.';
        if (empty($post['city'])) $errors[] = 'Şehir gereklidir.';
        if (empty($post['state'])) $errors[] = 'İl/Bölge gereklidir.';
        if (empty($post['latitude']) || empty($post['longitude'])) {
            $errors[] = 'Harita üzerinden konum seçimi gereklidir.';
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors, 'listingId' => null];
        }
        
        // Arsa için varsayılan değerler
        if ($isLand) {
            $post['rooms'] = 0;
            $post['bathrooms'] = 0;
            $post['floors_no'] = 1;
            $post['garages'] = 0;
            $post['year_built'] = null;
            $post['energy_efficiency'] = null;
        }
        
        // Veri hazırlama
        $listingData = [
            'title' => $post['title'],
            'description' => $post['description'],
            'short_description' => $post['short_description'] ?? null,
            'keywords' => $post['keywords'] ?? null,
            'category' => $post['category'],
            'listing_type' => $listingType,
            'sale_price' => !empty($post['sale_price']) ? (float)$post['sale_price'] : null,
            'rent_price' => !empty($post['rent_price']) ? (float)$post['rent_price'] : null,
            'property_size' => (float)$post['property_size'],
            'property_lot_size' => !empty($post['property_lot_size']) ? (float)$post['property_lot_size'] : null,
            'rooms' => (int)$post['rooms'],
            'bathrooms' => (int)$post['bathrooms'],
            'floors_no' => (int)$post['floors_no'],
            'garages' => (int)($post['garages'] ?? 0),
            'year_built' => !empty($post['year_built']) ? (int)$post['year_built'] : null,
            'energy_efficiency' => $post['energy_efficiency'] ?? null,
            'street' => $post['street'],
            'zip' => $post['zip'] ?? null,
            'city' => $post['city'],
            'state' => $post['state'],
            'country' => $post['country'] ?? 'Türkiye',
            'latitude' => (float)$post['latitude'],
            'longitude' => (float)$post['longitude'],
            'video_url' => $post['video_url'] ?? null,
            'virtual_tour' => $post['virtual_tour'] ?? null,
            'featured' => isset($post['featured']) ? 1 : 0,
            'agent_id' => !empty($post['agent_id']) ? (int)$post['agent_id'] : null,
            'status' => 'active'
        ];
        
        // --- Mevcut ilanı güncelle
        if ($listing) {
            // İlanı güncelle
            $listingObj->updateListing($listing['id'], $listingData);
            $listingId = $listing['id'];
        } else {
            // --- Yeni ilan ekle
            // Yeni ilanı ekle
            $listingId = $listingObj->addListing($listingData);
        }
        
        // Hata kontrolü
        if (!$listingId) {
            return ['success' => false, 'errors' => ['İlan kaydedilemedi.'], 'listingId' => null];
        }
        
        // --- Görsel yükleme
        if ($hasNewFiles) {
            $uploadResult = $imageUploader->uploadMultiple($files[$imageFieldName], $listingId);
            
            if (!$uploadResult['success']) {
                // Yükleme hatalarını döndür
                return ['success' => false, 'errors' => $uploadResult['errors'], 'listingId' => $listingId];
            }
            
            // Yüklenen görsellerle ilgili işlemler
            foreach ($uploadResult['files'] as $index => $uploadedUrl) {
                // Ana görsel belirleme
                $isMain = ($index === 0) ? 1 : 0; // İlk yüklenen görsel ana görsel olsun
                
                // Veritabanına ekle
                $listingObj->addListingImage($listingId, $uploadedUrl, $isMain);
            }
        }
        
        return ['success' => true, 'errors' => [], 'listingId' => $listingId];
    }
}