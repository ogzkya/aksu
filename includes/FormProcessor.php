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
                     // Yeni yüklenenler arasından ana görsel seçildiyse onu ayarla
                     $newMainImageIndex = isset($postData['main_image_index']) ? (int)$postData['main_image_index'] : -1;
                     if ($newMainImageIndex >= 0 && isset($uploadResult['files'][$newMainImageIndex])) {
                        // Yeni yüklenen görselin ID'sini bulmamız lazım (bu biraz zor olabilir direkt)
                        // Alternatif: Yükleme sonrası son ID'yi alıp, ona göre ayarlama veya URL ile bulma.
                        // Şimdilik: addListingImage'in is_main parametresini kullanıyoruz.
                        // Eğer ImageUploader direkt ID döndürmüyorsa, bu mantık FormProcessor yerine
                        // add.php/edit.php içinde dosya yükleme sonrası yapılmalı.

                        // Geçici Çözüm: Yüklenen ilk görseli ana yapalım (eğer $newMainImageIndex 0 ise)
                        // ve diğer mevcutları is_main=0 yapalım.
                         if ($newMainImageIndex === 0) {
                             $newImageUrl = $uploadResult['files'][0];
                             // Bu URL ile eşleşen image ID'sini bul ve is_main yap
                             $newImageId = $listingObj->db->fetch("SELECT id FROM listing_images WHERE image_url = ? AND listing_id = ? ORDER BY id DESC LIMIT 1", [$newImageUrl, $listingId]);
                             if($newImageId) {
                                 $listingObj->setMainImage($listingId, $newImageId['id']);
                             }
                         }
                     } elseif (!$isEditMode || !$hasExistingImages) {
                          // Eğer yeni ilansa veya hiç mevcut görsel yoksa ve yeni görsel yüklendiyse, ilkini ana yap
                          if (!empty($uploadResult['files'])) {
                              $firstImageUrl = $uploadResult['files'][0];
                              $firstImageId = $listingObj->db->fetch("SELECT id FROM listing_images WHERE image_url = ? AND listing_id = ? ORDER BY id DESC LIMIT 1", [$firstImageUrl, $listingId]);
                              if($firstImageId) {
                                  $listingObj->setMainImage($listingId, $firstImageId['id']);
                              }
                          }
                     }
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
}