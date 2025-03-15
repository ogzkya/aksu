<?php
class Listing {
    private $db;
    
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
            }
            
            // Kategori filtresi
            if (isset($filters['category']) && $filters['category']) {
                $sql .= " AND category = ?";
                $params[] = $filters['category'];
            }
            
            // Fiyat aralığı filtresi - satılık için
            if (isset($filters['min_price']) && $filters['min_price'] && 
                (!isset($filters['listing_type']) || $filters['listing_type'] == 'sale')) {
                $sql .= " AND sale_price >= ?";
                $params[] = $filters['min_price'];
            }
            
            if (isset($filters['max_price']) && $filters['max_price'] && 
                (!isset($filters['listing_type']) || $filters['listing_type'] == 'sale')) {
                $sql .= " AND sale_price <= ?";
                $params[] = $filters['max_price'];
            }
            
            // Fiyat aralığı filtresi - kiralık için
            if (isset($filters['min_rent']) && $filters['min_rent'] && 
                isset($filters['listing_type']) && $filters['listing_type'] == 'rent') {
                $sql .= " AND rent_price >= ?";
                $params[] = $filters['min_rent'];
            }
            
            if (isset($filters['max_rent']) && $filters['max_rent'] && 
                isset($filters['listing_type']) && $filters['listing_type'] == 'rent') {
                $sql .= " AND rent_price <= ?";
                $params[] = $filters['max_rent'];
            }
            
            // Oda sayısı filtresi
            if (isset($filters['rooms']) && $filters['rooms']) {
                $sql .= " AND rooms = ?";
                $params[] = $filters['rooms'];
            }
            
            // Şehir/konum filtresi
            if (isset($filters['city']) && $filters['city']) {
                $sql .= " AND city LIKE ?";
                $params[] = "%{$filters['city']}%";
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
                WHERE featured = 1 
                ORDER BY created_at DESC 
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$limit]);
    }
    
    public function getNewListings($limit = 6) {
        $sql = "SELECT l.*, 
                (SELECT image_url FROM listing_images WHERE listing_id = l.id AND is_main = 1 LIMIT 1) as main_image 
                FROM listings l 
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
        $sql = "SELECT * FROM listing_images WHERE listing_id = ? ORDER BY is_main DESC";
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
        
        $params = [
            $data['title'], 
            $data['description'], 
            $data['short_description'], 
            $data['sale_price'], 
            $data['rent_price'], 
            $data['property_size'], 
            $data['rooms'], 
            $data['bathrooms'], 
            $data['floors_no'], 
            $data['garages'], 
            $data['energy_efficiency'], 
            $data['year_built'], 
            $data['property_lot_size'], 
            $data['category'], 
            $data['latitude'], 
            $data['longitude'], 
            $data['city'], 
            $data['state'], 
            $data['country'], 
            $data['street'], 
            $data['zip'], 
            json_encode($data['multimedia']), 
            $data['keywords'], 
            json_encode($data['distances']), 
            json_encode($data['features']), 
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
        
        $params = [
            $data['title'], 
            $data['description'], 
            $data['short_description'], 
            $data['sale_price'], 
            $data['rent_price'], 
            $data['property_size'], 
            $data['rooms'], 
            $data['bathrooms'], 
            $data['floors_no'], 
            $data['garages'], 
            $data['energy_efficiency'], 
            $data['year_built'], 
            $data['property_lot_size'], 
            $data['category'], 
            $data['latitude'], 
            $data['longitude'], 
            $data['city'], 
            $data['state'], 
            $data['country'], 
            $data['street'], 
            $data['zip'], 
            json_encode($data['multimedia']), 
            $data['keywords'], 
            json_encode($data['distances']), 
            json_encode($data['features']), 
            isset($data['featured']) ? 1 : 0,
            $id
        ];
        
        return $this->db->query($sql, $params);
    }
    
    public function deleteListing($id) {
        $sql = "DELETE FROM listings WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function addListingImage($listingId, $imageUrl, $isMain = 0) {
        // Eğer ana görsel ekleniyorsa, diğer tüm görsellerin ana görsel işaretini kaldır
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
    
    public function deleteListingImage($id) {
        $sql = "DELETE FROM listing_images WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function getMapData() {
        $sql = "SELECT id, title, latitude, longitude, 
                sale_price, category, 
                (SELECT image_url FROM listing_images WHERE listing_id = listings.id AND is_main = 1 LIMIT 1) as main_image 
                FROM listings 
                WHERE latitude IS NOT NULL AND longitude IS NOT NULL";
                
        return $this->db->fetchAll($sql);
    }
    
    public function toggleFeatured($id) {
        $sql = "UPDATE listings SET featured = NOT featured WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function countListings($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM listings WHERE 1=1";
        $params = [];
        
        // Filtreleme koşulları
        if (!empty($filters)) {
            if (isset($filters['category']) && $filters['category']) {
                $sql .= " AND category = ?";
                $params[] = $filters['category'];
            }
            
            if (isset($filters['min_price']) && $filters['min_price']) {
                $sql .= " AND sale_price >= ?";
                $params[] = $filters['min_price'];
            }
            
            if (isset($filters['max_price']) && $filters['max_price']) {
                $sql .= " AND sale_price <= ?";
                $params[] = $filters['max_price'];
            }
            
            if (isset($filters['rooms']) && $filters['rooms']) {
                $sql .= " AND rooms = ?";
                $params[] = $filters['rooms'];
            }
            
            if (isset($filters['city']) && $filters['city']) {
                $sql .= " AND city LIKE ?";
                $params[] = "%{$filters['city']}%";
            }
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['total'];
    }
}
?>
