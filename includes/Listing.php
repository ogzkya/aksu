<?php
class Listing {
    public $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Güncellenmiş getAllListings: kiralık/satılık filtreleri ve diğer filtreleme seçenekleri eklendi
    public function getAllListings($limit = 10, $offset = 0, $filters = []) {
        $sql = "SELECT l.*,
                (SELECT image_url FROM listing_images WHERE listing_id = l.id AND is_main = 1 LIMIT 1) as main_image
                FROM listings l WHERE 1=1";
        $params = [];

        // Filtreleme koşulları
        if (!empty($filters)) {
            // Kiralık/Satılık filtresi
            if (isset($filters['listing_type']) && $filters['listing_type']) {
                if ($filters['listing_type'] == 'rent') {
                    $sql .= " AND rent_price IS NOT NULL AND rent_price > 0";
                } else if ($filters['listing_type'] == 'sale') {
                    $sql .= " AND sale_price IS NOT NULL AND sale_price > 0";
                }
                 // Hem satılık hem kiralık durumu için ek bir koşula gerek yok, ikisi de varsa gösterir.
            }

            // Kategori filtresi
            if (isset($filters['category']) && $filters['category']) {
                $sql .= " AND category = ?";
                $params[] = $filters['category'];
            }

             // Fiyat aralığı filtresi - satılık için (listing_type=rent seçilmediyse veya seçilmemişse)
             if ((!isset($filters['listing_type']) || $filters['listing_type'] !== 'rent')) {
                if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
                    $sql .= " AND sale_price >= ?";
                    $params[] = $filters['min_price'];
                }
                if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
                    $sql .= " AND sale_price <= ?";
                    $params[] = $filters['max_price'];
                }
            }

            // Fiyat aralığı filtresi - kiralık için (listing_type=rent seçildiyse)
            if (isset($filters['listing_type']) && $filters['listing_type'] === 'rent') {
                 if (isset($filters['min_rent']) && is_numeric($filters['min_rent'])) {
                    $sql .= " AND rent_price >= ?";
                    $params[] = $filters['min_rent'];
                }
                if (isset($filters['max_rent']) && is_numeric($filters['max_rent'])) {
                    $sql .= " AND rent_price <= ?";
                    $params[] = $filters['max_rent'];
                }
            }


            // Oda sayısı filtresi
            if (isset($filters['rooms']) && is_numeric($filters['rooms'])) {
                 if($filters['rooms'] == 5) { // 5+ seçeneği için
                     $sql .= " AND rooms >= ?";
                 } else {
                     $sql .= " AND rooms = ?";
                 }
                $params[] = $filters['rooms'];
            }

             // Şehir/konum veya başlık filtresi
             if (isset($filters['city']) && !empty($filters['city'])) {
                 $searchTerm = "%{$filters['city']}%";
                 $sql .= " AND (l.title LIKE ? OR l.city LIKE ? OR l.state LIKE ? OR l.street LIKE ?)";
                 $params[] = $searchTerm;
                 $params[] = $searchTerm;
                 $params[] = $searchTerm;
                 $params[] = $searchTerm;
             }
             // Arama filtresi (admin panelinden gelen)
             if (isset($filters['search']) && !empty($filters['search'])) {
                 $searchTerm = "%{$filters['search']}%";
                 $sql .= " AND (l.title LIKE ? OR l.city LIKE ? OR l.state LIKE ?)";
                 $params[] = $searchTerm;
                 $params[] = $searchTerm;
                 $params[] = $searchTerm;
             }

             // Öne çıkanlar filtresi (admin panelinden gelen)
              if (isset($filters['featured']) && $filters['featured']) {
                 $sql .= " AND l.featured = 1";
             }

        }

        $sql .= " ORDER BY featured DESC, created_at DESC LIMIT ?, ?";
        $params[] = (int)$offset;
        $params[] = (int)$limit;

        return $this->db->fetchAll($sql, $params);
    }

    public function getFeaturedListings($limit = 6) {
        $sql = "SELECT l.*,
                (SELECT image_url FROM listing_images WHERE listing_id = l.id AND is_main = 1 LIMIT 1) as main_image
                FROM listings l
                WHERE featured = 1 AND (sale_price > 0 OR rent_price > 0) /* Fiyatı olanları getir */
                ORDER BY created_at DESC
                LIMIT ?";

        return $this->db->fetchAll($sql, [$limit]);
    }

    public function getNewListings($limit = 6) {
        $sql = "SELECT l.*,
                (SELECT image_url FROM listing_images WHERE listing_id = l.id AND is_main = 1 LIMIT 1) as main_image
                FROM listings l
                WHERE (sale_price > 0 OR rent_price > 0) /* Fiyatı olanları getir */
                ORDER BY created_at DESC
                LIMIT ?";

        return $this->db->fetchAll($sql, [$limit]);
    }

    // Yeni: Kiralık ilanlar
    public function getRentListings($limit = 6) {
        $sql = "SELECT l.*,
                (SELECT image_url FROM listing_images WHERE listing_id = l.id AND is_main = 1 LIMIT 1) as main_image
                FROM listings l
                WHERE rent_price IS NOT NULL AND rent_price > 0
                ORDER BY featured DESC, created_at DESC
                LIMIT ?";

        return $this->db->fetchAll($sql, [$limit]);
    }

    // Yeni: Satılık ilanlar
    public function getSaleListings($limit = 6) {
        $sql = "SELECT l.*,
                (SELECT image_url FROM listing_images WHERE listing_id = l.id AND is_main = 1 LIMIT 1) as main_image
                FROM listings l
                WHERE sale_price IS NOT NULL AND sale_price > 0
                ORDER BY featured DESC, created_at DESC
                LIMIT ?";

        return $this->db->fetchAll($sql, [$limit]);
    }

    public function getListingById($id) {
        $sql = "SELECT * FROM listings WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function getListingImages($listingId) {
        $sql = "SELECT * FROM listing_images WHERE listing_id = ? ORDER BY is_main DESC, id ASC"; // ID'ye göre sıralama eklendi
        return $this->db->fetchAll($sql, [$listingId]);
    }

    public function addListing($data) {
        $sql = "INSERT INTO listings (
                title, description, short_description, sale_price, rent_price,
                property_size, rooms, bathrooms, floors_no, garages,
                energy_efficiency, year_built, property_lot_size, category,
                latitude, longitude, city, state, country, street,
                zip, multimedia, keywords, distances, features, featured
            ) VALUES (
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?
            )";

        // JSON verilerini null olarak ayarlama kontrolü
        $multimediaJson = (isset($data['multimedia']) && !empty(array_filter($data['multimedia']))) ? json_encode($data['multimedia']) : null;
        $distancesJson = (isset($data['distances']) && !empty($data['distances'])) ? json_encode($data['distances']) : null;
        $featuresJson = (isset($data['features']) && !empty(array_filter($data['features']))) ? json_encode($data['features']) : null;


        $params = [
            $data['title'],
            $data['description'],
            $data['short_description'] ?? null,
            $data['sale_price'] ?? null, // NULL olabilir
            $data['rent_price'] ?? null, // NULL olabilir
            $data['property_size'],
            $data['rooms'],
            $data['bathrooms'],
            $data['floors_no'],
            $data['garages'] ?? 0,
            $data['energy_efficiency'] ?? null,
            $data['year_built'] ?? null,
            $data['property_lot_size'] ?? null,
            $data['category'],
            $data['latitude'],
            $data['longitude'],
            $data['city'],
            $data['state'],
            $data['country'],
            $data['street'],
            $data['zip'],
            $multimediaJson,
            $data['keywords'] ?? null,
            $distancesJson,
            $featuresJson,
            isset($data['featured']) ? 1 : 0
        ];

        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }

    public function updateListing($id, $data) {
        $sql = "UPDATE listings SET
                title = ?, description = ?, short_description = ?,
                sale_price = ?, rent_price = ?, property_size = ?,
                rooms = ?, bathrooms = ?, floors_no = ?, garages = ?,
                energy_efficiency = ?, year_built = ?, property_lot_size = ?,
                category = ?, latitude = ?, longitude = ?, city = ?,
                state = ?, country = ?, street = ?, zip = ?,
                multimedia = ?, keywords = ?, distances = ?,
                features = ?, featured = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?";

        // JSON verilerini null olarak ayarlama kontrolü
        $multimediaJson = (isset($data['multimedia']) && !empty(array_filter($data['multimedia']))) ? json_encode($data['multimedia']) : null;
        $distancesJson = (isset($data['distances']) && !empty($data['distances'])) ? json_encode($data['distances']) : null;
        $featuresJson = (isset($data['features']) && !empty(array_filter($data['features']))) ? json_encode($data['features']) : null;


        $params = [
            $data['title'],
            $data['description'],
            $data['short_description'] ?? null,
            $data['sale_price'] ?? null,
            $data['rent_price'] ?? null,
            $data['property_size'],
            $data['rooms'],
            $data['bathrooms'],
            $data['floors_no'],
            $data['garages'] ?? 0,
            $data['energy_efficiency'] ?? null,
            $data['year_built'] ?? null,
            $data['property_lot_size'] ?? null,
            $data['category'],
            $data['latitude'],
            $data['longitude'],
            $data['city'],
            $data['state'],
            $data['country'],
            $data['street'],
            $data['zip'],
            $multimediaJson,
            $data['keywords'] ?? null,
            $distancesJson,
            $featuresJson,
            isset($data['featured']) ? 1 : 0,
            $id
        ];

        return $this->db->query($sql, $params);
    }

    public function deleteListing($id) {
        // Önce ilişkili görselleri sil (isteğe bağlı, dosya sisteminden silme eklenebilir)
         $this->db->query("DELETE FROM listing_images WHERE listing_id = ?", [$id]);
        // Sonra ilanı sil
        $sql = "DELETE FROM listings WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function addListingImage($listingId, $imageUrl, $isMain = 0) {
        // EKLE: Aynı resim daha önce bu ilan için eklenmiş mi kontrol et
        $checkSql = "SELECT id FROM listing_images WHERE listing_id = ? AND image_url = ?";
        $existingImage = $this->db->fetch($checkSql, [$listingId, $imageUrl]);
        
        if ($existingImage) {
            // Resim zaten var, sadece ana görsel durumunu güncelle
            if ($isMain) {
                $this->setMainImage($listingId, $existingImage['id']);
            }
            return $existingImage['id']; // Mevcut ID'yi döndür, tekrar ekleme yapma
        }
    
        // Normal kaydetme işlemi - sadece daha önce olmayan görseller için
        if ($isMain) {
            $this->db->query(
                "UPDATE listing_images SET is_main = 0 WHERE listing_id = ?",
                [$listingId]
            );
        }
    
        $sql = "INSERT INTO listing_images (listing_id, image_url, is_main) VALUES (?, ?, ?)";
        $this->db->query($sql, [$listingId, $imageUrl, $isMain]);
        return $this->db->lastInsertId();
    }

     public function setMainImage($listingId, $imageId) {
         // Önce tüm görsellerin is_main'ini 0 yap
         $this->db->query("UPDATE listing_images SET is_main = 0 WHERE listing_id = ?", [$listingId]);
         // Sonra seçilen görseli 1 yap
         $this->db->query("UPDATE listing_images SET is_main = 1 WHERE id = ? AND listing_id = ?", [$imageId, $listingId]);
     }


    public function deleteListingImage($id) {
         // İsteğe bağlı: Dosya sisteminden görseli silme kodu buraya eklenebilir
         // $image = $this->db->fetch("SELECT image_url FROM listing_images WHERE id = ?", [$id]);
         // if ($image && file_exists($_SERVER['DOCUMENT_ROOT'] . $image['image_url'])) {
         //     unlink($_SERVER['DOCUMENT_ROOT'] . $image['image_url']);
         // }
        $sql = "DELETE FROM listing_images WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    // *** GÜNCELLENDİ: rent_price eklendi ***
    public function getMapData() {
        $sql = "SELECT id, title, latitude, longitude,
                sale_price, rent_price, category, featured, /* featured eklendi */
                (SELECT image_url FROM listing_images WHERE listing_id = listings.id AND is_main = 1 LIMIT 1) as main_image
                FROM listings
                WHERE latitude IS NOT NULL AND longitude IS NOT NULL
                AND (sale_price > 0 OR rent_price > 0)"; /* Fiyatı olanları getir */

        return $this->db->fetchAll($sql);
    }

    public function toggleFeatured($id) {
        $sql = "UPDATE listings SET featured = NOT featured WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    // countListings fonksiyonu getAllListings ile aynı filtreleri kullanacak şekilde güncellendi
    public function countListings($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM listings l WHERE 1=1"; // Alias 'l' eklendi
        $params = [];

         // Filtreleme koşulları (getAllListings ile aynı)
         if (!empty($filters)) {
            // Kiralık/Satılık filtresi
            if (isset($filters['listing_type']) && $filters['listing_type']) {
                if ($filters['listing_type'] == 'rent') {
                    $sql .= " AND rent_price IS NOT NULL AND rent_price > 0";
                } else if ($filters['listing_type'] == 'sale') {
                    $sql .= " AND sale_price IS NOT NULL AND sale_price > 0";
                }
            }

            // Kategori filtresi
            if (isset($filters['category']) && $filters['category']) {
                $sql .= " AND category = ?";
                $params[] = $filters['category'];
            }

            // Fiyat aralığı filtresi - satılık için
             if ((!isset($filters['listing_type']) || $filters['listing_type'] !== 'rent')) {
                if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
                    $sql .= " AND sale_price >= ?";
                    $params[] = $filters['min_price'];
                }
                if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
                    $sql .= " AND sale_price <= ?";
                    $params[] = $filters['max_price'];
                }
            }

            // Fiyat aralığı filtresi - kiralık için
            if (isset($filters['listing_type']) && $filters['listing_type'] === 'rent') {
                 if (isset($filters['min_rent']) && is_numeric($filters['min_rent'])) {
                    $sql .= " AND rent_price >= ?";
                    $params[] = $filters['min_rent'];
                }
                if (isset($filters['max_rent']) && is_numeric($filters['max_rent'])) {
                    $sql .= " AND rent_price <= ?";
                    $params[] = $filters['max_rent'];
                }
            }

            // Oda sayısı filtresi
            if (isset($filters['rooms']) && is_numeric($filters['rooms'])) {
                 if($filters['rooms'] == 5) {
                     $sql .= " AND rooms >= ?";
                 } else {
                     $sql .= " AND rooms = ?";
                 }
                $params[] = $filters['rooms'];
            }

            // Şehir/konum veya başlık filtresi
             if (isset($filters['city']) && !empty($filters['city'])) {
                 $searchTerm = "%{$filters['city']}%";
                 $sql .= " AND (l.title LIKE ? OR l.city LIKE ? OR l.state LIKE ? OR l.street LIKE ?)";
                 $params[] = $searchTerm;
                 $params[] = $searchTerm;
                 $params[] = $searchTerm;
                 $params[] = $searchTerm;
             }
              // Arama filtresi (admin panelinden gelen)
             if (isset($filters['search']) && !empty($filters['search'])) {
                 $searchTerm = "%{$filters['search']}%";
                 $sql .= " AND (l.title LIKE ? OR l.city LIKE ? OR l.state LIKE ?)"; // 'l' alias eklendi
                 $params[] = $searchTerm;
                 $params[] = $searchTerm;
                 $params[] = $searchTerm;
             }
             // Öne çıkanlar filtresi (admin panelinden gelen)
             if (isset($filters['featured']) && $filters['featured']) {
                 $sql .= " AND l.featured = 1"; // 'l' alias eklendi
             }
        }


        $result = $this->db->fetch($sql, $params);
        return $result['total'];
    }
}
?>